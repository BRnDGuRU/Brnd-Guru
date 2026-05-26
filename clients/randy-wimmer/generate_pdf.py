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
        ("BACKGROUND", (0,0), (-1,-1), NAVY),
        ("TOPPADDING",    (0,0), (-1,-1), 6),
        ("BOTTOMPADDING", (0,0), (-1,-1), 6),
        ("LEFTPADDING",   (0,0), (-1,-1), 10),
        ("RIGHTPADDING",  (0,0), (-1,-1), 10),
        ("ROUNDEDCORNERS",(0,0), (-1,-1), [4,4,4,4]),
    ]))
    return tbl

def agent_header(num, name, tag):
    tbl = Table([[
        Paragraph(f"<b>AGENT {num}</b>", style("ah_num", fontName="Helvetica-Bold", fontSize=14, textColor=GOLD, leading=18)),
        Paragraph(f"<b>{name}</b><br/><font size=8>{tag}</font>",
                  style("ah_name", fontName="Helvetica-Bold", fontSize=11, textColor=WHITE, leading=16)),
    ]], colWidths=[1.1*inch, 5.9*inch])
    tbl.setStyle(TableStyle([
        ("BACKGROUND", (0,0), (-1,-1), NAVY),
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
        ("BACKGROUND",    (0,0), (0,-1), LGRAY),
        ("GRID",          (0,0), (-1,-1), 0.4, colors.HexColor("#dce3ea")),
        ("TOPPADDING",    (0,0), (-1,-1), 5),
        ("BOTTOMPADDING", (0,0), (-1,-1), 5),
        ("LEFTPADDING",   (0,0), (-1,-1), 8),
        ("RIGHTPADDING",  (0,0), (-1,-1), 8),
        ("VALIGN",        (0,0), (-1,-1), "TOP"),
        ("ROWBACKGROUNDS",(0,0), (-1,-1), [WHITE, LGRAY]),
    ]))
    return tbl

def pricing_table(headers, rows, col_widths):
    data = [[Paragraph(h, style("th", fontName="Helvetica-Bold", fontSize=9, textColor=WHITE, alignment=TA_CENTER, leading=13)) for h in headers]]
    for row in rows:
        data.append([Paragraph(str(c), S_body) for c in row])
    tbl = Table(data, colWidths=col_widths)
    tbl.setStyle(TableStyle([
        ("BACKGROUND",    (0,0), (-1,0),  NAVY),
        ("ROWBACKGROUNDS",(0,1), (-1,-1), [WHITE, LGRAY]),
        ("GRID",          (0,0), (-1,-1), 0.4, colors.HexColor("#dce3ea")),
        ("TOPPADDING",    (0,0), (-1,-1), 5),
        ("BOTTOMPADDING", (0,0), (-1,-1), 5),
        ("LEFTPADDING",   (0,0), (-1,-1), 8),
        ("RIGHTPADDING",  (0,0), (-1,-1), 8),
        ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
        ("ALIGN",         (0,0), (-1,-1), "CENTER"),
    ]))
    return tbl

story = []

# ─── COVER BANNER ──────────────────────────────────────────────────────────────
cover_data = [[
    Paragraph("PROPOSAL", S_title),
    Paragraph("Govt. Contracting's Webinar-Led Revenue Infrastructure", S_subtitle),
    Paragraph("6-Agent Automation System", S_subtitle),
    Spacer(1, 6),
    Paragraph("Prepared For: &nbsp; Randy Wimmer — Government Contracting Academy", S_meta),
    Paragraph("Prepared By: &nbsp; Shivanshu — BrndGuru &nbsp;|&nbsp; brndguruofficial@gmail.com", S_meta),
    Paragraph("Date: May 26, 2026 &nbsp;|&nbsp; Valid Until: June 10, 2026", S_meta),
]]
cover_tbl = Table([cover_data[0]], colWidths=[7*inch])
cover_tbl.setStyle(TableStyle([
    ("BACKGROUND",    (0,0), (-1,-1), NAVY),
    ("TOPPADDING",    (0,0), (-1,-1), 22),
    ("BOTTOMPADDING", (0,0), (-1,-1), 22),
    ("LEFTPADDING",   (0,0), (-1,-1), 18),
    ("RIGHTPADDING",  (0,0), (-1,-1), 18),
]))
story.append(cover_tbl)
story.append(Spacer(1, 14))

