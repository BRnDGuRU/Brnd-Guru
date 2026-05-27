<?php
/**
 * Plugin Name: BrndGuru — Restore Header
 * Description: Fixes missing header/footer by switching pages from elementor_canvas back to elementor_full_width
 * Version: 1.0
 */

add_action('admin_menu', function () {
    add_menu_page('BG Restore Header', 'BG Restore Header', 'manage_options', 'bg-restore-header', 'bg_restore_header_page', 'dashicons-redo', 2);
});

function bg_restore_header_page() {
    ?>
    <div class="wrap">
        <h1>🔧 BrndGuru — Restore Header</h1>
        <?php
        if (isset($_POST['bg_restore']) && check_admin_referer('bg_restore_header_nonce')) {
            bg_do_restore();
        } else {
            ?>
            <p style="font-size:16px;color:#d63638;font-weight:bold;">The header is missing because pages are set to <code>elementor_canvas</code> (no header/footer). This will switch them to <code>elementor_full_width</code> which restores the Astra header and footer.</p>
            <form method="post">
                <?php wp_nonce_field('bg_restore_header_nonce'); ?>
                <input type="hidden" name="bg_restore" value="1">
                <p><input type="submit" class="button button-primary button-hero" value="▶ RESTORE HEADER ON ALL PAGES"></p>
            </form>
            <?php
        }
        ?>
    </div>
    <?php
}

function bg_do_restore() {
    // All pages that may have been set to elementor_canvas
    // Get ALL pages and fix any set to elementor_canvas
    $pages = get_posts([
        'post_type'      => 'page',
        'posts_per_page' => -1,
        'post_status'    => ['publish', 'draft'],
        'meta_query'     => [
            [
                'key'   => '_wp_page_template',
                'value' => 'elementor_canvas',
            ],
        ],
    ]);

    $fixed = [];

    foreach ($pages as $page) {
        update_post_meta($page->ID, '_wp_page_template', 'elementor_full_width');
        $fixed[] = "ID:{$page->ID} — \"{$page->post_title}\" → elementor_full_width";
    }

    // Also explicitly fix key known pages regardless
    $key_ids = [12, 1628, 4325, 23, 762, 724, 766, 765, 2970, 21, 1483];
    foreach ($key_ids as $id) {
        $current = get_post_meta($id, '_wp_page_template', true);
        if ($current === 'elementor_canvas' || $current === '') {
            update_post_meta($id, '_wp_page_template', 'elementor_full_width');
            $post = get_post($id);
            if ($post) {
                $already_listed = false;
                foreach ($fixed as $f) {
                    if (strpos($f, "ID:{$id}") !== false) { $already_listed = true; break; }
                }
                if (!$already_listed) {
                    $fixed[] = "ID:{$id} — \"{$post->post_title}\" (was: {$current}) → elementor_full_width";
                }
            }
        }
    }

    // Flush rewrite rules
    flush_rewrite_rules(true);

    // Clear Elementor CSS cache
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }

    // Clear Swift Performance cache if active
    if (class_exists('Swift_Performance')) {
        do_action('swift_performance_clear_all_cache');
    }

    echo '<div class="notice notice-success is-dismissible"><p><strong>✅ Header restored on ' . count($fixed) . ' page(s):</strong></p><ul>';
    foreach ($fixed as $f) {
        echo '<li style="margin-left:20px;">✓ ' . esc_html($f) . '</li>';
    }
    echo '</ul>';
    echo '<p>⚡ Page template cache cleared. <a href="' . home_url('/') . '" target="_blank">View site →</a></p>';
    echo '</div>';

    if (empty($fixed)) {
        echo '<div class="notice notice-warning"><p>No pages with <code>elementor_canvas</code> template found. All pages may already be correct. Check if header shows now: <a href="' . home_url('/') . '" target="_blank">View site →</a></p></div>';
    }
}
