<?php
/**
 * Plugin Name: BrndGuru Site Fixes v5
 * Description: Fix 3 ONLY — Footer Facebook icon URL. Searches ALL post types including elementor-hf. DELETE after running.
 * Version: 5.0
 */

if (!defined('ABSPATH')) exit;

add_filter('plugin_row_meta', function($links, $file) {
    if (strpos($file, 'brndguru-fixes') !== false) {
        $nonce = wp_create_nonce('brndguru_fixes_v5');
        $links[] = '<a href="' . admin_url('?brndguru_run_v5=1&brndguru_nonce=' . $nonce) . '" style="font-weight:700;color:#00a32a;font-size:14px;">▶ RUN FIX 3 NOW</a>';
    }
    return $links;
}, 10, 2);

add_action('admin_init', function() {
    if (!isset($_GET['brndguru_run_v5'])) return;
    if (!current_user_can('manage_options')) wp_die('Permission denied.');
    if (!wp_verify_nonce($_GET['brndguru_nonce'] ?? '', 'brndguru_fixes_v5')) wp_die('Invalid nonce.');

    echo '<pre style="font-family:monospace;padding:20px;background:#0d1117;color:#58d68d;font-size:13px;line-height:1.7;">';
    echo "BrndGuru Site Fixes v5 — Fix 3: Footer Facebook Icon\n" . str_repeat('=', 60) . "\n\n";

    bg5_fix3_footer_facebook();
    bg5_flush();

    echo str_repeat('=', 60) . "\n";
    echo '<span style="color:#fff">DONE. Deactivate + delete this plugin now.</span>' . "\n";
    echo '</pre>';
    exit;
});

function bg5_ok($m)   { echo '  <span style="color:#2ecc71">✓ ' . esc_html($m) . '</span>' . "\n"; }
function bg5_err($m)  { echo '  <span style="color:#e74c3c">✗ ' . esc_html($m) . '</span>' . "\n"; }
function bg5_info($m) { echo '  ' . esc_html($m) . "\n"; }
function bg5_head($m) { echo '<span style="color:#f1c40f">── ' . esc_html($m) . ' ──</span>' . "\n"; }

function bg5_get_el($id) {
    $raw = get_post_meta($id, '_elementor_data', true);
    if (!$raw) return [null, null];
    $d = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) return [null, null];
    return [$d, $raw];
}

function bg5_set_el($id, $data) {
    update_post_meta($id, '_elementor_data', wp_slash(wp_json_encode($data, JSON_UNESCAPED_UNICODE)));
    delete_post_meta($id, '_elementor_css');
}

function bg5_walk(array &$nodes, callable $fn) {
    foreach ($nodes as &$n) {
        $fn($n);
        if (!empty($n['elements'])) bg5_walk($n['elements'], $fn);
    }
}

