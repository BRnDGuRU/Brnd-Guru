<?php
/**
 * Plugin Name: BrndGuru — EMERGENCY Restore Header
 * Description: Restores the disappeared Astra site header by removing the incorrect ast-main-header-display=disabled meta. AUTO-RUNS on activation.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgrh_run');
add_action('admin_init', function () {
    if (get_option('bgrh_done') !== '1') bgrh_run();
});

function bgrh_run() {
    global $wpdb;
    $log = [];

    // All page IDs that had the wrong meta set
    $page_ids = [12, 1628, 4325, 23, 21, 1483, 762, 724, 766, 765, 2970];

    // FIX: Remove the WRONG meta that hid the main Astra header
    // ast-main-header-display = 'disabled' kills the ENTIRE site header nav
    $fixed = 0;
    foreach ($page_ids as $id) {
        delete_post_meta($id, 'ast-main-header-display');
        $wpdb->delete($wpdb->postmeta, [
            'post_id'  => $id,
            'meta_key' => 'ast-main-header-display',
        ]);
        clean_post_cache($id);
        $fixed++;
    }
    $log[] = "Removed ast-main-header-display from {$fixed} pages - header restored";

    // Global sweep - remove from any other pages too
    $extra = $wpdb->query(
        "DELETE FROM {$wpdb->postmeta}
         WHERE meta_key = 'ast-main-header-display'
         AND meta_value = 'disabled'"
    );
    if ($extra > 0) {
        $log[] = "Also cleared {$extra} additional rows site-wide";
    }

    // Keep ast-page-title-bar disabled (correct - hides page title bar only)
    foreach ($page_ids as $id) {
        update_post_meta($id, 'ast-page-title-bar', 'disabled');
    }
    $log[] = "ast-page-title-bar kept disabled on all pages (correct)";

    // Check global Astra settings
    $astra = get_option('astra-settings', []);
    if (is_array($astra) && isset($astra['ast-main-header-display'])) {
        unset($astra['ast-main-header-display']);
        update_option('astra-settings', $astra);
        $log[] = "Removed ast-main-header-display from global Astra settings";
    }

    // Clear ALL caches
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        $log[] = "Elementor cache cleared";
    }

    foreach ([
        WP_CONTENT_DIR . '/cache/swift-performance/',
        WP_CONTENT_DIR . '/cache/swift-performance-lite/',
    ] as $dir) {
        if (is_dir($dir)) {
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($it as $f) {
                $f->isDir() ? @rmdir($f) : @unlink($f);
            }
            $log[] = "Wiped cache: " . basename($dir);
        }
    }

    if (class_exists('Swift_Performance')) {
        do_action('swift_performance_clear_all_cache');
    }

    wp_cache_flush();
    if (function_exists('opcache_reset')) opcache_reset();
    flush_rewrite_rules(true);

    update_option('bgrh_log', $log);
    update_option('bgrh_done', '1');
}

add_action('admin_notices', function () {
    if (get_option('bgrh_done') !== '1') return;
    $log = get_option('bgrh_log', []);
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px 16px;border-left-color:#00a32a;">
        <h3 style="margin:0 0 8px;">Header Restored!</h3>
        <ul style="margin:0 0 10px;padding-left:20px;font-size:13px;">
            <?php foreach ($log as $line): ?>
                <li><?php echo esc_html($line); ?></li>
            <?php endforeach; ?>
        </ul>
        <p>
            <a href="<?php echo home_url('/'); ?>" target="_blank" class="button button-primary">Check Home</a>
            <a href="<?php echo home_url('/portfolio/'); ?>" target="_blank" class="button">Portfolio</a>
            <a href="<?php echo home_url('/services/'); ?>" target="_blank" class="button">Services</a>
            Open in incognito to confirm header is back. Deactivate this plugin after confirming.
        </p>
    </div>
    <?php
});
