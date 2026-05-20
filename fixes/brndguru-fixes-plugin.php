<?php
/**
 * Plugin Name: BrndGuru Site Fixes v4
 * Description: Final fixes: Astra social/header settings, pricing buttons, UTM regex. DELETE after running.
 * Version: 4.0
 */

if (!defined('ABSPATH')) exit;

add_filter('plugin_row_meta', function($links, $file) {
    if (strpos($file, 'brndguru-fixes') !== false) {
        $nonce = wp_create_nonce('brndguru_fixes');
        $links[] = '<a href="' . admin_url('?brndguru_run_fixes=1&brndguru_nonce=' . $nonce) . '" style="font-weight:700;color:#00a32a;font-size:14px;">▶ RUN FIXES NOW</a>';
    }
    return $links;
}, 10, 2);

add_action('admin_init', function() {
    if (!isset($_GET['brndguru_run_fixes'])) return;
    if (!current_user_can('manage_options')) wp_die('Permission denied.');
    if (!wp_verify_nonce($_GET['brndguru_nonce'] ?? '', 'brndguru_fixes')) wp_die('Invalid nonce.');

    echo '<pre style="font-family:monospace;padding:20px;background:#0d1117;color:#58d68d;font-size:13px;line-height:1.7;">';
    echo "BrndGuru Site Fixes v4\n" . str_repeat('=', 60) . "\n\n";

    bg4_fix3_facebook_astra();
    bg4_fix4_header_astra();
    bg4_fix5_pricing_buttons();
    bg4_fix9_utm_links();
    bg4_flush();

    echo str_repeat('=', 60) . "\n";
    echo '<span style="color:#fff">DONE. Deactivate + delete this plugin now.</span>' . "\n";
    echo '</pre>';
    exit;
});

function bg4_ok($m)   { echo '  <span style="color:#2ecc71">✓ ' . esc_html($m) . '</span>' . "\n"; }
function bg4_err($m)  { echo '  <span style="color:#e74c3c">✗ ' . esc_html($m) . '</span>' . "\n"; }
function bg4_info($m) { echo '  ' . esc_html($m) . "\n"; }
function bg4_head($m) { echo '<span style="color:#f1c40f">── ' . esc_html($m) . ' ──</span>' . "\n"; }

function bg4_get_el($id) {
    $raw = get_post_meta($id, '_elementor_data', true);
    if (!$raw) return null;
    $d = json_decode($raw, true);
    return json_last_error() === JSON_ERROR_NONE ? $d : null;
}
function bg4_set_el($id, $data) {
    update_post_meta($id, '_elementor_data', wp_slash(wp_json_encode($data, JSON_UNESCAPED_UNICODE)));
    delete_post_meta($id, '_elementor_css');
}
function bg4_walk(array &$nodes, callable $fn) {
    foreach ($nodes as &$n) { $fn($n); if (!empty($n['elements'])) bg4_walk($n['elements'], $fn); }
}

