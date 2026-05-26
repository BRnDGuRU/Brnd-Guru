from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch
from reportlab.lib import colors
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle,
    HRFlowable, KeepTogether
)
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_RIGHT
from reportlab.platypus import PageBreak

NAVY   = colors.HexColor("#1e3a5f")
MINT   = colors.HexColor("#2ec4a5")
GOLD   = colors.HexColor("#f5a623")
LGRAY  = colors.HexColor("#f4f6f9")
DGRAY  = colors.HexColor("#2c3e50")
WHITE  = colors.white

OUTPUT = "/home/user/Brnd-Guru/clients/randy-wimmer/Randy_Wimmer_Proposal.pdf"

doc = SimpleDocTemplate(
    OUTPUT,
    pagesize=letter,
    leftMargin=0.75*inch,
    rightMargin=0.75*inch,
    topMargin=0.6*inch,
    bottomMargin=0.6*inch,
)

styles = getSampleStyleSheet()

def style(name, **kw):
    s = ParagraphStyle(name, **kw)
    return s

S_title      = style("S_title",      fontName="Helvetica-Bold",   fontSize=22, textColor=WHITE,  alignment=TA_CENTER, leading=28, spaceAfter=4)
S_subtitle   = style("S_subtitle",   fontName="Helvetica",        fontSize=11, textColor=MINT,   alignment=TA_CENTER, leading=16, spaceAfter=2)
S_meta       = style("S_meta",       fontName="Helvetica",        fontSize=9,  textColor=WHITE,  alignment=TA_CENTER, leading=13, spaceAfter=2)
S_h1         = style("S_h1",         fontName="Helvetica-Bold",   fontSize=13, textColor=WHITE,  alignment=TA_LEFT,  leading=18, spaceBefore=6, spaceAfter=4)
S_h2         = style("S_h2",         fontName="Helvetica-Bold",   fontSize=11, textColor=NAVY,   alignment=TA_LEFT,  leading=16, spaceBefore=10, spaceAfter=4)
S_h3         = style("S_h3",         fontName="Helvetica-Bold",   fontSize=10, textColor=MINT,   alignment=TA_LEFT,  leading=14, spaceBefore=8,  spaceAfter=3)
S_body       = style("S_body",       fontName="Helvetica",        fontSize=9,  textColor=DGRAY,  alignment=TA_LEFT,  leading=14, spaceAfter=3)
S_body_w     = style("S_body_w",     fontName="Helvetica",        fontSize=9,  textColor=WHITE,  alignment=TA_LEFT,  leading=14, spaceAfter=3)
S_bullet     = style("S_bullet",     fontName="Helvetica",        fontSize=9,  textColor=DGRAY,  alignment=TA_LEFT,  leading=14, spaceAfter=2,   leftIndent=14, bulletIndent=4)
S_label      = style("S_label",      fontName="Helvetica-Bold",   fontSize=9,  textColor=DGRAY,  alignment=TA_LEFT,  leading=12)
S_center     = style("S_center",     fontName="Helvetica",        fontSize=9,  textColor=DGRAY,  alignment=TA_CENTER,leading=13)
S_note       = style("S_note",       fontName="Helvetica-Oblique",fontSize=8,  textColor=colors.HexColor("#7f8c8d"), alignment=TA_CENTER, leading=12)
S_sign_label = style("S_sign_label", fontName="Helvetica-Bold",   fontSize=9,  textColor=NAVY,   alignment=TA_LEFT,  leading=13)
S_sign_val   = style("S_sign_val",   fontName="Helvetica",        fontSize=9,  textColor=DGRAY,  alignment=TA_LEFT,  leading=13)

def section_header(text):
    tbl = Table([[Paragraph(text, S_h1)]], colWidths=[7*inch])
    tbl.setStyle(TableStyle([
        ("BACKGROUND",    (0,0), (-1,-1), NAVY),
        ("TOPPADDING",    (0,0), (-1,-1), 6),
        ("BOTTOMPADDING", (0,0), (-1,-1), 6),
        ("LEFTPADDING",   (0,0), (-1,-1), 10),
        ("RIGHTPADDING",  (0,0), (-1,-1), 10),
    ]))
    return tbl

