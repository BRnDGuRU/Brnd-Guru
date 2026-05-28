<?php
/**
 * Plugin Name: BrndGuru — Dark Theme Nuclear Fix
 * Description: JS luminance pass recolors ALL light blocks dark + fixes invisible icon-box titles + duplicate button text. Bulletproof.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgnuke_run');
add_action('admin_init', function () {
    if (get_option('bgnuke_done') !== '1') bgnuke_run();
});

function bgnuke_run() {
    global $wpdb;
    $log = [];

    // ── Fix duplicate button text in DB ──
    $dupes = [
        'Strategy &amp; Audit: Strategy &amp; Audit' => 'Strategy &amp; Audit',
        'Strategy & Audit: Strategy & Audit'         => 'Strategy & Audit',
        'Infrastructure Build: Infrastructure Build' => 'Infrastructure Build',
        'Launch &amp; Optimise: Launch &amp; Optimise' => 'Launch &amp; Optimise',
        'Launch & Optimise: Launch & Optimise'       => 'Launch & Optimise',
        'Launch &amp; Optimize: Launch &amp; Optimize' => 'Launch &amp; Optimize',
        'Launch & Optimize: Launch & Optimize'       => 'Launch & Optimize',
        'Discover: Discover'                         => 'Discover',
        'Design &amp; Build: Design &amp; Build'       => 'Design &amp; Build',
        'Design & Build: Design & Build'             => 'Design & Build',
        'Launch &amp; Grow: Launch &amp; Grow'         => 'Launch &amp; Grow',
        'Launch & Grow: Launch & Grow'               => 'Launch & Grow',
    ];

    $pages = [12, 1628, 4325, 23, 21, 1483, 762, 724, 766, 765, 2970];
    $fixed = 0;
    foreach ($pages as $pid) {
        $raw = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key='_elementor_data' LIMIT 1", $pid
        ));
        if ($raw) {
            $new = $raw;
            foreach ($dupes as $old => $rep) $new = str_replace($old, $rep, $new);
            if ($new !== $raw) {
                $wpdb->update($wpdb->postmeta, ['meta_value' => $new],
                    ['post_id' => $pid, 'meta_key' => '_elementor_data']);
                delete_post_meta($pid, '_elementor_css');
                clean_post_cache($pid);
                $fixed++;
            }
        }
        // Also post_content
        $pc = $wpdb->get_var($wpdb->prepare("SELECT post_content FROM {$wpdb->posts} WHERE ID=%d", $pid));
        if ($pc) {
            $npc = $pc;
            foreach ($dupes as $old => $rep) $npc = str_replace($old, $rep, $npc);
            if ($npc !== $pc) {
                $wpdb->update($wpdb->posts, ['post_content' => $npc], ['ID' => $pid]);
                clean_post_cache($pid);
            }
        }
    }
    $log[] = "✓ Fixed duplicate button text on {$fixed} pages";

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
    if (function_exists('opcache_reset')) opcache_reset();

    update_option('bgnuke_log', $log);
    update_option('bgnuke_done', '1');
}

/* ── Comprehensive CSS: dark containers + light text on ALL widget types ── */
add_action('wp_footer', function () {
    // Only run on front-end pages, not in editor
    if (is_admin()) return;
    ?>
<style id="bg-dark-nuclear-css">
/* ── ALL Elementor container types → dark ── */
.elementor-section,
.elementor-top-section,
.elementor-inner-section,
.e-con,
.e-con-inner,
.e-parent,
.e-child,
.elementor-column,
.elementor-widget-wrap,
.elementor-element-populated {
    background-color: #0d0d0d !important;
}

/* ── Light overlays → transparent (kills peach overlay tints) ── */
.elementor-background-overlay {
    background-color: transparent !important;
    opacity: 0 !important;
}

/* ── ALL heading / title / text widget types → light ── */
.elementor-heading-title,
.elementor-heading-title a,
.elementor-icon-box-title,
.elementor-icon-box-title a,
.elementor-image-box-title,
.elementor-image-box-title a,
.elementor-icon-list-text,
.elementor-accordion-title,
.elementor-toggle-title,
.elementor-tab-title,
.elementor-price-table__title,
.elementor-testimonial-name,
.elementor-cta__title {
    color: #ffffff !important;
}

.elementor-icon-box-description,
.elementor-image-box-description,
.elementor-widget-text-editor,
.elementor-widget-text-editor p,
.elementor-testimonial-content,
.elementor-cta__description {
    color: #b5b5b5 !important;
}

/* ── Icon boxes: orange icons, dark circle bg ── */
.elementor-icon-box-icon .elementor-icon,
.elementor-icon {
    color: #e4522b !important;
    background-color: rgba(228,82,43,0.10) !important;
}
.elementor-icon svg, .elementor-icon i { color: #e4522b !important; fill: #e4522b !important; }

/* ── Buttons stay solid orange, bold white text ── */
.elementor-button,
.elementor-button-link,
a.elementor-button {
    background-color: #e4522b !important;
    color: #ffffff !important;
    font-weight: 700 !important;
}
.elementor-button:hover { background-color: #c03b1e !important; }
.elementor-button .elementor-button-text { color:#ffffff !important; font-weight:700 !important; }

/* ── Dividers / borders ── */
.elementor-divider-separator, hr { border-color:#262626 !important; }

/* ── Testimonial / review cards ── */
.elementor-testimonial-wrapper,
[class*="testimonial"] { background:#141414 !important; border:1px solid #222 !important; border-radius:12px !important; }
.elementor-testimonial-name { color:#fff !important; }
.elementor-testimonial-job { color:#888 !important; }
</style>

<script id="bg-dark-nuclear-js">
(function(){
  function luminance(r,g,b){ return (0.2126*r + 0.7152*g + 0.0722*b)/255; }
  function parseColor(str){
    if(!str) return null;
    var m = str.match(/rgba?\(([^)]+)\)/);
    if(!m) return null;
    var p = m[1].split(',').map(function(x){return parseFloat(x.trim());});
    return {r:p[0], g:p[1], b:p[2], a:(p.length>3? p[3] : 1)};
  }
  function fix(){
    var content = document.querySelector('.elementor, .site-content, #content, main') || document.body;
    var all = content.querySelectorAll('*');
    for(var i=0;i<all.length;i++){
      var el = all[i];
      var tag = el.tagName;
      if(tag==='IMG' || tag==='SVG' || tag==='PATH' || tag==='VIDEO' || tag==='IFRAME') continue;
      // Skip buttons (keep orange) and elements we explicitly themed
      if(el.classList && (el.classList.contains('elementor-button') || el.classList.contains('bg-btn'))) continue;

      var cs = getComputedStyle(el);
      var bg = parseColor(cs.backgroundColor);
      // Recolor LIGHT solid backgrounds → dark
      if(bg && bg.a > 0.15 && luminance(bg.r,bg.g,bg.b) > 0.62){
        el.style.setProperty('background-color', '#0d0d0d', 'important');
        // if it had a light gradient image too, neutralize
        if(cs.backgroundImage && cs.backgroundImage.indexOf('gradient')>-1 && cs.backgroundImage.indexOf('url(')===-1){
          el.style.setProperty('background-image', 'none', 'important');
        }
      }
      // Recolor DARK text → light (for readability on dark bg)
      var col = parseColor(cs.color);
      if(col && col.a > 0.3 && luminance(col.r,col.g,col.b) < 0.30){
        // keep orange-ish text as-is (high red, low green/blue)
        var isOrange = col.r>150 && col.g<140 && col.b<110;
        if(!isOrange){
          // headings brighter than body
          var isHeading = /^H[1-6]$/.test(tag) || (el.className+'').match(/title|heading/i);
          el.style.setProperty('color', isHeading ? '#ffffff' : '#b5b5b5', 'important');
        }
      }
    }
  }
  if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', fix); }
  else { fix(); }
  // Re-run after a tick in case Elementor lazy-loads styles
  setTimeout(fix, 600);
  window.addEventListener('load', fix);
})();
</script>
<?php }, 9999);

add_action('admin_notices', function () {
    if (get_option('bgnuke_done') !== '1') return;
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px 16px;">
        <h3 style="margin:0 0 8px;">✅ Dark Theme Nuclear Fix Applied</h3>
        <ul style="margin:0 0 10px;padding-left:20px;font-size:13px;">
            <?php foreach (get_option('bgnuke_log', []) as $l): ?><li><?php echo esc_html($l); ?></li><?php endforeach; ?>
        </ul>
        <p style="margin:0;">JS recolors any light block + invisible titles on every page load. Check in incognito:
            <a href="<?php echo home_url('/about-us/'); ?>" target="_blank" class="button button-primary">About →</a>
            <a href="<?php echo home_url('/'); ?>" target="_blank" class="button">Home →</a>
        </p>
    </div>
    <?php
});
