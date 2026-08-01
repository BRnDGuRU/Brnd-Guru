# AGENT 6 — REVENUE CONTROL TOWER DASHBOARD
## Complete Build Guide for New Claude Session

---

## WHO YOU ARE WORKING WITH

**Client:** Randy Wimmer
**Business:** Government Contracting Academy / ISO Certification Group
**Goal:** Help government contractors get certified for ISO standards
**Product:** ISO certification coaching / consulting
**Account Manager:** Shivanshu / BrndGuru (brndguruofficial@gmail.com)

**IMPORTANT CONTEXT — Agents 1-5 exist (or are being built):**
Agent 6 is the command center that reads data FROM all other agents.
It does not run any outreach or automation itself.
It only collects, formats, and displays data in one live dashboard.
Build Agent 6 alongside or after the other agents start producing data.

---

## WHAT YOU ARE BUILDING

**Agent 6 = Revenue Control Tower Dashboard**

A single live URL that Randy opens to see the health of his entire revenue system:
- Every agent's KPIs in one place
- No platform switching (no logging into GHL, LinkedIn, Zoom separately)
- No manual number pulling
- Updates automatically on schedule (n8n writes data, Looker Studio displays it)
- Buying Intent scoring — tells Randy exactly who is ready to buy RIGHT NOW

**Total cost to build: $0**
- Google Looker Studio: FREE
- Google Sheets: FREE
- n8n: already on VPS (existing)
- GHL: already subscribed ($97/mo covers this)

---

## CREDENTIALS

Fill these in before using this document in a new session:

### Google Account (for Sheets + Looker Studio)
- **Google Account Email:** [FILL IN - BrndGuru Gmail or Randy's Gmail]
- **Google Sheets URL:** [FILL IN after creating the sheet]
- **Looker Studio URL:** [FILL IN after creating the dashboard]
- **Google Service Account JSON:** [FILL IN - needed for n8n to write to Sheets via API]
  - Create at: console.cloud.google.com -> Service Accounts -> Create Key -> JSON

### GoHighLevel (GHL)
- **Login URL:** https://app.gohighlevel.com
- **Email:** [FILL IN]
- **Password:** [FILL IN]
- **API Key (Location):** [FILL IN]
- **Location ID:** [FILL IN]
- **Webhook URL base:** https://services.leadconnectorhq.com

### Zoom
- **Client ID:** [FILL IN]
- **Client Secret:** [FILL IN]
- **Account ID:** [FILL IN]
- **Base URL:** https://api.zoom.us/v2

### LinkedIn Ads (for Agent 2 data)
- **Access Token:** [FILL IN]
- **Campaign Manager Account ID:** [FILL IN]
- **Base URL:** https://api.linkedin.com/v2

### HeyReach (for Agent 4 data)
- **Login URL:** https://app.heyreach.io
- **Email:** [FILL IN]
- **Password:** [FILL IN]
- **API Key:** [FILL IN - found in HeyReach Settings > API]

### Calendly
- **API Token:** [FILL IN]
- **Base URL:** https://api.calendly.com
- **Randy's Booking URL:** [FILL IN]

### n8n (Self-Hosted on VPS)
- **n8n URL:** [FILL IN - e.g. http://YOUR_VPS_IP:5678]
- **n8n Username:** [FILL IN]
- **n8n Password:** [FILL IN]
- **Same VPS as Agents 1 and 2 - do not reinstall**

### Slack (for instant alerts - optional but recommended)
- **Workspace:** [FILL IN]
- **Bot Token:** [FILL IN - starts with xoxb-]
- **Channel for alerts:** [FILL IN - e.g. #randy-alerts]

---

## PLATFORM STACK

| Platform | Role | Cost |
|----------|------|------|
| Google Sheets | Central data store - n8n writes all metrics here | FREE |
| Google Looker Studio | Live dashboard - reads from Google Sheets, displays to Randy | FREE |
| n8n (VPS) | Pulls data from all APIs on schedule, writes to Sheets | FREE (existing) |
| GHL | Source for pipeline, email, call, contact data | $97/mo (existing) |
| Zoom API | Source for webinar attendance data | Existing |
| LinkedIn Ads API | Source for ad spend, CPL, creative performance | Existing |
| HeyReach API | Source for outreach stats, DM replies, acceptance rate | Existing |
| Calendly API | Source for call bookings | Free tier (existing) |

---

## GOOGLE SHEETS STRUCTURE

### Create one Google Sheet named:
**"BrndGuru - Randy Wimmer - Dashboard Data"**

### Tabs to create (one per agent + extras):

**Tab 1: Agent1_Data**
Columns: Date | Registrations | ShowUpRate | NoShowRate | CallsBooked | ReplayOpens | EmailsSent | SMSDelivered | NextWebinarDate | CycleID

**Tab 2: Agent2_Data**
Columns: Date | AdSpend | Impressions | Clicks | CTR | Registrations | CostPerReg | CostPerCall | ActiveCampaigns | BestCreative | RetargetingSize

**Tab 3: Agent3_Data**
Columns: Date | PostsPublished | TotalImpressions | AvgEngRate | TopPost | CommentsReceived | ProfileViews | FollowersGained | OrganicSignUps | NewsletterSubs

**Tab 4: Agent4_Data**
Columns: Date | RequestsSent | AcceptanceRate | ConnectionsMade | DMsSent | DMReplyRate | ActiveConvos | CallsBooked | HeyReachStatus | TopICPReached

**Tab 5: Agent5_Data**
Columns: Date | EmailsSent | UniqueOpens | OpenRate | ClickRate | Replies | Unsubscribes | LeadsReactivated | CallsBooked | Deliverability

**Tab 6: Revenue_Pipeline**
Columns: Date | PipelineValue | ActiveProspects | ProposalsSent | DealsWon | RevenueClosed | AttributionAds | AttributionOrganic | AttributionEmail | AttributionDMs | AvgDealValue | CloseRate | RevenueForecast

**Tab 7: Email_Marketing**
Columns: Date | TotalListSize | WeeklyGrowth | ActiveSequences | BestSequence | ConfirmSeqOpenRate | NurtureSeqOpenRate | PostWebinarSeqOpenRate | ReactivationSeqOpenRate | Deliverability | BounceRate | UnsubscribeRate | RevenueAttributed | ListHealthScore

**Tab 8: Intent_Scores**
Columns: ContactID | ContactEmail | ContactName | IntentScore | Tier | LastSignal | LastSignalDate | DaysToIntent | LastUpdated

**Tab 9: Top5_Hot_Leads**
Columns: Rank | Name | Email | IntentScore | LastAction | LastActionDate | Phone | DaysInFunnel

**Tab 10: Summary**
Columns: Date | TotalPipelineValue | CallsBookedWeek | RevenueClosedMonth | ActiveLeads | AvgIntentScore | HotLeadsCount | WarmLeadsCount

---

## GOOGLE LOOKER STUDIO DASHBOARD STRUCTURE

### Dashboard URL: Share this one URL with Randy
Create at: lookerstudio.google.com -> Create -> Report -> Connect to Google Sheets

### Page 1: Command Center (All 6 Agents at a Glance)

**Top summary bar - 4 scorecards:**
- Total Pipeline Value (from Revenue_Pipeline tab)
- Calls Booked This Week (sum across all agents)
- Revenue Closed This Month (from Revenue_Pipeline)
- Active Leads (from Summary tab)

**2x3 grid of agent mini-panels (one per agent):**
Each panel shows:
- Agent number + name (color-coded)
- 3 KPI scorecards

| Agent | Color | KPI 1 | KPI 2 | KPI 3 |
|-------|-------|-------|-------|-------|
| 1 - Webinar Funnel | #FF6600 | Registrations | Show-Up Rate | Calls Booked |
| 2 - Paid Acquisition | #2980b9 | Ad Spend | Cost Per Reg | Calls from Ads |
| 3 - Content Authority | #8e44ad | Posts Live | Impressions | Organic Sign-Ups |
| 4 - LinkedIn Outreach | #ea4b71 | Requests Sent | Accept Rate | Calls from DMs |
| 5 - Email Nurture | #16a085 | Emails Sent | Open Rate | Calls from Email |
| 6 - Revenue Pipeline | #1e3a5f | Pipeline Value | Proposals Out | Revenue Closed |

### Page 2: Agent 1 Panel - Webinar Funnel

**Top 4 scorecards:** Registrations | Show-Up Rate | Calls Booked | No-Show Rate

**Table with full detail metrics:**
- Registrations: total people registered (current cycle)
- Show-Up Rate: % of registrants who attended live
- No-Show Rate: % who missed (auto-enter replay sequence)
- Calls Booked: total Calendly bookings from webinar funnel
- Replay Opens: no-shows who opened + watched replay email
- Emails Sent: total automated emails fired this cycle
- SMS Delivered: confirmation + reminder SMS delivered
- Next Webinar: scheduled date + days remaining
- Sequence Status: which automation steps fired vs pending

**Data source:** Agent1_Data tab

### Page 3: Agent 2 Panel - Paid Acquisition

**Top 4 scorecards:** Ad Spend | Cost Per Reg | CTR | Calls from Ads

**Table with full detail metrics:**
- Ad Spend: total LinkedIn spend this cycle
- Impressions: total times ads were shown
- Clicks: link clicks to GHL landing page
- Click-Through Rate: benchmark 0.4%+
- Registrations: people who registered after clicking ad
- Cost Per Registrant: target under $15
- Cost Per Call: target under $150
- Active Campaigns: number of live LinkedIn campaigns
- Best Creative: top performing ad by CTR this cycle
- Retargeting Size: no-show + website visitor audience size

**Data source:** Agent2_Data tab

### Page 4: Agent 3 Panel - LinkedIn Content

**Top 4 scorecards:** Posts Published | Total Impressions | Engagement Rate | Organic Sign-Ups

**Table with full detail metrics:**
- Posts Published: total LinkedIn posts this week
- Total Impressions: combined reach across all posts
- Avg Engagement Rate: target 3%+
- Top Post: best performing post by impressions
- Comments Received: total comments (engagement signal)
- Profile Views: views on Randy's LinkedIn profile this week
- Followers Gained: net new followers added
- Organic Sign-Ups: webinar registrations from organic content
- Newsletter Subscribers: LinkedIn newsletter count

**Data source:** Agent3_Data tab

### Page 5: Agent 4 Panel - LinkedIn Outreach

**Top 4 scorecards:** Requests Sent | Accept Rate | DM Replies | Calls Booked

**Table with full detail metrics:**
- Requests Sent: connection requests via HeyReach this week
- Acceptance Rate: target 25-40% for GovCon audience
- Connections Made: total new connections added
- DM Sequence Sent: follow-up DMs to accepted connections
- DM Reply Rate: target 15%+
- Active Conversations: ongoing LinkedIn inbox threads
- Calls Booked: Calendly bookings from DM conversations
- HeyReach Status: current campaign status + daily send count
- Top ICP Reached: most common job titles in new connections

**Data source:** Agent4_Data tab

### Page 6: Agent 5 Panel - Email Nurture

**Top 4 scorecards:** Emails Sent | Open Rate | Click Rate | Calls from Email

**Table with full detail metrics:**
- Emails Sent: total across all active sequences this week
- Unique Opens: individual contacts who opened
- Open Rate: target 30-45% for GovCon niche
- Click Rate: clicks/opens, measures CTA effectiveness
- Replies Received: contacts who replied to nurture emails
- Unsubscribes: healthy rate under 0.5%
- Leads Reactivated: cold leads who re-engaged
- Calls Booked: Calendly bookings from email
- Deliverability: % landing in inbox vs spam, target 98%+

**Data source:** Agent5_Data tab

### Page 7: Revenue Pipeline - Full Funnel View

**Top 4 scorecards:** Pipeline Value | Proposals Out | Revenue Closed | Active Prospects

**Funnel visualization (bar chart, descending):**
Ad Impressions -> Clicks -> Registrations -> Attended -> Calls Booked -> Proposals -> Closed

**Attribution pie chart:**
Revenue by source: Ads / Organic / Email / DMs

**Table with full detail metrics:**
- Pipeline Value: total estimated value of active deals in GHL
- Active Prospects: leads with booked calls in active follow-up
- Proposals Sent: ISO certification proposals sent this month
- Deals Won: closed deals, new clients signed
- Revenue Closed: total revenue collected this month
- Attribution Split: revenue by channel
- Avg Deal Value: average ISO certification contract value
- Close Rate: target 20-35%
- Revenue Forecast: projected monthly from current pipeline

**Data source:** Revenue_Pipeline tab

### Page 8: Email Marketing Deep Dive

**Top 4 scorecards:** Total List Size | Weekly Growth | Avg Open Rate | Revenue from Email

**14 detailed rows:**

TEAL group (list health):
- Total List Size: all active contacts in at least one GHL sequence
- Weekly List Growth: new contacts added from all sources
- Active Sequences: number of sequences currently running
- Best Sequence: highest open-rate this cycle

NAVY group (per-sequence benchmarks):
- Confirmation Sequence open rate: benchmark 55%+
- Nurture Sequence open rate: benchmark 35%+
- Post-Webinar Sequence open rate: benchmark 40%+
- Reactivation Sequence open rate: benchmark 22%+

ORANGE group (deliverability):
- Deliverability Score: % in inbox vs spam, target 98%+
- Bounce Rate: healthy under 2%
- Unsubscribe Rate: healthy under 0.5%

GREEN group (revenue):
- Revenue Attributed: closed deal value where email was last touch
- A/B Tests Running: current tests + winning variant
- List Health Score: combined engagement + deliverability rating

**Data source:** Email_Marketing tab

### Page 9: Buying Intent Dashboard

**4 tier scorecards (color-coded):**
- HOT (80-100): count of hot leads - RED (#e74c3c)
- WARM (50-79): count of warm leads - ORANGE (#e67e22)
- INTERESTED (25-49): count - YELLOW (#f1c40f)
- COLD (0-24): count - GRAY (#95a5a6)

**Intent scoring chart:** bar chart showing signal breakdown

**11 detailed metric rows:**
- Hot Leads (80-100): count + instant alert threshold
- Warm Leads (50-79): high priority nurture targets
- Interested (25-49): engaged, keep in sequence
- Cold Leads (0-24): long-term nurture + retarget via ads
- Avg Intent Score: rising average = messaging is working
- Score Velocity: how fast leads moving up tiers week-over-week
- Top Signal This Week: which touchpoint driving most engagement
- Intent to Booked Rate: % of Hot leads who book a call, target 60%+
- Avg Days to Intent: average days from registration to score 80+
- Re-Engaged This Week: cold leads who suddenly fired a signal
- Top 5 Hottest Leads: name + score + last action (refreshes hourly)

**Data source:** Intent_Scores tab + Top5_Hot_Leads tab

---

## INTENT SCORING SYSTEM - BUILD GUIDE

### GHL Custom Field to Create
- Field name: Intent Score
- Field key: intent_score
- Field type: Number
- Default value: 0
- Add to: all contacts

### The 10 Scoring Signals

| Signal | Points | How n8n Detects It |
|--------|--------|--------------------|
| Clicked Calendly link (did not book) | +50 | GHL webhook: link_clicked event on Calendly URL |
| Booked call + cancelled | +45 | Calendly webhook: invitee.canceled event |
| Attended live webinar | +40 | Zoom API: appears in attendees list |
| Replied to DM or email | +35 | GHL webhook: email_replied or LinkedIn API |
| Clicked CTA in email | +30 | GHL webhook: email_link_clicked |
| Visited landing page 2+ times | +25 | GHL webhook: page_visited (count threshold) |
| Opened 3+ emails in 7 days | +20 | GHL webhook: email_opened (count + date filter) |
| Watched webinar replay | +20 | GHL webhook: replay link clicked + 5 min dwell |
| Clicked any email link | +15 | GHL webhook: email_link_clicked |
| Opened single email | +10 | GHL webhook: email_opened |

### Score Tier Logic
- 0-24: COLD - tag: intent-cold
- 25-49: INTERESTED - tag: intent-interested
- 50-79: WARM - tag: intent-warm
- 80-100: HOT - tag: intent-hot + SEND ALERT

### n8n Intent Score Workflow
**Trigger:** GHL webhook (fires on every email event)
**Node sequence:**
```
GHL Webhook -> Extract (contact_id, event_type, email)
  -> Switch (event_type):
      email_opened -> points = 10
      email_link_clicked -> points = 15 (or 30 if CTA link)
      page_visited -> points = 25
      replay_clicked -> points = 20
  -> HTTP Request -> GHL API GET contact/{id} (get current intent_score)
  -> Set node: new_score = current + points
  -> IF new_score > 100: cap at 100
  -> HTTP Request -> GHL API PATCH contact/{id} custom_fields: intent_score = new_score
  -> Update tier tag (remove old tier, add new tier)
  -> IF new_score >= 80:
      -> Send Email -> Randy + BrndGuru: "HOT LEAD ALERT: [name] just hit score [score]"
      -> Slack message -> #randy-alerts (optional)
  -> HTTP Request -> Google Sheets API -> append row to Intent_Scores tab
```

### Top 5 Hot Leads Refresh (Hourly)
**Trigger:** Schedule node - every 1 hour
**Node sequence:**
```
Schedule -> HTTP Request -> GHL API: search contacts, sort by intent_score DESC, limit 5
  -> Set node: format top 5 rows
  -> HTTP Request -> Google Sheets API: clear Top5_Hot_Leads tab
  -> HTTP Request -> Google Sheets API: append 5 rows
  -> (Looker Studio reads this tab and refreshes automatically)
```

---

## n8n DATA COLLECTION WORKFLOWS

### Workflow 1: Agent 1 Data Pull (Weekly)
**Trigger:** Schedule - every Monday 7AM EST
**Nodes:**
```
Schedule
  -> GHL API: GET contacts with tag webinar-registered (count = registrations)
  -> GHL API: GET contacts with tag webinar-attended (count + % = show-up rate)
  -> GHL API: GET contacts with tag webinar-noshow (count + % = no-show rate)
  -> GHL API: GET contacts with tag call-booked (count = calls booked)
  -> GHL API: GET email stats for current cycle
  -> Zoom API: GET /v2/past_webinars/{id}/attendees (cross-reference)
  -> Calendly API: GET scheduled events this week
  -> Set node: format all data into one row
  -> Google Sheets API: append row to Agent1_Data tab
```

### Workflow 2: Agent 2 Data Pull (Weekly)
**Trigger:** Schedule - every Monday 7:05AM EST
**Nodes:**
```
Schedule
  -> LinkedIn Ads API: GET /adAnalytics (spend, impressions, clicks, CTR per campaign)
  -> LinkedIn Ads API: GET /adCampaigns (list active campaigns)
  -> GHL API: GET contacts with utm_source=linkedin (registrations from ads)
  -> Set node: calculate CPL, CPA, best creative
  -> Google Sheets API: append row to Agent2_Data tab
```

### Workflow 3: Agent 3 Data Pull (Weekly)
**Trigger:** Schedule - every Monday 7:10AM EST
**Nodes:**
```
Schedule
  -> LinkedIn API: GET /ugcPosts (posts published this week, impressions, reactions, comments)
  -> LinkedIn API: GET /networkSizes (follower count change)
  -> LinkedIn API: GET /profileViews (profile view count)
  -> GHL API: GET contacts with utm_source=linkedin AND utm_medium=organic (organic sign-ups)
  -> Set node: calculate engagement rate, find top post
  -> Google Sheets API: append row to Agent3_Data tab
```

### Workflow 4: Agent 4 Data Pull (Weekly)
**Trigger:** Schedule - every Monday 7:15AM EST
**Nodes:**
```
Schedule
  -> HeyReach API: GET /campaigns/{id}/stats (requests sent, accepted, reply rate)
  -> HeyReach API: GET /conversations (active convos count)
  -> GHL API: GET contacts with tag call-booked AND source=linkedin-dm
  -> Set node: format all stats
  -> Google Sheets API: append row to Agent4_Data tab
```

### Workflow 5: Agent 5 Data Pull (Weekly)
**Trigger:** Schedule - every Monday 7:20AM EST
**Nodes:**
```
Schedule
  -> GHL API: GET email stats (emails sent, opens, clicks, unsubscribes, bounces)
  -> GHL API: GET contacts with tag call-booked AND source=email
  -> GHL API: GET contacts total count (list size)
  -> Set node: calculate open rate, click rate, deliverability
  -> Google Sheets API: append row to Agent5_Data tab
```

### Workflow 6: Revenue Pipeline Pull (Weekly)
**Trigger:** Schedule - every Monday 7:25AM EST
**Nodes:**
```
Schedule
  -> GHL API: GET /opportunities (all pipeline stages + values)
  -> Set node: sum by stage, calculate pipeline value, close rate, forecast
  -> GHL API: GET contacts with tag client-won (this month, filter by date)
  -> Set node: calculate revenue closed, attribution split, avg deal value
  -> Google Sheets API: append row to Revenue_Pipeline tab
```

### Workflow 7: Email Marketing Deep Dive (Weekly)
**Trigger:** Schedule - every Monday 7:30AM EST
**Nodes:**
```
Schedule
  -> GHL API: GET all email campaigns + stats (per sequence open rates)
  -> GHL API: GET contacts count (list size, growth vs last week)
  -> GHL API: GET email deliverability stats (bounce rate, spam rate)
  -> Set node: format 14 metrics, calculate list health score
  -> Google Sheets API: append row to Email_Marketing tab
```

### Workflow 8: Summary Row (Weekly)
**Trigger:** Schedule - every Monday 7:35AM EST
**Nodes:**
```
Schedule
  -> Google Sheets API: read latest row from all 6 data tabs
  -> Set node: combine into summary row
  -> Google Sheets API: append row to Summary tab
```

---

## GHL SETUP FOR AGENT 6

### Custom Fields to Create (Settings -> Custom Fields)
Add these to Contacts:
1. **intent_score** (Number, default: 0)
2. **intent_tier** (Text: hot/warm/interested/cold)
3. **last_intent_signal** (Text)
4. **last_intent_date** (Date)
5. **days_in_funnel** (Number - auto-calculated from contact created date)
6. **utm_source** (Text)
7. **utm_medium** (Text)
8. **utm_campaign** (Text)
9. **revenue_attributed** (Number - for won deals)
10. **deal_source** (Text: ads/organic/email/dms)

### GHL Pipeline: Webinar Funnel Revenue
Stages (in order):
1. Registered
2. Attended
3. No-Show
4. Call Booked
5. Proposal Sent
6. Client Won
7. Client Lost

For each Won deal: fill in deal value + deal_source field

### GHL Webhooks to Configure (for Intent Scoring)
Settings -> Integrations -> Webhooks -> Add New

Events to subscribe (all POST to n8n webhook URL):
- contact.email_opened
- contact.email_link_clicked
- contact.page_visited
- contact.tag_added
- opportunity.status_changed

---

## GOOGLE LOOKER STUDIO SETUP STEPS

1. Go to lookerstudio.google.com
2. Click Create -> Report
3. Add data source -> Google Sheets -> select "BrndGuru - Randy Wimmer - Dashboard Data"
4. Create one data source connection per tab (you can add multiple sheets as separate sources)
5. Build each page using:
   - Scorecards for KPI numbers
   - Tables for detail rows
   - Bar charts for funnels
   - Pie charts for attribution
6. Style guide:
   - Font: Roboto
   - Background: #f8f9fa (light gray)
   - Headers: #1e3a5f (navy) with white text
   - Agent 1 accent: #FF6600
   - Agent 2 accent: #2980b9
   - Agent 3 accent: #8e44ad
   - Agent 4 accent: #ea4b71
   - Agent 5 accent: #16a085
   - Agent 6 accent: #1e3a5f
7. Set data refresh: Auto (every 15 minutes)
8. Share settings: "Anyone with the link can view"
9. Copy the shareable link -> this is Randy's dashboard URL

---

## API REFERENCE

### GHL API
- Base: https://services.leadconnectorhq.com
- Auth: Authorization: Bearer {API_KEY} + Version: 2021-07-28
- Get contacts: GET /contacts?locationId={id}&tags=webinar-registered
- Update contact: PATCH /contacts/{contactId}
- Get email stats: GET /campaigns/{id}/stats
- Get opportunities: GET /opportunities/search?location_id={id}

### Google Sheets API (via n8n)
- Use n8n's built-in "Google Sheets" node
- Auth: Service Account (upload the JSON key file to n8n credentials)
- Append row: Spreadsheet ID + Sheet Name + Values array
- Read range: Spreadsheet ID + A1 notation

### Zoom API
- Base: https://api.zoom.us/v2
- Auth: Server-to-Server OAuth (same as Agent 1)
- Past webinar attendees: GET /past_webinars/{webinar_id}/attendees
- Past webinar absentees: GET /past_webinars/{webinar_id}/absentees

### LinkedIn Ads API
- Base: https://api.linkedin.com/v2
- Auth: OAuth 2.0 Bearer Token (same as Agent 2)
- Ad analytics: GET /adAnalytics?q=analytics&pivot=CREATIVE&dateRange=...
- Campaigns: GET /adCampaigns?q=search&search.account.values[0]=urn:li:sponsoredAccount:{id}

### HeyReach API
- Base: https://api.heyreach.io/api/public
- Auth: X-API-KEY: {HEYREACH_API_KEY}
- Campaign stats: GET /campaign/{id}/statistics
- Conversations: GET /inbox/conversations

### Calendly API
- Base: https://api.calendly.com
- Auth: Authorization: Bearer {TOKEN}
- Scheduled events: GET /scheduled_events?user={user_uri}&min_start_time={date}

---

## BUYING INTENT - ALERT SYSTEM

### When a lead hits score 80+, n8n sends:

**Email to Randy:**
Subject: HOT LEAD ALERT - [First Name] [Last Name] just hit score [X]
Body:
- Name: [Full Name]
- Email: [Email]
- Phone: [Phone]
- Intent Score: [Score]
- Last Action: [e.g. "Clicked Calendly link 12 minutes ago"]
- Days in funnel: [X] days
- Total emails opened: [X]
- Webinar attended: [Yes/No]
- Action: Book a call with them NOW -> [Calendly link]

**Email to BrndGuru:**
Same as above + link to GHL contact record

**Slack message (if configured):**
"HOT LEAD: [Name] | Score: [X] | Last: [action] | [phone]"

---

## BUILD SEQUENCE (Week by Week)

### Week 1 - Foundation
1. Create Google Account / use BrndGuru Gmail
2. Create Google Sheet "BrndGuru - Randy Wimmer - Dashboard Data" with all 10 tabs
3. Add all column headers to each tab (per structure above)
4. Create Google Service Account + download JSON key
5. Add GHL custom fields: intent_score, intent_tier, last_intent_signal, utm fields
6. Add GHL pipeline stages in correct order

### Week 2 - Intent Scoring
7. Set up n8n Google Sheets credentials (Service Account JSON)
8. Configure GHL webhooks (all 5 event types) pointing to n8n
9. Build n8n Workflow: Intent Score Updater (handles all signal events)
10. Test: open a test email in GHL -> confirm n8n fires -> confirm intent_score updates
11. Build n8n Workflow: Top 5 Hot Leads (hourly refresh to Sheets)
12. Test HOT alert: manually set a test contact to score 80 -> confirm email fires

### Week 3 - Data Pipelines
13. Build n8n Workflow 1: Agent 1 data pull (Monday schedule)
14. Build n8n Workflow 2: Agent 2 data pull
15. Build n8n Workflow 3: Agent 3 data pull
16. Build n8n Workflow 4: Agent 4 data pull
17. Build n8n Workflow 5: Agent 5 data pull
18. Build n8n Workflow 6: Revenue Pipeline pull
19. Run all workflows manually once -> verify data appears in correct Google Sheet tabs

### Week 4 - Looker Studio Dashboard
20. Create Looker Studio report -> connect all Google Sheet tabs as data sources
21. Build Page 1: Command Center (summary bar + 6 agent mini-cards)
22. Build Page 2: Agent 1 Panel
23. Build Page 3: Agent 2 Panel
24. Build Page 4: Agent 3 Panel
25. Build Page 5: Agent 4 Panel
26. Build Page 6: Agent 5 Panel
27. Build Page 7: Revenue Pipeline + Full Funnel
28. Build Page 8: Email Marketing Deep Dive
29. Build Page 9: Buying Intent Dashboard (tier scorecards + Top 5 table)
30. Apply BrndGuru color styling
31. Set auto-refresh to 15 minutes
32. Get shareable link -> test in incognito browser
33. Send URL to Randy

---

## TESTING CHECKLIST

- [ ] All 10 Google Sheet tabs exist with correct column headers
- [ ] GHL custom field intent_score exists on contacts (check a contact record)
- [ ] GHL webhooks fire correctly: send test email -> confirm n8n receives event
- [ ] Intent score updates on GHL contact when webhook fires
- [ ] HOT lead alert email sends when score reaches 80 (test manually)
- [ ] Top 5 Hot Leads tab in Sheets refreshes every hour (check timestamps)
- [ ] Run all 6 Monday data pull workflows manually -> data appears in correct tabs
- [ ] Looker Studio shows data from Sheets (may take 15 mins to refresh)
- [ ] All 9 dashboard pages load correctly in Randy's browser
- [ ] Dashboard is accessible via shareable link without login
- [ ] Test in mobile browser (Randy may check on phone)
- [ ] Confirm data auto-refreshes every 15 minutes (wait and watch)

---

## DASHBOARD GO-LIVE PLAN

| Section | Goes Live | Depends On |
|---------|-----------|-----------|
| Intent Scoring System | Day 1 of build | GHL webhooks + n8n |
| Command Center (overview) | Week 1 | Google Sheets setup |
| Agent 1 Panel | Month 1 | Agent 1 producing data |
| Agent 2 Panel | Month 1 | Agent 2 producing data |
| Full Funnel View | Month 1 | Agents 1 + 2 + GHL pipeline |
| Revenue Pipeline | Month 1 | GHL pipeline stages filled |
| Email Marketing Panel | Month 1 | GHL email sequences running |
| Buying Intent Dashboard | Month 1 | Intent scoring live |
| Agent 3 Panel | Month 2 | Agent 3 built |
| Agent 4 Panel | Month 2 | Agent 4 + HeyReach |
| Agent 5 Panel | Month 3 | Agent 5 built |
| Full Attribution Split | Month 3 | All agents live |

---

## IMPORTANT NOTES FOR NEW SESSION

- Build the Google Sheets structure FIRST before building Looker Studio (Looker reads from Sheets)
- The intent scoring system can go live immediately - it does not depend on any other agent
- All n8n workflows share the same VPS as Agents 1 and 2 - do not touch existing workflows
- When data is missing (agent not built yet), the Sheets cell stays empty - Looker shows "No Data" which is fine
- Do NOT delete or rename any tab in the Google Sheet once Looker is connected - it will break the dashboard
- The Looker Studio sharing must be set to "Anyone with the link can view" so Randy can access without a Google account
- Randy's dashboard URL never changes - even as new data comes in, it's the same URL
- If Randy wants the dashboard embedded in a webpage later, Looker Studio supports iframe embedding

---

*Document prepared by BrndGuru | brndguruofficial@gmail.com*
*Agent 6 - Revenue Control Tower | Government Contracting Academy*
