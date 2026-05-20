<?php
/**
 * Plugin Name: BrndGuru Site Fixes v6
 * Description: Fix 3 — Full DB search for LinkedIn/Facebook icon URL. Checks options, widgets, all meta. DELETE after running.
 * Version: 6.0
 */

if (!defined('ABSPATH')) exit;

add_filter('plugin_row_meta', function($links, $file) {
    if (strpos($file, 'brndguru-fixes') !== false) {
        $nonce = wp_create_nonce('bg6_run');
        $links[] = '<a href="' . admin_url('?bg6_run=1&bg6_nonce=' . $nonce) . '" style="font-weight:700;color:#00a32a;font-size:14px;">▶ RUN FIX 3 (v6)</a>';
    }
    return $links;
}, 10, 2);

add_action('admin_init', function() {
    if (!isset($_GET['bg6_run'])) return;
    if (!current_user_can('manage_options')) wp_die('Permission denied.');
    if (!wp_verify_nonce($_GET['bg6_nonce'] ?? '', 'bg6_run')) wp_die('Invalid nonce.');

    echo '<pre style="font-family:monospace;padding:20px;background:#0d1117;color:#58d68d;font-size:13px;line-height:1.7;">';
    echo "BrndGuru Site Fixes v6 — Fix 3: Full DB Search\n" . str_repeat('=', 60) . "\n\n";

    bg6_search_and_fix();
    bg6_flush();

    echo str_repeat('=', 60) . "\n";
    echo '<span style="color:#fff">DONE. Deactivate + delete this plugin now.</span>' . "\n";
    echo '</pre>';
    exit;
});

function bg6_ok($m)   { echo '  <span style="color:#2ecc71">✓ ' . esc_html($m) . '</span>' . "\n"; }
function bg6_err($m)  { echo '  <span style="color:#e74c3c">✗ ' . esc_html($m) . '</span>' . "\n"; }
function bg6_info($m) { echo '  ' . esc_html($m) . "\n"; }
function bg6_head($m) { echo '<span style="color:#f1c40f">── ' . esc_html($m) . ' ──</span>' . "\n"; }

