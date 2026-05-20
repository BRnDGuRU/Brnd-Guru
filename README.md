# BrndGuru.com — Site Fixes

## Overview

This repo contains fix scripts for 11 issues audited on brndguru.com (WordPress + Elementor Pro).

## Method A — WP-CLI via SSH (fastest)

SSH into the server, then:

```bash
cd /var/www/html   # or wherever WordPress is installed
bash /path/to/fixes/00-run-all-wpcli.sh 2>&1 | tee run-output.txt
```

This covers **Fixes 1, 3, 8, 9, 10, 11** automatically.

Then do **Fixes 2, 4, 5, 6, 7** manually via the Elementor editor — see `fixes/ELEMENTOR-GUIDE.md`.

## Method B — PHP Plugin via WordPress Admin (no SSH needed)

1. Upload `fixes/brndguru-fixes-plugin.php` to `/wp-content/plugins/brndguru-fixes/` via SFTP or File Manager
2. Activate in **WordPress Admin → Plugins**
3. On the plugin row, copy the nonce and click **"RUN FIXES NOW"**
4. Review the output
5. **Deactivate and delete** the plugin immediately after

This covers the same WP-CLI fixes (1, 3, 8, 9, 10, 11).

## Fix Summary

| Fix | Description | Method |
|-----|-------------|--------|
| Fix 1 | Services nav "#" → /services/ | WP-CLI / Plugin |
| Fix 2 | About "Learn More" → #how-we-work | Elementor UI |
| Fix 3 | Footer Facebook icon → correct URL | WP-CLI / Plugin |
| Fix 4 | Header duplicate CTAs → responsive | Elementor UI |
| Fix 5 | Pricing "Learn More" → "Book a Call" | Elementor UI |
| Fix 6 | Client logo broken images | Elementor UI |
| Fix 7 | OG image 1200×628 | SEO Plugin UI |
| Fix 8 | About meta description | WP-CLI / Plugin |
| Fix 9 | Award links — remove UTM params | WP-CLI / Plugin |
| Fix 10 | Active nav state CSS | WP-CLI / Plugin |
| Fix 11 | Remove "Get a Quote" nav item | WP-CLI / Plugin |

## Files

```
fixes/
  00-run-all-wpcli.sh          Master runner (all WP-CLI fixes)
  01-diagnose.sh               Discover IDs before running fixes
  02-fix1-nav-services.sh      Fix 1: Services nav
  03-fix3-footer-facebook.sh   Fix 3: Footer Facebook URL
  04-fix8-about-meta.sh        Fix 8: About meta description
  05-fix9-award-links.sh       Fix 9: UTM strip from award links
  06-fix10-active-nav-css.sh   Fix 10: Active nav CSS
  07-fix11-consolidate-nav.sh  Fix 11: Remove Get a Quote nav
  brndguru-fixes-plugin.php    PHP plugin (alternative to WP-CLI)
  ELEMENTOR-GUIDE.md           Step-by-step Elementor UI fixes
```
