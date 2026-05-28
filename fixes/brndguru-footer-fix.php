<?php
/**
 * Plugin Name: BrndGuru — Footer Dark Theme Fix
 * Description: Forces footer to dark theme (#0d0d0d) with light text, orange links. CSS + JS luminance pass.
 * Version: 1.0
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
footer, .footer, .site-footer, .elementor-section.elementor-footer,
.elementor-top-section.elementor-footer, .e-con.elementor-footer,
.ast-footer-widget-area, .footer-widget-area,
[class*="footer"] .elementor-section, [class*="footer"] .elementor-top-section,
[class*="footer"] .e-con, [class*="footer"] .elementor-column,
[class*="footer"] .elementor-widget-wrap {
    background-color: #0d0d0d !important;
}
footer .elementor-heading-title, footer .elementor-heading-title a,
footer .elementor-widget-heading h1, footer .elementor-widget-heading h2,
footer .elementor-widget-heading h3, footer .elementor-widget-heading h4,
footer .elementor-widget-heading h5, footer .elementor-widget-heading h6,
.footer-widget-area .elementor-heading-title, .site-footer .elementor-heading-title,
footer h1, footer h2, footer h3, footer h4, footer h5, footer h6,
[class*="footer"] h1, [class*="footer"] h2, [class*="footer"] h3,
[class*="footer"] h4, [class*="footer"] h5, [class*="footer"] h6 {
    color: #ffffff !important;
}
footer p, footer .elementor-widget-text-editor, footer .elementor-widget-text-editor p,
footer .elementor-icon-list-text, .footer-widget-area p, .site-footer p,
.footer-widget-area .elementor-widget-text-editor p,
[class*="footer"] p, [class*="footer"] .elementor-widget-text-editor p {
    color: #b5b5b5 !important;
}
footer a:not(.elementor-button):not(.button),
.footer-widget-area a:not(.elementor-button):not(.button),
.site-footer a:not(.elementor-button):not(.button),
[class*="footer"] a:not(.elementor-button):not(.button) {
    color: #e4522b !important;
    text-decoration: none;
}
footer a:not(.elementor-button):not(.button):hover,
.footer-widget-area a:not(.elementor-button):not(.button):hover,
.site-footer a:not(.elementor-button):not(.button):hover {
    color: #ff6b47 !important;
    text-decoration: underline;
}
footer .elementor-button, footer .elementor-button-link, footer a.elementor-button,
footer .button, .footer-widget-area .elementor-button, .site-footer .elementor-button {
    background-color: #e4522b !important;
    color: #ffffff !important;
    font-weight: 700 !important;
}
footer .elementor-button:hover, footer .elementor-button-link:hover,
footer a.elementor-button:hover, footer .button:hover {
    background-color: #c03b1e !important;
}
footer .elementor-divider-separator, footer hr,
.footer-widget-area .elementor-divider-separator, .site-footer .elementor-divider-separator,
[class*="footer"] .elementor-divider-separator, [class*="footer"] hr {
    border-color: #262626 !important;
}
footer .elementor-background-overlay, .footer-widget-area .elementor-background-overlay,
[class*="footer"] .elementor-background-overlay {
    background-color: transparent !important;
    opacity: 0 !important;
}
.site-footer .copyright, footer .copyright, [class*="footer"] .copyright,
.ast-footer-bottom, .footer-bottom {
    color: #b5b5b5 !important;
    border-color: #262626 !important;
}
.ast-footer-widget-area { background-color: #0d0d0d !important; }
.ast-footer-widget-area h1, .ast-footer-widget-area h2, .ast-footer-widget-area h3,
.ast-footer-widget-area h4, .ast-footer-widget-area h5, .ast-footer-widget-area h6 {
    color: #ffffff !important;
}
.ast-footer-widget-area p, .ast-footer-widget-area .elementor-widget-text-editor p {
    color: #b5b5b5 !important;
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
    var footer = document.querySelector('footer, .footer, .site-footer, .ast-footer-widget-area, [class*="footer"]');
    if(!footer) return;
    var all = footer.querySelectorAll('*');
    for(var i=0;i<all.length;i++){
      var el = all[i];
      var tag = el.tagName;
      if(tag==='IMG' || tag==='SVG' || tag==='PATH' || tag==='VIDEO' || tag==='IFRAME') continue;
      if(el.classList && (el.classList.contains('elementor-button') || el.classList.contains('button') || el.classList.contains('bg-btn'))) continue;
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
          var isHeading = /^H[1-6]$/.test(tag) || (el.className+'').match(/title|heading|h[1-6]|copyright/i);
          el.style.setProperty('color', isHeading ? '#ffffff' : '#b5b5b5', 'important');
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
