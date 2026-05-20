#!/bin/bash
# ============================================================
# BRNDGURU.COM — MASTER FIX RUNNER (WP-CLI Fixes Only)
# Covers: Fix 1, 3, 8, 9, 10, 11
# Fixes 2, 4, 5, 6, 7 require Elementor UI — see ELEMENTOR-GUIDE.md
#
# Usage: bash 00-run-all-wpcli.sh 2>&1 | tee run-output.txt
# ============================================================

set -e
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WP="wp --path=/var/www/html --allow-root"

echo "============================================"
echo "BRNDGURU.COM — Starting WP-CLI Fix Sequence"
echo "$(date)"
echo "============================================"
echo ""

# Quick connectivity check
$WP option get siteurl || { echo "ERROR: wp-cli not working. Check path."; exit 1; }
echo ""

echo "--- RUNNING: Diagnostic scan ---"
bash "$SCRIPT_DIR/01-diagnose.sh"
echo ""

echo "--- RUNNING: Fix 1 — Services nav href ---"
bash "$SCRIPT_DIR/02-fix1-nav-services.sh"
echo ""

echo "--- RUNNING: Fix 3 — Footer Facebook URL ---"
bash "$SCRIPT_DIR/03-fix3-footer-facebook.sh"
echo ""

echo "--- RUNNING: Fix 8 — About meta description ---"
bash "$SCRIPT_DIR/04-fix8-about-meta.sh"
echo ""

echo "--- RUNNING: Fix 9 — Award link UTM cleanup ---"
bash "$SCRIPT_DIR/05-fix9-award-links.sh"
echo ""

echo "--- RUNNING: Fix 10 — Active nav CSS ---"
bash "$SCRIPT_DIR/06-fix10-active-nav-css.sh"
echo ""

echo "--- RUNNING: Fix 11 — Remove Get a Quote nav item ---"
bash "$SCRIPT_DIR/07-fix11-consolidate-nav.sh"
echo ""

echo "--- RUNNING: Fix 2 — About 'Learn More' button anchor ---"
bash "$SCRIPT_DIR/08-fix2-about-learn-more.sh"
echo ""

echo "--- RUNNING: Fix 4 — Header duplicate CTA responsive visibility ---"
bash "$SCRIPT_DIR/09-fix4-header-cta-responsive.sh"
echo ""

echo "--- RUNNING: Fix 5 — Pricing 'Learn More' → 'Book a Call' ---"
bash "$SCRIPT_DIR/10-fix5-pricing-buttons.sh"
echo ""

echo "============================================"
echo "Flushing caches..."
echo "============================================"

# WP Rocket
$WP rocket clean --confirm 2>/dev/null && echo "WP Rocket cache flushed" || true

# LiteSpeed Cache
$WP litespeed-purge --all 2>/dev/null && echo "LiteSpeed cache flushed" || true

# W3 Total Cache
$WP w3-total-cache flush 2>/dev/null && echo "W3TC cache flushed" || true

# WP Super Cache
$WP super-cache flush 2>/dev/null && echo "Super Cache flushed" || true

# Elementor CSS regeneration
$WP elementor flush-css 2>/dev/null && echo "Elementor CSS flushed" || true

# Transient cleanup
$WP transient delete --all 2>/dev/null && echo "Transients cleared" || true

# Object cache
$WP cache flush 2>/dev/null && echo "Object cache flushed" || true

echo ""
echo "============================================"
echo "WP-CLI Fixes COMPLETE"
echo "============================================"
echo ""
echo "REMAINING — Do these manually:"
echo "  Fix 6: Client logos → fix or hide broken images (needs real image files)"
echo "  Fix 7: OG image → upload 1200x628 image and set in SEO plugin"
echo ""
echo "See fixes/ELEMENTOR-GUIDE.md for step-by-step instructions on Fix 6 + 7."
