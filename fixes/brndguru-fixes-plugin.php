<?php
/**
 * Plugin Name: BrndGuru Site Fixes
 * Description: One-time fixes for navigation, CSS, and meta issues. DELETE after running.
 * Version: 1.0
 * Author: BrndGuru Dev
 *
 * USAGE:
 *   1. Upload this file to /wp-content/plugins/brndguru-fixes/brndguru-fixes-plugin.php
 *   2. Activate the plugin in WordPress Admin → Plugins
 *   3. Visit: /wp-admin/?brndguru_run_fixes=1&brndguru_nonce=<nonce>
 *      (The nonce is printed on the plugin row in the Plugins list)
 *   4. Check the output for success/errors
 *   5. DEACTIVATE and DELETE this plugin immediately after use
 */

if (!defined('ABSPATH')) exit;

// Show nonce in plugin row so you can use it
add_filter('plugin_row_meta', function($links, $file) {
    if (strpos($file, 'brndguru-fixes') !== false) {
        $nonce = wp_create_nonce('brndguru_fixes');
        $links[] = '<strong>Fix URL nonce: ' . $nonce . '</strong>';
        $links[] = '<a href="' . admin_url('?brndguru_run_fixes=1&brndguru_nonce=' . $nonce) . '">RUN FIXES NOW</a>';
    }
    return $links;
}, 10, 2);

// Run fixes when triggered
add_action('admin_init', function() {
    if (!isset($_GET['brndguru_run_fixes'])) return;
    if (!current_user_can('manage_options')) wp_die('Permission denied.');
    if (!wp_verify_nonce($_GET['brndguru_nonce'] ?? '', 'brndguru_fixes')) wp_die('Invalid nonce.');

    echo '<pre style="font-family:monospace;padding:20px;background:#0d1117;color:#58d68d;font-size:13px;">';
    echo "BrndGuru Site Fixes — Running...\n";
    echo str_repeat('=', 60) . "\n\n";

    brndguru_fix1_services_nav();
    brndguru_fix8_about_meta();
    brndguru_fix9_award_links();
    brndguru_fix10_active_nav_css();
    brndguru_fix11_remove_get_quote_nav();
    brndguru_flush_caches();

    echo "\n" . str_repeat('=', 60) . "\n";
    echo "ALL DONE. Check output above for any warnings.\n";
    echo "IMPORTANT: Deactivate and delete this plugin now!\n";
    echo '</pre>';
    exit;
});

// -----------------------------------------------------------------
// FIX 1 — Services nav item: remove "#" href
// -----------------------------------------------------------------
function brndguru_fix1_services_nav() {
    echo "FIX 1: Services nav item href...\n";

    $menus = wp_get_nav_menus();
    $fixed = false;

    foreach ($menus as $menu) {
        $items = wp_get_nav_menu_items($menu->term_id);
        if (!$items) continue;

        foreach ($items as $item) {
            if (strtolower($item->title) === 'services' && $item->url === '#') {
                // Ensure /services/ page exists
                $services_page = get_page_by_path('services');
                $services_url = '/services/';
                if (!$services_page) {
                    $page_id = wp_insert_post([
                        'post_title'   => 'Services',
                        'post_name'    => 'services',
                        'post_type'    => 'page',
                        'post_status'  => 'publish',
                        'post_content' => '<p>Our services</p>',
                    ]);
                    if (!is_wp_error($page_id)) {
                        $services_url = get_permalink($page_id);
                        echo "  Created /services/ page (ID: $page_id)\n";
                    }
                }

                // Update nav item URL
                wp_update_nav_menu_item($menu->term_id, $item->ID, [
                    'menu-item-url'    => $services_url,
                    'menu-item-status' => 'publish',
                ]);
                echo "  Fixed: '{$item->title}' in menu '{$menu->name}' → $services_url\n";
                $fixed = true;
            }
        }
    }

    if (!$fixed) {
        // Broader search — find any item with url="#" and title containing "service"
        foreach ($menus as $menu) {
            $items = wp_get_nav_menu_items($menu->term_id);
            if (!$items) continue;
            foreach ($items as $item) {
                if ($item->url === '#') {
                    echo "  NOTICE: Found nav item with url='#': '{$item->title}' (ID: {$item->ID}) in '{$menu->name}'\n";
                }
            }
        }
        echo "  WARNING: Could not find Services item with url='#'. Check notices above.\n";
    }
    echo "\n";
}

// -----------------------------------------------------------------
// FIX 8 — About page meta description
// -----------------------------------------------------------------
function brndguru_fix8_about_meta() {
    echo "FIX 8: About page meta description...\n";

    $about = get_page_by_path('about');
    if (!$about) {
        echo "  ERROR: Cannot find About page by slug 'about'.\n\n";
        return;
    }
    $id = $about->ID;
    echo "  About page ID: $id\n";

    $new_meta = "Brnd Guru is a B2B digital marketing agency helping ambitious brands scale with strategy, design, and performance marketing. 200+ brands, 30+ industries.";

    // Yoast SEO
    if (defined('WPSEO_VERSION') || class_exists('WPSEO_Frontend')) {
        update_post_meta($id, '_yoast_wpseo_metadesc', $new_meta);
        echo "  Updated Yoast SEO meta description (" . strlen($new_meta) . " chars)\n";
    }

    // RankMath SEO
    if (class_exists('RankMath') || defined('RANK_MATH_VERSION')) {
        update_post_meta($id, 'rank_math_description', $new_meta);
        echo "  Updated RankMath meta description\n";
    }

    // Set both regardless (harmless if plugin not active)
    update_post_meta($id, '_yoast_wpseo_metadesc', $new_meta);
    update_post_meta($id, 'rank_math_description', $new_meta);
    echo "  Set both Yoast + RankMath keys as fallback\n";
    echo "\n";
}

