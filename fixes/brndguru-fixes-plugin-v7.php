<?php
/**
 * Plugin Name: BrndGuru Site Fixes v7
 * Description: Fix 3 FINAL — Patch footer post ID 46 _elementor_data + post_content. DELETE after running.
 * Version: 7.0
 */

if (!defined('ABSPATH')) exit;

add_filter('plugin_row_meta', function($links, $file) {
    if (strpos($file, 'brndguru-fixes') !== false) {
        $nonce = wp_create_nonce('bg7_run');
        $links[] = '<a href="' . admin_url('?bg7_run=1&bg7_nonce=' . $nonce) . '" style="font-weight:700;color:#00a32a;font-size:14px;">▶ RUN FIX 3 FINAL</a>';
    }
    return $links;
}, 10, 2);

add_action('admin_init', function() {
    if (!isset($_GET['bg7_run'])) return;
    if (!current_user_can('manage_options')) wp_die('Permission denied.');
    if (!wp_verify_nonce($_GET['bg7_nonce'] ?? '', 'bg7_run')) wp_die('Invalid nonce.');

    echo '<pre style="font-family:monospace;padding:20px;background:#0d1117;color:#58d68d;font-size:13px;line-height:1.7;">';
    echo "BrndGuru Site Fixes v7 — Fix 3 FINAL\n" . str_repeat('=', 60) . "\n\n";

    bg7_fix3();
    bg7_flush();

    echo str_repeat('=', 60) . "\n";
    echo '<span style="color:#fff">DONE. Deactivate + delete this plugin now.</span>' . "\n";
    echo '</pre>';
    exit;
});

function bg7_ok($m)   { echo '  <span style="color:#2ecc71">✓ ' . esc_html($m) . '</span>' . "\n"; }
function bg7_err($m)  { echo '  <span style="color:#e74c3c">✗ ' . esc_html($m) . '</span>' . "\n"; }
function bg7_info($m) { echo '  ' . esc_html($m) . "\n"; }
function bg7_head($m) { echo '<span style="color:#f1c40f">── ' . esc_html($m) . ' ──</span>' . "\n"; }

function bg7_fix3() {
    bg7_head('FIX 3: Footer Facebook icon — patch post ID 46');

    $POST_ID  = 46;
    $WRONG    = 'linkedin.com/company/brndguru';   // unescaped form
    $CORRECT  = 'https://www.facebook.com/brndguru';

    // ── Patch _elementor_data (stored with escaped slashes \/) ──────────
    $raw_meta = get_post_meta($POST_ID, '_elementor_data', true);
    if (!$raw_meta) {
        bg7_err('No _elementor_data found on post ID ' . $POST_ID);
        return;
    }

    bg7_info('_elementor_data length: ' . strlen($raw_meta));
    bg7_info('Contains "linkedin": ' . (strpos($raw_meta, 'linkedin') !== false ? 'YES' : 'NO'));

    // Count occurrences before
    $before = substr_count($raw_meta, 'linkedin');

    // Replace all URL variants — escaped and unescaped, with/without https/www/trailing slash
    $replacements = [
        // Escaped slash variants (as stored in Elementor JSON)
        'https:\/\/www.linkedin.com\/company\/brndguru\/'  => 'https:\/\/www.facebook.com\/brndguru',
        'https:\/\/www.linkedin.com\/company\/brndguru'    => 'https:\/\/www.facebook.com\/brndguru',
        'https:\/\/linkedin.com\/company\/brndguru\/'      => 'https:\/\/www.facebook.com\/brndguru',
        'https:\/\/linkedin.com\/company\/brndguru'        => 'https:\/\/www.facebook.com\/brndguru',
        // Unescaped variants (fallback)
        'https://www.linkedin.com/company/brndguru/'       => 'https://www.facebook.com/brndguru',
        'https://www.linkedin.com/company/brndguru'        => 'https://www.facebook.com/brndguru',
        'https://linkedin.com/company/brndguru/'           => 'https://www.facebook.com/brndguru',
        'https://linkedin.com/company/brndguru'            => 'https://www.facebook.com/brndguru',
    ];

    $new_meta = $raw_meta;
    foreach ($replacements as $find => $replace) {
        if (strpos($new_meta, $find) !== false) {
            $new_meta = str_replace($find, $replace, $new_meta);
            bg7_info('  Replaced: "' . $find . '"');
        }
    }

    $after = substr_count($new_meta, 'linkedin');
    bg7_info('"linkedin" occurrences: before=' . $before . '  after=' . $after);

    if ($new_meta !== $raw_meta) {
        // wp_slash re-escapes for DB storage — but $new_meta is already a raw JSON string
        // Use update_post_meta with wp_slash to handle DB escaping correctly
        update_post_meta($POST_ID, '_elementor_data', wp_slash($new_meta));
        delete_post_meta($POST_ID, '_elementor_css');
        bg7_ok('_elementor_data updated on post ID ' . $POST_ID);
    } else {
        bg7_err('_elementor_data unchanged — no LinkedIn URL matched');
        // Show 200-char context for debugging
        $pos = strpos($raw_meta, 'linkedin');
        if ($pos !== false) {
            bg7_info('  Context: ...' . substr($raw_meta, max(0, $pos-80), 300) . '...');
        }
    }

    // ── Patch post_content (rendered HTML cached by Elementor) ──────────
    $post = get_post($POST_ID);
    if ($post && strpos($post->post_content, 'linkedin') !== false) {
        bg7_info('');
        bg7_info('post_content also contains LinkedIn URL — patching...');
        $new_content = $post->post_content;
        foreach ($replacements as $find => $replace) {
            // post_content uses unescaped URLs
            $find_plain    = str_replace('\/', '/', $find);
            $replace_plain = str_replace('\/', '/', $replace);
            if (strpos($new_content, $find_plain) !== false) {
                $new_content = str_replace($find_plain, $replace_plain, $new_content);
                bg7_info('  post_content replaced: "' . $find_plain . '"');
            }
        }
        if ($new_content !== $post->post_content) {
            wp_update_post(['ID' => $POST_ID, 'post_content' => $new_content]);
            bg7_ok('post_content updated on post ID ' . $POST_ID);
        }
    } else {
        bg7_info('post_content: no LinkedIn URL (or already clean)');
    }

    // ── Also fix any revisions that might be cached ──────────────────────
    global $wpdb;
    $rev_count = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, 'linkedin.com/company/brndguru', 'facebook.com/brndguru')
             WHERE post_parent = %d AND post_type = 'revision'",
            $POST_ID
        )
    );
    if ($rev_count > 0) bg7_info('Updated ' . $rev_count . ' revision(s)');

    echo "\n";
    bg7_ok('Fix 3 complete. Verify: footer Facebook icon should now link to facebook.com/brndguru');
    echo "\n";
}

function bg7_flush() {
    bg7_head('FLUSHING CACHES');
    wp_cache_flush(); bg7_ok('Object cache');
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    bg7_ok('Transients');
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        bg7_ok('Elementor CSS cache');
    }
    do_action('swift_performance_after_clean_cache');
    if (function_exists('rocket_clean_domain')) { rocket_clean_domain(); bg7_ok('WP Rocket'); }
    if (class_exists('LiteSpeed_Cache_API')) { LiteSpeed_Cache_API::purge_all(); bg7_ok('LiteSpeed'); }
    bg7_ok('Done');
    echo "\n";
}
