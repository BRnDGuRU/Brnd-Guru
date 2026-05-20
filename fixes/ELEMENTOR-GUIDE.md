# Elementor UI Fixes — Step-by-Step Guide
## brndguru.com | Fixes 2, 4, 5, 6, 7

These fixes require the Elementor editor (or SEO plugin UI) because they involve
visual widget settings or responsive visibility toggles that WP-CLI cannot set safely.

---

## FIX 2 — About Page: Fix "Learn More" button → anchor link

**Goal:** The "Learn More" button links to "#" — link it to the "How We Work" section instead.

### Steps:
1. Go to **WordPress Admin → Pages → About** → click **Edit with Elementor**
2. Find the **"Learn More" button** widget (usually in the hero or intro section)
3. Click the button widget → **Content tab** → **Link** field
4. Change `#` to `#how-we-work`
5. Now find the **"How We Work"** section on the same page
6. Click the **section** (the outermost container) → **Advanced tab**
7. Under **CSS ID**, type: `how-we-work` (no `#`)
8. Click **Update** (green button, top-left)

---

## FIX 4 — Header: Fix duplicate "Schedule a Call Now" CTA buttons

**Goal:** Two identical CTA buttons render simultaneously on some viewports. Set one to desktop-only, one to mobile-only.

### Steps:
1. Go to **WordPress Admin → Templates → Theme Builder → Header**
   - Or: **Elementor → Theme Builder → Header**
2. Open the header template in Elementor editor
3. Find the **first** "Schedule a Call Now" button (the desktop version — usually larger/right-aligned)
4. Click the button widget → **Advanced tab → Responsive**
5. Under **Hide On**, check: **Mobile** (and **Tablet** if needed)
6. Find the **second** "Schedule a Call Now" button (the mobile version — usually in a hamburger menu area)
7. Click it → **Advanced tab → Responsive**
8. Under **Hide On**, check: **Desktop** (and **Tablet** if it duplicates there too)
9. Click **Update**

**Verify:** Use browser DevTools to resize to 375px, 768px, and 1280px — only ONE button should appear at each width.

---

## FIX 5 — Homepage: Rename pricing "Learn More" → "Book a Call"

**Goal:** The 3 pricing tier cards (Launch, Scale, Dominate) have "Learn More" buttons — rename to "Book a Call".

### Steps:
1. Go to **WordPress Admin → Pages → Home** → click **Edit with Elementor**
2. Scroll to the **pricing section** (3 cards)
3. Click the **"Learn More" button** on the **first card (Launch)**
4. In the **Content tab** → **Text** field, change `Learn More` to `Book a Call`
5. While in the **Content tab** → **Link** field:
   - Verify URL is: `https://brndgurumedia.com/widget/bookings/brndguru`
   - Click the gear icon next to the link → check **Open in new tab** and add `rel="noopener"`
6. Repeat steps 3–5 for the **Scale** card button
7. Repeat steps 3–5 for the **Dominate** card button
8. Click **Update**

---

## FIX 6 — Homepage: Fix or hide broken client logo images

**Goal:** Client logo carousel has empty/broken image src.

### Option A — Replace with real logos (preferred):
1. Upload logo files to **WordPress Admin → Media → Add New**
2. Go to **Pages → Home → Edit with Elementor**
3. Scroll to **"Our Amazing Clients"** section
4. Click each broken image widget → **Content tab** → **Choose Image**
5. Select the correct logo from Media Library
6. Click **Update**

### Option B — Hide the section (if no logos available):
1. Go to **Pages → Home → Edit with Elementor**
2. Click the **"Our Amazing Clients" section** container (outermost)
3. **Advanced tab → Custom CSS** → add:
   ```css
   display: none;
   ```
4. Or use the **Responsive** settings to hide on all devices
5. Click **Update**

### Option C — CSS fallback (fastest, non-Elementor):
Go to **WordPress Admin → Appearance → Customize → Additional CSS** and add:
```css
/* Hide broken client logo images */
.clients-section img[src=""],
.clients-section img:not([src]),
.clients-logos img[src=""],
.clients-logos img:not([src]) {
  display: none;
}
```

---

## FIX 7 — Homepage + About: Fix OG image dimensions

**Goal:** Current OG image is 769×210px. Needs 1200×628px.

### Create the new OG image:
- Dimensions: **1200 × 628 pixels**
- Content: Brnd Guru logo + tagline on brand background
- Tools: Canva, Figma, or Photoshop
- Export as: JPG or PNG, under 200KB

### Set on Homepage (Yoast SEO):
1. Go to **Pages → Home → Edit**
2. Scroll down to **Yoast SEO** (or RankMath) meta box
3. Click **Social** tab
4. Under **Facebook image**, click **Select image**
5. Upload or select your new 1200×628 image
6. Click **Update**

### Set on About Page:
1. Go to **Pages → About → Edit**
2. Same as above — Yoast SEO → Social → Facebook image
3. Upload the landscape 1200×628 version (different from the portrait currently used)
4. Click **Update**

### Set global default OG image (Yoast):
1. Go to **SEO → Social → Facebook tab**
2. Under **Default image**, set your 1200×628 branded image
3. Save changes

---

## Final Checklist
After completing all Elementor fixes:

| Fix | Done? | Notes |
|-----|-------|-------|
| Fix 2 | ☐ | About "Learn More" → #how-we-work |
| Fix 4 | ☐ | Header CTA responsive visibility |
| Fix 5 | ☐ | Pricing buttons → "Book a Call" |
| Fix 6 | ☐ | Client logos fixed or hidden |
| Fix 7 | ☐ | OG image 1200×628 on Home + About |

**After all edits:** Go to **WP Rocket → Dashboard → Clear All Cache** (or your cache plugin).