def agent_header(num, name, tag):
    tbl = Table([[
        Paragraph(f"<b>AGENT {num}</b>", style("ah_num", fontName="Helvetica-Bold", fontSize=14, textColor=GOLD, leading=18)),
        Paragraph(f"<b>{name}</b><br/><font size=8>{tag}</font>",
                  style("ah_name", fontName="Helvetica-Bold", fontSize=11, textColor=WHITE, leading=16)),
    ]], colWidths=[1.1*inch, 5.9*inch])
    tbl.setStyle(TableStyle([
        ("BACKGROUND",    (0,0), (-1,-1), NAVY),
        ("TOPPADDING",    (0,0), (-1,-1), 8),
        ("BOTTOMPADDING", (0,0), (-1,-1), 8),
        ("LEFTPADDING",   (0,0), (-1,-1), 10),
        ("RIGHTPADDING",  (0,0), (-1,-1), 10),
        ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
    ]))
    return tbl

def bullet(text):
    return Paragraph(f"• &nbsp; {text}", S_bullet)

def two_col_table(rows, col_widths=(2.5*inch, 4.5*inch)):
    data = []
    for k, v in rows:
        data.append([Paragraph(k, S_label), Paragraph(v, S_body)])
    tbl = Table(data, colWidths=col_widths)
    tbl.setStyle(TableStyle([
        ("GRID",          (0,0), (-1,-1), 0.4, colors.HexColor("#dce3ea")),
        ("TOPPADDING",    (0,0), (-1,-1), 5),
        ("BOTTOMPADDING", (0,0), (-1,-1), 5),
        ("LEFTPADDING",   (0,0), (-1,-1), 8),
        ("RIGHTPADDING",  (0,0), (-1,-1), 8),
        ("VALIGN",        (0,0), (-1,-1), "TOP"),
        ("ROWBACKGROUNDS",(0,0), (-1,-1), [WHITE, LGRAY]),
    ]))
    return tbl

def pricing_table(headers, rows, col_widths, highlight_last=False):
    data = [[Paragraph(h, style("th", fontName="Helvetica-Bold", fontSize=9, textColor=WHITE, alignment=TA_CENTER, leading=13)) for h in headers]]
    for i, row in enumerate(rows):
        is_last = highlight_last and i == len(rows) - 1
        cell_style = style(f"td{i}", fontName="Helvetica-Bold" if is_last else "Helvetica",
                           fontSize=9, textColor=NAVY if is_last else DGRAY, leading=13)
        data.append([Paragraph(str(c), cell_style) for c in row])
    tbl = Table(data, colWidths=col_widths)
    ts = [
        ("BACKGROUND",    (0,0), (-1,0),  NAVY),
        ("ROWBACKGROUNDS",(0,1), (-1,-1), [WHITE, LGRAY]),
        ("GRID",          (0,0), (-1,-1), 0.4, colors.HexColor("#dce3ea")),
        ("TOPPADDING",    (0,0), (-1,-1), 5),
        ("BOTTOMPADDING", (0,0), (-1,-1), 5),
        ("LEFTPADDING",   (0,0), (-1,-1), 8),
        ("RIGHTPADDING",  (0,0), (-1,-1), 8),
        ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
        ("ALIGN",         (0,0), (-1,-1), "CENTER"),
    ]
    if highlight_last:
        ts.append(("BACKGROUND", (0, len(rows)), (-1, len(rows)), colors.HexColor("#e8f8f5")))
        ts.append(("LINEABOVE",  (0, len(rows)), (-1, len(rows)), 1.5, MINT))
    tbl.setStyle(TableStyle(ts))
    return tbl

def goal_box(text):
    tbl = Table([[Paragraph(f"<b>Goal:</b> {text}", S_body)]], colWidths=[7*inch])
    tbl.setStyle(TableStyle([
        ("BACKGROUND",    (0,0), (-1,-1), LGRAY),
        ("TOPPADDING",    (0,0), (-1,-1), 6),
        ("BOTTOMPADDING", (0,0), (-1,-1), 6),
        ("LEFTPADDING",   (0,0), (-1,-1), 10),
        ("RIGHTPADDING",  (0,0), (-1,-1), 10),
        ("LINERIGHT",     (0,0), (0,-1),  3, MINT),
    ]))
    return tbl

story = []

# ── COVER BANNER ──────────────────────────────────────────────────────────────
cover_cells = [
    Paragraph("PROPOSAL", S_title),
    Paragraph("Govt. Contracting's Webinar-Led Revenue Infrastructure", S_subtitle),
    Paragraph("6-Agent Automation System", S_subtitle),
    Spacer(1, 6),
    Paragraph("Prepared For: &nbsp; Randy Wimmer — Government Contracting Academy", S_meta),
    Paragraph("Prepared By: &nbsp; Shivanshu — BrndGuru &nbsp;|&nbsp; brndguruofficial@gmail.com", S_meta),
    Paragraph("Date: May 26, 2026 &nbsp;|&nbsp; Valid Until: June 10, 2026", S_meta),
]
cover_tbl = Table([cover_cells], colWidths=[7*inch])
cover_tbl.setStyle(TableStyle([
    ("BACKGROUND",    (0,0), (-1,-1), NAVY),
    ("TOPPADDING",    (0,0), (-1,-1), 22),
    ("BOTTOMPADDING", (0,0), (-1,-1), 22),
    ("LEFTPADDING",   (0,0), (-1,-1), 18),
    ("RIGHTPADDING",  (0,0), (-1,-1), 18),
]))
story.append(cover_tbl)
story.append(Spacer(1, 14))

