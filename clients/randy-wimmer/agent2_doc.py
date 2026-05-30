from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import ParagraphStyle
from reportlab.lib.units import inch
from reportlab.lib import colors
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle,
    KeepTogether, PageBreak
)
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.graphics.shapes import Drawing, Rect, String, Line, Polygon, Circle
from reportlab.graphics import renderPDF

ORANGE = colors.HexColor("#FF6600")
NAVY   = colors.HexColor("#1e3a5f")
LGRAY  = colors.HexColor("#f4f6f9")
DGRAY  = colors.HexColor("#2c3e50")
WHITE  = colors.white
BLACK  = colors.HexColor("#111111")
GREEN  = colors.HexColor("#27ae60")
LGREEN = colors.HexColor("#eafaf1")
RED    = colors.HexColor("#e74c3c")
LRED   = colors.HexColor("#fdecea")
BLUE   = colors.HexColor("#2980b9")
LBLUE  = colors.HexColor("#ebf5fb")
GRAY   = colors.HexColor("#888888")
PURPLE = colors.HexColor("#8e44ad")
PINK   = colors.HexColor("#ea4b71")
TEAL   = colors.HexColor("#16a085")

OUTPUT = "/home/user/Brnd-Guru/clients/randy-wimmer/Agent2_Paid_Acquisition.pdf"

doc = SimpleDocTemplate(
    OUTPUT, pagesize=letter,
    leftMargin=0.75*inch, rightMargin=0.75*inch,
    topMargin=0.88*inch, bottomMargin=0.65*inch,
)

def s(name, **kw):
    return ParagraphStyle(name, **kw)

S_body = s("bd", fontName="Helvetica",        fontSize=10, textColor=DGRAY, leading=15, spaceAfter=4)
S_note = s("nt", fontName="Helvetica-Oblique", fontSize=8,  textColor=GRAY,  leading=12, alignment=TA_CENTER)

def banner(title):
    tbl = Table([[
        "",
        Paragraph(title, s("bt", fontName="Helvetica-Bold", fontSize=13,
                            textColor=WHITE, leading=18))
    ]], colWidths=[0.08*inch, 6.92*inch])
    tbl.setStyle(TableStyle([
        ("BACKGROUND", (0,0),(0,-1), ORANGE),
        ("BACKGROUND", (1,0),(1,-1), NAVY),
        ("TOPPADDING",    (0,0),(-1,-1), 10),
        ("BOTTOMPADDING", (0,0),(-1,-1), 10),
        ("LEFTPADDING",   (1,0),(1,-1), 12),
        ("VALIGN",        (0,0),(-1,-1), "MIDDLE"),
    ]))
    return tbl

# ── CANVAS CALLBACKS ──────────────────────────────────────────────────────────
def cover_draw(canvas, doc):
    W, H = letter
    canvas.saveState()
    canvas.setFillColor(BLACK)
    canvas.rect(0, 0, W, H, fill=1, stroke=0)

    # Corner triangles
    for pts, col in [
        ([(W,H),(W-220,H),(W,H-220)], ORANGE),
        ([(W,H),(W-110,H),(W,H-110)], colors.HexColor("#CC5200")),
        ([(0,0),(130,0),(0,130)],      ORANGE),
    ]:
        p = canvas.beginPath()
        p.moveTo(*pts[0]); p.lineTo(*pts[1]); p.lineTo(*pts[2])
        p.close(); canvas.drawPath(p, fill=1, stroke=0)

    # Left edge bar
    canvas.setFillColor(ORANGE)
    canvas.rect(0, H*0.22, 5, H*0.52, fill=1, stroke=0)

    # Wordmark
    bx, by = 52, H - 90
    canvas.setFont("Helvetica-Bold", 40)
    canvas.setFillColor(WHITE)
    bw = canvas.stringWidth("BRND", "Helvetica-Bold", 40)
    canvas.drawString(bx, by, "BRND")
    canvas.setFillColor(ORANGE)
    canvas.drawString(bx + bw, by, "GURU")
    canvas.setFont("Helvetica", 10)
    canvas.setFillColor(colors.HexColor("#aaaaaa"))
    canvas.drawString(bx, by - 18, "AI-Powered Revenue Systems")
    canvas.setStrokeColor(ORANGE); canvas.setLineWidth(1.5)
    canvas.line(bx, by - 32, W - 52, by - 32)

    # Agent label
    canvas.setFont("Helvetica-Bold", 9)
    canvas.setFillColor(ORANGE)
    canvas.drawString(bx, H - 165, "A G E N T   2   —   D E M A N D   E N G I N E")

    # Main title
    canvas.setFont("Helvetica-Bold", 42)
    canvas.setFillColor(WHITE)
    canvas.drawString(bx, H - 210, "Paid Acquisition")
    canvas.setFillColor(ORANGE)
    canvas.drawString(bx, H - 258, "Agent")
    canvas.setFont("Helvetica", 13)
    canvas.setFillColor(colors.HexColor("#aaaaaa"))
    canvas.drawString(bx, H - 284, "LinkedIn Ads + AI Creative Generation — Running 24/7")

    # Orange rule
    canvas.setFillColor(ORANGE)
    canvas.rect(bx, H - 304, W - bx - 52, 2, fill=1, stroke=0)

    # What this agent does box
    bw2   = W - bx - 52
    box_y = H - 422
    box_h = 108
    canvas.setFillColor(colors.HexColor("#1a1a1a"))
    canvas.rect(bx, box_y, bw2, box_h, fill=1, stroke=0)
    canvas.setFillColor(ORANGE)
    canvas.rect(bx, box_y + box_h - 22, bw2, 22, fill=1, stroke=0)
    canvas.setStrokeColor(ORANGE); canvas.setLineWidth(1)
    canvas.rect(bx, box_y, bw2, box_h, fill=0, stroke=1)
    canvas.setFont("Helvetica-Bold", 8.5)
    canvas.setFillColor(WHITE)
    canvas.drawString(bx + 12, box_y + box_h - 15, "W H A T   T H I S   A G E N T   D O E S :")
    canvas.setFont("Helvetica", 11)
    canvas.setFillColor(WHITE)
    for i, line in enumerate([
        "Fills your webinar pipeline with qualified leads,",
        "generates AI video ads automatically, and retargets",
        "every no-show — all on autopilot.",
    ]):
        canvas.drawString(bx + 12, box_y + box_h - 44 - i*18, line)

    # Prepared for
    canvas.setFont("Helvetica-Bold", 8)
    canvas.setFillColor(colors.HexColor("#aaaaaa"))
    canvas.drawString(bx, H - 448, "P R E P A R E D   F O R")
    canvas.setFont("Helvetica-Bold", 20)
    canvas.setFillColor(WHITE)
    canvas.drawString(bx, H - 470, "Randy Wimmer")
    canvas.setFont("Helvetica", 10)
    canvas.setFillColor(colors.HexColor("#888888"))
    canvas.drawString(bx, H - 488, "Government Contracting Academy / ISO Certification Group")

    # 3 promise boxes
    box_w = (W - bx - 52) / 3
    canvas.setFillColor(colors.HexColor("#1a1a1a"))
    canvas.rect(bx, H - 570, W - bx - 52, 60, fill=1, stroke=0)
    for i, (t, sub) in enumerate([
        ("AI-Generated","Zero design work"),
        ("Fully Tracked","Every dollar measured"),
        ("Self-Optimizing","Pauses losers automatically"),
    ]):
        cx = bx + i * box_w + box_w / 2
        if i > 0:
            canvas.setStrokeColor(colors.HexColor("#333333")); canvas.setLineWidth(0.5)
            canvas.line(bx + i*box_w, H-570, bx + i*box_w, H-510)
        canvas.setFont("Helvetica-Bold", 11); canvas.setFillColor(ORANGE)
        canvas.drawCentredString(cx, H - 536, t)
        canvas.setFont("Helvetica", 8); canvas.setFillColor(colors.HexColor("#aaaaaa"))
        canvas.drawCentredString(cx, H - 552, sub)

    # Footer
    canvas.setFillColor(colors.HexColor("#080808"))
    canvas.rect(0, 0, W, 44, fill=1, stroke=0)
    canvas.setFont("Helvetica-Bold", 9); canvas.setFillColor(ORANGE)
    canvas.drawString(50, 16, "brndguruofficial@gmail.com")
    canvas.setFont("Helvetica", 8); canvas.setFillColor(colors.HexColor("#666666"))
    canvas.drawRightString(W - 50, 16, "Government Contracting Academy — Agent 2 Breakdown")
    canvas.restoreState()

