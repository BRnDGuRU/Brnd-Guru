#!/bin/bash
# ============================================================
# FIX 11 — Navigation: Remove "Get a Quote" as a separate nav item
# The Contact page already handles quote requests.
# This script removes the redundant "Get a Quote" nav item.
# ============================================================

WP="wp --path=/var/www/html --allow-root"

echo "=============================="
echo "Current menu structure:"
echo "=============================="
for MID in $($WP menu list --field=term_id 2>/dev/null); do
  MNAME=$($WP menu list --fields=term_id,name --format=json 2>/dev/null | \
    python3 -c "import json,sys; d=json.load(sys.stdin); [print(m['name']) for m in d if str(m.get('term_id',''))==str($MID)]" 2>/dev/null)
  echo ""
  echo "Menu ID: $MID ($MNAME)"
  $WP menu item list $MID --fields=ID,title,url,type,menu_order
done

echo ""
echo "=============================="

# ---- Find "Get a Quote" item across all menus -------------
QUOTE_ITEMS=$($WP menu list --field=term_id 2>/dev/null | while read MID; do
  $WP menu item list $MID --format=json 2>/dev/null | python3 -c "
import json, sys
try:
    items = json.load(sys.stdin)
    for item in items:
        t = item.get('title','').lower()
        if 'quote' in t or ('get a' in t and 'quote' in t):
            print(f\"{$MID},{item['ID']},{item['title']}\")
except: pass
"
done)

if [ -z "$QUOTE_ITEMS" ]; then
  echo "No 'Get a Quote' nav item found automatically."
  echo "Check the menu listings above and remove it manually if needed."
  echo ""
  echo "Manual removal command:"
  echo "  wp --path=/var/www/html --allow-root menu item delete <ITEM_ID>"
  exit 0
fi

echo "Found 'Get a Quote' items:"
echo "$QUOTE_ITEMS"
echo ""

# ---- Confirm and remove -----------------------------------
echo "$QUOTE_ITEMS" | while IFS=',' read MENU_ID ITEM_ID TITLE; do
  echo "Removing nav item: '$TITLE' (ID: $ITEM_ID) from menu $MENU_ID"
  $WP menu item delete $ITEM_ID
  echo "Removed."
done

echo ""
echo "FIX 11 COMPLETE."
echo ""
echo "NEXT: Add a 'Get a Quote' CTA section to the /contact/ page via Elementor."
echo "  - Add a heading: 'Get a Quote'"
echo "  - Add a button linking to: https://brndgurumedia.com/widget/bookings/brndguru"
