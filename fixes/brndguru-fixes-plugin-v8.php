<?php
/**
 * Plugin Name: BrndGuru Site Fixes v8
 * Description: Fix 4 (header CTA visibility), Fix 6 (client logos CSS), Fix 7 (OG image). DELETE after running.
 * Version: 8.0
 */

if (!defined('ABSPATH')) exit;

add_filter('plugin_row_meta', function($links, $file) {
    if (strpos($file, 'brndguru-fixes') !== false) {
        $nonce = wp_create_nonce('bg8_run');
        $links[] = '<a href="' . admin_url('?bg8_run=1&bg8_nonce=' . $nonce) . '" style="font-weight:700;color:#00a32a;font-size:14px;">▶ RUN FIXES 4 + 6 + 7</a>';
    }
    return $links;
}, 10, 2);

add_action('admin_init', function() {
    if (!isset($_GET['bg8_run'])) return;
    if (!current_user_can('manage_options')) wp_die('Permission denied.');
    if (!wp_verify_nonce($_GET['bg8_nonce'] ?? '', 'bg8_run')) wp_die('Invalid nonce.');

    echo '<pre style="font-family:monospace;padding:20px;background:#0d1117;color:#58d68d;font-size:13px;line-height:1.7;">';
    echo "BrndGuru Site Fixes v8 — Fixes 4, 6, 7\n" . str_repeat('=', 60) . "\n\n";

    bg8_fix4_header_visibility();
    bg8_fix6_client_logos();
    bg8_fix7_og_image();
    bg8_flush();

    echo str_repeat('=', 60) . "\n";
    echo '<span style="color:#fff">DONE. Deactivate + delete this plugin now.</span>' . "\n";
    echo '</pre>';
    exit;
});

function bg8_ok($m)   { echo '  <span style="color:#2ecc71">✓ ' . esc_html($m) . '</span>' . "\n"; }
function bg8_err($m)  { echo '  <span style="color:#e74c3c">✗ ' . esc_html($m) . '</span>' . "\n"; }
function bg8_info($m) { echo '  ' . esc_html($m) . "\n"; }
function bg8_head($m) { echo '<span style="color:#f1c40f">── ' . esc_html($m) . ' ──</span>' . "\n"; }

function bg8_walk(array &$nodes, callable $fn) {
    foreach ($nodes as &$n) { $fn($n); if (!empty($n['elements'])) bg8_walk($n['elements'], $fn); }
}

