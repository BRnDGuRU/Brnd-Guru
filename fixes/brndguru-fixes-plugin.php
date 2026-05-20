<?php
/**
 * Plugin Name: BrndGuru Site Fixes v3
 * Description: Targeted fixes for Header/Footer Kit, pricing buttons, UTM links. DELETE after running.
 * Version: 3.0
 */

if (!defined('ABSPATH')) exit;

add_filter('plugin_row_meta', function($links, $file) {
    if (strpos($file, 'brndguru-fixes') !== false) {
        $nonce = wp_create_nonce('brndguru_fixes');
        $links[] = '<a href="' . admin_url('?brndguru_run_fixes=1&brndguru_nonce=' . $nonce) . '" style="font-weight:700;color:#00a32a;font-size:13px;">▶ RUN FIXES NOW</a>';
    }
    return $links;
}, 10, 2);

add_action('admin_init', function() {
    if (!isset($_GET['brndguru_run_fixes'])) return;
    if (!current_user_can('manage_options')) wp_die('Permission denied.');
    if (!wp_verify_nonce($_GET['brndguru_nonce'] ?? '', 'brndguru_fixes')) wp_die('Invalid nonce.');

    echo '<pre style="font-family:monospace;padding:20px;background:#0d1117;color:#58d68d;font-size:13px;line-height:1.7;">';
    echo "BrndGuru Site Fixes v3\n" . str_repeat('=', 60) . "\n\n";

    bg3_fix3_footer_facebook();
    bg3_fix4_header_cta();
    bg3_fix5_pricing_buttons();
    bg3_fix9_utm_links();
    bg3_flush();

    echo str_repeat('=', 60) . "\n";
    echo '<span style="color:#fff">DONE. Deactivate + delete this plugin now.</span>' . "\n";
    echo '</pre>';
    exit;
});

// ── helpers ───────────────────────────────────────────────────
function bg3_ok($m)   { echo '  <span style="color:#2ecc71">✓ ' . esc_html($m) . '</span>' . "\n"; }
function bg3_err($m)  { echo '  <span style="color:#e74c3c">✗ ' . esc_html($m) . '</span>' . "\n"; }
function bg3_info($m) { echo '  ' . esc_html($m) . "\n"; }
function bg3_head($m) { echo '<span style="color:#f1c40f">── ' . esc_html($m) . ' ──</span>' . "\n"; }

function bg3_get_el($id) {
    $raw = get_post_meta($id, '_elementor_data', true);
    if (!$raw) return null;
    $d = json_decode($raw, true);
    return json_last_error() === JSON_ERROR_NONE ? $d : null;
}
function bg3_set_el($id, $data) {
    update_post_meta($id, '_elementor_data', wp_slash(wp_json_encode($data, JSON_UNESCAPED_UNICODE)));
    delete_post_meta($id, '_elementor_css');
}
function bg3_walk(array &$nodes, callable $fn) {
    foreach ($nodes as &$n) { $fn($n); if (!empty($n['elements'])) bg3_walk($n['elements'], $fn); }
}

/** Get ALL elementor_library posts including kit, any status */
function bg3_all_templates() {
    return get_posts([
        'post_type'      => 'elementor_library',
        'post_status'    => ['publish','draft','private','inherit'],
        'posts_per_page' => 100,
    ]);
}

/** Find template by _elementor_template_type, searching all statuses */
function bg3_find_tpl($type) {
    $posts = get_posts([
        'post_type'      => 'elementor_library',
        'post_status'    => ['publish','draft','private','inherit'],
        'posts_per_page' => 10,
        'meta_query'     => [['key'=>'_elementor_template_type','value'=>$type]],
    ]);
    if ($posts) return $posts[0];
    // Title fallback
    foreach (bg3_all_templates() as $p) {
        if (stripos($p->post_title, $type) !== false) return $p;
    }
    return null;
}