# ── CORE OUTCOME BANNER ───────────────────────────────────────────────────────
outcome_tbl = Table([[
    Paragraph("<b>CORE OUTCOME:</b>", style("co_lbl", fontName="Helvetica-Bold", fontSize=10, textColor=GOLD, leading=14)),
    Paragraph("Fill weekly webinars, convert qualified attendees into calls, and push warm leads into monthly bootcamps.",
              style("co_txt", fontName="Helvetica", fontSize=9, textColor=WHITE, leading=14)),
]], colWidths=[1.4*inch, 5.6*inch])
outcome_tbl.setStyle(TableStyle([
    ("BACKGROUND",    (0,0), (-1,-1), DGRAY),
    ("TOPPADDING",    (0,0), (-1,-1), 8),
    ("BOTTOMPADDING", (0,0), (-1,-1), 8),
    ("LEFTPADDING",   (0,0), (-1,-1), 10),
    ("RIGHTPADDING",  (0,0), (-1,-1), 10),
    ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
]))
story.append(outcome_tbl)
story.append(Spacer(1, 12))

# ── PIPELINE ─────────────────────────────────────────────────────────────────
story.append(Paragraph("Core Revenue Pipeline", S_h2))
pipeline_stages = ["Traffic", "Webinar\nRegistration", "Webinar\nAttendance", "Call\nBooking", "Opportunity\n& Proposal", "Closed\nDeals"]
pipeline_colors = ["#3498db","#2980b9","#8e44ad","#e67e22","#e74c3c","#27ae60"]
pipeline_data = [[Paragraph(s, style("pip", fontName="Helvetica-Bold", fontSize=8, textColor=WHITE, alignment=TA_CENTER, leading=12)) for s in pipeline_stages]]
pipeline_tbl = Table(pipeline_data, colWidths=[1.17*inch]*6)
ts = [("TOPPADDING",(0,0),(-1,-1),8),("BOTTOMPADDING",(0,0),(-1,-1),8),("GRID",(0,0),(-1,-1),1,WHITE),("VALIGN",(0,0),(-1,-1),"MIDDLE")]
for i, c in enumerate(pipeline_colors):
    ts.append(("BACKGROUND",(i,0),(i,0),colors.HexColor(c)))
pipeline_tbl.setStyle(TableStyle(ts))
story.append(pipeline_tbl)
story.append(Spacer(1, 14))
story.append(HRFlowable(width="100%", thickness=1, color=colors.HexColor("#dce3ea")))
story.append(Spacer(1, 8))

# ── EXECUTIVE SUMMARY ────────────────────────────────────────────────────────
story.append(section_header("EXECUTIVE SUMMARY"))
story.append(Spacer(1, 8))
story.append(Paragraph(
    "Randy Wimmer has built decades of credibility in the federal contracting space — an 8-figure exit, $1B+ in awards led, and a mission to help the next generation of GovCon founders break in. This proposal outlines a <b>6-Agent Automation Infrastructure</b> — a fully integrated, system-driven revenue engine to generate consistent, scalable monthly revenue from ISO certification packages ($20K–$24K).",
    S_body))
story.append(Spacer(1, 6))
story.append(two_col_table([
    ("Client",           "Randy Wimmer — Government Contracting Academy / ISO Certification Group"),
    ("Service Provider", "BrndGuru — brndguruofficial@gmail.com"),
    ("Offer",            "ISO 9001 Certification Packages ($20,000–$24,000)"),
    ("Target Audience",  "GovCon small businesses — 1–50 employees, 0–5 contract awards"),
    ("Delivery",         "3 months — 2 agents per month (phased rollout)"),
]))
story.append(Spacer(1, 14))

# ── THE 6-AGENT SYSTEM ───────────────────────────────────────────────────────
story.append(section_header("THE 6-AGENT SYSTEM"))
story.append(Spacer(1, 10))

