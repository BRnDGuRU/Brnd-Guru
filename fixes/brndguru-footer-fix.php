<?php
/**
 * Plugin Name: BrndGuru Footer Dark Fix
 * Description: Dark footer styling with visible nav links and orange accents.
 * Version:     2.0
 * Author:      BrndGuru
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', 'brndguru_footer_css', 100 );
add_action( 'wp_footer', 'brndguru_footer_css', 100 );
add_action( 'wp_footer', 'brndguru_footer_js', 101 );

function brndguru_footer_css() {
    ?>
    <style>
    /* backgrounds */
    .site-footer,
    .site-footer *:not(img):not(svg):not(path):not(iframe),
    .ast-footer-widget-area,
    .ast-footer-widget-area *:not(img):not(svg):not(path),
    .ast-footer-bottom-bar,
    .ast-footer-bottom-bar * {
        background-color: #0d0d0d !important;
        border-color: #1e1e1e !important;
    }

    /* headings */
    .site-footer h1, .site-footer h2, .site-footer h3,
    .site-footer h4, .site-footer h5, .site-footer h6,
    .site-footer .elementor-heading-title,
    .ast-footer-widget-area h1, .ast-footer-widget-area h2,
    .ast-footer-widget-area h3, .ast-footer-widget-area h4,
    .ast-footer-widget-area h5, .ast-footer-widget-area h6 {
        color: #ffffff !important;
    }

    /* body text */
    .site-footer p,
    .site-footer li,
    .site-footer span,
    .site-footer small,
    .site-footer label,
    .ast-footer-widget-area p,
    .ast-footer-widget-area li,
    .ast-footer-widget-area span {
        color: #b5b5b5 !important;
    }

    /* all links */
    .site-footer a,
    .ast-footer-widget-area a {
        color: #b5b5b5 !important;
        text-decoration: none !important;
    }
    .site-footer a:hover,
    .ast-footer-widget-area a:hover {
        color: #e4522b !important;
    }

    /* buttons */
    .site-footer .elementor-button,
    .site-footer a.elementor-button,
    .site-footer .wp-block-button__link,
    .ast-footer-widget-area .elementor-button {
        background-color: #e4522b !important;
        color: #ffffff !important;
        border: none !important;
    }
    .site-footer .elementor-button:hover,
    .site-footer a.elementor-button:hover {
        background-color: #c03b1e !important;
    }

    /* form inputs */
    .site-footer input[type="email"],
    .site-footer input[type="text"],
    .site-footer input[type="search"],
    .site-footer textarea,
    .ast-footer-widget-area input[type="email"],
    .ast-footer-widget-area input[type="text"] {
        background: #1a1a1a !important;
        border: 1px solid #333 !important;
        color: #b5b5b5 !important;
        border-radius: 4px !important;
    }
    .site-footer input::placeholder,
    .ast-footer-widget-area input::placeholder {
        color: #555 !important;
    }

    /* submit buttons */
    .site-footer input[type="submit"],
    .site-footer button[type="submit"],
    .ast-footer-widget-area input[type="submit"],
    .ast-footer-widget-area button[type="submit"] {
        background: #e4522b !important;
        color: #fff !important;
        border: none !important;
        cursor: pointer !important;
    }

    /* dividers */
    .site-footer hr,
    .site-footer .elementor-divider-separator,
    .ast-footer-widget-area hr {
        border-color: #2a2a2a !important;
        background: #2a2a2a !important;
    }

    /* remove overlays */
    .site-footer .elementor-background-overlay {
        opacity: 0 !important;
    }
    </style>
    <?php
}

function brndguru_footer_js() {
    ?>
    <script>
    (function () {
        function lum(r, g, b) { return (0.2126 * r + 0.7152 * g + 0.0722 * b) / 255; }
        function rgba(str) {
            var m = str && str.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
            return m ? { r: +m[1], g: +m[2], b: +m[3], a: m[4] != null ? +m[4] : 1 } : null;
        }
        function fix() {
            var footer = document.querySelector('.site-footer, footer, .ast-footer-widget-area');
            if (!footer) return;
            footer.querySelectorAll('*').forEach(function (el) {
                var tag = el.tagName;
                if (/^(IMG|SVG|PATH|VIDEO|IFRAME|CANVAS|SCRIPT|STYLE)$/.test(tag)) return;
                var cls = (el.className || '').toString();
                var isBtn = /elementor-button|wp-block-button/.test(cls) || tag === 'BUTTON' || (tag === 'INPUT' && el.type === 'submit');
                var cs = window.getComputedStyle(el);

                var bg = rgba(cs.backgroundColor);
                if (bg && bg.a > 0.1 && lum(bg.r, bg.g, bg.b) > 0.5 && !isBtn)
                    el.style.setProperty('background-color', '#0d0d0d', 'important');

                if (!isBtn) {
                    var col = rgba(cs.color);
                    if (col && col.a > 0.1 && lum(col.r, col.g, col.b) < 0.2) {
                        var isOrange = col.r > 160 && col.g < 130 && col.b < 100;
                        if (!isOrange) {
                            var isHead = /^H[1-6]$/.test(tag) || /heading|title/i.test(cls);
                            el.style.setProperty('color', isHead ? '#ffffff' : '#b5b5b5', 'important');
                        }
                    }
                    if (tag === 'A') {
                        var ac = rgba(cs.color);
                        if (!ac || ac.a < 0.2)
                            el.style.setProperty('color', '#b5b5b5', 'important');
                    }
                }
            });
        }
        document.readyState === 'loading'
            ? document.addEventListener('DOMContentLoaded', fix)
            : fix();
        setTimeout(fix, 800);
        window.addEventListener('load', fix);
    })();
    </script>
    <?php
}
