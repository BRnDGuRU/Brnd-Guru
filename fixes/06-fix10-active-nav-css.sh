#!/bin/bash
# ============================================================
# FIX 10 — Navigation: Add active state CSS for current page
# Adds CSS to highlight the current nav item in WordPress.
# Uses wp_head hook via Additional CSS in Customizer.
# ============================================================

WP="wp --path=/var/www/html --allow-root"

CSS='
/* Active nav item highlight */
.current-menu-item > a,
.current-menu-ancestor > a,
.current-menu-parent > a {
  font-weight: 600 !important;
  border-bottom: 2px solid currentColor;
}
/* Elementor nav menu active state */
.elementor-nav-menu .elementor-item.elementor-item-active,
.elementor-nav-menu .elementor-item.highlighted {
  font-weight: 600 !important;
  border-bottom: 2px solid currentColor;
}
'

# ---- Get active theme name --------------------------------
THEME=$($WP theme list --status=active --field=name 2>/dev/null | head -1)
echo "Active theme: $THEME"
echo ""

# ---- Check for existing Additional CSS --------------------
EXISTING=$($WP option get theme_mods_$THEME 2>/dev/null | python3 -c "
import sys, json
try:
    data = json.load(sys.stdin)
    print(data.get('custom_css', ''))
except: pass
" 2>/dev/null)

echo "Existing custom CSS (${#EXISTING} chars)"

# ---- Check if our CSS already exists ----------------------
if echo "$EXISTING" | grep -q "current-menu-item"; then
  echo "Active nav CSS already present. No change needed."
  exit 0
fi

# ---- Append to existing Additional CSS via Customizer ----
# wp_customize option is stored as custom_css post
CUSTOM_CSS_POST=$($WP post list --post_type=custom_css --field=ID 2>/dev/null | head -1)

if [ -n "$CUSTOM_CSS_POST" ]; then
  echo "Found custom_css post ID: $CUSTOM_CSS_POST"
  CURRENT_CSS=$($WP post get $CUSTOM_CSS_POST --field=post_content 2>/dev/null)
  NEW_CSS="${CURRENT_CSS}
${CSS}"
  $WP post update $CUSTOM_CSS_POST --post_content="$NEW_CSS"
  echo "Updated custom_css post with active nav CSS"
else
  echo "No custom_css post found. Trying theme_mods approach..."

  # Try adding via wp_add_custom_css (WP 4.7+)
  THEME_SLUG=$($WP theme list --status=active --field=stylesheet 2>/dev/null | head -1)

  # Create/update the custom_css post
  $WP post create \
    --post_type=custom_css \
    --post_title="$THEME_SLUG" \
    --post_name="$THEME_SLUG" \
    --post_status=publish \
    --post_content="$CSS" \
    --porcelain && echo "Created custom_css post" || \
    echo "WARNING: Could not create custom_css post. Add CSS manually via Appearance > Customize > Additional CSS"
fi

echo ""
echo "CSS to add (if manual step needed):"
echo "Go to: WordPress Admin → Appearance → Customize → Additional CSS"
echo "Paste:"
echo "$CSS"
echo ""
echo "FIX 10 COMPLETE."