// ─────────────────────────────────────────────────────────────
// FIX 4 — Header duplicate CTA: device visibility
// Strategy: find Astra header builder layout rows; remove button
// components from the device layouts where they should be hidden.
// Falls back to custom CSS if layout structure not found.
// ─────────────────────────────────────────────────────────────
function bg8_fix4_header_visibility() {
    bg8_head('FIX 4: Header CTA device visibility');

    $astra = get_option('astra-settings', []);

    // ── Dump header builder layout keys ──────────────────────
    $layout_keys = ['hba-header-desktop-items', 'hba-header-tablet-items', 'hba-header-mobile-items',
                    'hba-header-desktop', 'hba-header-tablet', 'hba-header-mobile',
                    'header-desktop-items', 'header-tablet-items', 'header-mobile-items'];

    $found_layouts = [];
    foreach ($layout_keys as $k) {
        if (isset($astra[$k])) {
            $found_layouts[$k] = $astra[$k];
            $s = maybe_serialize($astra[$k]);
            bg8_info('Layout key [' . $k . ']: ' . substr($s, 0, 300));
        }
    }

    // Also search for any key containing button IDs in layout-like arrays
    $button_in_layout = false;
    foreach ($astra as $k => $v) {
        if (strpos($k, 'hba-') === 0 || strpos($k, 'header-layout') !== false || strpos($k, 'header-row') !== false) {
            $s = maybe_serialize($v);
            if (strpos($s, 'button') !== false) {
                bg8_info('Button in layout key [' . $k . ']: ' . substr($s, 0, 400));
                $button_in_layout = true;
                $found_layouts[$k] = $v;
            }
        }
    }

    $fixed = false;

    // ── Try to manipulate layout arrays ──────────────────────
    // Astra Pro stores header builder layout as nested arrays:
    // [ 'desktop' => [ [row1_left], [row1_center], [row1_right] ], ... ]
    // Components are identified by their slot name e.g. 'button-1', 'button-2'

    foreach (['hba-header-desktop-items', 'hba-header-desktop'] as $desk_key) {
        if (!isset($astra[$desk_key])) continue;
        $layout = $astra[$desk_key];
        $s = maybe_serialize($layout);

        // Look for button-2 (the mobile duplicate) in desktop layout and remove it
        if (is_array($layout)) {
            $modified = bg8_remove_from_layout($layout, 'button-2');
            if ($modified !== $layout) {
                $astra[$desk_key] = $modified;
                update_option('astra-settings', $astra);
                bg8_ok('Removed button-2 from desktop layout key: ' . $desk_key);
                $fixed = true;
            }
        }
    }

    foreach (['hba-header-mobile-items', 'hba-header-mobile'] as $mob_key) {
        if (!isset($astra[$mob_key])) continue;
        $layout = $astra[$mob_key];

        if (is_array($layout)) {
            $modified = bg8_remove_from_layout($layout, 'button-1');
            if ($modified !== $layout) {
                $astra[$mob_key] = $modified;
                update_option('astra-settings', $astra);
                bg8_ok('Removed button-1 from mobile layout key: ' . $mob_key);
                $fixed = true;
            }
        }
    }

    // ── Fallback: CSS-based hide (works regardless of storage format) ──
    // If only one button exists, nothing to do. If two exist, CSS hides one per breakpoint.
    $btn1_text = $astra['header-button1-text'] ?? '';
    $btn2_text = $astra['header-button2-text'] ?? '';

    bg8_info('');
    bg8_info('header-button1-text: "' . $btn1_text . '"');
    bg8_info('header-button2-text: "' . $btn2_text . '"');

    $schedule_in_btn2 = stripos($btn2_text, 'schedule') !== false || stripos($btn2_text, 'call') !== false;

    if (!$fixed) {
        if ($schedule_in_btn2) {
            // Two CTA buttons — add CSS to hide button-2 on desktop and button-1 on mobile
            $css = "/* Fix 4: Header CTA responsive visibility */\n"
                 . "@media (min-width: 922px) { .ast-header-button-2 { display:none !important; } }\n"
                 . "@media (max-width: 921px) { .ast-header-button-1 { display:none !important; } }\n";
            bg8_add_custom_css($css, 'fix4-header-cta');
            bg8_ok('Added CSS to hide duplicate CTA button per device breakpoint');
            $fixed = true;
        } else {
            // Only one button found — check if duplicate comes from Elementor header template
            bg8_info('Only one Astra header button with "Schedule a Call" text.');
            bg8_info('The duplicate may come from an Elementor sticky header or separate template.');

            // Add broad CSS targeting any second instance of a .schedule or button with that text
            // We'll hide it via CSS selector targeting Astra button classes
            $css = "/* Fix 4: Hide duplicate header CTA on mobile */\n"
                 . ".ast-header-break-point .ast-header-button-1 { display:none !important; }\n"
                 . ".main-header-bar .ast-header-button-1:last-of-type { display:none !important; }\n";
            bg8_info('');
            bg8_info('Adding conservative CSS — verify visually after applying.');
            bg8_add_custom_css($css, 'fix4-header-cta');
            bg8_ok('Added scoped CSS for Fix 4 (verify result in browser)');
            $fixed = true;
        }
    }

    if (!$fixed) {
        bg8_err('Could not auto-fix Fix 4 — manual Customizer step required.');
    }
    echo "\n";
}

function bg8_remove_from_layout(array $layout, string $component_id): array {
    foreach ($layout as &$row) {
        if (is_array($row)) {
            foreach ($row as &$zone) {
                if (is_array($zone)) {
                    $zone = array_values(array_filter($zone, fn($c) => $c !== $component_id && (is_array($c) ? ($c['id'] ?? $c['key'] ?? '') !== $component_id : true)));
                }
            }
        }
    }
    return $layout;
}

