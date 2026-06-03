# Agent 1 — Detailed Workflow

The complete operational flow of the webinar funnel: every trigger, what fires it, the data that
moves between platforms, the timing, and the decision logic. Read top to bottom — it follows a
single lead through the entire system.

Legend: 🟦 GHL · 🟩 n8n · 🟨 Zoom · 🟧 Calendly · 🟪 SiteGround/DNS

---

## 0. System map (who talks to whom)

```
                         ┌─────────────────────────────┐
   Randy (admin form) ──▶│ 🟦 GHL                       │
                         │  • Landing page (/webinar)  │
   Lead (registration) ─▶│  • Contacts + tags          │◀── 🟩 n8n WF2 (attendance tags)
                         │  • 5 workflows (email/SMS)  │◀── 🟩 n8n WF3 (call-booked tag)
                         │  • Pipeline (8 stages)      │
                         │  • Custom values (9)        │◀── 🟩 n8n WF1 (update values)
                         └──────────┬──────────────────┘
                                    │ sends email/SMS (via verified domain 🟪 DNS)
                                    ▼
                                  Lead
                                    │ joins / attends
                                    ▼
                         ┌─────────────────────────────┐
                         │ 🟨 Zoom Webinar             │
                         │  • Live delivery            │
                         │  • Attendance report        │──▶ 🟩 n8n WF2 (webinar.ended webhook)
                         └─────────────────────────────┘
                                    │ lead books a call
                                    ▼
                         ┌─────────────────────────────┐
                         │ 🟧 Calendly                 │──▶ 🟩 n8n WF3 (invitee.created webhook)
                         └─────────────────────────────┘
```

**Rule of thumb:** 🟦 GHL owns everything *time-based* (send X before/after a date).
🟩 n8n owns everything *event-based that crosses platforms* (Zoom attendance, Calendly bookings,
admin-form → custom values).

---

## PHASE 1 — Webinar Setup (recurring, before each webinar)

