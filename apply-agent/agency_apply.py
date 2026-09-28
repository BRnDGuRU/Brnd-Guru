"""
Agency CV Submission Automation
Reads agency-list.csv, visits each pending agency's CV submission page,
fills the form, uploads the correct CV, submits, and logs results.

Run: python apply-agent/agency_apply.py
"""

import asyncio
import csv
import json
import os
import sys
import traceback
from datetime import date
from pathlib import Path

from dotenv import load_dotenv
from playwright.async_api import async_playwright, TimeoutError as PlaywrightTimeout

load_dotenv(Path(__file__).parent / ".env")

# ── Paths ──────────────────────────────────────────────────────────────────────
BASE_DIR        = Path(__file__).parent
REPO_DIR        = BASE_DIR.parent
AGENCY_CSV      = BASE_DIR / "agency-list.csv"
TRACKER_CSV     = BASE_DIR / "recruitment-tracker.csv"
PROFILE_JSON    = BASE_DIR / "profile.json"
SESSION_DIR     = BASE_DIR / "playwright-session"
CV_DIR          = REPO_DIR / "cv"

# ── Config ─────────────────────────────────────────────────────────────────────
GMAIL_EMAIL    = os.getenv("GMAIL_EMAIL", "shivanhsuabroadjobs@gmail.com")
GMAIL_PASSWORD = os.getenv("GMAIL_PASSWORD", "")
FORM_TIMEOUT   = 15_000   # ms per action
PAGE_TIMEOUT   = 30_000   # ms page load

# ── Load profile ───────────────────────────────────────────────────────────────
with open(PROFILE_JSON) as f:
    PROFILE = json.load(f)


def cv_path(agency_type: str) -> Path:
    """Return the correct CV file path for a given agency type."""
    cv_map = PROFILE["cv_files"]
    rel = cv_map.get(agency_type.lower(), cv_map["general"])
    full = REPO_DIR / rel
    if not full.exists():
        # fallback: look for any .docx in cv/
        fallbacks = list(CV_DIR.glob("*.docx"))
        if fallbacks:
            print(f"  ⚠  CV not found at {full}, using {fallbacks[0].name}")
            return fallbacks[0]
        raise FileNotFoundError(f"No CV file found. Expected: {full}")
    return full


def read_agencies() -> list[dict]:
    if not AGENCY_CSV.exists():
        print(f"ERROR: {AGENCY_CSV} not found.")
        sys.exit(1)
    with open(AGENCY_CSV, newline="", encoding="utf-8") as f:
        return list(csv.DictReader(f))


