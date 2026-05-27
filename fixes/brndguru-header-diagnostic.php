<?php
/**
 * Plugin Name: BrndGuru Header Diagnostic
 * Description: Diagnoses why header is missing + attempts full fix. Shows debug info in admin.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

/* ── Force show EVERYTHING including above-header row ── */
add_action('wp_head', function () { ?>
<style id="bg-header-force" type="text/css">
/* Force every possible Astra header element visible */
#masthead,
.site-header,
.ast-header-break-point,
.site-primary-header-wrap,
.ast-primary-header-area,
.site-header-wrap,
.hfb-primary-header,
.ast-main-header-wrap,
.site-above-header-wrap,
.hfb-above-header,
.site-below-header-wrap,
.hfb-below-header,
.elementor-location-header,
header.site-header,
.ast-site-header-wrap,
[class*="ast-header"],
[id*="masthead"] {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    height: auto !important;
    max-height: none !important;
    overflow: visible !important;
    position: relative !important;
    transform: none !important;
    clip: auto !important;
    clip-path: none !important;
}
</style>
<?php }, 999); /* Priority 999 — runs AFTER any hiding CSS */

/* ── Diagnostic + fix on admin pages ── */
add_action('admin_init', function () {
    global $wpdb;

    $info = [];

    // 1. Check page templates for all main pages
    $page_ids = [12, 1628, 4325, 23, 21, 1483, 762, 724, 766, 765, 2970];
    $canvas_pages = [];
    foreach ($page_ids as $id) {
        $tpl = get_post_meta($id, '_wp_page_template', true);
        if ($tpl === 'elementor_canvas') {
            $canvas_pages[] = $id;
            // FIX: change to full width
            update_post_meta($id, '_wp_page_template', 'elementor_full_width');
        }
        $info[] = "Page {$id}: template={$tpl}";
    }
    if ($canvas_pages) {
        $info[] = "FIXED canvas->full_width on pages: " . implode(', ', $canvas_pages);
    }

    // 2. Check for any remaining ast-main-header-display rows
    $bad_meta = $wpdb->get_results(
        "SELECT post_id, meta_value FROM {$wpdb->postmeta}
         WHERE meta_key = 'ast-main-header-display'
         LIMIT 20"
    );
    if ($bad_meta) {
        foreach ($bad_meta as $row) {
            $info[] = "BAD META: post {$row->post_id} ast-main-header-display={$row->meta_value}";
        }
        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'ast-main-header-display'");
        $info[] = "FIXED: deleted all ast-main-header-display rows";
    } else {
        $info[] = "GOOD: No ast-main-header-display rows found in DB";
    }

    // 3. Check Astra settings
    $astra = get_option('astra-settings', []);
    $info[] = "Astra header-layout: " . ($astra['header-layouts'] ?? 'not set');
    $info[] = "Astra header-main-layout-width: " . ($astra['header-main-layout-width'] ?? 'not set');
    if (isset($astra['ast-main-header-display'])) {
        $info[] = "BAD: astra-settings has ast-main-header-display=" . $astra['ast-main-header-display'];
        unset($astra['ast-main-header-display']);
        update_option('astra-settings', $astra);
        $info[] = "FIXED: removed from astra-settings";
    }

    // 4. Check which plugins are active (look for anything that might hide header)
    $active = get_option('active_plugins', []);
    foreach ($active as $plugin) {
        if (stripos($plugin, 'kill') !== false || stripos($plugin, 'strip') !== false || stripos($plugin, 'brnd') !== false) {
            $info[] = "Active plugin: {$plugin}";
        }
    }

    // 5. Clear caches
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
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
            foreach ($it as $f) { $f->isDir() ? @rmdir($f) : @unlink($f); }
        }
    }
    if (class_exists('Swift_Performance')) do_action('swift_performance_clear_all_cache');
    wp_cache_flush();

    update_option('bgdiag_info', $info);
    update_option('bgdiag_time', current_time('mysql'));
});

add_action('admin_notices', function () {
    $info = get_option('bgdiag_info', []);
    $time = get_option('bgdiag_time', '');
    ?>
    <div class="notice notice-info" style="padding:16px;">
        <h2 style="margin:0 0 10px;">🔍 Header Diagnostic — <?php echo esc_html($time); ?></h2>
        <div style="background:#1e1e1e;color:#a8ff78;font-family:monospace;font-size:12px;padding:12px;border-radius:4px;max-height:300px;overflow-y:auto;margin-bottom:12px;">
            <?php foreach ($info as $line): ?>
                <div><?php echo esc_html($line); ?></div>
            <?php endforeach; ?>
        </div>
        <p style="margin:0 0 10px;font-size:14px;color:#d63638;">
            <strong>IMPORTANT:</strong> Also go to <a href="<?php echo admin_url('plugins.php'); ?>">Plugins</a>
            and <strong>DEACTIVATE: "BrndGuru — Kill Page Strips" (the OLD version)</strong> if it is listed there.
            That plugin has CSS that may be hiding your header.
        </p>
        <p style="margin:0;">
            <a href="<?php echo home_url('/'); ?>" target="_blank" class="button button-primary">Check Homepage (Incognito)</a>
            &nbsp;
            <a href="<?php echo admin_url('plugins.php'); ?>" class="button">Go to Plugins →</a>
        </p>
    </div>
    <?php
});
