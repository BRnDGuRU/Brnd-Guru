#!/bin/bash
# ============================================================
# FIX 4 — Header: Fix duplicate "Schedule a Call Now" CTAs
# Sets responsive visibility so desktop button hides on mobile
# and mobile button hides on desktop.
# ============================================================

WP="wp --path=/var/www/html --allow-root"

# ---- STEP 1: Find the header template ---------------------
echo "Looking for header template..."
HEADER_ID=$($WP post list --post_type=elementor_library --format=json 2>/dev/null | \
  python3 -c "
import json, sys
items = json.load(sys.stdin)
for item in items:
    t = item.get('post_title','').lower()
    if 'header' in t:
        print(item['ID'])
" 2>/dev/null | head -1)

if [ -z "$HEADER_ID" ]; then
  echo "All Elementor templates:"
  $WP post list --post_type=elementor_library --fields=ID,post_title,post_status
  echo ""
  echo "Set HEADER_ID manually: HEADER_ID=<ID> bash $0"
  exit 1
fi

echo "Using header template ID: $HEADER_ID"

# ---- STEP 2: Get Elementor JSON ---------------------------
CURRENT_JSON=$($WP post meta get $HEADER_ID _elementor_data 2>/dev/null)
if [ -z "$CURRENT_JSON" ]; then
  echo "ERROR: No Elementor data on header ID $HEADER_ID"
  exit 1
fi
echo "JSON length: ${#CURRENT_JSON}"

# ---- STEP 3: Patch JSON for responsive visibility ----------
PATCHED_JSON=$(echo "$CURRENT_JSON" | python3 << 'PYEOF'
import sys, json

raw = sys.stdin.read()
try:
    data = json.loads(raw)
except Exception as e:
    print(f"JSON parse error: {e}", file=sys.stderr)
    sys.exit(1)

CTA_TEXT = "schedule a call"
cta_buttons = []

def collect_buttons(el, path=""):
    widget_type = el.get('widgetType', '')
    settings = el.get('settings', {})

    if widget_type == 'button':
        text = settings.get('text', '')
        if CTA_TEXT in text.lower():
            cta_buttons.append((el, path))
            print(f"Found CTA button: '{text}' at path {path}", file=sys.stderr)

    for i, child in enumerate(el.get('elements', [])):
        collect_buttons(child, f"{path}/{i}")

for i, section in enumerate(data):
    collect_buttons(section, str(i))

print(f"\nTotal '{CTA_TEXT}' buttons found: {len(cta_buttons)}", file=sys.stderr)

if len(cta_buttons) < 2:
    print("WARNING: Found fewer than 2 CTA buttons. Listing ALL buttons:", file=sys.stderr)
    def dump_buttons(el):
        if el.get('widgetType') == 'button':
            text = el.get('settings',{}).get('text','')
            print(f"  '{text}'", file=sys.stderr)
        for child in el.get('elements',[]):
            dump_buttons(child)
    for s in data:
        dump_buttons(s)
    print("No responsive fix applied — check button text matches.", file=sys.stderr)
    print(json.dumps(data, ensure_ascii=False))
    sys.exit(0)

# Elementor responsive hide settings:
# hide_desktop = hide on desktop (>= 1025px)
# hide_tablet  = hide on tablet (768–1024px)
# hide_mobile  = hide on mobile (< 768px)

# First button = desktop CTA → hide on mobile
first_el, _ = cta_buttons[0]
first_el['settings']['hide_mobile'] = 'yes'
first_el['settings']['hide_tablet'] = ''
first_el['settings']['hide_desktop'] = ''
print(f"Button 1 ('{first_el['settings'].get('text','')}') → hide on mobile", file=sys.stderr)

# Second button = mobile CTA → hide on desktop (and tablet)
second_el, _ = cta_buttons[1]
second_el['settings']['hide_desktop'] = 'yes'
second_el['settings']['hide_tablet'] = 'yes'
second_el['settings']['hide_mobile'] = ''
print(f"Button 2 ('{second_el['settings'].get('text','')}') → hide on desktop + tablet", file=sys.stderr)

print(json.dumps(data, ensure_ascii=False))
PYEOF
)

if [ $? -ne 0 ] || [ -z "$PATCHED_JSON" ]; then
  echo "Patch failed."
  exit 1
fi

# ---- STEP 4: Save and flush --------------------------------
$WP post meta update $HEADER_ID _elementor_data "$PATCHED_JSON"
$WP post meta delete $HEADER_ID _elementor_css 2>/dev/null || true
$WP elementor flush-css 2>/dev/null || true

echo ""
echo "FIX 4 COMPLETE."
echo "Verify with browser DevTools at 375px, 768px, and 1280px:"
echo "  → Only ONE 'Schedule a Call Now' button should appear at each width."
echo ""
echo "NOTE: Elementor stores responsive in widget settings, but the Elementor"
echo "editor UI shows it under Advanced → Responsive. If this script's approach"
echo "doesn't work with your Elementor version, set it manually there."