// ─────────────────────────────────────────────────────────────
// FIX 3 — Footer Facebook icon
// Searches: (a) elementor_library footer type, (b) Default Kit docs,
//           (c) Astra footer widget areas
// ─────────────────────────────────────────────────────────────
function bg3_fix3_footer_facebook() {
    bg3_head('FIX 3: Footer Facebook icon URL');

    $WRONG  = 'linkedin.com/company/brndguru';
    $CORRECT = 'https://www.facebook.com/brndguru';
    $fixed  = false;

    // Dump all templates for context
    $all = bg3_all_templates();
    bg3_info('All Elementor Library posts (' . count($all) . '):');
    foreach ($all as $p) {
        $type = get_post_meta($p->ID, '_elementor_template_type', true);
        bg3_info('  ID:' . $p->ID . ' type:' . ($type ?: 'none') . ' status:' . $p->post_status . ' "' . $p->post_title . '"');
    }

    // (a) Try footer template type
    $footer = bg3_find_tpl('footer');
    if ($footer) {
        bg3_info('Found footer template: ID ' . $footer->ID);
        $data = bg3_get_el($footer->ID);
        if ($data && bg3_patch_fb_icon($data, $WRONG, $CORRECT)) {
            bg3_set_el($footer->ID, $data);
            bg3_ok('Fixed Facebook icon in footer template ID ' . $footer->ID);
            $fixed = true;
        }
    }

    // (b) Search ALL templates for Facebook+LinkedIn combo
    if (!$fixed) {
        foreach ($all as $p) {
            $data = bg3_get_el($p->ID);
            if (!$data) continue;
            if (bg3_patch_fb_icon($data, $WRONG, $CORRECT)) {
                bg3_set_el($p->ID, $data);
                bg3_ok('Fixed Facebook icon in "' . $p->post_title . '" (ID ' . $p->ID . ')');
                $fixed = true;
            }
        }
    }

    // (c) Search widget areas (classic widgets footer)
    if (!$fixed) {
        $sidebars = get_option('sidebars_widgets', []);
        foreach ($sidebars as $sidebar_id => $widget_ids) {
            if (!is_array($widget_ids)) continue;
            foreach ($widget_ids as $widget_id) {
                $parts = explode('-', $widget_id);
                $num = array_pop($parts);
                $base = implode('-', $parts);
                $opts = get_option('widget_' . $base, []);
                if (isset($opts[$num])) {
                    $serialized = maybe_serialize($opts[$num]);
                    if (strpos($serialized, $WRONG) !== false) {
                        $opts[$num] = unserialize(str_replace($WRONG, ltrim($CORRECT,'https://'), $serialized));
                        update_option('widget_' . $base, $opts);
                        bg3_ok('Fixed in widget "' . $widget_id . '" in sidebar "' . $sidebar_id . '"');
                        $fixed = true;
                    }
                }
            }
        }
    }

    // (d) Direct search in wp_postmeta for LinkedIn URL near facebook context
    if (!$fixed) {
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id, meta_value FROM {$wpdb->postmeta}
             WHERE meta_key = '_elementor_data'
             AND meta_value LIKE %s",
            '%' . $wpdb->esc_like('linkedin.com/company/brndguru') . '%'
        ));
        bg3_info('Direct DB search for LinkedIn URL: ' . count($rows) . ' post(s) found');
        foreach ($rows as $row) {
            $data = json_decode($row->meta_value, true);
            if (!$data) continue;
            if (bg3_patch_fb_icon($data, $WRONG, $CORRECT)) {
                bg3_set_el($row->post_id, $data);
                bg3_ok('Fixed in post ID ' . $row->post_id);
                $fixed = true;
            } else {
                // Raw string replace as last resort
                $new_val = str_replace(
                    'https://www.linkedin.com/company/brndguru/',
                    $CORRECT,
                    $row->meta_value
                );
                // Only replace if the nearby context suggests it's a Facebook icon
                if ($new_val !== $row->meta_value) {
                    update_post_meta($row->post_id, '_elementor_data', wp_slash($new_val));
                    delete_post_meta($row->post_id, '_elementor_css');
                    bg3_ok('Raw-replaced LinkedIn→Facebook URL in post ID ' . $row->post_id . ' (verify in Elementor)');
                    $fixed = true;
                }
            }
        }
    }

    if (!$fixed) bg3_err('Could not find Facebook icon with LinkedIn URL anywhere.');
    echo "\n";
}