// ─────────────────────────────────────────────────────────────
// FIX 3 — Footer Facebook icon
// Searches ALL post types including elementor-hf for:
//   widgetType = social-icons  →  social_icon_list items
//   widgetType = icon-list     →  icon_list items
// where icon is Facebook but URL is LinkedIn
// ─────────────────────────────────────────────────────────────
function bg5_fix3_footer_facebook() {
    bg5_head('FIX 3: Footer Facebook icon URL');

    global $wpdb;

    $WRONG_PARTIAL   = 'linkedin.com/company/brndguru';
    $CORRECT_URL     = 'https://www.facebook.com/brndguru';

    // ── Step 1: Find all posts with LinkedIn URL in Elementor data ──
    $rows = $wpdb->get_results(
        "SELECT pm.post_id, p.post_title, p.post_type, p.post_status
         FROM {$wpdb->postmeta} pm
         JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = '_elementor_data'
         AND pm.meta_value LIKE '%linkedin.com/company/brndguru%'
         AND p.post_type != 'revision'
         ORDER BY p.ID"
    );

    bg5_info('Posts with LinkedIn URL in _elementor_data: ' . count($rows));
    foreach ($rows as $r) {
        bg5_info('  ID:' . $r->post_id . ' type:' . $r->post_type . ' status:' . $r->post_status . ' "' . $r->post_title . '"');
    }

    if (empty($rows)) {
        bg5_info('');
        bg5_info('No LinkedIn URL found in any Elementor data.');
        bg5_info('Checking Astra settings as fallback...');
        bg5_fix3_astra_fallback($WRONG_PARTIAL, $CORRECT_URL);
        return;
    }

    $total_fixed = 0;

    foreach ($rows as $row) {
        [$data, $raw] = bg5_get_el($row->post_id);
        if (!$data) {
            bg5_err('Could not parse Elementor JSON for ID ' . $row->post_id);
            continue;
        }

        $fixed = 0;
        $dump_widgets = [];

        bg5_walk($data, function(&$n) use (&$fixed, &$dump_widgets, $WRONG_PARTIAL, $CORRECT_URL) {
            $wt = $n['widgetType'] ?? '';
            $s  =& $n['settings'];

            // ── social-icons widget ──
            if ($wt === 'social-icons' && !empty($s['social_icon_list'])) {
                foreach ($s['social_icon_list'] as &$item) {
                    $icon_val = '';
                    if (is_array($item['social_icon'] ?? null)) {
                        $icon_val = $item['social_icon']['value'] ?? '';
                    } elseif (is_string($item['social_icon'] ?? null)) {
                        $icon_val = $item['social_icon'];
                    }

                    $link_url = '';
                    if (is_array($item['link'] ?? null)) {
                        $link_url = $item['link']['url'] ?? '';
                    } elseif (is_string($item['link'] ?? null)) {
                        $link_url = $item['link'];
                    }

                    $dump_widgets[] = 'social-icons item: icon=' . $icon_val . ' url=' . $link_url;

                    // Facebook icon with wrong URL
                    $is_facebook = stripos($icon_val, 'facebook') !== false;
                    $has_wrong   = strpos($link_url, $WRONG_PARTIAL) !== false;

                    if ($is_facebook && $has_wrong) {
                        if (is_array($item['link'])) {
                            $item['link']['url'] = $CORRECT_URL;
                            $item['link']['is_external'] = 'on';
                        } else {
                            $item['link'] = ['url' => $CORRECT_URL, 'is_external' => 'on', 'nofollow' => ''];
                        }
                        $fixed++;
                    }

                    // Also fix: any icon with LinkedIn URL (regardless of icon type) — log it
                    if ($has_wrong && !$is_facebook) {
                        $dump_widgets[] = '  !! LinkedIn URL on non-Facebook icon: ' . $icon_val;
                    }
                }
                unset($item);
            }

            // ── icon-list widget ──
            if ($wt === 'icon-list' && !empty($s['icon_list'])) {
                foreach ($s['icon_list'] as &$item) {
                    $icon_val = '';
                    if (is_array($item['social_icon'] ?? null)) {
                        $icon_val = $item['social_icon']['value'] ?? '';
                    } elseif (is_array($item['icon'] ?? null)) {
                        $icon_val = $item['icon']['value'] ?? '';
                    } elseif (is_string($item['icon'] ?? null)) {
                        $icon_val = $item['icon'];
                    }

                    $link_url = '';
                    if (is_array($item['link'] ?? null)) {
                        $link_url = $item['link']['url'] ?? '';
                    }

                    $dump_widgets[] = 'icon-list item: icon=' . $icon_val . ' url=' . $link_url;

                    $is_facebook = stripos($icon_val, 'facebook') !== false;
                    $has_wrong   = strpos($link_url, $WRONG_PARTIAL) !== false;

                    if ($is_facebook && $has_wrong) {
                        $item['link']['url'] = $CORRECT_URL;
                        if (isset($item['link']['is_external'])) $item['link']['is_external'] = 'on';
                        $fixed++;
                    }
                }
                unset($item);
            }

            // ── Fallback: any widget with LinkedIn URL — brute-force string key scan ──
            if ($fixed === 0) {
                $serialized = maybe_serialize($s ?? []);
                if (strpos($serialized, $WRONG_PARTIAL) !== false) {
                    $dump_widgets[] = 'Widget "' . $wt . '" has LinkedIn URL. Settings keys: ' . implode(', ', array_keys($s ?? []));
                }
            }
        });

        // Log all widget dumps for diagnosis
        foreach ($dump_widgets as $msg) {
            bg5_info('    ' . $msg);
        }

        if ($fixed > 0) {
            bg5_set_el($row->post_id, $data);
            bg5_ok('Fixed ' . $fixed . ' Facebook icon(s) in "' . $row->post_title . '" (ID ' . $row->post_id . ', type: ' . $row->post_type . ')');
            $total_fixed += $fixed;
        } else {
            // Brute-force raw string replacement as last resort
            bg5_info('  Widget walk did not fix — trying raw JSON string replace...');
            $patterns = [
                'https:\/\/www.linkedin.com\/company\/brndguru\/' => 'https:\/\/www.facebook.com\/brndguru',
                'https:\/\/www.linkedin.com\/company\/brndguru'  => 'https:\/\/www.facebook.com\/brndguru',
                'https://www.linkedin.com/company/brndguru/'     => 'https://www.facebook.com/brndguru',
                'https://www.linkedin.com/company/brndguru'      => 'https://www.facebook.com/brndguru',
                'linkedin.com\/company\/brndguru'                => 'facebook.com\/brndguru',
                'linkedin.com/company/brndguru'                  => 'facebook.com/brndguru',
            ];
            $new_raw = $raw;
            $changed = false;
            foreach ($patterns as $find => $replace) {
                if (strpos($new_raw, $find) !== false) {
                    $new_raw = str_replace($find, $replace, $new_raw);
                    $changed = true;
                    bg5_info('  Replaced: "' . $find . '" → "' . $replace . '"');
                }
            }
            if ($changed) {
                update_post_meta($row->post_id, '_elementor_data', wp_slash($new_raw));
                delete_post_meta($row->post_id, '_elementor_css');
                bg5_ok('Fixed via raw string replace in "' . $row->post_title . '" (ID ' . $row->post_id . ')');
                $total_fixed++;
            } else {
                bg5_err('Could not fix "' . $row->post_title . '" (ID ' . $row->post_id . ') — check manually');
                // Show raw context around LinkedIn URL
                $pos = strpos($raw, 'linkedin.com');
                if ($pos !== false) {
                    bg5_info('  Raw context: ...' . substr($raw, max(0, $pos - 100), 300) . '...');
                }
            }
        }
    }

    bg5_info('');
    if ($total_fixed > 0) {
        bg5_ok('Fix 3 complete: ' . $total_fixed . ' post(s) updated.');
        bg5_info('Verify: Footer Facebook icon should now link to facebook.com/brndguru');
    } else {
        bg5_err('Fix 3: No changes made.');
        bg5_fix3_astra_fallback($WRONG_PARTIAL, $CORRECT_URL);
    }
    echo "\n";
}

