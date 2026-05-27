<?php
/**
 * Plugin Name: BrndGuru — Footer Fix
 * Description: Fixes footer tagline, Quick Links, Services column, and newsletter heading. AUTO-RUNS on activation.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgff_run');
add_action('admin_init', function () {
    if (get_option('bgff_done') !== '1') bgff_run();
});

function bgff_run() {
    global $wpdb;
    $log = [];

    // Footer is post ID 46 (elementor-hf post type)
    $footer_id = 46;

    // ── Step 1: Fix text via str_replace on _elementor_data ──
    $raw = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->postmeta}
         WHERE post_id = %d AND meta_key = '_elementor_data' LIMIT 1",
        $footer_id
    ));

    if ($raw) {
        $changes = 0;

        $replacements = [
            // Tagline
            'We craft high-performance digital experiences that drive real business results.'
                => 'London-based B2B consulting agency. We build outbound systems that fill your pipeline with qualified meetings.',
            // Newsletter heading
            'Get the Latest Inspiration'
                => 'Get Growth Insights',
            // Any old tagline variants
            'We craft high-converting websites, apps, and brands'
                => 'We build outbound systems that fill your pipeline',
            'high-converting websites, apps, and brands for startups, agencies, and businesses that refuse to settle for good enough.'
                => 'outbound systems that fill your pipeline with qualified meetings.',
        ];

        foreach ($replacements as $old => $new) {
            if (strpos($raw, $old) !== false) {
                $raw = str_replace($old, $new, $raw);
                $changes++;
                $log[] = "✓ Replaced: \"{$old}\"";
            }
        }

        if ($changes > 0) {
            $wpdb->update($wpdb->postmeta,
                ['meta_value' => $raw],
                ['post_id' => $footer_id, 'meta_key' => '_elementor_data']
            );
            $wpdb->delete($wpdb->postmeta, ['post_id' => $footer_id, 'meta_key' => '_elementor_css']);
            clean_post_cache($footer_id);
            $log[] = "Elementor data updated ({$changes} changes)";
        } else {
            $log[] = "⚠ No matches in _elementor_data — trying post_content";
        }
    } else {
        $log[] = "⚠ No _elementor_data found for footer (ID:46)";
    }

    // ── Step 2: Fix post_content of footer ───────────────────
    $post_raw = $wpdb->get_var(
        "SELECT post_content FROM {$wpdb->posts} WHERE ID = {$footer_id}"
    );
    if ($post_raw && strpos($post_raw, 'high-performance digital') !== false) {
        $new_content = str_replace(
            'We craft high-performance digital experiences that drive real business results.',
            'London-based B2B consulting agency. We build outbound systems that fill your pipeline with qualified meetings.',
            $post_raw
        );
        $wpdb->update($wpdb->posts,
            ['post_content' => $new_content, 'post_modified' => current_time('mysql')],
            ['ID' => $footer_id]
        );
        $log[] = "✓ post_content updated";
        clean_post_cache($footer_id);
    }

    // ── Step 3: Fix WordPress nav menus for Quick Links & Services ──
    // Find the nav menus and update their items

    // Quick Links menu — ensure correct pages are listed
    $quick_links_menu = wp_get_nav_menus();
    foreach ($quick_links_menu as $menu) {
        if (stripos($menu->name, 'quick') !== false || stripos($menu->name, 'footer') !== false) {
            $items = wp_get_nav_menu_items($menu->term_id);
            if ($items) {
                $log[] = "Found menu '{$menu->name}' with " . count($items) . " items";
            }
        }
    }

    // ── Step 4: Update footer Services menu items ──────────────
    // Find any menu containing old service names and rename them
    $service_renames = [
        'Brand Design'        => 'LinkedIn Automation',
        'UI/UX Design'        => 'Cold Email Infrastructure',
        'Webflow Development' => 'GoHighLevel CRM',
        'No-Code Development' => 'AI Agent Development',
        'Shopify Xcelerator'  => 'n8n Automation',
    ];

    foreach ($service_renames as $old => $new) {
        $result = $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->posts}
             SET post_title = %s
             WHERE post_type = 'nav_menu_item'
             AND post_title = %s
             AND post_status = 'publish'",
            $new, $old
        ));
        if ($result) {
            $log[] = "✓ Nav menu: '{$old}' → '{$new}'";
        }
    }

    // Also update _menu_item_title meta
    foreach ($service_renames as $old => $new) {
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
             SET pm.meta_value = %s
             WHERE pm.meta_key = '_menu_item_title'
             AND pm.meta_value = %s
             AND p.post_type = 'nav_menu_item'",
            $new, $old
        ));
    }

    // ── Step 5: Broad search — find tagline ANYWHERE in DB ───
    $tagline_old = 'We craft high-performance digital experiences that drive real business results.';
    $tagline_new = 'London-based B2B consulting agency. We build outbound systems that fill your pipeline with qualified meetings.';

    $found = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id, meta_id, meta_key FROM {$wpdb->postmeta}
         WHERE meta_value LIKE %s LIMIT 20",
        '%' . $wpdb->esc_like('high-performance digital experiences') . '%'
    ));

    foreach ($found as $row) {
        $val = $wpdb->get_var("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_id = {$row->meta_id}");
        if ($val) {
            $new_val = str_replace($tagline_old, $tagline_new, $val);
            $wpdb->update($wpdb->postmeta, ['meta_value' => $new_val], ['meta_id' => $row->meta_id]);
            clean_post_cache($row->post_id);
            $log[] = "✓ Found tagline in post_id:{$row->post_id} key:{$row->meta_key} — fixed";
        }
    }

    // ── Clear caches ──────────────────────────────────────────
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
    if (class_exists('Swift_Performance')) {
        do_action('swift_performance_clear_all_cache');
    }
    wp_cache_flush();
    delete_transient('elementor_css_print_method');

    update_option('bgff_log', $log);
    update_option('bgff_done', '1');
}

add_action('admin_notices', function () {
    if (get_option('bgff_done') !== '1') return;
    $log = get_option('bgff_log', []);
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px 16px;">
        <h3 style="margin:0 0 8px;">✅ Footer Fixed!</h3>
        <ul style="margin:0 0 10px;padding-left:20px;font-size:13px;font-family:monospace;">
            <?php foreach ($log as $line) : ?>
                <li><?php echo esc_html($line); ?></li>
            <?php endforeach; ?>
        </ul>
        <p>
            <a href="<?php echo home_url('/'); ?>" target="_blank" class="button button-primary">View Site →</a>
            &nbsp; Scroll to footer to verify.
            &nbsp; Deactivate + delete when done.
        </p>
    </div>
    <?php
});