# Agent 1
story.append(KeepTogether([
    agent_header(1, "WEBINAR FUNNEL AGENT", "Event Engine — Core | MONTH 1"),
    Spacer(1, 6),
    Paragraph("The conversion core of the entire system. Every channel drives traffic here first.", S_body),
    Spacer(1, 4),
]))
for item in [
    "Landing page creation (registration-optimized)",
    "Registration form & lead capture",
    "Confirmation email + calendar add automation",
    "Reminder sequence (T-24hr, T-3hr, T-15min)",
    "Pre-webinar nurture sequence",
    "Webinar delivery infrastructure (GoHighLevel + Zoom integration)",
    "Replay delivery automation for no-shows",
    "No-show recovery sequence",
    "Post-webinar follow-up (call booking CTA)",
    "Workshop / Bootcamp upsell sequence",
]:
    story.append(bullet(item))
story.append(Spacer(1, 4))
story.append(goal_box("Maximize registrations, attendance rate, and call bookings from every webinar."))
story.append(Spacer(1, 12))

# Agent 2
story.append(KeepTogether([
    agent_header(2, "PAID ACQUISITION OPTIMIZATION AGENT", "Demand Engine | MONTH 1"),
    Spacer(1, 6),
    Paragraph("Drives paid traffic into the webinar funnel. Scales what works, cuts what doesn't.", S_body),
    Spacer(1, 4),
]))
for item in [
    "LinkedIn Ads account setup & campaign structure",
    "Audience targeting (ICP-matched GovCon founders, 1–50 employees, SAM.gov registered)",
    "Creative testing (3–5 ad variations per campaign)",
    "CPL (Cost per Lead) & CPA (Cost per Acquisition) tracking setup",
    "Cost per registrant / attendee / qualified booking monitoring",
    "Weekly campaign optimization",
    "Retargeting campaigns (website visitors, webinar no-shows)",
    "Lookalike audience creation from existing list",
    "Attribution & reporting (source tracking across all touchpoints)",
]:
    story.append(bullet(item))
story.append(Spacer(1, 4))
story.append(goal_box("Lower cost per qualified webinar attendee and scale registered volume profitably."))
story.append(Spacer(1, 12))

# Agent 3
story.append(KeepTogether([
    agent_header(3, "LINKEDIN CONTENT AUTHORITY AGENT", "Authority Engine | MONTH 2"),
    Spacer(1, 6),
    Paragraph("Builds Randy's organic presence and keeps the audience warm between webinars.", S_body),
    Spacer(1, 4),
]))
for item in [
    "Founder profile posts (3–5/week, authority-building content)",
    "Company page posts (Government Contracting Academy + ISO Certification Group)",
    "Webinar promotion posts (every webinar cycle)",
    "Authority / thought leadership content (ISO, GovCon strategy, market insights)",
    "Social proof / case study posts (client results, contract wins)",
    "Reels / short-form video snippets (repurposed from webinar recordings)",
    "Newsletter content (The GovCon Times + The Good Enough Entrepreneur)",
    "Bootcamp promotion posts (monthly)",
    "Content calendar automation (scheduled, consistent posting)",
]:
    story.append(bullet(item))
story.append(Spacer(1, 4))
story.append(goal_box("Build authority, trust, and organic visibility that drives inbound webinar registrations."))
story.append(Spacer(1, 12))

# Agent 4
story.append(KeepTogether([
    agent_header(4, "LINKEDIN INTENT & ENGAGEMENT MONITORING AGENT", "Intelligence Engine | MONTH 2"),
    Spacer(1, 6),
    Paragraph("Monitors LinkedIn activity across Randy's profile and company pages — tracking who's engaging, detecting buying signals, and surfacing warm leads automatically so no opportunity is missed.", S_body),
    Spacer(1, 4),
]))
story.append(Paragraph("<b>A. Profile & Content Engagement Tracking</b>", S_h3))
for item in [
    "Monitor post engagement (likes, comments, shares) across founder profile and company pages",
    "Track profile viewers — log who's visiting and flag ICP matches",
    "Identify repeat engagers (people who consistently interact with content)",
    "Tag and segment warm engagers into GHL CRM automatically",
]:
    story.append(bullet(item))
story.append(Paragraph("<b>B. Inbound DM & Comment Management (AI-Assisted)</b>", S_h3))
for item in [
    "AI-drafted replies to inbound DMs and comments (human reviews before sending)",
    "Detect buying intent signals in conversations (ISO questions, pricing enquiries, call requests)",
    "Detect webinar interest from comment activity",
    "Auto-tag high-intent leads and escalate to booking funnel immediately",
]:
    story.append(bullet(item))
