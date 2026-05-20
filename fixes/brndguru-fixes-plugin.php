<?php
/**
 * Plugin Name: BrndGuru Site Fixes v2
 * Description: One-time fixes for all 11 site issues. DELETE after running.
 * Version: 2.0
 *
 * USAGE:
 *   1. Upload this file to /wp-content/plugins/brndguru-fixes/brndguru-fixes-plugin.php
 *   2. Activate in WordPress Admin → Plugins
 *   3. Click "RUN FIXES NOW" on the plugin row
 *   4. DEACTIVATE and DELETE immediately after
 */

if (!defined('ABSPATH')) exit;

add_filter('plugin_row_meta', function($links, $file) {
    if (strpos($file, 'brndguru-fixes') !== false) {
        $nonce = wp_create_nonce('brndguru_fixes');
        $links[] = '<strong style="color:#d63638">Nonce: ' . $nonce . '</strong>';
        $links[] = '<a href="' . admin_url('?brndguru_run_fixes=1&brndguru_nonce=' . $nonce) . '" style="font-weight:700;color:#00a32a">▶ RUN FIXES NOW</a>';
    }
    return $links;
}, 10, 2);

add_action('admin_init', function() {
    if (!isset($_GET['brndguru_run_fixes'])) return;
    if (!current_user_can('manage_options')) wp_die('Permission denied.');
    if (!wp_verify_nonce($_GET['brndguru_nonce'] ?? '', 'brndguru_fixes')) wp_die('Invalid nonce.');

    echo '<pre style="font-family:monospace;padding:20px;background:#0d1117;color:#58d68d;font-size:13px;line-height:1.6;">';
    echo "BrndGuru Site Fixes v2 — Running...\n";
    echo str_repeat('=', 60) . "\n\n";

    bg_fix1_services_nav();
    bg_fix2_about_learn_more();
    bg_fix3_footer_facebook();
    bg_fix4_header_cta_responsive();
    bg_fix5_pricing_buttons();
    bg_fix8_about_meta();
    bg_fix9_award_links();
    bg_fix10_active_nav_css();
    bg_fix11_remove_get_quote_nav();
    bg_flush_caches();

    echo str_repeat('=', 60) . "\n";
    echo '<span style="color:#fff">ALL DONE. Review above, then deactivate + delete this plugin.</span>' . "\n";
    echo '</pre>';
    exit;
});

// ─────────────────────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────────────────────

function bg_ok($msg)   { echo '  <span style="color:#2ecc71">✓ ' . esc_html($msg) . '</span>' . "\n"; }
function bg_err($msg)  { echo '  <span style="color:#e74c3c">✗ ' . esc_html($msg) . '</span>' . "\n"; }
function bg_info($msg) { echo '  ' . esc_html($msg) . "\n"; }
function bg_head($msg) { echo '<span style="color:#f1c40f">── ' . esc_html($msg) . ' ──</span>' . "\n"; }

/** Get Elementor JSON for a post, decoded as array */
function bg_get_el($post_id) {
    $raw = get_post_meta($post_id, '_elementor_data', true);
    if (!$raw) return null;
    $data = json_decode($raw, true);
    return (json_last_error() === JSON_ERROR_NONE) ? $data : null;
}

/** Save Elementor JSON back to post meta and clear cache */
function bg_set_el($post_id, array $data) {
    $json = wp_slash(wp_json_encode($data, JSON_UNESCAPED_UNICODE));
    update_post_meta($post_id, '_elementor_data', $json);
    delete_post_meta($post_id, '_elementor_css');
}

/** Recursively walk Elementor nodes, calling $fn on each */
function bg_walk(array &$nodes, callable $fn) {
    foreach ($nodes as &$node) {
        $fn($node);
        if (!empty($node['elements'])) bg_walk($node['elements'], $fn);
    }
}

/** Find Elementor library template by type (header/footer/section) */
function bg_find_template($type) {
    $posts = get_posts([
        'post_type'      => 'elementor_library',
        'post_status'    => 'publish',
        'posts_per_page' => 10,
        'meta_query'     => [[
            'key'     => '_elementor_template_type',
            'value'   => $type,
            'compare' => '=',
        ]],
    ]);
    if ($posts) return $posts[0];
    // Fallback: search by title
    $all = get_posts(['post_type' => 'elementor_library', 'post_status' => 'publish', 'posts_per_page' => 50]);
    foreach ($all as $p) {
        if (stripos($p->post_title, $type) !== false) return $p;
    }
    return null;
}