def later_pages(canvas, doc):
    W, H = letter
    canvas.saveState()
    canvas.setFillColor(colors.HexColor("#f8f8f8"))
    canvas.rect(0, H - 28, W, 28, fill=1, stroke=0)
    canvas.setFillColor(ORANGE); canvas.rect(0, H - 30, W, 2, fill=1, stroke=0)
    canvas.setFont("Helvetica-Bold", 8); canvas.setFillColor(ORANGE)
    canvas.drawString(0.75*inch, H - 18, "BRNDGURU")
    canvas.setFont("Helvetica", 8); canvas.setFillColor(GRAY)
    canvas.drawString(0.75*inch + 58, H - 18, "Agent 2 — Paid Acquisition Agent")
    canvas.drawRightString(W - 0.75*inch, H - 18, f"Page {doc.page}")
    canvas.setFillColor(colors.HexColor("#f0f0f0"))
    canvas.rect(0, 0, W, 26, fill=1, stroke=0)
    canvas.setFillColor(ORANGE); canvas.rect(0, 26, W, 1.5, fill=1, stroke=0)
    canvas.setFont("Helvetica", 7.5); canvas.setFillColor(GRAY)
    canvas.drawCentredString(W/2, 8, "Confidential — Prepared for Randy Wimmer | Government Contracting Academy | BrndGuru")
    canvas.restoreState()

# ── STORY ─────────────────────────────────────────────────────────────────────
story = []
story.append(PageBreak())

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 2 — WHAT IS AGENT 2
# ══════════════════════════════════════════════════════════════════════════════
story.append(banner("WHAT IS AGENT 2?"))
story.append(Spacer(1, 10))
story.append(Paragraph(
    "<b>Agent 2 is your traffic engine.</b><br/><br/>"
    "Agent 1 runs the webinar funnel perfectly — but it needs people flowing into it. "
    "Agent 2 runs LinkedIn Ads targeting the exact right people: small business owners who are "
    "registered federal contractors and need ISO certification to win more government contracts. "
    "It generates AI video creatives automatically, optimizes spend weekly, and retargets every "
    "no-show from Agent 1. No manual ad work. No design agency. No wasted budget.",
    s("intro", fontName="Helvetica", fontSize=10.5, textColor=DGRAY, leading=17, spaceAfter=12)))

