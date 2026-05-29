<?php
/**
 * Plugin Name: BrndGuru — Footer Dark Theme Fix
 * Description: Forces footer to dark theme (#0d0d0d) with light text, orange links. CSS + JS luminance pass.
 * Version: 1.1
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgfooter_run');
add_action('admin_init', function () {
    if (get_option('bgfooter_done') !== '1') bgfooter_run();
});

function bgfooter_run() {
    global $wpdb;
    $log = [];

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

    $log[] = "✓ Footer dark theme applied with full cache clear";
    update_option('bgfooter_log', $log);
    update_option('bgfooter_done', '1');
}

add_action('wp_footer', function () {
    if (is_admin()) return;
    ?>
<style id="bg-footer-dark-css">
/* ── Backgrounds ── */
footer, .footer, .site-footer, .elementor-section.elementor-footer,
.elementor-top-section.elementor-footer, .e-con.elementor-footer,
.ast-footer-widget-area, .footer-widget-area,
[class*="footer"] .elementor-section, [class*="footer"] .elementor-top-section,
[class*="footer"] .e-con, [class*="footer"] .elementor-column,
[class*="footer"] .elementor-widget-wrap,
.ast-footer-bottom-bar, .footer-bottom-wrap, .site-footer > * {
    background-color: #0d0d0d !important;
}

/* ── Headings ── */
footer h1, footer h2, footer h3, footer h4, footer h5, footer h6,
footer .elementor-heading-title, footer .elementor-heading-title a,
.footer-widget-area h1, .footer-widget-area h2, .footer-widget-area h3,
.footer-widget-area h4, .footer-widget-area h5, .footer-widget-area h6,
.ast-footer-widget-area h1, .ast-footer-widget-area h2, .ast-footer-widget-area h3,
.ast-footer-widget-area h4, .ast-footer-widget-area h5, .ast-footer-widget-area h6,
[class*="footer"] h1, [class*="footer"] h2, [class*="footer"] h3,
[class*="footer"] h4, [class*="footer"] h5, [class*="footer"] h6 {
    color: #ffffff !important;
}

/* ── Body text & list labels ── */
footer p, footer span, footer small, footer label,
footer .elementor-widget-text-editor, footer .elementor-widget-text-editor p,
footer .elementor-icon-list-text, footer li, footer ul, footer ul li,
.footer-widget-area p, .footer-widget-area li, .footer-widget-area span,
.ast-footer-widget-area p, .ast-footer-widget-area li, .ast-footer-widget-area span,
.ast-footer-bottom-bar *, .footer-bottom-bar *,
[class*="footer"] p, [class*="footer"] span, [class*="footer"] li {
    color: #b5b5b5 !important;
}

/* ── Nav menu & quick-links visibility ── */
footer nav a, footer .menu a, footer .menu li a, footer .menu-item a,
footer .widget_nav_menu a, footer .widget_pages a, footer .widget_categories a,
footer .elementor-nav-menu a, footer .elementor-nav-menu--main .elementor-item,
footer .elementor-nav-menu .elementor-item,
footer .elementor-icon-list-item a, footer .elementor-icon-list-text,
.footer-widget-area .widget_nav_menu a, .footer-widget-area .menu li a,
.ast-footer-widget-area .widget_nav_menu a, .ast-footer-widget-area .menu li a,
[class*="footer"] .menu a, [class*="footer"] .menu-item a,
[class*="footer"] .widget_nav_menu a {
    color: #b5b5b5 !important;
    text-decoration: none !important;
}
footer nav a:hover, footer .menu a:hover, footer .menu-item a:hover,
footer .widget_nav_menu a:hover, footer .elementor-nav-menu a:hover,
footer .elementor-icon-list-item a:hover,
[class*="footer"] .menu a:hover, [class*="footer"] .widget_nav_menu a:hover {
    color: #e4522b !important;
}

/* ── Generic links (non-nav, non-button) ── */
footer a:not(.elementor-button):not(.button):not(.menu-item > a):not(.elementor-item),
.footer-widget-area a:not(.elementor-button):not(.button),
.site-footer a:not(.elementor-button):not(.button),
[class*="footer"] a:not(.elementor-button):not(.button) {
    color: #b5b5b5 !important;
    text-decoration: none;
}
footer a:not(.elementor-button):not(.button):hover,
.footer-widget-area a:not(.elementor-button):not(.button):hover,
.site-footer a:not(.elementor-button):not(.button):hover {
    color: #e4522b !important;
    text-decoration: none;
}

/* ── Buttons / CTAs ── */
footer .elementor-button, footer .elementor-button-link, footer a.elementor-button,
footer .button, footer input[type="submit"], footer button[type="submit"],
.footer-widget-area .elementor-button, .site-footer .elementor-button {
    background-color: #e4522b !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    border: none !important;
}
footer .elementor-button:hover, footer a.elementor-button:hover,
footer input[type="submit"]:hover, footer button[type="submit"]:hover {
    background-color: #c03b1e !important;
}

