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

/* ── Footer: lighter background so it stands out ── */
#colophon, .site-footer, .ast-footer-widget-area,
.ast-footer-below-section, .ast-footer-bottom-bar,
.footer-widget-area {
    background-color: #1a1a1a !important;
    border-top: 2px solid rgba(228,82,43,0.35) !important;
}
#colophon *:not(img):not(svg):not(path):not(video):not(iframe):not(input):not(button),
.ast-footer-widget-area *:not(img):not(svg):not(path) {
    background-color: #1a1a1a !important;
}

/* ── Footer headings ── */
#colophon h1,#colophon h2,#colophon h3,#colophon h4,#colophon h5,#colophon h6,
#colophon .elementor-heading-title,
.site-footer h1,.site-footer h2,.site-footer h3,.site-footer h4 {
    color: #ffffff !important;
}

/* ── Footer nav links & ALL anchors — the critical fix ── */
#colophon a, #colophon a:link, #colophon a:visited,
#colophon ul li a, #colophon nav a,
#colophon .menu-item a, #colophon .menu-item > a,
#colophon .widget_nav_menu a, #colophon .widget_nav_menu ul li a,
#colophon .elementor-nav-menu a,
#colophon .elementor-nav-menu--main .elementor-item,
#colophon .elementor-nav-menu .elementor-item,
#colophon .elementor-icon-list-item a,
.site-footer a, .site-footer ul li a,
.site-footer .menu-item a,
.site-footer .widget_nav_menu a,
.site-footer .elementor-nav-menu a,
.site-footer .elementor-nav-menu--main .elementor-item,
.ast-footer-widget-area a, .ast-footer-widget-area ul li a {
    color: #cccccc !important;
    opacity: 1 !important;
    visibility: visible !important;
    text-decoration: none !important;
}
#colophon a:hover, .site-footer a:hover,
#colophon .menu-item a:hover, #colophon .elementor-nav-menu a:hover {
    color: #e4522b !important;
}

/* ── Footer body text ── */
#colophon p, #colophon li, #colophon span, #colophon small,
.site-footer p, .site-footer li, .site-footer span,
.ast-footer-widget-area p, .ast-footer-widget-area li {
    color: #aaaaaa !important;
}

/* ── Footer inputs ── */
#colophon input[type="email"], #colophon input[type="text"],
.site-footer input[type="email"], .site-footer input[type="text"] {
    background: #242424 !important;
    border: 1px solid #444 !important;
    color: #cccccc !important;
}
#colophon input[type="submit"], #colophon button[type="submit"],
.site-footer input[type="submit"] {
    background: #e4522b !important;
    color: #fff !important;
    border: none !important;
}
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
      if(el.classList && (el.classList.contains('elementor-button') || el.classList.contains('bg-btn'))) continue;
      var cs = getComputedStyle(el);
      var bg = parseColor(cs.backgroundColor);
      if(bg && bg.a > 0.15 && luminance(bg.r,bg.g,bg.b) > 0.62){
        el.style.setProperty('background-color', '#0d0d0d', 'important');
        if(cs.backgroundImage && cs.backgroundImage.indexOf('gradient')>-1 && cs.backgroundImage.indexOf('url(')===-1){
          el.style.setProperty('background-image', 'none', 'important');
        }
      }
      var col = parseColor(cs.color);
      if(col && col.a > 0.3 && luminance(col.r,col.g,col.b) < 0.30){
        var isOrange = col.r>150 && col.g<140 && col.b<110;
        if(!isOrange){
          var isHeading = /^H[1-6]$/.test(tag) || (el.className+'').match(/title|heading/i);
          el.style.setProperty('color', isHeading ? '#ffffff' : '#b5b5b5', 'important');
        }
      }
    }
  }

  function fixFooter(){
    var footer = document.querySelector('#colophon, .site-footer, footer');
    if(!footer) return;
    /* Fix ALL backgrounds in footer to #1a1a1a */
    footer.querySelectorAll('*').forEach(function(el){
      var tag = el.tagName;
      if(/^(IMG|SVG|PATH|VIDEO|IFRAME|CANVAS|SCRIPT|STYLE)$/.test(tag)) return;
      var cls = (el.className||'').toString();
      var isBtn = /elementor-button/.test(cls) || tag==='BUTTON'||(tag==='INPUT'&&(el.type==='submit'||el.type==='button'));
      var cs = getComputedStyle(el);
      var bg = parseColor(cs.backgroundColor);
      if(bg && bg.a > 0.05 && !isBtn){
        var bl = luminance(bg.r,bg.g,bg.b);
        if(bl > 0.4 || bl < 0.015) el.style.setProperty('background-color','#1a1a1a','important');
      }
      /* Fix ALL text including transparent — catch color:rgba(0,0,0,0) */
      if(!isBtn){
        var col = parseColor(cs.color);
        /* transparent OR too dark → make visible */
        if(!col || col.a < 0.4 || luminance(col.r,col.g,col.b) < 0.35){
          var isOrg = col && col.r>150 && col.g<130 && col.b<100;
          if(!isOrg){
            var isHd = /^H[1-6]$/.test(tag)||(cls).match(/heading|title/i);
            el.style.setProperty('color', isHd?'#ffffff':'#cccccc','important');
            el.style.setProperty('opacity','1','important');
            el.style.setProperty('visibility','visible','important');
          }
        }
      }
    });
    /* Second pass: force every anchor in footer */
    footer.querySelectorAll('a').forEach(function(a){
      var isBtn = /elementor-button/.test((a.className||'').toString());
      if(isBtn) return;
      var col = parseColor(getComputedStyle(a).color);
      if(!col || col.a < 0.5 || luminance(col.r,col.g,col.b) < 0.25){
        a.style.setProperty('color','#cccccc','important');
        a.style.setProperty('opacity','1','important');
      }
    });
  }

  if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded', function(){ fix(); fixFooter(); });
  } else { fix(); fixFooter(); }
  setTimeout(function(){ fix(); fixFooter(); }, 600);
  setTimeout(fixFooter, 1500);
  window.addEventListener('load', function(){ fix(); fixFooter(); });
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