# Before / After
ba = Table([
    [Paragraph("WITHOUT AGENT 2", s("bh", fontName="Helvetica-Bold", fontSize=11, textColor=WHITE, leading=15, alignment=TA_CENTER)),
     Paragraph("WITH AGENT 2",    s("ah", fontName="Helvetica-Bold", fontSize=11, textColor=WHITE, leading=15, alignment=TA_CENTER))],
    [Paragraph("✗  Manually design every ad creative\n✗  No idea which ad is working\n✗  No-shows disappear forever\n✗  Spend budget with no tracking\n✗  Start from scratch every webinar",
               s("bc", fontName="Helvetica", fontSize=9.5, textColor=RED, leading=17)),
     Paragraph("✓  AI generates video ads automatically\n✓  Every dollar tracked to registrations\n✓  No-shows get retargeted for next webinar\n✓  Underperforming ads auto-paused\n✓  New creatives live in 8 minutes",
               s("ac", fontName="Helvetica", fontSize=9.5, textColor=GREEN, leading=17))],
], colWidths=[3.5*inch, 3.5*inch])
ba.setStyle(TableStyle([
    ("BACKGROUND",    (0,0),(0,0), RED),
    ("BACKGROUND",    (1,0),(1,0), GREEN),
    ("BACKGROUND",    (0,1),(0,1), LRED),
    ("BACKGROUND",    (1,1),(1,1), LGREEN),
    ("GRID",          (0,0),(-1,-1), 0.5, colors.HexColor("#dddddd")),
    ("TOPPADDING",    (0,0),(-1,-1), 10),
    ("BOTTOMPADDING", (0,0),(-1,-1), 10),
    ("LEFTPADDING",   (0,0),(-1,-1), 12),
    ("VALIGN",        (0,0),(-1,-1), "TOP"),
]))
story.append(ba)
story.append(Spacer(1, 14))

# Stat cards
story.append(banner("WHAT IT TARGETS"))
story.append(Spacer(1, 10))
stats = [
    ("<$15",  "Cost Per\nRegistrant",    ORANGE),
    ("<$150", "Cost Per\nCall Booking",  GREEN),
    ("5",     "AI Videos\nPer Cycle",    BLUE),
    ("$0",    "Design\nAgency Cost",     NAVY),
]
r1 = [Paragraph(n, s(f"n{i}", fontName="Helvetica-Bold", fontSize=22, textColor=c, leading=28, alignment=TA_CENTER)) for i,(n,l,c) in enumerate(stats)]
r2 = [Paragraph(l, s(f"l{i}", fontName="Helvetica",      fontSize=8,  textColor=DGRAY, leading=11, alignment=TA_CENTER)) for i,(n,l,c) in enumerate(stats)]
st = Table([r1, r2], colWidths=[1.75*inch]*4)
st.setStyle(TableStyle([
    ("GRID",          (0,0),(-1,-1), 0.5, colors.HexColor("#dddddd")),
    ("BACKGROUND",    (0,0),(-1,-1), LGRAY),
    ("TOPPADDING",    (0,0),(-1,0), 14), ("BOTTOMPADDING",(0,0),(-1,0), 4),
    ("TOPPADDING",    (0,1),(-1,1), 2),  ("BOTTOMPADDING",(0,1),(-1,1), 14),
    ("VALIGN",        (0,0),(-1,-1), "MIDDLE"),
]))
story.append(st)

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 3 — THE 5 PHASES
# ══════════════════════════════════════════════════════════════════════════════
story.append(PageBreak())
story.append(banner("THE 5 PHASES OF AGENT 2"))
story.append(Spacer(1, 8))
story.append(Paragraph(
    "Agent 2 runs across 5 distinct phases — from initial setup to weekly self-optimization. "
    "Once built, only Phase 1 requires human involvement.",
    S_body))
story.append(Spacer(1, 10))

def make_phases():
    phases = [
        ("1", "SETUP",       "#2c3e50"),
        ("2", "PRE-WEBINAR", "#FF6600"),
        ("3", "ADS RUNNING", "#2980b9"),
        ("4", "RETARGETING", "#8e44ad"),
        ("5", "REPORTING",   "#27ae60"),
    ]
    TW = 504; n = len(phases); aw = 14
    bw = (TW - (n-1)*aw) / n; bh = 54
    d = Drawing(TW, bh + 14)
    for i, (num, lbl, col) in enumerate(phases):
        x = i*(bw+aw)
        d.add(Rect(x, 10, bw, bh, fillColor=colors.HexColor(col), strokeWidth=0))
        d.add(String(x+7, 10+bh-16, num, fontName="Helvetica-Bold", fontSize=11, fillColor=WHITE))
        lines = lbl.split('\n')
        mid = 10 + bh/2
        d.add(String(x+bw/2, mid-5, lbl, fontName="Helvetica-Bold", fontSize=8, fillColor=WHITE, textAnchor="middle"))
        if i < n-1:
            ax = x+bw; ay = 10+bh/2
            d.add(Polygon([ax,ay+8, ax+aw-1,ay, ax,ay-8], fillColor=colors.HexColor(col), strokeWidth=0))
    return d

story.append(make_phases())
story.append(Spacer(1, 14))

