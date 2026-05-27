<?php
/**
 * Plugin Name: BrndGuru — Kill Page Strips
 * Description: Permanently removes all rogue strips: hides Astra page title bar, removes content injections, fixes all gaps. AUTO-RUNS on activation.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgks_run');
add_action('admin_init', function () {
    if (get_option('bgks_done') !== '1') bgks_run();
});

/* ── CSS: kill every possible strip source ── */
add_action('wp_head', function () { ?>
<style id="bg-kill-strips">
/* ── Astra page title bar ── */
.ast-page-title-bar,
.page-title-bar,
.ast-archive-description,
.page-header,
.entry-header .ast-breadcrumbs-wrapper,
.ast-page-title-wrap,
.site-above-header-wrap,
.hfb-above-header { display: none !important; }

/* ── Remove any gap between header and content ── */
.site-content,
.content-area,
.ast-container,
#content,
.entry-content { margin-top: 0 !important; padding-top: 0 !important; }

/* ── Kill any accidental empty divs creating strips ── */
.entry-content > div:empty,
.entry-content > section:empty,
.entry-content > p:empty { display: none !important; height: 0 !important; margin: 0 !important; padding: 0 !important; }

/* ── Remove Astra default page padding ── */
.ast-separate-container .ast-article-single { padding: 0 !important; }
.single-page .entry-content { padding: 0 !important; }

/* ── WordPress admin bar gap fix ── */
html body.admin-bar .site-header,
html body.admin-bar #masthead { top: 32px !important; }
@media screen and (max-width: 782px) {
  html body.admin-bar .site-header { top: 46px !important; }
}
</style>
<?php }, 1);

function bgks_run() {
    global $wpdb;
    $log = [];

    // Page IDs to fix
    $page_ids = [12, 1628, 4325, 23, 21, 1483, 762, 724, 766, 765, 2970];

    // Disable Astra page title bar per page
    foreach ($page_ids as $id) {
        // Astra uses these meta keys to hide page title
        update_post_meta($id, 'ast-main-header-display', 'disabled');
        update_post_meta($id, 'ast-page-title-bar', 'disabled');
        update_post_meta($id, 'site-content-layout', 'plain-container');
        // Hide page title heading
        update_post_meta($id, 'site-sidebar-layout', 'no-sidebar');
        clean_post_cache($id);
    }
    $log[] = "✓ Astra page title bar disabled on " . count($page_ids) . " pages";

    // Also set global Astra option to hide page titles
    $astra_settings = get_option('astra-settings', []);
    if (is_array($astra_settings)) {
        $astra_settings['page-title-style']        = 'disabled';
        $astra_settings['ast-page-title-bar']       = 'disabled';
        $astra_settings['single-page-title-bar']    = 'disabled';
        update_option('astra-settings', $astra_settings);
        $log[] = "✓ Astra global page title settings updated";
    }

    // Clear all caches
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
    update_option('bgks_done', '1');
}

// Also hook into Astra's page title filter to remove it programmatically
add_filter('astra_page_title_bar_display', '__return_false');
add_filter('astra_the_title_enabled', '__return_false');
add_filter('astra_page_title_enabled', '__return_false');

// Remove Astra entry title on pages
add_action('wp', function () {
    if (is_page()) {
        remove_action('astra_entry_top', 'astra_entry_header_markup', 10);
        remove_action('astra_page_title_bar', 'astra_page_title_bar_markup', 10);
    }
});

add_action('admin_notices', function () {
    if (get_option('bgks_done') !== '1') return;
    $log = get_option('bgks_log', []);
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px 16px;">
        <h3 style="margin:0 0 8px;">✅ All Strips Killed!</h3>
        <ul style="margin:0 0 10px;padding-left:20px;">
            <?php foreach ($log as $l): ?><li><?php echo esc_html($l); ?></li><?php endforeach; ?>
        </ul>
        <p>
            <a href="<?php echo home_url('/portfolio/'); ?>" target="_blank" class="button button-primary">Portfolio →</a>
            <a href="<?php echo home_url('/services/'); ?>" target="_blank" class="button">Services →</a>
            <a href="<?php echo home_url('/'); ?>" target="_blank" class="button">Home →</a>
            &nbsp; <strong>Keep this plugin active</strong> — CSS runs on every page.
        </p>
    </div>
    <?php
});
