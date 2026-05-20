#!/bin/bash
# ============================================================
# FIX 5 — Homepage: Rename pricing "Learn More" → "Book a Call"
# Targets the 3 pricing tier cards (Launch, Scale, Dominate).
# Keeps existing href unchanged; adds target="_blank" rel="noopener".
# ============================================================

WP="wp --path=/var/www/html --allow-root"

BOOKING_URL="brndgurumedia.com/widget/bookings/brndguru"

# ---- STEP 1: Find the homepage post -----------------------
echo "Finding homepage..."
HOME_URL=$($WP option get home 2>/dev/null)
echo "Site home: $HOME_URL"

# Try front page
HOME_ID=$($WP option get page_on_front 2>/dev/null)
if [ -z "$HOME_ID" ] || [ "$HOME_ID" = "0" ]; then
  echo "No static front page set — trying post with name 'home'..."
  HOME_ID=$($WP post list --post_type=page --name=home --field=ID 2>/dev/null | head -1)
fi
if [ -z "$HOME_ID" ]; then
  HOME_ID=$($WP post list --post_type=page --fields=ID,post_title --format=json 2>/dev/null | \
    python3 -c "
import json,sys
items=json.load(sys.stdin)
for i in items:
    t=i.get('post_title','').lower()
    if t in ('home','homepage','front page','main'): print(i['ID'])
" | head -1)
fi

if [ -z "$HOME_ID" ]; then
  echo "Could not auto-detect homepage. All pages:"
  $WP post list --post_type=page --fields=ID,post_title,post_name
  echo ""
  echo "Set HOME_ID manually: HOME_ID=<ID> bash $0"
  exit 1
fi

echo "Homepage post ID: $HOME_ID"

# ---- STEP 2: Get Elementor JSON ---------------------------
CURRENT_JSON=$($WP post meta get $HOME_ID _elementor_data 2>/dev/null)
if [ -z "$CURRENT_JSON" ]; then
  echo "ERROR: No Elementor data on homepage ID $HOME_ID"
  exit 1
fi
echo "JSON length: ${#CURRENT_JSON}"

# ---- STEP 3: Patch pricing buttons ------------------------
PATCHED_JSON=$(echo "$CURRENT_JSON" | python3 << 'PYEOF'
import sys, json

raw = sys.stdin.read()
try:
    data = json.loads(raw)
except Exception as e:
    print(f"JSON parse error: {e}", file=sys.stderr)
    sys.exit(1)

BOOKING_URL = "brndgurumedia.com/widget/bookings/brndguru"
PRICING_TIERS = ['launch', 'scale', 'dominate']
fixed = 0
all_buttons = []

def patch_element(el):
    global fixed
    widget_type = el.get('widgetType', '')
    settings = el.get('settings', {})

    if widget_type == 'button':
        text = settings.get('text', '')
        link = settings.get('link', {})
        url = link.get('url', '') if isinstance(link, dict) else ''
        all_buttons.append({'text': text, 'url': url})

        # Match: text is "Learn More" and URL contains booking URL
        if 'learn more' in text.lower() and BOOKING_URL in url:
            settings['text'] = 'Book a Call'
            # Add target and rel
            if isinstance(settings.get('link'), dict):
                settings['link']['is_external'] = 'on'
                settings['link']['nofollow'] = ''
                # noopener is set via is_external in Elementor
            fixed += 1
            print(f"Fixed: '{text}' → 'Book a Call'  (url: {url})", file=sys.stderr)

    for child in el.get('elements', []):
        patch_element(child)

for section in data:
    patch_element(section)

if fixed == 0:
    print(f"WARNING: No pricing 'Learn More' buttons found with booking URL.", file=sys.stderr)
    print(f"All buttons found:", file=sys.stderr)
    for b in all_buttons:
        print(f"  text='{b['text']}'  url='{b['url']}'", file=sys.stderr)

    # Fallback: fix ALL "Learn More" buttons (broader match)
    print("\nTrying broader match: ALL 'Learn More' buttons...", file=sys.stderr)

    def patch_all_learn_more(el):
        global fixed
        settings = el.get('settings', {})
        if el.get('widgetType') == 'button':
            text = settings.get('text', '')
            if 'learn more' in text.lower():
                settings['text'] = 'Book a Call'
                if isinstance(settings.get('link'), dict):
                    settings['link']['is_external'] = 'on'
                fixed += 1
                print(f"  Fixed (broad): '{text}' → 'Book a Call'", file=sys.stderr)
        for child in el.get('elements', []):
            patch_all_learn_more(child)

    # Only apply broad fix if user confirms — for safety, just warn
    print("ACTION NEEDED: Run with BROAD_FIX=1 to rename ALL 'Learn More' buttons:", file=sys.stderr)
    print("  BROAD_FIX=1 bash 10-fix5-pricing-buttons.sh", file=sys.stderr)
else:
    print(f"\nTotal fixed: {fixed} buttons", file=sys.stderr)

print(json.dumps(data, ensure_ascii=False))
PYEOF
)

PATCH_EXIT=$?

# Handle broad fix mode
if [ "${BROAD_FIX}" = "1" ]; then
  echo "BROAD_FIX mode: renaming ALL 'Learn More' buttons on homepage..."
  PATCHED_JSON=$(echo "$CURRENT_JSON" | python3 -c "
import sys, json
raw = sys.stdin.read()
data = json.loads(raw)
fixed = 0
def fix(el):
    global fixed
    s = el.get('settings', {})
    if el.get('widgetType') == 'button' and 'learn more' in s.get('text','').lower():
        s['text'] = 'Book a Call'
        if isinstance(s.get('link'), dict): s['link']['is_external'] = 'on'
        fixed += 1
    for c in el.get('elements',[]): fix(c)
for s in data: fix(s)
import sys; print(f'Fixed {fixed} buttons', file=sys.stderr)
print(json.dumps(data, ensure_ascii=False))
")
fi

if [ $PATCH_EXIT -ne 0 ] || [ -z "$PATCHED_JSON" ]; then
  echo "Patch failed."
  exit 1
fi

# ---- STEP 4: Save and flush --------------------------------
$WP post meta update $HOME_ID _elementor_data "$PATCHED_JSON"
$WP post meta delete $HOME_ID _elementor_css 2>/dev/null || true
$WP elementor flush-css 2>/dev/null || true

echo ""
echo "FIX 5 COMPLETE."
echo "Verify: Homepage pricing cards should show 'Book a Call' (not 'Learn More')."
echo "If no buttons were fixed, try: BROAD_FIX=1 bash $0"
