<?php
/**
 * Plugin Name: BrndGuru — Fix White Strip
 * Description: Hides the empty primary header row (white strip) WITHOUT touching the above-header nav row.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

add_action('wp_head', function () { ?>
<style id="bg-fix-white-strip">
/* Hide the EMPTY primary header row — this is the white strip.
   Your nav lives in .site-above-header-wrap so we leave that alone. */
.site-primary-header-wrap:empty,
.hfb-primary-header:empty,
.site-below-header-wrap:empty,
.hfb-below-header:empty,
.ast-above-header-section { display: none !important; }

/* Also hide the Astra page title bar below the header */
.ast-page-title-bar,
.page-title-bar,
.ast-page-title-wrap { display: none !important; }
</style>
<?php }, 20);
