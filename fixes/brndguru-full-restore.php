<?php
/**
 * Plugin Name: BrndGuru — Full Site Restore
 * Description: Restores original Elementor data from WordPress revisions for all affected pages
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_menu_page('BG Full Restore', '🔴 BG RESTORE', 'manage_options', 'bg-full-restore', 'bg_restore_page', 'dashicons-backup', 1);
});

// Pages affected by the rebuild plugins
function bg_affected_pages() {
    return [
        12   => 'Home',
        1628 => 'About',
        4325 => 'Services',
        23   => 'Contact',
        762  => 'Brand Design',
        724  => 'UI/UX Design',
        766  => 'No-Code Development',
        765  => 'Webflow Development',
        2970 => 'Shopify Xcelerator',
        21   => 'Careers',
        1483 => 'Portfolio',
    ];
}

function bg_restore_page() {
    ?>
    <div class="wrap">
        <h1 style="color:#d63638;">🔴 BrndGuru — Full Site Restore</h1>
        <p style="font-size:15px;">This tool restores each page from its WordPress revision history — bringing back the <strong>original Elementor content and header</strong>.</p>

        <?php
        // Handle restore action
        if (isset($_POST['bg_restore_revision']) && check_admin_referer('bg_restore_nonce')) {
            $page_id = intval($_POST['page_id']);
            $rev_id  = intval($_POST['revision_id']);
            bg_do_restore_revision($page_id, $rev_id);
        }

        // Handle restore-all action
        if (isset($_POST['bg_restore_all']) && check_admin_referer('bg_restore_all_nonce')) {
            bg_do_restore_all();
        }

        // Show restore-all button
        $pages = bg_affected_pages();
        ?>

        <hr>
        <h2>Option A — One-Click: Restore ALL Pages to Original</h2>
        <p>Finds the oldest available revision for each page (the original content before any plugin ran) and restores it.</p>
        <form method="post" onsubmit="return confirm('Restore ALL pages to their original content? This cannot be undone.');">
            <?php wp_nonce_field('bg_restore_all_nonce'); ?>
            <input type="hidden" name="bg_restore_all" value="1">
            <p><input type="submit" class="button button-primary button-hero" style="background:#d63638;border-color:#d63638;" value="🔴 RESTORE ALL PAGES TO ORIGINAL"></p>
        </form>

        <hr>
        <h2>Option B — Per-Page: Choose a Specific Revision</h2>
        <p>Pick a revision date/time for each page. Choose the one <strong>before</strong> today's date (<?php echo date('Y-m-d'); ?>) to restore the original.</p>

        <?php foreach ($pages as $page_id => $page_name) :
            $revisions = wp_get_post_revisions($page_id, ['order' => 'DESC', 'posts_per_page' => 20]);
            $current   = get_post($page_id);
            if (!$current) { echo "<p>⚠ Page ID:{$page_id} ({$page_name}) not found.</p>"; continue; }
            $template  = get_post_meta($page_id, '_wp_page_template', true);
            $has_elem  = get_post_meta($page_id, '_elementor_data', true) ? 'yes' : 'no';
            ?>
            <div style="border:1px solid #ccc;border-radius:8px;padding:16px 20px;margin-bottom:20px;">
                <h3 style="margin:0 0 4px;"><?php echo esc_html($page_name); ?> <small style="color:#999;">(ID: <?php echo $page_id; ?>)</small></h3>
                <p style="margin:0 0 10px;color:#666;font-size:13px;">
                    Template: <code><?php echo esc_html($template ?: '(default)'); ?></code> &nbsp;|&nbsp;
                    Elementor data: <strong><?php echo $has_elem; ?></strong> &nbsp;|&nbsp;
                    Modified: <?php echo esc_html($current->post_modified); ?>
                </p>

                <?php if (empty($revisions)) : ?>
                    <p style="color:#d63638;">⚠ No revisions found for this page.</p>
                <?php else : ?>
                    <form method="post">
                        <?php wp_nonce_field('bg_restore_nonce'); ?>
                        <input type="hidden" name="page_id" value="<?php echo $page_id; ?>">
                        <select name="revision_id" style="width:400px;height:36px;font-size:14px;">
                            <?php foreach ($revisions as $rev) :
                                $rev_elem = get_metadata('post', $rev->ID, '_elementor_data', true);
                                $has_rev_elem = $rev_elem ? '✓ Elementor data' : '✗ no Elementor data';
                                ?>
                                <option value="<?php echo $rev->ID; ?>">
                                    <?php echo esc_html($rev->post_modified); ?> — Rev#<?php echo $rev->ID; ?> — <?php echo $has_rev_elem; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="bg_restore_revision" value="1">
                        <input type="submit" class="button button-secondary" value="Restore This Page">
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

function bg_do_restore_revision($page_id, $rev_id) {
    $rev = get_post($rev_id);
    if (!$rev || $rev->post_parent != $page_id) {
        echo '<div class="notice notice-error"><p>❌ Invalid revision.</p></div>';
        return;
    }

    // Restore post content
    wp_update_post([
        'ID'           => $page_id,
        'post_content' => $rev->post_content,
        'post_title'   => $rev->post_title,
    ]);

    // Restore Elementor data from revision
    $elem_data = get_metadata('post', $rev_id, '_elementor_data', true);
    if ($elem_data) {
        update_post_meta($page_id, '_elementor_data', wp_slash($elem_data));
        update_post_meta($page_id, '_elementor_edit_mode', 'builder');
    } else {
        delete_post_meta($page_id, '_elementor_data');
        update_post_meta($page_id, '_elementor_edit_mode', '');
    }

    // Restore template - use elementor_full_width (shows Astra header) if it was elementor_canvas
    $rev_template = get_metadata('post', $rev_id, '_wp_page_template', true);
    if ($rev_template && $rev_template !== 'elementor_canvas') {
        update_post_meta($page_id, '_wp_page_template', $rev_template);
    } else {
        // Default to elementor_full_width to ensure header shows
        update_post_meta($page_id, '_wp_page_template', 'elementor_full_width');
    }

    // Restore elementor CSS
    delete_post_meta($page_id, '_elementor_css');

    // Clear Elementor cache
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }

    $page = get_post($page_id);
    echo '<div class="notice notice-success"><p>✅ <strong>' . esc_html($page->post_title) . '</strong> (ID:'.$page_id.') restored from revision #'.$rev_id.' dated '.esc_html($rev->post_modified).'</p></div>';
}

function bg_do_restore_all() {
    $pages = bg_affected_pages();
    $results = [];

    foreach ($pages as $page_id => $page_name) {
        // Get revisions sorted oldest first to find original content
        $revisions = wp_get_post_revisions($page_id, [
            'order'          => 'ASC',
            'posts_per_page' => 50,
        ]);

        if (empty($revisions)) {
            // No revisions — at least fix the template
            $current_template = get_post_meta($page_id, '_wp_page_template', true);
            if ($current_template === 'elementor_canvas') {
                update_post_meta($page_id, '_wp_page_template', 'elementor_full_width');
                $results[] = ['page' => $page_name, 'id' => $page_id, 'status' => 'template_fixed', 'note' => 'No revisions — template fixed to elementor_full_width'];
            } else {
                $results[] = ['page' => $page_name, 'id' => $page_id, 'status' => 'skip', 'note' => 'No revisions found'];
            }
            continue;
        }

        // Find the OLDEST revision that has Elementor data (the original)
        $target_rev = null;
        foreach ($revisions as $rev) {
            $elem_data = get_metadata('post', $rev->ID, '_elementor_data', true);
            if ($elem_data) {
                $target_rev = $rev;
                break; // oldest revision with Elementor data
            }
        }

        // If no revision has Elementor data, use the oldest revision regardless
        if (!$target_rev) {
            $target_rev = reset($revisions);
        }

        // Restore post content
        wp_update_post([
            'ID'           => $page_id,
            'post_content' => $target_rev->post_content,
            'post_title'   => $target_rev->post_title,
        ]);

        // Restore Elementor data
        $elem_data = get_metadata('post', $target_rev->ID, '_elementor_data', true);
        if ($elem_data) {
            update_post_meta($page_id, '_elementor_data', wp_slash($elem_data));
            update_post_meta($page_id, '_elementor_edit_mode', 'builder');
        } else {
            delete_post_meta($page_id, '_elementor_data');
            update_post_meta($page_id, '_elementor_edit_mode', '');
        }

        // Fix template — always use elementor_full_width to restore header
        $rev_template = get_metadata('post', $target_rev->ID, '_wp_page_template', true);
        if ($rev_template && $rev_template !== 'elementor_canvas') {
            update_post_meta($page_id, '_wp_page_template', $rev_template);
        } else {
            update_post_meta($page_id, '_wp_page_template', 'elementor_full_width');
        }

        // Clear CSS cache for this page
        delete_post_meta($page_id, '_elementor_css');

        $results[] = [
            'page'   => $page_name,
            'id'     => $page_id,
            'status' => 'restored',
            'note'   => 'Restored from revision #' . $target_rev->ID . ' (' . $target_rev->post_modified . ')',
            'elem'   => $elem_data ? 'yes' : 'no',
        ];
    }

    // Clear all Elementor caches
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
    if (class_exists('Swift_Performance')) {
        do_action('swift_performance_clear_all_cache');
    }
    flush_rewrite_rules(true);

    echo '<div class="notice notice-success is-dismissible">';
    echo '<h3>✅ Restore Complete</h3>';
    echo '<table class="widefat" style="margin:10px 0;">';
    echo '<thead><tr><th>Page</th><th>ID</th><th>Status</th><th>Elementor</th><th>Note</th></tr></thead><tbody>';
    foreach ($results as $r) {
        $color = $r['status'] === 'restored' ? '#2ecc71' : ($r['status'] === 'template_fixed' ? '#f39c12' : '#e74c3c');
        echo '<tr>';
        echo '<td><strong>' . esc_html($r['page']) . '</strong></td>';
        echo '<td>' . esc_html($r['id']) . '</td>';
        echo '<td style="color:' . $color . '"><strong>' . esc_html($r['status']) . '</strong></td>';
        echo '<td>' . esc_html($r['elem'] ?? '-') . '</td>';
        echo '<td>' . esc_html($r['note']) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '<p style="font-size:15px;"><a href="' . home_url('/') . '" target="_blank" class="button button-primary">👁 View Site Now →</a></p>';
    echo '</div>';
}