# ─── CORE OUTCOME BANNER ───────────────────────────────────────────────────────
outcome_data = [[
    Paragraph("<b>CORE OUTCOME:</b>", style("co_lbl", fontName="Helvetica-Bold", fontSize=10, textColor=GOLD, leading=14)),
    Paragraph("Fill weekly webinars, convert qualified attendees into calls, and push warm leads into monthly bootcamps.",
              style("co_txt", fontName="Helvetica", fontSize=9, textColor=WHITE, leading=14)),
]]
outcome_tbl = Table([outcome_data[0]], colWidths=[1.4*inch, 5.6*inch])
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

# ─── PIPELINE ──────────────────────────────────────────────────────────────────
story.append(Paragraph("Core Revenue Pipeline:", S_h2))
pipeline_stages = ["Traffic", "Webinar\nRegistration", "Webinar\nAttendance", "Call\nBooking", "Opportunity\n& Proposal", "Closed\nDeals"]
pipeline_data = [[Paragraph(s, style("pip", fontName="Helvetica-Bold", fontSize=8, textColor=WHITE, alignment=TA_CENTER, leading=12)) for s in pipeline_stages]]
pipeline_tbl = Table(pipeline_data, colWidths=[1.17*inch]*6)
pipeline_tbl.setStyle(TableStyle([
    ("BACKGROUND",    (0,0), (0,0),  colors.HexColor("#3498db")),
    ("BACKGROUND",    (1,0), (1,0),  colors.HexColor("#2980b9")),
    ("BACKGROUND",    (2,0), (2,0),  colors.HexColor("#8e44ad")),
    ("BACKGROUND",    (3,0), (3,0),  colors.HexColor("#e67e22")),
    ("BACKGROUND",    (4,0), (4,0),  colors.HexColor("#e74c3c")),
    ("BACKGROUND",    (5,0), (5,0),  colors.HexColor("#27ae60")),
    ("TOPPADDING",    (0,0), (-1,-1), 8),
    ("BOTTOMPADDING", (0,0), (-1,-1), 8),
    ("GRID",          (0,0), (-1,-1), 1, WHITE),
    ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
]))
story.append(pipeline_tbl)
story.append(Spacer(1, 14))
story.append(HRFlowable(width="100%", thickness=1, color=colors.HexColor("#dce3ea")))
story.append(Spacer(1, 8))

# ─── EXEC SUMMARY ──────────────────────────────────────────────────────────────
story.append(section_header("EXECUTIVE SUMMARY"))
story.append(Spacer(1, 8))
story.append(Paragraph(
    "Randy Wimmer has built decades of credibility in the federal contracting space — an 8-figure exit, $1B+ in awards led, and a mission to help the next generation of GovCon founders break in. This proposal outlines a <b>6-Agent Automation Infrastructure</b> — a fully integrated, system-driven revenue engine to generate consistent, scalable monthly revenue from ISO certification packages ($20K–$24K).",
    S_body))
story.append(Spacer(1, 6))
story.append(two_col_table([
    ("Total Agency Fee",    "$900/month ($800 service + $100 AI automation)"),
    ("Variable Tool Costs", "~$205–$705/month (GHL, HeyReach, Sendr.io, Twain.ai)"),
    ("LinkedIn Ads Budget", "$500–$1,500/month (client-funded, separate)"),
    ("Delivery Timeline",   "3 months — 2 agents per month"),
    ("Payment Structure",   "50% upfront / 50% on milestone completion"),
]))
story.append(Spacer(1, 14))

# ─── THE 6 AGENTS ──────────────────────────────────────────────────────────────
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
    "Webinar delivery (GoHighLevel + Zoom integration)",
    "Replay delivery automation for no-shows",
    "No-show recovery sequence",
    "Post-webinar follow-up (call booking CTA)",
    "Workshop / Bootcamp upsell sequence",
]:
    story.append(bullet(item))
