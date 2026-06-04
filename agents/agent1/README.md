# Agent 1 — Webinar Funnel Automation

**Client:** Randy Wimmer — Government Contracting Academy / ISO Certification Group
**Account Manager:** BrndGuru (brndguruofficial@gmail.com)
**Domain:** fedgovstartup.com
**Goal:** Fully automated webinar funnel. Randy only delivers the live webinar; everything
else (registration, reminders, attendance split, follow-up, call booking) runs on its own.

```
TRAFFIC → REGISTER → CONFIRM → NURTURE → WEBINAR → FOLLOW-UP → CALL BOOKED
```

---

## Platform stack

| Platform | Role |
|----------|------|
| GoHighLevel (GHL) | CRM, landing page, all email/SMS, pipeline, time-based workflows |
| Zoom Webinars | Live delivery + the only source of truth for attendance |
| n8n (self-hosted VPS) | Cross-platform glue: Zoom→GHL tagging, Calendly→GHL, admin-form→GHL |
| Calendly | Call booking — the finish line of every sequence |
| SiteGround | Hosts the domain + DNS (SPF/DKIM) for email deliverability |

**Division of labor:** GHL handles everything *time-based* (send X days before/after).
n8n handles everything *event-based that crosses platforms* (Zoom attendance, Calendly bookings).

---

## What's in this folder

```
agents/agent1/
  README.md                 ← you are here
  WORKFLOW.md               ← detailed end-to-end flow (every trigger, data, timing)
  RUNBOOK.md                ← step-by-step setup for the login-required parts (GHL, DNS, Zoom, Calendly, n8n)
  TESTING.md                ← end-to-end test checklist before go-live
  diagram.mmd               ← Mermaid flow diagram (render at mermaid.live)
  credentials.example.env   ← placeholder env vars; copy to credentials.env (gitignored) for real values
  emails/
    SEQUENCE.md             ← all copy (10 emails + 2 SMS + 2 internal) with merge tags
    html/                   ← branded, paste-ready HTML for emails 1–13 + call-prep + SUBJECTS.md
  n8n/                      ← 3 importable workflows + docker-compose/Caddy deploy stack + README
  landing/                  ← registration, thank-you, and internal admin-form HTML
  scripts/
    provision_ghl.py        ← creates custom values + tags via GHL API (idempotent; --dry-run)
    build_emails.py         ← regenerates emails/html/ from the content table
```

---

## Build status

| # | Deliverable | Type | Status |
|---|-------------|------|--------|
| 1 | Email + SMS copy + branded HTML | File (this repo) | ✅ Built — `emails/SEQUENCE.md` + `emails/html/` |
| 2 | Landing + thank-you page | File (this repo) | ✅ Built — see `landing/` |
| 3 | n8n: Update Webinar Details | File (this repo) | ✅ Built — `n8n/wf1-update-webinar-details.json` |
| 4 | n8n: Zoom Attendance Split | File (this repo) | ✅ Built — `n8n/wf2-zoom-attendance-split.json` |
| 5 | n8n: Calendly → GHL | File (this repo) | ✅ Built — `n8n/wf3-calendly-webhook.json` |
| 6 | GHL custom values (9) | Script (needs API key) | ⚡ `scripts/provision_ghl.py` — run after adding API key |
| 7 | GHL pipeline (8 stages) | UI — needs login | ⏳ See RUNBOOK §3 |
| 8 | GHL tags (5) | Script (needs API key) | ⚡ `scripts/provision_ghl.py` — run after adding API key |
| 9 | GHL workflows (5) | UI — needs login | ⏳ See RUNBOOK §5 |
| 10 | GHL admin form | UI + standalone HTML | ✅ HTML built (`landing/admin-form.html`); GHL version per RUNBOOK §6 |
| 14b | n8n deploy stack | File (this repo) | ✅ Built — `n8n/docker-compose.yml` + Caddy + README |
| 11 | DNS records (SPF/DKIM/CNAME) | SiteGround — needs login | ⏳ See RUNBOOK §1 |
| 12 | Zoom Server-to-Server OAuth app | Zoom — needs login | ⏳ See RUNBOOK §7 |
| 13 | Calendly webhook + token | Calendly — needs login | ⏳ See RUNBOOK §8 |
| 14 | n8n deployed on VPS | VPS — needs SSH | ⏳ See RUNBOOK §9 |

---

## Real business content (sourced from Randy's onboarding email)

- **Host:** Randy Wimmer, founder, Government Contracting Academy / ISO Certification Group;
  author of *GOOD ENOUGH! to Launch Your Company*; LinkedIn newsletter *The GovCon Times*.
- **Calendly booking URL:** `https://calendly.com/randy-wimmer/30-minute-zoom-mtg-with-randy`
- **From-email domain:** `@fedgovstartup.com`
- **Webinar topic rotation (biweekly):**
  1. Get On Contract Vehicles Without Extensive Past Performances!
  2. How to Write Higher Scored Proposals that WIN!
  3. The FAST Way to Get Sub-Contracts
  4. How to Win MASSIVE Contracts as a Small Business
  5. Are You Using AI the RIGHT WAY to Write Bids?
  6. How to Maximize Contract Profitability
- **Audience:** Small businesses pursuing U.S. government contracts (GovCon).
- **Assets on hand (from Randy, not in this repo):** 25k verified email list, 2–3 lead magnets,
  brand logos/videos (delivered in "Bi-Weekly Webinar Campaign.zip").

---

## ⚠️ Credentials & security

- **No real secrets live in this repo.** Use `credentials.example.env` as a template; put real
  values in `credentials.env`, which is gitignored.
- The credentials Randy shared (GHL, email, Zoom, SiteGround, etc.) were transmitted in plaintext
  and should be **rotated** after setup.
- A code agent cannot operate the GHL/Zoom/Calendly/SiteGround web UIs — the steps that require
  logging in are documented in `RUNBOOK.md` for a human to execute.

---

## The trigger chain (T1–T11) at a glance

| Trigger | Owner | Fires when | Does what |
|---------|-------|-----------|-----------|
| T1 | GHL form → n8n WF1 | Randy submits admin form | Updates 4 custom values → page auto-updates |
| T2 | GHL | Lead registers | Create contact, tag `webinar-registered`, confirm email+SMS |
| T3 | GHL | webinar_date − 3 days | Agenda preview email |
| T4 | GHL | webinar_date − 24h | Reminder email |
| T5 | GHL | start − 3h | "Today" reminder email |
| T6 | GHL | start − 15min | SMS reminder |
| T7 | n8n WF2 | end + 75min | Poll Zoom attendees/absentees |
| T8 | n8n WF2 | attendance data received | **Tag each contact attended / no-show (core logic)** |
| T9 | GHL | tag `webinar-attended` | 3-email attended sequence |
| T10 | GHL | tag `webinar-noshow` | 5-email no-show sequence |
| T11 | Calendly → n8n WF3 | call booked | Tag `call-booked`, move stage, notify Randy |

---

## Known risks (validate early)

1. **Date-based triggers vs. custom values.** GHL date triggers don't reliably anchor to a
   *custom value*. Plan: store the webinar date on the **contact record** at registration so the
   pre-webinar sequence (T3–T6) has a per-contact date to count from. (See RUNBOOK §5.)
2. **Attendance matching is by email and will be imperfect.** People register and join Zoom with
   different emails. WF2 includes a name-based fallback match to reduce false no-shows.
3. **Zoom report lag.** Attendee reports aren't always ready at end-of-webinar; the +75min delay
   in WF2 mitigates this.