// -----------------------------------------------------------------
// FIX 9 — About page: remove UTM params from award links
// -----------------------------------------------------------------
function brndguru_fix9_award_links() {
    echo "FIX 9: About page award link UTM cleanup...\n";

    $about = get_page_by_path('about');
    if (!$about) {
        echo "  ERROR: Cannot find About page.\n\n";
        return;
    }
    $id = $about->ID;

    // Get Elementor data
    $data = get_post_meta($id, '_elementor_data', true);
    if (empty($data)) {
        echo "  No Elementor data on About page. Skipping.\n\n";
        return;
    }

    $original = $data;
    // Strip UTM params from all URLs in the JSON using regex
    $fixed = preg_replace_callback(
        '/"(https?:\/\/[^"]*\?[^"]*utm_[^"]*)"/',
        function($matches) {
            $url = $matches[1];
            $parsed = parse_url($url);
            if (isset($parsed['query'])) {
                parse_str($parsed['query'], $params);
                foreach (array_keys($params) as $key) {
                    if (strpos($key, 'utm_') === 0) unset($params[$key]);
                }
                $new_query = http_build_query($params);
                $clean = $parsed['scheme'] . '://' . $parsed['host'];
                if (isset($parsed['path'])) $clean .= $parsed['path'];
                if ($new_query) $clean .= '?' . $new_query;
                return '"' . $clean . '"';
            }
            return $matches[0];
        },
        $data
    );

    if ($fixed !== $original) {
        update_post_meta($id, '_elementor_data', $fixed);
        // Count fixes
        $count = substr_count($original, 'utm_') - substr_count($fixed, 'utm_');
        echo "  Removed ~$count UTM parameter occurrence(s) from Elementor data\n";
    } else {
        echo "  No UTM-tagged URLs found in Elementor JSON data.\n";
        echo "  If award links are in HTML text widgets, edit manually in Elementor.\n";
    }

    // Delete Elementor CSS cache for this post
    delete_post_meta($id, '_elementor_css');
    echo "\n";
}

// -----------------------------------------------------------------
// FIX 10 — Add active nav state CSS
// -----------------------------------------------------------------
function brndguru_fix10_active_nav_css() {
    echo "FIX 10: Active nav state CSS...\n";

    $css = '
/* Active nav item highlight */
.current-menu-item > a,
.current-menu-ancestor > a,
.current-menu-parent > a {
    font-weight: 600 !important;
    border-bottom: 2px solid currentColor;
}
.elementor-nav-menu .elementor-item.elementor-item-active,
.elementor-nav-menu .elementor-item.highlighted {
    font-weight: 600 !important;
    border-bottom: 2px solid currentColor;
}';

    // Get existing Additional CSS
    $theme = get_option('stylesheet');
    $existing = wp_get_custom_css($theme);

    if (strpos($existing, 'current-menu-item') !== false) {
        echo "  Active nav CSS already present. Skipping.\n\n";
        return;
    }

    wp_update_custom_css_post($existing . "\n" . $css, ['stylesheet' => $theme]);
    echo "  Added active nav CSS to Additional CSS (Customizer)\n\n";
}

// -----------------------------------------------------------------
// FIX 11 — Remove "Get a Quote" duplicate nav item
// -----------------------------------------------------------------
function brndguru_fix11_remove_get_quote_nav() {
    echo "FIX 11: Remove 'Get a Quote' nav item...\n";

    $menus = wp_get_nav_menus();
    $found = false;

    foreach ($menus as $menu) {
        $items = wp_get_nav_menu_items($menu->term_id);
        if (!$items) continue;
        foreach ($items as $item) {
            $title_lower = strtolower($item->title);
            if (strpos($title_lower, 'get a quote') !== false || $title_lower === 'quote') {
                wp_delete_post($item->ID, true);
                echo "  Removed: '{$item->title}' (ID: {$item->ID}) from menu '{$menu->name}'\n";
                $found = true;
            }
        }
    }

    if (!$found) {
        echo "  No 'Get a Quote' nav item found.\n";
    }
    echo "\n";
}

// -----------------------------------------------------------------
// Flush caches
// -----------------------------------------------------------------
function brndguru_flush_caches() {
    echo "FLUSHING CACHES...\n";

    // WP core
    wp_cache_flush();
    echo "  Object cache flushed\n";

    // Transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    echo "  Transients cleared\n";

    // Elementor CSS
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        echo "  Elementor CSS cache cleared\n";
    }

    // WP Rocket
    if (function_exists('rocket_clean_domain')) {
        rocket_clean_domain();
        echo "  WP Rocket cache cleared\n";
    }

    // LiteSpeed Cache
    if (class_exists('LiteSpeed_Cache_API')) {
        LiteSpeed_Cache_API::purge_all();
        echo "  LiteSpeed cache cleared\n";
    }

    // W3 Total Cache
    if (function_exists('w3tc_flush_all')) {
        w3tc_flush_all();
        echo "  W3TC cache cleared\n";
    }

    echo "\n";
}
