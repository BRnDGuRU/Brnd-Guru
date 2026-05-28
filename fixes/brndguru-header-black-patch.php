<?php
/**
 * Plugin Name: BrndGuru — Header Black + Services Patch
 * Description: Black header background, lighter service text, moves stats bar below services.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

/* ── CSS: Black header + lighter text ── */
add_action('wp_head', function () { ?>
<style id="bg-header-black-patch">

/* ── BLACK HEADER ── */
#masthead,
.site-header,
.site-above-header-wrap,
.hfb-above-header,
.ast-site-header-wrap,
.main-header-bar,
.ast-primary-header-area {
    background-color: #0d0d0d !important;
    background: #0d0d0d !important;
}

/* White nav links on dark header */
.main-navigation a,
.ast-nav-menu a,
.ast-builder-menu a,
.menu-item > a,
.ast-header-break-point .main-navigation a,
#site-navigation a,
.site-header .menu > li > a {
    color: #ffffff !important;
}

/* Keep Schedule a Call Now button orange */
.ast-header-break-point .menu-item:last-child a,
.elementor-menu-cart__toggle,
.ast-builder-layout-element .ast-builder-menu .menu > li.menu-item-has-children > a,
.ast-masthead-custom-menu-items a.ast-button {
    color: #ffffff !important;
}

/* Header bottom border */
.site-header {
    border-bottom: 1px solid #1a1a1a !important;
}

/* ── LIGHTER SERVICE TEXT ── */
/* Service card headings - reduce weight */
.elementor-widget-heading .elementor-heading-title {
    font-weight: 600 !important;
}
/* Service card body text */
.elementor-widget-text-editor p {
    font-weight: 400 !important;
}
/* Specific to services page cards */
.is-page-4325 .elementor-heading-title,
body.page-id-4325 .elementor-heading-title {
    font-weight: 600 !important;
    letter-spacing: -0.3px;
}

/* ── The "02" number from emoji replacement ── hide it, the photo card grid replaces it ── */
body.page-id-4325 .elementor-widget-text-editor p:only-child {
    display: none !important;
}

</style>
<?php }, 999);

/* ── Move stats bar: remove from top, re-add at bottom of services ── */
add_filter('the_content', function ($content) {
    if (!is_page(4325)) return $content;

    // The stats bar HTML starts with this — strip it from top if it got injected there
    $stats_start = '<div style="background:#111;padding:52px 40px;border-top:3px solid #e4522b';
    if (strpos($content, $stats_start) !== false) {
        // Find and remove from current position, re-append at end
        $start = strpos($content, $stats_start);
        // Find end of this div
        $end_marker = '</div>' . "\n";
        // Simple approach: rebuild without the stats div at top, add at bottom
        // Extract the stats div
        $close = '</div>';
        // Count divs to find the matching close - just remove from start to first section
        $section_pos = strpos($content, '<section', $start + 10);
        if ($section_pos !== false) {
            $stats_html = substr($content, $start, $section_pos - $start);
            $content_without_top_stats = substr($content, 0, $start) . substr($content, $section_pos);
            $content = $content_without_top_stats . $stats_html;
        }
    }

    return $content;
}, 99);

/* ── Fix header on activation via Astra settings ── */
register_activation_hook(__FILE__, 'bgbp_run');
add_action('admin_init', function () {
    if (get_option('bgbp_done') !== '1') bgbp_run();
});

function bgbp_run() {
    // Try to set header background via Astra settings
    $astra = get_option('astra-settings', []);
    if (is_array($astra)) {
        $astra['header-main-bg-color']         = '#0d0d0d';
        $astra['header-main-bg-color-desktop']  = '#0d0d0d';
        $astra['above-header-bg-color']         = '#0d0d0d';
        $astra['below-header-bg-color']         = '#0d0d0d';
        $astra['header-bg-color']               = '#0d0d0d';
        update_option('astra-settings', $astra);
    }

    // Also try theme_mods
    $mods = get_option('theme_mods_astra', []);
    if (is_array($mods)) {
        $mods['header-bg-color']          = '#0d0d0d';
        $mods['above-header-bg-color']    = '#0d0d0d';
        $mods['header-main-bg-color']     = '#0d0d0d';
        update_option('theme_mods_astra', $mods);
    }

    // Clear caches
    if (class_exists('\Elementor\Plugin')) \Elementor\Plugin::$instance->files_manager->clear_cache();
    foreach ([WP_CONTENT_DIR.'/cache/swift-performance/', WP_CONTENT_DIR.'/cache/swift-performance-lite/'] as $dir) {
        if (is_dir($dir)) { $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST); foreach ($it as $f) { $f->isDir() ? @rmdir($f) : @unlink($f); } }
    }
    if (class_exists('Swift_Performance')) do_action('swift_performance_clear_all_cache');
    wp_cache_flush();

    update_option('bgbp_done', '1');
}

add_action('admin_notices', function () {
    if (get_option('bgbp_done') !== '1') return;
    ?>
    <div class="notice notice-success is-dismissible" style="padding:12px 16px;">
        <strong>✅ Header black + Services patch applied.</strong>
        <a href="<?php echo home_url('/services/'); ?>" target="_blank" class="button button-primary" style="margin-left:12px;">View Services →</a>
        <a href="<?php echo home_url('/'); ?>" target="_blank" class="button" style="margin-left:8px;">Check Header →</a>
    </div>
    <?php
});