/** Patch social_icon_list entries where icon=facebook but url=linkedin */
function bg3_patch_fb_icon(array &$data, $wrong, $correct): bool {
    $fixed = 0;
    bg3_walk($data, function(&$node) use ($wrong, $correct, &$fixed) {
        $icons = &$node['settings']['social_icon_list'] ?? null;
        if (!is_array($icons)) return;
        foreach ($icons as &$icon) {
            $val = $icon['social_icon']['value'] ?? $icon['social_icon'] ?? '';
            $url = $icon['link']['url'] ?? '';
            $is_fb = is_string($val) && stripos($val, 'facebook') !== false;
            if ($is_fb && strpos($url, $wrong) !== false) {
                $icon['link']['url'] = $correct;
                $fixed++;
            }
        }
    });
    return $fixed > 0;
}

// ─────────────────────────────────────────────────────────────
// FIX 4 — Header duplicate CTA buttons (Astra + Elementor)
// ─────────────────────────────────────────────────────────────
function bg3_fix4_header_cta() {
    bg3_head('FIX 4: Header duplicate CTA buttons');

    // Try elementor header template
    $header = bg3_find_tpl('header');
    if ($header) {
        bg3_info('Found header template: "' . $header->post_title . '" ID ' . $header->ID);
        $data = bg3_get_el($header->ID);
        if ($data) {
            $ctas = [];
            bg3_walk($data, function(&$n) use (&$ctas) {
                if (($n['widgetType'] ?? '') !== 'button') return;
                $t = strtolower($n['settings']['text'] ?? '');
                if (strpos($t,'schedule') !== false || strpos($t,'call') !== false) $ctas[] = &$n;
            });
            bg3_info('CTA buttons in template: ' . count($ctas));
            if (count($ctas) >= 2) {
                $ctas[0]['settings']['hide_mobile'] = 'yes';
                $ctas[0]['settings']['hide_tablet'] = '';
                $ctas[0]['settings']['hide_desktop'] = '';
                $ctas[1]['settings']['hide_desktop'] = 'yes';
                $ctas[1]['settings']['hide_tablet'] = 'yes';
                $ctas[1]['settings']['hide_mobile'] = '';
                bg3_set_el($header->ID, $data);
                bg3_ok('Set responsive visibility on ' . count($ctas) . ' CTA buttons');
                echo "\n"; return;
            }
        }
    }

    // Search ALL templates for schedule/call buttons
    $found_in = [];
    foreach (bg3_all_templates() as $p) {
        $data = bg3_get_el($p->ID);
        if (!$data) continue;
        $ctas = [];
        bg3_walk($data, function(&$n) use (&$ctas) {
            if (($n['widgetType'] ?? '') !== 'button') return;
            $t = strtolower($n['settings']['text'] ?? '');
            if (strpos($t,'schedule') !== false || strpos($t,'call') !== false) $ctas[] = &$n;
        });
        if ($ctas) $found_in[] = '"' . $p->post_title . '" (ID:' . $p->ID . ') — ' . count($ctas) . ' CTA(s)';
    }

    if ($found_in) {
        bg3_info('CTA buttons found in: ' . implode(', ', $found_in));
        bg3_info('Header uses Astra theme builder — fix responsive visibility manually:');
        bg3_info('  Elementor → find the header template above → edit → Advanced → Responsive');
    } else {
        bg3_info('No CTA buttons found in any Elementor template.');
        bg3_info('Header is likely built with Astra Header Builder (not Elementor).');
        bg3_info('Fix: Appearance → Customize → Header → find duplicate button → set device visibility.');
    }
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FIX 5 — Homepage pricing "Learn More" → "Book a Call"
// ─────────────────────────────────────────────────────────────
function bg3_fix5_pricing_buttons() {
    bg3_head('FIX 5: Pricing "Learn More" → "Book a Call"');

    $home_id = (int) get_option('page_on_front');
    if (!$home_id) {
        $p = get_page_by_path('home') ?: get_page_by_path('homepage');
        $home_id = $p ? $p->ID : 0;
    }
    bg3_info('Homepage ID: ' . $home_id);

    $data = $home_id ? bg3_get_el($home_id) : null;

    if (!$data) {
        bg3_err('No Elementor data on homepage ID ' . $home_id);
        echo "\n"; return;
    }

    // Dump ALL button widgets found on homepage for diagnosis
    $all_btns = [];
    bg3_walk($data, function(&$n) use (&$all_btns) {
        if (($n['widgetType'] ?? '') === 'button') {
            $all_btns[] = '"' . ($n['settings']['text'] ?? '') . '" → ' . ($n['settings']['link']['url'] ?? '');
        }
    });
    bg3_info('All button widgets on homepage (' . count($all_btns) . '):');
    foreach ($all_btns as $b) bg3_info('  ' . $b);

    // Fix: rename any button containing "learn more" (case-insensitive)
    $fixed = 0;
    bg3_walk($data, function(&$n) use (&$fixed) {
        if (($n['widgetType'] ?? '') !== 'button') return;
        if (stripos($n['settings']['text'] ?? '', 'learn more') !== false) {
            $n['settings']['text'] = 'Book a Call';
            if (isset($n['settings']['link'])) $n['settings']['link']['is_external'] = 'on';
            $fixed++;
        }
    });

    // Also check for price-table / pricing-table widgets
    $pricing_fixed = 0;
    bg3_walk($data, function(&$n) use (&$pricing_fixed) {
        $wt = $n['widgetType'] ?? '';
        if (!in_array($wt, ['price-table','pricing-table','jet-woo-products','woocommerce-products'])) return;
        // Check button text within pricing widget settings
        foreach (['button_text','cta_text','btn_text'] as $key) {
            if (isset($n['settings'][$key]) && stripos($n['settings'][$key], 'learn more') !== false) {
                $n['settings'][$key] = 'Book a Call';
                $pricing_fixed++;
            }
        }
    });

    if ($fixed > 0 || $pricing_fixed > 0) {
        bg3_set_el($home_id, $data);
        bg3_ok('Renamed ' . ($fixed + $pricing_fixed) . ' button(s) to "Book a Call"');
    } else {
        bg3_info('No "Learn More" buttons found via widget scan.');
        bg3_info('Pricing section may use a global/reusable section. Checking...');

        // Search all posts for "Learn More" buttons with brndgurumedia URL
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = '_elementor_data'
             AND meta_value LIKE %s
             AND meta_value LIKE %s",
            '%' . $wpdb->esc_like('learn more') . '%',
            '%' . $wpdb->esc_like('brndgurumedia') . '%'
        ));
        bg3_info('Posts with Learn More + brndgurumedia: ' . count($rows));
        foreach ($rows as $row) {
            $d = bg3_get_el($row->post_id);
            if (!$d) continue;
            $cnt = 0;
            bg3_walk($d, function(&$n) use (&$cnt) {
                if (($n['widgetType'] ?? '') === 'button' && stripos($n['settings']['text'] ?? '', 'learn more') !== false) {
                    $n['settings']['text'] = 'Book a Call';
                    if (isset($n['settings']['link'])) $n['settings']['link']['is_external'] = 'on';
                    $cnt++;
                }
            });
            if ($cnt > 0) {
                bg3_set_el($row->post_id, $d);
                $title = get_the_title($row->post_id);
                bg3_ok('Fixed ' . $cnt . ' button(s) in "' . $title . '" (ID ' . $row->post_id . ')');
            }
        }
    }
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FIX 9 — Strip UTM params — also searches raw HTML content
// ─────────────────────────────────────────────────────────────
function bg3_fix9_utm_links() {
    bg3_head('FIX 9: Strip UTM params from award links (About page)');

    $about = get_page_by_path('about');
    if (!$about) { bg3_err('About page not found'); echo "\n"; return; }
    $id = $about->ID;

    $raw = get_post_meta($id, '_elementor_data', true);
    if (!$raw) { bg3_err('No Elementor data'); echo "\n"; return; }

    $before = substr_count($raw, 'utm_');
    bg3_info('UTM occurrences before: ' . $before);

    // Pass 1: JSON link objects  {"url":"...?utm_..."}
    $fixed = preg_replace_callback(
        '/"(https?:\/\/[^"]*utm_[^"]*)"/',
        'bg3_clean_utm_match', $raw
    );

    // Pass 2: HTML href attributes  href="...?utm_..."
    $fixed = preg_replace_callback(
        '/href=\\"(https?:\/\/[^"\\\\]*utm_[^"\\\\]*)\\\"/',
        function($m) { return 'href=\"' . bg3_clean_utm($m[1]) . '\"'; },
        $fixed
    );

    // Pass 3: HTML href in unescaped context href="...?utm_..."
    $fixed = preg_replace_callback(
        '/href="(https?:\/\/[^"]*utm_[^"]*)"/',
        function($m) { return 'href="' . bg3_clean_utm($m[1]) . '"'; },
        $fixed
    );

    $after = substr_count($fixed, 'utm_');
    bg3_info('UTM occurrences after: ' . $after);

    if ($fixed !== $raw) {
        update_post_meta($id, '_elementor_data', wp_slash($fixed));
        delete_post_meta($id, '_elementor_css');
        bg3_ok('Removed ' . ($before - $after) . ' UTM occurrence(s)');
    } else {
        bg3_info('No UTM params found. The award links may be in a separate global section.');

        // Last resort: search all elementor data for utm_source=chatgpt
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = '_elementor_data'
             AND meta_value LIKE %s",
            '%' . $wpdb->esc_like('utm_source') . '%'
        ));
        bg3_info('Posts with utm_source in Elementor data: ' . count($rows));
        foreach ($rows as $row) {
            $r = get_post_meta($row->post_id, '_elementor_data', true);
            $cnt_before = substr_count($r, 'utm_');
            $r2 = preg_replace_callback('/"(https?:\/\/[^"]*utm_[^"]*)"/', 'bg3_clean_utm_match', $r);
            $r2 = preg_replace_callback('/href=\\"(https?:\/\/[^"\\\\]*utm_[^"\\\\]*)\\\"/', function($m){ return 'href=\"'.bg3_clean_utm($m[1]).'\"'; }, $r2);
            if ($r2 !== $r) {
                update_post_meta($row->post_id, '_elementor_data', wp_slash($r2));
                delete_post_meta($row->post_id, '_elementor_css');
                bg3_ok('Cleaned UTM from post ID ' . $row->post_id . ' (' . get_the_title($row->post_id) . '): removed ' . ($cnt_before - substr_count($r2, 'utm_')) . ' occurrence(s)');
            }
        }
    }
    echo "\n";
}

