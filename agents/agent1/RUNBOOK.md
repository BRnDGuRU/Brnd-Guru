# Agent 1 — Setup Runbook

Step-by-step for the parts that require logging into a platform (a code agent can't click these).
Do them roughly in this order. Credentials live in `credentials.env` (gitignored) — never in this file.

> Reminder: rotate any password that was shared in plaintext once setup is complete.

---

## §0 — Prerequisites checklist

- [ ] GHL sub-account exists for Government Contracting Academy (timezone = EST)
- [ ] GHL has a phone number with SMS enabled (Twilio via GHL)
- [ ] Access to SiteGround for fedgovstartup.com DNS
- [ ] Zoom account with a **Webinars** license (not just Meetings)
- [ ] Calendly account (booking page: `calendly.com/randy-wimmer/30-minute-zoom-mtg-with-randy`)
- [ ] A VPS for n8n + SSH access

---

## §1 — DNS for email deliverability (SiteGround)

SiteGround → Websites → fedgovstartup.com → cPanel → **Zone Editor**. Add:

| Type | Name | Value |
|------|------|-------|
| TXT | `@` | `v=spf1 include:sendgrid.net include:mailgun.org ~all` |
| TXT | `s1._domainkey` | *(GHL DKIM key 1 — from GHL Settings > Email Services)* |
| TXT | `s2._domainkey` | *(GHL DKIM key 2 — from GHL Settings > Email Services)* |
| CNAME | `em` | *(value GHL gives you in Email Services — often `sendgrid.net`)* |

> Get the exact DKIM/CNAME values from GHL first (§4 step "Add domain"), paste them here, then return to
> GHL and click **Verify**. SPF/DKIM are what keep emails out of spam — do not skip.

After adding, allow up to a few hours for propagation, then verify in GHL.

---

## §2 — GHL custom values (Settings → Custom Values → Add)

Create all of these (the first 4 are from the guide; the rest keep links reusable):

| Name | Used by |
|------|---------|
| `webinar_topic` | page, emails |
| `webinar_date` | page, emails |
| `webinar_time` | page, emails |
| `zoom_webinar_id` | n8n WF2 reference |
| `zoom_link` | confirmation/reminders |
| `lead_magnet_link` | confirmation email |
| `replay_link` | post-webinar emails |
| `calendly_link` | every CTA → `https://calendly.com/randy-wimmer/30-minute-zoom-mtg-with-randy` |
| `bootcamp_link` | urgency / no-show emails |

---

## §3 — Pipeline + tags

**Pipeline "Webinar Funnel"** (Settings → Pipelines), stages in order:
1. Registered  2. Reminder Sent  3. Attended  4. No-Show  5. Call Booked  6. Proposal Sent  7. Client Won  8. Client Lost

**Tags** (created automatically when first applied, or add under Settings → Tags):
`webinar-registered`, `webinar-attended`, `webinar-noshow`, `call-booked`, `unsubscribed`

---

## §4 — Email domain + sending identity (GHL)

1. GHL → Settings → **Email Services** → add/verify domain `fedgovstartup.com`.
2. Copy the DKIM + CNAME values it shows → add them in SiteGround (§1) → click **Verify**.
3. Set the default From: `Randy Wimmer <randy@fedgovstartup.com>` (or a verified mailbox).

---

## §5 — GHL workflows (Automation → Workflows)

> **Critical date-handling note:** GHL "wait until a date" steps need a *contact* date field, not a
> custom value. In **Workflow 1**, add a step that copies the current webinar date into a contact
> custom field (e.g. `webinar_date_dt`, type Date). Workflow 2's waits then anchor to that field.

**Workflow 1 — On Registration**
- Trigger: Form Submitted = registration form
- Actions: Add tag `webinar-registered` → set contact field `webinar_date_dt` = `{{custom_values.webinar_date}}`
  → move to pipeline stage **Registered** → send **Email 1 (Confirmation)** → Wait 2 min → send **SMS 1**.

**Workflow 2 — Pre-Webinar Nurture**
- Trigger: Tag added `webinar-registered`
- Actions (each is "Wait until …" then "Send"):
  - Wait until `[registration + 1 day]` → **Email 2 (Authority)**
  - Wait until `[webinar_date_dt − 3 days]` → **Email 3 (Agenda)** → move stage **Reminder Sent**
  - Wait until `[webinar_date_dt − 24h]` → **Email 4 (T-24h)**
  - Wait until `[webinar_date_dt − 3h]` → **Email 5 (T-3h)**
  - Wait until `[webinar_date_dt − 15min]` → **SMS 2**

**Workflow 3 — Post-Webinar Attended**
- Trigger: Tag added `webinar-attended`
- Actions: move stage **Attended** → **Email 6** → Wait 23h → **Email 7** → Wait 24h → **Email 8**

**Workflow 4 — Post-Webinar No-Show**
- Trigger: Tag added `webinar-noshow`
- Actions: move stage **No-Show** → **Email 9** → Wait 1d → **Email 10** → Wait 3d → **Email 11**
  → Wait 2d → **Email 12** → Wait 3d → **Email 13**

**Workflow 5 — Call Booked**
- Trigger: Tag added `call-booked`
- Actions: move stage **Call Booked** → send **Email 14 (internal)** to Randy → send **Email 15 (call prep)** to lead

Email/SMS bodies: copy from `emails/SEQUENCE.md`.

---

## §6 — GHL admin form (how Randy sets each webinar)

Build an internal form (Sites → Forms): fields = Webinar Topic (text), Webinar Date (date),
Webinar Time (dropdown of EST times), Zoom Webinar ID (text), Zoom Link (text).
On submit → **Webhook** action → POST to `https://<n8n>/webhook/update-webinar` (n8n WF1).

---

## §7 — Zoom Server-to-Server OAuth app

marketplace.zoom.us → Develop → **Build App** → **Server-to-Server OAuth**.
- Note **Account ID**, **Client ID**, **Client Secret** → put in `credentials.env`.
- **Scopes:**
  - `webinar:read:admin`
  - `webinar:read:list_absentees:admin`
  - `report:read:admin`  ← required for the attendee participants report used in WF2
- **Event Subscriptions:** add event **Webinar → Webinar Ended (`webinar.ended`)**;
  Event notification endpoint URL = `https://<n8n>/webhook/zoom-webinar-ended`.
  Copy the **Secret Token** → `ZOOM_WEBHOOK_SECRET_TOKEN` (WF2 uses it for the CRC validation).
- Activate the app.

---

## §8 — Calendly webhook + token

- Calendly → Integrations → **API & Webhooks** → create a **Personal Access Token** → `CALENDLY_PAT`.
- Create a **Webhook subscription**: event `invitee.created`, URL = `https://<n8n>/webhook/calendly`.

---

## §9 — Deploy n8n + import workflows

On the VPS (Docker is simplest):
```bash
docker volume create n8n_data
docker run -d --restart unless-stopped --name n8n -p 5678:5678 \
  -e N8N_HOST="n8n.fedgovstartup.com" -e N8N_PROTOCOL="https" \
  -e WEBHOOK_URL="https://n8n.fedgovstartup.com/" \
  -e GHL_LOCATION_ID="<your location id>" \
  -e ZOOM_ACCOUNT_ID="<...>" -e ZOOM_WEBHOOK_SECRET_TOKEN="<...>" \
  -v n8n_data:/home/node/.n8n docker.n8n.io/n8nio/n8n
```
Put n8n behind HTTPS (Caddy/Nginx + Let's Encrypt) — Zoom and Calendly require a valid TLS endpoint.

**In the n8n UI:**
1. Create credentials:
   - **HTTP Header Auth** named `GHL API Key (Bearer)` → header `Authorization` = `Bearer <GHL_API_KEY>`.
   - **HTTP Basic Auth** named `Zoom Client (Basic id:secret)` → user = Client ID, password = Client Secret.
2. Import the 3 JSON files from `n8n/` (Workflows → Import from File).
3. Confirm each HTTP node picked up the credential and that env vars resolve.
4. Activate all 3 workflows. Copy the production webhook URLs back into GHL (§6), Zoom (§7), Calendly (§8).

---

## §10 — GHL API key (for the n8n credential)

GHL → Settings → **Business Profile / API Keys** (or Integrations → Private Integrations on newer
sub-accounts) → create a key with contacts + custom values scopes → paste into `credentials.env`
as `GHL_API_KEY`, then build the n8n credential in §9. Also grab the **Location ID** (Settings →
Business Info / URL) for `GHL_LOCATION_ID`.

---

When §1–§10 are done, run `TESTING.md` end to end before pointing traffic at the page.