// ─────────────────────────────────────────────────────────────
// FIX 1 — Services nav href="#" → /services/
// ─────────────────────────────────────────────────────────────
function bg_fix1_services_nav() {
    bg_head('FIX 1: Services nav href');
    $fixed = false;
    foreach (wp_get_nav_menus() as $menu) {
        foreach ((wp_get_nav_menu_items($menu->term_id) ?: []) as $item) {
            if (stripos($item->title, 'service') !== false && $item->url === '#') {
                if (!get_page_by_path('services')) {
                    $pid = wp_insert_post(['post_title'=>'Services','post_name'=>'services','post_type'=>'page','post_status'=>'publish','post_content'=>'<!-- wp:paragraph --><p>Our Services</p><!-- /wp:paragraph -->']);
                    bg_info('Created /services/ page (ID: ' . $pid . ')');
                }
                wp_update_nav_menu_item($menu->term_id, $item->ID, ['menu-item-url' => '/services/', 'menu-item-status' => 'publish']);
                bg_ok('Fixed "' . $item->title . '" in "' . $menu->name . '" → /services/');
                $fixed = true;
            }
        }
    }
    if (!$fixed) bg_info('Already fixed or not found (Fix 1 ran earlier via REST API).');
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FIX 2 — About page: "Learn More" button → #how-we-work
// ─────────────────────────────────────────────────────────────
function bg_fix2_about_learn_more() {
    bg_head('FIX 2: About "Learn More" button');
    $about = get_page_by_path('about');
    if (!$about) { bg_err('About page not found'); echo "\n"; return; }

    $data = bg_get_el($about->ID);
    if (!$data) { bg_err('No Elementor data on About page'); echo "\n"; return; }

    $btn_fixed = 0;
    $section_idx = 0;
    $section_id_set = false;

    bg_walk($data, function(&$node) use (&$btn_fixed, &$section_idx, &$section_id_set) {
        // Fix button
        if (($node['widgetType'] ?? '') === 'button') {
            $text = $node['settings']['text'] ?? '';
            $url  = $node['settings']['link']['url'] ?? '';
            if (stripos($text, 'learn more') !== false && $url === '#') {
                $node['settings']['link']['url'] = '#how-we-work';
                $btn_fixed++;
            }
        }
        // Add CSS ID to section 2 (first section after hero)
        if (in_array($node['elType'] ?? '', ['section', 'container'])) {
            $section_idx++;
            if ($section_idx === 2 && !$section_id_set && empty($node['settings']['_element_id'])) {
                $node['settings']['_element_id'] = 'how-we-work';
                $section_id_set = true;
            }
        }
    });

    bg_set_el($about->ID, $data);
    bg_ok('Button(s) fixed: ' . $btn_fixed . ' | Section ID set: ' . ($section_id_set ? 'yes' : 'already had one'));
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FIX 3 — Footer: Facebook icon URL
// ─────────────────────────────────────────────────────────────
function bg_fix3_footer_facebook() {
    bg_head('FIX 3: Footer Facebook icon URL');

    $footer = bg_find_template('footer');
    if (!$footer) {
        // List all templates for debugging
        $all = get_posts(['post_type'=>'elementor_library','post_status'=>'publish','posts_per_page'=>20]);
        $titles = array_map(fn($p) => $p->post_title . '(ID:' . $p->ID . ')', $all);
        bg_err('Footer template not found. Templates: ' . implode(', ', $titles));
        echo "\n"; return;
    }
    bg_info('Footer template: "' . $footer->post_title . '" (ID: ' . $footer->ID . ')');

    $data = bg_get_el($footer->ID);
    if (!$data) { bg_err('No Elementor data on footer template'); echo "\n"; return; }

    $fixed = 0;
    bg_walk($data, function(&$node) use (&$fixed) {
        $icons = &$node['settings']['social_icon_list'] ?? null;
        if (!$icons) return;
        foreach ($icons as &$icon) {
            $icon_val = $icon['social_icon']['value'] ?? $icon['social_icon'] ?? '';
            $is_fb = is_string($icon_val) && stripos($icon_val, 'facebook') !== false;
            $url   = $icon['link']['url'] ?? '';
            if ($is_fb && stripos($url, 'linkedin.com') !== false) {
                $icon['link']['url'] = 'https://www.facebook.com/brndguru';
                $fixed++;
            }
        }
    });

    if ($fixed === 0) {
        // Dump all social icons found for debugging
        bg_info('No Facebook+LinkedIn combo found. Scanning all social icons:');
        bg_walk($data, function(&$node) {
            foreach ($node['settings']['social_icon_list'] ?? [] as $icon) {
                $val = $icon['social_icon']['value'] ?? $icon['social_icon'] ?? 'unknown';
                $url = $icon['link']['url'] ?? '';
                bg_info('  icon=' . $val . ' url=' . $url);
            }
        });
    } else {
        bg_set_el($footer->ID, $data);
        bg_ok('Fixed ' . $fixed . ' Facebook icon URL(s) → https://www.facebook.com/brndguru');
    }
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FIX 4 — Header: duplicate CTA buttons responsive visibility
// ─────────────────────────────────────────────────────────────
function bg_fix4_header_cta_responsive() {
    bg_head('FIX 4: Header duplicate CTA buttons');

    $header = bg_find_template('header');
    if (!$header) {
        $all = get_posts(['post_type'=>'elementor_library','post_status'=>'publish','posts_per_page'=>20]);
        $titles = array_map(fn($p) => $p->post_title . '(ID:' . $p->ID . ')', $all);
        bg_err('Header template not found. Templates: ' . implode(', ', $titles));
        echo "\n"; return;
    }
    bg_info('Header template: "' . $header->post_title . '" (ID: ' . $header->ID . ')');

    $data = bg_get_el($header->ID);
    if (!$data) { bg_err('No Elementor data on header template'); echo "\n"; return; }

    $cta_nodes = [];
    bg_walk($data, function(&$node) use (&$cta_nodes) {
        if (($node['widgetType'] ?? '') !== 'button') return;
        $text = strtolower($node['settings']['text'] ?? '');
        if (strpos($text, 'schedule') !== false || strpos($text, 'call') !== false) {
            $cta_nodes[] = &$node;
        }
    });

    bg_info('CTA buttons found: ' . count($cta_nodes));
    if (count($cta_nodes) >= 2) {
        // Button 1 = desktop → hide on mobile
        $cta_nodes[0]['settings']['hide_mobile']  = 'yes';
        $cta_nodes[0]['settings']['hide_tablet']  = '';
        $cta_nodes[0]['settings']['hide_desktop'] = '';
        // Button 2 = mobile → hide on desktop + tablet
        $cta_nodes[1]['settings']['hide_desktop'] = 'yes';
        $cta_nodes[1]['settings']['hide_tablet']  = 'yes';
        $cta_nodes[1]['settings']['hide_mobile']  = '';
        bg_set_el($header->ID, $data);
        bg_ok('Responsive visibility set on ' . count($cta_nodes) . ' CTA buttons');
    } else {
        bg_info('Fewer than 2 CTA buttons — check header in Elementor');
    }
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FIX 5 — Homepage: pricing "Learn More" → "Book a Call"
// ─────────────────────────────────────────────────────────────
function bg_fix5_pricing_buttons() {
    bg_head('FIX 5: Pricing "Learn More" → "Book a Call"');

    $home_id = (int) get_option('page_on_front');
    if (!$home_id) {
        // Try to find by slug
        $p = get_page_by_path('home') ?: get_page_by_path('homepage');
        $home_id = $p ? $p->ID : 0;
    }
    if (!$home_id) { bg_err('Homepage not found'); echo "\n"; return; }
    bg_info('Homepage ID: ' . $home_id);

    $data = bg_get_el($home_id);
    if (!$data) { bg_err('No Elementor data on homepage'); echo "\n"; return; }

    $fixed = 0;
    $booking_url = 'brndgurumedia.com/widget/bookings/brndguru';

    bg_walk($data, function(&$node) use (&$fixed, $booking_url) {
        if (($node['widgetType'] ?? '') !== 'button') return;
        $text = $node['settings']['text'] ?? '';
        $url  = $node['settings']['link']['url'] ?? '';
        if (stripos($text, 'learn more') !== false && strpos($url, 'brndgurumedia') !== false) {
            $node['settings']['text'] = 'Book a Call';
            $node['settings']['link']['is_external'] = 'on';
            $fixed++;
        }
    });

    // Broader fallback if no booking URL match
    if ($fixed === 0) {
        bg_info('No booking-URL match. Renaming ALL "Learn More" buttons on homepage...');
        bg_walk($data, function(&$node) use (&$fixed) {
            if (($node['widgetType'] ?? '') === 'button' && stripos($node['settings']['text'] ?? '', 'learn more') !== false) {
                $node['settings']['text'] = 'Book a Call';
                $node['settings']['link']['is_external'] = 'on';
                $fixed++;
            }
        });
    }

    if ($fixed > 0) {
        bg_set_el($home_id, $data);
        bg_ok('Renamed ' . $fixed . ' button(s) to "Book a Call"');
    } else {
        bg_info('No "Learn More" buttons found on homepage — check page ID');
    }
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FIX 8 — About page meta description (Rank Math + Yoast)
// ─────────────────────────────────────────────────────────────
function bg_fix8_about_meta() {
    bg_head('FIX 8: About meta description');
    $about = get_page_by_path('about');
    if (!$about) { bg_err('About page not found'); echo "\n"; return; }
    $id  = $about->ID;
    $new = 'Brnd Guru is a B2B digital marketing agency helping ambitious brands scale with strategy, design, and performance marketing. 200+ brands, 30+ industries.';
    update_post_meta($id, 'rank_math_description', $new);
    update_post_meta($id, '_yoast_wpseo_metadesc', $new);
    bg_ok('Set Rank Math + Yoast meta (' . strlen($new) . ' chars) on page ID ' . $id);
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FIX 9 — About page: strip UTM params from award links
// ─────────────────────────────────────────────────────────────
function bg_fix9_award_links() {
    bg_head('FIX 9: Strip UTM params from award links');
    $about = get_page_by_path('about');
    if (!$about) { bg_err('About page not found'); echo "\n"; return; }
    $id  = $about->ID;
    $raw = get_post_meta($id, '_elementor_data', true);
    if (!$raw) { bg_err('No Elementor data'); echo "\n"; return; }

    $before = substr_count($raw, 'utm_');
    $fixed  = preg_replace_callback(
        '/"(https?:\/\/[^"]*utm_[^"]*)"/',
        function($m) {
            $parts = parse_url($m[1]);
            parse_str($parts['query'] ?? '', $qs);
            foreach (array_keys($qs) as $k) { if (strpos($k, 'utm_') === 0) unset($qs[$k]); }
            $q = http_build_query($qs);
            $url = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '');
            if (!empty($parts['path'])) $url .= $parts['path'];
            if ($q) $url .= '?' . $q;
            return '"' . $url . '"';
        },
        $raw
    );
    $after = substr_count($fixed, 'utm_');

    if ($fixed !== $raw) {
        update_post_meta($id, '_elementor_data', wp_slash($fixed));
        delete_post_meta($id, '_elementor_css');
        bg_ok('Removed ' . ($before - $after) . ' UTM occurrence(s)');
    } else {
        bg_info('No UTM params found in JSON. Check for raw HTML text widgets in Elementor editor.');
    }
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FIX 10 — Active nav state CSS
// ─────────────────────────────────────────────────────────────
function bg_fix10_active_nav_css() {
    bg_head('FIX 10: Active nav state CSS');

    $css = '
/* BrndGuru: active nav item highlight */
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

    $theme    = get_option('stylesheet');
    $existing = wp_get_custom_css($theme);

    if (strpos($existing, 'current-menu-item') !== false) {
        bg_ok('Already applied — skipping');
        echo "\n"; return;
    }

    $result = wp_update_custom_css_post($existing . "\n" . $css, ['stylesheet' => $theme]);
    if (is_wp_error($result)) {
        // Fallback: store in Astra custom CSS option
        $astra_css = get_option('astra-settings', []);
        $astra_css['custom-css'] = ($astra_css['custom-css'] ?? '') . "\n" . $css;
        update_option('astra-settings', $astra_css);
        bg_ok('Added to Astra custom CSS (wp_update_custom_css_post failed)');
    } else {
        bg_ok('Added to Additional CSS (Customizer)');
    }
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FIX 11 — Remove "Get a Quote" nav item
// ─────────────────────────────────────────────────────────────
function bg_fix11_remove_get_quote_nav() {
    bg_head('FIX 11: Remove "Get a Quote" nav item');
    $found = false;
    foreach (wp_get_nav_menus() as $menu) {
        foreach ((wp_get_nav_menu_items($menu->term_id) ?: []) as $item) {
            $t = strtolower($item->title);
            if (strpos($t, 'get a quote') !== false || $t === 'quote') {
                wp_delete_post($item->ID, true);
                bg_ok('Removed "' . $item->title . '" (ID ' . $item->ID . ') from "' . $menu->name . '"');
                $found = true;
            }
        }
    }
    if (!$found) bg_info('Not found (may already be removed or named differently).');
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FLUSH CACHES
// ─────────────────────────────────────────────────────────────
function bg_flush_caches() {
    bg_head('FLUSHING CACHES');
    wp_cache_flush();
    bg_ok('Object cache');

    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    bg_ok('Transients');

    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        bg_ok('Elementor CSS cache');
    }
    // Swift Performance
    if (function_exists('swift_performance_cache_warmup')) {
        do_action('swift_performance_after_clean_cache');
        bg_ok('Swift Performance cache');
    }
    if (function_exists('rocket_clean_domain'))    { rocket_clean_domain();          bg_ok('WP Rocket'); }
    if (class_exists('LiteSpeed_Cache_API'))       { LiteSpeed_Cache_API::purge_all(); bg_ok('LiteSpeed'); }
    if (function_exists('w3tc_flush_all'))         { w3tc_flush_all();               bg_ok('W3TC'); }
    echo "\n";
}