### Step 1.1 · Randy schedules the Zoom webinar 🟨
- Randy creates the webinar in Zoom (or it's a recurring one) and notes the **Webinar ID** + join link.
- *Manual, ~2 min. The only Zoom action Randy takes.*

### Step 1.2 · Randy submits the GHL Admin Form 🟦
- Internal form, 5 fields: **Topic, Date, Time, Zoom Webinar ID, Zoom Link.**
- On submit, GHL fires a **Webhook** action → `POST https://<n8n>/webhook/update-webinar`.
- Payload:
  ```json
  { "webinar_topic": "...", "webinar_date": "Tuesday, June 17, 2026",
    "webinar_time": "7:00 PM EST", "zoom_webinar_id": "123 456 7890",
    "zoom_link": "https://zoom.us/j/123456789" }
  ```

### Step 1.3 · n8n WF1 updates the custom values 🟩 → 🟦  *(Trigger T1)*
1. **Webhook** receives the payload.
2. **Normalize Input** maps the 5 fields.
3. **GHL: List Custom Values** — `GET /locations/{id}/customValues` to learn each value's `id`.
4. **Map Name → ID** (Code) — pairs each incoming field with its custom-value id.
5. **GHL: Update Custom Value** — `PUT /locations/{id}/customValues/{cvId}` for each.
6. **Respond Success** — returns `{ ok: true, updated: N }` to GHL.

**Result:** The landing page and every email now read the new webinar details automatically.
**No page rebuild, ever.** This is the reusability engine of the whole funnel.

---

## PHASE 2 — Registration & Confirmation

### Step 2.1 · Traffic arrives → lead registers 🟦
- LinkedIn ads / posts / the 25k email list point to `fedgovstartup.com/webinar`.
- The page renders `{{custom_values.webinar_topic / _date / _time}}`.
- Lead submits **First name, Last name, Email, Phone** → redirected to `thank-you.html`.

### Step 2.2 · n8n... no — GHL Workflow 1 fires 🟦  *(Trigger T2)*
GHL workflow **"On Registration"** (trigger: Form Submitted):
1. Create/find contact (name, email, phone).
2. Add tag **`webinar-registered`**.
3. **Set contact field `webinar_date_dt`** (a real Date field) = the webinar date.
   *← This is critical. All later date-based waits anchor to this contact field, not the custom value.*
4. Move to pipeline stage **Registered**.
5. Send **Email 1 — Confirmation** (Zoom link + lead magnet + add-to-calendar).
6. **Wait 2 minutes.**
7. Send **SMS 1 — Confirmation**.

**Data written to contact:** tags `[webinar-registered]`, field `webinar_date_dt`, pipeline opp at "Registered".

---

## PHASE 3 — Pre-Webinar Nurture (time-based, all 🟦)

GHL Workflow 2 **"Pre-Webinar Nurture"** (trigger: tag `webinar-registered` added). Each step is a
"Wait until [date math on `webinar_date_dt`]" followed by a send:

| Trigger | When | Action |
|---------|------|--------|
| **T3** wait | registration + 1 day | **Email 2 — Authority** |
| **T3** wait | `webinar_date_dt − 3 days` | **Email 3 — Agenda** → move stage **Reminder Sent** |
| **T4** wait | `webinar_date_dt − 24h` | **Email 4 — T-24h Reminder** |
| **T5** wait | `webinar_date_dt − 3h` | **Email 5 — T-3h Reminder** |
| **T6** wait | `webinar_date_dt − 15min` | **SMS 2 — "Starting soon"** |

**Why a contact date field (not the custom value):** GHL's "wait until a date" steps reliably read a
contact-level Date field but not a global custom value. `webinar_date_dt` is set at registration
(Step 2.2.3) so every contact carries their own countdown anchor.

---

## PHASE 4 — The Webinar (live)

### Step 4.1 · Randy delivers 🟨
- The one manual thing Randy does end to end: show up and present on Zoom.

### Step 4.2 · Zoom records attendance 🟨
- Zoom logs join/leave times per participant. This is the **only source of truth** for who attended.

---

## PHASE 5 — Attendance Split (the core logic, 🟩 → 🟦)

### Step 5.1 · Zoom fires `webinar.ended` → n8n WF2  *(Trigger T7)*
1. **Webhook** receives the Zoom event at `https://<n8n>/webhook/zoom-webinar-ended`.
2. **Is URL Validation?** (IF):
   - If Zoom sends a CRC challenge (`endpoint.url_validation`) → **Build CRC Response** (HMAC-SHA256 of
     `plainToken` with the webhook secret) and reply. *(One-time + periodic handshake.)*
   - Otherwise → continue to the real flow.
3. **Wait 75 minutes** — Zoom's attendee report isn't reliably ready at end-of-webinar; this delay lets it settle.
4. **Set Webinar ID** — pulls `payload.object.id` from the original event.

### Step 5.2 · Pull attendees from Zoom 🟨
5. **Zoom: Get OAuth Token** — `POST https://zoom.us/oauth/token?grant_type=account_credentials&account_id=…`
   with Basic auth (client_id:client_secret). Server-to-Server OAuth.
6. **Zoom: Get Attendees** — `GET /v2/report/webinars/{id}/participants` (scope `report:read:admin`),
   paginated via `next_page_token`. Returns everyone who actually joined.

### Step 5.3 · Pull registered contacts from GHL 🟦
7. **GHL: Get Registered Contacts** — all contacts tagged `webinar-registered`
   (production: `POST /contacts/search` filtered by tag), paginated.

### Step 5.4 · Decide attended vs no-show  *(Trigger T8 — the heart of the system)*
8. **Decide Attended / No-Show** (Code):
   - Build two match sets from Zoom: **emails** + **normalized names** (lowercased, trimmed).
   - For each registered GHL contact:
     - `attended = (contact email ∈ Zoom emails) OR (contact full name ∈ Zoom names)`
     - Output `tag = attended ? 'webinar-attended' : 'webinar-noshow'`.
   - **Name fallback** matters: people register with a work email but join Zoom with a personal one.
     Without the name fallback you'd mislabel them as no-shows.

### Step 5.5 · Write the tag back to GHL 🟦
9. **GHL: Add Attendance Tag** — `POST /contacts/{contactId}/tags` with `{ "tags": ["webinar-attended" | "webinar-noshow"] }`.

**Result:** every registrant now carries exactly one of the two attendance tags. Those tags are the
triggers for the two follow-up paths below.

---

## PHASE 6 — Post-Webinar Follow-Up (the fork, all 🟦)

### PATH A — Attended · GHL Workflow 3  *(Trigger T9)*
Trigger: tag `webinar-attended` added.
| When | Action |
|------|--------|
| immediately | move stage **Attended** |
| +1h (from tag) | **Email 6 — Replay + Book-a-Call** |
| +24h | **Email 7 — "Did you watch?"** |
| +48h | **Email 8 — Urgency + Bootcamp alt** |

### PATH B — No-Show · GHL Workflow 4  *(Trigger T10)*
Trigger: tag `webinar-noshow` added.
| When | Action |
|------|--------|
| immediately | move stage **No-Show** |
| +1h | **Email 9 — Replay delivery** |
| +2d | **Email 10 — Re-engagement** |
| +5d | **Email 11 — Last nudge** |
| +7d | **Email 12 — Next webinar invite** |
| +10d | **Email 13 — Bootcamp offer** |

Both paths point at the same finish line: the Calendly booking link in every CTA.

---

## PHASE 7 — Call Booked (🟧 → 🟩 → 🟦)

### Step 7.1 · Lead books on Calendly  *(Trigger T11)*
- Calendly fires `invitee.created` → `POST https://<n8n>/webhook/calendly`.

### Step 7.2 · n8n WF3 processes the booking 🟩
1. **Webhook** receives the event.
2. **Is invitee.created?** (IF) — ignore other event types.
3. **Extract Booking Data** — invitee email, name, scheduled start time.
4. **GHL: Find Contact by Email** — `POST /contacts/search` by email.
5. **Resolve Contact** (Code) — get `contactId` (or flag not-found for a create-contact branch).
6. **GHL: Tag `call-booked`** — `POST /contacts/{id}/tags`.

### Step 7.3 · GHL Workflow 5 closes the loop 🟦
Trigger: tag `call-booked` added.
1. Move to pipeline stage **Call Booked**.
2. Send **Email 14 — internal notification** to Randy (lead details).
3. Send **Email 15 — call-prep** to the lead.

**Result:** the lead is now a warm, booked prospect sitting in the sales pipeline at **Call Booked**,
ready for Randy. Downstream stages (Proposal Sent → Client Won/Lost) are managed manually by Randy.

---

## Trigger chain — one-line reference

| T | Owner | Fires on | Outcome |
|---|-------|----------|---------|
| T1 | 🟦→🟩 WF1 | Admin form submit | 9 custom values updated → page auto-populates |
| T2 | 🟦 WF1-onReg | Lead registers | contact + `webinar-registered` + `webinar_date_dt` + Email1 + SMS1 |
| T3 | 🟦 WF2-nurture | reg+1d / date−3d | Authority + Agenda emails |
| T4 | 🟦 | date−24h | Reminder email |
| T5 | 🟦 | date−3h | "Today" email |
| T6 | 🟦 | date−15m | SMS reminder |
| T7 | 🟨→🟩 WF2 | `webinar.ended` (+75m) | pull Zoom attendees |
| T8 | 🟩 WF2 | attendance data | **tag attended / no-show** |
| T9 | 🟦 WF3 | tag `webinar-attended` | 3-email attended path |
| T10 | 🟦 WF4 | tag `webinar-noshow` | 5-email no-show path |
| T11 | 🟧→🟩 WF3 | `invitee.created` | tag `call-booked` → notify + prep |

---

## Failure modes & how the workflow handles them

| Risk | Where | Mitigation in this build |
|------|-------|--------------------------|
| Date trigger won't read a custom value | Phase 3 | Anchor waits to contact field `webinar_date_dt` (set in T2) |
| Attendee report not ready | Phase 5 | 75-min wait before pulling the report |
| Register vs. Zoom email mismatch | Phase 5 | Name-based fallback match in WF2 Code node |
| Large attendee/contact lists | Phase 5 | Pagination on both Zoom (`next_page_token`) and GHL (`startAfter`) |
| Zoom webhook spoofing | Phase 5 | CRC validation via webhook secret token |
| Calendly sends non-booking events | Phase 7 | IF gate on `invitee.created` |
| Contact books before webinar | Phase 7 | `call-booked` path is independent of attendance tags |
| Lead unsubscribes | All email | `unsubscribed` tag suppresses sends (GHL native) |

---

## Two-minute mental model

1. **Set up:** Randy fills a form → n8n updates GHL → page is ready. *(T1)*
2. **Register:** lead opts in → tagged + confirmed instantly. *(T2)*
3. **Nurture:** GHL drips 5 timed touches to the webinar. *(T3–T6)*
4. **Attend:** Randy presents → Zoom records who showed. *(live)*
5. **Split:** n8n compares Zoom vs GHL → tags attended/no-show. *(T7–T8)*
6. **Follow-up:** the tag fires the matching email path. *(T9/T10)*
7. **Book:** Calendly booking → tagged, staged, Randy notified. *(T11)*

Randy does two things total: schedule the Zoom webinar + the admin form (Phase 1), and deliver the
webinar (Phase 4). Everything else is automatic.