story.append(Paragraph("<b>C. Audience Intelligence & Segmentation</b>", S_h3))
for item in [
    "Weekly audience insight report (who's engaging, by role and company type)",
    "Segment warm audience into webinar invitees vs call-ready leads",
    "Feed high-intent signals into GHL for automated follow-up sequences",
    "Newsletter subscriber growth tracking (GovCon Times + Good Enough Entrepreneur)",
]:
    story.append(bullet(item))
story.append(Spacer(1, 4))
story.append(goal_box("Surface warm leads from existing LinkedIn activity and route them into the booking funnel automatically."))
story.append(Spacer(1, 12))

# Agent 5
story.append(KeepTogether([
    agent_header(5, "EMAIL INTENT & NURTURE ENGINE", "Intent Engine | MONTH 3"),
    Spacer(1, 6),
    Paragraph("Automates the full email pipeline — from webinar reminders to post-call follow-up — ensuring every lead is nurtured until they book or buy.", S_body),
    Spacer(1, 4),
]))
for item in [
    "Webinar reminder sequences — T-24hr, T-3hr, T-15min (automated inside GHL)",
    "Pre-webinar nurture (value-building emails before each session)",
    "Replay delivery automation (sent to no-shows within 1 hour of webinar end)",
    "No-show recovery sequence (3-step re-engagement to rebook missed attendees)",
    "Post-webinar follow-up (multi-step sequence pushing attendees to book a 1:1 call)",
    "Bootcamp / offer upsell sequences (push warm leads to monthly bootcamp enrollment)",
    "Reactivation campaigns (dormant registrant segments — 30/60/90-day re-engagement)",
    "Intent scoring & segmentation (auto-tag leads by engagement: hot / warm / cold)",
    "GHL pipeline automation (move leads between stages based on email actions)",
]:
    story.append(bullet(item))
story.append(Spacer(1, 4))
story.append(goal_box("Nurture, re-engage, and convert attendees and list members into qualified sales calls."))
story.append(Spacer(1, 12))

# Agent 6
story.append(KeepTogether([
    agent_header(6, "REVENUE CONTROL TOWER DASHBOARD", "Control Tower | MONTH 3"),
    Spacer(1, 6),
    Paragraph("Full visibility across every touchpoint. One dashboard to see what's working, what's not, and where to scale.", S_body),
    Spacer(1, 4),
]))
for item in [
    "Ad spend & performance tracking",
    "Registration volume by source",
    "Attendance & no-show rates",
    "Booked calls (total + by source)",
    "Qualified leads, proposals, wins & revenue",
    "Source attribution (LinkedIn, Email, Ads, Organic)",
    "LinkedIn outreach & content performance stats",
    "Pipeline value & revenue forecast",
]:
    story.append(bullet(item))
story.append(Spacer(1, 4))
story.append(goal_box("Full visibility, insights, and revenue performance control across all channels."))
story.append(Spacer(1, 14))

# ── TOOLS & TECHNOLOGY STACK ─────────────────────────────────────────────────
story.append(section_header("TOOLS & TECHNOLOGY STACK"))
story.append(Spacer(1, 8))
story.append(Paragraph(
    "The following platforms power the 6-agent system. Each tool is purpose-selected for reliability, GovCon-market fit, and cost efficiency.",
    S_body))
story.append(Spacer(1, 8))

tools = [
    # (Category, Tool, Role, Used In, Notes)
    ("CRM & Automation",    "GoHighLevel (GHL)",    "CRM, pipeline management, funnel builder, landing pages, calendar integration",                  "Agents 1, 5, 6",         "Already subscribed — $97/month"),
    ("Email Infrastructure","GHL Email",            "Transactional & marketing email delivery, webinar reminders, nurture sequences, reactivation campaigns", "Agents 1, 5",    "Configured inside GHL — no extra cost"),
    ("Webinar Platform",    "Zoom Webinars",        "Live webinar hosting, attendance tracking, Q&A, recording & replay delivery",                      "Agent 1",                "Existing subscription"),
    ("LinkedIn — Pages",    "LinkedIn (Organic)",   "Founder profile, company pages (GCA, ICG), newsletter publishing, scheduled content posting",     "Agents 3, 4",            "Existing accounts — no extra cost"),
    ("LinkedIn — Ads",      "LinkedIn Campaign Mgr","Paid acquisition campaigns, audience targeting, retargeting, lookalike audiences, CPL/CPA tracking","Agent 2",                "Ad budget billed separately ($500–$1,500/month)"),
    ("AI Automation",       "Claude AI",            "Content drafting, inbound DM reply assist, sequence optimization, intent detection (human-in-the-loop)", "Agents 3, 4, 5",   "$100/month (included in agency fee)"),
    ("Analytics & BI",      "Revenue Dashboard",    "Unified reporting across all channels — registrations, calls, pipeline, revenue, attribution",      "Agent 6",                "Built inside GHL + connected data sources"),
]