phases_detail = [
    ("1", "SETUP",       "#2c3e50", "One-time build by BrndGuru. LinkedIn Campaign Manager configured, audience targeting defined (GovCon founders, 1–50 employees, SAM.gov registered), GHL landing page built with Custom Values, LinkedIn Insight Tag pixel installed, VPS connections wired (Higgsfield + Claude + LinkedIn APIs)."),
    ("2", "PRE-WEBINAR", "#FF6600", "Triggered automatically when Randy creates a new Zoom webinar. n8n reads the topic, date, and time → updates the GHL landing page instantly → calls Claude AI to write video prompts → calls Higgsfield API to generate 5 B-roll clips → FFmpeg assembles the final video ad → uploads everything to LinkedIn. Takes ~8 minutes. Zero manual work."),
    ("3", "ADS RUNNING", "#2980b9", "LinkedIn campaigns run with all creatives live. LinkedIn Insight Tag tracks every registration back to the exact ad. Every 7 days, n8n pulls performance data → auto-pauses creatives with CTR below 0.3% → shifts budget to winners → alerts BrndGuru if CPL exceeds $20."),
    ("4", "RETARGETING", "#8e44ad", "Agent 1 tags no-shows → GHL webhook → n8n → adds emails to LinkedIn 'No-Show' audience → separate retargeting campaign fires. Website visitors who didn't register are captured by the Insight Tag pixel and shown ads automatically. Lookalike audience built from Randy's 300+ contacts in GHL."),
    ("5", "REPORTING",   "#27ae60", "Every Monday at 8AM, n8n pulls LinkedIn Ads API + GHL pipeline data → formats weekly report → pushes to Agent 6 dashboard → emails Randy and BrndGuru: registrations, CPL, calls booked, revenue attributed to LinkedIn. Full attribution from ad click to closed deal."),
]
for num, stage, col, desc in phases_detail:
    tbl = Table([[
        Paragraph(num, s(f"pn{num}", fontName="Helvetica-Bold", fontSize=15, textColor=WHITE, leading=20, alignment=TA_CENTER)),
        [Paragraph(stage, s(f"ph{num}", fontName="Helvetica-Bold", fontSize=10, textColor=colors.HexColor(col), leading=14, spaceAfter=2)),
         Paragraph(desc,  s(f"pd{num}", fontName="Helvetica",      fontSize=9,  textColor=DGRAY, leading=13))],
    ]], colWidths=[0.45*inch, 6.55*inch])
    tbl.setStyle(TableStyle([
        ("BACKGROUND",    (0,0),(0,-1), colors.HexColor(col)),
        ("BACKGROUND",    (1,0),(1,-1), LGRAY if int(num)%2==0 else WHITE),
        ("TOPPADDING",    (0,0),(-1,-1), 8), ("BOTTOMPADDING",(0,0),(-1,-1), 8),
        ("LEFTPADDING",   (1,0),(1,-1), 10), ("RIGHTPADDING", (0,0),(-1,-1), 8),
        ("VALIGN",        (0,0),(-1,-1), "MIDDLE"),
        ("BOX",           (0,0),(-1,-1), 0.5, colors.HexColor("#dddddd")),
    ]))
    story.append(tbl)
    story.append(Spacer(1, 4))

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 4 — AI CREATIVE PIPELINE
# ══════════════════════════════════════════════════════════════════════════════
story.append(PageBreak())
story.append(banner("THE AI CREATIVE PIPELINE"))
story.append(Spacer(1, 8))
story.append(Paragraph(
    "Every webinar cycle, the system generates fresh LinkedIn ad creatives automatically — "
    "no design agency, no manual work. Here is the exact step-by-step flow:",
    S_body))
story.append(Spacer(1, 10))

def make_creative_pipeline():
    TW = 504; bh = 48; bw = 68; gap = 10
    nodes = [
        ("ZOOM\nWEBHOOK",  "#e67e22"),
        ("n8n\nON VPS",    "#2c3e50"),
        ("CLAUDE\nAI",     "#2980b9"),
        ("HIGGSFIELD\nAPI", "#ea4b71"),
        ("FFmpeg\nVPS",    "#16a085"),
        ("LINKEDIN\nADS",  "#0077b5"),
    ]
    unit = bw + gap + 10
    d = Drawing(TW, bh + 30)
    for i, (lbl, col) in enumerate(nodes):
        x = i * unit
        c = colors.HexColor(col)
        d.add(Rect(x, 16, bw, bh, fillColor=c, strokeWidth=0))
        ll = lbl.split("\n")
        cy = 16 + bh/2
        d.add(String(x+bw/2, cy+3,  ll[0], fontName="Helvetica-Bold", fontSize=7, fillColor=WHITE, textAnchor="middle"))
        d.add(String(x+bw/2, cy-8,  ll[1], fontName="Helvetica-Bold", fontSize=7, fillColor=WHITE, textAnchor="middle"))
        if i < len(nodes)-1:
            ax = x + bw; ay = 16 + bh/2
            d.add(Polygon([ax,ay+6, ax+gap+9,ay, ax,ay-6], fillColor=c, strokeWidth=0))
        # Step number
        d.add(String(x+bw/2, 4, str(i+1), fontName="Helvetica-Bold", fontSize=8, fillColor=colors.HexColor(col), textAnchor="middle"))
    return d

story.append(make_creative_pipeline())
story.append(Spacer(1, 12))

