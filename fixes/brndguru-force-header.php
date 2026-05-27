<?php
/**
 * Plugin Name: BrndGuru FORCE SHOW HEADER
 * Description: Forces site header visible via CSS + deletes all bad meta. Install and activate from wp-admin.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

/* ── FORCE HEADER VISIBLE VIA CSS — runs on every page load ── */
add_action('wp_head', function () { ?>
<style id="bg-force-header">
/* Force Astra header and all its wrappers to be visible */
#masthead,
.site-header,
.ast-header-break-point,
.site-primary-header-wrap,
.ast-primary-header-area,
.site-header-wrap,
.hfb-primary-header,
.ast-main-header-wrap,
.elementor-location-header,
header.site-header {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    height: auto !important;
    overflow: visible !important;
    position: relative !important;
}
</style>
<?php }, 0);

/* ── NUKE ALL BAD META + CACHE on admin load ── */
add_action('admin_init', function () {
    global $wpdb;

    // Delete ALL ast-main-header-display rows in the ENTIRE database
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'ast-main-header-display'");

    // Fix any canvas templates
    $wpdb->query(
        "UPDATE {$wpdb->postmeta}
         SET meta_value = 'elementor_full_width'
         WHERE meta_key = '_wp_page_template'
         AND meta_value = 'elementor_canvas'"
    );

    // Fix Astra global settings
    $astra = get_option('astra-settings', []);
    if (is_array($astra)) {
        unset($astra['ast-main-header-display']);
        update_option('astra-settings', $astra);
    }

    // Also check theme_mods
    $theme_mods = get_option('theme_mods_astra', []);
    if (is_array($theme_mods)) {
        unset($theme_mods['ast-main-header-display']);
        update_option('theme_mods_astra', $theme_mods);
    }

    // Wipe ALL caches
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
    foreach ([
        WP_CONTENT_DIR . '/cache/swift-performance/',
        WP_CONTENT_DIR . '/cache/swift-performance-lite/',
        WP_CONTENT_DIR . '/cache/elementor/',
        WP_CONTENT_DIR . '/cache/astra/',
    ] as $dir) {
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
    flush_rewrite_rules(false);
}, 1);

/* ── ADMIN NOTICE ── */
add_action('admin_notices', function () { ?>
    <div class="notice notice-success" style="padding:16px;border-left:4px solid #00a32a;background:#f0fff4;">
        <h2 style="margin:0 0 10px;color:#00a32a;">✅ HEADER FIX RUNNING</h2>
        <p style="margin:0 0 12px;font-size:15px;">
            Bad meta deleted. Caches cleared. CSS is forcing header visible on all pages.
        </p>
        <p style="margin:0;">
            <a href="<?php echo home_url('/'); ?>" target="_blank" class="button button-primary" style="font-size:14px;padding:6px 20px;">
                👉 Open Homepage in Incognito
            </a>
            &nbsp;
            <a href="<?php echo home_url('/services/'); ?>" target="_blank" class="button" style="font-size:14px;">
                Services
            </a>
            &nbsp;
            <a href="<?php echo home_url('/portfolio/'); ?>" target="_blank" class="button" style="font-size:14px;">
                Portfolio
            </a>
        </p>
    </div>
<?php });