tool_header = ["Category", "Tool", "Role & Function", "Used In", "Notes"]
tool_data = [[Paragraph(h, style(f"th{i}", fontName="Helvetica-Bold", fontSize=8, textColor=WHITE, alignment=TA_CENTER, leading=12)) for i, h in enumerate(tool_header)]]
row_bg = [WHITE, LGRAY]
for i, row in enumerate(tools):
    tool_data.append([Paragraph(str(c), style(f"tc{i}", fontName="Helvetica", fontSize=8, textColor=DGRAY, leading=12)) for c in row])

tool_tbl = Table(tool_data, colWidths=[1.1*inch, 1.1*inch, 2.3*inch, 0.8*inch, 1.7*inch])
tool_tbl.setStyle(TableStyle([
    ("BACKGROUND",    (0,0), (-1,0),  NAVY),
    ("ROWBACKGROUNDS",(0,1), (-1,-1), [WHITE, LGRAY]),
    ("GRID",          (0,0), (-1,-1), 0.4, colors.HexColor("#dce3ea")),
    ("TOPPADDING",    (0,0), (-1,-1), 5),
    ("BOTTOMPADDING", (0,0), (-1,-1), 5),
    ("LEFTPADDING",   (0,0), (-1,-1), 6),
    ("RIGHTPADDING",  (0,0), (-1,-1), 6),
    ("VALIGN",        (0,0), (-1,-1), "TOP"),
    ("ALIGN",         (3,0), (3,-1),  "CENTER"),
    ("FONTNAME",      (1,1), (1,-1),  "Helvetica-Bold"),
    ("TEXTCOLOR",     (1,1), (1,-1),  NAVY),
]))
story.append(tool_tbl)
story.append(Spacer(1, 6))
story.append(Paragraph("* LinkedIn Ads budget is client-funded and billed directly to the client's ad account. BrndGuru manages strategy, targeting, and optimization.", S_note))
story.append(Spacer(1, 14))

# ── IMPLEMENTATION TIMELINE ──────────────────────────────────────────────────
story.append(section_header("IMPLEMENTATION TIMELINE"))
story.append(Spacer(1, 8))
story.append(pricing_table(
    ["Phase", "Month", "Agents Delivered", "Focus"],
    [
        ["Phase 1", "Month 1", "Agent 1 + Agent 2", "Build the core conversion path — webinar funnel + paid traffic"],
        ["Phase 2", "Month 2", "Agent 3 + Agent 4", "Layer in authority content + LinkedIn intent & engagement monitoring"],
        ["Phase 3", "Month 3", "Agent 5 + Agent 6", "Activate email engine + deploy full revenue dashboard"],
    ],
    [0.8*inch, 0.8*inch, 1.7*inch, 3.7*inch]
))
story.append(Spacer(1, 6))
story.append(Paragraph(
    "<i>Strategic Principle: Perfect the core conversion path first — Traffic → Webinar → Call → Close. Then scale everything.</i>",
    S_note))
story.append(Spacer(1, 14))

# ── SUCCESS METRICS & KPIs ────────────────────────────────────────────────────
story.append(section_header("SUCCESS METRICS & KPIs"))
story.append(Spacer(1, 8))
kpi_data = [
    ["Agent", "KPI", "Target"],
    ["1 — Webinar Funnel",        "Show-up rate",                     "40%+"],
    ["1 — Webinar Funnel",        "Calls booked per webinar",          "3–5+"],
    ["2 — Paid Acquisition",      "Cost per registrant",               "<$15"],
    ["2 — Paid Acquisition",      "Cost per qualified call booking",   "<$150"],
    ["3 — LinkedIn Content",      "Engagement rate",                   "3–5%+"],
    ["3 — LinkedIn Content",      "Webinar sign-ups from organic",     "10+/cycle"],
    ["4 — LinkedIn Intelligence", "Warm leads surfaced per week",      "10–20+"],
    ["4 — LinkedIn Intelligence", "Inbound DM reply rate",            ">60%"],
    ["5 — Email Engine",          "Open rate",                         "30–45%"],
    ["5 — Email Engine",          "Calls booked from nurture sequences","10+/month"],
    ["6 — Dashboard",             "Full pipeline visibility",          "Weekly"],
]
kpi_tbl = Table(kpi_data, colWidths=[2.4*inch, 3.1*inch, 1.5*inch])
kpi_tbl.setStyle(TableStyle([
    ("BACKGROUND",    (0,0), (-1,0),  NAVY),
    ("TEXTCOLOR",     (0,0), (-1,0),  WHITE),
    ("FONTNAME",      (0,0), (-1,0),  "Helvetica-Bold"),
    ("FONTSIZE",      (0,0), (-1,-1), 9),
    ("ROWBACKGROUNDS",(0,1), (-1,-1), [WHITE, LGRAY]),
    ("GRID",          (0,0), (-1,-1), 0.4, colors.HexColor("#dce3ea")),
    ("TOPPADDING",    (0,0), (-1,-1), 5),
    ("BOTTOMPADDING", (0,0), (-1,-1), 5),
    ("LEFTPADDING",   (0,0), (-1,-1), 8),
    ("RIGHTPADDING",  (0,0), (-1,-1), 8),
    ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
    ("ALIGN",         (2,0), (2,-1),  "CENTER"),
    ("TEXTCOLOR",     (2,1), (2,-1),  MINT),
    ("FONTNAME",      (2,1), (2,-1),  "Helvetica-Bold"),
]))
story.append(kpi_tbl)
story.append(Spacer(1, 14))