steps = [
    ("1", "ZOOM WEBHOOK",   "#e67e22", "Randy creates a new Zoom Webinar → Zoom fires a webhook to n8n on the VPS instantly. Payload includes: webinar topic, date, time, and Zoom Webinar ID."),
    ("2", "n8n ON VPS",     "#2c3e50", "n8n receives the webhook and orchestrates everything from here. It updates the GHL landing page via API (Custom Values), then calls Claude AI and Higgsfield in sequence."),
    ("3", "CLAUDE AI",      "#2980b9", "n8n sends the webinar topic to Claude API. Claude writes 5 tailored Higgsfield video prompts — each targeting a different creative angle (authority, pain point, social proof, urgency, curiosity)."),
    ("4", "HIGGSFIELD API", "#ea4b71", "n8n sends all 5 prompts to Higgsfield API simultaneously. Model: Kling 3.0. Higgsfield renders 5 cinematic B-roll clips (8 seconds each). n8n polls every 30 seconds until all 5 are complete, then downloads them to the VPS."),
    ("5", "FFmpeg (VPS)",   "#16a085", "FFmpeg (free, runs on VPS) assembles the final video ad: combines best B-roll clips, adds animated webinar title overlay, adds Randy's pre-recorded voiceover, adds captions and background music. Output: polished 60-sec LinkedIn video ad."),
    ("6", "LINKEDIN ADS",   "#0077b5", "n8n calls LinkedIn Marketing API: uploads the video creative, creates the ad campaign, applies the saved audience targeting (GovCon founders), sets the daily budget. Ads go live. Total time from Zoom webinar creation to ads live: ~8 minutes."),
]
for num, stage, col, desc in steps:
    tbl = Table([[
        Paragraph(num, s(f"sn{num}", fontName="Helvetica-Bold", fontSize=15, textColor=WHITE, leading=20, alignment=TA_CENTER)),
        [Paragraph(stage, s(f"sh{num}", fontName="Helvetica-Bold", fontSize=10, textColor=colors.HexColor(col), leading=14, spaceAfter=2)),
         Paragraph(desc,  s(f"sd{num}", fontName="Helvetica",      fontSize=9,  textColor=DGRAY, leading=13))],
    ]], colWidths=[0.45*inch, 6.55*inch])
    tbl.setStyle(TableStyle([
        ("BACKGROUND",    (0,0),(0,-1), colors.HexColor(col)),
        ("BACKGROUND",    (1,0),(1,-1), LGRAY if int(num)%2==0 else WHITE),
        ("TOPPADDING",    (0,0),(-1,-1), 7), ("BOTTOMPADDING",(0,0),(-1,-1), 7),
        ("LEFTPADDING",   (1,0),(1,-1), 10), ("RIGHTPADDING", (0,0),(-1,-1), 8),
        ("VALIGN",        (0,0),(-1,-1), "MIDDLE"),
        ("BOX",           (0,0),(-1,-1), 0.5, colors.HexColor("#dddddd")),
    ]))
    story.append(tbl)
    story.append(Spacer(1, 4))

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 5 — THE RETARGETING LOOP
# ══════════════════════════════════════════════════════════════════════════════
story.append(PageBreak())
story.append(banner("THE RETARGETING LOOP — AGENT 1 ↔ AGENT 2"))
story.append(Spacer(1, 8))
story.append(Paragraph(
    "No lead goes cold. Agent 1 feeds audience data back to Agent 2 automatically — "
    "every no-show, every website visitor, every past registrant gets re-circulated into the next webinar cycle.",
    S_body))
story.append(Spacer(1, 10))

def make_retargeting():
    TW = 504; H = 300; cx = TW/2
    d = Drawing(TW, H)

    # Agent 1 box (top)
    d.add(Rect(cx-85, H-50, 170, 38, fillColor=NAVY, strokeWidth=0))
    d.add(String(cx, H-26, "AGENT 1 — WEBINAR FUNNEL", fontName="Helvetica-Bold", fontSize=10, fillColor=WHITE, textAnchor="middle"))
    d.add(String(cx, H-39, "Tracks every attendee & no-show", fontName="Helvetica", fontSize=7.5, fillColor=colors.HexColor("#aaaaaa"), textAnchor="middle"))

    # Three audience types branching down
    aud = [
        (TW*0.18, "NO-SHOW",         RED,    "'Missed the webinar?'\nSee replay + next date ad"),
        (TW*0.50, "WEBSITE VISITOR", BLUE,   "Visited but didn't register\nInsight Tag pixel retargets"),
        (TW*0.82, "PAST ATTENDEE",   PURPLE, "Attended but didn't book\nFree consultation ad"),
    ]
    for ax, lbl, col, desc in aud:
        d.add(Line(cx, H-50, ax, H-100, strokeColor=col, strokeWidth=1.5))
        d.add(Rect(ax-62, H-136, 124, 36, fillColor=col, strokeWidth=0))
        d.add(String(ax, H-113, lbl, fontName="Helvetica-Bold", fontSize=9, fillColor=WHITE, textAnchor="middle"))
        d.add(String(ax, H-126, "audience", fontName="Helvetica", fontSize=7.5, fillColor=WHITE, textAnchor="middle"))
        lines = desc.split('\n')
        for j, line in enumerate(lines):
            d.add(String(ax, H-152-j*12, line, fontName="Helvetica", fontSize=7, fillColor=DGRAY, textAnchor="middle"))

    # n8n box (middle)
    d.add(Rect(cx-65, 148, 130, 30, fillColor=colors.HexColor("#2c3e50"), strokeWidth=0))
    d.add(String(cx, 168, "n8n on VPS", fontName="Helvetica-Bold", fontSize=9, fillColor=WHITE, textAnchor="middle"))
    d.add(String(cx, 156, "Routes audiences to LinkedIn API", fontName="Helvetica", fontSize=7, fillColor=colors.HexColor("#aaaaaa"), textAnchor="middle"))

    for ax, lbl, col, desc in aud:
        d.add(Line(ax, H-172, cx, 178, strokeColor=col, strokeWidth=1))

    # LinkedIn box
    d.add(Line(cx, 148, cx, 108, strokeColor=BLUE, strokeWidth=1.5))
    d.add(Rect(cx-75, 72, 150, 36, fillColor=colors.HexColor("#0077b5"), strokeWidth=0))
    d.add(String(cx, 95, "LINKEDIN CAMPAIGN MANAGER", fontName="Helvetica-Bold", fontSize=9, fillColor=WHITE, textAnchor="middle"))
    d.add(String(cx, 82, "Custom audiences updated automatically", fontName="Helvetica", fontSize=7, fillColor=WHITE, textAnchor="middle"))

    # Back to Agent 1
    d.add(Line(cx, 72, cx, 38, strokeColor=ORANGE, strokeWidth=1.5))
    d.add(Rect(cx-90, 4, 180, 30, fillColor=ORANGE, strokeWidth=0))
    d.add(String(cx, 24, "NEW WEBINAR ADS SHOWN TO WARM AUDIENCES", fontName="Helvetica-Bold", fontSize=8, fillColor=WHITE, textAnchor="middle"))
    d.add(String(cx, 11, "They register → Agent 1 takes over", fontName="Helvetica", fontSize=7, fillColor=WHITE, textAnchor="middle"))

    return d