function bg6_search_and_fix() {
    global $wpdb;

    $WRONG_PATTERNS = [
        'linkedin.com/company/brndguru',
        'linkedin.com\/company\/brndguru',
        'linkedin.com%2Fcompany%2Fbrndguru',
    ];
    $CORRECT = 'https://www.facebook.com/brndguru';
    $total_fixed = 0;

    // ── 1. SEARCH ALL post meta (not just _elementor_data) ──────────────
    bg6_head('STEP 1: All post meta containing "linkedin.com"');
    $meta_rows = $wpdb->get_results(
        "SELECT pm.post_id, pm.meta_key, p.post_title, p.post_type
         FROM {$wpdb->postmeta} pm
         JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_value LIKE '%linkedin.com%'
         AND p.post_type != 'revision'
         ORDER BY p.ID"
    );
    bg6_info('Found ' . count($meta_rows) . ' post meta row(s) with linkedin.com');
    foreach ($meta_rows as $r) {
        $val = get_post_meta($r->post_id, $r->meta_key, true);
        $serialized = maybe_serialize($val);
        bg6_info('  ID:' . $r->post_id . ' type:' . $r->post_type . ' key:' . $r->meta_key . ' "' . $r->post_title . '"');
        bg6_info('  Value (first 200): ' . substr($serialized, 0, 200));
    }
    echo "\n";

    // ── 2. SEARCH ALL wp_options containing "linkedin.com" ──────────────
    bg6_head('STEP 2: All wp_options containing "linkedin.com"');
    $opt_rows = $wpdb->get_results(
        "SELECT option_name, option_value
         FROM {$wpdb->options}
         WHERE option_value LIKE '%linkedin.com%'
         AND option_name NOT LIKE '_transient%'
         AND option_name NOT LIKE '_site_transient%'
         ORDER BY option_name
         LIMIT 50"
    );
    bg6_info('Found ' . count($opt_rows) . ' option(s) with linkedin.com');
    foreach ($opt_rows as $r) {
        bg6_info('  option: ' . $r->option_name . '  (length: ' . strlen($r->option_value) . ')');
        // Show context around linkedin.com
        $pos = strpos($r->option_value, 'linkedin.com');
        if ($pos !== false) {
            bg6_info('  context: ...' . substr($r->option_value, max(0, $pos - 60), 200) . '...');
        }
    }

    if ($opt_rows) {
        echo "\n";
        bg6_head('STEP 2b: Fixing options with LinkedIn → Facebook');
        foreach ($opt_rows as $r) {
            $opt_name = $r->option_name;
            $val = get_option($opt_name);
            $changed = false;

            if (is_array($val) || is_object($val)) {
                $serialized_before = maybe_serialize($val);
                array_walk_recursive($val, function(&$v) use ($CORRECT, &$changed) {
                    if (is_string($v) && strpos($v, 'linkedin.com') !== false) {
                        // Only fix if it looks like the Facebook icon URL (brndguru linkedin)
                        if (strpos($v, 'brndguru') !== false || strpos($v, 'company') !== false) {
                            $new_v = preg_replace('#https?://(?:www\.)?linkedin\.com/(?:company/)?brndguru/?#i', $CORRECT, $v);
                            if ($new_v !== $v) { $v = $new_v; $changed = true; }
                        }
                    }
                });
                if ($changed) {
                    update_option($opt_name, $val);
                    bg6_ok('Fixed in option (array): ' . $opt_name);
                    $total_fixed++;
                }
            } elseif (is_string($val)) {
                $new_val = preg_replace('#https?://(?:www\.)?linkedin\.com/(?:company/)?brndguru/?#i', $CORRECT, $val);
                if ($new_val !== $val) {
                    update_option($opt_name, $new_val);
                    bg6_ok('Fixed in option (string): ' . $opt_name);
                    $total_fixed++;
                    $changed = true;
                }
            }

            if (!$changed) {
                bg6_info('  No matching LinkedIn/brndguru pattern in: ' . $opt_name . ' (may be unrelated linkedin.com reference)');
            }
        }
    }
    echo "\n";

    // ── 3. CHECK WIDGET OPTIONS SPECIFICALLY ────────────────────────────
    bg6_head('STEP 3: WordPress Widget areas');
    $widget_opts = $wpdb->get_results(
        "SELECT option_name, option_value FROM {$wpdb->options}
         WHERE option_name LIKE 'widget_%'
         AND option_value LIKE '%linkedin%'
         LIMIT 20"
    );
    bg6_info('Widget options with linkedin: ' . count($widget_opts));
    foreach ($widget_opts as $r) {
        bg6_info('  ' . $r->option_name . ': ' . substr($r->option_value, 0, 200));
    }
    echo "\n";

    // ── 4. CHECK ELEMENTOR GLOBAL WIDGETS / TEMPLATES ───────────────────
    bg6_head('STEP 4: All post types with linkedin.com in ANY meta');
    $all_types = $wpdb->get_results(
        "SELECT DISTINCT p.post_type, p.post_status, COUNT(*) as cnt
         FROM {$wpdb->postmeta} pm
         JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_value LIKE '%linkedin%'
         GROUP BY p.post_type, p.post_status"
    );
    if ($all_types) {
        foreach ($all_types as $r) {
            bg6_info('  post_type=' . $r->post_type . ' status=' . $r->post_status . ' count=' . $r->cnt);
        }
    } else {
        bg6_info('No post meta with linkedin found at all.');
    }
    echo "\n";

    // ── 5. CHECK ASTRA SETTINGS IN DETAIL ───────────────────────────────
    bg6_head('STEP 5: Full Astra settings dump (any key with URL/social/icon value)');
    $astra = get_option('astra-settings', []);
    bg6_info('Total Astra setting keys: ' . count($astra));

    // Show ALL keys that contain a URL or icon-like value
    $shown = 0;
    foreach ($astra as $k => $v) {
        $s = maybe_serialize($v);
        if (
            strpos($s, 'http') !== false ||
            strpos($s, 'icon') !== false ||
            strpos($s, 'social') !== false ||
            strpos($s, 'facebook') !== false ||
            strpos($s, 'footer') !== false
        ) {
            if (strlen($s) < 500) {
                bg6_info('  [' . $k . '] = ' . substr($s, 0, 300));
                $shown++;
            }
        }
    }
    if ($shown === 0) bg6_info('  (No URL/icon/social keys found in Astra)');
    echo "\n";

    // ── 6. CHECK THEME MODS ─────────────────────────────────────────────
    bg6_head('STEP 6: Theme mods with linkedin or facebook');
    $theme = get_option('theme_mods_' . get_stylesheet(), []);
    bg6_info('Theme mods keys: ' . count($theme));
    foreach ($theme as $k => $v) {
        $s = maybe_serialize($v);
        if (strpos($s, 'linkedin') !== false || strpos($s, 'facebook') !== false || strpos($s, 'social') !== false) {
            bg6_info('  [' . $k . '] = ' . substr($s, 0, 300));
        }
    }
    echo "\n";

    // ── 7. DIRECT FIX: elementor-hf post type raw DB ────────────────────
    bg6_head('STEP 7: elementor-hf posts — raw content check');
    $ehf_posts = $wpdb->get_results(
        "SELECT ID, post_title, post_status FROM {$wpdb->posts}
         WHERE post_type = 'elementor-hf'
         AND post_status IN ('publish','draft')
         ORDER BY ID"
    );
    bg6_info('elementor-hf posts: ' . count($ehf_posts));
    foreach ($ehf_posts as $p) {
        bg6_info('  ID:' . $p->ID . ' "' . $p->post_title . '" status:' . $p->post_status);

        // Get raw post content too (some themes store data in post_content)
        $post_obj = get_post($p->ID);
        if ($post_obj && strpos($post_obj->post_content, 'linkedin') !== false) {
            $pos = strpos($post_obj->post_content, 'linkedin');
            bg6_info('  post_content has linkedin: ...' . substr($post_obj->post_content, max(0,$pos-50), 200) . '...');
        }

        // All meta for this post
        $all_meta = get_post_meta($p->ID);
        foreach ($all_meta as $mk => $mv) {
            $s = maybe_serialize($mv);
            if (strpos($s, 'linkedin') !== false || strpos($s, 'facebook') !== false || strpos($s, 'social') !== false) {
                bg6_info('  meta[' . $mk . ']: ' . substr($s, 0, 300));
            }
        }
    }
    echo "\n";

    if ($total_fixed > 0) {
        bg6_ok('TOTAL: ' . $total_fixed . ' location(s) fixed.');
    } else {
        bg6_err('LinkedIn URL not found in any automated location.');
        bg6_info('');
        bg6_info('MANUAL FIX — Footer Facebook icon:');
        bg6_info('  Option A: Elementor Footer editor');
        bg6_info('    1. WP Admin → Pages or Templates → find Footer (ID 46)');
        bg6_info('    2. Edit with Elementor → click the social icons widget');
        bg6_info('    3. Find the Facebook icon → change URL to: https://www.facebook.com/brndguru');
        bg6_info('    4. Update');
        bg6_info('');
        bg6_info('  Option B: Astra Customizer');
        bg6_info('    1. Appearance → Customize → Footer Builder');
        bg6_info('    2. Click the social icons area → edit Facebook URL');
        bg6_info('    3. Publish');
        bg6_info('');
        bg6_info('  Option C: If using Astra Social widget in a sidebar');
        bg6_info('    1. Appearance → Widgets → Footer widget area');
        bg6_info('    2. Find any social icon widget → edit Facebook URL');
    }
}

function bg6_flush() {
    bg6_head('FLUSHING CACHES');
    wp_cache_flush(); bg6_ok('Object cache');
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    bg6_ok('Transients');
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        bg6_ok('Elementor CSS cache');
    }
    do_action('swift_performance_after_clean_cache');
    bg6_ok('Done');
    echo "\n";
}
