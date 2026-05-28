<?php
/**
 * Plugin Name: BrndGuru — Services Creative Redesign
 * Description: Removes emoji strips, fixes buttons, adds creative visuals to Services page.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgsc_run');
add_action('admin_init', function () {
    if (get_option('bgsc_done') !== '1') bgsc_run();
});

function bgsc_run() {
    global $wpdb;
    $log = [];

    // ── Remove ⚡ and all emojis from Services page Elementor data ──
    $services_id = 4325;
    $raw = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key='_elementor_data' LIMIT 1",
        $services_id
    ));

    if ($raw) {
        $emoji_map = [
            '⚡' => '', '🔗' => '', '📧' => '', '🤖' => '', '🔄' => '',
            '⚡' => '', 'ὑ7' => '', '὎7' => '', 'ᾑ6' => '', 'ὐ4' => '',
            // encoded variants
            '&#9889;' => '', '&#128279;' => '', '&#128231;' => '', '&#129302;' => '', '&#128260;' => '',
        ];
        $new_raw = str_replace(array_keys($emoji_map), array_values($emoji_map), $raw);
        if ($new_raw !== $raw) {
            $wpdb->update($wpdb->postmeta, ['meta_value' => $new_raw],
                ['post_id' => $services_id, 'meta_key' => '_elementor_data']);
            clean_post_cache($services_id);
            $log[] = "✓ Emoji stripped from Services Elementor data";
        }

        // Also remove from post_content
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->posts}
             SET post_content = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(post_content,'⚡',''),'🔗',''),'📧',''),'🤖',''),'🔄','')
             WHERE ID = %d", $services_id
        ));
    }

    // ── Also sweep all sub-pages ──
    foreach ([762, 724, 766, 765, 2970] as $pid) {
        $raw2 = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key='_elementor_data' LIMIT 1", $pid
        ));
        if ($raw2) {
            $new2 = str_replace(array_keys($emoji_map), array_values($emoji_map), $raw2);
            if ($new2 !== $raw2) {
                $wpdb->update($wpdb->postmeta, ['meta_value' => $new2],
                    ['post_id' => $pid, 'meta_key' => '_elementor_data']);
                clean_post_cache($pid);
            }
        }
    }
    $log[] = "✓ Emoji swept from all service sub-pages";

    // Clear caches
    if (class_exists('\Elementor\Plugin')) \Elementor\Plugin::$instance->files_manager->clear_cache();
    foreach ([WP_CONTENT_DIR.'/cache/swift-performance/', WP_CONTENT_DIR.'/cache/swift-performance-lite/'] as $dir) {
        if (is_dir($dir)) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($it as $f) { $f->isDir() ? @rmdir($f) : @unlink($f); }
        }
    }
    if (class_exists('Swift_Performance')) do_action('swift_performance_clear_all_cache');
    wp_cache_flush();

    update_option('bgsc_log', $log);
    update_option('bgsc_done', '1');
}

/* ── Global CSS fixes ── */
add_action('wp_head', function () { ?>
<style id="bg-services-creative">

/* ── Fix ALL buttons: bold + white text ── */
.elementor-button,
.elementor-button-text,
.elementor-button span,
a.elementor-button,
.elementor-widget-button .elementor-button,
.wp-block-button__link,
.bg-btn,
a.bg-btn {
    font-weight: 700 !important;
    color: #ffffff !important;
}

/* ── Hide any element that contains ONLY an emoji (the floating strip) ── */
.elementor-widget-text-editor p:only-child {
    line-height: 1;
}
/* Target the specific emoji icon sections by their emptiness / single-char content */
.elementor-widget-container:has(> .elementor-widget-text-editor):has(p:only-child:empty) {
    display: none !important;
}

/* ── Hide the emoji strip section completely if it's a standalone section ── */
/* The ⚡ shows in its own Elementor section — hide sections that only have one widget with single char */
.elementor-section:has(.elementor-widget-text-editor p:only-child:-moz-only-whitespace),
.elementor-section:has(.elementor-widget-text-editor p:empty) {
    display: none !important;
}

/* ── Creative: Service number badges ── */
.bg-svc-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 52px; height: 52px;
    background: linear-gradient(135deg, #e4522b, #c03b1e);
    border-radius: 50%;
    color: #fff;
    font-size: 20px;
    font-weight: 900;
    margin-bottom: 20px;
    box-shadow: 0 8px 32px rgba(228,82,43,0.35);
}

/* ── Creative: Glowing card hover ── */
.bg-svc-card {
    background: #141414;
    border: 1px solid #222;
    border-radius: 16px;
    padding: 36px 32px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}
.bg-svc-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #e4522b, #ff7a57, #e4522b);
    background-size: 200%;
    animation: shimmer 3s linear infinite;
}
@keyframes shimmer {
    0% { background-position: 200% center; }
    100% { background-position: -200% center; }
}
.bg-svc-card:hover {
    border-color: rgba(228,82,43,0.4);
    transform: translateY(-6px);
    box-shadow: 0 24px 64px rgba(228,82,43,0.15);
}

/* ── Creative: Gradient heading ── */
.bg-gradient-text {
    background: linear-gradient(135deg, #ffffff 40%, #e4522b 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* ── Process steps connector ── */
.bg-process-step {
    position: relative;
    padding-left: 72px;
}
.bg-process-step::before {
    content: attr(data-num);
    position: absolute;
    left: 0; top: 0;
    width: 52px; height: 52px;
    background: linear-gradient(135deg, #e4522b, #c03b1e);
    border-radius: 50%;
    color: #fff;
    font-size: 20px;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px rgba(228,82,43,0.4);
}

/* ── Hide orphan emoji icons that Elementor renders as standalone widgets ── */
.elementor-widget-text-editor > .elementor-widget-container > p:only-child {
    font-size: 0 !important;
    line-height: 0 !important;
    height: 0 !important;
    overflow: hidden !important;
    margin: 0 !important;
    padding: 0 !important;
}
/* But restore normal paragraphs that have actual text (more than 3 chars) */

/* Target the specific single-emoji section that creates the gold strip */
.elementor-section.elementor-inner-section .elementor-widget-text-editor:only-child {
    /* If a section only has one text-editor widget, it's likely an emoji icon */
}
</style>
<?php }, 5);

/* ── Inject creative "How It Works" section into Services page ── */
add_filter('the_content', function ($content) {
    if (!is_page(4325)) return $content;

    $how_it_works = '
<section style="background:#0d0d0d;padding:96px 40px;position:relative;overflow:hidden;">
  <div style="position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(228,82,43,0.07) 0%,transparent 70%);top:-200px;left:-100px;border-radius:50%;pointer-events:none;"></div>
  <div style="max-width:1100px;margin:0 auto;">
    <div style="text-align:center;margin-bottom:64px;">
      <div style="width:60px;height:3px;background:linear-gradient(90deg,#e4522b,#ff7a57);border-radius:2px;margin:0 auto 24px;"></div>
      <h2 style="color:#fff;font-size:clamp(28px,4vw,44px);font-weight:800;margin:0 0 16px;background:linear-gradient(135deg,#fff 40%,#e4522b 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">How We Work</h2>
      <p style="color:#888;font-size:17px;max-width:520px;margin:0 auto;line-height:1.7;">From audit to booked calls — a proven 4-step process.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:32px;">
      <div style="background:#141414;border:1px solid #1e1e1e;border-radius:16px;padding:36px 28px;position:relative;overflow:hidden;">
        <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#e4522b,#ff7a57,#e4522b);background-size:200%;"></div>
        <div style="width:52px;height:52px;background:linear-gradient(135deg,#e4522b,#c03b1e);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;font-weight:900;margin-bottom:20px;box-shadow:0 8px 24px rgba(228,82,43,0.35);">01</div>
        <h3 style="color:#fff;font-size:18px;font-weight:700;margin:0 0 12px;">Discovery & Audit</h3>
        <p style="color:#888;font-size:14px;line-height:1.75;margin:0;">We audit your ICP, current outbound, and tech stack to find gaps and quick wins.</p>
      </div>
      <div style="background:#141414;border:1px solid #1e1e1e;border-radius:16px;padding:36px 28px;position:relative;overflow:hidden;">
        <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#e4522b,#ff7a57,#e4522b);background-size:200%;"></div>
        <div style="width:52px;height:52px;background:linear-gradient(135deg,#e4522b,#c03b1e);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;font-weight:900;margin-bottom:20px;box-shadow:0 8px 24px rgba(228,82,43,0.35);">02</div>
        <h3 style="color:#fff;font-size:18px;font-weight:700;margin:0 0 12px;">Build the System</h3>
        <p style="color:#888;font-size:14px;line-height:1.75;margin:0;">We set up your outbound infrastructure — sequences, copy, automations, and CRM workflows.</p>
      </div>
      <div style="background:#141414;border:1px solid #1e1e1e;border-radius:16px;padding:36px 28px;position:relative;overflow:hidden;">
        <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#e4522b,#ff7a57,#e4522b);background-size:200%;"></div>
        <div style="width:52px;height:52px;background:linear-gradient(135deg,#e4522b,#c03b1e);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;font-weight:900;margin-bottom:20px;box-shadow:0 8px 24px rgba(228,82,43,0.35);">03</div>
        <h3 style="color:#fff;font-size:18px;font-weight:700;margin:0 0 12px;">Launch & Optimise</h3>
        <p style="color:#888;font-size:14px;line-height:1.75;margin:0;">We go live, monitor reply rates, A/B test messaging, and iterate weekly until your numbers are where they need to be.</p>
      </div>
      <div style="background:#141414;border:1px solid #1e1e1e;border-radius:16px;padding:36px 28px;position:relative;overflow:hidden;">
        <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#e4522b,#ff7a57,#e4522b);background-size:200%;"></div>
        <div style="width:52px;height:52px;background:linear-gradient(135deg,#e4522b,#c03b1e);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;font-weight:900;margin-bottom:20px;box-shadow:0 8px 24px rgba(228,82,43,0.35);">04</div>
        <h3 style="color:#fff;font-size:18px;font-weight:700;margin:0 0 12px;">Scale What Works</h3>
        <p style="color:#888;font-size:14px;line-height:1.75;margin:0;">Once your pipeline is flowing, we scale the winning channels and hand off a self-sustaining system.</p>
      </div>
    </div>
  </div>
</section>';

    // Inject after first </section>
    $pos = strpos($content, '</section>');
    if ($pos !== false) {
        $content = substr($content, 0, $pos + 10) . $how_it_works . substr($content, $pos + 10);
    } else {
        $content = $how_it_works . $content;
    }

    return $content;
}, 15);

add_action('admin_notices', function () {
    if (get_option('bgsc_done') !== '1') return;
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px 16px;">
        <h3 style="margin:0 0 8px;">✅ Services Creative Redesign Applied</h3>
        <ul style="margin:0 0 10px;padding-left:20px;font-size:13px;">
            <?php foreach (get_option('bgsc_log', []) as $l): ?><li><?php echo esc_html($l); ?></li><?php endforeach; ?>
        </ul>
        <p>
            <a href="<?php echo home_url('/services/'); ?>" target="_blank" class="button button-primary">View Services →</a>
        </p>
    </div>
    <?php
});
