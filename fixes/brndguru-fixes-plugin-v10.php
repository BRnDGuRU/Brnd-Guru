<?php
/**
 * Plugin Name: BrndGuru Fix v10
 * Description: Build Services page content + clean leftover CSS. DELETE after running.
 * Version: 10.0
 */

if (!defined('ABSPATH')) exit;

add_filter('plugin_row_meta', function($links, $file) {
    if (strpos($file, 'brndguru-fixes') !== false) {
        $nonce = wp_create_nonce('bg10_run');
        $links[] = '<a href="' . admin_url('?bg10_run=1&bg10_nonce=' . $nonce) . '" style="font-weight:700;color:#00a32a;font-size:14px;">▶ BUILD SERVICES PAGE</a>';
    }
    return $links;
}, 10, 2);

add_action('admin_init', function() {
    if (!isset($_GET['bg10_run'])) return;
    if (!current_user_can('manage_options')) wp_die('Permission denied.');
    if (!wp_verify_nonce($_GET['bg10_nonce'] ?? '', 'bg10_run')) wp_die('Invalid nonce.');

    echo '<pre style="font-family:monospace;padding:20px;background:#0d1117;color:#58d68d;font-size:13px;line-height:1.7;">';
    echo "BrndGuru Fix v10 — Services Page + CSS Cleanup\n" . str_repeat('=', 60) . "\n\n";

    bg10_clean_leftover_css();
    bg10_build_services_page();
    bg10_flush();

    echo str_repeat('=', 60) . "\n";
    echo '<span style="color:#fff">DONE. Deactivate + delete this plugin now.</span>' . "\n";
    echo '</pre>';
    exit;
});

function bg10_ok($m)   { echo '  <span style="color:#2ecc71">✓ ' . esc_html($m) . '</span>' . "\n"; }
function bg10_err($m)  { echo '  <span style="color:#e74c3c">✗ ' . esc_html($m) . '</span>' . "\n"; }
function bg10_info($m) { echo '  ' . esc_html($m) . "\n"; }
function bg10_head($m) { echo '<span style="color:#f1c40f">── ' . esc_html($m) . ' ──</span>' . "\n"; }

