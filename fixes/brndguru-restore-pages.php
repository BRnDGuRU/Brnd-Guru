<?php
/**
 * Plugin Name: BrndGuru — Restore Home About Contact Services
 * Description: AUTO-RUNS on activation. Restores Home, About, Contact & Services pages from their oldest WordPress revision (original Elementor content).
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgrp_run');

add_action('admin_init', function () {
    if (get_option('bgrp_done') !== '1') {
        bgrp_run();
    }
});

function bgrp_run() {
    global $wpdb;

    $targets = [
        12   => 'Home',
        1628 => 'About',
        23   => 'Contact',
        4325 => 'Services',
    ];

    $log = [];

    foreach ($targets as $page_id => $label) {
        $post = get_post($page_id);
        if (!$post) {
            $log[] = "⚠ {$label} (ID:{$page_id}) — page not found, skipping.";
            continue;
        }

        // ── Get ALL revisions for this page, oldest first ────────
        $revisions = wp_get_post_revisions($page_id, [
            'order'          => 'ASC',   // oldest first
            'posts_per_page' => 100,
            'post_status'    => 'any',
        ]);

        if (empty($revisions)) {
            // No revisions — just fix the template and clear plugin-injected content
            update_post_meta($page_id, '_wp_page_template', 'elementor_full_width');
            $log[] = "⚠ {$label} (ID:{$page_id}) — no revisions found. Template fixed to elementor_full_width.";
            continue;
        }

        // ── Find the best revision to restore ───────────────────
        // Priority: oldest revision that HAS Elementor data
        $best_rev = null;

        foreach ($revisions as $rev) {
            $elem = get_metadata('post', $rev->ID, '_elementor_data', true);
            if ($elem && strlen($elem) > 100) {   // non-trivial Elementor data
                $best_rev = $rev;
                break;  // oldest one with real Elementor data
            }
        }

        // Fallback: oldest revision regardless
        if (!$best_rev) {
            $best_rev = reset($revisions);
        }

        $rev_elem = get_metadata('post', $best_rev->ID, '_elementor_data', true);
        $rev_template = get_metadata('post', $best_rev->ID, '_wp_page_template', true);

        // ── Restore post content + title ─────────────────────────
        $wpdb->update(
            $wpdb->posts,
            [
                'post_content'  => $best_rev->post_content,
                'post_title'    => $best_rev->post_title,
                'post_modified' => current_time('mysql'),
            ],
            ['ID' => $page_id]
        );
        clean_post_cache($page_id);

        // ── Restore Elementor data ────────────────────────────────
        if ($rev_elem && strlen($rev_elem) > 100) {
            // Delete then re-insert to avoid wp_slash doubling
            $wpdb->delete($wpdb->postmeta, ['post_id' => $page_id, 'meta_key' => '_elementor_data']);
            $wpdb->insert($wpdb->postmeta, [
                'post_id'    => $page_id,
                'meta_key'   => '_elementor_data',
                'meta_value' => $rev_elem,   // already stored with wp_slash from original save
            ]);
            update_post_meta($page_id, '_elementor_edit_mode', 'builder');
            $data_note = '✓ Elementor data restored (' . round(strlen($rev_elem)/1024, 1) . 'KB)';
        } else {
            // No elementor data in revision — clear any plugin-generated data
            delete_post_meta($page_id, '_elementor_data');
            update_post_meta($page_id, '_elementor_edit_mode', '');
            $data_note = '⚠ No Elementor data in revision — cleared plugin-generated content';
        }

        // ── Restore template (ensure header shows) ───────────────
        if ($rev_template && $rev_template !== 'elementor_canvas' && $rev_template !== '') {
            update_post_meta($page_id, '_wp_page_template', $rev_template);
            $tmpl_note = $rev_template;
        } else {
            update_post_meta($page_id, '_wp_page_template', 'elementor_full_width');
            $tmpl_note = 'elementor_full_width (forced)';
        }

        // ── Clear per-page Elementor CSS cache ───────────────────
        delete_post_meta($page_id, '_elementor_css');

        // ── Clear Astra per-page header overrides ────────────────
        delete_post_meta($page_id, 'ast-main-header-display');
        delete_post_meta($page_id, 'ast-hfb-above-header-display');
        delete_post_meta($page_id, 'ast-hfb-below-header-display');
        delete_post_meta($page_id, '_elementor_page_settings');

        $log[] = "✓ {$label} (ID:{$page_id}) — restored from Rev#{$best_rev->ID} ({$best_rev->post_modified}) | {$data_note} | template: {$tmpl_note}";
    }

    // ── Global cache flush ────────────────────────────────────────
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
    if (class_exists('Swift_Performance')) {
        do_action('swift_performance_clear_all_cache');
    }
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    flush_rewrite_rules(true);

    update_option('bgrp_log', $log);
    update_option('bgrp_done', '1');
}

add_action('admin_notices', function () {
    if (get_option('bgrp_done') !== '1') return;
    $log = get_option('bgrp_log', []);
    ?>
    <div class="notice notice-success is-dismissible" style="padding:12px 16px;">
        <h3 style="margin:0 0 8px;">✅ BrndGuru — Pages Restored!</h3>
        <ul style="margin:0 0 10px;padding-left:20px;">
            <?php foreach ($log as $line) : ?>
                <li><?php echo esc_html($line); ?></li>
            <?php endforeach; ?>
        </ul>
        <p style="margin:4px 0;">
            <a href="<?php echo home_url('/'); ?>" target="_blank" class="button button-primary">👁 View Home →</a>
            <a href="<?php echo home_url('/about/'); ?>" target="_blank" class="button">About</a>
            <a href="<?php echo home_url('/services/'); ?>" target="_blank" class="button">Services</a>
            <a href="<?php echo home_url('/contact/'); ?>" target="_blank" class="button">Contact</a>
            &nbsp; Then deactivate + delete this plugin.
        </p>
    </div>
    <?php
});