story.append(Spacer(1, 4))
goal_tbl = Table([[Paragraph("<b>Goal:</b> Maximize registrations, attendance rate, and call bookings from every webinar.", S_body)]], colWidths=[7*inch])
goal_tbl.setStyle(TableStyle([("BACKGROUND",(0,0),(-1,-1),LGRAY),("TOPPADDING",(0,0),(-1,-1),6),("BOTTOMPADDING",(0,0),(-1,-1),6),("LEFTPADDING",(0,0),(-1,-1),10),("RIGHTPADDING",(0,0),(-1,-1),10)]))
story.append(goal_tbl)
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
    "Audience targeting (ICP-matched GovCon founders)",
    "Creative testing (3–5 ad variations per campaign)",
    "CPL & CPA tracking setup",
    "Cost per registrant / attendee / qualified booking monitoring",
    "Weekly campaign optimization",
    "Retargeting (website visitors, webinar no-shows)",
    "Lookalike audience creation from existing list",
    "Attribution & reporting (source tracking across all touchpoints)",
]:
    story.append(bullet(item))
story.append(Spacer(1, 4))
goal_tbl2 = Table([[Paragraph("<b>Goal:</b> Lower cost per qualified webinar attendee and scale volume profitably.", S_body)]], colWidths=[7*inch])
goal_tbl2.setStyle(TableStyle([("BACKGROUND",(0,0),(-1,-1),LGRAY),("TOPPADDING",(0,0),(-1,-1),6),("BOTTOMPADDING",(0,0),(-1,-1),6),("LEFTPADDING",(0,0),(-1,-1),10),("RIGHTPADDING",(0,0),(-1,-1),10)]))
story.append(goal_tbl2)
story.append(Spacer(1, 12))

# Agent 3
story.append(KeepTogether([
    agent_header(3, "LINKEDIN CONTENT AUTHORITY AGENT", "Authority Engine | MONTH 2"),
    Spacer(1, 6),
    Paragraph("Builds Randy's organic presence and keeps the audience warm between webinars and outreach.", S_body),
    Spacer(1, 4),
]))
for item in [
    "Founder profile posts (3–5/week, authority-building)",
    "Company page posts (GCA + ISO Certification Group)",
    "Webinar promotion posts (every cycle)",
    "Authority / thought leadership content",
    "Social proof / case study posts",
    "Reels / short-form video snippets (repurposed from webinar recordings)",
    "Newsletter content (The GovCon Times + The Good Enough Entrepreneur)",
    "Bootcamp promotion posts",
    "Content calendar automation (scheduled, consistent posting)",
]:
    story.append(bullet(item))
story.append(Spacer(1, 4))
goal_tbl3 = Table([[Paragraph("<b>Goal:</b> Build authority, trust, and organic visibility so inbound demand supplements outbound.", S_body)]], colWidths=[7*inch])
goal_tbl3.setStyle(TableStyle([("BACKGROUND",(0,0),(-1,-1),LGRAY),("TOPPADDING",(0,0),(-1,-1),6),("BOTTOMPADDING",(0,0),(-1,-1),6),("LEFTPADDING",(0,0),(-1,-1),10),("RIGHTPADDING",(0,0),(-1,-1),10)]))
story.append(goal_tbl3)
story.append(Spacer(1, 12))

# Agent 4
story.append(KeepTogether([
    agent_header(4, "LINKEDIN RELATIONSHIP ACTIVATION + AI REPLY SYSTEM", "Relationship Engine | MONTH 2"),
    Spacer(1, 6),
    Paragraph("Activates the 21K+ existing network and builds new high-intent connections — with AI-assisted replies to escalate hot leads.", S_body),
    Spacer(1, 4),
]))
story.append(Paragraph("<b>A. Existing Network Reactivation</b>", S_h3))
for item in ["Reconnect with 21K+ existing connections (segmented outreach)","Identify and tag warm leads from existing network","Invite engaged contacts to upcoming webinars","Move warm contacts into nurture pipeline"]:
    story.append(bullet(item))
story.append(Paragraph("<b>B. New Connection Growth Engine</b>", S_h3))
for item in ["Target ICP: Compliance, AI Governance, ISO, Security, Risk Leaders","Decision makers at target GovCon companies","300–500 new connection requests/month (HeyReach)","High-intent connection messaging strategy"]:
    story.append(bullet(item))