/* ── Newsletter / form inputs ── */
footer input[type="email"], footer input[type="text"], footer input[type="search"],
footer textarea, footer select,
.footer-widget-area input, .footer-widget-area textarea,
[class*="footer"] input[type="email"], [class*="footer"] input[type="text"] {
    background-color: #1a1a1a !important;
    border: 1px solid #333 !important;
    color: #b5b5b5 !important;
    border-radius: 4px !important;
}
footer input::placeholder, [class*="footer"] input::placeholder {
    color: #555 !important;
}

/* ── Dividers ── */
footer .elementor-divider-separator, footer hr,
[class*="footer"] .elementor-divider-separator, [class*="footer"] hr {
    border-color: #262626 !important;
    background-color: #262626 !important;
}

/* ── Overlays ── */
footer .elementor-background-overlay, [class*="footer"] .elementor-background-overlay {
    background-color: transparent !important;
    opacity: 0 !important;
}

/* ── Bottom bar / copyright ── */
.ast-footer-bottom, .footer-bottom, .ast-footer-bottom-bar,
.site-footer .copyright, footer .copyright, [class*="footer"] .copyright {
    color: #b5b5b5 !important;
    border-color: #262626 !important;
    background-color: #0d0d0d !important;
}

/* ── Social icons keep their shape ── */
footer .elementor-social-icon { opacity: 1 !important; }

/* ── Astra footer widgets ── */
.ast-footer-widget-area {
    background-color: #0d0d0d !important;
    border-top: 1px solid #1e1e1e !important;
}
</style>
<script id="bg-footer-dark-js">
(function(){
  function luminance(r,g,b){ return (0.2126*r + 0.7152*g + 0.0722*b)/255; }
  function parseColor(str){
    if(!str) return null;
    var m = str.match(/rgba?\(([^)]+)\)/);
    if(!m) return null;
    var p = m[1].split(',').map(function(x){return parseFloat(x.trim());});
    return {r:p[0], g:p[1], b:p[2], a:(p.length>3? p[3] : 1)};
  }
  function fixFooter(){
    var footerEl = document.querySelector('footer.site-footer, footer, .site-footer, .ast-footer-widget-area');
    if(!footerEl) return;
    var all = footerEl.querySelectorAll('*');
    for(var i=0;i<all.length;i++){
      var el = all[i];
      var tag = el.tagName;
      if(tag==='IMG'||tag==='SVG'||tag==='PATH'||tag==='VIDEO'||tag==='IFRAME'||tag==='CANVAS') continue;
      var cls = (el.className||'').toString();
      var isBtn = cls.match(/elementor-button|^button|bg-btn/) || (tag==='INPUT'&&el.type==='submit') || tag==='BUTTON';
      var isSocial = cls.match(/social-icon|social_icon/);
      var cs = getComputedStyle(el);
      /* fix light backgrounds */
      var bg = parseColor(cs.backgroundColor);
      if(bg && bg.a > 0.15 && luminance(bg.r,bg.g,bg.b) > 0.55 && !isBtn && !isSocial){
        el.style.setProperty('background-color','#0d0d0d','important');
        if(cs.backgroundImage&&cs.backgroundImage.indexOf('gradient')>-1&&cs.backgroundImage.indexOf('url(')===-1)
          el.style.setProperty('background-image','none','important');
      }
      /* fix dark/invisible text */
      var col = parseColor(cs.color);
      if(col && col.a > 0.2 && luminance(col.r,col.g,col.b) < 0.25 && !isBtn){
        var isOrange = col.r>150&&col.g<140&&col.b<110;
        if(!isOrange){
          var isHead = /^H[1-6]$/.test(tag)||cls.match(/title|heading|copyright/i);
          el.style.setProperty('color',isHead?'#ffffff':'#b5b5b5','important');
        }
      }
      /* nav/list anchors that are transparent color — force visible */
      if(tag==='A'&&!isBtn){
        var acol = parseColor(cs.color);
        if(!acol || (acol.a<0.4)){
          el.style.setProperty('color','#b5b5b5','important');
        }
      }
    }
  }
  if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', fixFooter); }
  else { fixFooter(); }
  setTimeout(fixFooter, 600);
  window.addEventListener('load', fixFooter);
})();
</script>
<?php }, 9999);

add_action('admin_notices', function () {
    if (get_option('bgfooter_done') !== '1') return;
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px 16px;">
        <h3 style="margin:0 0 8px;">✅ Footer Dark Theme Applied</h3>
        <ul style="margin:0 0 10px;padding-left:20px;font-size:13px;">
            <?php foreach (get_option('bgfooter_log', []) as $l): ?><li><?php echo esc_html($l); ?></li><?php endforeach; ?>
        </ul>
        <p style="margin:0;">Check footer on all pages for dark background, light text, orange links. Verify in incognito mode.</p>
    </div>
    <?php
});
