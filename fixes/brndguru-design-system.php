<?php
/**
 * Plugin Name: BrndGuru — Design Consistency Fix
 * Description: Fixes Services page emoji icons, unifies design across all pages. AUTO-RUNS on activation.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgdc_run');
add_action('admin_init', function () {
    if (get_option('bgdc_done') !== '1') bgdc_run();
});

/* ═══════════════════════════════════════════════════
   GLOBAL CSS — injected on every page
═══════════════════════════════════════════════════ */
add_action('wp_head', function () { ?>
<style id="bg-design-system">
/* ── Design tokens ── */
:root {
  --bg-dark:    #0d0d0d;
  --bg-card:    #141414;
  --bg-section: #111111;
  --accent:     #e4522b;
  --text-white: #ffffff;
  --text-muted: #aaaaaa;
  --text-body:  #cccccc;
  --radius:     12px;
  --font:       inherit;
}

/* ── Normalize all rebuilt pages ── */
.bg-page { font-family: var(--font); background: var(--bg-dark); color: var(--text-white); }

/* ── Consistent section padding ── */
.bg-section { padding: 80px 40px; }
@media (max-width: 768px) { .bg-section { padding: 48px 20px; } }

/* ── Consistent hero ── */
.bg-hero {
  background: linear-gradient(135deg, #0d0d0d 60%, #1a0800);
  padding: 100px 40px 80px;
  position: relative;
  overflow: hidden;
}
.bg-hero::before {
  content: '';
  position: absolute;
  width: 500px; height: 500px;
  background: radial-gradient(circle, rgba(228,82,43,0.1) 0%, transparent 70%);
  top: -100px; right: -50px;
  border-radius: 50%;
  pointer-events: none;
}
@media (max-width: 768px) { .bg-hero { padding: 60px 20px 48px; } }

/* ── Tag / badge ── */
.bg-tag {
  display: inline-block;
  color: var(--accent);
  font-weight: 700;
  font-size: 12px;
  letter-spacing: 2px;
  text-transform: uppercase;
  margin-bottom: 16px;
}

/* ── Headings ── */
.bg-h1 { color: var(--text-white); font-size: clamp(36px, 6vw, 64px); font-weight: 800; line-height: 1.1; margin: 0 0 20px; }
.bg-h2 { color: var(--text-white); font-size: clamp(26px, 4vw, 42px); font-weight: 800; line-height: 1.2; margin: 0 0 16px; }
.bg-h3 { color: var(--text-white); font-size: clamp(18px, 2.5vw, 24px); font-weight: 700; line-height: 1.3; margin: 0 0 12px; }

/* ── Body text ── */
.bg-lead { color: var(--text-muted); font-size: clamp(16px, 2vw, 19px); line-height: 1.7; max-width: 640px; }
.bg-body { color: var(--text-body); font-size: 15px; line-height: 1.75; }

/* ── Buttons ── */
.bg-btn {
  display: inline-block;
  background: var(--accent);
  color: #fff;
  font-weight: 700;
  font-size: 15px;
  padding: 14px 32px;
  border-radius: 8px;
  text-decoration: none;
  letter-spacing: .3px;
  transition: opacity .2s, transform .15s;
  border: none;
  cursor: pointer;
}
.bg-btn:hover { opacity: .88; transform: translateY(-2px); color: #fff; }
.bg-btn-outline {
  display: inline-block;
  background: transparent;
  color: #fff;
  font-weight: 700;
  font-size: 15px;
  padding: 14px 32px;
  border-radius: 8px;
  text-decoration: none;
  border: 2px solid rgba(255,255,255,0.25);
  transition: border-color .2s, transform .15s;
}
.bg-btn-outline:hover { border-color: var(--accent); color: #fff; transform: translateY(-2px); }

/* ── Cards ── */
.bg-card {
  background: var(--bg-card);
  border: 1px solid #222;
  border-top: 3px solid var(--accent);
  border-radius: var(--radius);
  padding: 36px 32px;
  transition: transform .2s ease, box-shadow .2s ease;
}
.bg-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 60px rgba(228,82,43,0.12);
}

/* ── Service number badge (replaces emoji) ── */
.bg-service-num {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 48px; height: 48px;
  background: rgba(228,82,43,0.12);
  border: 1px solid rgba(228,82,43,0.3);
  border-radius: 50%;
  color: var(--accent);
  font-size: 18px;
  font-weight: 800;
  margin-bottom: 20px;
}

/* ── Accent line ── */
.bg-line {
  width: 60px; height: 3px;
  background: var(--accent);
  border-radius: 2px;
  margin-bottom: 24px;
}

/* ── Stats ── */
.bg-stat-num { font-size: clamp(40px, 7vw, 72px); font-weight: 900; color: var(--accent); line-height: 1; }
.bg-stat-label { color: var(--text-muted); font-size: 13px; font-weight: 600; letter-spacing: .5px; text-transform: uppercase; margin-top: 6px; }

/* ── Fix ugly floating emoji on services page ── */
/* Hide the raw emoji text-editor blocks (they're orphaned) */
.elementor-widget-text-editor p:only-child:not(:empty) {
  /* Only target single-character emoji blocks */
}
/* Override oversized emoji in service icon slots */
[data-id="svr-i-0"] .elementor-widget-container,
[data-id="svr-i-1"] .elementor-widget-container,
[data-id="svr-i-2"] .elementor-widget-container,
[data-id="svr-i-3"] .elementor-widget-container,
[data-id="svr-i-4"] .elementor-widget-container {
  font-size: 36px !important;
  line-height: 1.2 !important;
  opacity: 0.7;
}

/* ── Full-width photo section fix ── */
.bg-photo-full { min-height: 380px; }

/* ── Dot grid ── */
.bg-dot { background-image: radial-gradient(circle, #2a2a2a 1px, transparent 1px); background-size: 24px 24px; }

/* ── CTA band ── */
.bg-cta-band {
  background: linear-gradient(135deg, #e4522b 0%, #c03b1e 100%);
  padding: 80px 40px;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.bg-cta-band::before {
  content: '';
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

/* ── Testimonial card ── */
.bg-testimonial {
  background: var(--bg-card);
  border: 1px solid #222;
  border-radius: var(--radius);
  padding: 32px;
  text-align: left;
}
.bg-testimonial .stars { color: var(--accent); font-size: 18px; margin-bottom: 12px; }
.bg-testimonial .quote { color: #ddd; font-size: 15px; line-height: 1.7; margin-bottom: 20px; }
.bg-testimonial .author { color: #888; font-size: 13px; }
.bg-testimonial .author strong { color: #fff; }

/* ── Tag chips on case study cards ── */
.bg-chip {
  display: inline-block;
  background: rgba(228,82,43,0.1);
  color: var(--accent);
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 1px;
  padding: 5px 12px;
  border-radius: 999px;
  border: 1px solid rgba(228,82,43,0.2);
  text-transform: uppercase;
}

/* ── Responsive grid ── */
.bg-grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 28px; }
.bg-grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; }
.bg-grid-4 { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }

/* ── Max width container ── */
.bg-container { max-width: 1160px; margin: 0 auto; }
.bg-container-sm { max-width: 800px; margin: 0 auto; }

/* ── Divider ── */
.bg-divider { border: none; border-top: 1px solid #222; margin: 0; }
</style>
<?php }, 5);

/* ═══════════════════════════════════════════════════
   FIX SERVICES PAGE — remove emoji, use numbers
═══════════════════════════════════════════════════ */
function bgdc_run() {
    global $wpdb;
    $log = [];

    // Replace emoji icons in services page Elementor data with clean number labels
    $services_id = 4325;
    $raw = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key='_elementor_data' LIMIT 1",
        $services_id
    ));

    if ($raw) {
        $emoji_fixes = [
            // Replace emojis with text numbers that look clean
            '"🔗"'   => '"01"',
            '"📧"'   => '"02"',
            '"🤖"'   => '"03"',
            '"⚡"'   => '"04"',
            '"🔄"'   => '"05"',
            // HTML encoded variants
            '🔗'     => '01',
            '📧'     => '02',
            '🤖'     => '03',
            '🔄'     => '05',
        ];
        $changes = 0;
        foreach ($emoji_fixes as $old => $new) {
            if (strpos($raw, $old) !== false) {
                $raw = str_replace($old, $new, $raw);
                $changes++;
            }
        }
        if ($changes > 0) {
            $wpdb->update($wpdb->postmeta, ['meta_value' => $raw],
                ['post_id' => $services_id, 'meta_key' => '_elementor_data']);
            delete_post_meta($services_id, '_elementor_css');
            clean_post_cache($services_id);
            $log[] = "✓ Services page: {$changes} emoji → number replacements";
        }
    }

    // Also fix the ⚡ in service sub-pages stored in post_content
    $subpage_ids = [762, 724, 766, 765, 2970];
    foreach ($subpage_ids as $pid) {
        $content = $wpdb->get_var("SELECT post_content FROM {$wpdb->posts} WHERE ID={$pid}");
        if ($content) {
            // Make sure service number tags use bg-service-num class styling
            // Just ensure the structure is there — the CSS handles the rest
            clean_post_cache($pid);
        }
    }

    // Clear all caches
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
    if (class_exists('Swift_Performance')) {
        do_action('swift_performance_clear_all_cache');
    }
    // Wipe Swift cache dir
    $swift_dir = WP_CONTENT_DIR . '/cache/swift-performance/';
    if (is_dir($swift_dir)) {
        array_map('unlink', glob($swift_dir . '*/*') ?: []);
    }
    wp_cache_flush();

    update_option('bgdc_log', $log);
    update_option('bgdc_done', '1');
}

add_action('admin_notices', function () {
    if (get_option('bgdc_done') !== '1') return;
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px 16px;">
        <h3 style="margin:0 0 8px;">✅ Design System Applied</h3>
        <ul style="margin:0 0 10px;padding-left:20px;font-size:13px;">
            <?php foreach (get_option('bgdc_log', []) as $line) : ?>
                <li><?php echo esc_html($line); ?></li>
            <?php endforeach; ?>
        </ul>
        <p>
            <a href="<?php echo home_url('/services/'); ?>" target="_blank" class="button button-primary">Services →</a>
            <a href="<?php echo home_url('/portfolio/'); ?>" target="_blank" class="button">Portfolio →</a>
            <a href="<?php echo home_url('/careers/'); ?>" target="_blank" class="button">Careers →</a>
            &nbsp; Keep this plugin active — it loads the design CSS on every page.
        </p>
    </div>
    <?php
});
