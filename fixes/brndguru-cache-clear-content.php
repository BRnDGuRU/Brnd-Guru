<?php
/**
 * Plugin Name: BrndGuru — Cache Clear + Home Content Fix
 * Description: Clears Swift Performance + all caches, then replaces the exact strings visible to logged-out visitors. AUTO-RUNS on activation.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgcc_run');
add_action('admin_init', function () {
    if (get_option('bgcc_done') !== '1') bgcc_run();
});

function bgcc_run() {
    global $wpdb;
    $log = [];

    // ════════════════════════════════════════════════════
    // 1. REPLACE EXACT STRINGS VISIBLE IN INCOGNITO VIEW
    // ════════════════════════════════════════════════════

    // All strings we need to replace (exact as seen on live site)
    $replacements = [
        // Hero badge
        'BRAND & DIGITAL CONSULTING'
            => 'B2B GROWTH & AUTOMATION CONSULTING',
        'Brand &amp; Digital Consulting'
            => 'B2B Growth &amp; Automation Consulting',
        'Brand & Digital Consulting'
            => 'B2B Growth & Automation Consulting',

        // Hero heading
        'We Build Brands That Dominate Markets.'
            => 'We Build Outbound Systems That Generate Revenue.',
        'We Build Brands That Dominate Markets'
            => 'We Build Outbound Systems That Generate Revenue',

        // Hero subheading
        'BrndGuru is a results-driven consulting agency that transforms ambitious companies into category-defining brands. Strategy, design, and digital — all under one roof.'
            => 'BRND GURU is a London-based B2B consulting agency. We design and deploy LinkedIn automation, cold email, AI agents, and CRM systems that consistently fill your pipeline.',
        // HTML encoded variant
        'BrndGuru is a results-driven consulting agency that transforms ambitious companies into category-defining brands. Strategy, design, and digital — all under one roof.'
            => 'BRND GURU is a London-based B2B consulting agency. We design and deploy LinkedIn automation, cold email, AI agents, and CRM systems that consistently fill your pipeline.',

        // Second button
        'See Our Work'
            => 'View Our Results',

        // Trusted by section
        'TRUSTED BY BRANDS IN'
            => 'TRUSTED BY B2B BUSINESSES IN',

        // Industry tags (optionally update)
        // Keep E-Commerce, SaaS, Retail — these are fine
        // These were in original, leave them

        // Also catch any remaining old heading variants
        'Where Stunning Design Meets Flawless Functionality'
            => 'We Build Outbound Systems That Generate Revenue.',
        'We craft high-converting websites, apps, and brands for startups, agencies, and businesses that refuse to settle for good enough.'
            => 'BRND GURU is a London-based B2B consulting agency. We design and deploy LinkedIn automation, cold email, AI agents, and CRM systems that consistently fill your pipeline.',
    ];

    // Search ALL postmeta rows for these strings
    $search_terms = [
        'We Build Brands That Dominate Markets',
        'results-driven consulting agency',
        'category-defining brands',
        'Where Stunning Design Meets',
        'BRAND & DIGITAL CONSULTING',
        'See Our Work',
    ];

    $meta_ids_updated = 0;
    foreach ($search_terms as $term) {
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT meta_id, post_id, meta_key, meta_value FROM {$wpdb->postmeta}
             WHERE meta_value LIKE %s LIMIT 30",
            '%' . $wpdb->esc_like($term) . '%'
        ));
        foreach ($rows as $row) {
            $new_val = $row->meta_value;
            foreach ($replacements as $old => $new) {
                $new_val = str_replace($old, $new, $new_val);
            }
            if ($new_val !== $row->meta_value) {
                $wpdb->update($wpdb->postmeta, ['meta_value' => $new_val], ['meta_id' => $row->meta_id]);
                clean_post_cache($row->post_id);
                $meta_ids_updated++;
                $log[] = "✓ postmeta ID:{$row->meta_id} post:{$row->post_id} key:{$row->meta_key}";
            }
        }
    }
    $log[] = "Postmeta rows updated: {$meta_ids_updated}";

    // Also search posts table (post_content)
    $posts_updated = 0;
    foreach ($search_terms as $term) {
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, post_type, post_content FROM {$wpdb->posts}
             WHERE post_content LIKE %s
             AND post_status NOT IN ('auto-draft','inherit')
             LIMIT 20",
            '%' . $wpdb->esc_like($term) . '%'
        ));
        foreach ($rows as $row) {
            $new_content = $row->post_content;
            foreach ($replacements as $old => $new) {
                $new_content = str_replace($old, $new, $new_content);
            }
            if ($new_content !== $row->post_content) {
                $wpdb->update($wpdb->posts,
                    ['post_content' => $new_content, 'post_modified' => current_time('mysql')],
                    ['ID' => $row->ID]
                );
                clean_post_cache($row->ID);
                $posts_updated++;
                $log[] = "✓ posts row ID:{$row->ID} type:{$row->post_type}";
            }
        }
    }
    $log[] = "Posts rows updated: {$posts_updated}";

    // ════════════════════════════════════════════════════
    // 2. NUCLEAR CACHE CLEAR — every caching layer
    // ════════════════════════════════════════════════════

    // Elementor CSS cache
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        $log[] = "✓ Elementor CSS cache cleared";
    }

    // Swift Performance — try every known method
    $swift_cleared = false;
    if (class_exists('Swift_Performance')) {
        do_action('swift_performance_clear_all_cache');
        $swift_cleared = true;
    }
    if (class_exists('Swift_Performance_Lite')) {
        do_action('swift_performance_lite_clear_all_cache');
        $swift_cleared = true;
    }
    // Direct Swift Performance cache directory wipe
    $swift_dirs = [
        WP_CONTENT_DIR . '/cache/swift-performance/',
        WP_CONTENT_DIR . '/cache/swift-performance-lite/',
    ];
    foreach ($swift_dirs as $dir) {
        if (is_dir($dir)) {
            bgcc_delete_dir($dir);
            wp_mkdir_p($dir);
            $swift_cleared = true;
        }
    }
    $log[] = $swift_cleared ? "✓ Swift Performance cache cleared" : "ℹ Swift Performance not found";

    // WP Super Cache
    if (function_exists('wp_cache_clear_cache')) {
        wp_cache_clear_cache();
        $log[] = "✓ WP Super Cache cleared";
    }
    // W3 Total Cache
    if (function_exists('w3tc_flush_all')) {
        w3tc_flush_all();
        $log[] = "✓ W3 Total Cache cleared";
    }
    // WP Rocket
    if (function_exists('rocket_clean_domain')) {
        rocket_clean_domain();
        $log[] = "✓ WP Rocket cache cleared";
    }
    // LiteSpeed
    if (class_exists('\LiteSpeed\Purge')) {
        do_action('litespeed_purge_all');
        $log[] = "✓ LiteSpeed cache cleared";
    }
    // WP object cache
    wp_cache_flush();
    // Opcache
    if (function_exists('opcache_reset')) {
        opcache_reset();
        $log[] = "✓ PHP opcache reset";
    }

    flush_rewrite_rules(true);

    update_option('bgcc_log', $log);
    update_option('bgcc_done', '1');
}

// Recursively delete a directory (for cache wipe)
function bgcc_delete_dir(string $dir): void {
    if (!is_dir($dir)) return;
    $items = array_diff(scandir($dir), ['.', '..']);
    foreach ($items as $item) {
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? bgcc_delete_dir($path) : unlink($path);
    }
    // Don't rmdir root — just empty it so Swift recreates structure
}

add_action('admin_notices', function () {
    if (get_option('bgcc_done') !== '1') return;
    $log = get_option('bgcc_log', []);
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px 16px;">
        <h3 style="margin:0 0 8px;">✅ Cache Cleared + Content Updated!</h3>
        <ul style="margin:0 0 10px;padding-left:20px;font-size:12px;font-family:monospace;max-height:300px;overflow-y:auto;">
            <?php foreach ($log as $line) : ?>
                <li><?php echo esc_html($line); ?></li>
            <?php endforeach; ?>
        </ul>
        <p style="margin:0;">
            <a href="<?php echo home_url('/'); ?>" target="_blank" class="button button-primary">👁 View Site in New Tab (Incognito) →</a>
            &nbsp; Deactivate + delete when done.
        </p>
    </div>
    <?php
});
