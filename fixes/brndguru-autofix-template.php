<?php
/**
 * Plugin Name: BrndGuru — Auto Fix Header NOW
 * Description: RUNS IMMEDIATELY ON ACTIVATION — forces elementor_full_width template on all pages so Astra header reappears
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

// Run immediately on activation
register_activation_hook(__FILE__, 'bgafix_run');

// Also run on admin_init in case activation hook missed it
add_action('admin_init', function() {
    if (get_option('bgafix_done') !== '1') {
        bgafix_run();
    }
});

function bgafix_run() {
    global $wpdb;

    // Method 1: Direct DB update — change ALL elementor_canvas templates to elementor_full_width
    $updated = $wpdb->query(
        "UPDATE {$wpdb->postmeta}
         SET meta_value = 'elementor_full_width'
         WHERE meta_key = '_wp_page_template'
         AND meta_value = 'elementor_canvas'"
    );

    // Method 2: Also explicitly set key page IDs regardless
    $page_ids = [12, 1628, 4325, 23, 762, 724, 766, 765, 2970, 21, 1483];
    foreach ($page_ids as $id) {
        $current = get_post_meta($id, '_wp_page_template', true);
        // If blank, canvas, or anything that isn't full_width — force it
        if ($current !== 'elementor_full_width') {
            update_post_meta($id, '_wp_page_template', 'elementor_full_width');
        }
    }

    // Method 3: Also check Astra page-level header settings and unset them
    foreach ($page_ids as $id) {
        // Astra stores per-page header/footer visibility here
        delete_post_meta($id, 'ast-main-header-display');
        delete_post_meta($id, 'ast-hfb-above-header-display');
        delete_post_meta($id, 'ast-hfb-below-header-display');
        delete_post_meta($id, 'footer-sml-layout');
        delete_post_meta($id, 'ast-footer-layout');
        // Elementor per-page header hide setting
        delete_post_meta($id, '_elementor_page_settings');
    }

    // Clear ALL caches
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
    if (function_exists('astra_delete_option')) {
        astra_delete_option('custom-css');
    }
    if (class_exists('Swift_Performance')) {
        do_action('swift_performance_clear_all_cache');
    }
    // WP total cache
    if (function_exists('w3tc_flush_all')) { w3tc_flush_all(); }
    if (function_exists('wp_cache_flush')) { wp_cache_flush(); }

    flush_rewrite_rules(true);

    update_option('bgafix_done', '1');

    // Log result
    error_log("BG AutoFix: Updated {$updated} page template(s) from elementor_canvas to elementor_full_width");
}

// Show result on admin pages
add_action('admin_notices', function() {
    if (get_option('bgafix_done') === '1') {
        ?>
        <div class="notice notice-success">
            <p>
                <strong>✅ BrndGuru Header Fix Applied!</strong>
                All pages switched from <code>elementor_canvas</code> → <code>elementor_full_width</code>.
                Astra header should now be visible.
                <a href="<?php echo home_url('/'); ?>" target="_blank" style="font-weight:bold;">👁 View Site →</a>
                &nbsp;|&nbsp;
                <a href="<?php echo admin_url('plugins.php'); ?>">Deactivate this plugin when done</a>
            </p>
        </div>
        <?php
    }
});
