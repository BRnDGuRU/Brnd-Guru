<?php
/**
 * Plugin Name: BrndGuru — Gold to Orange Color Fix
 * Description: Replaces all gold (#e4b84d) with orange (#e4522b) across every page, widget, and theme setting. AUTO-RUNS on activation.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgcf_run');
add_action('admin_init', function () {
    if (get_option('bgcf_done') !== '1') bgcf_run();
});

function bgcf_run() {
    global $wpdb;
    $log = [];

    // All gold/yellow color variants → orange
    $color_map = [
        '#e4b84d' => '#e4522b',
        '#E4B84D' => '#e4522b',
        'e4b84d'  => 'e4522b',
        'E4B84D'  => 'e4522b',
        // RGB variants
        'rgb(228, 184, 77)'  => 'rgb(228, 82, 43)',
        'rgb(228,184,77)'    => 'rgb(228,82,43)',
        // Any other gold shades that might appear
        '#e4b84e' => '#e4522b',
        '#d4a93d' => '#e4522b',
        '#c9992d' => '#e4522b',
        '#f0c040' => '#e4522b',
        '#ffc107' => '#e4522b',
        // Gold in rgba
        'rgba(228, 184, 77' => 'rgba(228, 82, 43',
        'rgba(228,184,77'   => 'rgba(228,82,43',
    ];

    // ── 1. Replace in ALL postmeta (Elementor data, theme settings, etc.) ──
    $meta_count = 0;
    foreach ($color_map as $old => $new) {
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT meta_id, post_id, meta_key, meta_value FROM {$wpdb->postmeta}
             WHERE meta_value LIKE %s LIMIT 200",
            '%' . $wpdb->esc_like($old) . '%'
        ));
        foreach ($rows as $row) {
            $new_val = str_replace($old, $new, $row->meta_value);
            if ($new_val !== $row->meta_value) {
                $wpdb->update($wpdb->postmeta, ['meta_value' => $new_val], ['meta_id' => $row->meta_id]);
                clean_post_cache($row->post_id);
                $meta_count++;
            }
        }
    }
    $log[] = "✓ postmeta rows updated: {$meta_count}";

    // ── 2. Replace in posts table (post_content) ──
    $posts_count = 0;
    foreach ($color_map as $old => $new) {
        $result = $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->posts}
             SET post_content = REPLACE(post_content, %s, %s),
                 post_modified = %s
             WHERE post_content LIKE %s
             AND post_status NOT IN ('auto-draft')",
            $old, $new, current_time('mysql'),
            '%' . $wpdb->esc_like($old) . '%'
        ));
        $posts_count += (int)$result;
    }
    $log[] = "✓ posts rows updated: {$posts_count}";

    // ── 3. Replace in wp_options (theme_mods, Elementor global settings, Astra options) ──
    $options_count = 0;
    $option_rows = $wpdb->get_results(
        "SELECT option_id, option_name, option_value FROM {$wpdb->options}
         WHERE (option_name LIKE '%elementor%'
            OR option_name LIKE '%astra%'
            OR option_name LIKE '%theme_mod%'
            OR option_name LIKE '%customize%'
            OR option_name LIKE '%color%'
            OR option_name LIKE '%brnd%')
         AND option_value LIKE '%e4b84d%'
         LIMIT 100"
    );
    foreach ($option_rows as $opt) {
        $new_val = $opt->option_value;
        foreach ($color_map as $old => $new) {
            $new_val = str_replace($old, $new, $new_val);
        }
        if ($new_val !== $opt->option_value) {
            $wpdb->update($wpdb->options, ['option_value' => $new_val], ['option_id' => $opt->option_id]);
            $options_count++;
            $log[] = "✓ option: {$opt->option_name}";
        }
    }
    $log[] = "Options rows updated: {$options_count}";

    // ── 4. Also catch uppercase variants in options ──
    $wpdb->query(
        "UPDATE {$wpdb->options}
         SET option_value = REPLACE(option_value, '#E4B84D', '#e4522b')
         WHERE option_value LIKE '%E4B84D%'"
    );

    // ── 5. Clear EVERY cache layer ──
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        $log[] = "✓ Elementor CSS cache cleared";
    }
    // Swift Performance — wipe cache directory
    foreach ([
        WP_CONTENT_DIR . '/cache/swift-performance/',
        WP_CONTENT_DIR . '/cache/swift-performance-lite/',
    ] as $dir) {
        if (is_dir($dir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $f) {
                $f->isDir() ? rmdir($f) : unlink($f);
            }
            $log[] = "✓ Swift Performance cache dir wiped: " . basename($dir);
        }
    }
    if (class_exists('Swift_Performance')) {
        do_action('swift_performance_clear_all_cache');
    }
    // Other caches
    if (function_exists('rocket_clean_domain'))  rocket_clean_domain();
    if (function_exists('w3tc_flush_all'))        w3tc_flush_all();
    if (function_exists('wp_cache_clear_cache'))  wp_cache_clear_cache();
    if (function_exists('opcache_reset'))         opcache_reset();
    wp_cache_flush();
    delete_option('_transient_elementor_css');
    flush_rewrite_rules(true);

    $log[] = "✓ All caches cleared";

    update_option('bgcf_log', $log);
    update_option('bgcf_done', '1');
}

add_action('admin_notices', function () {
    if (get_option('bgcf_done') !== '1') return;
    $log = get_option('bgcf_log', []);
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px 16px;">
        <h3 style="margin:0 0 8px;">✅ Gold → Orange Color Replace Complete!</h3>
        <ul style="margin:0 0 10px;padding-left:20px;font-size:13px;font-family:monospace;max-height:260px;overflow-y:auto;">
            <?php foreach ($log as $line): ?>
                <li><?php echo esc_html($line); ?></li>
            <?php endforeach; ?>
        </ul>
        <p>
            <a href="<?php echo home_url('/'); ?>" target="_blank" class="button button-primary">View Site →</a>
            <a href="<?php echo home_url('/services/'); ?>" target="_blank" class="button">Services →</a>
            &nbsp; Open in <strong>incognito</strong> to confirm. Deactivate + delete when done.
        </p>
    </div>
    <?php
});