// ─────────────────────────────────────────────────────────────
// FIX 3 — Footer Facebook icon via Astra settings
// ─────────────────────────────────────────────────────────────
function bg4_fix3_facebook_astra() {
    bg4_head('FIX 3: Footer Facebook icon (Astra settings)');

    $astra = get_option('astra-settings', []);
    $WRONG   = 'linkedin.com/company/brndguru';
    $CORRECT = 'https://www.facebook.com/brndguru';
    $fixed   = false;

    // Dump all Astra keys that contain "social" or "facebook" or "linkedin"
    $social_keys = array_filter(array_keys($astra), function($k) {
        return stripos($k,'social')!==false || stripos($k,'facebook')!==false || stripos($k,'linkedin')!==false;
    });
    bg4_info('Astra social-related keys: ' . (count($social_keys) ? implode(', ', $social_keys) : 'none'));

    // Search every Astra key whose value contains the wrong LinkedIn URL
    foreach ($astra as $key => $val) {
        $serialized = maybe_serialize($val);
        if (strpos($serialized, $WRONG) !== false) {
            // If it's an array, walk and replace
            if (is_array($val)) {
                array_walk_recursive($val, function(&$v) use ($WRONG, $CORRECT) {
                    if (is_string($v) && strpos($v, $WRONG) !== false) {
                        $v = str_replace('https://www.' . $WRONG . '/', $CORRECT, $v);
                        $v = str_replace('https://' . $WRONG . '/', $CORRECT, $v);
                        $v = str_replace($WRONG, ltrim($CORRECT, 'https://'), $v);
                    }
                });
                $astra[$key] = $val;
            } else {
                $astra[$key] = str_replace(
                    ['https://www.' . $WRONG . '/', 'https://' . $WRONG . '/'],
                    [$CORRECT, $CORRECT],
                    $val
                );
            }
            bg4_ok('Replaced LinkedIn URL in Astra key: ' . $key);
            $fixed = true;
        }
    }

    if ($fixed) {
        update_option('astra-settings', $astra);
        bg4_ok('Astra settings saved.');
    } else {
        bg4_info('LinkedIn URL not found in Astra settings.');

        // Check Customizer / theme_mods
        $mods = get_theme_mods();
        $mod_keys = array_filter(array_keys($mods), function($k) {
            $s = maybe_serialize($mods[$k] ?? '');
            return strpos($s, $WRONG) !== false;
        });
        if ($mod_keys) {
            foreach ($mod_keys as $k) {
                $v = $mods[$k];
                if (is_string($v)) set_theme_mod($k, str_replace($WRONG, trim($CORRECT,'https://'), $v));
                bg4_ok('Fixed in theme_mod: ' . $k);
                $fixed = true;
            }
        }

        if (!$fixed) {
            // Dump ALL astra keys and values (truncated) for manual inspection
            bg4_info('--- Astra settings dump (social/icon relevant) ---');
            foreach ($astra as $k => $v) {
                $s = maybe_serialize($v);
                if (strlen($s) < 300 && (
                    stripos($k,'social')!==false || stripos($k,'icon')!==false ||
                    stripos($k,'footer')!==false || stripos($k,'button')!==false ||
                    stripos($k,'link')!==false
                )) {
                    bg4_info('  [' . $k . '] = ' . substr($s, 0, 120));
                }
            }
            bg4_err('Facebook/LinkedIn icon URL not found in Astra or Elementor. Check footer widget area manually.');
        }
    }
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FIX 4 — Header duplicate CTA via Astra Header Builder
// ─────────────────────────────────────────────────────────────
function bg4_fix4_header_astra() {
    bg4_head('FIX 4: Header duplicate CTA (Astra Header Builder)');

    $astra = get_option('astra-settings', []);

    // Dump header-button related keys
    $btn_keys = array_filter(array_keys($astra), function($k) {
        return stripos($k,'button')!==false || stripos($k,'header')!==false;
    });

    bg4_info('Astra header/button keys (' . count($btn_keys) . '):');
    foreach ($btn_keys as $k) {
        $v = $astra[$k];
        if (!is_array($v)) bg4_info('  [' . $k . '] = ' . substr((string)$v, 0, 100));
    }

    // Look for device visibility settings on header buttons
    // Astra stores these as: header-button1-on-desktop, header-button1-on-tablet, header-button1-on-mobile
    $visibility_patterns = [
        'header-button1-on-mobile' => false,   // desktop button → hide on mobile
        'header-button2-on-desktop' => false,  // mobile button → hide on desktop
        'header-button2-on-tablet' => false,
    ];

    // Try common Astra Pro header builder device visibility keys
    $device_keys_found = [];
    foreach ($astra as $k => $v) {
        if (preg_match('/button.*mobile|button.*tablet|button.*desktop|mobile.*button|desktop.*button/i', $k)) {
            $device_keys_found[$k] = $v;
        }
    }

    if ($device_keys_found) {
        bg4_info('Device visibility keys found: ' . implode(', ', array_keys($device_keys_found)));
        // Can set them here if needed
    } else {
        bg4_info('No device visibility keys found in Astra settings.');
        bg4_info('');
        bg4_info('MANUAL STEP for Fix 4:');
        bg4_info('  1. Go to: Appearance → Customize');
        bg4_info('  2. Click "Header Builder" or "Header"');
        bg4_info('  3. Find the "Schedule a Call Now" button');
        bg4_info('  4. Click it → look for "Visibility" or device icons (desktop/tablet/mobile)');
        bg4_info('  5. Set Desktop button: hide on Mobile');
        bg4_info('  6. Set Mobile button: hide on Desktop');
        bg4_info('  7. Publish');
    }
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FIX 5 — Pricing "Learn More" → "Book a Call"
// Finds the actual global section via DB and dumps widget types
// ─────────────────────────────────────────────────────────────
function bg4_fix5_pricing_buttons() {
    bg4_head('FIX 5: Pricing "Learn More" → "Book a Call"');

    global $wpdb;

    // Find published pages/posts (not revisions) with brndgurumedia URL
    $rows = $wpdb->get_results(
        "SELECT pm.post_id, p.post_title, p.post_type, p.post_status
         FROM {$wpdb->postmeta} pm
         JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = '_elementor_data'
         AND pm.meta_value LIKE '%brndgurumedia%'
         AND p.post_type != 'revision'
         AND p.post_status IN ('publish','draft')
         ORDER BY p.post_type, p.ID"
    );

    bg4_info('Published posts with brndgurumedia in Elementor data: ' . count($rows));
    foreach ($rows as $row) {
        bg4_info('  ID:' . $row->post_id . ' type:' . $row->post_type . ' "' . $row->post_title . '"');
    }

    $total_fixed = 0;

    foreach ($rows as $row) {
        $data = bg4_get_el($row->post_id);
        if (!$data) continue;

        $fixed = 0;
        bg4_walk($data, function(&$n) use (&$fixed) {
            $wt = $n['widgetType'] ?? '';
            $s  = $n['settings'] ?? [];

            // Standard button widget
            if ($wt === 'button' && stripos($s['text'] ?? '', 'learn more') !== false) {
                $n['settings']['text'] = 'Book a Call';
                $n['settings']['link']['is_external'] = 'on';
                $fixed++;
                return;
            }

            // Price table / pricing widgets — check common setting keys
            $btn_keys = ['button_text','cta_text','btn_text','price_button_text',
                         'footer_button_text','button','edd_button_text'];
            foreach ($btn_keys as $bk) {
                if (isset($s[$bk]) && stripos($s[$bk], 'learn more') !== false) {
                    $n['settings'][$bk] = 'Book a Call';
                    $fixed++;
                }
            }

            // Dump any widget containing brndgurumedia + learn more for diagnosis
            $serialized = maybe_serialize($s);
            if (strpos($serialized, 'brndgurumedia') !== false && stripos($serialized, 'learn more') !== false) {
                global $bg4_dumped;
                if (!isset($bg4_dumped)) {
                    $bg4_dumped = true;
                    bg4_info('  Widget type with both strings: ' . ($wt ?: 'container/section'));
                    bg4_info('  Settings keys: ' . implode(', ', array_keys($s)));
                }
            }
        });

        if ($fixed > 0) {
            bg4_set_el($row->post_id, $data);
            bg4_ok('Fixed ' . $fixed . ' button(s) in "' . $row->post_title . '" (ID ' . $row->post_id . ')');
            $total_fixed += $fixed;
        }
    }

    if ($total_fixed === 0 && count($rows) > 0) {
        bg4_info('');
        bg4_info('Pricing buttons found in DB but widget type not matched.');
        bg4_info('The pricing section likely uses a custom/third-party widget.');
        bg4_info('');
        bg4_info('MANUAL STEP for Fix 5:');
        bg4_info('  1. Go to WordPress Admin → Pages → find the pricing page');
        bg4_info('     OR: Home → Edit with Elementor → find pricing cards');
        bg4_info('  2. Click each "Learn More" button → change text to "Book a Call"');
        bg4_info('  3. There are 3 cards: Launch, Scale, Dominate');
        bg4_info('  4. Update the page');
    } elseif ($total_fixed > 0) {
        bg4_ok('Total: ' . $total_fixed . ' pricing button(s) renamed to "Book a Call"');
    }
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FIX 9 — Strip UTM params — corrected regex for escaped JSON
// ─────────────────────────────────────────────────────────────
function bg4_fix9_utm_links() {
    bg4_head('FIX 9: Strip UTM params (corrected regex)');

    global $wpdb;

    // Find ALL posts (any type) with utm_ in Elementor data
    $rows = $wpdb->get_results(
        "SELECT pm.post_id, p.post_title, p.post_type, p.post_status
         FROM {$wpdb->postmeta} pm
         JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = '_elementor_data'
         AND pm.meta_value LIKE '%utm\_%'
         AND p.post_type != 'revision'
         AND p.post_status IN ('publish','draft')"
    );

    bg4_info('Published posts with utm_ in Elementor data: ' . count($rows));

    foreach ($rows as $row) {
        $raw = get_post_meta($row->post_id, '_elementor_data', true);
        $before = substr_count($raw, 'utm_');
        bg4_info('  ID:' . $row->post_id . ' "' . $row->post_title . '" — utm_ count: ' . $before);

        // Strategy: work on the raw JSON string directly
        // URLs appear in multiple forms in stored Elementor JSON:
        // 1. "url":"https:\/\/...?utm_source=chatgpt.com"
        // 2. href=\"https:\/\/...?utm_source=chatgpt.com\"
        // 3. href="https://...?utm_source=chatgpt.com"
        // The key insight: forward slashes are escaped as \/ in stored JSON

        $fixed = $raw;

        // Universal approach: find anything that looks like a URL containing utm_
        // Match URLs in any quoting context, allowing escaped slashes
        $fixed = preg_replace_callback(
            '/https?:\\\\?\/\\\\?\/[^\s"\'<>\\\\]*utm_[^\s"\'<>\\\\]*/u',
            function($m) {
                $url = stripslashes($m[0]);
                return bg4_clean_utm_raw($url, $m[0]);
            },
            $fixed
        );

        // Also handle URLs between JSON quotes (may have escaped slashes stored as \/)
        $fixed = preg_replace_callback(
            '/(https?:(?:\\\\\/|\/){2}[^",\s\\\\]*utm_[^",\s\\\\]*)/u',
            function($m) {
                $url_escaped = $m[1];
                // Un-escape for parsing
                $url_clean = str_replace('\/', '/', $url_escaped);
                $cleaned = bg4_clean_utm_raw($url_clean, $url_escaped);
                // Re-escape if original was escaped
                if (strpos($url_escaped, '\/') !== false) {
                    $cleaned = str_replace('/', '\/', $cleaned);
                }
                return $cleaned;
            },
            $fixed
        );

        $after = substr_count($fixed, 'utm_');

        if ($fixed !== $raw && $after < $before) {
            update_post_meta($row->post_id, '_elementor_data', wp_slash($fixed));
            delete_post_meta($row->post_id, '_elementor_css');
            bg4_ok('  Removed ' . ($before - $after) . ' UTM occurrence(s) from "' . $row->post_title . '"');
        } else {
            // Last resort: simple string replace of known UTM patterns
            $known_patterns = [
                '?utm_source=chatgpt.com',
                '&utm_source=chatgpt.com',
                '?utm_source=chatgpt',
                '&utm_source=chatgpt.com',
            ];
            $changed = false;
            foreach ($known_patterns as $pat) {
                if (strpos($raw, $pat) !== false) {
                    $fixed = str_replace($pat, '', $raw);
                    $changed = true;
                    bg4_info('  Removed known pattern: ' . $pat);
                }
            }
            if ($changed) {
                update_post_meta($row->post_id, '_elementor_data', wp_slash($fixed));
                delete_post_meta($row->post_id, '_elementor_css');
                bg4_ok('  Cleaned UTM via string replace in "' . $row->post_title . '"');
            } else {
                // Show first 200 chars around "utm_" for diagnosis
                $pos = strpos($raw, 'utm_');
                if ($pos !== false) {
                    $context = substr($raw, max(0, $pos - 80), 200);
                    bg4_info('  Context around utm_: ...' . $context . '...');
                }
                bg4_err('  Could not auto-strip UTM from "' . $row->post_title . '" — edit manually in Elementor');
            }
        }
    }
    echo "\n";
}

function bg4_clean_utm_raw($url, $original) {
    $parts = parse_url($url);
    if (!isset($parts['query']) || strpos($parts['query'], 'utm_') === false) return $original;
    parse_str($parts['query'], $qs);
    foreach (array_keys($qs) as $k) { if (strpos($k,'utm_')===0) unset($qs[$k]); }
    $q = http_build_query($qs);
    $out = ($parts['scheme']??'https') . '://' . ($parts['host']??'');
    if (!empty($parts['path'])) $out .= $parts['path'];
    if ($q) $out .= '?' . $q;
    return $out;
}

// ─────────────────────────────────────────────────────────────
// FLUSH CACHES
// ─────────────────────────────────────────────────────────────
function bg4_flush() {
    bg4_head('FLUSHING CACHES');
    wp_cache_flush(); bg4_ok('Object cache');
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    bg4_ok('Transients');
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        bg4_ok('Elementor CSS cache');
    }
    do_action('swift_performance_after_clean_cache');
    if (function_exists('rocket_clean_domain'))  { rocket_clean_domain(); bg4_ok('WP Rocket'); }
    if (class_exists('LiteSpeed_Cache_API'))     { LiteSpeed_Cache_API::purge_all(); bg4_ok('LiteSpeed'); }
    bg4_ok('Done');
    echo "\n";
}
