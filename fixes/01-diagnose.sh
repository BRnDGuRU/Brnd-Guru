#!/bin/bash
# ============================================================
# BRNDGURU.COM - DIAGNOSTIC SCRIPT
# Run this first to discover IDs needed for fix scripts.
# Usage: bash 01-diagnose.sh | tee diagnose-output.txt
# ============================================================

WP="wp --path=/var/www/html --allow-root"

echo "=============================="
echo "WordPress & Site Info"
echo "=============================="
$WP --info 2>/dev/null | grep -E "WP-CLI|WordPress"
$WP option get siteurl

echo ""
echo "=============================="
echo "Navigation Menus"
echo "=============================="
$WP menu list --fields=term_id,name,slug,locations

echo ""
echo "=============================="
echo "Menu Items (all menus)"
echo "=============================="
for MID in $($WP menu list --field=term_id); do
  MNAME=$($WP menu list --field=name --format=csv | sed -n "$(($(echo $($WP menu list --field=term_id) | tr ' ' '\n' | grep -n "^${MID}$" | cut -d: -f1) + 0))p" 2>/dev/null || echo "Menu $MID")
  echo ""
  echo "--- Menu ID: $MID ---"
  $WP menu item list $MID --fields=ID,title,url,type,menu_order
done

echo ""
echo "=============================="
echo "Pages (ID, title, status, slug)"
echo "=============================="
$WP post list --post_type=page --fields=ID,post_title,post_status,post_name --posts_per_page=50

echo ""
echo "=============================="
echo "About Page Post ID"
echo "=============================="
$WP post list --post_type=page --name=about --field=ID

echo ""
echo "=============================="
echo "Active SEO Plugin"
echo "=============================="
$WP plugin list --status=active --fields=name | grep -Ei "yoast|rankmath|rank-math|seo"

echo ""
echo "=============================="
echo "About Page - Yoast Meta"
echo "=============================="
ABOUT_ID=$($WP post list --post_type=page --name=about --field=ID 2>/dev/null | head -1)
if [ -n "$ABOUT_ID" ]; then
  echo "About page ID: $ABOUT_ID"
  $WP post meta get $ABOUT_ID _yoast_wpseo_metadesc 2>/dev/null || echo "(no yoast meta)"
  $WP post meta get $ABOUT_ID rank_math_description 2>/dev/null || echo "(no rankmath meta)"
  $WP post meta get $ABOUT_ID _yoast_wpseo_opengraph-image 2>/dev/null || echo "(no yoast OG image)"
fi

echo ""
echo "=============================="
echo "Header & Footer Templates (Elementor)"
echo "=============================="
$WP post list --post_type=elementor_library --fields=ID,post_title,post_status --posts_per_page=30

echo ""
echo "=============================="
echo "Active Theme"
echo "=============================="
$WP theme list --status=active

echo ""
echo "=============================="
echo "Cache Plugin"
echo "=============================="
$WP plugin list --status=active --fields=name | grep -Ei "rocket|litespeed|w3|super-cache"

echo ""
echo "DONE. Review output above before running fix scripts."