story.append(Paragraph("<b>C. AI Reply Assist (Human-in-the-Loop)</b>", S_h3))
for item in ["Detect buying intent signals in DM replies","Detect webinar interest in conversations","AI-drafted reply suggestions (reviewed before sending)","Escalate hot leads to Randy's calendar immediately"]:
    story.append(bullet(item))
story.append(Spacer(1, 4))
goal_tbl4 = Table([[Paragraph("<b>Goal:</b> Activate relationships and book more webinars through direct, personal LinkedIn outreach.", S_body)]], colWidths=[7*inch])
goal_tbl4.setStyle(TableStyle([("BACKGROUND",(0,0),(-1,-1),LGRAY),("TOPPADDING",(0,0),(-1,-1),6),("BOTTOMPADDING",(0,0),(-1,-1),6),("LEFTPADDING",(0,0),(-1,-1),10),("RIGHTPADDING",(0,0),(-1,-1),10)]))
story.append(goal_tbl4)
story.append(Spacer(1, 12))

# Agent 5
story.append(KeepTogether([
    agent_header(5, "EMAIL INTENT & NURTURE ENGINE", "Intent Engine | MONTH 3"),
    Spacer(1, 6),
    Paragraph("Activates the 25K verified GovCon email list. Drives registrations, nurtures leads, and re-engages cold contacts.", S_body),
    Spacer(1, 4),
]))
for item in [
    "Webinar reminder email sequences (integrated with GHL)",
    "Pre-webinar nurture (value delivery before the session)",
    "Replay & follow-up sequences (no-show recovery via email)",
    "High-intent outbound campaigns via Twain.ai (300–500 hot leads/month)",
    "Post-webinar nurture (multi-step follow-up to book calls)",
    "Bootcamp / offer upsell sequences",
    "Reactivation campaigns (dormant list segments)",
    "Intent scoring & segmentation (tag leads by engagement level)",
]:
    story.append(bullet(item))
story.append(Spacer(1, 4))
goal_tbl5 = Table([[Paragraph("<b>Goal:</b> Nurture, re-engage, and convert attendees and list members into qualified sales calls.", S_body)]], colWidths=[7*inch])
goal_tbl5.setStyle(TableStyle([("BACKGROUND",(0,0),(-1,-1),LGRAY),("TOPPADDING",(0,0),(-1,-1),6),("BOTTOMPADDING",(0,0),(-1,-1),6),("LEFTPADDING",(0,0),(-1,-1),10),("RIGHTPADDING",(0,0),(-1,-1),10)]))
story.append(goal_tbl5)
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
goal_tbl6 = Table([[Paragraph("<b>Goal:</b> Full visibility, insights, and revenue performance control across all channels.", S_body)]], colWidths=[7*inch])
goal_tbl6.setStyle(TableStyle([("BACKGROUND",(0,0),(-1,-1),LGRAY),("TOPPADDING",(0,0),(-1,-1),6),("BOTTOMPADDING",(0,0),(-1,-1),6),("LEFTPADDING",(0,0),(-1,-1),10),("RIGHTPADDING",(0,0),(-1,-1),10)]))
story.append(goal_tbl6)
story.append(Spacer(1, 14))

# ─── TIMELINE ──────────────────────────────────────────────────────────────────
story.append(section_header("IMPLEMENTATION TIMELINE"))
story.append(Spacer(1, 8))
story.append(pricing_table(
    ["Phase", "Month", "Agents", "Focus"],
    [
        ["Phase 1", "Month 1", "Agent 1 + Agent 2", "Build the core conversion path"],
        ["Phase 2", "Month 2", "Agent 3 + Agent 4", "Layer in authority & relationship engine"],
        ["Phase 3", "Month 3", "Agent 5 + Agent 6", "Activate email engine + full dashboard"],
    ],
    [1*inch, 1*inch, 2*inch, 3*inch]
))
story.append(Spacer(1, 6))
story.append(Paragraph(
    "<i>Strategic Principle: Perfect the core conversion path first — Traffic → Webinar → Call → Close. Then scale everything.</i>",
    S_note))
