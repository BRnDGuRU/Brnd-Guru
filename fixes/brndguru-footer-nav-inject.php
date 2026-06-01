<?php
/**
 * Plugin Name: BrndGuru Footer Nav Inject
 * Description: Bypasses Elementor nav widgets. Reads WP menus directly and injects visible styled links into the footer. Nuclear approach.
 * Version:     1.0
 * Author:      BrndGuru
 */
defined('ABSPATH') || exit;

/* ── CSS for the injected nav overlay ── */
add_action('wp_head', function () { ?>
<style id="bgnav-inject-css">
#bgnav-inject {
    background-color: #2a2a2a !important;
    padding: 52px 40px 28px !important;
    position: relative;
    z-index: 10;
    border-top: 2px solid rgba(228,82,43,0.4) !important;
}
#bgnav-inject .bgnav-grid {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap;
    gap: 48px;
    justify-content: space-between;
}
#bgnav-inject .bgnav-col { flex: 1 1 160px; }
#bgnav-inject .bgnav-heading {
    color: #ffffff !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    letter-spacing: 1.4px !important;
    text-transform: uppercase !important;
    margin: 0 0 18px !important;
    padding-bottom: 10px !important;
    border-bottom: 1px solid rgba(228,82,43,0.3) !important;
}
#bgnav-inject ul {
    list-style: none !important;
    margin: 0 !important;
    padding: 0 !important;
}
#bgnav-inject ul li {
    margin-bottom: 10px !important;
    display: block !important;
}
#bgnav-inject ul li a {
    color: #bbbbbb !important;
    font-size: 14px !important;
    text-decoration: none !important;
    line-height: 1.5 !important;
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
    transition: color .2s !important;
}
#bgnav-inject ul li a:hover {
    color: #e4522b !important;
}
#bgnav-inject .bgnav-bottom {
    max-width: 1200px;
    margin: 32px auto 0;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.07);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
#bgnav-inject .bgnav-copy {
    color: #666 !important;
    font-size: 12px !important;
}
#bgnav-inject .bgnav-copy a {
    color: #888 !important;
    text-decoration: none !important;
    font-size: 12px !important;
}

/* Hide the original Elementor nav menu widgets in footer so they don't show blank areas */
#colophon .elementor-widget-nav-menu,
#colophon .elementor-widget-icon-list,
.site-footer .elementor-widget-nav-menu,
.site-footer .elementor-widget-icon-list {
    display: none !important;
}

@media (max-width: 767px) {
    #bgnav-inject { padding: 40px 24px 20px !important; }
    #bgnav-inject .bgnav-grid { gap: 32px; flex-direction: column; }
    #bgnav-inject .bgnav-bottom { flex-direction: column; text-align: center; }
}
</style>
<?php }, 100);

/* ── Output the injected nav right before </body> ── */
add_action('wp_footer', function () {
    if (is_admin()) return;

    /* ── Build nav columns ── */
    $columns = [];

    /* Try to load registered WP nav menus */
    $all_menus = wp_get_nav_menus();
    $menu_map  = [];
    foreach ($all_menus as $m) {
        $menu_map[ strtolower($m->name) ] = $m->term_id;
    }

    /* Helper: render a WP nav menu as <li> items */
    $render_menu = function($menu_id) {
        $items = wp_get_nav_menu_items($menu_id);
        if (!$items) return '';
        $out = '';
        foreach ($items as $item) {
            $out .= '<li><a href="' . esc_url($item->url) . '">' . esc_html($item->title) . '</a></li>';
        }
        return $out;
    };

    /* Quick Links column */
    $ql_items = '';
    foreach (['quick links', 'quick-links', 'quicklinks', 'footer quick links', 'footer-quick-links'] as $k) {
        if (isset($menu_map[$k])) { $ql_items = $render_menu($menu_map[$k]); break; }
    }
    if (!$ql_items) {
        /* Fallback: hardcode standard site links */
        $ql_items = '
        <li><a href="' . home_url('/') . '">Home</a></li>
        <li><a href="' . home_url('/about-us/') . '">About Us</a></li>
        <li><a href="' . home_url('/services/') . '">Services</a></li>
        <li><a href="' . home_url('/case-studies/') . '">Case Studies</a></li>
        <li><a href="' . home_url('/blog/') . '">Blog</a></li>
        <li><a href="' . home_url('/contact/') . '">Contact</a></li>';
    }
    $columns[] = ['heading' => 'Quick Links', 'items' => $ql_items];

    /* Services column */
    $sv_items = '';
    foreach (['services', 'footer services', 'footer-services', 'our services'] as $k) {
        if (isset($menu_map[$k])) { $sv_items = $render_menu($menu_map[$k]); break; }
    }
    if (!$sv_items) {
        /* Fallback: hardcode known services */
        $sv_items = '
        <li><a href="' . home_url('/services/linkedin-automation/') . '">LinkedIn Automation</a></li>
        <li><a href="' . home_url('/services/cold-email/') . '">Cold Email</a></li>
        <li><a href="' . home_url('/services/ai-agents/') . '">AI Agents</a></li>
        <li><a href="' . home_url('/services/gohighlevel-crm/') . '">GoHighLevel CRM</a></li>
        <li><a href="' . home_url('/services/n8n-automation/') . '">n8n Automation</a></li>';
    }
    $columns[] = ['heading' => 'Services', 'items' => $sv_items];

    /* ── Output ── */
    echo '<div id="bgnav-inject">';
    echo '<div class="bgnav-grid">';
    foreach ($columns as $col) {
        echo '<div class="bgnav-col">';
        echo '<p class="bgnav-heading">' . esc_html($col['heading']) . '</p>';
        echo '<ul>' . $col['items'] . '</ul>';
        echo '</div>';
    }
    echo '</div>';
    echo '<div class="bgnav-bottom">';
    echo '<span class="bgnav-copy">&copy; ' . date('Y') . ' BRND GURU Ltd. All rights reserved.</span>';
    echo '<span class="bgnav-copy">London, UK &nbsp;·&nbsp; <a href="' . home_url('/contact/') . '">Contact Us</a></span>';
    echo '</div>';
    echo '</div>';
}, 99999);