story.append(make_retargeting())
story.append(Spacer(1, 12))

# Insight box
ins = Table([[
    Paragraph("💡", s("ii", fontName="Helvetica-Bold", fontSize=16, textColor=ORANGE, leading=20)),
    Paragraph("<b>The compounding effect.</b> Every webinar builds a larger retargeting audience. "
              "By month 3, Randy has hundreds of warm leads who've seen his content, visited his page, "
              "or attended a previous webinar — and Agent 2 is showing them ads for the next one automatically. "
              "Cost per registration drops as audiences get warmer.",
              s("it", fontName="Helvetica", fontSize=10, textColor=DGRAY, leading=15)),
]], colWidths=[0.45*inch, 6.55*inch])
ins.setStyle(TableStyle([
    ("BACKGROUND", (0,0),(-1,-1), colors.HexColor("#fff5ee")),
    ("BOX",        (0,0),(-1,-1), 1.5, ORANGE),
    ("TOPPADDING", (0,0),(-1,-1), 10), ("BOTTOMPADDING",(0,0),(-1,-1), 10),
    ("LEFTPADDING",(0,0),(-1,-1), 10), ("RIGHTPADDING", (0,0),(-1,-1), 10),
    ("VALIGN",     (0,0),(-1,-1), "MIDDLE"),
]))
story.append(ins)

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 6 — PLATFORMS + CHECKLIST
# ══════════════════════════════════════════════════════════════════════════════
story.append(PageBreak())
story.append(banner("THE 6 PLATFORMS POWERING AGENT 2"))
story.append(Spacer(1, 10))

platforms = [
    ("LI",   "LinkedIn Campaign Manager", colors.HexColor("#0077b5"),
     "The ad platform. Campaign creation, audience targeting, bidding, and creative management all happen here. LinkedIn Insight Tag pixel installs on GHL page to track every conversion.",
     "✅ Needs account setup", "Ad spend only"),
    ("GHL",  "GoHighLevel",               NAVY,
     "Hosts the landing page with Custom Values. Receives all leads from LinkedIn into CRM. UTM parameters stored on every contact record for full attribution.",
     "✅ Already subscribed", "$97/month"),
    ("VPS",  "VPS + n8n",                 colors.HexColor("#2c3e50"),
     "The automation brain. n8n on the VPS orchestrates the entire pipeline: Zoom webhook → Claude → Higgsfield → FFmpeg → LinkedIn API. Runs 24/7 on the same server as Agent 1.",
     "✅ Already set up", "$250 / 2 years"),
    ("HIG",  "Higgsfield AI",             PINK,
     "Generates cinematic B-roll video clips from text prompts. Called by n8n via API. Model: Kling 3.0. 5 clips per webinar cycle at ~6 credits each = 30 credits. Starter plan covers multiple cycles.",
     "⚙️  API key needed", "$15/month"),
    ("CAI",  "Claude AI API",             BLUE,
     "Writes the Higgsfield video prompts and ad copy based on the webinar topic. Called by n8n. One call per webinar cycle generates all creative briefs automatically.",
     "⚙️  API key needed", "~$1–2/cycle"),
    ("FFM",  "FFmpeg (VPS)",              TEAL,
     "Free video editor that runs directly on the VPS. Combines Higgsfield clips, adds title overlays, Randy's voiceover, captions, and music. Outputs the final LinkedIn video ad. Zero cost.",
     "✅ Free, open-source", "$0"),
]
for abbr, name, col, desc, status, cost in platforms:
    tbl = Table([[
        Paragraph(abbr, s(f"pa{abbr}", fontName="Helvetica-Bold", fontSize=9, textColor=WHITE, leading=14, alignment=TA_CENTER)),
        [Paragraph(name,   s(f"pn{abbr}", fontName="Helvetica-Bold", fontSize=10.5, textColor=col,  leading=14, spaceAfter=2)),
         Paragraph(desc,   s(f"pd{abbr}", fontName="Helvetica",      fontSize=8.5,  textColor=DGRAY, leading=13))],
        [Paragraph(status, s(f"ps{abbr}", fontName="Helvetica-Bold", fontSize=8,    textColor=GREEN, leading=11)),
         Paragraph(cost,   s(f"pc{abbr}", fontName="Helvetica-Bold", fontSize=9,    textColor=col,   leading=12))],
    ]], colWidths=[0.6*inch, 4.75*inch, 1.65*inch])
    tbl.setStyle(TableStyle([
        ("BACKGROUND", (0,0),(0,-1), col),
        ("BACKGROUND", (1,0),(1,-1), LGRAY),
        ("TOPPADDING", (0,0),(-1,-1), 9), ("BOTTOMPADDING",(0,0),(-1,-1), 9),
        ("LEFTPADDING",(0,0),(0,-1), 4), ("LEFTPADDING",(1,0),(1,-1), 10),
        ("RIGHTPADDING",(0,0),(-1,-1), 8),
        ("VALIGN",     (0,0),(-1,-1), "MIDDLE"),
        ("BOX",        (0,0),(-1,-1), 0.5, colors.HexColor("#dddddd")),
    ]))
    story.append(tbl)
    story.append(Spacer(1, 4))

