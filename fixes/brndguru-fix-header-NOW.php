<?php
/**
 * Plugin Name: BrndGuru FIX HEADER NOW
 * Description: Restores missing site header immediately. Run once then deactivate.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

// Run immediately on every admin page load until fixed
add_action('admin_init', 'bgfh_run_now');

function bgfh_run_now() {
    global $wpdb;

    // ── NUCLEAR: delete ast-main-header-display from EVERY post in the database ──
    $deleted = $wpdb->query(
        "DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'ast-main-header-display'"
    );

    // ── Also fix page templates - make sure no pages are on elementor_canvas ──
    // elementor_canvas = no header/footer. elementor_full_width = shows header.
    $canvas_pages = $wpdb->get_results(
        "SELECT post_id FROM {$wpdb->postmeta}
         WHERE meta_key = '_wp_page_template'
         AND meta_value = 'elementor_canvas'"
    );
    $template_fixed = 0;
    foreach ($canvas_pages as $row) {
        update_post_meta($row->post_id, '_wp_page_template', 'elementor_full_width');
        clean_post_cache($row->post_id);
        $template_fixed++;
    }

    // ── Fix global Astra settings ──
    $astra = get_option('astra-settings', []);
    if (is_array($astra)) {
        unset($astra['ast-main-header-display']);
        $astra['header-main-layout-width'] = 'full-width';
        update_option('astra-settings', $astra);
    }

    // ── Nuke ALL caches ──
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
    foreach ([
        WP_CONTENT_DIR . '/cache/swift-performance/',
        WP_CONTENT_DIR . '/cache/swift-performance-lite/',
        WP_CONTENT_DIR . '/cache/elementor/',
    ] as $dir) {
        if (is_dir($dir)) {
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($it as $f) {
                $f->isDir() ? @rmdir($f) : @unlink($f);
            }
        }
    }
    if (class_exists('Swift_Performance')) {
        do_action('swift_performance_clear_all_cache');
    }
    wp_cache_flush();
    if (function_exists('opcache_reset')) opcache_reset();
    delete_transient('elementor_css');
    flush_rewrite_rules(false);

    // Store result for notice
    update_option('bgfh_result', [
        'deleted_meta' => $deleted,
        'fixed_templates' => $template_fixed,
        'time' => current_time('mysql'),
    ]);
}

add_action('admin_notices', function () {
    $r = get_option('bgfh_result');
    if (!$r) return;
    ?>
    <div class="notice notice-success" style="padding:16px;border-left:4px solid #00a32a;">
        <h2 style="margin:0 0 8px;color:#00a32a;">✅ HEADER FIX APPLIED</h2>
        <p style="margin:0 0 8px;font-size:14px;">
            Deleted <strong><?php echo (int)$r['deleted_meta']; ?></strong> bad header meta rows.
            Fixed <strong><?php echo (int)$r['fixed_templates']; ?></strong> canvas page templates.
            All caches cleared. Run at: <?php echo esc_html($r['time']); ?>
        </p>
        <p style="margin:0;">
            <a href="<?php echo home_url('/'); ?>" target="_blank" class="button button-primary" style="margin-right:8px;">
                ✅ Check Homepage (open in NEW incognito tab)
            </a>
            <a href="<?php echo home_url('/services/'); ?>" target="_blank" class="button" style="margin-right:8px;">
                Services
            </a>
            <a href="<?php echo home_url('/portfolio/'); ?>" target="_blank" class="button">
                Portfolio
            </a>
        </p>
        <p style="margin:8px 0 0;color:#666;font-size:12px;">
            ⚠️ Once header is confirmed working: Plugins → Deactivate this plugin → Delete it.
        </p>
    </div>
    <?php
});
