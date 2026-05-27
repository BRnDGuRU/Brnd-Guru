<?php
/**
 * Plugin Name: BrndGuru — Content Audit
 * Description: Reads and displays all text content from Elementor widgets on each page so content can be replaced accurately
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_menu_page('BG Content Audit', '📋 BG Content Audit', 'manage_options', 'bg-content-audit', 'bg_audit_page', 'dashicons-list-view', 2);
});

function bg_audit_page() {
    $pages = [
        12   => 'Home',
        1628 => 'About',
        4325 => 'Services',
        23   => 'Contact',
    ];
    ?>
    <div class="wrap">
        <h1>📋 BrndGuru — Current Content Audit</h1>
        <p>Copy everything below and send to Claude so the content can be replaced accurately while keeping all styles.</p>
        <textarea id="audit-output" style="width:100%;height:600px;font-family:monospace;font-size:12px;background:#0d1117;color:#58d68d;padding:16px;border-radius:8px;border:1px solid #333;"><?php
        foreach ($pages as $id => $name) {
            echo "=== PAGE: {$name} (ID:{$id}) ===\n";
            $data = get_post_meta($id, '_elementor_data', true);
            if (!$data) {
                echo "  [NO ELEMENTOR DATA — uses post_content]\n";
                $post = get_post($id);
                $text = wp_strip_all_tags($post->post_content ?? '');
                echo "  " . substr(trim($text), 0, 500) . "\n\n";
                continue;
            }
            $elements = json_decode($data, true);
            if (!$elements) {
                echo "  [COULD NOT PARSE JSON]\n\n";
                continue;
            }
            $counter = ['section' => 0, 'widget' => 0];
            bg_walk_elements($elements, $counter, 0);
            echo "\n";
        }
        ?></textarea>
        <p><button onclick="document.getElementById('audit-output').select();document.execCommand('copy');alert('Copied! Paste to Claude.');" class="button button-primary button-hero" style="margin-top:10px;">📋 Copy All to Clipboard</button></p>
    </div>
    <?php
}

function bg_walk_elements(array $elements, array &$counter, int $depth) {
    $indent = str_repeat('  ', $depth);
    foreach ($elements as $el) {
        $type = $el['elType'] ?? 'unknown';

        if ($type === 'section' || $type === 'container') {
            $counter['section']++;
            echo "{$indent}[SECTION {$counter['section']}]\n";
        }

        if ($type === 'widget') {
            $wt       = $el['widgetType'] ?? 'unknown';
            $settings = $el['settings'] ?? [];
            $id_hint  = $el['id'] ?? '';
            $texts    = bg_extract_text($wt, $settings);
            if ($texts) {
                foreach ($texts as $key => $val) {
                    $val = trim(wp_strip_all_tags($val));
                    if (strlen($val) > 1) {
                        echo "{$indent}  [{$wt}] [{$key}] {$id_hint}: " . substr($val, 0, 200) . "\n";
                    }
                }
            }
        }

        if (!empty($el['elements'])) {
            bg_walk_elements($el['elements'], $counter, $depth + 1);
        }
    }
}

function bg_extract_text(string $widget_type, array $settings): array {
    $text_keys = [
        // heading widget
        'title',
        // text-editor widget
        'editor',
        // button widget
        'text', 'link',
        // icon-box
        'title_text', 'description_text',
        // image-box
        'title_text', 'description_text',
        // testimonial
        'testimonial_content', 'testimonial_name', 'testimonial_job',
        // counter
        'title', 'prefix', 'suffix', 'ending_number',
        // progress bar
        'title',
        // accordion / toggle
        'tab_title', 'tab_content',
        // price table (UAEL)
        'cta_text', 'plan_title', 'plan_price', 'plan_sub_title',
        // nav menu
        'menu',
        // spacer
        // alert
        'alert_title', 'alert_description',
        // call to action
        'heading', 'description', 'button',
        // flip box
        'title_text_a', 'description_text_a', 'title_text_b', 'description_text_b',
        // generic catch-all
        'content', 'label', 'placeholder', 'caption',
    ];

    $result = [];
    foreach ($text_keys as $key) {
        if (isset($settings[$key]) && is_string($settings[$key]) && trim(wp_strip_all_tags($settings[$key])) !== '') {
            $result[$key] = $settings[$key];
        }
    }

    // Handle accordion items (array)
    if ($widget_type === 'accordion' || $widget_type === 'toggle') {
        foreach ($settings['tabs'] ?? [] as $i => $tab) {
            if (!empty($tab['tab_title'])) $result["tab_{$i}_title"] = $tab['tab_title'];
            if (!empty($tab['tab_content'])) $result["tab_{$i}_content"] = $tab['tab_content'];
        }
    }

    return $result;
}
