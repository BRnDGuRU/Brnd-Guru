#!/bin/bash
# ============================================================
# FIX 3 — Footer: Fix Facebook icon URL
# The Facebook icon links to LinkedIn URL — fix it.
# This script patches the Elementor JSON in the footer template.
# ============================================================

WP="wp --path=/var/www/html --allow-root"

WRONG_FB_URL="https://www.linkedin.com/company/brndguru/"
CORRECT_FB_URL="https://www.facebook.com/brndguru"
LINKEDIN_URL="https://www.linkedin.com/company/brndguru/"

# ---- STEP 1: Find the footer template post -----------------
echo "Looking for Elementor footer templates..."
$WP post list --post_type=elementor_library --fields=ID,post_title,post_status --posts_per_page=50

echo ""
echo "Also checking: elementor_pro footer locations..."
FOOTER_ID=$($WP post list --post_type=elementor_library --format=json 2>/dev/null | \
  python3 -c "
import json, sys
items = json.load(sys.stdin)
for item in items:
    t = item.get('post_title','').lower()
    if 'footer' in t:
        print(item['ID'])
" 2>/dev/null | head -1)

# Also check theme builder conditions
if [ -z "$FOOTER_ID" ]; then
  echo "Trying theme builder conditions..."
  FOOTER_ID=$($WP post meta list --meta_key=_elementor_conditions --format=json 2>/dev/null | \
    python3 -c "
import json, sys
try:
    data = json.load(sys.stdin)
    for row in data:
        print(row.get('post_id',''))
except: pass
" 2>/dev/null | head -1)
fi

if [ -z "$FOOTER_ID" ]; then
  echo ""
  echo "Could not auto-detect footer template ID."
  echo "From the list above, identify the footer template and set:"
  echo "  FOOTER_ID=<ID> bash $0"
  exit 1
fi

echo "Using footer template ID: $FOOTER_ID"

# ---- STEP 2: Get the Elementor JSON for the footer ---------
CURRENT_JSON=$($WP post meta get $FOOTER_ID _elementor_data 2>/dev/null)
if [ -z "$CURRENT_JSON" ]; then
  echo "No Elementor data found for post ID $FOOTER_ID"
  exit 1
fi

echo "Footer Elementor data length: ${#CURRENT_JSON} chars"

# ---- STEP 3: Check for the wrong Facebook URL --------------
WRONG_COUNT=$(echo "$CURRENT_JSON" | python3 -c "
import sys, json
data = sys.stdin.read()
# Count occurrences - look for social icon with wrong URL
import re
# Find all URL occurrences near 'facebook' context
count = 0
# Simple string search for the wrong URL in social icons
lines = data.split('linkedin.com')
print(len(lines) - 1)
" 2>/dev/null)
echo "Found $WRONG_COUNT LinkedIn URL occurrence(s) in footer JSON"

# ---- STEP 4: Patch the JSON --------------------------------
# Strategy: find social icons widget entries where:
#   - icon type = "fab fa-facebook" (or similar) AND url = LinkedIn URL
# Replace that specific URL with the Facebook URL

PATCHED_JSON=$(echo "$CURRENT_JSON" | python3 -c "
import sys, json, re

raw = sys.stdin.read()

# Try to parse as JSON
try:
    data = json.loads(raw)
except Exception as e:
    print(f'JSON parse error: {e}', file=sys.stderr)
    sys.exit(1)

WRONG = 'https://www.linkedin.com/company/brndguru/'
CORRECT_FB = 'https://www.facebook.com/brndguru'
LINKEDIN = 'https://www.linkedin.com/company/brndguru/'

fixed_count = 0

def patch_element(el):
    global fixed_count
    settings = el.get('settings', {})

    # Check social_icon_list (social icons widget)
    icon_list = settings.get('social_icon_list', [])
    for icon in icon_list:
        icon_type = str(icon.get('social_icon', {}).get('value', '')).lower()
        icon_url = icon.get('link', {}).get('url', '')

        # If icon looks like Facebook but points to LinkedIn URL → fix it
        if 'facebook' in icon_type and icon_url == WRONG:
            icon['link']['url'] = CORRECT_FB
            fixed_count += 1
            print(f'Fixed: facebook icon had LinkedIn URL → now {CORRECT_FB}', file=sys.stderr)

        # Also check for old-style social_icon field
        old_icon = str(icon.get('social', '')).lower()
        old_url = icon.get('social_url', '')
        if 'facebook' in old_icon and old_url == WRONG:
            icon['social_url'] = CORRECT_FB
            fixed_count += 1
            print(f'Fixed (legacy): facebook icon URL', file=sys.stderr)

    # Recurse into children
    for child in el.get('elements', []):
        patch_element(child)

for section in data:
    patch_element(section)

if fixed_count == 0:
    print('WARNING: No Facebook icon with wrong LinkedIn URL found. Check manually.', file=sys.stderr)
    print('Dumping social icon URLs found:', file=sys.stderr)
    def dump_icons(el):
        settings = el.get('settings', {})
        for icon in settings.get('social_icon_list', []):
            print(f'  icon={icon.get(\"social_icon\",{}).get(\"value\",\"?\")} url={icon.get(\"link\",{}).get(\"url\",\"?\")}', file=sys.stderr)
        for child in el.get('elements', []):
            dump_icons(child)
    for section in data:
        dump_icons(section)
else:
    print(f'Total fixes applied: {fixed_count}', file=sys.stderr)

print(json.dumps(data, ensure_ascii=False))
" 2>/tmp/fix3_patch.log)

cat /tmp/fix3_patch.log

if [ $? -ne 0 ] || [ -z "$PATCHED_JSON" ]; then
  echo "Patch failed. Check JSON manually."
  exit 1
fi

# ---- STEP 5: Update the post meta -------------------------
$WP post meta update $FOOTER_ID _elementor_data "$PATCHED_JSON"
echo ""
echo "Updated _elementor_data for footer template ID: $FOOTER_ID"

# ---- STEP 6: Flush Elementor cache for this post ----------
$WP post meta delete $FOOTER_ID _elementor_css 2>/dev/null || true
$WP elementor flush-css 2>/dev/null || true

echo ""
echo "FIX 3 COMPLETE."
echo "Verify: Footer Facebook icon should now link to $CORRECT_FB"