// ─────────────────────────────────────────────────────────────
// Clean leftover duplicate CSS
// ─────────────────────────────────────────────────────────────
function bg10_clean_leftover_css() {
    bg10_head('STEP 1: Clean leftover Elementor CSS');

    $el_css = get_option('elementor_custom_css', '');
    // Remove the duplicate "Hide images that fail to load" block entirely
    $cleaned = preg_replace('/\/\*\s*Hide images that fail to load\s*\*\/\s*\n?img\.attachment-full[^\n]+\n?/i', '', $el_css);
    $cleaned = trim(preg_replace('/\n{3,}/', "\n\n", $cleaned));

    if ($cleaned !== $el_css) {
        update_option('elementor_custom_css', $cleaned);
        bg10_ok('Removed leftover image CSS from Elementor');
    } else {
        bg10_info('Nothing to remove');
    }

    if (trim($cleaned)) {
        bg10_info('Remaining Elementor CSS: ' . trim($cleaned));
    } else {
        bg10_info('Elementor custom CSS is now empty/clean');
    }
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// Build the Services page with Elementor JSON
// ─────────────────────────────────────────────────────────────
function bg10_build_services_page() {
    bg10_head('STEP 2: Build Services page content');

    $services_page_id = 4325; // Created by v9

    // Verify it exists
    $page = get_post($services_page_id);
    if (!$page || $page->post_type !== 'page') {
        // Try to find it by slug
        $found = get_page_by_path('services');
        if ($found) {
            $services_page_id = $found->ID;
        } else {
            bg10_err('Services page not found. Run v9 first.');
            return;
        }
    }

    bg10_info('Building content for Services page ID: ' . $services_page_id);

    $home_url = rtrim(get_home_url(), '/');

    // Service cards data
    $services = [
        [
            'title'       => 'Brand Design',
            'url'         => $home_url . '/brand-design/',
            'icon'        => '🎨',
            'description' => 'From logo creation to full brand identity systems. We craft brands that stand out and connect with your audience.',
            'cta'         => 'Explore Brand Design',
        ],
        [
            'title'       => 'UI/UX Design',
            'url'         => $home_url . '/ui-ux-design/',
            'icon'        => '✏️',
            'description' => 'Beautiful, intuitive interfaces designed for conversion. We turn complex user journeys into seamless experiences.',
            'cta'         => 'Explore UI/UX Design',
        ],
        [
            'title'       => 'No-Code Development',
            'url'         => $home_url . '/no-code-development/',
            'icon'        => '⚡',
            'description' => 'Launch faster with no-code tools. We build powerful, scalable websites and apps without traditional coding.',
            'cta'         => 'Explore No-Code Dev',
        ],
        [
            'title'       => 'Webflow Development',
            'url'         => $home_url . '/webflow-development/',
            'icon'        => '🌐',
            'description' => 'Pixel-perfect Webflow sites with clean code and CMS flexibility. High performance, fully responsive.',
            'cta'         => 'Explore Webflow',
        ],
        [
            'title'       => 'Shopify Xcelerator',
            'url'         => $home_url . '/shopify-xcelerator/',
            'icon'        => '🛒',
            'description' => 'E-commerce that converts. We design and develop Shopify stores built for growth and exceptional UX.',
            'cta'         => 'Explore Shopify',
        ],
    ];

    // Build Elementor JSON for the services page
    $elements = [];

    // ── Hero section ─────────────────────────────────────────
    $elements[] = [
        'id'       => 'svc-hero',
        'elType'   => 'container',
        'settings' => [
            'flex_direction'        => 'column',
            'content_width'         => 'full',
            'background_background' => 'classic',
            'background_color'      => '#0d0d0d',
            'padding'               => ['unit' => 'px', 'top' => '100', 'right' => '40', 'bottom' => '80', 'left' => '40', 'isLinked' => false],
            'text_align'            => 'center',
        ],
        'elements' => [
            [
                'id'         => 'svc-hero-heading',
                'elType'     => 'widget',
                'widgetType' => 'heading',
                'settings'   => [
                    'title'          => 'Our Services',
                    'header_size'    => 'h1',
                    'title_color'    => '#ffffff',
                    'typography_font_size' => ['unit' => 'px', 'size' => 56],
                    'typography_font_weight' => '700',
                    'align'          => 'center',
                ],
            ],
            [
                'id'         => 'svc-hero-sub',
                'elType'     => 'widget',
                'widgetType' => 'text-editor',
                'settings'   => [
                    'editor'     => '<p style="font-size:20px;color:#aaaaaa;max-width:650px;margin:0 auto;">We help brands grow with design, development, and digital strategy — all under one roof.</p>',
                    'text_align' => 'center',
                ],
            ],
            [
                'id'         => 'svc-hero-cta',
                'elType'     => 'widget',
                'widgetType' => 'button',
                'settings'   => [
                    'text'             => 'Book a Free Call',
                    'link'             => ['url' => 'https://www.brndgurumedia.com/widget/bookings/brndguru', 'is_external' => 'on', 'nofollow' => ''],
                    'background_color' => '#e4b84d',
                    'button_text_color'=> '#000000',
                    'border_radius'    => ['unit' => 'px', 'top' => '6', 'right' => '6', 'bottom' => '6', 'left' => '6', 'isLinked' => true],
                    'size'             => 'lg',
                    'align'            => 'center',
                    'margin'           => ['unit' => 'px', 'top' => '32', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false],
                ],
            ],
        ],
    ];

    // ── Service cards section ─────────────────────────────────
    $card_elements = [];
    foreach ($services as $i => $svc) {
        $card_elements[] = [
            'id'       => 'svc-card-' . $i,
            'elType'   => 'container',
            'settings' => [
                'flex_direction'        => 'column',
                'background_background' => 'classic',
                'background_color'      => '#141414',
                'border_radius'         => ['unit' => 'px', 'top' => '12', 'right' => '12', 'bottom' => '12', 'left' => '12', 'isLinked' => true],
                'padding'               => ['unit' => 'px', 'top' => '40', 'right' => '36', 'bottom' => '40', 'left' => '36', 'isLinked' => false],
                'width'                 => ['unit' => '%', 'size' => 30],
                'flex_grow'             => '1',
                'link_to'              => 'custom',
                'link'                  => ['url' => $svc['url'], 'is_external' => ''],
            ],
            'elements' => [
                [
                    'id'         => 'svc-card-icon-' . $i,
                    'elType'     => 'widget',
                    'widgetType' => 'heading',
                    'settings'   => [
                        'title'       => $svc['icon'],
                        'header_size' => 'div',
                        'typography_font_size' => ['unit' => 'px', 'size' => 48],
                    ],
                ],
                [
                    'id'         => 'svc-card-title-' . $i,
                    'elType'     => 'widget',
                    'widgetType' => 'heading',
                    'settings'   => [
                        'title'       => $svc['title'],
                        'header_size' => 'h3',
                        'title_color' => '#ffffff',
                        'typography_font_size'   => ['unit' => 'px', 'size' => 24],
                        'typography_font_weight' => '600',
                        'margin'      => ['unit' => 'px', 'top' => '16', 'right' => '0', 'bottom' => '12', 'left' => '0', 'isLinked' => false],
                    ],
                ],
                [
                    'id'         => 'svc-card-desc-' . $i,
                    'elType'     => 'widget',
                    'widgetType' => 'text-editor',
                    'settings'   => [
                        'editor'     => '<p style="color:#999999;font-size:15px;line-height:1.7;">' . esc_html($svc['description']) . '</p>',
                    ],
                ],
                [
                    'id'         => 'svc-card-btn-' . $i,
                    'elType'     => 'widget',
                    'widgetType' => 'button',
                    'settings'   => [
                        'text'              => $svc['cta'] . ' →',
                        'link'              => ['url' => $svc['url'], 'is_external' => ''],
                        'button_type'       => 'ghost',
                        'button_text_color' => '#e4b84d',
                        'border_color'      => '#e4b84d',
                        'border_width'      => ['unit' => 'px', 'top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'isLinked' => true],
                        'border_radius'     => ['unit' => 'px', 'top' => '6', 'right' => '6', 'bottom' => '6', 'left' => '6', 'isLinked' => true],
                        'size'              => 'sm',
                        'margin'            => ['unit' => 'px', 'top' => '20', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false],
                    ],
                ],
            ],
        ];
    }

    $elements[] = [
        'id'       => 'svc-cards-wrap',
        'elType'   => 'container',
        'settings' => [
            'flex_direction'  => 'row',
            'flex_wrap'       => 'wrap',
            'content_width'   => 'boxed',
            'boxed_width'     => ['unit' => 'px', 'size' => 1200, 'sizes' => []],
            'gap'             => ['unit' => 'px', 'size' => 28],
            'padding'         => ['unit' => 'px', 'top' => '80', 'right' => '40', 'bottom' => '80', 'left' => '40', 'isLinked' => false],
            'background_color'=> '#0a0a0a',
            'content_width'   => 'full',
        ],
        'elements' => $card_elements,
    ];

    // ── Bottom CTA section ────────────────────────────────────
    $elements[] = [
        'id'       => 'svc-bottom-cta',
        'elType'   => 'container',
        'settings' => [
            'flex_direction'        => 'column',
            'content_width'         => 'full',
            'background_background' => 'classic',
            'background_color'      => '#e4b84d',
            'padding'               => ['unit' => 'px', 'top' => '80', 'right' => '40', 'bottom' => '80', 'left' => '40', 'isLinked' => false],
            'text_align'            => 'center',
        ],
        'elements' => [
            [
                'id'         => 'svc-cta-heading',
                'elType'     => 'widget',
                'widgetType' => 'heading',
                'settings'   => [
                    'title'       => 'Not sure where to start?',
                    'header_size' => 'h2',
                    'title_color' => '#000000',
                    'typography_font_size'   => ['unit' => 'px', 'size' => 40],
                    'typography_font_weight' => '700',
                    'align'       => 'center',
                ],
            ],
            [
                'id'         => 'svc-cta-sub',
                'elType'     => 'widget',
                'widgetType' => 'text-editor',
                'settings'   => [
                    'editor'     => '<p style="font-size:18px;color:#333;max-width:540px;margin:16px auto 0;">Book a free strategy call and we\'ll find the right solution for your brand.</p>',
                    'text_align' => 'center',
                ],
            ],
            [
                'id'         => 'svc-cta-btn',
                'elType'     => 'widget',
                'widgetType' => 'button',
                'settings'   => [
                    'text'              => 'Schedule a Free Call',
                    'link'              => ['url' => 'https://www.brndgurumedia.com/widget/bookings/brndguru', 'is_external' => 'on', 'nofollow' => ''],
                    'background_color'  => '#000000',
                    'button_text_color' => '#ffffff',
                    'border_radius'     => ['unit' => 'px', 'top' => '6', 'right' => '6', 'bottom' => '6', 'left' => '6', 'isLinked' => true],
                    'size'              => 'lg',
                    'align'             => 'center',
                    'margin'            => ['unit' => 'px', 'top' => '32', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false],
                ],
            ],
        ],
    ];

    // Save Elementor data
    $json = wp_json_encode($elements, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    update_post_meta($services_page_id, '_elementor_data', wp_slash($json));
    update_post_meta($services_page_id, '_elementor_edit_mode', 'builder');
    update_post_meta($services_page_id, '_wp_page_template', 'elementor_canvas');
    delete_post_meta($services_page_id, '_elementor_css');

    // Update page title and status
    wp_update_post([
        'ID'          => $services_page_id,
        'post_title'  => 'Services',
        'post_name'   => 'services',
        'post_status' => 'publish',
        'post_content'=> '',
    ]);

    bg10_ok('Services page built with ' . count($services) . ' service cards');
    bg10_ok('URL: ' . get_permalink($services_page_id));
    bg10_info('Services included:');
    foreach ($services as $svc) {
        bg10_info('  • ' . $svc['title'] . ' → ' . $svc['url']);
    }
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FLUSH
// ─────────────────────────────────────────────────────────────
function bg10_flush() {
    bg10_head('FLUSHING CACHES');
    wp_cache_flush(); bg10_ok('Object cache');
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    bg10_ok('Transients');
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        bg10_ok('Elementor CSS cache');
    }
    do_action('swift_performance_after_clean_cache');
    if (function_exists('rocket_clean_domain'))  { rocket_clean_domain(); bg10_ok('WP Rocket'); }
    if (class_exists('LiteSpeed_Cache_API'))     { LiteSpeed_Cache_API::purge_all(); bg10_ok('LiteSpeed'); }
    bg10_ok('Done');
    echo "\n";
}