story.append(Spacer(1, 10))
story.append(banner("RANDY — YOUR 4-ITEM CHECKLIST TO GO LIVE"))
story.append(Spacer(1, 10))

checklist = [
    ("01", "Create a LinkedIn Campaign Manager account",  "Go to linkedin.com/campaignmanager → connect to your LinkedIn profile → add billing card. BrndGuru handles everything else."),
    ("02", "Set your first webinar ad budget",            "Recommendation: start with $200–$300 for the first webinar to test creative. BrndGuru optimizes from there."),
    ("03", "Record a 60-second voiceover (once)",         "Randy speaks: 'If you're a federal contractor trying to win more contracts...' — recorded on phone or Zoom. Used across all video ads."),
    ("04", "Confirm Higgsfield + Claude API access",      "BrndGuru sets up both API keys on the VPS. Randy only needs to approve the monthly spend (~$15–$17/month total for both)."),
]
for num, task, detail in checklist:
    tbl = Table([[
        Paragraph(num, s(f"cn{num}", fontName="Helvetica-Bold", fontSize=12, textColor=WHITE, leading=16, alignment=TA_CENTER)),
        [Paragraph(f"<b>{task}</b>", s(f"ct{num}", fontName="Helvetica-Bold", fontSize=10, textColor=NAVY, leading=14)),
         Paragraph(detail,           s(f"cd{num}", fontName="Helvetica",      fontSize=8.5, textColor=DGRAY, leading=12))],
        Paragraph("☐", s(f"cb{num}", fontName="Helvetica", fontSize=18, textColor=ORANGE, leading=22, alignment=TA_CENTER)),
    ]], colWidths=[0.5*inch, 5.8*inch, 0.7*inch])
    tbl.setStyle(TableStyle([
        ("BACKGROUND", (0,0),(0,-1), ORANGE),
        ("BACKGROUND", (1,0),(1,-1), LGRAY if int(num)%2==0 else WHITE),
        ("TOPPADDING", (0,0),(-1,-1), 9), ("BOTTOMPADDING",(0,0),(-1,-1), 9),
        ("LEFTPADDING",(1,0),(1,-1), 12), ("RIGHTPADDING", (0,0),(-1,-1), 8),
        ("VALIGN",     (0,0),(-1,-1), "MIDDLE"),
        ("BOX",        (0,0),(-1,-1), 0.5, colors.HexColor("#dddddd")),
    ]))
    story.append(tbl)
    story.append(Spacer(1, 3))

story.append(Spacer(1, 14))
story.append(Paragraph(
    "Once all 4 items are confirmed, BrndGuru builds Agent 2 alongside Agent 1. "
    "Target: both agents live and ads running within 14 business days.",
    s("final", fontName="Helvetica-Bold", fontSize=10, textColor=NAVY, leading=15, alignment=TA_CENTER)))

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 7 — TECHNICAL WORKFLOW & TRIGGER MAP
# ══════════════════════════════════════════════════════════════════════════════
story.append(PageBreak())
story.append(banner("AGENT 2 — FULL TECHNICAL WORKFLOW & TRIGGER MAP"))
story.append(Spacer(1, 5))
story.append(Paragraph(
    "Complete trigger map — every automation step, every API call, every tool. For implementation reference.",
    s("twi", fontName="Helvetica-Oblique", fontSize=9, textColor=GRAY, leading=13)))
story.append(Spacer(1, 8))

_CW = [0.32*inch, 1.1*inch, 0.85*inch, 2.73*inch, 2.0*inch]

_hdr = Table([[
    Paragraph("#",               s("th0", fontName="Helvetica-Bold", fontSize=7.5, textColor=WHITE, leading=10, alignment=TA_CENTER)),
    Paragraph("TRIGGER",         s("th1", fontName="Helvetica-Bold", fontSize=7.5, textColor=WHITE, leading=10)),
    Paragraph("TOOL",            s("th2", fontName="Helvetica-Bold", fontSize=7.5, textColor=WHITE, leading=10, alignment=TA_CENTER)),
    Paragraph("ACTION / OUTPUT", s("th3", fontName="Helvetica-Bold", fontSize=7.5, textColor=WHITE, leading=10)),
    Paragraph("→ FIRES NEXT",    s("th4", fontName="Helvetica-Bold", fontSize=7.5, textColor=WHITE, leading=10)),
]], colWidths=_CW)
_hdr.setStyle(TableStyle([
    ("BACKGROUND", (0,0),(-1,-1), NAVY),
    ("TOPPADDING", (0,0),(-1,-1), 7), ("BOTTOMPADDING",(0,0),(-1,-1), 7),
    ("LEFTPADDING",(0,0),(-1,-1), 5), ("RIGHTPADDING", (0,0),(-1,-1), 5),
]))
story.append(_hdr)

_TCOLS = {
    "GoHighLevel":   NAVY,
    "n8n (VPS)":     colors.HexColor("#2c3e50"),
    "Claude API":    BLUE,
    "Higgsfield":    PINK,
    "FFmpeg (VPS)":  TEAL,
    "LinkedIn API":  colors.HexColor("#0077b5"),
    "n8n Schedule":  colors.HexColor("#2c3e50"),
}

