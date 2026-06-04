# Agent 1 — GHL Build Sheet (click-by-click)

The custom values + tags are already created (via the provisioning script). This sheet covers the
parts GHL only lets you build in the dashboard: the **pipeline** and the **5 workflows**. Follow it
top to bottom like a recipe. Email bodies to paste are in `emails/html/` (the file name is given at
each step); subject lines are in `emails/html/SUBJECTS.md`.

> Tip: open `emails/html/` in one tab and GHL in another. For each "Send Email" step, open the named
> HTML file, copy all of it, and in GHL's email editor switch to the **Code/HTML** view and paste.

---

## A. Pipeline (do this first — workflows reference its stages)

GHL → **Settings → Pipelines → + Create new pipeline**
- Name: **Webinar Funnel**
- Add these stages in this exact order, then Save:
  1. Registered
  2. Reminder Sent
  3. Attended
  4. No-Show
  5. Call Booked
  6. Proposal Sent
  7. Client Won
  8. Client Lost

---

## B. One-time prerequisite: a contact date field

The pre-webinar reminders count down from the webinar date. GHL date waits need a **contact** date
field, so create one:

GHL → **Settings → Custom Fields → Add Field**
- Type: **Date**
- Name: **Webinar Date DT**  (this creates key `contact.webinar_date_dt`)

---

## C. Workflow 1 — On Registration

Automation → **Workflows → + Create Workflow → Start from scratch**. Name: **1 · On Registration**.

- **Trigger:** Form Submitted → select your webinar registration form.
- **Actions (in order):**
  1. **Add Contact Tag** → `webinar-registered`
  2. **Update Contact Field** → field *Webinar Date DT* = `{{custom_values.webinar_date}}`
     *(if GHL won't parse the text date, set this field from the admin form / registration date instead)*
  3. **Create/Update Opportunity** → Pipeline **Webinar Funnel**, Stage **Registered**
  4. **Send Email** → subject from SUBJECTS.md (`01-confirmation`), body = `emails/html/01-confirmation.html`
  5. **Wait** → 2 minutes
  6. **Send SMS** → text:
     `Hi {{contact.first_name}}, you're confirmed for "{{custom_values.webinar_topic}}" on {{custom_values.webinar_date}} at {{custom_values.webinar_time}}. Zoom: {{custom_values.zoom_link}} — Randy Wimmer / GovCon Academy`
- **Save + Publish** (toggle the workflow on).

---

## D. Workflow 2 — Pre-Webinar Nurture

New workflow. Name: **2 · Pre-Webinar Nurture**.

- **Trigger:** Contact Tag Added → `webinar-registered`
- **Actions:**
  1. **Wait** → "Time delay" 1 day → **Send Email** `02-authority.html`
  2. **Wait** → "Wait until" a date/time = field *Webinar Date DT*, **minus 3 days** → **Send Email** `03-agenda.html`
  3. **Update Opportunity** → Stage **Reminder Sent**
  4. **Wait** → until *Webinar Date DT* **minus 1 day** → **Send Email** `04-reminder-24h.html`
  5. **Wait** → until *Webinar Date DT* **minus 3 hours** → **Send Email** `05-reminder-3h.html`
  6. **Wait** → until *Webinar Date DT* **minus 15 minutes** → **Send SMS**:
     `{{contact.first_name}} — we start in 15 minutes! Join now: {{custom_values.zoom_link}} — Randy`
- **Save + Publish.**

---

## E. Workflow 3 — Post-Webinar: Attended

New workflow. Name: **3 · Attended**.

- **Trigger:** Contact Tag Added → `webinar-attended`
- **Actions:**
  1. **Update Opportunity** → Stage **Attended**
  2. **Send Email** `06-attended-replay-book.html`
  3. **Wait** → 23 hours
  4. **Send Email** `07-attended-followup.html`
  5. **Wait** → 24 hours
  6. **Send Email** `08-attended-urgency.html`
- **Save + Publish.**

---

## F. Workflow 4 — Post-Webinar: No-Show

New workflow. Name: **4 · No-Show**.

- **Trigger:** Contact Tag Added → `webinar-noshow`
- **Actions:**
  1. **Update Opportunity** → Stage **No-Show**
  2. **Send Email** `09-noshow-replay.html`
  3. **Wait** 1 day → **Send Email** `10-noshow-reengage.html`
  4. **Wait** 3 days → **Send Email** `11-noshow-nudge.html`
  5. **Wait** 2 days → **Send Email** `12-noshow-next-webinar.html`
  6. **Wait** 3 days → **Send Email** `13-noshow-bootcamp.html`
- **Save + Publish.**

---

## G. Workflow 5 — Call Booked

New workflow. Name: **5 · Call Booked**.

- **Trigger:** Contact Tag Added → `call-booked`  *(n8n WF3 applies this tag from Calendly)*
- **Actions:**
  1. **Update Opportunity** → Stage **Call Booked**
  2. **Send Internal Notification** (email) to Randy (`randy.wimmer@gmail.com`):
     subject `New call booked: {{contact.first_name}} {{contact.last_name}}`, body = contact email/phone
  3. **Send Email** to the lead → `15-call-prep.html`
- **Save + Publish.**

---

## Done check

- [ ] Pipeline "Webinar Funnel" with 8 stages
- [ ] Contact date field "Webinar Date DT"
- [ ] Workflows 1–5 built and **published** (each toggle ON)
- [ ] Every "Send Email" step has its HTML pasted + subject set
- [ ] Test: register yourself → confirm Email 1 + SMS arrive and the contact is tagged + in the pipeline

When workflows are live, continue with `RUNBOOK.md` §1 (DNS), §7 (Zoom), §8 (Calendly), §9 (n8n).