def write_agencies(rows: list[dict]):
    fieldnames = ["company", "form_url", "agency_type", "status", "date_applied", "notes"]
    with open(AGENCY_CSV, "w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=fieldnames)
        w.writeheader()
        w.writerows(rows)


def append_tracker(company: str, url: str, status: str, cv_used: str, notes: str = ""):
    header = not TRACKER_CSV.exists()
    with open(TRACKER_CSV, "a", newline="", encoding="utf-8") as f:
        w = csv.writer(f)
        if header:
            w.writerow(["date", "company", "url", "status", "cv_used", "notes"])
        w.writerow([date.today().isoformat(), company, url, status, cv_used, notes])


# ── Form field helpers ─────────────────────────────────────────────────────────
FIELD_MAP = {
    # name fields
    "first.name":   PROFILE["personal"]["first_name"],
    "firstname":    PROFILE["personal"]["first_name"],
    "first_name":   PROFILE["personal"]["first_name"],
    "last.name":    PROFILE["personal"]["last_name"],
    "lastname":     PROFILE["personal"]["last_name"],
    "last_name":    PROFILE["personal"]["last_name"],
    "full.name":    PROFILE["personal"]["full_name"],
    "fullname":     PROFILE["personal"]["full_name"],
    "full_name":    PROFILE["personal"]["full_name"],
    "name":         PROFILE["personal"]["full_name"],
    "your.name":    PROFILE["personal"]["full_name"],
    "candidate":    PROFILE["personal"]["full_name"],
    # contact
    "email":        PROFILE["personal"]["email"],
    "e-mail":       PROFILE["personal"]["email"],
    "mail":         PROFILE["personal"]["email"],
    "phone":        PROFILE["personal"]["phone"],
    "mobile":       PROFILE["personal"]["phone"],
    "contact":      PROFILE["personal"]["phone"],
    "telephone":    PROFILE["personal"]["phone"],
    # location
    "city":         PROFILE["personal"]["location_city"],
    "location":     PROFILE["personal"]["current_location"],
    "country":      PROFILE["personal"]["location_country"],
    "state":        PROFILE["personal"]["location_state"],
    # professional
    "title":        PROFILE["professional"]["current_title"],
    "job.title":    PROFILE["professional"]["current_title"],
    "current.role": PROFILE["professional"]["current_title"],
    "position":     PROFILE["professional"]["current_title"],
    "linkedin":     PROFILE["social"]["linkedin_url"],
    "profile":      PROFILE["social"]["linkedin_url"],
    "experience":   str(PROFILE["professional"]["years_experience"]),
    "years":        str(PROFILE["professional"]["years_experience"]),
    "notice":       PROFILE["professional"]["notice_period"],
    "availability": PROFILE["professional"]["availability"],
    "salary":       PROFILE["professional"]["salary_expected_range"],
    "expected":     PROFILE["professional"]["salary_expected_range"],
    "message":      PROFILE["cover_letter_intro"],
    "cover":        PROFILE["cover_letter_intro"],
    "about":        PROFILE["cover_letter_intro"],
    "introduction": PROFILE["cover_letter_intro"],
    "comments":     PROFILE["cover_letter_intro"],
    "how.did":      "Online research",
    "source":       "Online research",
    "referral":     "Online research",
}


def match_field(name_attr: str) -> str | None:
    """Match an input name/id/placeholder to a profile value."""
    key = name_attr.lower().replace("-", ".").replace("_", ".").strip()
    for pattern, value in FIELD_MAP.items():
        if pattern in key or key in pattern:
            return value
    return None


async def fill_inputs(page, exclude_types=("submit", "button", "hidden", "file", "checkbox", "radio")):
    """Try to fill all visible text inputs on the page using FIELD_MAP."""
    inputs = await page.query_selector_all("input, textarea, select")
    filled = 0
    for el in inputs:
        try:
            itype = (await el.get_attribute("type") or "text").lower()
            if itype in exclude_types:
                continue
            if not await el.is_visible():
                continue

            # gather identifying attributes
            attrs = []
            for attr in ("name", "id", "placeholder", "aria-label", "data-field"):
                val = await el.get_attribute(attr)
                if val:
                    attrs.append(val)

            value = None
            for attr in attrs:
                value = match_field(attr)
                if value:
                    break

            if value:
                tag = await el.evaluate("el => el.tagName.toLowerCase()")
                if tag == "select":
                    # try to select option by visible text match
                    options = await el.query_selector_all("option")
                    for opt in options:
                        txt = (await opt.inner_text()).strip().lower()
                        if any(v.lower() in txt for v in [
                            PROFILE["personal"]["location_country"].lower(),
                            "india", "full-time", "full time", "permanent"
                        ]):
                            opt_val = await opt.get_attribute("value")
                            if opt_val:
                                await el.select_option(value=opt_val)
                                filled += 1
                                break
                else:
                    await el.fill(value)
                    filled += 1
        except Exception:
            continue
    return filled


async def upload_cv(page, cv_file: Path) -> bool:
    """Attempt to upload CV via file input. Returns True if successful."""
    file_inputs = await page.query_selector_all("input[type='file']")
    for fi in file_inputs:
        try:
            await fi.set_input_files(str(cv_file))
            print(f"  ✓ CV uploaded: {cv_file.name}")
            return True
        except Exception:
            continue

    # Fallback: look for upload button by text
    for selector in [
        "text=Upload CV", "text=Upload Resume", "text=Attach CV",
        "text=Browse", "text=Choose File", "[data-upload]", ".upload-btn"
    ]:
        try:
            btn = page.locator(selector).first
            if await btn.is_visible(timeout=2000):
                async with page.expect_file_chooser() as fc_info:
                    await btn.click()
                file_chooser = await fc_info.value
                await file_chooser.set_files(str(cv_file))
                print(f"  ✓ CV uploaded via file chooser: {cv_file.name}")
                return True
        except Exception:
            continue

    print("  ⚠  No file input found — CV not uploaded")
    return False


async def submit_form(page) -> bool:
    """Click the submit/send button. Returns True if a navigation or success indicator appears."""
    submit_selectors = [
        "input[type='submit']",
        "button[type='submit']",
        "button:has-text('Submit')",
        "button:has-text('Send')",
        "button:has-text('Apply')",
        "button:has-text('Register')",
        "button:has-text('Upload')",
        "button:has-text('Send CV')",
        "button:has-text('Send Resume')",
        "button:has-text('Submit CV')",
        "button:has-text('Submit Resume')",
    ]
    for sel in submit_selectors:
        try:
            btn = page.locator(sel).first
            if await btn.is_visible(timeout=2000):
                await btn.click()
                try:
                    await page.wait_for_load_state("networkidle", timeout=10_000)
                except PlaywrightTimeout:
                    pass
                return True
        except Exception:
            continue
    return False


async def detect_success(page) -> bool:
    """Heuristic: check page for success/thank-you indicators."""
    content = (await page.content()).lower()
    success_phrases = [
        "thank you", "thanks for", "successfully", "received your",
        "we will be in touch", "we'll be in touch", "application submitted",
        "cv received", "resume received", "submission successful",
        "message sent", "form submitted"
    ]
    return any(p in content for p in success_phrases)


async def handle_google_login(page):
    """Handle Google OAuth login if triggered."""
    try:
        google_btn = page.locator("text=Sign in with Google, a[href*='accounts.google.com']").first
        if await google_btn.is_visible(timeout=3000):
            print("  → Google login detected")
            await google_btn.click()
            await page.wait_for_selector("input[type='email']", timeout=10_000)
            await page.fill("input[type='email']", GMAIL_EMAIL)
            await page.click("#identifierNext, button:has-text('Next')")
            await page.wait_for_selector("input[type='password']", timeout=10_000)
            await page.fill("input[type='password']", GMAIL_PASSWORD)
            await page.click("#passwordNext, button:has-text('Next')")
            # Handle 2FA if it appears
            try:
                await page.wait_for_selector(
                    "input[type='tel'], input[aria-label*='code'], input[id*='code']",
                    timeout=8_000
                )
                otp = input("  🔐 2FA required — enter your Google OTP code: ").strip()
                await page.fill("input[type='tel'], input[aria-label*='code']", otp)
                await page.click("button:has-text('Next')")
            except PlaywrightTimeout:
                pass  # No 2FA
            await page.wait_for_load_state("networkidle", timeout=15_000)
            print("  ✓ Google login complete")
    except Exception:
        pass  # Not a Google login gate


# ── Main per-agency handler ────────────────────────────────────────────────────
async def process_agency(browser, row: dict) -> tuple[str, str]:
    """
    Visit one agency's form URL, fill + submit.
    Returns (status, notes) — status is 'Applied', 'needs_manual', or 'url_dead'.
    """
    company     = row["company"]
    url         = row["form_url"]
    agency_type = row.get("agency_type", "general")
    cv_file     = cv_path(agency_type)

    print(f"\n{'─'*60}")
    print(f"  Agency : {company}")
    print(f"  URL    : {url}")
    print(f"  CV     : {cv_file.name}")

    context = await browser.new_context()
    page    = await context.new_page()

    try:
        resp = await page.goto(url, timeout=PAGE_TIMEOUT, wait_until="domcontentloaded")
        if resp and resp.status >= 400:
            print(f"  ✗ HTTP {resp.status} — URL dead")
            return "url_dead", f"HTTP {resp.status}"

        await page.wait_for_load_state("networkidle", timeout=PAGE_TIMEOUT)

        # Handle Google login if needed
        await handle_google_login(page)

        # Fill form fields
        filled = await fill_inputs(page)
        print(f"  ✓ Filled {filled} field(s)")

        # Upload CV
        uploaded = await upload_cv(page, cv_file)

        # Submit
        submitted = await submit_form(page)
        if not submitted:
            print("  ⚠  No submit button found")
            return "needs_manual", "No submit button found"

        # Detect success
        success = await detect_success(page)
        if success:
            print(f"  ✓ Submitted successfully")
            return "Applied", ""
        else:
            print(f"  ⚠  Submitted but no success confirmation — marking needs_manual")
            return "needs_manual", "No success confirmation detected"

    except PlaywrightTimeout:
        print(f"  ✗ Page timed out")
        return "needs_manual", "Timeout"
    except Exception as e:
        print(f"  ✗ Error: {e}")
        return "needs_manual", str(e)[:120]
    finally:
        await context.close()


# ── Entry point ────────────────────────────────────────────────────────────────
async def main():
    rows = read_agencies()
    pending = [r for r in rows if r.get("status", "").strip().lower() not in ("applied", "url_dead")]

    if not pending:
        print("✓ All agencies already processed. Nothing to do.")
        return

    print(f"Starting agency CV submission — {len(pending)} pending out of {len(rows)} total")
    print(f"Session dir: {SESSION_DIR}")
    SESSION_DIR.mkdir(exist_ok=True)

    async with async_playwright() as p:
        browser = await p.chromium.launch_persistent_context(
            str(SESSION_DIR),
            headless=False,   # set True after confirming it works
            viewport={"width": 1280, "height": 900},
            args=["--disable-blink-features=AutomationControlled"],
        )

        for row in rows:
            if row.get("status", "").strip().lower() in ("applied", "url_dead"):
                continue

            status, notes = await process_agency(browser, row)
            row["status"]       = status
            row["date_applied"] = date.today().isoformat() if status == "Applied" else ""
            row["notes"]        = notes

            # Persist after every row — safe to restart
            write_agencies(rows)
            append_tracker(
                company  = row["company"],
                url      = row["form_url"],
                status   = status,
                cv_used  = cv_path(row.get("agency_type", "general")).name,
                notes    = notes,
            )

        await browser.close()

    applied      = sum(1 for r in rows if r["status"] == "Applied")
    needs_manual = sum(1 for r in rows if r["status"] == "needs_manual")
    url_dead     = sum(1 for r in rows if r["status"] == "url_dead")

    print(f"\n{'='*60}")
    print(f"  Done.")
    print(f"  Applied      : {applied}")
    print(f"  Needs manual : {needs_manual}")
    print(f"  URL dead     : {url_dead}")
    print(f"  Log          : {TRACKER_CSV}")
    print(f"{'='*60}")


if __name__ == "__main__":
    asyncio.run(main())
