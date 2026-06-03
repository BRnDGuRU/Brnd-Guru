#!/usr/bin/env python3
"""
Provision Randy's GHL sub-account for Agent 1: create the 9 custom values and 5 tags via the
GoHighLevel (LeadConnector) v2 API. Idempotent — safe to re-run; it skips anything that exists.

Pipelines are NOT created here: the GHL v2 API does not expose a stable pipeline-create endpoint,
so the 8-stage pipeline must be built in the UI (see RUNBOOK §3). This script prints that checklist.

Usage:
    # put GHL_API_KEY and GHL_LOCATION_ID in credentials.env (gitignored), then:
    python3 scripts/provision_ghl.py            # creates missing custom values + tags
    python3 scripts/provision_ghl.py --dry-run  # show what it WOULD do, no API calls

Requires only the Python standard library (urllib). No pip installs.
"""
import argparse
import json
import os
import sys
import urllib.request
import urllib.error

API_BASE = "https://services.leadconnectorhq.com"
API_VERSION = "2021-07-28"

CUSTOM_VALUES = [
    ("webinar_topic", "Get On Contract Vehicles Without Extensive Past Performances"),
    ("webinar_date", "Tuesday, June 17, 2026"),
    ("webinar_time", "7:00 PM EST"),
    ("zoom_webinar_id", "000 0000 0000"),
    ("zoom_link", "https://zoom.us/j/000000000"),
    ("lead_magnet_link", "https://fedgovstartup.com/guide.pdf"),
    ("replay_link", "https://fedgovstartup.com/replay"),
    ("calendly_link", "https://calendly.com/randy-wimmer/30-minute-zoom-mtg-with-randy"),
    ("bootcamp_link", "https://fedgovstartup.com/bootcamp"),
]

TAGS = ["webinar-registered", "webinar-attended", "webinar-noshow", "call-booked", "unsubscribed"]

PIPELINE_STAGES = [
    "Registered", "Reminder Sent", "Attended", "No-Show",
    "Call Booked", "Proposal Sent", "Client Won", "Client Lost",
]


def load_env():
    """Load credentials.env (KEY=VALUE lines) if present, then fall back to os.environ."""
    env = {}
    path = os.path.join(os.path.dirname(__file__), "..", "credentials.env")
    if os.path.exists(path):
        with open(path) as f:
            for line in f:
                line = line.strip()
                if not line or line.startswith("#") or "=" not in line:
                    continue
                k, v = line.split("=", 1)
                env[k.strip()] = v.split("#", 1)[0].strip()
    for k in ("GHL_API_KEY", "GHL_LOCATION_ID"):
        if os.environ.get(k):
            env[k] = os.environ[k]
    return env


def api(method, path, token, body=None):
    url = API_BASE + path
    data = json.dumps(body).encode() if body is not None else None
    req = urllib.request.Request(url, data=data, method=method)
    req.add_header("Authorization", f"Bearer {token}")
    req.add_header("Version", API_VERSION)
    req.add_header("Accept", "application/json")
    if data:
        req.add_header("Content-Type", "application/json")
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            raw = resp.read().decode()
            return resp.status, (json.loads(raw) if raw else {})
    except urllib.error.HTTPError as e:
        raw = e.read().decode()
        try:
            return e.code, json.loads(raw)
        except Exception:
            return e.code, {"error": raw}


def provision_custom_values(token, loc, dry):
    print("\n=== Custom Values ===")
    existing = {}
    if not dry:
        status, data = api("GET", f"/locations/{loc}/customValues", token)
        if status >= 400:
            print(f"  ! Could not list custom values ({status}): {data}")
            return
        for cv in data.get("customValues", data.get("customValue", []) or []):
            existing[cv.get("name", "").lower()] = cv.get("id")
    for name, value in CUSTOM_VALUES:
        if name.lower() in existing:
            print(f"  ✓ exists: {name}")
            continue
        if dry:
            print(f"  + would create: {name} = {value!r}")
            continue
        status, data = api("POST", f"/locations/{loc}/customValues", token,
                           {"name": name, "value": value})
        if status < 400:
            print(f"  + created: {name}")
        else:
            print(f"  ! failed: {name} ({status}): {data}")


def provision_tags(token, loc, dry):
    print("\n=== Tags ===")
    existing = set()
    if not dry:
        status, data = api("GET", f"/locations/{loc}/tags", token)
        if status < 400:
            for t in data.get("tags", []):
                existing.add(t.get("name", "").lower())
    for name in TAGS:
        if name.lower() in existing:
            print(f"  ✓ exists: {name}")
            continue
        if dry:
            print(f"  + would create: {name}")
            continue
        status, data = api("POST", f"/locations/{loc}/tags", token, {"name": name})
        if status < 400:
            print(f"  + created: {name}")
        else:
            print(f"  ! failed: {name} ({status}): {data}")


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--dry-run", action="store_true", help="print actions without calling the API")
    args = ap.parse_args()

    env = load_env()
    token = env.get("GHL_API_KEY")
    loc = env.get("GHL_LOCATION_ID")

    if not args.dry_run and (not token or not loc):
        print("ERROR: set GHL_API_KEY and GHL_LOCATION_ID in credentials.env (or env vars).")
        print("       To get them: GHL > Settings > Private Integrations (API key) and")
        print("       Settings > Business Info (Location ID). See RUNBOOK §10.")
        print("       Run with --dry-run to preview without credentials.")
        sys.exit(1)

    print(f"GHL provisioning {'(DRY RUN)' if args.dry_run else 'for location ' + (loc or '?')}")
    provision_custom_values(token, loc, args.dry_run)
    provision_tags(token, loc, args.dry_run)

    print("\n=== Pipeline (manual — GHL API has no stable pipeline-create endpoint) ===")
    print("  Create pipeline 'Webinar Funnel' in GHL > Settings > Pipelines with these stages:")
    for i, s in enumerate(PIPELINE_STAGES, 1):
        print(f"    {i}. {s}")
    print("\nDone. Next: build the 5 GHL workflows (RUNBOOK §5) and paste emails from emails/html/.")


if __name__ == "__main__":
    main()
