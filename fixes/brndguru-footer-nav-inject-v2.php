<?php
/**
 * Plugin Name: BrndGuru Footer Nav Inject v2
 * Description: Hide broken Elementor footer. Replace with light beige footer.
 * Version:     2.1
 * Author:      BrndGuru
 */
defined('ABSPATH') || exit;

/* Hide ALL footer elements completely */
add_action('wp_head', function () { ?>
<style id="bgnav-hide-old">
/* Nuke the entire Elementor footer */
#colophon { display: none !important; visibility: hidden !important; height: 0 !important; margin: 0 !important; padding: 0 !important; }
.site-footer { display: none !important; visibility: hidden !important; height: 0 !important; margin: 0 !important; padding: 0 !important; }
.ast-footer-widget-area { display: none !important; visibility: hidden !important; height: 0 !important; margin: 0 !important; padding: 0 !important; }
.ast-footer-below-section { display: none !important; visibility: hidden !important; height: 0 !important; margin: 0 !important; padding: 0 !important; }
.ast-footer-bottom-bar { display: none !important; visibility: hidden !important; height: 0 !important; margin: 0 !important; padding: 0 !important; }
.footer-widget-area { display: none !important; visibility: hidden !important; height: 0 !important; margin: 0 !important; padding: 0 !important; }
footer { display: none !important; visibility: hidden !important; height: 0 !important; margin: 0 !important; padding: 0 !important; }

/* NEW light beige footer */
#bgnav-footer {
    background: #FEF1E4 !important;
    padding: 60px 40px 32px !important;
    margin: 0 !important;
    width: 100% !important;
    font-family: inherit !important;
}
#bgnav-footer * { box-sizing: border-box; }
#bgnav-footer .bgnav-top {
    max-width: 1200px;
    margin: 0 auto 40px;
    display: flex;
    flex-wrap: wrap;
    gap: 40px;
    justify-content: space-between;
    align-items: flex-start;
}
#bgnav-footer .bgnav-brand { flex: 0 0 220px; }
#bgnav-footer .bgnav-logo {
    font-size: 26px;
    font-weight: 900;
    letter-spacing: -0.5px;
    color: #222222;
    margin: 0 0 14px;
    line-height: 1;
}
#bgnav-footer .bgnav-logo span { color: #e4522b; }
#bgnav-footer .bgnav-tagline {
    color: #555555;
    font-size: 13px;
    line-height: 1.6;
    margin: 0 0 20px;
}
#bgnav-footer .bgnav-socials { display: flex; gap: 14px; }
#bgnav-footer .bgnav-socials a {
    display: flex; align-items: center; justify-content: center;
    width: 36px; height: 36px;
    background: #e4522b;
    border-radius: 50%;
    color: #ffffff !important;
    font-size: 15px;
    text-decoration: none !important;
    transition: background .2s;
}
#bgnav-footer .bgnav-socials a:hover { background: #c03b1e; }
#bgnav-footer .bgnav-col { flex: 1 1 140px; }
#bgnav-footer .bgnav-col-heading {
    color: #222222 !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    letter-spacing: 2px !important;
    text-transform: uppercase !important;
    margin: 0 0 20px !important;
    opacity: 1 !important;
}
#bgnav-footer .bgnav-col ul {
    list-style: none !important;
    margin: 0 !important; padding: 0 !important;
}
#bgnav-footer .bgnav-col ul li {
    margin-bottom: 11px !important;
    display: block !important;
}
#bgnav-footer .bgnav-col ul li a {
    color: #555555 !important;
    font-size: 14px !important;
    text-decoration: none !important;
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
    line-height: 1.4 !important;
    transition: color .15s !important;
}
#bgnav-footer .bgnav-col ul li a:hover { color: #e4522b !important; }
#bgnav-footer .bgnav-cta { flex: 0 0 240px; }
#bgnav-footer .bgnav-cta-label {
    color: #222222 !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    letter-spacing: 2px !important;
    text-transform: uppercase !important;
    margin: 0 0 10px !important;
    opacity: 1 !important;
}
#bgnav-footer .bgnav-cta-title {
    color: #222222 !important;
    font-size: 17px !important;
    font-weight: 700 !important;
    margin: 0 0 18px !important;
    line-height: 1.35 !important;
}
#bgnav-footer .bgnav-cta a.bgnav-btn {
    display: inline-block !important;
    background: #e4522b !important;
    color: #ffffff !important;
    font-size: 14px !important;
    font-weight: 800 !important;
    padding: 13px 28px !important;
    border-radius: 8px !important;
    text-decoration: none !important;
    letter-spacing: 0.3px !important;
    transition: opacity .2s !important;
    border: none !important;
    cursor: pointer !important;
}
#bgnav-footer .bgnav-cta a.bgnav-btn:hover { opacity: 0.88 !important; }
#bgnav-footer .bgnav-divider {
    max-width: 1200px;
    margin: 0 auto 24px;
    border: none;
    border-top: 1px solid rgba(0,0,0,0.1);
}
#bgnav-footer .bgnav-bottom {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
#bgnav-footer .bgnav-copy,
#bgnav-footer .bgnav-copy a {
    color: #777777 !important;
    font-size: 12px !important;
    text-decoration: none !important;
}
#bgnav-footer .bgnav-copy a:hover { color: #e4522b !important; }

