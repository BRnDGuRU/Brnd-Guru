<?php
/**
 * Plugin Name: BrndGuru Emergency Fix v9
 * Description: Remove bad CSS from Fix 4/6, fix services page 404. DELETE after running.
 * Version: 9.0
 */

if (!defined('ABSPATH')) exit;

add_filter('plugin_row_meta', function($links, $file) {
    if (strpos($file, 'brndguru-fixes') !== false) {
        $nonce = wp_create_nonce('bg9_run');
        $links[] = '<a href="' . admin_url('?bg9_run=1&bg9_nonce=' . $nonce) . '" style="font-weight:700;color:#e74c3c;font-size:14px;">▶ FIX DISTORTION + 404 NOW</a>';
    }
    return $links;
}, 10, 2);

add_action('admin_init', function() {
    if (!isset($_GET['bg9_run'])) return;
    if (!current_user_can('manage_options')) wp_die('Permission denied.');
    if (!wp_verify_nonce($_GET['bg9_nonce'] ?? '', 'bg9_run')) wp_die('Invalid nonce.');

    echo '<pre style="font-family:monospace;padding:20px;background:#0d1117;color:#58d68d;font-size:13px;line-height:1.7;">';
    echo "BrndGuru Emergency Fix v9\n" . str_repeat('=', 60) . "\n\n";

    bg9_remove_bad_css();
    bg9_fix_services_404();
    bg9_flush();

    echo str_repeat('=', 60) . "\n";
    echo '<span style="color:#fff">DONE. Deactivate + delete this plugin now.</span>' . "\n";
    echo '</pre>';
    exit;
});

function bg9_ok($m)   { echo '  <span style="color:#2ecc71">✓ ' . esc_html($m) . '</span>' . "\n"; }
function bg9_err($m)  { echo '  <span style="color:#e74c3c">✗ ' . esc_html($m) . '</span>' . "\n"; }
function bg9_info($m) { echo '  ' . esc_html($m) . "\n"; }
function bg9_head($m) { echo '<span style="color:#f1c40f">── ' . esc_html($m) . ' ──</span>' . "\n"; }

// ─────────────────────────────────────────────────────────────
// REMOVE bad CSS injected by Fix 4 and Fix 6
// ─────────────────────────────────────────────────────────────
function bg9_remove_bad_css() {
    bg9_head('STEP 1: Remove bad CSS from Fix 4 + Fix 6');

    // Blocks we want to strip — identified by their comment labels
    $labels_to_remove = ['fix4-header-cta', 'fix6-client-logos', 'Fix 4:', 'Fix 6:', 'Fix6:'];

    // ── Strip from WP Additional CSS (wp_get_custom_css) ────────────────
    $additional_css = wp_get_custom_css();
    $original_len   = strlen($additional_css);
    $cleaned_css    = bg9_strip_css_blocks($additional_css, $labels_to_remove);

    if ($cleaned_css !== $additional_css) {
        wp_update_custom_css_post($cleaned_css);
        bg9_ok('Removed bad CSS from WP Additional CSS (was ' . $original_len . ' chars, now ' . strlen($cleaned_css) . ')');
    } else {
        bg9_info('WP Additional CSS: no bad blocks found (length: ' . $original_len . ')');
    }

    // ── Strip from elementor_custom_css option ───────────────────────────
    $el_css      = get_option('elementor_custom_css', '');
    $el_original = strlen($el_css);
    $el_cleaned  = bg9_strip_css_blocks($el_css, $labels_to_remove);

    if ($el_cleaned !== $el_css) {
        update_option('elementor_custom_css', $el_cleaned);
        bg9_ok('Removed bad CSS from Elementor custom CSS (was ' . $el_original . ' chars, now ' . strlen($el_cleaned) . ')');
    } else {
        bg9_info('Elementor custom CSS: no bad blocks found (length: ' . $el_original . ')');
    }

    // ── Remove bad Elementor custom JS ──────────────────────────────────
    $el_js = get_option('elementor_custom_js', '');
    if (strpos($el_js, 'Fix6') !== false || strpos($el_js, 'broken images') !== false) {
        // Remove the JS snippet we added
        $el_js_clean = preg_replace('/\n?\/\/ Fix6:.*?(?=\n\/\/|\z)/s', '', $el_js);
        // Broader: remove everything after our marker
        $el_js_clean = preg_replace('/\n?\/\/ Fix6: hide broken images[\s\S]*?(?=\n\/\/[^\n]|\z)/', '', $el_js);
        update_option('elementor_custom_js', trim($el_js_clean));
        bg9_ok('Removed bad JS from Elementor custom JS');
    } else {
        bg9_info('Elementor custom JS: no bad snippet found');
    }

    // ── Show what remains ───────────────────────────────────────────────
    $remaining = get_option('elementor_custom_css', '');
    if (trim($remaining)) {
        bg9_info('Remaining Elementor CSS:');
        bg9_info(trim($remaining));
    }
    echo "\n";
}

