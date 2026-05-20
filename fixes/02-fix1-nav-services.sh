#!/bin/bash
# ============================================================
# FIX 1 — Navigation: Remove broken "#" href from Services menu item
# Changes href="#" → href="/services/" on the Services nav item.
# Run AFTER 01-diagnose.sh so you know the correct MENU_ID.
# ============================================================

WP="wp --path=/var/www/html --allow-root"

# ---- STEP 1: List all menus to find the primary nav --------
echo "Available menus:"
$WP menu list --fields=term_id,name,slug,locations
echo ""

# ---- STEP 2: Find the primary/header menu ID ---------------
# Edit PRIMARY_MENU_ID if your primary menu has a different ID
PRIMARY_MENU_ID=$($WP menu list --field=term_id 2>/dev/null | head -1)
echo "Using menu ID: $PRIMARY_MENU_ID (edit script if this is wrong)"
echo ""

# ---- STEP 3: List items in that menu -----------------------
echo "Current menu items:"
$WP menu item list $PRIMARY_MENU_ID --fields=ID,title,url,type,menu_order
echo ""

# ---- STEP 4: Find the Services item with href="#" ----------
SERVICES_ITEM_ID=$($WP menu item list $PRIMARY_MENU_ID --format=json 2>/dev/null | \
  python3 -c "
import json, sys
items = json.load(sys.stdin)
for item in items:
    if item.get('url') == '#' and 'service' in item.get('title','').lower():
        print(item['ID'])
" 2>/dev/null)

if [ -z "$SERVICES_ITEM_ID" ]; then
  echo "Auto-detection failed. Listing all items with url='#':"
  $WP menu item list $PRIMARY_MENU_ID --format=json 2>/dev/null | \
    python3 -c "
import json, sys
items = json.load(sys.stdin)
for item in items:
    if item.get('url') == '#':
        print(f\"  ID={item['ID']}  title='{item['title']}'  url='{item['url']}'\")"
  echo ""
  echo "ERROR: Set SERVICES_ITEM_ID manually and re-run:"
  echo "  SERVICES_ITEM_ID=<ID> bash $0"
  exit 1
fi

echo "Found Services menu item ID: $SERVICES_ITEM_ID"

# ---- STEP 5: Check if /services/ page exists ---------------
SERVICES_PAGE=$($WP post list --post_type=page --name=services --field=ID 2>/dev/null | head -1)

if [ -z "$SERVICES_PAGE" ]; then
  echo "No /services/ page found. Creating a simple redirect page..."
  SERVICES_PAGE=$($WP post create \
    --post_type=page \
    --post_title="Services" \
    --post_name=services \
    --post_status=publish \
    --post_content='<!-- wp:paragraph --><p>Redirecting...</p><!-- /wp:paragraph -->' \
    --porcelain)
  echo "Created /services/ page with ID: $SERVICES_PAGE"
else
  echo "Found existing /services/ page ID: $SERVICES_PAGE"
fi

# ---- STEP 6: Update the menu item URL ----------------------
$WP menu item update $SERVICES_ITEM_ID --url="/services/"
echo ""
echo "Updated Services nav item (ID: $SERVICES_ITEM_ID) → /services/"

# ---- STEP 7: Verify ----------------------------------------
echo ""
echo "Verification - menu item after update:"
$WP menu item list $PRIMARY_MENU_ID --fields=ID,title,url | grep -A1 -B1 "$SERVICES_ITEM_ID"

echo ""
echo "FIX 1 COMPLETE."