@media (max-width: 900px) {
    #bgnav-footer { padding: 48px 24px 28px; }
    #bgnav-footer .bgnav-top { gap: 32px; }
    #bgnav-footer .bgnav-brand { flex: 0 0 100%; }
    #bgnav-footer .bgnav-cta { flex: 0 0 100%; }
    #bgnav-footer .bgnav-bottom { flex-direction: column; text-align: center; }
}
</style>
<?php }, 100);

add_action('wp_footer', function () {
    if (is_admin()) return;

    $all_menus = wp_get_nav_menus();
    $menu_map  = [];
    foreach ($all_menus as $m) {
        $menu_map[strtolower(trim($m->name))] = $m->term_id;
    }

    $render_menu = function($menu_id) {
        $items = wp_get_nav_menu_items($menu_id);
        if (!$items) return '';
        $out = '';
        foreach ($items as $item) {
            if ((int)$item->menu_item_parent !== 0) continue;
            $out .= '<li><a href="' . esc_url($item->url) . '">' . esc_html($item->title) . '</a></li>';
        }
        return $out;
    };

    $ql = '';
    foreach (['quick links','quick-links','quicklinks','footer','footer links','footer-links','main','primary'] as $k) {
        if (isset($menu_map[$k]) && ($ql = $render_menu($menu_map[$k]))) break;
    }
    if (!$ql) $ql = '
        <li><a href="' . home_url('/') . '">Home</a></li>
        <li><a href="' . home_url('/about-us/') . '">About Us</a></li>
        <li><a href="' . home_url('/services/') . '">Services</a></li>
        <li><a href="' . home_url('/case-studies/') . '">Case Studies</a></li>
        <li><a href="' . home_url('/blog/') . '">Blog</a></li>
        <li><a href="' . home_url('/contact/') . '">Contact</a></li>';

    $sv = '';
    foreach (['services','footer services','our services','services menu'] as $k) {
        if (isset($menu_map[$k]) && ($sv = $render_menu($menu_map[$k]))) break;
    }
    if (!$sv) $sv = '
        <li><a href="' . home_url('/services/linkedin-automation/') . '">LinkedIn Automation</a></li>
        <li><a href="' . home_url('/services/cold-email/') . '">Cold Email</a></li>
        <li><a href="' . home_url('/services/ai-agents/') . '">AI Agents</a></li>
        <li><a href="' . home_url('/services/gohighlevel-crm/') . '">GoHighLevel CRM</a></li>
        <li><a href="' . home_url('/services/n8n-automation/') . '">n8n Automation</a></li>';

    ?>
<div id="bgnav-footer">
    <div class="bgnav-top">
        <div class="bgnav-brand">
            <p class="bgnav-logo">BRn<span>D</span> Gu<span>RU</span></p>
            <p class="bgnav-tagline">London-based B2B outbound<br>agency. We fill your pipeline.</p>
            <div class="bgnav-socials">
                <a href="https://facebook.com/brndguru" aria-label="Facebook">f</a>
                <a href="https://linkedin.com/company/brndguru" aria-label="LinkedIn">in</a>
                <a href="https://pinterest.com/brndguru" aria-label="Pinterest">P</a>
            </div>
        </div>
        <div class="bgnav-col">
            <p class="bgnav-col-heading">Quick Links</p>
            <ul><?php echo $ql; ?></ul>
        </div>
        <div class="bgnav-col">
            <p class="bgnav-col-heading">Services</p>
            <ul><?php echo $sv; ?></ul>
        </div>
        <div class="bgnav-cta">
            <p class="bgnav-cta-label">Get Growth Insights</p>
            <p class="bgnav-cta-title">Ready to fill your pipeline?</p>
            <a href="<?php echo home_url('/contact/'); ?>" class="bgnav-btn">Book a Free Call &rarr;</a>
        </div>
    </div>
    <hr class="bgnav-divider">
    <div class="bgnav-bottom">
        <span class="bgnav-copy">&copy; <?php echo date('Y'); ?> BRND GURU Ltd. All rights reserved. &nbsp;·&nbsp; London, UK</span>
        <span class="bgnav-copy">
            <a href="<?php echo home_url('/privacy-policy/'); ?>">Privacy Policy</a>
            &nbsp;·&nbsp;
            <a href="<?php echo home_url('/contact/'); ?>">Contact</a>
        </span>
    </div>
</div>
<?php }, 99999);
