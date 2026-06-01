<?php
/**
 * Plugin Name: BrndGuru Footer Dark Fix
 * Description: Lighter dark footer (#2a2a2a) with visible nav links and orange accents.
 * Version:     2.3
 * Author:      BrndGuru
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head',   'brndguru_footer_css', 100 );
add_action( 'wp_footer', 'brndguru_footer_css', 10000 );
add_action( 'wp_footer', 'brndguru_footer_js',  10001 );

function brndguru_footer_css() {
    ?>
    <style id="brndguru-footer-css">

    /* ── Footer background — lighter dark so it stands out ── */
    #colophon,
    .site-footer,
    .ast-footer-widget-area,
    .ast-footer-below-section,
    .footer-widget-area,
    .ast-footer-bottom-bar {
        background-color: #2a2a2a !important;
    }

    /* also cover every child element */
    #colophon *:not(img):not(svg):not(path):not(video):not(iframe):not(input):not(button):not(select),
    .ast-footer-widget-area *:not(img):not(svg):not(path) {
        background-color: #2a2a2a !important;
    }

    /* ── Headings ── */
    #colophon h1, #colophon h2, #colophon h3,
    #colophon h4, #colophon h5, #colophon h6,
    #colophon .elementor-heading-title,
    .site-footer h1, .site-footer h2, .site-footer h3,
    .site-footer h4, .site-footer h5, .site-footer h6,
    .ast-footer-widget-area h1, .ast-footer-widget-area h2,
    .ast-footer-widget-area h3, .ast-footer-widget-area h4 {
        color: #ffffff !important;
    }

    /* ── Body text ── */
    #colophon p, #colophon li, #colophon span, #colophon small, #colophon label,
    .site-footer p, .site-footer li, .site-footer span,
    .ast-footer-widget-area p, .ast-footer-widget-area li {
        color: #aaaaaa !important;
    }

    /* ── Nav / quick-links — every possible selector ── */
    #colophon a,
    #colophon a:link,
    #colophon a:visited,
    #colophon ul li a,
    #colophon ul li a:link,
    #colophon ul li a:visited,
    #colophon nav a,
    #colophon nav ul li a,
    #colophon .menu a,
    #colophon .menu-item > a,
    #colophon .menu-item a,
    #colophon .sub-menu a,
    #colophon .widget_nav_menu a,
    #colophon .widget_nav_menu ul li a,
    #colophon .elementor-nav-menu a,
    #colophon .elementor-nav-menu li a,
    #colophon .elementor-nav-menu--main .elementor-item,
    #colophon .elementor-icon-list-item a,
    #colophon .elementor-icon-list-text,
    .site-footer a,
    .site-footer ul li a,
    .site-footer .menu-item a,
    .site-footer .widget_nav_menu a,
    .site-footer .elementor-nav-menu a,
    .site-footer .elementor-nav-menu--main .elementor-item,
    .ast-footer-widget-area a,
    .ast-footer-widget-area ul li a {
        color: #cccccc !important;
        text-decoration: none !important;
        opacity: 1 !important;
        visibility: visible !important;
        display: revert !important;
    }

    /* ── Hover ── */
    #colophon a:hover,
    #colophon ul li a:hover,
    #colophon .menu-item a:hover,
    #colophon .elementor-nav-menu a:hover,
    .site-footer a:hover,
    .ast-footer-widget-area a:hover {
        color: #e4522b !important;
    }

    /* ── Buttons — keep orange ── */
    #colophon .elementor-button,
    #colophon a.elementor-button,
    .site-footer .elementor-button {
        background-color: #e4522b !important;
        color: #ffffff !important;
        border: none !important;
    }

    /* ── Inputs ── */
    #colophon input[type="email"],
    #colophon input[type="text"],
    #colophon input[type="search"],
    #colophon textarea,
    .site-footer input[type="email"],
    .site-footer input[type="text"] {
        background: #242424 !important;
        border: 1px solid #444 !important;
        color: #cccccc !important;
        border-radius: 4px !important;
    }
    #colophon input[type="submit"],
    #colophon button[type="submit"],
    .site-footer input[type="submit"] {
        background: #e4522b !important;
        color: #fff !important;
        border: none !important;
    }

    /* ── Dividers ── */
    #colophon hr,
    .site-footer hr,
    #colophon .elementor-divider-separator,
    .site-footer .elementor-divider-separator {
        border-color: #333 !important;
        background: #333 !important;
    }

    /* ── Top border accent ── */
    #colophon { border-top: 2px solid rgba(228,82,43,0.4) !important; }

    /* ── Remove overlays ── */
    #colophon .elementor-background-overlay { opacity: 0 !important; }
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
            return m ? { r:+m[1], g:+m[2], b:+m[3], a: m[4]!=null ? +m[4] : 1 } : null;
        }
        function fix() {
            var footer = document.querySelector('#colophon, .site-footer, footer');
            if (!footer) return;
            footer.querySelectorAll('*').forEach(function (el) {
                var tag = el.tagName;
                if (/^(IMG|SVG|PATH|VIDEO|IFRAME|CANVAS|SCRIPT|STYLE)$/.test(tag)) return;
                var cls = (el.className || '').toString();
                var isBtn = /elementor-button/.test(cls) || tag === 'BUTTON'
                    || (tag === 'INPUT' && (el.type === 'submit' || el.type === 'button'));
                var cs = window.getComputedStyle(el);

                /* Fix too-dark or too-light backgrounds */
                var bg = toRgb(cs.backgroundColor);
                if (bg && bg.a > 0.05 && !isBtn) {
                    var bl = lum(bg.r, bg.g, bg.b);
                    if (bl > 0.5) el.style.setProperty('background-color', '#2a2a2a', 'important');
                    if (bl < 0.02) el.style.setProperty('background-color', '#2a2a2a', 'important');
                }

                /* Fix invisible or dark text */
                if (!isBtn) {
                    var col = toRgb(cs.color);
                    if (!col || col.a < 0.3 || lum(col.r, col.g, col.b) < 0.35) {
                        var isOrange = col && col.r > 160 && col.g < 130 && col.b < 100;
                        if (!isOrange) {
                            var isHead = /^H[1-6]$/.test(tag) || /heading|title/i.test(cls);
                            el.style.setProperty('color', isHead ? '#ffffff' : '#cccccc', 'important');
                            el.style.setProperty('opacity', '1', 'important');
                            el.style.setProperty('visibility', 'visible', 'important');
                        }
                    }
                }
            });

            /* Unconditional anchor pass */
            footer.querySelectorAll('a').forEach(function(a) {
                var isBtn = /elementor-button/.test((a.className||'').toString());
                if (isBtn) return;
                a.style.setProperty('color', '#cccccc', 'important');
                a.style.setProperty('opacity', '1', 'important');
                a.style.setProperty('visibility', 'visible', 'important');
                a.style.setProperty('display', 'block', 'important');
            });
            /* Force li items visible */
            footer.querySelectorAll('li').forEach(function(li) {
                li.style.setProperty('display', 'block', 'important');
                li.style.setProperty('visibility', 'visible', 'important');
                li.style.setProperty('opacity', '1', 'important');
            });
        }

        document.readyState === 'loading'
            ? document.addEventListener('DOMContentLoaded', fix) : fix();
        setTimeout(fix, 300);
        setTimeout(fix, 800);
        setTimeout(fix, 2000);
        window.addEventListener('load', fix);
    })();
    </script>
    <?php
}
