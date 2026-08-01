# AGENT 2 — PAID ACQUISITION OPTIMIZATION AGENT
## Complete Build Guide for New Claude Session

---

## WHO YOU ARE WORKING WITH

**Client:** Randy Wimmer
**Business:** Government Contracting Academy / ISO Certification Group
**Goal:** Help government contractors get certified for ISO standards
**Product:** ISO certification coaching / consulting
**Account Manager:** Shivanshu / BrndGuru (brndguruofficial@gmail.com)

**IMPORTANT CONTEXT — Agent 1 already exists (or is being built alongside):**
Agent 1 handles the webinar funnel (registration -> reminders -> follow-up -> call booking).
Agent 2 is the traffic engine that feeds people INTO Agent 1.
They are tightly connected — Agent 1 feeds no-show data back to Agent 2 for retargeting.

---

## WHAT YOU ARE BUILDING

**Agent 2 = Paid Acquisition Optimization Agent**

A fully automated LinkedIn advertising system that:
1. Detects when Randy creates a new Zoom webinar
2. Auto-generates 5 AI video ad creatives (no designer needed)
3. Launches LinkedIn ad campaigns automatically
4. Optimizes spend weekly (pauses underperformers, boosts winners)
5. Retargets no-shows, website visitors, and past attendees
6. Reports weekly to Randy and pushes data to Agent 6 dashboard

Randy only does ONE thing: create the Zoom webinar.
Everything from ad creative to campaign launch to weekly optimization runs automatically.

**Key metrics to target:**
- Cost Per Registrant: under $15
- Cost Per Call Booking: under $150
- AI Videos generated per webinar cycle: 5
- Design agency cost: $0
- Time from Zoom webhook to ads live: ~8 minutes

---

## CREDENTIALS

Fill these in before using this document in a new session:

### LinkedIn Campaign Manager
- **Login URL:** https://www.linkedin.com/campaignmanager
- **Email:** [FILL IN - Randy's LinkedIn email]
- **Password:** [FILL IN]
- **Campaign Manager Account ID:** [FILL IN - found in URL after login: /campaignmanager/accounts/XXXXXXXXX]
- **LinkedIn Access Token (OAuth):** [FILL IN - generated via LinkedIn Developer App]
- **LinkedIn Client ID:** [FILL IN]
- **LinkedIn Client Secret:** [FILL IN]
- **Saved Audience ID (GovCon targeting):** [FILL IN - created during setup]

### Higgsfield AI
- **Website:** https://cloud.higgsfield.ai
- **Email:** [FILL IN]
- **Password:** [FILL IN]
- **API Key:** [FILL IN - found in dashboard under API section]
- **Plan:** Starter ($15/month)
- **Model to use:** kling-3.0
- **Credits per video:** 6 credits
- **Videos per webinar cycle:** 5 (= 30 credits per cycle)

### Anthropic (Claude AI API)
- **Dashboard:** https://console.anthropic.com
- **Email:** [FILL IN]
- **Password:** [FILL IN]
- **API Key:** [FILL IN - starts with sk-ant-...]
- **Model to use:** claude-sonnet-4-6 (or latest Sonnet for cost efficiency)
- **Cost per webinar cycle:** ~$1-2

### GoHighLevel (GHL) — same as Agent 1
- **Login URL:** https://app.gohighlevel.com
- **Email:** [FILL IN]
- **Password:** [FILL IN]
- **Sub-Account Name:** Government Contracting Academy
- **API Key (Location):** [FILL IN]
- **Location ID:** [FILL IN - found in Settings > Business Info > Location ID]

### Zoom — same as Agent 1
- **Login URL:** https://zoom.us
- **Email:** [FILL IN]
- **Password:** [FILL IN]
- **Client ID (OAuth App):** [FILL IN]
- **Client Secret:** [FILL IN]
- **Webhook Secret Token:** [FILL IN]
- **Webhook Events needed:**
  - webinar.created (fires when Randy creates a new webinar)
  - webinar.ended (fires when webinar ends — used by Agent 1)

### n8n (Self-Hosted on VPS) — same server as Agent 1
- **VPS Provider:** [FILL IN]
- **n8n URL:** [FILL IN - e.g. http://YOUR_VPS_IP:5678]
- **n8n Username:** [FILL IN]
- **n8n Password:** [FILL IN]
- **VPS Storage Path for creatives:** /home/creatives/{date}/ (create this folder)
- **SSH Access:** [FILL IN if direct VPS access needed for FFmpeg]

### Randy's Voiceover File
- **File name:** randy_voiceover.mp3
- **Location on VPS:** /home/assets/randy_voiceover.mp3
- **Content Randy must record:** "If you're a small business owner trying to win more government contracts, ISO certification is the fastest way to stand out in a crowded field. Join me for a free live webinar where I'll show you exactly how to get certified without the typical headaches."
- **Recording instructions:** Record on phone voice memo app or Zoom, send to BrndGuru, we upload to VPS.

### Background Music File
- **File name:** bg_music.mp3
- **Location on VPS:** /home/assets/bg_music.mp3
- **Source:** Royalty-free from pixabay.com or freesound.org (instrumental, professional tone)

---

## PLATFORM STACK

| Platform | Role | Cost |
|----------|------|------|
| LinkedIn Campaign Manager | Ad platform — campaigns, audiences, bidding, creative management | Ad spend only |
| GoHighLevel (GHL) | Landing page, CRM, lead capture, UTM tracking per contact | $97/mo (existing) |
| VPS + n8n | Automation brain — orchestrates everything | $250/2yrs (existing) |
| Higgsfield AI | AI video generation — 5 cinematic B-roll clips per cycle | $15/mo |
| Claude AI API | Writes video prompts + ad copy from webinar topic | ~$1-2/cycle |
| FFmpeg (VPS) | Assembles final video ad — free, runs on VPS | $0 |

**Total new monthly cost for Agent 2:** ~$15-17/month + ad spend

---

## THE 5 PHASES

```
SETUP -> PRE-WEBINAR -> ADS RUNNING -> RETARGETING -> REPORTING
```

### Phase 1: SETUP (one-time, done by BrndGuru)
- LinkedIn Campaign Manager account configured
- Audience targeting defined (GovCon founders, 1-50 employees, SAM.gov registered keywords)
- LinkedIn Insight Tag pixel installed on GHL landing page
- VPS connections wired: Higgsfield API, Claude API, LinkedIn API, FFmpeg
- Randy records 60-sec voiceover (once, reused across all ad cycles)
- 3 saved audiences created in LinkedIn: Warm (past visitors), Cold (new targeting), Lookalike

### Phase 2: PRE-WEBINAR (auto-triggered by Zoom webhook)
- Randy creates a new Zoom Webinar (only action needed)
- n8n receives Zoom `webinar.created` webhook
- GHL landing page updated automatically (Custom Values)
- Claude AI writes 5 video prompts
- Higgsfield generates 5 B-roll clips (~8 min)
- FFmpeg assembles final 60-sec video ad
- LinkedIn campaign launched with targeting + budget
- Total: ~8 minutes, zero manual work

### Phase 3: ADS RUNNING (ongoing, every 7 days)
- Campaigns run live on LinkedIn
- LinkedIn Insight Tag tracks every registration back to exact ad
- n8n weekly optimization job:
  - Pull ad analytics from LinkedIn API
  - Pause creatives with CTR below 0.3%
  - Shift budget to top-performing creatives
  - Alert BrndGuru if CPL exceeds $20

### Phase 4: RETARGETING (auto-triggered by Agent 1 tags)
- When Agent 1 tags a lead as `webinar-noshow` -> GHL webhook -> n8n -> LinkedIn audience updated
- Three retargeting audiences:
  1. **No-Show audience:** "You missed it — here's the next date" ad
  2. **Website visitor audience:** Captured by Insight Tag pixel — shown registration ads
  3. **Past attendee audience:** "Didn't book a call? Here's your free slot" ad
- Lookalike audience: built when GHL contacts exceed 300 (2% similarity match)

### Phase 5: REPORTING (every Monday 8AM)
- n8n pulls LinkedIn Ads API data + GHL pipeline data
- Formats weekly performance report
- Pushes metrics to Agent 6 dashboard (Google Sheets/Looker Studio)
- Emails Randy + BrndGuru with:
  - Total ad spend
  - Registrations from LinkedIn
  - Cost per registrant (CPL)
  - Calls booked from LinkedIn traffic
  - Revenue attributed to LinkedIn

---

## THE AI CREATIVE PIPELINE (Step by Step)

```
ZOOM WEBHOOK -> n8n -> CLAUDE AI -> HIGGSFIELD API -> FFmpeg -> LINKEDIN ADS
    (Step 1)   (Step 2)  (Step 3)     (Step 4+5)      (Step 6)   (goes live)
```

### Step 1: Zoom Webhook
- Randy creates a new Zoom Webinar
- Zoom fires POST webhook to n8n webhook URL
- Payload includes: webinar topic, date, time, webinar ID
- n8n stores these values in workflow context

### Step 2: n8n Orchestration
- n8n receives webhook
- Fires two parallel branches:
  - Branch A: Update GHL Custom Values (landing page)
  - Branch B: Start AI creative generation (Steps 3-6)

### Step 3: Claude AI — Prompt Writing
- n8n sends HTTP POST to Claude API
- Prompt template:
  ```
  Write 5 Higgsfield AI video prompts for a LinkedIn ad promoting a free webinar titled:
  "[WEBINAR_TOPIC]"
  Target audience: Small business owners who are federal contractors seeking ISO certification.
  Each prompt should use a different creative angle:
  1. Authority (expert/credibility angle)
  2. Pain point (problem awareness)
  3. Social proof (results/transformation)
  4. Urgency (limited spots/time)
  5. Curiosity (surprising insight/hook)
  Format each prompt as: cinematic B-roll, 8 seconds, 16:9 aspect ratio, professional business setting.
  ```
- Claude returns 5 structured prompts, one per angle

### Step 4: Higgsfield API — Video Generation (5 parallel calls)
- n8n sends 5 POST requests simultaneously to Higgsfield API
- **Endpoint:** POST https://cloud.higgsfield.ai/api/v1/generate/video
- **Payload per request:**
  ```json
  {
    "model": "kling-3.0",
    "prompt": "[Claude's prompt for angle X]",
    "duration": 8,
    "aspect_ratio": "16:9",
    "quality": "standard"
  }
  ```
- Each request returns a `job_id`

### Step 5: Poll Until Complete
- n8n polls every 30 seconds: GET /api/v1/jobs/{job_id}
- Waits for all 5 jobs to return `status: "complete"`
- Downloads all 5 .mp4 files to VPS: /home/creatives/{webinar_date}/clip_1.mp4 ... clip_5.mp4

### Step 6: FFmpeg Assembly
- n8n runs FFmpeg command on VPS via SSH Execute node
- FFmpeg command builds final 60-second video ad:
  ```bash
  # Step 1: Concatenate best 3 clips (use clips 1, 3, 5 by default)
  ffmpeg -i clip_1.mp4 -i clip_3.mp4 -i clip_5.mp4 \
    -filter_complex "[0:v][1:v][2:v]concat=n=3:v=1[outv]" \
    -map "[outv]" combined.mp4

  # Step 2: Add title overlay text
  ffmpeg -i combined.mp4 \
    -vf "drawtext=text='[WEBINAR_TOPIC]':fontsize=36:fontcolor=white:x=(w-text_w)/2:y=h-100:box=1:boxcolor=black@0.5" \
    with_title.mp4

  # Step 3: Add voiceover + background music
  ffmpeg -i with_title.mp4 -i /home/assets/randy_voiceover.mp3 -i /home/assets/bg_music.mp3 \
    -filter_complex "[2:a]volume=0.15[bg];[1:a][bg]amix=inputs=2[aout]" \
    -map 0:v -map "[aout]" \
    -t 60 ad_final.mp4

  # Step 4: Add captions (optional - use auto-subtitle tools or hardcode)
  # Output: /home/creatives/{date}/ad_final.mp4
  ```

### Step 7: LinkedIn Ad Launch
- n8n uploads video to LinkedIn:
  1. POST /v2/assets — upload video, get asset URN
  2. POST /v2/adCreativesV2 — create creative with video asset
  3. POST /v2/adCampaigns — create campaign with targeting
  4. POST /v2/adCampaignGroups — assign to campaign group

---

## THE FULL TRIGGER MAP (T1-T11)

| # | Trigger | Tool | Action | Fires Next |
|---|---------|------|--------|-----------|
| T1 | Zoom webhook: webinar.created | n8n (VPS) | Extract topic, date, time, zoom_id from payload | T2 + T3 in parallel |
| T2 | n8n: landing page update | GoHighLevel | PATCH GHL custom values: all 4 fields updated | Agent 1 date triggers updated |
| T3 | n8n: creative generation | Claude API | POST Claude: write 5 video prompts for webinar topic | T4 (5 parallel) |
| T4 | Claude returns 5 prompts | Higgsfield | POST /api/v1/generate/video x5 (parallel), model: kling-3.0 | T5 (poll loop) |
| T5 | Poll: video status check | Higgsfield | GET /api/v1/jobs/{id} every 30s until complete, download 5 .mp4 files | T6 (FFmpeg) |
| T6 | Videos on VPS ready | FFmpeg (VPS) | Assemble: clips + title + voiceover + music = ad_final.mp4 | T7 (LinkedIn) |
| T7 | Final video assembled | LinkedIn API | Upload video, create creative, launch campaign with targeting | Ads live (~8 min total) |
| T8 | Schedule: every 7 days | n8n Schedule | Pull ad analytics, pause CTR <0.3%, alert if CPL >$20 | Data to Agent 6 |
| T9 | GHL tag: webinar-noshow (Agent 1) | n8n (VPS) | Add email to LinkedIn No-Show matched audience, fire retargeting campaign | LinkedIn retargeting ads |
| T10 | GHL contacts >300 milestone | n8n (VPS) | Export GHL contacts, POST to LinkedIn matched audiences, build lookalike (2%) | New lookalike campaign |
| T11 | Schedule: Monday 8AM | n8n Schedule | Pull LinkedIn + GHL data, format report, email Randy + BrndGuru | Agent 6 dashboard updated |

---

## LINKEDIN CAMPAIGN STRUCTURE

### Campaign Group: GovCon Academy — Webinar Ads
**Budget strategy:** Start $10/day per campaign, optimize after 7 days

### Campaign 1: Cold Audience — New Registrations
- **Objective:** Website Conversions (registration form submit)
- **Audience targeting:**
  - Job titles: Business Owner, Founder, CEO, President, Managing Director
  - Company size: 1-50 employees
  - Industries: Government Administration, Defense, Construction, IT Services
  - Keywords: federal contractor, government contract, SAM.gov, SDVOSB, 8(a)
  - Location: United States
- **Ad formats:** Single video ad (AI-generated) + 2-3 static image ads
- **Conversion tracking:** LinkedIn Insight Tag pixel on GHL /webinar page, track "Thank You" page load

### Campaign 2: Warm Audience — Website Visitors
- **Objective:** Website Conversions
- **Audience:** Matched audience — people who visited fedgovstartup.com (Insight Tag retargeting)
- **Exclusion:** People who already submitted the registration form
- **Ad creative:** Different message — "Still thinking about it? Spots are limited."

### Campaign 3: No-Show Retargeting
- **Objective:** Website Conversions
- **Audience:** Matched audience — emails from GHL no-show list (updated by n8n after each webinar)
- **Ad creative:** "You missed the last one — here's the next date"
- **Budget:** $5/day (smaller, warm audience)

### Campaign 4: Lookalike — Scales After 300 Contacts
- **Objective:** Website Conversions
- **Audience:** 2% Lookalike based on GHL registered contact list
- **Turns on:** When GHL has 300+ contacts (triggered automatically by T10)

### LinkedIn Insight Tag Setup
1. Log in to LinkedIn Campaign Manager
2. Account Assets > Insight Tag
3. Copy the JavaScript snippet
4. In GHL Landing Page builder: add custom code block, paste Insight Tag
5. Create conversion: name "Webinar Registration", trigger: "Thank You" page URL
6. Wait 24hrs for verification

---

## n8n WORKFLOWS TO BUILD

### Workflow 1: Creative Generation Pipeline
**Trigger:** Webhook node (receives Zoom webinar.created event)
**Node sequence:**
```
Webhook -> Set (extract fields) -> Split (2 branches)
  Branch A: HTTP Request -> GHL API (update Custom Values x4)
  Branch B:
    -> HTTP Request -> Claude API (write 5 prompts)
    -> Split In Batches (5 items)
      -> HTTP Request -> Higgsfield API POST /generate/video (get job_ids)
    -> Wait (30 sec)
    -> Loop: HTTP Request -> Higgsfield GET /jobs/{id} (check status)
      -> IF (all complete?) -> Continue
      -> ELSE -> Wait 30s -> Loop again
    -> HTTP Request (download each .mp4 to VPS)
    -> Execute Command node (run FFmpeg assembly script)
    -> HTTP Request -> LinkedIn API (upload video + launch campaign)
    -> Send Email (notify BrndGuru: "Ads live for [topic]")
```

### Workflow 2: Weekly Ad Optimization
**Trigger:** Schedule node — every Monday at 7AM EST
**Node sequence:**
```
Schedule -> HTTP Request -> LinkedIn Ads Analytics API
  -> IF (any creative CTR < 0.3%)
      -> HTTP Request -> LinkedIn API PATCH (pause creative)
  -> IF (any campaign CPL > $20)
      -> Send Email -> BrndGuru alert
  -> HTTP Request -> GHL API (get pipeline data: calls booked, revenue)
  -> Code node (format report data)
  -> HTTP Request -> Google Sheets API (push to Agent 6 sheet)
  -> Send Email -> Randy + BrndGuru (weekly report)
```

### Workflow 3: No-Show Retargeting
**Trigger:** Webhook node (GHL fires when `webinar-noshow` tag added)
**Node sequence:**
```
Webhook -> Extract (contact email, name)
  -> HTTP Request -> LinkedIn API POST /v2/dmpSegments
     (add email to "No-Show Audience" matched audience)
  -> IF (retargeting campaign paused?) -> Resume campaign
  -> Set node (log: contact added to retargeting)
```

### Workflow 4: Lookalike Audience Builder
**Trigger:** Webhook (GHL fires when contact count reaches 300)
**Node sequence:**
```
Webhook -> HTTP Request -> GHL API (export all contacts: email list)
  -> Code node (format as CSV)
  -> HTTP Request -> LinkedIn API (POST matched audience with CSV)
  -> Wait 24hrs (LinkedIn needs time to process)
  -> HTTP Request -> LinkedIn API (create lookalike campaign)
  -> Send Email -> BrndGuru: "Lookalike campaign created"
```

---

## LINKEDIN API REFERENCE

**Base URL:** https://api.linkedin.com/v2/

**Auth:** OAuth 2.0 Bearer Token
**Header:** `Authorization: Bearer {ACCESS_TOKEN}`

**Key endpoints:**

| Action | Method | Endpoint |
|--------|--------|----------|
| Upload video | POST | /assets?action=registerUpload |
| Create ad creative | POST | /adCreativesV2 |
| Create campaign | POST | /adCampaigns |
| Get ad analytics | GET | /adAnalytics |
| Create matched audience | POST | /dmpSegments |
| Add to matched audience | POST | /dmpSegments/{id}/users |

**LinkedIn OAuth Scopes needed:**
- r_ads
- w_organization_social
- rw_ads
- r_basicprofile

**Getting Access Token:**
1. Go to LinkedIn Developer Portal: developers.linkedin.com
2. Create app -> Products: Marketing Developer Platform
3. OAuth 2.0 settings -> Redirect URL: http://YOUR_VPS_IP:5678/oauth2-callback
4. Copy Client ID + Client Secret
5. In n8n: Credentials -> LinkedIn OAuth2 API -> paste Client ID + Secret -> authorize

---

## HIGGSFIELD API REFERENCE

**Base URL:** https://cloud.higgsfield.ai/api/v1/

**Auth:** API Key in header: `Authorization: Bearer {HIGGSFIELD_API_KEY}`

**Generate video:**
```
POST /api/v1/generate/video
{
  "model": "kling-3.0",
  "prompt": "Cinematic B-roll of a small business owner reviewing government contract documents in a professional office. 8 seconds. Confident, professional atmosphere.",
  "duration": 8,
  "aspect_ratio": "16:9"
}
Response: {"job_id": "abc123", "status": "queued"}
```

**Check job status:**
```
GET /api/v1/jobs/{job_id}
Response: {"status": "complete", "video_url": "https://..."}
```

**Download video:**
```
GET video_url -> save to VPS as clip_1.mp4
```

**Credits:** 6 credits per 8-second Kling 3.0 video. Starter plan = enough for several webinar cycles.

---

## CLAUDE API REFERENCE

**Base URL:** https://api.anthropic.com/v1/

**Auth:** Header: `x-api-key: {ANTHROPIC_API_KEY}` + `anthropic-version: 2023-06-01`

**Generate prompts:**
```
POST /messages
{
  "model": "claude-sonnet-4-6",
  "max_tokens": 1024,
  "messages": [{
    "role": "user",
    "content": "Write 5 Higgsfield AI video prompts for a LinkedIn ad for a webinar titled: [TOPIC]. Target: US federal contractors seeking ISO certification. Angles: authority, pain point, social proof, urgency, curiosity. Each prompt: cinematic B-roll, 8 seconds, 16:9, professional business setting."
  }]
}
```

---

## GHL SETUP FOR AGENT 2

### UTM Tracking on Landing Page
Every LinkedIn ad URL must include UTM parameters:
- `utm_source=linkedin`
- `utm_medium=paid`
- `utm_campaign=webinar-[DATE]`
- `utm_content=[ad-creative-id]`

In GHL: Settings -> Custom Fields -> Add:
- `utm_source`
- `utm_medium`
- `utm_campaign`
- `utm_content`

In GHL Landing Page: add hidden form fields for each UTM parameter using `{{request.query.utm_source}}` etc.

### LinkedIn Insight Tag in GHL
1. Open GHL landing page in builder
2. Add Custom Code block in page header
3. Paste LinkedIn Insight Tag JavaScript
4. Publish page
5. In LinkedIn Campaign Manager -> Insight Tag -> verify tag fires (use LinkedIn Tag Helper Chrome extension)

### GHL Workflow: No-Show -> LinkedIn Trigger
- Trigger: Contact tag added = `webinar-noshow`
- Action: Send webhook POST to n8n URL
- Payload: `{"email": "{{contact.email}}", "name": "{{contact.name}}", "event": "noshow"}`

---

## VOICEOVER SCRIPT (Randy Records Once)

**Script for Randy to record:**
```
"Hey, if you're a small business owner trying to win more government contracts,
ISO certification is one of the fastest ways to stand out from the competition.
Most contractors don't have it — and that means you can jump ahead of them.
I'm Randy Wimmer, and I'm hosting a free live webinar where I'll walk you through
exactly how to get ISO certified without the expensive consultants and the paperwork nightmare.
Join me — link in the bio. Spots are limited."
```

**Recording instructions:**
- Use phone voice memo OR record a Zoom solo session
- Speak clearly, professional tone, no background noise
- Total length: ~45-60 seconds
- Save as MP3, send to BrndGuru
- BrndGuru uploads to VPS as: /home/assets/randy_voiceover.mp3

---

## BUILD SEQUENCE (What to Build in What Order)

### Week 1 — Accounts + Setup
1. Create LinkedIn Campaign Manager account (Randy does this — needs billing card)
2. Create LinkedIn Developer App, get Client ID + Client Secret
3. Set up LinkedIn OAuth in n8n (same VPS as Agent 1)
4. Install LinkedIn Insight Tag on GHL landing page
5. Create Higgsfield account, get API key, test with one prompt
6. Get Anthropic (Claude) API key, test one API call

### Week 2 — Creative Pipeline
7. Build n8n Workflow 1: Zoom webhook -> Claude -> Higgsfield -> poll -> download
8. Install FFmpeg on VPS: `sudo apt install ffmpeg`
9. Upload Randy's voiceover + background music to VPS /home/assets/
10. Write + test FFmpeg assembly script
11. Test full pipeline end-to-end with a test webinar topic
12. Build n8n LinkedIn upload nodes (upload video, create creative)

### Week 3 — Campaigns + Retargeting
13. Create LinkedIn saved audiences (Cold, Warm, No-Show)
14. Build LinkedIn campaign structure in Campaign Manager
15. Build n8n campaign launch automation (T7 nodes)
16. Build GHL Workflow: no-show tag -> webhook to n8n
17. Build n8n Workflow 3: No-Show retargeting (T9)
18. Add UTM custom fields to GHL + update landing page form

### Week 4 — Optimization + Reporting
19. Build n8n Workflow 2: Weekly optimization job (T8)
20. Build n8n Workflow 4: Lookalike builder (T10)
21. Build n8n Workflow: Monday reporting (T11)
22. Connect to Google Sheets for Agent 6 dashboard data
23. Full end-to-end test: create Zoom webinar -> watch all 6 steps fire -> confirm ads live

---

## TESTING CHECKLIST

Before going live:
- [ ] Create test Zoom webinar -> confirm n8n webhook receives payload
- [ ] Confirm Claude API returns 5 prompts in correct format
- [ ] Confirm Higgsfield generates 1 test video (test with single call before running 5)
- [ ] Confirm FFmpeg assembles video correctly (check output file plays properly)
- [ ] Confirm LinkedIn video upload succeeds (check Campaign Manager -> Assets)
- [ ] Confirm campaign launches with correct targeting (check Campaign Manager -> Campaigns)
- [ ] Register on landing page via LinkedIn ad -> confirm UTM fields stored in GHL
- [ ] Tag a test contact as `webinar-noshow` -> confirm n8n fires -> LinkedIn audience updated
- [ ] Run weekly optimization manually -> confirm analytics pulled correctly
- [ ] Confirm Monday report email arrives with correct data

---

## IMPORTANT NOTES FOR NEW SESSION

- The VPS is SHARED with Agent 1's n8n — do not reinstall or reset n8n
- Randy's voiceover is recorded ONCE and reused across every webinar cycle
- LinkedIn ads budget: Randy approves, BrndGuru manages — start conservative ($10/day)
- Do not create new LinkedIn profiles — use Randy's existing LinkedIn account
- The Higgsfield Starter plan ($15/mo) should cover multiple webinar cycles — monitor credits
- Zoom webhook `webinar.created` fires both Agent 1 (update landing page) and Agent 2 (creative generation) — they share the same webhook endpoint in n8n, which then branches
- All API keys go into n8n Credentials panel — never hardcode in workflow nodes
- Agent 6 dashboard: push data to a Google Sheet named "BrndGuru - Randy Wimmer - Agent 2 Data" — Agent 6 reads from this sheet

---

*Document prepared by BrndGuru | brndguruofficial@gmail.com*
*Agent 2 — Paid Acquisition | Government Contracting Academy*
