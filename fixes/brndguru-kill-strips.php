<?php
/**
 * Plugin Name: BrndGuru — Kill Page Strips
 * Description: Removes page title bar strips. SAFE version - does NOT touch main header. AUTO-RUNS on activation.
 * Version: 2.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgks_run');
add_action('admin_init', function () {
    if (get_option('bgks_done') !== '2') bgks_run();
});

/* CSS: kill page title bar strips ONLY - does NOT affect main site header */
add_action('wp_head', function () { ?>
<style id="bg-kill-strips">
/* Astra page title bar ONLY (not the main nav header) */
.ast-page-title-bar,
.page-title-bar,
.ast-archive-description,
.ast-page-title-wrap { display: none !important; }

/* Remove gap between header and content */
.site-content,
#content { margin-top: 0 !important; padding-top: 0 !important; }

/* Kill empty divs creating strips */
.entry-content > div:empty,
.entry-content > section:empty,
.entry-content > p:empty { display: none !important; height: 0 !important; margin: 0 !important; padding: 0 !important; }

/* Remove Astra default page padding */
.ast-separate-container .ast-article-single { padding: 0 !important; }
.single-page .entry-content { padding: 0 !important; }
</style>
<?php }, 1);

function bgks_run() {
    global $wpdb;
    $log = [];

    $page_ids = [12, 1628, 4325, 23, 21, 1483, 762, 724, 766, 765, 2970];

    // SAFE: Only disable page title bar, NOT the main header
    foreach ($page_ids as $id) {
        // CORRECT: hides only the page title section below nav
        update_post_meta($id, 'ast-page-title-bar', 'disabled');
        update_post_meta($id, 'site-content-layout', 'plain-container');
        update_post_meta($id, 'site-sidebar-layout', 'no-sidebar');

        // IMPORTANT: Make sure main header is NOT disabled
        delete_post_meta($id, 'ast-main-header-display');
        $wpdb->delete($wpdb->postmeta, [
            'post_id'  => $id,
            'meta_key' => 'ast-main-header-display',
        ]);

        clean_post_cache($id);
    }
    $log[] = "Page title bar disabled on " . count($page_ids) . " pages (main header preserved)";

    // Global Astra settings - hide page title only
    $astra_settings = get_option('astra-settings', []);
    if (is_array($astra_settings)) {
        $astra_settings['page-title-style']     = 'disabled';
        $astra_settings['ast-page-title-bar']   = 'disabled';
        $astra_settings['single-page-title-bar'] = 'disabled';
        // Explicitly ensure main header display is not set to disabled
        if (isset($astra_settings['ast-main-header-display'])) {
            unset($astra_settings['ast-main-header-display']);
        }
        update_option('astra-settings', $astra_settings);
        $log[] = "Astra global page title settings updated";
    }

    // Clear caches
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
    foreach ([WP_CONTENT_DIR.'/cache/swift-performance/', WP_CONTENT_DIR.'/cache/swift-performance-lite/'] as $dir) {
        if (is_dir($dir)) {
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($it as $f) { $f->isDir() ? @rmdir($f) : @unlink($f); }
        }
    }
    if (class_exists('Swift_Performance')) do_action('swift_performance_clear_all_cache');
    wp_cache_flush();
    if (function_exists('opcache_reset')) opcache_reset();

    update_option('bgks_log', $log);
    update_option('bgks_done', '2');
}

// Astra filters to disable page title bar only
add_filter('astra_page_title_bar_display', '__return_false');
add_filter('astra_the_title_enabled', '__return_false');
add_filter('astra_page_title_enabled', '__return_false');

// Remove Astra entry title on pages (page title heading, not nav)
add_action('wp', function () {
    if (is_page()) {
        remove_action('astra_entry_top', 'astra_entry_header_markup', 10);
        remove_action('astra_page_title_bar', 'astra_page_title_bar_markup', 10);
    }
});

add_action('admin_notices', function () {
    if (get_option('bgks_done') !== '2') return;
    $log = get_option('bgks_log', []);
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px 16px;">
        <h3 style="margin:0 0 8px;">Page Strips Removed (Header Safe)</h3>
        <ul style="margin:0 0 10px;padding-left:20px;">
            <?php foreach ($log as $l): ?><li><?php echo esc_html($l); ?></li><?php endforeach; ?>
        </ul>
        <p>
            <a href="<?php echo home_url('/portfolio/'); ?>" target="_blank" class="button button-primary">Portfolio</a>
            <a href="<?php echo home_url('/services/'); ?>" target="_blank" class="button">Services</a>
            <a href="<?php echo home_url('/'); ?>" target="_blank" class="button">Home</a>
            &nbsp; <strong>Keep this plugin active</strong> — CSS runs on every page.
        </p>
    </div>
    <?php
});
