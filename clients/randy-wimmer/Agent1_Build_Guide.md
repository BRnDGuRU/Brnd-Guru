# AGENT 1 — WEBINAR FUNNEL AUTOMATION
## Complete Build Guide for New Claude Session

---

## WHO YOU ARE WORKING WITH

**Client:** Randy Wimmer  
**Business:** Government Contracting Academy / ISO Certification Group  
**Goal:** Help government contractors get certified for ISO standards  
**Product:** ISO certification coaching / consulting  
**Webinar Model:** Randy runs free live webinars on Zoom to educate prospects, then books sales calls  
**Account Manager:** Shivanshu / BrndGuru (brndguruofficial@gmail.com)

---

## WHAT YOU ARE BUILDING

**Agent 1 = Webinar Funnel Automation**

A fully automated system that:
1. Hosts a registration landing page on GHL (fedgovstartup.com)
2. Sends all confirmation, reminder, and follow-up emails + SMS automatically
3. Detects who attended vs who missed the webinar (via Zoom + n8n)
4. Fires two separate post-webinar sequences (attended path / no-show path)
5. Drives every lead toward booking a 30-min call on Randy's Calendly

Randy only does ONE thing: show up and deliver the live webinar.
Everything else is automated.

---

## CREDENTIALS

Fill these in before using this document in a new session:

### GoHighLevel (GHL)
- **Login URL:** https://app.gohighlevel.com
- **Email:** [FILL IN]
- **Password:** [FILL IN]
- **Sub-Account Name:** [FILL IN - Randy's sub-account]
- **API Key (Location):** Settings > Integrations > API Key > [FILL IN]

### Zoom
- **Login URL:** https://zoom.us
- **Email:** [FILL IN]
- **Password:** [FILL IN]
- **Account Type:** Zoom Webinars license (NOT just Meetings)
- **Client ID (OAuth App):** [FILL IN]
- **Client Secret (OAuth App):** [FILL IN]
- **Webhook Secret Token:** [FILL IN - set when creating webhook]

### SiteGround (for DNS)
- **Login URL:** https://my.siteground.com
- **Email:** [FILL IN]
- **Password:** [FILL IN]
- **Domain:** fedgovstartup.com
- **DNS Zone access needed for:** SPF, DKIM, CNAME records

### Calendly
- **Login URL:** https://calendly.com
- **Email:** [FILL IN]
- **Password:** [FILL IN]
- **Booking Page URL:** [FILL IN - e.g. calendly.com/randy-wimmer/30min]

### n8n (Self-Hosted on VPS)
- **VPS Provider:** [FILL IN]
- **n8n URL:** [FILL IN - e.g. http://YOUR_VPS_IP:5678]
- **n8n Username:** [FILL IN]
- **n8n Password:** [FILL IN]
- **SSH Access:** [FILL IN if needed]

### GHL Custom Values (auto-updated by n8n each webinar)
These are dynamic fields that auto-populate the landing page:
- `{{custom.webinar_topic}}` — e.g. "Get On Contract Vehicles Without Extensive Past Performances"
- `{{custom.webinar_date}}` — e.g. "Tuesday, June 17, 2026"
- `{{custom.webinar_time}}` — e.g. "7:00 PM EST"
- `{{custom.zoom_webinar_id}}` — e.g. "123 456 7890"

---

## PLATFORM STACK

| Platform | Role | Cost |
|----------|------|------|
| GoHighLevel (GHL) | CRM, landing page, emails, SMS, pipeline, automation | $97/mo |
| Zoom Webinars | Live webinar delivery, attendance tracking, replay recording | Existing |
| n8n (VPS) | Automation connector — Zoom -> GHL tagging, triggers sequences | FREE |
| SiteGround | Hosts fedgovstartup.com, DNS records for email deliverability | Existing |
| Calendly | Call booking page linked in every CTA email | Free tier |

---

## THE 7-STAGE JOURNEY

Every lead goes through this exact path:

```
TRAFFIC -> REGISTER -> CONFIRM -> NURTURE -> WEBINAR -> FOLLOW-UP -> CALL BOOKED
```

1. **TRAFFIC** — LinkedIn ads / organic posts / email campaigns all point to the registration page
2. **REGISTER** — GHL landing page on fedgovstartup.com captures name, email, phone
3. **CONFIRM** — Instant email + SMS: Zoom link + lead magnet + calendar add button
4. **NURTURE** — 2-3 emails over next few days building authority and anticipation
5. **WEBINAR** — Randy delivers live on Zoom. n8n tracks attendance in real-time.
6. **FOLLOW-UP** — Two paths: Attended sequence OR No-Show sequence (see below)
7. **CALL BOOKED** — Lead books via Calendly. Enters sales pipeline as warm prospect.

---

## THE COMPLETE EMAIL + SMS SEQUENCE (10 Touches)

### BEFORE WEBINAR

| Timing | Type | Content |
|--------|------|---------|
| Instantly on register | EMAIL | Confirmation + Zoom link + lead magnet download + Add to Calendar button |
| Instantly on register | SMS | "You're in! Here's your webinar link: [ZOOM_LINK]. See you [DATE]!" |
| Day +1 after register | EMAIL | Authority email — share a key GovCon insight to build trust in Randy |
| 3 days before webinar | EMAIL | Agenda preview + quick win tip related to the webinar topic |
| T-24 hours | EMAIL | Reminder + Zoom link + "Bring your questions" |

### DAY OF WEBINAR

| Timing | Type | Content |
|--------|------|---------|
| T-3 hours | EMAIL | Today reminder — single CTA: "Click here to join" |
| T-15 minutes | SMS | "Starting in 15 min! Click here to join: [ZOOM_LINK]" |

### AFTER WEBINAR (Split by Attendance)

**PATH A — ATTENDED:**

| Timing | Type | Content |
|--------|------|---------|
| 1 hour after | EMAIL | Replay link + Book-a-Call CTA (primary action) |
| 24 hours after | EMAIL | "Did you get a chance to watch the replay / book your call?" |
| 48 hours after | EMAIL | Urgency email — last chance + Bootcamp offer as alternative |

**PATH B — NO-SHOW:**

| Timing | Type | Content |
|--------|------|---------|
| 1 hour after | EMAIL | Replay delivery — "You missed it but here's the recording" |
| Day +2 | EMAIL | Re-engagement — highlight one key insight from the webinar |
| Day +5 | EMAIL | Last nudge — Book a call before spots fill |
| Day +7 | EMAIL | Invite to next webinar |
| Day +10 | EMAIL | Bootcamp offer as alternative entry point |

---

## THE TRIGGER CHAIN (T1-T11)

### T1 — Webinar Setup Trigger
- **What fires it:** Randy fills a GHL Admin Form (4 fields: topic, date, time, Zoom ID)
- **What happens:** n8n webhook receives form submission, updates all 4 GHL Custom Values instantly
- **Result:** Landing page auto-updates with new webinar details. No page rebuild needed.

### T2 — Registration Trigger
- **What fires it:** Lead submits the GHL registration form
- **What happens:** 
  - GHL creates contact with name, email, phone
  - Adds tag: `webinar-registered`
  - Triggers "Confirmation" GHL workflow
  - Sends confirmation email + SMS instantly
  - Assigns to webinar pipeline (stage: Registered)

### T3 — 3 Days Before Trigger
- **What fires it:** GHL date-based trigger = webinar_date minus 3 days
- **What happens:** Agenda preview email fires to all contacts tagged `webinar-registered`

### T4 — T-24 Hours Trigger
- **What fires it:** GHL date-based trigger = webinar_date minus 1 day
- **What happens:** Reminder email with Zoom link fires

### T5 — T-3 Hours Trigger
- **What fires it:** GHL date-based trigger = webinar start time minus 3 hours
- **What happens:** Same-day reminder email fires

### T6 — T-15 Minutes Trigger
- **What fires it:** GHL date-based trigger = webinar start time minus 15 minutes
- **What happens:** SMS reminder fires

### T7 — Webinar Ends Trigger
- **What fires it:** n8n scheduled check (polls Zoom API ~1 hour after webinar end time)
- **What happens:** n8n calls Zoom API: GET /v2/past_webinars/{webinar_id}/absentees and /attendees

### T8 — Attendance Split (Core Logic)
- **What fires it:** n8n receives Zoom attendance data
- **What happens:**
  - For each contact in GHL:
    - If they appear in Zoom attendees list → add tag `webinar-attended`
    - If they do NOT appear → add tag `webinar-noshow`
  - GHL workflows listen for these tags and fire the appropriate sequence

### T9 — Attended Sequence Trigger
- **What fires it:** GHL tag `webinar-attended` applied to contact
- **What happens:** 3-email attended follow-up sequence starts (1hr, 24hr, 48hr)

### T10 — No-Show Sequence Trigger
- **What fires it:** GHL tag `webinar-noshow` applied to contact
- **What happens:** 5-email no-show sequence starts (1hr, Day2, Day5, Day7, Day10)

### T11 — Call Booked Trigger
- **What fires it:** Calendly webhook fires when lead books a call
- **What happens:**
  - GHL contact updated: tag `call-booked` added
  - Pipeline stage moved to: "Call Booked"
  - Confirmation email to Randy: lead details + booking time
  - Confirmation email to lead: call prep instructions

---

## GHL STRUCTURE TO BUILD

### Sub-Account Setup
- Sub-account name: Government Contracting Academy
- Timezone: EST (Randy's timezone)
- Phone number: needs SMS capability (Twilio integrated via GHL)

### Custom Values (4 to create)
Go to: Settings > Custom Values > Add New
1. Name: `webinar_topic` | Key: `custom.webinar_topic`
2. Name: `webinar_date` | Key: `custom.webinar_date`
3. Name: `webinar_time` | Key: `custom.webinar_time`
4. Name: `zoom_webinar_id` | Key: `custom.zoom_webinar_id`

### Landing Page
- URL slug: `/webinar` on fedgovstartup.com
- Headline: uses `{{custom.webinar_topic}}`
- Date/Time block: uses `{{custom.webinar_date}}` and `{{custom.webinar_time}}`
- Registration form fields: First Name, Last Name, Email, Phone
- On submit: redirect to thank-you page
- Thank-you page: "Check your email for your Zoom link!"

### Pipeline: Webinar Funnel
Stages (in order):
1. Registered
2. Reminder Sent
3. Attended
4. No-Show
5. Call Booked
6. Proposal Sent
7. Client Won
8. Client Lost

### Tags to Create
- `webinar-registered`
- `webinar-attended`
- `webinar-noshow`
- `call-booked`
- `unsubscribed`

### GHL Workflows to Build

**Workflow 1: On Registration**
- Trigger: Form submitted (registration form)
- Actions:
  - Add tag: `webinar-registered`
  - Move to pipeline stage: Registered
  - Send email: Confirmation (with Zoom link + lead magnet)
  - Wait 2 minutes
  - Send SMS: Confirmation SMS

**Workflow 2: Pre-Webinar Nurture**
- Trigger: Tag added `webinar-registered`
- Actions:
  - Wait until [Day +1 from registration]: Send Authority email
  - Wait until [webinar_date - 3 days]: Send Agenda preview email
  - Wait until [webinar_date - 24 hours]: Send Reminder email
  - Wait until [webinar_date - 3 hours]: Send Today reminder email
  - Wait until [webinar_date - 15 minutes]: Send SMS

**Workflow 3: Post-Webinar Attended**
- Trigger: Tag added `webinar-attended`
- Actions:
  - Move to pipeline stage: Attended
  - Send email: Replay + Book-a-Call (1 hr)
  - Wait 23 hours
  - Send email: Follow-up ("Did you get a chance?")
  - Wait 24 hours
  - Send email: Urgency + Bootcamp offer

**Workflow 4: Post-Webinar No-Show**
- Trigger: Tag added `webinar-noshow`
- Actions:
  - Move to pipeline stage: No-Show
  - Send email: Replay delivery (1 hr)
  - Wait 1 day
  - Send email: Re-engagement
  - Wait 3 days
  - Send email: Last nudge
  - Wait 2 days
  - Send email: Next webinar invite
  - Wait 3 days
  - Send email: Bootcamp offer

**Workflow 5: Call Booked**
- Trigger: Tag added `call-booked` (set by Calendly webhook via n8n)
- Actions:
  - Move to pipeline stage: Call Booked
  - Send internal notification to Randy
  - Send email to lead: Call prep + what to expect

### Admin Form (for Randy to update webinar details)
Build a separate GHL form (internal use only):
- Fields:
  - Webinar Topic (text)
  - Webinar Date (date picker)
  - Webinar Time (dropdown: EST times)
  - Zoom Webinar ID (text)
- On submit: fires n8n webhook to update Custom Values

---

## n8n WORKFLOWS TO BUILD

### n8n Workflow 1: Update Webinar Details
**Trigger:** Webhook (receives data from GHL Admin Form)
**Nodes:**
1. Webhook node — receives: topic, date, time, zoom_id
2. GHL API node — PATCH Custom Values:
   - PUT https://services.leadconnectorhq.com/locations/{locationId}/customValues/{id}
   - Update each of the 4 fields
3. Response node — confirm success

**Credentials needed:** GHL API Key

### n8n Workflow 2: Zoom Attendance Split
**Trigger:** Schedule node (runs 75 minutes after scheduled webinar end time)
**Nodes:**
1. Schedule Trigger — fires at webinar_end_time + 75 min
2. Zoom API node — GET /v2/past_webinars/{webinar_id}/attendees
3. Zoom API node — GET /v2/past_webinars/{webinar_id}/absentees
4. GHL Search node — get all contacts tagged `webinar-registered`
5. Split In Batches node — loop through all registered contacts
6. IF node — check: is contact email in Zoom attendees list?
   - YES: GHL API — add tag `webinar-attended`
   - NO: GHL API — add tag `webinar-noshow`
7. Set node — log results

**Credentials needed:** Zoom OAuth credentials, GHL API Key

### n8n Workflow 3: Calendly Webhook
**Trigger:** Webhook (Calendly sends POST when call booked)
**Nodes:**
1. Webhook node — receives Calendly event data
2. Extract node — get invitee email + name + booking time
3. GHL API node — search contact by email
4. GHL API node — add tag `call-booked`
5. GHL API node — update pipeline stage to "Call Booked"
6. Email node — notify Randy with lead details

**Credentials needed:** Calendly API token, GHL API Key

---

## DNS SETUP ON SITEGROUND

Log in to SiteGround > Websites > fedgovstartup.com > cPanel > Zone Editor

Records to add:

| Type | Name | Value |
|------|------|-------|
| TXT | @ | v=spf1 include:sendgrid.net include:mailgun.org ~all |
| TXT | s1._domainkey | [GHL DKIM key 1 — get from GHL Settings > Email Services] |
| TXT | s2._domainkey | [GHL DKIM key 2 — get from GHL Settings > Email Services] |
| CNAME | em | sendgrid.net (or GHL's CNAME — get from GHL Email Settings) |

After adding: go back to GHL > Settings > Email Services > Verify Domain

---

## ZOOM WEBHOOK SETUP

In Zoom App Marketplace, create an OAuth app:
1. Go to marketplace.zoom.us > Develop > Build App
2. App type: Server-to-Server OAuth (recommended) or General OAuth
3. Scopes needed:
   - webinar:read:admin
   - webinar:read:list_absentees
   - webinar:read:list_attendees
   - webinar:read:list_webinars
4. Event subscriptions (webhook):
   - webinar.ended (POST to n8n webhook URL)

---

## CALENDLY WEBHOOK SETUP

1. Log in to Calendly > Integrations > Webhooks
2. Create webhook:
   - Event: invitee.created
   - URL: [your n8n webhook URL]/calendly
3. Also note the API token: Integrations > API & Webhooks > Personal Access Tokens

---

## GHL API REFERENCE

Base URL: `https://services.leadconnectorhq.com`

Key endpoints:
- Contacts: `GET/POST /contacts`
- Add tag: `POST /contacts/{contactId}/tags` — body: `{"tags": ["tag-name"]}`
- Custom Values: `GET/PUT /locations/{locationId}/customValues`
- Pipeline: `POST /opportunities` / `PUT /opportunities/{id}`

Auth header: `Authorization: Bearer {API_KEY}` + `Version: 2021-07-28`

---

## EMAIL TEMPLATES — CONTENT GUIDE

### Email 1: Confirmation (fires instantly)
**Subject:** You're registered! Here's everything you need [First Name]
**Content:**
- You're confirmed for: [webinar_topic]
- Date: [webinar_date] at [webinar_time] EST
- Your Zoom link: [zoom_link]
- Download your free guide: [lead_magnet_link]
- Add to calendar: [Google] [Apple] [Outlook]
- CTA: Add to calendar now

### Email 2: Authority (Day +1)
**Subject:** The #1 mistake small businesses make with government contracts
**Content:**
- Share a specific insight / stat about GovCon or ISO certification
- Position Randy as the expert
- Soft CTA: "See you at the webinar"

### Email 3: Agenda Preview (3 days before)
**Subject:** Here's exactly what we're covering on [DATE]
**Content:**
- 3-4 bullet points from the webinar agenda
- Quick win tip related to the topic
- CTA: "Make sure you're registered — here's your link again"

### Email 4: T-24 Hour Reminder
**Subject:** Tomorrow at [TIME] — don't forget [First Name]
**Content:**
- Reminder with date + time
- Zoom link prominent
- "Bring your biggest question about [topic]"
- CTA: Add to calendar / Join link

### Email 5: T-3 Hour Reminder
**Subject:** TODAY at [TIME] — your webinar is in 3 hours
**Content:**
- Simple, short
- One CTA: Join button with Zoom link
- "See you in 3 hours"

### SMS 1: Confirmation
"Hi [First Name], you're confirmed for [webinar_topic] on [date] at [time] EST. Zoom link: [link] - BrndGuru / Randy Wimmer"

### SMS 2: T-15 Min Reminder
"[First Name] — your webinar starts in 15 min! Click to join: [zoom_link]"

### Email 6: Post-Webinar Attended (1 hr after)
**Subject:** Here's the replay + your next step [First Name]
**Content:**
- "Great to have you on the call today"
- Replay link (prominent)
- 1 CTA: Book your free 30-min ISO strategy call
- Calendly link

### Email 7: Attended Follow-Up (24 hrs)
**Subject:** Did you get a chance to watch? [First Name]
**Content:**
- Reference something specific from the webinar
- "I have a few spots open this week for a free call"
- CTA: Book now on Calendly

### Email 8: Attended Urgency (48 hrs)
**Subject:** Last chance [First Name] — only 2 spots left this week
**Content:**
- Urgency — spots filling
- Alternative: mention the Bootcamp for those not ready for 1-on-1
- CTA: Book call OR join Bootcamp

### Email 9: No-Show Replay (1 hr after)
**Subject:** You missed it — but here's the recording [First Name]
**Content:**
- "No worries, life gets busy"
- Replay link — watch in 45 min
- CTA: Watch the replay

### Email 10: No-Show Re-engagement (Day +2)
**Subject:** One thing you should know about ISO certification
**Content:**
- Pull out one key insight from the webinar
- "This is just one thing we covered — there's more in the replay"
- CTA: Watch replay + Book a call

---

## BUILD SEQUENCE (What to Build in What Order)

**Week 1 — Foundation**
1. Set up GHL sub-account for Randy
2. Create the 4 Custom Values
3. Set up DNS on SiteGround (SPF, DKIM)
4. Verify email domain in GHL
5. Build the registration landing page using Custom Values

**Week 2 — Automation**
6. Build GHL Workflow 1: On Registration (confirmation email + SMS)
7. Build GHL Workflow 2: Pre-webinar nurture sequence
8. Set up n8n on VPS
9. Build n8n Workflow 1: Update Webinar Details (from GHL Admin Form)

**Week 3 — Post-Webinar Logic**
10. Set up Zoom OAuth app + scopes
11. Build n8n Workflow 2: Zoom Attendance Split
12. Build GHL Workflow 3: Attended sequence
13. Build GHL Workflow 4: No-Show sequence

**Week 4 — Calendly + Testing**
14. Build n8n Workflow 3: Calendly webhook -> GHL tag
15. Build GHL Workflow 5: Call booked notification
16. Write all 10 email templates in GHL
17. Full end-to-end test: Register -> go through webinar -> check attendance split -> check sequences

---

## TESTING CHECKLIST

Before going live, test every step:
- [ ] Register on the landing page with a test email
- [ ] Confirm GHL contact is created with `webinar-registered` tag
- [ ] Confirm confirmation email arrives in inbox (not spam)
- [ ] Confirm confirmation SMS arrives
- [ ] Trigger n8n workflow manually to update Custom Values — check page updates
- [ ] Simulate webinar end — manually run n8n attendance split with test data
- [ ] Confirm `webinar-attended` tag fires the correct email sequence
- [ ] Confirm `webinar-noshow` tag fires the correct email sequence
- [ ] Book a test call on Calendly — confirm `call-booked` tag added in GHL
- [ ] Confirm Randy receives internal notification on call booking
- [ ] Check all emails land in inbox (run through mail-tester.com)

---

## NOTES FOR THE NEW SESSION

- Build everything inside Randy's existing GHL sub-account — do not create a new account
- The landing page URL must be on fedgovstartup.com (not a GHL subdomain)
- Randy updates webinar details via the GHL Admin Form — never manually in the page builder
- The n8n instance runs on Randy's existing VPS — get SSH access before starting
- All email from addresses should use @fedgovstartup.com (verified domain)
- Calendly is already set up — just need the webhook + API token
- Do not change Randy's existing Zoom account settings — only add the OAuth app

---

*Document prepared by BrndGuru | brndguruofficial@gmail.com*  
*Agent 1 — Webinar Funnel | Government Contracting Academy*
