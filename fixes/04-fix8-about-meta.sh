#!/bin/bash
# ============================================================
# FIX 8 — About Page: Fix meta description
# Sets a proper 156-char meta description on the About page.
# Supports Yoast SEO and RankMath.
# ============================================================

WP="wp --path=/var/www/html --allow-root"

NEW_META="Brnd Guru is a B2B digital marketing agency helping ambitious brands scale with strategy, design, and performance marketing. 200+ brands, 30+ industries."

echo "New meta description (${#NEW_META} chars):"
echo "  $NEW_META"
echo ""

# ---- STEP 1: Find the About page ID ------------------------
ABOUT_ID=$($WP post list --post_type=page --name=about --field=ID 2>/dev/null | head -1)

if [ -z "$ABOUT_ID" ]; then
  echo "Searching for About page by title..."
  ABOUT_ID=$($WP post list --post_type=page --fields=ID,post_title --format=json 2>/dev/null | \
    python3 -c "
import json, sys
items = json.load(sys.stdin)
for item in items:
    if 'about' in item.get('post_title','').lower():
        print(item['ID'])
" | head -1)
fi

if [ -z "$ABOUT_ID" ]; then
  echo "ERROR: Could not find About page. List pages:"
  $WP post list --post_type=page --fields=ID,post_title,post_name
  echo "Set ABOUT_ID manually: ABOUT_ID=<ID> bash $0"
  exit 1
fi

echo "About page ID: $ABOUT_ID"

# ---- STEP 2: Detect SEO plugin ----------------------------
ACTIVE_PLUGINS=$($WP plugin list --status=active --field=name 2>/dev/null)
echo ""

if echo "$ACTIVE_PLUGINS" | grep -qi "yoast\|wordpress-seo"; then
  echo "Detected: Yoast SEO"
  # Update Yoast meta description
  $WP post meta update $ABOUT_ID _yoast_wpseo_metadesc "$NEW_META"
  echo "Updated _yoast_wpseo_metadesc"

  # Verify
  CURRENT=$($WP post meta get $ABOUT_ID _yoast_wpseo_metadesc 2>/dev/null)
  echo "Verified value: $CURRENT"

elif echo "$ACTIVE_PLUGINS" | grep -qi "seo-by-rank-math\|rank-math\|rankmath"; then
  echo "Detected: RankMath SEO"
  $WP post meta update $ABOUT_ID rank_math_description "$NEW_META"
  echo "Updated rank_math_description"

  CURRENT=$($WP post meta get $ABOUT_ID rank_math_description 2>/dev/null)
  echo "Verified value: $CURRENT"

else
  echo "No known SEO plugin detected. Trying both Yoast and RankMath keys..."
  $WP post meta update $ABOUT_ID _yoast_wpseo_metadesc "$NEW_META" 2>/dev/null && echo "Set Yoast key"
  $WP post meta update $ABOUT_ID rank_math_description "$NEW_META" 2>/dev/null && echo "Set RankMath key"
  echo "Active plugins:"
  echo "$ACTIVE_PLUGINS"
fi

echo ""
echo "FIX 8 COMPLETE."
echo "Verify: curl -s 'https://brndguru.com/about/' | grep -o 'content=\"[^\"]*\"' | grep -i 'brnd guru'"