# ── INVESTMENT & PRICING ─────────────────────────────────────────────────────
story.append(section_header("INVESTMENT & PRICING"))
story.append(Spacer(1, 8))

story.append(Paragraph("Monthly Agency Fee", S_h2))
story.append(pricing_table(
    ["Item", "Monthly Cost"],
    [
        ["Service Management Fee", "$800"],
        ["AI Automation (Claude)", "$100"],
        ["Total Agency Fee",       "$900/month"],
    ],
    [4.5*inch, 2.5*inch],
    highlight_last=True
))
story.append(Spacer(1, 10))

story.append(Paragraph("Payment Schedule", S_h2))
story.append(pricing_table(
    ["Milestone", "Amount", "Due"],
    [
        ["Month 1 Kickoff (50%)",    "$450", "Before work begins"],
        ["Month 1 Completion (50%)", "$450", "Upon Agent 1 + 2 live"],
        ["Month 2 Ongoing",          "$900", "Month 2 start"],
        ["Month 3 Ongoing",          "$900", "Month 3 start"],
    ],
    [3*inch, 1.5*inch, 2.5*inch]
))
story.append(Spacer(1, 10))

story.append(Paragraph("Tool Costs — Variable (Billed Directly to Client)", S_h2))
story.append(Paragraph("BrndGuru does not mark up tool costs. You pay each provider directly at their published rates.", S_body))
story.append(Spacer(1, 4))
story.append(pricing_table(
    ["Tool / Platform", "Purpose", "Est. Monthly Cost"],
    [
        ["GoHighLevel (GHL)",      "CRM, funnels, landing pages, pipeline mgmt","$97 (already subscribed)"],
        ["GHL Email",              "Email delivery, sequences & automation",  "Included in GHL"],
        ["Zoom Webinars",          "Live webinar hosting & replay delivery",  "Existing subscription"],
        ["LinkedIn (Organic)",     "Content scheduling, pages, newsletters",  "No extra cost"],
        ["LinkedIn Campaign Mgr",  "Paid ads, retargeting & lookalikes",      "$500–$1,500 (ad spend)"],
    ],
    [1.9*inch, 2.6*inch, 2.5*inch]
))
story.append(Spacer(1, 10))

story.append(Paragraph("Total Monthly Investment Summary", S_h2))
story.append(pricing_table(
    ["Category", "Range"],
    [
        ["Agency Fee (services + AI automation)", "$900"],
        ["Tools & Platforms (excl. ads)",         "$97 (GHL only — all others existing/free)"],
        ["LinkedIn Ads Budget",                    "$500–$1,500"],
        ["Total Monthly Estimate",                 "~$1,497–$2,497/month"],
    ],
    [4*inch, 3*inch],
    highlight_last=True
))
story.append(Spacer(1, 6))
story.append(Paragraph("*LinkedIn ad spend is the primary variable cost. Recommended starting budget: $500/month. Scale as cost-per-registrant improves.", S_note))
story.append(Spacer(1, 14))

