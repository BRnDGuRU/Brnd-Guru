#!/bin/bash
# ============================================================
# FIX 2 — About Page: Fix "Learn More" button linking to "#"
# Changes href="#" → "#how-we-work" on the About page button.
# Also adds CSS ID "how-we-work" to the first section after the hero.
# ============================================================

WP="wp --path=/var/www/html --allow-root"

# ---- STEP 1: Find About page ID ---------------------------
ABOUT_ID=$($WP post list --post_type=page --name=about --field=ID 2>/dev/null | head -1)
if [ -z "$ABOUT_ID" ]; then
  ABOUT_ID=$($WP post list --post_type=page --fields=ID,post_title --format=json 2>/dev/null | \
    python3 -c "
import json,sys
items=json.load(sys.stdin)
for i in items:
    if 'about' in i.get('post_title','').lower(): print(i['ID'])
" | head -1)
fi
if [ -z "$ABOUT_ID" ]; then
  echo "ERROR: Cannot find About page."
  $WP post list --post_type=page --fields=ID,post_title,post_name
  exit 1
fi
echo "About page ID: $ABOUT_ID"

# ---- STEP 2: Get Elementor JSON ---------------------------
CURRENT_JSON=$($WP post meta get $ABOUT_ID _elementor_data 2>/dev/null)
if [ -z "$CURRENT_JSON" ]; then
  echo "ERROR: No Elementor data on About page ID $ABOUT_ID"
  exit 1
fi
echo "JSON length: ${#CURRENT_JSON}"

# ---- STEP 3: Patch JSON -----------------------------------
PATCHED_JSON=$(echo "$CURRENT_JSON" | python3 << 'PYEOF'
import sys, json

raw = sys.stdin.read()
try:
    data = json.loads(raw)
except Exception as e:
    print(f"JSON parse error: {e}", file=sys.stderr)
    sys.exit(1)

fixed_buttons = 0
section_id_added = False
section_index = 0

def patch_element(el, depth=0):
    global fixed_buttons, section_id_added, section_index
    settings = el.get('settings', {})
    el_type = el.get('elType', '')
    widget_type = el.get('widgetType', '')

    # Fix button widget with url="#" and text containing "Learn More"
    if widget_type == 'button':
        text = settings.get('text', '')
        link = settings.get('link', {})
        url = link.get('url', '') if isinstance(link, dict) else ''

        if 'learn more' in text.lower() and url == '#':
            settings['link']['url'] = '#how-we-work'
            fixed_buttons += 1
            print(f"Fixed button: '{text}' href='#' → '#how-we-work'", file=sys.stderr)

    # Add CSS ID to the second section (first after hero) — where "How We Work" likely lives
    if el_type == 'section' and depth == 0:
        section_index += 1
        # Add id to section 2 (index 1) — adjust if wrong section
        if section_index == 2 and not section_id_added:
            current_id = settings.get('_element_id', '')
            if not current_id:
                settings['_element_id'] = 'how-we-work'
                section_id_added = True
                print(f"Added CSS ID 'how-we-work' to section {section_index}", file=sys.stderr)

    for child in el.get('elements', []):
        patch_element(child, depth + 1)

for section in data:
    patch_element(section)

if fixed_buttons == 0:
    print("WARNING: No 'Learn More' button with href='#' found.", file=sys.stderr)
    print("Dumping all buttons found:", file=sys.stderr)
    def dump_buttons(el):
        settings = el.get('settings', {})
        if el.get('widgetType') == 'button':
            link = settings.get('link', {})
            url = link.get('url','') if isinstance(link,dict) else ''
            print(f"  text='{settings.get('text','')}' url='{url}'", file=sys.stderr)
        for child in el.get('elements', []):
            dump_buttons(child)
    for s in data:
        dump_buttons(s)

if not section_id_added:
    print("NOTE: CSS ID 'how-we-work' not set (section 2 not found or already has an ID).", file=sys.stderr)
    print("Set it manually: Elementor → section → Advanced → CSS ID → 'how-we-work'", file=sys.stderr)

print(json.dumps(data, ensure_ascii=False))
PYEOF
)

if [ $? -ne 0 ] || [ -z "$PATCHED_JSON" ]; then
  echo "Patch failed."
  exit 1
fi

# ---- STEP 4: Save and flush cache -------------------------
$WP post meta update $ABOUT_ID _elementor_data "$PATCHED_JSON"
$WP post meta delete $ABOUT_ID _elementor_css 2>/dev/null || true
$WP elementor flush-css 2>/dev/null || true
echo ""
echo "FIX 2 COMPLETE."
echo "Verify: About page 'Learn More' button should scroll to the 'How We Work' section."
echo "If the wrong section got the ID, edit manually: Elementor → section → Advanced → CSS ID → 'how-we-work'"