story.append(Spacer(1, 14))

# ─── INVESTMENT ────────────────────────────────────────────────────────────────
story.append(section_header("INVESTMENT & PRICING"))
story.append(Spacer(1, 8))

story.append(Paragraph("Monthly Agency Fee", S_h2))
story.append(pricing_table(
    ["Item", "Monthly Cost"],
    [
        ["Service Management Fee", "$800"],
        ["AI Automation (Claude)", "$100"],
        ["Total Agency Fee", "$900"],
    ],
    [4.5*inch, 2.5*inch]
))
story.append(Spacer(1, 10))

story.append(Paragraph("Payment Schedule", S_h2))
story.append(pricing_table(
    ["Milestone", "Amount", "Due"],
    [
        ["Month 1 Kickoff (50%)", "$450", "Before work begins"],
        ["Month 1 Completion (50%)", "$450", "Upon Agent 1 + 2 live"],
        ["Month 2 Ongoing", "$900", "Month 2 start"],
        ["Month 3 Ongoing", "$900", "Month 3 start"],
    ],
    [3*inch, 1.5*inch, 2.5*inch]
))
story.append(Spacer(1, 10))

story.append(Paragraph("Tool Costs — Variable (Billed Directly to Client)", S_h2))
story.append(Paragraph("BrndGuru does not mark up tool costs. You are billed directly by each provider.", S_body))
story.append(Spacer(1, 4))
story.append(pricing_table(
    ["Tool", "Purpose", "Est. Monthly Cost"],
    [
        ["GoHighLevel", "CRM, funnels, email automation", "$97 (already subscribed)"],
        ["Zoom Webinars", "Webinar hosting", "Existing subscription"],
        ["HeyReach", "LinkedIn connection & messaging", "~$39 (with coupon)"],
        ["Sendr.io", "Personalized LinkedIn landing pages", "~$69 (with coupon)"],
        ["Twain.ai", "AI-personalized email research", "~$1/lead ($300–600/month)"],
        ["LinkedIn Ads Budget", "Paid acquisition (Agent 2)", "$500–$1,500 (recommended)"],
    ],
    [1.8*inch, 2.7*inch, 2.5*inch]
))
story.append(Spacer(1, 10))

story.append(Paragraph("Total Monthly Investment Summary", S_h2))
story.append(pricing_table(
    ["Category", "Range"],
    [
        ["Agency Fee (services + AI)", "$900"],
        ["Tools & Platforms", "~$205–$705"],
        ["LinkedIn Ads Budget", "$500–$1,500"],
        ["Total Monthly Estimate", "~$1,605–$3,105/month"],
    ],
    [4*inch, 3*inch]
))
story.append(Spacer(1, 6))
story.append(Paragraph("*Ad budget is the primary variable. Start at $500/month and scale as ROAS improves.", S_note))
story.append(Spacer(1, 14))

# ─── KPIs ──────────────────────────────────────────────────────────────────────
story.append(section_header("SUCCESS METRICS & KPIs"))
story.append(Spacer(1, 8))
kpi_data = [
    ["Agent", "KPI", "Target"],
    ["Webinar Funnel",       "Show-up rate",                    "40%+"],
    ["Webinar Funnel",       "Calls booked per webinar",         "3–5+"],
    ["Paid Acquisition",     "Cost per registrant",              "<$15"],
    ["Paid Acquisition",     "Cost per qualified call booking",  "<$150"],
    ["LinkedIn Content",     "Engagement rate",                  "3–5%+"],
    ["LinkedIn Relationship","Connection acceptance rate",        "25–40%"],
    ["LinkedIn Relationship","Calls booked from DMs",            "5+/month"],
    ["Email Engine",         "Open rate",                        "30–45%"],
    ["Email Engine",         "Calls booked from sequences",      "10+/month"],
]
kpi_tbl = Table(kpi_data, colWidths=[2.2*inch, 3.3*inch, 1.5*inch])
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
    ("ALIGN",         (2,0), (2,-1),  "CENTER"),
    ("TEXTCOLOR",     (2,1), (2,-1),  MINT),
    ("FONTNAME",      (2,1), (2,-1),  "Helvetica-Bold"),
]))
story.append(kpi_tbl)
story.append(Spacer(1, 14))