# ── TERMS & CONDITIONS ───────────────────────────────────────────────────────
story.append(section_header("TERMS & CONDITIONS"))
story.append(Spacer(1, 8))
story.append(two_col_table([
    ("1. Engagement Model",      "Monthly retainer. Either party may terminate with 14 days written notice after Month 1 completion."),
    ("2. Payment Terms",         "50% upfront before work begins each phase; 50% on milestone completion."),
    ("3. Revisions",             "Up to 2 rounds of revisions per deliverable included."),
    ("4. Tool Costs",            "All third-party platform costs are the client's responsibility, billed directly by providers."),
    ("5. Ad Budget",             "LinkedIn ad spend is managed by BrndGuru but funded from the client's ad account. Budget decisions remain with the client."),
    ("6. Confidentiality",       "All client credentials, data, and business information handled with full confidentiality."),
    ("7. Intellectual Property", "All work product is owned by the client upon full payment."),
    ("8. Timeline",              "Deliverable timelines depend on timely receipt of required assets and access from the client."),
], col_widths=(2*inch, 5*inch)))
story.append(Spacer(1, 14))

# ── NEXT STEPS ───────────────────────────────────────────────────────────────
story.append(section_header("NEXT STEPS"))
story.append(Spacer(1, 8))
ns_data = [
    ["01", "Review & approve this proposal"],
    ["02", "Sign the agreement below"],
    ["03", "Submit Month 1 payment — $450 (50% upfront)"],
    ["04", "Complete onboarding checklist (access + assets)"],
    ["05", "Kickoff call — align on webinar title, dates, and ICP filters"],
]
ns_tbl = Table(ns_data, colWidths=[0.6*inch, 6.4*inch])
ns_tbl.setStyle(TableStyle([
    ("BACKGROUND",    (0,0), (0,-1), MINT),
    ("TEXTCOLOR",     (0,0), (0,-1), WHITE),
    ("FONTNAME",      (0,0), (0,-1), "Helvetica-Bold"),
    ("FONTSIZE",      (0,0), (-1,-1), 9),
    ("ROWBACKGROUNDS",(0,0), (-1,-1), [WHITE, LGRAY]),
    ("BACKGROUND",    (0,0), (0,-1), MINT),
    ("GRID",          (0,0), (-1,-1), 0.4, colors.HexColor("#dce3ea")),
    ("TOPPADDING",    (0,0), (-1,-1), 8),
    ("BOTTOMPADDING", (0,0), (-1,-1), 8),
    ("LEFTPADDING",   (0,0), (-1,-1), 10),
    ("RIGHTPADDING",  (0,0), (-1,-1), 10),
    ("ALIGN",         (0,0), (0,-1), "CENTER"),
    ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
]))
story.append(ns_tbl)
story.append(Spacer(1, 16))

# ── SIGNATURE ────────────────────────────────────────────────────────────────
story.append(section_header("AGREEMENT & SIGNATURE"))
story.append(Spacer(1, 10))
sig_data = [
    [
        Paragraph("<b>CLIENT</b>", style("sig_h",  fontName="Helvetica-Bold", fontSize=10, textColor=NAVY, leading=14)),
        Paragraph("<b>SERVICE PROVIDER</b>", style("sig_h2", fontName="Helvetica-Bold", fontSize=10, textColor=NAVY, leading=14)),
    ],
    [Paragraph("Name: Randy Wimmer",                          S_sign_val), Paragraph("Name: Shivanshu",                S_sign_val)],
    [Paragraph("Company: Government Contracting Academy",     S_sign_val), Paragraph("Company: BrndGuru",              S_sign_val)],
    [Paragraph("Email: Randy.Wimmer@gmail.com",               S_sign_val), Paragraph("Email: brndguruofficial@gmail.com", S_sign_val)],
    [Spacer(1, 18), Spacer(1, 18)],
    [Paragraph("Signature: ___________________________",      S_sign_label), Paragraph("Signature: ___________________________", S_sign_label)],
    [Paragraph("Date: ______________________________",        S_sign_label), Paragraph("Date: ______________________________",    S_sign_label)],
]
sig_tbl = Table(sig_data, colWidths=[3.5*inch, 3.5*inch])
sig_tbl.setStyle(TableStyle([
    ("BACKGROUND",    (0,0), (-1,0),  LGRAY),
    ("GRID",          (0,0), (-1,-1), 0.4, colors.HexColor("#dce3ea")),
    ("TOPPADDING",    (0,0), (-1,-1), 7),
    ("BOTTOMPADDING", (0,0), (-1,-1), 7),
    ("LEFTPADDING",   (0,0), (-1,-1), 12),
    ("RIGHTPADDING",  (0,0), (-1,-1), 12),
    ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
    ("LINEAFTER",     (0,0), (0,-1),  1, colors.HexColor("#dce3ea")),
]))
story.append(sig_tbl)
story.append(Spacer(1, 12))
story.append(Paragraph("Questions? Contact brndguruofficial@gmail.com  |  Proposal valid until June 10, 2026", S_note))

doc.build(story)
print("PDF generated:", OUTPUT)
