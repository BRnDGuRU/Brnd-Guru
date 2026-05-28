<?php
/**
 * Plugin Name: BrndGuru — Hide Top Strip
 * Description: Hides only the empty white strip above the main header. Safe — does not touch nav.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

add_action('wp_head', function () { ?>
<style id="bg-hide-top-strip">
/* Hide the empty Astra "above header" row — the white strip above the nav */
.site-above-header-wrap,
.hfb-above-header,
.ast-above-header-section { display: none !important; }

/* Remove any top margin/padding gap on the main header */
.site-header,
#masthead { margin-top: 0 !important; }

/* Hide empty page title bar (strip below header on some pages) */
.ast-page-title-bar:empty,
.page-title-bar:empty { display: none !important; }
</style>
<?php }, 20);