# ─── TERMS ─────────────────────────────────────────────────────────────────────
story.append(section_header("TERMS & CONDITIONS"))
story.append(Spacer(1, 8))
terms = [
    ("1. Engagement Model", "Monthly retainer. Either party may terminate with 14 days written notice after Month 1 completion."),
    ("2. Payment Terms",    "50% upfront before work begins each phase; 50% on milestone completion."),
    ("3. Revisions",        "Up to 2 rounds of revisions per deliverable included."),
    ("4. Tool Costs",       "All third-party platform costs are the client's responsibility, billed directly by providers."),
    ("5. Ad Budget",        "LinkedIn ad spend is managed by BrndGuru but funded directly from client's ad account. Budget decisions remain with the client."),
    ("6. Confidentiality",  "All client credentials, data, and business information handled with full confidentiality."),
    ("7. Intellectual Property", "All work product is owned by the client upon full payment."),
    ("8. Timeline",         "Deliverable timelines are dependent on timely receipt of required assets and access from the client."),
]
story.append(two_col_table(terms, col_widths=(2*inch, 5*inch)))
story.append(Spacer(1, 14))

# ─── NEXT STEPS ────────────────────────────────────────────────────────────────
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
    ("FONTNAME",      (0,0), (0,-1), "Helvetica-Bold"),
    ("FONTSIZE",      (0,0), (-1,-1), 9),
    ("TEXTCOLOR",     (0,0), (0,-1), WHITE),
    ("ROWBACKGROUNDS",(1,0), (1,-1), [WHITE, LGRAY]),
    ("ROWBACKGROUNDS",(0,0), (0,-1), [MINT]),
    ("GRID",          (0,0), (-1,-1), 0.4, colors.HexColor("#dce3ea")),
    ("TOPPADDING",    (0,0), (-1,-1), 8),
    ("BOTTOMPADDING", (0,0), (-1,-1), 8),
    ("LEFTPADDING",   (0,0), (-1,-1), 10),
    ("RIGHTPADDING",  (0,0), (-1,-1), 10),
    ("ALIGN",         (0,0), (0,-1), "CENTER"),
    ("VALIGN",        (0,0), (-1,-1), "MIDDLE"),
    ("ROWBACKGROUNDS",(0,0), (-1,-1), [WHITE, LGRAY]),
]))
# Override first column
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

# ─── SIGNATURE ─────────────────────────────────────────────────────────────────
story.append(section_header("AGREEMENT & SIGNATURE"))
story.append(Spacer(1, 10))
sig_data = [
    [
        Paragraph("<b>CLIENT</b>", style("sig_h", fontName="Helvetica-Bold", fontSize=10, textColor=NAVY, leading=14)),
        Paragraph("<b>SERVICE PROVIDER</b>", style("sig_h2", fontName="Helvetica-Bold", fontSize=10, textColor=NAVY, leading=14)),
    ],
    [
        Paragraph("Name: Randy Wimmer", S_sign_val),
        Paragraph("Name: Shivanshu", S_sign_val),
    ],
    [
        Paragraph("Company: Government Contracting Academy", S_sign_val),
        Paragraph("Company: BrndGuru", S_sign_val),
    ],
    [
        Paragraph("Email: Randy.Wimmer@gmail.com", S_sign_val),
        Paragraph("Email: brndguruofficial@gmail.com", S_sign_val),
    ],
    [
        Spacer(1, 20),
        Spacer(1, 20),
    ],
    [
        Paragraph("Signature: ___________________________", S_sign_label),
        Paragraph("Signature: ___________________________", S_sign_label),
    ],
    [
        Paragraph("Date: ______________________________", S_sign_label),
        Paragraph("Date: ______________________________", S_sign_label),
    ],
]
sig_tbl = Table(sig_data, colWidths=[3.5*inch, 3.5*inch])
sig_tbl.setStyle(TableStyle([
    ("BACKGROUND",    (0,0), (-1,0),  LGRAY),
    ("FONTNAME",      (0,0), (-1,0),  "Helvetica-Bold"),
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
