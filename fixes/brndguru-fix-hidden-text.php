<?php
/**
 * Plugin Name: BrndGuru — Fix Hidden Service Text
 * Description: Restores hidden service description text on Services page. Removes the bad p:only-child CSS.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

/* Override the bad CSS rule that was hiding description paragraphs */
add_action('wp_head', function () { ?>
<style id="bg-fix-hidden-text">
/* RESTORE: service description text that was incorrectly hidden */
body.page-id-4325 .elementor-widget-text-editor p:only-child,
body.page-id-4325 .elementor-widget-text-editor p,
.elementor-widget-text-editor p:only-child,
.elementor-widget-text-editor p {
    display: block !important;
    visibility: visible !important;
    font-size: inherit !important;
    line-height: inherit !important;
    height: auto !important;
    overflow: visible !important;
    margin-bottom: 1em !important;
    padding: 0 !important;
}

/* Style the number watermarks nicely instead of hiding them */
/* They show as 01, 02, 03 etc — make them look like intentional design */
</style>
<?php }, 1000); /* Priority 1000 — overrides all previous CSS */
