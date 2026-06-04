# Agent 1 — End-to-End Testing Checklist

Run this before sending real traffic. Use a test email + phone you control.

## Setup sanity
- [ ] All 9 custom values exist and resolve on the landing page (visit `/webinar`, confirm topic/date/time render)
- [ ] Email domain shows **Verified** in GHL (SPF + DKIM green)
- [ ] All 3 n8n workflows are **Active** with green webhook URLs

## T1 — Webinar setup
- [ ] Submit the GHL admin form with a test topic/date/time/Zoom ID
- [ ] Confirm n8n WF1 ran and the landing page now shows the new details (no rebuild needed)

## T2 — Registration
- [ ] Register on `/webinar` with a test contact
- [ ] Contact created in GHL with tag `webinar-registered` and stage **Registered**
- [ ] Contact field `webinar_date_dt` is populated (needed for date-based waits)
- [ ] **Email 1 (Confirmation)** lands in the inbox (not spam)
- [ ] **SMS 1** arrives (~2 min later)

## T3–T6 — Pre-webinar sequence (use a near-term test date)
- [ ] Set the webinar date a few minutes/hours out and confirm the date-based steps fire in order
      (Authority → Agenda → T-24h → T-3h → SMS). Tip: temporarily shorten the offsets to verify wiring.

## T7–T8 — Attendance split (the critical one)
- [ ] Manually trigger Zoom `webinar.ended` (or run WF2 with a real past webinar ID)
- [ ] Confirm WF2 pulls attendees and that the Zoom CRC validation responds correctly
- [ ] A test contact who **attended** gets tag `webinar-attended`
- [ ] A test contact who **did not** gets tag `webinar-noshow`
- [ ] Verify the email-vs-name fallback: register with one email, "attend" Zoom under a slightly
      different email but same name → should still match as attended

## T9 — Attended sequence
- [ ] `webinar-attended` fires Workflow 3 → stage **Attended** → Emails 6, 7, 8 in order

## T10 — No-show sequence
- [ ] `webinar-noshow` fires Workflow 4 → stage **No-Show** → Emails 9–13 in order

## T11 — Call booked
- [ ] Book a test slot on Calendly
- [ ] WF3 finds the contact, adds `call-booked`, moves stage to **Call Booked**
- [ ] Randy receives **Email 14 (internal notification)**
- [ ] Lead receives **Email 15 (call prep)**

## Deliverability
- [ ] Run a send through https://www.mail-tester.com — aim for 9–10/10
- [ ] Confirm unsubscribe works and applies tag `unsubscribed`

## Edge cases to eyeball
- [ ] Duplicate registration (same email) doesn't create a second contact / double-send
- [ ] Someone who registers but books a call *before* the webinar still gets `call-booked` handling
- [ ] Zoom report not ready: WF2's 75-min wait is enough (bump if your attendee report lags)
