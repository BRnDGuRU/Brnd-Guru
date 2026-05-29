<?php
/**
 * Plugin Name: BrndGuru — Footer Dark Theme Fix
 * Description: Forces footer dark theme (#0d0d0d), visible nav links, orange accents. CSS in head+footer + JS pass.
 * Version: 1.2
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgfooter_run');
add_action('admin_init', function () {
    if (get_option('bgfooter_done') !== '3') bgfooter_run();
});

if (!function_exists('bgfooter_run')) :
function bgfooter_run() {
    if (class_exists('\Elementor\Plugin') && isset(\Elementor\Plugin::$instance->files_manager))
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    foreach ([WP_CONTENT_DIR.'/cache/swift-performance/', WP_CONTENT_DIR.'/cache/swift-performance-lite/'] as $dir) {
        if (!is_dir($dir)) continue;
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $f) { $f->isDir() ? @rmdir($f) : @unlink($f); }
    }
    if (class_exists('Swift_Performance')) do_action('swift_performance_clear_all_cache');
    wp_cache_flush();
    if (function_exists('opcache_reset')) opcache_reset();
    update_option('bgfooter_log', ['✓ Footer dark theme v1.2 applied, cache cleared']);
    update_option('bgfooter_done', '3');
}
endif;

function bgfooter_css_output() { ?>
<style id="bg-footer-css">
footer,footer *,.site-footer,.site-footer *,
.ast-footer-widget-area,.ast-footer-widget-area *,
.footer-widget-area,.footer-widget-area *,
.ast-footer-bottom-bar,.ast-footer-bottom-bar * {
    background-color:#0d0d0d !important;
}
/* headings */
footer h1,footer h2,footer h3,footer h4,footer h5,footer h6,
.site-footer h1,.site-footer h2,.site-footer h3,.site-footer h4,.site-footer h5,.site-footer h6,
.ast-footer-widget-area h1,.ast-footer-widget-area h2,.ast-footer-widget-area h3,
.ast-footer-widget-area h4,.ast-footer-widget-area h5,.ast-footer-widget-area h6,
footer .elementor-heading-title,.site-footer .elementor-heading-title {
    color:#ffffff !important;
}
/* body text, list items */
footer p,footer li,footer span,footer small,footer label,
.site-footer p,.site-footer li,.site-footer span,
.ast-footer-widget-area p,.ast-footer-widget-area li,.ast-footer-widget-area span,
footer .elementor-widget-text-editor p,footer .elementor-icon-list-text {
    color:#b5b5b5 !important;
}
/* nav + quick-link anchors */
footer a,footer nav a,footer .menu a,footer .menu-item a,
footer .widget_nav_menu a,footer .widget_pages a,
footer .elementor-nav-menu a,.footer-widget-area a,
.site-footer a,.ast-footer-widget-area a,
footer .elementor-icon-list-item a,footer .elementor-icon-list-text {
    color:#b5b5b5 !important;
    text-decoration:none !important;
}
footer a:hover,footer nav a:hover,footer .menu a:hover,
footer .widget_nav_menu a:hover,.site-footer a:hover {
    color:#e4522b !important;
}
/* buttons */
footer .elementor-button,footer a.elementor-button,footer input[type="submit"],
footer button[type="submit"],.site-footer .elementor-button {
    background-color:#e4522b !important;
    color:#ffffff !important;
    border:none !important;
}
footer .elementor-button:hover,footer input[type="submit"]:hover { background-color:#c03b1e !important; }
/* form inputs */
footer input[type="email"],footer input[type="text"],footer textarea,
.site-footer input,.ast-footer-widget-area input {
    background-color:#1a1a1a !important;
    border:1px solid #333 !important;
    color:#b5b5b5 !important;
}
footer input::placeholder { color:#555 !important; }
/* dividers */
footer hr,footer .elementor-divider-separator,
.site-footer hr,.ast-footer-widget-area hr {
    border-color:#262626 !important;
    background-color:#262626 !important;
}
/* overlays */
footer .elementor-background-overlay { opacity:0 !important; }
/* bottom bar */
.ast-footer-bottom,.ast-footer-bottom-bar {
    border-top:1px solid #1e1e1e !important;
}
</style>
<?php }

/* inject in <head> so cached pages still get it */
add_action('wp_head', function () {
    if (!is_admin()) bgfooter_css_output();
}, 999);

/* inject again before </body> to override anything Elementor adds late */
add_action('wp_footer', function () {
    if (is_admin()) return;
    bgfooter_css_output();
    ?>
<script id="bg-footer-js">
(function(){
  function lum(r,g,b){return(0.2126*r+0.7152*g+0.0722*b)/255;}
  function parseRgb(s){
    if(!s)return null;
    var m=s.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
    return m?{r:+m[1],g:+m[2],b:+m[3],a:m[4]!=null?+m[4]:1}:null;
  }
  function fix(){
    var f=document.querySelector('footer.site-footer,footer,.site-footer,.ast-footer-widget-area');
    if(!f)return;
    f.querySelectorAll('*').forEach(function(el){
      var tag=el.tagName;
      if(/^(IMG|SVG|PATH|VIDEO|IFRAME|CANVAS)$/.test(tag))return;
      var cls=(el.className||'').toString();
      var isBtn=cls.match(/elementor-button|wp-block-button/)&&tag!=='SPAN';
      var cs=getComputedStyle(el);
      /* dark background */
      var bg=parseRgb(cs.backgroundColor);
      if(bg&&bg.a>0.1&&lum(bg.r,bg.g,bg.b)>0.5&&!isBtn)
        el.style.setProperty('background-color','#0d0d0d','important');
      /* visible text */
      var col=parseRgb(cs.color);
      if(col&&col.a>0.15&&!isBtn){
        var l=lum(col.r,col.g,col.b);
        if(l<0.25){/* too dark to read on dark bg */
          var orange=col.r>160&&col.g<130&&col.b<100;
          if(!orange){
            var head=/^H[1-6]$/.test(tag)||cls.match(/heading|title/i);
            el.style.setProperty('color',head?'#ffffff':'#b5b5b5','important');
          }
        }
      }
      /* anchors with inherited/zero colour */
      if(tag==='A'&&!isBtn){
        var ac=parseRgb(cs.color);
        if(!ac||ac.a<0.3)el.style.setProperty('color','#b5b5b5','important');
      }
    });
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',fix);
  else fix();
  setTimeout(fix,700);
  window.addEventListener('load',fix);
})();
</script>
<?php }, 9999);

add_action('admin_notices', function () {
    if (get_option('bgfooter_done') !== '3') return;
    echo '<div class="notice notice-success is-dismissible"><p><strong>✅ Footer Dark Theme v1.2</strong> — cache cleared. Check footer in incognito.</p></div>';
});
