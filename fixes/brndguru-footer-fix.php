<?php
/**
 * Plugin Name: BrndGuru Footer Dark Fix
 * Description: Dark footer styling with visible nav links and orange accents.
 * Version:     2.1
 * Author:      BrndGuru
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head',   'brndguru_footer_css', 100 );
add_action( 'wp_footer', 'brndguru_footer_css', 100 );
add_action( 'wp_footer', 'brndguru_footer_js',  101 );

function brndguru_footer_css() {
    ?>
    <style id="brndguru-footer-css">

    /* ── Every possible footer wrapper ── */
    #colophon, #colophon *:not(img):not(svg):not(path):not(iframe):not(input):not(button),
    .site-footer, .site-footer *:not(img):not(svg):not(path):not(iframe):not(input):not(button),
    .ast-footer-widget-area, .ast-footer-widget-area *:not(img):not(svg):not(path),
    .ast-footer-below-section, .ast-footer-below-section *,
    .footer-widget-area, .footer-widget-area *,
    .ast-footer-bottom-bar, .ast-footer-bottom-bar * {
        background-color: #0d0d0d !important;
    }

    /* ── Headings ── */
    #colophon h1, #colophon h2, #colophon h3, #colophon h4, #colophon h5, #colophon h6,
    .site-footer h1, .site-footer h2, .site-footer h3,
    .site-footer h4, .site-footer h5, .site-footer h6,
    .ast-footer-widget-area h1, .ast-footer-widget-area h2,
    .ast-footer-widget-area h3, .ast-footer-widget-area h4,
    .ast-footer-widget-area h5, .ast-footer-widget-area h6,
    #colophon .elementor-heading-title,
    .site-footer .elementor-heading-title {
        color: #ffffff !important;
    }

    /* ── Body text ── */
    #colophon p, #colophon li, #colophon span, #colophon small, #colophon label,
    .site-footer p, .site-footer li, .site-footer span, .site-footer small,
    .ast-footer-widget-area p, .ast-footer-widget-area li, .ast-footer-widget-area span,
    .footer-widget-area p, .footer-widget-area li {
        color: #b5b5b5 !important;
    }

    /* ── ALL links — highest possible specificity ── */
    #colophon a, #colophon a:link, #colophon a:visited,
    .site-footer a, .site-footer a:link, .site-footer a:visited,
    .ast-footer-widget-area a, .ast-footer-widget-area a:link,
    .footer-widget-area a,
    /* Elementor nav menu in footer */
    #colophon .elementor-nav-menu a,
    #colophon .elementor-nav-menu--main .elementor-item,
    #colophon .elementor-nav-menu .elementor-item,
    #colophon nav a, #colophon .menu-item a,
    #colophon .widget_nav_menu a, #colophon .widget_nav_menu li a,
    #colophon .elementor-icon-list-item a,
    #colophon .elementor-icon-list-text,
    .site-footer .elementor-nav-menu a,
    .site-footer .elementor-nav-menu--main .elementor-item,
    .site-footer .menu-item a,
    .site-footer .widget_nav_menu a,
    .site-footer .elementor-icon-list-item a,
    .site-footer .elementor-icon-list-text {
        color: #b5b5b5 !important;
        text-decoration: none !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    #colophon a:hover, .site-footer a:hover,
    #colophon .menu-item a:hover, .site-footer .menu-item a:hover,
    #colophon .elementor-nav-menu a:hover, .site-footer .elementor-nav-menu a:hover {
        color: #e4522b !important;
    }

    /* ── Buttons ── */
    #colophon .elementor-button, #colophon a.elementor-button,
    .site-footer .elementor-button, .site-footer a.elementor-button {
        background-color: #e4522b !important;
        color: #ffffff !important;
        border: none !important;
    }

    /* ── Form inputs ── */
    #colophon input[type="email"], #colophon input[type="text"],
    #colophon input[type="search"], #colophon textarea,
    .site-footer input[type="email"], .site-footer input[type="text"] {
        background: #1a1a1a !important;
        border: 1px solid #333 !important;
        color: #b5b5b5 !important;
        border-radius: 4px !important;
    }
    #colophon input[type="submit"], #colophon button[type="submit"],
    .site-footer input[type="submit"], .site-footer button[type="submit"] {
        background: #e4522b !important;
        color: #fff !important;
        border: none !important;
    }

    /* ── Dividers ── */
    #colophon hr, .site-footer hr,
    #colophon .elementor-divider-separator,
    .site-footer .elementor-divider-separator {
        border-color: #2a2a2a !important;
        background: #2a2a2a !important;
    }

    /* ── Overlays off ── */
    #colophon .elementor-background-overlay,
    .site-footer .elementor-background-overlay {
        opacity: 0 !important;
    }
    </style>
    <?php
}

function brndguru_footer_js() {
    ?>
    <script id="brndguru-footer-js">
    (function () {
        function lum(r, g, b) { return (0.2126 * r + 0.7152 * g + 0.0722 * b) / 255; }
        function toRgb(str) {
            var m = str && str.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
            return m ? { r: +m[1], g: +m[2], b: +m[3], a: m[4] != null ? +m[4] : 1 } : null;
        }
        function fix() {
            var footer = document.querySelector('#colophon, .site-footer, footer, .ast-footer-widget-area');
            if (!footer) return;

            footer.querySelectorAll('*').forEach(function (el) {
                var tag = el.tagName;
                if (/^(IMG|SVG|PATH|VIDEO|IFRAME|CANVAS|SCRIPT|STYLE)$/.test(tag)) return;

                var cls = (el.className || '').toString();
                var isBtn = /elementor-button|wp-block-button/.test(cls)
                    || tag === 'BUTTON'
                    || (tag === 'INPUT' && (el.type === 'submit' || el.type === 'button'));

                var cs = window.getComputedStyle(el);

                /* Fix light backgrounds */
                var bg = toRgb(cs.backgroundColor);
                if (bg && bg.a > 0.05 && lum(bg.r, bg.g, bg.b) > 0.4 && !isBtn)
                    el.style.setProperty('background-color', '#0d0d0d', 'important');

                /* Fix invisible / dark text */
                if (!isBtn) {
                    var col = toRgb(cs.color);
                    /* catch: too dark, or near-transparent (invisible) */
                    if (col && (col.a < 0.3 || lum(col.r, col.g, col.b) < 0.3)) {
                        var isOrange = col.r > 160 && col.g < 130 && col.b < 100;
                        if (!isOrange) {
                            var isHead = /^H[1-6]$/.test(tag) || /heading|title/i.test(cls);
                            el.style.setProperty('color', isHead ? '#ffffff' : '#b5b5b5', 'important');
                            el.style.setProperty('opacity', '1', 'important');
                            el.style.setProperty('visibility', 'visible', 'important');
                        }
                    }
                }
            });
        }

        document.readyState === 'loading'
            ? document.addEventListener('DOMContentLoaded', fix)
            : fix();
        setTimeout(fix, 500);
        setTimeout(fix, 1500);
        window.addEventListener('load', fix);
    })();
    </script>
    <?php
}
