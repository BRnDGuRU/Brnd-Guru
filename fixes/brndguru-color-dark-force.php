<?php
/**
 * Plugin Name: BrndGuru — Force Dark All Pages
 * Description: Replaces all light/peach Elementor section backgrounds with dark. Fixes color mismatches.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgdf_run');
add_action('admin_init', function () {
    if (get_option('bgdf_done') !== '1') bgdf_run();
});

function bgdf_run() {
    global $wpdb;
    $log = [];

    // All light/peach/cream/white background colors found in Elementor data
    $color_map = [
        // Peach/cream/warm light colors → dark
        '#fef0e7' => '#0d0d0d',
        '#fce8d5' => '#0d0d0d',
        '#fdf1e8' => '#0d0d0d',
        '#fef3ec' => '#0d0d0d',
        '#fde8d4' => '#0d0d0d',
        '#fff5ee' => '#0d0d0d',
        '#fef4ed' => '#0d0d0d',
        '#fdf0e6' => '#0d0d0d',
        '#fce9d6' => '#0d0d0d',
        '#fde9d5' => '#0d0d0d',
        'fef0e7'  => '0d0d0d',
        'fce8d5'  => '0d0d0d',
        // White/near-white
        '#ffffff' => '#0d0d0d',
        '#fff'    => '#0d0d0d',
        '#f9f9f9' => '#0d0d0d',
        '#f8f8f8' => '#0d0d0d',
        '#f5f5f5' => '#111111',
        '#eeeeee' => '#111111',
        // Light gray
        '#e8e8e8' => '#141414',
        '#f0f0f0' => '#111111',
        // rgba white variants
        'rgba(255,255,255,1)'  => 'rgba(13,13,13,1)',
        'rgba(255, 255, 255, 1)' => 'rgba(13, 13, 13, 1)',
        // Old content strings to replace
        'Unforgettable, Websites, Brands &amp; Visuals for Bold Visionaries.' => 'We Build Outbound Systems That Generate Revenue.',
        'Unforgettable, Websites, Brands & Visuals for Bold Visionaries.' => 'We Build Outbound Systems That Generate Revenue.',
        'not your typical design agency' => 'B2B growth and automation consulting agency',
        'Founded in 2014' => 'Based in London',
        'designers, developers, and strategists' => 'outbound strategists and automation engineers',
        'digital experiences should be beautiful, functional, and human-centered' => 'outbound systems that fill your pipeline',
        'How We Grow Your Business' => 'Our Growth System',
        'From the BRND GURU Blog' => 'Insights & Resources',
    ];

    $pages = [12, 1628, 4325, 23, 21, 1483, 762, 724, 766, 765, 2970];
    $total = 0;

    foreach ($pages as $pid) {
        // Fix _elementor_data
        $raw = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key='_elementor_data' LIMIT 1", $pid
        ));
        if ($raw) {
            $new = $raw;
            foreach ($color_map as $old => $new_val) {
                $new = str_ireplace($old, $new_val, $new);
            }
            if ($new !== $raw) {
                $wpdb->update($wpdb->postmeta, ['meta_value' => $new],
                    ['post_id' => $pid, 'meta_key' => '_elementor_data']);
                $total++;
                $log[] = "✓ Page {$pid}: Elementor data updated";
            }
        }

        // Fix post_content (for block editor pages like Careers/Contact)
        $pc = $wpdb->get_var($wpdb->prepare(
            "SELECT post_content FROM {$wpdb->posts} WHERE ID=%d", $pid
        ));
        if ($pc) {
            $new_pc = $pc;
            foreach ($color_map as $old => $new_val) {
                $new_pc = str_ireplace($old, $new_val, $new_pc);
            }
            if ($new_pc !== $pc) {
                $wpdb->update($wpdb->posts,
                    ['post_content' => $new_pc, 'post_modified' => current_time('mysql')],
                    ['ID' => $pid]
                );
            }
        }

        // Delete cached Elementor CSS for this page
        delete_post_meta($pid, '_elementor_css');
        clean_post_cache($pid);
    }

    $log[] = "✓ Updated {$total} pages";

    // Clear all caches
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        $log[] = "✓ Elementor CSS cache cleared";
    }
    foreach ([WP_CONTENT_DIR.'/cache/swift-performance/', WP_CONTENT_DIR.'/cache/swift-performance-lite/'] as $dir) {
        if (is_dir($dir)) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($it as $f) { $f->isDir() ? @rmdir($f) : @unlink($f); }
        }
    }
    if (class_exists('Swift_Performance')) do_action('swift_performance_clear_all_cache');
    wp_cache_flush();
    if (function_exists('opcache_reset')) opcache_reset();

    update_option('bgdf_log', $log);
    update_option('bgdf_done', '1');
}

/* ── CSS loaded in footer AFTER Elementor's stylesheets — maximum override power ── */
add_action('wp_footer', function () { ?>
<style id="bg-dark-force-late">
/* Force dark on ALL Elementor sections site-wide — loaded after Elementor CSS */
.elementor-section,
.elementor-top-section,
.e-container,
.elementor-inner-section,
.elementor-section-wrap > .elementor-section {
    background-color: #0d0d0d !important;
    background-image: none !important; /* remove any light gradient */
}

/* Restore specific dark sections that use darker shades */
.elementor-section[data-settings*='"background_color":"#111"'],
.elementor-section[data-settings*='"background_color":"#141414"'] {
    background-color: #111111 !important;
}

/* White text everywhere on dark bg */
.elementor-widget-heading .elementor-heading-title {
    color: #ffffff !important;
}
.elementor-widget-text-editor,
.elementor-widget-text-editor p {
    color: #aaaaaa !important;
}

/* Keep orange accents */
.elementor-widget-heading .elementor-heading-title span[style*="color"],
h1 span, h2 span {
    color: #e4522b !important;
}

/* Buttons stay orange */
.elementor-button {
    background-color: #e4522b !important;
    color: #ffffff !important;
    font-weight: 700 !important;
}
.elementor-button:hover {
    background-color: #c03b1e !important;
    opacity: 1 !important;
}

/* Fix testimonial / review cards that were peach */
.elementor-testimonial,
.elementor-testimonial-wrapper,
[class*="testimonial"],
[class*="review"] {
    background: #141414 !important;
    border: 1px solid #222 !important;
    border-radius: 12px !important;
    color: #ddd !important;
}

/* Kill any remaining light-colored dividers or separators */
.elementor-divider-separator {
    border-color: #222 !important;
}
hr { border-color: #222 !important; }

/* Make sure injected sections (stats, photo strips, etc.) show above Elementor content */
.bg-blog-hero,
.bg-blog-grid,
.bg-post-card { position: relative; z-index: 1; }
</style>
<?php }, 999);

add_action('admin_notices', function () {
    if (get_option('bgdf_done') !== '1') return;
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px 16px;">
        <h3 style="margin:0 0 8px;">✅ Dark Theme Forced Across All Pages</h3>
        <ul style="margin:0 0 10px;padding-left:20px;font-size:12px;max-height:200px;overflow-y:auto;">
            <?php foreach (get_option('bgdf_log', []) as $l): ?><li><?php echo esc_html($l); ?></li><?php endforeach; ?>
        </ul>
        <p>
            <a href="<?php echo home_url('/'); ?>" target="_blank" class="button button-primary">Home →</a>
            <a href="<?php echo home_url('/about-us/'); ?>" target="_blank" class="button">About →</a>
            <a href="<?php echo home_url('/services/'); ?>" target="_blank" class="button">Services →</a>
        </p>
    </div>
    <?php
});