function bg9_strip_css_blocks(string $css, array $labels): string {
    foreach ($labels as $label) {
        // Remove entire /* label */ comment and everything until the next /* or end
        $css = preg_replace(
            '/\/\*[^*]*' . preg_quote($label, '/') . '[^*]*\*\/[\s\S]*?(?=\/\*|$)/i',
            '',
            $css
        );
        // Also remove lines that contain the label as a comment
        $css = preg_replace('/.*' . preg_quote($label, '/') . '.*\n?/i', '', $css);
    }
    // Clean up multiple blank lines
    $css = preg_replace('/\n{3,}/', "\n\n", $css);
    return trim($css);
}

// ─────────────────────────────────────────────────────────────
// FIX services page 404
// ─────────────────────────────────────────────────────────────
function bg9_fix_services_404() {
    bg9_head('STEP 2: Diagnose + fix Services page 404');

    global $wpdb;

    // ── Find the services page ───────────────────────────────────────────
    $services_page = get_page_by_path('services');
    if (!$services_page) {
        // Search by title
        $services_page = $wpdb->get_row(
            "SELECT * FROM {$wpdb->posts}
             WHERE post_type = 'page'
             AND post_status IN ('publish','draft','private')
             AND (post_title LIKE '%service%' OR post_name LIKE '%service%')
             ORDER BY post_status='publish' DESC, ID ASC
             LIMIT 1"
        );
    }

    if ($services_page) {
        bg9_info('Services page found: ID=' . $services_page->ID . ' status=' . $services_page->post_status . ' slug=' . $services_page->post_name . ' title="' . $services_page->post_title . '"');

        // If draft or private, publish it
        if ($services_page->post_status !== 'publish') {
            wp_update_post(['ID' => $services_page->ID, 'post_status' => 'publish']);
            bg9_ok('Published services page (was: ' . $services_page->post_status . ')');
        } else {
            bg9_ok('Services page is published');
        }

        // Ensure slug is correct
        if ($services_page->post_name !== 'services') {
            wp_update_post(['ID' => $services_page->ID, 'post_name' => 'services']);
            bg9_ok('Fixed slug to "services" (was: "' . $services_page->post_name . '")');
        }

        bg9_info('Expected URL: ' . get_permalink($services_page->ID));
    } else {
        bg9_info('No services page found. Creating one...');

        // Create a basic services page
        $page_id = wp_insert_post([
            'post_title'   => 'Services',
            'post_name'    => 'services',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '<!-- wp:paragraph --><p>Our services page is coming soon.</p><!-- /wp:paragraph -->',
        ]);

        if ($page_id && !is_wp_error($page_id)) {
            bg9_ok('Created new Services page (ID: ' . $page_id . ')');
            bg9_info('URL: ' . get_permalink($page_id));
        } else {
            bg9_err('Failed to create services page');
        }
    }

    // ── Flush rewrite rules to fix permalink 404s ────────────────────────
    flush_rewrite_rules(true);
    bg9_ok('Flushed rewrite rules (permalink structure rebuilt)');

    // ── Dump all published pages for reference ───────────────────────────
    bg9_info('');
    bg9_info('All published pages:');
    $pages = get_pages(['post_status' => 'publish', 'sort_column' => 'post_title']);
    foreach ($pages as $p) {
        bg9_info('  ID:' . $p->ID . '  slug:/' . $p->post_name . '  "' . $p->post_title . '"');
    }

    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FLUSH
// ─────────────────────────────────────────────────────────────
function bg9_flush() {
    bg9_head('FLUSHING CACHES');
    wp_cache_flush(); bg9_ok('Object cache');
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    bg9_ok('Transients');
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        bg9_ok('Elementor CSS cache');
    }
    do_action('swift_performance_after_clean_cache');
    if (function_exists('rocket_clean_domain'))  { rocket_clean_domain(); bg9_ok('WP Rocket'); }
    if (class_exists('LiteSpeed_Cache_API'))     { LiteSpeed_Cache_API::purge_all(); bg9_ok('LiteSpeed'); }
    bg9_ok('Done');
    echo "\n";
}