function bg5_fix3_astra_fallback($WRONG_PARTIAL, $CORRECT_URL) {
    bg5_info('Checking Astra settings for LinkedIn URL...');
    $astra = get_option('astra-settings', []);
    $found = false;
    foreach ($astra as $k => $v) {
        $s = maybe_serialize($v);
        if (strpos($s, $WRONG_PARTIAL) !== false) {
            bg5_info('  Found in Astra key: ' . $k . ' = ' . substr($s, 0, 150));
            $found = true;
            if (is_array($v)) {
                array_walk_recursive($v, function(&$val) use ($WRONG_PARTIAL, $CORRECT_URL) {
                    if (is_string($val) && strpos($val, $WRONG_PARTIAL) !== false) {
                        $val = preg_replace('#https?://(?:www\.)?linkedin\.com/company/brndguru/?#', $CORRECT_URL, $val);
                    }
                });
                $astra[$k] = $v;
            } else {
                $astra[$k] = preg_replace('#https?://(?:www\.)?linkedin\.com/company/brndguru/?#', $CORRECT_URL, $v);
            }
        }
    }
    if ($found) {
        update_option('astra-settings', $astra);
        bg5_ok('LinkedIn URL replaced in Astra settings.');
    } else {
        bg5_err('LinkedIn URL not found in Astra settings either.');
        bg5_info('Manual step: WP Admin → Appearance → Customize → Footer → Social Icons');
        bg5_info('  Find the Facebook icon and change its URL to: https://www.facebook.com/brndguru');
    }
}

// ─────────────────────────────────────────────────────────────
// FLUSH
// ─────────────────────────────────────────────────────────────
function bg5_flush() {
    bg5_head('FLUSHING CACHES');
    wp_cache_flush(); bg5_ok('Object cache');
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    bg5_ok('Transients');
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        bg5_ok('Elementor CSS cache');
    }
    do_action('swift_performance_after_clean_cache');
    if (function_exists('rocket_clean_domain'))  { rocket_clean_domain(); bg5_ok('WP Rocket'); }
    if (class_exists('LiteSpeed_Cache_API'))     { LiteSpeed_Cache_API::purge_all(); bg5_ok('LiteSpeed'); }
    bg5_ok('Done');
    echo "\n";
}