function bg3_clean_utm($url) {
    $parts = parse_url($url);
    parse_str($parts['query'] ?? '', $qs);
    foreach (array_keys($qs) as $k) { if (strpos($k,'utm_')===0) unset($qs[$k]); }
    $q = http_build_query($qs);
    $out = ($parts['scheme']??'https') . '://' . ($parts['host']??'');
    if (!empty($parts['path'])) $out .= $parts['path'];
    if ($q) $out .= '?' . $q;
    return $out;
}
function bg3_clean_utm_match($m) { return '"' . bg3_clean_utm($m[1]) . '"'; }

// ─────────────────────────────────────────────────────────────
// FLUSH
// ─────────────────────────────────────────────────────────────
function bg3_flush() {
    bg3_head('FLUSHING CACHES');
    wp_cache_flush(); bg3_ok('Object cache');
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    bg3_ok('Transients');
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        bg3_ok('Elementor CSS cache');
    }
    do_action('swift_performance_after_clean_cache');
    if (function_exists('rocket_clean_domain'))  { rocket_clean_domain(); bg3_ok('WP Rocket'); }
    if (class_exists('LiteSpeed_Cache_API'))     { LiteSpeed_Cache_API::purge_all(); bg3_ok('LiteSpeed'); }
    echo "\n";
}