_triggers = [
    ("T1",  "Zoom Webhook\nwebinar.created",     "n8n (VPS)",
     "Zoom fires POST webhook → n8n extracts topic, date, time, zoom_id → stores in workflow context",
     "→ T2 and T3 fire in parallel"),
    ("T2",  "n8n: landing\npage update",         "GoHighLevel",
     "PATCH GHL /custom-values: webinar_topic, webinar_date, webinar_time, zoom_webinar_id\nLanding page updates instantly — same URL, new content",
     "→ Agent 1 date triggers\nalso updated"),
    ("T3",  "n8n: creative\ngeneration start",   "Claude API",
     "POST Claude API: 'Write 5 Higgsfield video prompts for webinar on [topic], audience: GovCon founders'\n→ Returns 5 cinematic prompts with different angles",
     "→ T4 (5 parallel calls)"),
    ("T4",  "Claude output\n→ 5 prompts ready",  "Higgsfield",
     "POST /api/v1/generate/video × 5 (parallel)\nModel: kling-3.0 | Duration: 8s | Aspect: 16:9\nReturns 5 job_ids",
     "→ T5 (poll loop)"),
    ("T5",  "Poll: video\nstatus check",         "Higgsfield",
     "GET /api/v1/jobs/{job_id} every 30s until status='complete'\nDownload all 5 .mp4 files → save to VPS /creatives/{date}/",
     "→ T6 (FFmpeg)"),
    ("T6",  "Videos on VPS\nready",              "FFmpeg (VPS)",
     "FFmpeg assembles: combine clips + title overlay + Randy voiceover + captions + music\nExports final 60-sec ad-final.mp4",
     "→ T7 (LinkedIn upload)"),
    ("T7",  "Final video\nassembled",            "LinkedIn API",
     "POST /v2/assets (video upload) → POST /v2/adCreatives (3 static + 1 video)\nPOST /v2/adCampaigns → POST targeting facets → campaign live",
     "→ Ads live (~8 min\nfrom Zoom webhook)"),
    ("T8",  "Schedule:\nevery 7 days",           "n8n Schedule",
     "GET LinkedIn /adAnalytics → check CTR, CPL, impressions per creative\nIF CTR < 0.3% → PATCH creative status: PAUSED\nIF CPL > $20 → alert BrndGuru via email",
     "→ Data pushed to\nAgent 6 dashboard"),
    ("T9",  "GHL Tag:\n'no-show' (Agent 1)",     "n8n (VPS)",
     "GHL webhook fires → n8n → LinkedIn API: POST /v2/dmpSegments (add email to No-Show audience)\nRetargeting campaign fires: replay + next webinar ads",
     "→ LinkedIn shows\nretargeting ads"),
    ("T10", "GHL contacts\n> 300 (milestone)",   "n8n (VPS)",
     "n8n exports GHL contact list (emails) → POST LinkedIn /v2/dmpSegments (Matched Audience)\nLinkedIn builds Lookalike Audience (2% similarity) automatically",
     "→ New lookalike\ncampaign created"),
    ("T11", "Schedule:\nMonday 8AM",             "n8n Schedule",
     "Pull LinkedIn Ads API + GHL pipeline data → format weekly report\nPush to Agent 6 dashboard → email Randy + BrndGuru with CPL, registrations, calls, revenue",
     "→ Agent 6 dashboard\nupdated"),
]

for _i, (_num, _trig, _tool, _action, _fires) in enumerate(_triggers):
    _bg  = LGRAY if _i % 2 == 0 else WHITE
    _tc  = _TCOLS.get(_tool, BLUE)
    _hi  = _num in ("T4", "T5", "T6")
    _nb  = ORANGE if _hi else colors.HexColor("#2c3e50") if _i % 3 == 0 else NAVY
    _hbg = colors.HexColor("#fff8f0") if _hi else _bg
    _row = Table([[
        Paragraph(_num, s(f"rn{_i}", fontName="Helvetica-Bold", fontSize=7.5,
                          textColor=WHITE, leading=10, alignment=TA_CENTER)),
        Paragraph(_trig.replace("\n","<br/>"),   s(f"rt{_i}", fontName="Helvetica-Bold", fontSize=7.5,
                          textColor=WHITE if _hi else NAVY, leading=10)),
        Paragraph(_tool.replace("\n","<br/>"),   s(f"rl{_i}", fontName="Helvetica-Bold", fontSize=6.5,
                          textColor=WHITE, leading=9, alignment=TA_CENTER)),
        Paragraph(_action.replace("\n","<br/>"), s(f"ra{_i}", fontName="Helvetica", fontSize=7,
                          textColor=DGRAY, leading=10)),
        Paragraph(_fires.replace("\n","<br/>"),  s(f"rf{_i}", fontName="Helvetica-Bold", fontSize=7,
                          textColor=_tc, leading=10)),
    ]], colWidths=_CW)
    _row.setStyle(TableStyle([
        ("BACKGROUND", (0,0),(0,-1), _nb),
        ("BACKGROUND", (1,0),(1,-1), _hbg),
        ("BACKGROUND", (2,0),(2,-1), _tc),
        ("BACKGROUND", (3,0),(3,-1), _hbg),
        ("BACKGROUND", (4,0),(4,-1), _hbg),
        ("TOPPADDING",    (0,0),(-1,-1), 5), ("BOTTOMPADDING",(0,0),(-1,-1), 5),
        ("LEFTPADDING",   (0,0),(-1,-1), 5), ("RIGHTPADDING", (0,0),(-1,-1), 5),
        ("VALIGN",        (0,0),(-1,-1), "TOP"),
        ("LINEBELOW",     (0,0),(-1,-1), 0.3, colors.HexColor("#cccccc")),
    ]))
    story.append(_row)

story.append(Spacer(1, 10))
story.append(Paragraph(
    "⚠  T4–T6 (highlighted) are the Higgsfield AI creative generation nodes — "
    "the core innovation of Agent 2. These replace a design agency entirely.",
    s("note2", fontName="Helvetica-Bold", fontSize=8, textColor=ORANGE, leading=12)))

doc.build(story, onFirstPage=cover_draw, onLaterPages=later_pages)
print("PDF generated:", OUTPUT)