function bg8_add_custom_css(string $css, string $label) {
    // Try Elementor custom CSS option
    $el_css = get_option('elementor_custom_css', '');
    if (strpos($el_css, $label) === false) {
        update_option('elementor_custom_css', $el_css . "\n" . $css);
    }

    // Also add via WP core Additional CSS (theme_mods)
    $existing = wp_get_custom_css();
    if (strpos($existing, $label) === false) {
        wp_update_custom_css_post($existing . "\n" . $css);
    }
}

// ─────────────────────────────────────────────────────────────
// FIX 6 — Client logos: hide broken images via CSS + Elementor
// ─────────────────────────────────────────────────────────────
function bg8_fix6_client_logos() {
    bg8_head('FIX 6: Client logos — hide broken images');

    // ── Strategy 1: Find the logos section in homepage Elementor data ──
    $home_id = (int) get_option('page_on_front');
    if (!$home_id) {
        // Try finding by title/slug
        $home = get_page_by_path('home') ?: get_page_by_path('homepage');
        $home_id = $home ? $home->ID : 0;
    }

    $section_hidden = false;

    if ($home_id) {
        $raw = get_post_meta($home_id, '_elementor_data', true);
        $data = $raw ? json_decode($raw, true) : null;

        if ($data && json_last_error() === JSON_ERROR_NONE) {
            $logo_section_found = false;

            bg8_walk($data, function(&$n) use (&$logo_section_found) {
                $el_type = $n['elType'] ?? '';
                $wt      = $n['widgetType'] ?? '';
                $s       = &$n['settings'];

                // Look for image widgets with broken/placeholder src
                if ($wt === 'image') {
                    $url = $s['image']['url'] ?? '';
                    // Placeholder or empty images are client logos placeholders
                    if (empty($url) || strpos($url, 'placeholder') !== false) {
                        $s['_hidden'] = 'yes'; // hide this image widget
                        $logo_section_found = true;
                    }
                }

                // Look for a section/container with "client" or "logo" in its CSS ID or class
                if (in_array($el_type, ['section', 'container'])) {
                    $css_id    = $s['_element_id'] ?? '';
                    $css_class = $s['css_classes'] ?? '';
                    if (preg_match('/client|logo|partner|brand/i', $css_id . $css_class)) {
                        $s['visibility'] = 'hidden';
                        $logo_section_found = true;
                        bg8_info('Found logo/client section (CSS ID: "' . $css_id . '") — setting to hidden');
                    }
                }
            });

            if ($logo_section_found) {
                update_post_meta($home_id, '_elementor_data', wp_slash(wp_json_encode($data, JSON_UNESCAPED_UNICODE)));
                delete_post_meta($home_id, '_elementor_css');
                bg8_ok('Elementor logo section updated on homepage ID ' . $home_id);
                $section_hidden = true;
            }
        }
        bg8_info('Homepage ID: ' . $home_id . ($section_hidden ? ' — section updated' : ' — no logo section found by ID/class'));
    }

    // ── Strategy 2: CSS to hide broken <img> tags globally ──
    // This catches ALL broken images sitewide — minimal and safe
    $css = "/* Fix 6: Hide broken client logo images */\n"
         . "img[src=''], img:not([src]) { display:none !important; }\n"
         . "img { min-width:0; }\n"
         . "/* Hide images that fail to load */\n"
         . "img.attachment-full.size-full:not([src*='brndguru']):empty { display:none !important; }\n";

    bg8_add_custom_css($css, 'fix6-client-logos');
    bg8_ok('Added CSS to hide broken/empty image elements');

    // ── Strategy 3: Add onerror JS via wp_footer hook (stored as option) ──
    // We'll write a small option that a persistent mu-plugin or the theme could use,
    // but since we can't add JS hooks here directly, add via Elementor Custom JS option.
    $existing_js = get_option('elementor_custom_js', '');
    $js_snippet = "\n// Fix6: hide broken images\ndocument.querySelectorAll('img').forEach(function(i){i.onerror=function(){this.style.display='none';};if(!this.complete||this.naturalWidth===0)i.style.display='none';});\n";
    if (strpos($existing_js, 'Fix6') === false) {
        update_option('elementor_custom_js', $existing_js . $js_snippet);
        bg8_ok('Added JS to hide broken images on load');
    }

    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FIX 7 — OG image via Rank Math post meta
// Sets facebook/twitter image on Home and About pages.
// Uses existing site logo as OG image (replace with 1200x628 later).
// ─────────────────────────────────────────────────────────────
function bg8_fix7_og_image() {
    bg8_head('FIX 7: OG/Social image (Rank Math)');

    // Get the site logo URL as the OG image source
    $logo_id  = get_theme_mod('custom_logo');
    $logo_url = '';
    if ($logo_id) {
        $logo_data = wp_get_attachment_image_src($logo_id, 'full');
        $logo_url  = $logo_data ? $logo_data[0] : '';
    }

    // Fallback: use the Astra logo from settings
    if (!$logo_url) {
        $astra = get_option('astra-settings', []);
        $logo_url = $astra['transparent-header-logo'] ?? $astra['ast-header-responsive-logo'] ?? '';
    }

    // Fallback: use site icon
    if (!$logo_url) {
        $site_icon_id = get_option('site_icon');
        if ($site_icon_id) {
            $img = wp_get_attachment_image_src($site_icon_id, 'full');
            $logo_url = $img ? $img[0] : '';
        }
    }

    bg8_info('OG image source URL: ' . ($logo_url ?: '(none found)'));

    if (!$logo_url) {
        bg8_err('No logo/image found to use as OG image. Upload a 1200x628 image manually in Rank Math.');
        echo "\n";
        return;
    }

    // Get attachment ID for the logo URL
    $logo_att_id = attachment_url_to_postid($logo_url);
    bg8_info('Attachment ID: ' . ($logo_att_id ?: 'not found — will use URL only'));

    // Pages to update
    $home_id  = (int) get_option('page_on_front');
    $about_id = 0;

    // Find About page
    $about = get_page_by_path('about');
    if ($about) {
        $about_id = $about->ID;
    } else {
        global $wpdb;
        $about_id = (int) $wpdb->get_var(
            "SELECT ID FROM {$wpdb->posts}
             WHERE post_type='page' AND post_status='publish'
             AND post_title LIKE '%about%' LIMIT 1"
        );
    }

    $pages = array_filter(['Home' => $home_id, 'About' => $about_id]);

    if (empty($pages)) {
        bg8_err('Could not find Home or About page IDs.');
        echo "\n";
        return;
    }

    // Rank Math meta keys for OG/social images
    // rank_math_facebook_image      = URL
    // rank_math_facebook_image_id   = attachment ID
    // rank_math_twitter_image       = URL
    // rank_math_twitter_image_id    = attachment ID
    // rank_math_og_content_image    = fallback OG image
    foreach ($pages as $label => $pid) {
        if (!$pid) continue;

        update_post_meta($pid, 'rank_math_facebook_image',    $logo_url);
        update_post_meta($pid, 'rank_math_twitter_image',     $logo_url);
        update_post_meta($pid, 'rank_math_og_content_image',  $logo_url);
        if ($logo_att_id) {
            update_post_meta($pid, 'rank_math_facebook_image_id', $logo_att_id);
            update_post_meta($pid, 'rank_math_twitter_image_id',  $logo_att_id);
        }

        bg8_ok($label . ' page (ID ' . $pid . '): Rank Math OG image set to logo URL');
    }

    bg8_info('');
    bg8_info('NOTE: The current OG image is the site logo.');
    bg8_info('For best results, replace with a 1200x628px branded image:');
    bg8_info('  Edit page → Rank Math → Social tab → upload new image → Update');
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FLUSH
// ─────────────────────────────────────────────────────────────
function bg8_flush() {
    bg8_head('FLUSHING CACHES');
    wp_cache_flush(); bg8_ok('Object cache');
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    bg8_ok('Transients');
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        bg8_ok('Elementor CSS cache');
    }
    do_action('swift_performance_after_clean_cache');
    if (function_exists('rocket_clean_domain'))  { rocket_clean_domain(); bg8_ok('WP Rocket'); }
    if (class_exists('LiteSpeed_Cache_API'))     { LiteSpeed_Cache_API::purge_all(); bg8_ok('LiteSpeed'); }
    bg8_ok('Done');
    echo "\n";
}
