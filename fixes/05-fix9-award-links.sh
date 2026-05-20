#!/bin/bash
# ============================================================
# FIX 9 — About Page: Fix award citation links (remove UTM params)
# Strips ?utm_source=chatgpt.com from all 4 award hrefs in
# the Elementor JSON of the About page.
# ============================================================

WP="wp --path=/var/www/html --allow-root"

# ---- STEP 1: Find About page ID ---------------------------
ABOUT_ID=$($WP post list --post_type=page --name=about --field=ID 2>/dev/null | head -1)
if [ -z "$ABOUT_ID" ]; then
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
  echo "ERROR: Cannot find About page ID."
  $WP post list --post_type=page --fields=ID,post_title,post_name
  exit 1
fi

echo "About page ID: $ABOUT_ID"

# ---- STEP 2: Get Elementor JSON ---------------------------
CURRENT_JSON=$($WP post meta get $ABOUT_ID _elementor_data 2>/dev/null)
if [ -z "$CURRENT_JSON" ]; then
  echo "No Elementor data found for About page ID $ABOUT_ID"
  exit 1
fi
echo "JSON length: ${#CURRENT_JSON}"

# ---- STEP 3: Strip UTM params and verify award URLs -------
PATCHED_JSON=$(echo "$CURRENT_JSON" | python3 - << 'PYEOF'
import sys, json, urllib.parse, urllib.request

raw = sys.stdin.read()

try:
    data = json.loads(raw)
except Exception as e:
    print(f"JSON parse error: {e}", file=sys.stderr)
    sys.exit(1)

# Award URLs that should be clean (no UTM)
CLEAN_AWARD_URLS = {
    "e4mevents.com/idma": "https://e4mevents.com/idma-2025/",
    "brandempower.org": "https://www.brandempower.org/digital-marketing-quality-awards.htm",
    "campaignindiacrest.com": "https://www.campaignindiacrest.com/",
    "globaldesignawards.org": "https://globaldesignawards.org/",
}

fixed_links = []
removed_links = []

def strip_utm(url):
    """Remove UTM params from a URL."""
    if not url or 'utm_' not in url:
        return url, False
    parsed = urllib.parse.urlparse(url)
    qs = urllib.parse.parse_qs(parsed.query, keep_blank_values=True)
    clean_qs = {k: v for k, v in qs.items() if not k.startswith('utm_')}
    clean_parsed = parsed._replace(query=urllib.parse.urlencode(clean_qs, doseq=True))
    return urllib.parse.urlunparse(clean_parsed), True

def patch_element(el):
    settings = el.get('settings', {})

    # Check all URL fields in settings
    for key in list(settings.keys()):
        val = settings[key]

        # Handle link objects: {"url": "...", ...}
        if isinstance(val, dict) and 'url' in val:
            url = val['url']
            clean_url, changed = strip_utm(url)
            if changed:
                settings[key]['url'] = clean_url
                fixed_links.append(f"{key}: {url} → {clean_url}")

        # Handle plain string URLs
        elif isinstance(val, str) and ('utm_' in val):
            clean_url, changed = strip_utm(val)
            if changed:
                settings[key] = clean_url
                fixed_links.append(f"{key}: {val} → {clean_url}")

        # Handle lists (e.g., icon lists, text editor content)
        elif isinstance(val, list):
            for item in val:
                if isinstance(item, dict):
                    patch_element({'settings': item, 'elements': []})

    # Recurse into child elements
    for child in el.get('elements', []):
        patch_element(child)

for section in data:
    patch_element(section)

if fixed_links:
    print(f"\nFixed {len(fixed_links)} UTM-tagged URL(s):", file=sys.stderr)
    for f in fixed_links:
        print(f"  {f}", file=sys.stderr)
else:
    print("WARNING: No UTM-tagged URLs found in Elementor JSON.", file=sys.stderr)
    print("The award links may be in a text editor widget as raw HTML.", file=sys.stderr)
    print("Check manually in Elementor editor for links containing '?utm_source=chatgpt.com'", file=sys.stderr)

print(json.dumps(data, ensure_ascii=False))
PYEOF
)

PATCH_EXIT=$?
if [ $PATCH_EXIT -ne 0 ] || [ -z "$PATCHED_JSON" ]; then
  echo "Patch failed."
  exit 1
fi

# ---- STEP 4: Update the post meta -------------------------
$WP post meta update $ABOUT_ID _elementor_data "$PATCHED_JSON"
echo "Updated Elementor data for About page ID: $ABOUT_ID"

# ---- STEP 5: Clear Elementor CSS cache --------------------
$WP post meta delete $ABOUT_ID _elementor_css 2>/dev/null || true
$WP elementor flush-css 2>/dev/null || true

echo ""
echo "FIX 9 COMPLETE."
echo ""
echo "NOTE: If award links are inside a Text Editor widget (raw HTML), the"
echo "UTM params may be embedded in HTML <a> tags and not caught by this script."
echo "In that case, edit the About page in Elementor and manually remove ?utm_source=chatgpt.com"
echo "from each of these 4 links:"
echo "  https://e4mevents.com/idma-2025/"
echo "  https://www.brandempower.org/digital-marketing-quality-awards.htm"
echo "  https://www.campaignindiacrest.com/"
echo "  https://globaldesignawards.org/"
