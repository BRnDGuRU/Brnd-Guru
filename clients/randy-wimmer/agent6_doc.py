from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import ParagraphStyle
from reportlab.lib.units import inch
from reportlab.lib import colors
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle,
    KeepTogether, PageBreak, Image as RLImage
)
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_RIGHT
from reportlab.graphics.shapes import Drawing, Rect, String, Line, Polygon
from reportlab.graphics import renderPDF

INFOGRAPHIC6 = "/home/user/Brnd-Guru/clients/randy-wimmer/agent 6 info.png"

ORANGE = colors.HexColor("#FF6600")
NAVY   = colors.HexColor("#1e3a5f")
LGRAY  = colors.HexColor("#f4f6f9")
DGRAY  = colors.HexColor("#2c3e50")
WHITE  = colors.white
BLACK  = colors.HexColor("#111111")
GREEN  = colors.HexColor("#27ae60")
LGREEN = colors.HexColor("#eafaf1")
RED    = colors.HexColor("#e74c3c")
GRAY   = colors.HexColor("#888888")
MGRAY  = colors.HexColor("#cccccc")
LGRAY2 = colors.HexColor("#e8e8e8")
BLUE   = colors.HexColor("#2980b9")
PURPLE = colors.HexColor("#8e44ad")
TEAL   = colors.HexColor("#16a085")
PINK   = colors.HexColor("#ea4b71")

# Coming Soon palette
CS_BG   = colors.HexColor("#f0f0f0")
CS_TEXT = colors.HexColor("#aaaaaa")
CS_BDGE = colors.HexColor("#e0e0e0")

OUTPUT = "/home/user/Brnd-Guru/clients/randy-wimmer/Agent6_Dashboard.pdf"

doc = SimpleDocTemplate(
    OUTPUT, pagesize=letter,
    leftMargin=0.75*inch, rightMargin=0.75*inch,
    topMargin=0.88*inch, bottomMargin=0.65*inch,
)

def s(name, **kw):
    return ParagraphStyle(name, **kw)

S_body = s("bd", fontName="Helvetica",         fontSize=10, textColor=DGRAY, leading=15, spaceAfter=4)
S_note = s("nt", fontName="Helvetica-Oblique",  fontSize=8,  textColor=GRAY,  leading=12, alignment=TA_CENTER)
S_cs   = s("cs", fontName="Helvetica-Bold",     fontSize=8,  textColor=CS_TEXT, leading=11, alignment=TA_CENTER)

def banner(title):
    tbl = Table([[
        "",
        Paragraph(title, s("bt", fontName="Helvetica-Bold", fontSize=13, textColor=WHITE, leading=18))
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

def cs_bar():
    """Coming Soon badge bar."""
    tbl = Table([[
        Paragraph("COMING SOON - Data will populate automatically once agents are live",
                  s("csb", fontName="Helvetica-Bold", fontSize=8.5, textColor=WHITE, leading=12, alignment=TA_CENTER))
    ]], colWidths=[7*inch])
    tbl.setStyle(TableStyle([
        ("BACKGROUND",    (0,0),(-1,-1), colors.HexColor("#555555")),
        ("TOPPADDING",    (0,0),(-1,-1), 7),
        ("BOTTOMPADDING", (0,0),(-1,-1), 7),
    ]))
    return tbl

def cs_metric(label, unit=""):
    """A single grayed-out coming-soon metric block."""
    return [
        Paragraph(label, s(f"csl{label}", fontName="Helvetica-Bold", fontSize=7, textColor=GRAY, leading=9)),
        Paragraph("- -", s(f"csv{label}", fontName="Helvetica-Bold", fontSize=18, textColor=MGRAY, leading=22)),
        Paragraph("coming soon", s(f"css{label}", fontName="Helvetica-Oblique", fontSize=6.5, textColor=CS_TEXT, leading=9)),
    ]

# ── CANVAS CALLBACKS ──────────────────────────────────────────────────────────
def cover_draw(canvas, doc):
    W, H = letter
    canvas.saveState()
    canvas.setFillColor(BLACK)
    canvas.rect(0, 0, W, H, fill=1, stroke=0)

    for pts, col in [
        ([(W,H),(W-220,H),(W,H-220)], ORANGE),
        ([(W,H),(W-110,H),(W,H-110)], colors.HexColor("#CC5200")),
        ([(0,0),(130,0),(0,130)],      ORANGE),
    ]:
        p = canvas.beginPath()
        p.moveTo(*pts[0]); p.lineTo(*pts[1]); p.lineTo(*pts[2])
        p.close(); canvas.drawPath(p, fill=1, stroke=0)

    canvas.setFillColor(ORANGE)
    canvas.rect(0, H*0.22, 5, H*0.52, fill=1, stroke=0)

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

    canvas.setFont("Helvetica-Bold", 9)
    canvas.setFillColor(ORANGE)
    canvas.drawString(bx, H - 165, "A G E N T   6   -   C O N T R O L   T O W E R")

    canvas.setFont("Helvetica-Bold", 38)
    canvas.setFillColor(WHITE)
    canvas.drawString(bx, H - 210, "Revenue Control")
    canvas.setFillColor(ORANGE)
    canvas.drawString(bx, H - 254, "Tower Dashboard")
    canvas.setFont("Helvetica", 13)
    canvas.setFillColor(colors.HexColor("#aaaaaa"))
    canvas.drawString(bx, H - 280, "Every Agent. Every Metric. One Live View.")

    canvas.setFillColor(ORANGE)
    canvas.rect(bx, H - 300, W - bx - 52, 2, fill=1, stroke=0)

    bw2   = W - bx - 52
    box_y = H - 418
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
        "Pulls data from all 6 agents into one live dashboard -",
        "so Randy sees exactly what's working, what's not,",
        "and where to scale. Updates automatically, 24/7.",
    ]):
        canvas.drawString(bx + 12, box_y + box_h - 44 - i*18, line)

    canvas.setFont("Helvetica-Bold", 8)
    canvas.setFillColor(colors.HexColor("#aaaaaa"))
    canvas.drawString(bx, H - 444, "P R E P A R E D   F O R")
    canvas.setFont("Helvetica-Bold", 20)
    canvas.setFillColor(WHITE)
    canvas.drawString(bx, H - 466, "Randy Wimmer")
    canvas.setFont("Helvetica", 10)
    canvas.setFillColor(colors.HexColor("#888888"))
    canvas.drawString(bx, H - 484, "Government Contracting Academy / ISO Certification Group")

    box_w = (W - bx - 52) / 3
    canvas.setFillColor(colors.HexColor("#1a1a1a"))
    canvas.rect(bx, H - 566, W - bx - 52, 60, fill=1, stroke=0)
    for i, (t, sub) in enumerate([
        ("6 Agents","One dashboard"),
        ("Auto-Updates","No manual pulling"),
        ("Live Data","Real-time always"),
    ]):
        cx = bx + i * box_w + box_w / 2
        if i > 0:
            canvas.setStrokeColor(colors.HexColor("#333333")); canvas.setLineWidth(0.5)
            canvas.line(bx + i*box_w, H-566, bx + i*box_w, H-506)
        canvas.setFont("Helvetica-Bold", 11); canvas.setFillColor(ORANGE)
        canvas.drawCentredString(cx, H - 532, t)
        canvas.setFont("Helvetica", 8); canvas.setFillColor(colors.HexColor("#aaaaaa"))
        canvas.drawCentredString(cx, H - 548, sub)

    canvas.setFillColor(colors.HexColor("#080808"))
    canvas.rect(0, 0, W, 44, fill=1, stroke=0)
    canvas.setFont("Helvetica-Bold", 9); canvas.setFillColor(ORANGE)
    canvas.drawString(50, 16, "brndguruofficial@gmail.com")
    canvas.setFont("Helvetica", 8); canvas.setFillColor(colors.HexColor("#666666"))
    canvas.drawRightString(W - 50, 16, "Government Contracting Academy - Agent 6 Breakdown")
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
    canvas.drawString(0.75*inch + 58, H - 18, "Agent 6 - Revenue Control Tower Dashboard")
    canvas.drawRightString(W - 0.75*inch, H - 18, f"Page {doc.page}")
    canvas.setFillColor(colors.HexColor("#f0f0f0"))
    canvas.rect(0, 0, W, 26, fill=1, stroke=0)
    canvas.setFillColor(ORANGE); canvas.rect(0, 26, W, 1.5, fill=1, stroke=0)
    canvas.setFont("Helvetica", 7.5); canvas.setFillColor(GRAY)
    canvas.drawCentredString(W/2, 8, "Confidential - Prepared for Randy Wimmer | Government Contracting Academy | BrndGuru")
    canvas.restoreState()

# ── STORY ─────────────────────────────────────────────────────────────────────
story = []
story.append(PageBreak())

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 2 - INFOGRAPHIC OVERVIEW
# ══════════════════════════════════════════════════════════════════════════════
story.append(RLImage(INFOGRAPHIC6, width=7*inch, height=4.667*inch))
story.append(PageBreak())

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 3 - WHAT IS AGENT 6 + ARCHITECTURE
# ══════════════════════════════════════════════════════════════════════════════
story.append(banner("WHAT IS AGENT 6?"))
story.append(Spacer(1, 10))
story.append(Paragraph(
    "<b>Agent 6 is your command center.</b><br/><br/>"
    "Every other agent - webinar funnel, paid ads, content, outreach, email nurture - generates data. "
    "Agent 6 collects all of it, formats it, and displays it in one live dashboard so Randy "
    "can see the health of his entire revenue system at a glance. "
    "No switching between 6 platforms. No manual number-pulling. No guessing.",
    s("intro", fontName="Helvetica", fontSize=10.5, textColor=DGRAY, leading=17, spaceAfter=10)))

# Architecture diagram
story.append(banner("HOW IT WORKS - DATA FLOW"))
story.append(Spacer(1, 10))

def make_arch():
    TW = 504; H = 160
    d = Drawing(TW, H)

    sources = [
        ("GHL",       "#1e3a5f", 0),
        ("ZOOM",      "#2980b9", 1),
        ("LINKEDIN",  "#0077b5", 2),
        ("HEYREACH",  "#ea4b71", 3),
        ("CALENDLY",  "#27ae60", 4),
    ]
    sw = 72; sh = 34; gap = 14
    unit = sw + gap
    for i, (lbl, col, _) in enumerate(sources):
        x = i * unit
        d.add(Rect(x, H-44, sw, sh, fillColor=colors.HexColor(col), strokeWidth=0))
        d.add(String(x+sw/2, H-24, lbl, fontName="Helvetica-Bold", fontSize=7, fillColor=WHITE, textAnchor="middle"))
        # Arrow down
        d.add(Line(x+sw/2, H-44, x+sw/2, H-62, strokeColor=colors.HexColor(col), strokeWidth=1))
        d.add(Polygon([x+sw/2-5, H-62, x+sw/2+5, H-62, x+sw/2, H-70],
                      fillColor=colors.HexColor(col), strokeWidth=0))

    # n8n central
    nx = TW/2 - 55
    d.add(Rect(nx, H-108, 110, 34, fillColor=colors.HexColor("#2c3e50"), strokeWidth=0))
    d.add(String(TW/2, H-87, "n8n on VPS", fontName="Helvetica-Bold", fontSize=9, fillColor=WHITE, textAnchor="middle"))
    d.add(String(TW/2, H-100, "Collects + formats all data", fontName="Helvetica", fontSize=7, fillColor=CS_TEXT, textAnchor="middle"))

    # Converging lines from sources to n8n
    for i, (lbl, col, _) in enumerate(sources):
        sx = i * unit + sw/2
        d.add(Line(sx, H-70, TW/2, H-108, strokeColor=colors.HexColor(col), strokeWidth=0.8))

    # Arrow down to dashboard
    d.add(Line(TW/2, H-108, TW/2, H-128, strokeColor=ORANGE, strokeWidth=2))
    d.add(Polygon([TW/2-6, H-128, TW/2+6, H-128, TW/2, H-136], fillColor=ORANGE, strokeWidth=0))

    # Dashboard box
    d.add(Rect(TW/2-100, 0, 200, 28, fillColor=ORANGE, strokeWidth=0))
    d.add(String(TW/2, 18, "GOOGLE LOOKER STUDIO  +  GHL DASHBOARD", fontName="Helvetica-Bold", fontSize=8, fillColor=WHITE, textAnchor="middle"))
    d.add(String(TW/2, 6,  "Randy's live URL - always updated", fontName="Helvetica", fontSize=7, fillColor=WHITE, textAnchor="middle"))

    return d

story.append(make_arch())
story.append(Spacer(1, 10))

# Tools row
tools_row = [
    ("GHL",            NAVY,   "Pipeline, calls,\nemail, contacts"),
    ("Zoom API",       BLUE,   "Attendance,\nshow-up rate"),
    ("LinkedIn API",   colors.HexColor("#0077b5"), "Ad spend,\nCPL, creatives"),
    ("HeyReach API",   PINK,   "DM stats,\nconnection rate"),
    ("Calendly",       GREEN,  "Calls booked,\nscheduled"),
    ("Google Sheets",  colors.HexColor("#34a853"), "Central data\nstore (n8n writes)"),
]
r1 = [Paragraph(n, s(f"tn{i}", fontName="Helvetica-Bold", fontSize=8, textColor=WHITE, leading=10, alignment=TA_CENTER)) for i,(n,c,d) in enumerate(tools_row)]
r2 = [Paragraph(d.replace("\n","<br/>"), s(f"td{i}", fontName="Helvetica", fontSize=7, textColor=DGRAY, leading=10, alignment=TA_CENTER)) for i,(n,c,d) in enumerate(tools_row)]
tr = Table([r1, r2], colWidths=[1.167*inch]*6)
tr.setStyle(TableStyle([
    ("BACKGROUND",    (i,0),(i,0), tools_row[i][1]) for i in range(6)
] + [
    ("BACKGROUND",    (0,1),(-1,1), LGRAY),
    ("TOPPADDING",    (0,0),(-1,0), 7), ("BOTTOMPADDING",(0,0),(-1,0), 7),
    ("TOPPADDING",    (0,1),(-1,1), 5), ("BOTTOMPADDING",(0,1),(-1,1), 5),
    ("GRID",          (0,0),(-1,-1), 0.5, colors.HexColor("#dddddd")),
    ("VALIGN",        (0,0),(-1,-1), "MIDDLE"),
]))
story.append(tr)
story.append(Spacer(1, 6))
story.append(Paragraph("Total additional cost for Agent 6: $0 - built on GHL (already paid) + Google Looker Studio (free) + n8n (already on VPS).", S_note))

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 3 - COMMAND CENTER (MASTER VIEW)
# ══════════════════════════════════════════════════════════════════════════════
story.append(PageBreak())
story.append(banner("COMMAND CENTER - ALL 6 AGENTS AT A GLANCE"))
story.append(Spacer(1, 6))
story.append(cs_bar())
story.append(Spacer(1, 8))
story.append(Paragraph(
    "This is the first screen Randy sees when he opens his dashboard URL. "
    "Every agent's health status + top 3 KPIs - all in one view. "
    "Grayed sections below will populate with live data once each agent goes live.",
    s("p3i", fontName="Helvetica-Oblique", fontSize=9, textColor=GRAY, leading=13)))
story.append(Spacer(1, 10))

# Top-level summary bar (Coming Soon)
top_metrics = ["Total Pipeline Value", "Calls Booked (Week)", "Closed Revenue (Month)", "Active Leads"]
tm_row1 = [Paragraph(m, s(f"tm{i}", fontName="Helvetica-Bold", fontSize=7.5, textColor=GRAY, leading=10, alignment=TA_CENTER)) for i,m in enumerate(top_metrics)]
tm_row2 = [Paragraph("- -", s(f"tmv{i}", fontName="Helvetica-Bold", fontSize=20, textColor=MGRAY, leading=26, alignment=TA_CENTER)) for i in range(4)]
tm_row3 = [Paragraph("coming soon", s(f"tms{i}", fontName="Helvetica-Oblique", fontSize=7, textColor=CS_TEXT, leading=9, alignment=TA_CENTER)) for i in range(4)]
tm_tbl = Table([tm_row1, tm_row2, tm_row3], colWidths=[1.75*inch]*4)
tm_tbl.setStyle(TableStyle([
    ("BACKGROUND",    (0,0),(-1,-1), CS_BG),
    ("GRID",          (0,0),(-1,-1), 0.5, colors.HexColor("#dddddd")),
    ("TOPPADDING",    (0,0),(-1,0), 8),  ("BOTTOMPADDING",(0,0),(-1,0), 2),
    ("TOPPADDING",    (0,1),(-1,1), 4),  ("BOTTOMPADDING",(0,1),(-1,1), 2),
    ("TOPPADDING",    (0,2),(-1,2), 0),  ("BOTTOMPADDING",(0,2),(-1,2), 8),
]))
story.append(tm_tbl)
story.append(Spacer(1, 12))

# 6 agent mini-cards in 2x3 grid
agents_mini = [
    ("1", "WEBINAR FUNNEL",      "#FF6600", ["Registrations", "Show-Up Rate", "Calls Booked"]),
    ("2", "PAID ACQUISITION",    "#2980b9", ["Ad Spend",      "Cost Per Reg.", "Calls from Ads"]),
    ("3", "CONTENT AUTHORITY",   "#8e44ad", ["Posts Live",    "Impressions",   "Organic Sign-Ups"]),
    ("4", "LINKEDIN OUTREACH",   "#ea4b71", ["Requests Sent", "Accept Rate",   "Calls from DMs"]),
    ("5", "EMAIL NURTURE",       "#16a085", ["Emails Sent",   "Open Rate",     "Calls from Email"]),
    ("6", "REVENUE PIPELINE",    "#1e3a5f", ["Pipeline Value","Proposals Out", "Revenue Closed"]),
]

def make_agent_mini_card(num, name, col, metrics):
    c = colors.HexColor(col)
    hdr = Table([[
        Paragraph(num,  s(f"mn{num}", fontName="Helvetica-Bold", fontSize=13, textColor=WHITE, leading=18, alignment=TA_CENTER)),
        Paragraph(f"AGENT {num}<br/>{name}", s(f"mh{num}", fontName="Helvetica-Bold", fontSize=8, textColor=WHITE, leading=11)),
        Paragraph("COMING SOON", s(f"mc{num}", fontName="Helvetica-Bold", fontSize=7, textColor=WHITE, leading=10, alignment=TA_RIGHT)),
    ]], colWidths=[0.35*inch, 1.9*inch, 1.0*inch])
    hdr.setStyle(TableStyle([
        ("BACKGROUND", (0,0),(-1,-1), c),
        ("TOPPADDING",    (0,0),(-1,-1), 6), ("BOTTOMPADDING",(0,0),(-1,-1), 6),
        ("LEFTPADDING",   (0,0),(0,-1), 4),  ("LEFTPADDING",  (1,0),(1,-1), 6),
        ("RIGHTPADDING",  (0,0),(-1,-1), 6),
        ("VALIGN",        (0,0),(-1,-1), "MIDDLE"),
    ]))
    m_row1 = [Paragraph(m, s(f"ml{num}{i}", fontName="Helvetica-Bold", fontSize=6.5, textColor=GRAY, leading=9, alignment=TA_CENTER)) for i,m in enumerate(metrics)]
    m_row2 = [Paragraph("-", s(f"mv{num}{i}", fontName="Helvetica-Bold", fontSize=16, textColor=MGRAY, leading=20, alignment=TA_CENTER)) for i in range(3)]
    body = Table([m_row1, m_row2], colWidths=[1.083*inch]*3)
    body.setStyle(TableStyle([
        ("BACKGROUND",    (0,0),(-1,-1), CS_BG),
        ("GRID",          (0,0),(-1,-1), 0.5, colors.HexColor("#eeeeee")),
        ("TOPPADDING",    (0,0),(-1,0), 6), ("BOTTOMPADDING",(0,0),(-1,0), 2),
        ("TOPPADDING",    (0,1),(-1,1), 2), ("BOTTOMPADDING",(0,1),(-1,1), 8),
    ]))
    wrapper = Table([[hdr], [body]], colWidths=[3.25*inch])
    wrapper.setStyle(TableStyle([
        ("BOX",        (0,0),(-1,-1), 1, c),
        ("TOPPADDING", (0,0),(-1,-1), 0),
        ("BOTTOMPADDING",(0,0),(-1,-1), 0),
        ("LEFTPADDING",(0,0),(-1,-1), 0),
        ("RIGHTPADDING",(0,0),(-1,-1), 0),
    ]))
    return wrapper

# Build 2-column grid
cards = [make_agent_mini_card(num, name, col, metrics) for num, name, col, metrics in agents_mini]
grid = Table([
    [cards[0], cards[1]],
    [cards[2], cards[3]],
    [cards[4], cards[5]],
], colWidths=[3.25*inch, 3.25*inch], rowHeights=None)
grid.setStyle(TableStyle([
    ("TOPPADDING",    (0,0),(-1,-1), 4),
    ("BOTTOMPADDING", (0,0),(-1,-1), 4),
    ("LEFTPADDING",   (0,0),(-1,-1), 4),
    ("RIGHTPADDING",  (0,0),(-1,-1), 4),
    ("VALIGN",        (0,0),(-1,-1), "TOP"),
]))
story.append(grid)

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 4 - AGENT 1 + AGENT 2 DETAIL PANELS
# ══════════════════════════════════════════════════════════════════════════════
story.append(PageBreak())
story.append(banner("AGENT 1 PANEL - WEBINAR FUNNEL DASHBOARD"))
story.append(Spacer(1, 6))
story.append(cs_bar())
story.append(Spacer(1, 8))

def make_detail_panel(metrics_4, rows, col):
    c = colors.HexColor(col)
    r1 = [Paragraph(m, s(f"dp{m}", fontName="Helvetica-Bold", fontSize=7, textColor=GRAY, leading=9, alignment=TA_CENTER)) for m in metrics_4]
    r2 = [Paragraph("- -", s(f"dpv{m}", fontName="Helvetica-Bold", fontSize=18, textColor=MGRAY, leading=22, alignment=TA_CENTER)) for m in metrics_4]
    r3 = [Paragraph("coming soon", s(f"dps{m}", fontName="Helvetica-Oblique", fontSize=6.5, textColor=CS_TEXT, leading=9, alignment=TA_CENTER)) for m in metrics_4]
    top = Table([r1,r2,r3], colWidths=[1.75*inch]*4)
    top.setStyle(TableStyle([
        ("BACKGROUND",    (0,0),(-1,-1), CS_BG),
        ("GRID",          (0,0),(-1,-1), 0.5, colors.HexColor("#eeeeee")),
        ("TOPPADDING",    (0,0),(-1,0), 8), ("BOTTOMPADDING",(0,0),(-1,0), 2),
        ("TOPPADDING",    (0,1),(-1,1), 2), ("BOTTOMPADDING",(0,1),(-1,1), 2),
        ("TOPPADDING",    (0,2),(-1,2), 0), ("BOTTOMPADDING",(0,2),(-1,2), 8),
    ]))
    story.append(top)
    story.append(Spacer(1, 8))
    # Row details
    for label, desc in rows:
        row = Table([[
            Paragraph(label, s(f"rl{label}", fontName="Helvetica-Bold", fontSize=8.5, textColor=c, leading=12)),
            Paragraph(desc,  s(f"rd{label}", fontName="Helvetica", fontSize=8.5, textColor=DGRAY, leading=12)),
            Paragraph("- coming soon -", s(f"rv{label}", fontName="Helvetica-Oblique", fontSize=8, textColor=MGRAY, leading=12, alignment=TA_RIGHT)),
        ]], colWidths=[1.8*inch, 3.5*inch, 1.7*inch])
        row.setStyle(TableStyle([
            ("BACKGROUND",    (0,0),(-1,-1), CS_BG),
            ("TOPPADDING",    (0,0),(-1,-1), 6), ("BOTTOMPADDING",(0,0),(-1,-1), 6),
            ("LEFTPADDING",   (0,0),(-1,-1), 8), ("RIGHTPADDING", (0,0),(-1,-1), 8),
            ("LINEBELOW",     (0,0),(-1,-1), 0.4, colors.HexColor("#dddddd")),
            ("VALIGN",        (0,0),(-1,-1), "MIDDLE"),
        ]))
        story.append(row)

make_detail_panel(
    ["Registrations", "Show-Up Rate", "Calls Booked", "No-Show Rate"],
    [
        ("Registrations",    "Total people registered for the current webinar cycle"),
        ("Show-Up Rate",     "% of registrants who attended the live session"),
        ("No-Show Rate",     "% who missed - automatically enter replay sequence"),
        ("Calls Booked",     "Total Calendly bookings attributed to webinar funnel"),
        ("Replay Opens",     "No-shows who opened the replay email and watched"),
        ("Emails Sent",      "Total automated emails fired in current cycle"),
        ("SMS Delivered",    "Confirmation + reminder SMS delivered successfully"),
        ("Next Webinar",     "Scheduled date + days remaining until next session"),
        ("Sequence Status",  "Which automation steps have fired vs pending"),
    ],
    "#FF6600"
)

story.append(Spacer(1, 14))
story.append(banner("AGENT 2 PANEL - PAID ACQUISITION DASHBOARD"))
story.append(Spacer(1, 6))
story.append(cs_bar())
story.append(Spacer(1, 8))

make_detail_panel(
    ["Ad Spend", "Cost Per Reg.", "CTR", "Calls from Ads"],
    [
        ("Ad Spend",          "Total LinkedIn ad spend this cycle"),
        ("Impressions",       "Total times Randy's ads were shown on LinkedIn"),
        ("Clicks",            "Total link clicks to the GHL landing page"),
        ("Click-Through Rate","Clicks / Impressions - benchmark: 0.4%+"),
        ("Registrations",     "People who registered after clicking an ad"),
        ("Cost Per Registrant","Ad spend / registrations - target: < $15"),
        ("Cost Per Call",     "Ad spend / calls booked - target: < $150"),
        ("Active Campaigns",  "Number of live LinkedIn campaigns running"),
        ("Best Creative",     "Top performing ad by CTR this cycle"),
        ("Retargeting Size",  "No-show + website visitor audience size"),
    ],
    "#2980b9"
)

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 5 - AGENT 3 + AGENT 4 DETAIL PANELS
# ══════════════════════════════════════════════════════════════════════════════
story.append(PageBreak())
story.append(banner("AGENT 3 PANEL - LINKEDIN CONTENT DASHBOARD"))
story.append(Spacer(1, 6))
story.append(cs_bar())
story.append(Spacer(1, 8))

make_detail_panel(
    ["Posts Published", "Total Impressions", "Eng. Rate", "Organic Sign-Ups"],
    [
        ("Posts Published",   "Total LinkedIn posts published this week"),
        ("Total Impressions", "Combined reach across all posts this cycle"),
        ("Avg. Eng. Rate",    "Likes + comments + shares / impressions - target: 3%+"),
        ("Top Post",          "Best performing post by impressions this cycle"),
        ("Comments Received", "Total comments across all posts - engagement signal"),
        ("Profile Views",     "Views on Randy's LinkedIn profile this week"),
        ("Followers Gained",  "Net new followers added to Randy's profile"),
        ("Organic Sign-Ups",  "Webinar registrations attributed to organic content"),
        ("Newsletter Subs",   "LinkedIn newsletter subscriber count"),
    ],
    "#8e44ad"
)

story.append(Spacer(1, 14))
story.append(banner("AGENT 4 PANEL - LINKEDIN OUTREACH DASHBOARD"))
story.append(Spacer(1, 6))
story.append(cs_bar())
story.append(Spacer(1, 8))

make_detail_panel(
    ["Requests Sent", "Accept Rate", "DM Replies", "Calls Booked"],
    [
        ("Requests Sent",     "LinkedIn connection requests sent via HeyReach this week"),
        ("Acceptance Rate",   "% accepted - target: 25-40% for GovCon ICP audience"),
        ("Connections Made",  "Total new connections added to Randy's network"),
        ("DM Sequence Sent",  "Follow-up DMs sent to accepted connections"),
        ("DM Reply Rate",     "% of DMs that received a reply - target: 15%+"),
        ("Active Convos",     "Ongoing conversations in LinkedIn inbox"),
        ("Calls Booked",      "Calendly bookings directly from LinkedIn DM conversations"),
        ("HeyReach Status",   "Current campaign running status and daily send count"),
        ("Top ICP Reached",   "Most common job titles in new connections this week"),
    ],
    "#ea4b71"
)

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 6 - AGENT 5 + AGENT 6 FULL FUNNEL
# ══════════════════════════════════════════════════════════════════════════════
story.append(PageBreak())
story.append(banner("AGENT 5 PANEL - EMAIL NURTURE DASHBOARD"))
story.append(Spacer(1, 6))
story.append(cs_bar())
story.append(Spacer(1, 8))

make_detail_panel(
    ["Emails Sent", "Open Rate", "Click Rate", "Calls from Email"],
    [
        ("Emails Sent",       "Total emails sent across all active sequences this week"),
        ("Unique Opens",      "Individual contacts who opened at least one email"),
        ("Open Rate",         "Opens / delivered - target: 30-45% for GovCon niche"),
        ("Click Rate",        "Clicks / opens - measures CTA effectiveness"),
        ("Replies Received",  "Contacts who replied to nurture emails directly"),
        ("Unsubscribes",      "Contacts who opted out - healthy rate: < 0.5%"),
        ("Leads Reactivated", "Cold leads who re-engaged after a reactivation sequence"),
        ("Calls Booked",      "Calendly bookings directly attributed to email sequences"),
        ("Deliverability",    "% of emails landing in inbox vs spam - target: 98%+"),
    ],
    "#16a085"
)

story.append(Spacer(1, 14))
story.append(banner("AGENT 6 PANEL - FULL FUNNEL + REVENUE PIPELINE"))
story.append(Spacer(1, 6))
story.append(cs_bar())
story.append(Spacer(1, 8))

# Full funnel visual (coming soon overlay)
def make_funnel_cs():
    TW = 504; H = 120
    d  = Drawing(TW, H)
    stages = [
        ("Ad Impressions",  "#2980b9", 1.0),
        ("Clicks",          "#e67e22", 0.75),
        ("Registrations",   "#FF6600", 0.55),
        ("Attended",        "#8e44ad", 0.38),
        ("Calls Booked",    "#27ae60", 0.24),
        ("Proposals",       "#1e3a5f", 0.14),
        ("Closed",          "#e74c3c", 0.08),
    ]
    bh = 14; gap = 2; y0 = H - 10
    max_w = TW - 80
    for i, (lbl, col, pct) in enumerate(stages):
        y    = y0 - i*(bh+gap)
        bw   = max_w * pct
        bx   = (max_w - bw) / 2
        d.add(Rect(bx, y-bh, bw, bh, fillColor=colors.HexColor("#e8e8e8"), strokeWidth=0))
        d.add(String(TW/2, y-bh+3, lbl, fontName="Helvetica", fontSize=7, fillColor=CS_TEXT, textAnchor="middle"))
        d.add(String(TW-35, y-bh+3, "-", fontName="Helvetica-Bold", fontSize=8, fillColor=MGRAY, textAnchor="middle"))
    d.add(String(TW/2, 4, "Funnel data populates automatically after first webinar cycle", fontName="Helvetica-Oblique", fontSize=7, fillColor=CS_TEXT, textAnchor="middle"))
    return d

story.append(make_funnel_cs())
story.append(Spacer(1, 8))

make_detail_panel(
    ["Pipeline Value", "Proposals Out", "Revenue Closed", "Active Prospects"],
    [
        ("Pipeline Value",    "Total estimated value of all active deals in GHL pipeline"),
        ("Active Prospects",  "Leads who have booked a call and are in active follow-up"),
        ("Proposals Sent",    "Number of ISO certification proposals sent this month"),
        ("Deals Won",         "Closed deals - new ISO certification clients signed"),
        ("Revenue Closed",    "Total revenue collected from closed deals this month"),
        ("Attribution Split", "Revenue broken down by source: Ads / Organic / Email / DMs"),
        ("Avg. Deal Value",   "Average contract value per closed ISO certification client"),
        ("Close Rate",        "Proposals sent / deals closed - benchmark: 20-35%"),
        ("Revenue Forecast",  "Projected monthly revenue based on current pipeline"),
    ],
    "#1e3a5f"
)

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 7 - HOW DATA FLOWS + GO-LIVE PLAN
# ══════════════════════════════════════════════════════════════════════════════
story.append(PageBreak())
story.append(banner("HOW EACH AGENT FEEDS THE DASHBOARD"))
story.append(Spacer(1, 8))

feed_rows = [
    ("Agent 1", "Webinar Funnel",      "#FF6600", "n8n (VPS)",         "Registrations, show-up, no-show, calls booked -> GHL tags -> Looker Sheet"),
    ("Agent 2", "Paid Acquisition",    "#2980b9", "LinkedIn Ads API",   "Spend, impressions, clicks, CPL, CPA, creative CTR -> pulled weekly by n8n"),
    ("Agent 3", "Content Authority",   "#8e44ad", "LinkedIn API",       "Post impressions, engagement, follower growth, organic sign-ups -> n8n"),
    ("Agent 4", "LinkedIn Outreach",   "#ea4b71", "HeyReach API",       "Requests sent, acceptance rate, DM replies, calls booked -> n8n"),
    ("Agent 5", "Email Nurture",       "#16a085", "GHL Native",         "Open rate, click rate, replies, deliverability, calls from email -> GHL reporting"),
    ("Agent 6", "Revenue Pipeline",    "#1e3a5f", "GHL Pipeline",       "Pipeline stages, deal values, proposals, closed revenue -> GHL + Looker Studio"),
]

_hdr2 = Table([[
    Paragraph("AGENT",    s("fh0", fontName="Helvetica-Bold", fontSize=7.5, textColor=WHITE, leading=10)),
    Paragraph("NAME",     s("fh1", fontName="Helvetica-Bold", fontSize=7.5, textColor=WHITE, leading=10)),
    Paragraph("SOURCE",   s("fh2", fontName="Helvetica-Bold", fontSize=7.5, textColor=WHITE, leading=10)),
    Paragraph("DATA SENT TO DASHBOARD", s("fh3", fontName="Helvetica-Bold", fontSize=7.5, textColor=WHITE, leading=10)),
]], colWidths=[0.55*inch, 1.35*inch, 1.1*inch, 4.0*inch])
_hdr2.setStyle(TableStyle([
    ("BACKGROUND", (0,0),(-1,-1), NAVY),
    ("TOPPADDING", (0,0),(-1,-1), 7), ("BOTTOMPADDING",(0,0),(-1,-1), 7),
    ("LEFTPADDING",(0,0),(-1,-1), 6), ("RIGHTPADDING", (0,0),(-1,-1), 6),
]))
story.append(_hdr2)

for _i, (agt, name, col, src, data) in enumerate(feed_rows):
    _bg = LGRAY if _i % 2 == 0 else WHITE
    _row = Table([[
        Paragraph(agt,  s(f"fa{_i}", fontName="Helvetica-Bold", fontSize=8, textColor=WHITE, leading=11, alignment=TA_CENTER)),
        Paragraph(name, s(f"fn{_i}", fontName="Helvetica-Bold", fontSize=8, textColor=colors.HexColor(col), leading=11)),
        Paragraph(src,  s(f"fs{_i}", fontName="Helvetica",      fontSize=7.5, textColor=GRAY, leading=11)),
        Paragraph(data, s(f"fd{_i}", fontName="Helvetica",      fontSize=7.5, textColor=DGRAY, leading=11)),
    ]], colWidths=[0.55*inch, 1.35*inch, 1.1*inch, 4.0*inch])
    _row.setStyle(TableStyle([
        ("BACKGROUND",  (0,0),(0,-1), colors.HexColor(col)),
        ("BACKGROUND",  (1,0),(1,-1), _bg),
        ("BACKGROUND",  (2,0),(2,-1), _bg),
        ("BACKGROUND",  (3,0),(3,-1), _bg),
        ("TOPPADDING",    (0,0),(-1,-1), 6), ("BOTTOMPADDING",(0,0),(-1,-1), 6),
        ("LEFTPADDING",   (0,0),(-1,-1), 6), ("RIGHTPADDING", (0,0),(-1,-1), 6),
        ("VALIGN",        (0,0),(-1,-1), "TOP"),
        ("LINEBELOW",     (0,0),(-1,-1), 0.3, colors.HexColor("#cccccc")),
    ]))
    story.append(_row)

story.append(Spacer(1, 14))
story.append(banner("DASHBOARD GO-LIVE PLAN - SECTION BY SECTION"))
story.append(Spacer(1, 8))

golive = [
    ["Section",           "Goes Live",      "Depends On",            "Status"],
    ["Agent 1 Panel",     "Month 1",        "Agent 1 built",         "Coming Soon"],
    ["Agent 2 Panel",     "Month 1",        "Agent 2 built",         "Coming Soon"],
    ["Full Funnel View",  "Month 1",        "Agents 1 + 2 + GHL",   "Coming Soon"],
    ["Revenue Pipeline",  "Month 1",        "GHL pipeline setup",   "Coming Soon"],
    ["Agent 3 Panel",     "Month 2",        "Agent 3 built",         "Scheduled"],
    ["Agent 4 Panel",     "Month 2",        "Agent 4 + HeyReach",   "Scheduled"],
    ["Agent 5 Panel",     "Month 3",        "Agent 5 built",         "Scheduled"],
    ["Attribution Split", "Month 3",        "All agents live",       "Scheduled"],
]
gl_tbl = Table(golive, colWidths=[1.8*inch, 1.0*inch, 2.0*inch, 2.2*inch])
gl_tbl.setStyle(TableStyle([
    ("BACKGROUND",    (0,0),(-1,0),  NAVY),
    ("FONTNAME",      (0,0),(-1,0),  "Helvetica-Bold"),
    ("FONTNAME",      (0,1),(-1,-1), "Helvetica"),
    ("FONTSIZE",      (0,0),(-1,-1), 8.5),
    ("TEXTCOLOR",     (0,0),(-1,0),  WHITE),
    ("TEXTCOLOR",     (0,1),(-1,-1), DGRAY),
    ("ROWBACKGROUNDS",(0,1),(-1,-1), [LGRAY, WHITE]),
    ("TOPPADDING",    (0,0),(-1,-1), 6), ("BOTTOMPADDING",(0,0),(-1,-1), 6),
    ("LEFTPADDING",   (0,0),(-1,-1), 8), ("RIGHTPADDING", (0,0),(-1,-1), 8),
    ("GRID",          (0,0),(-1,-1), 0.3, colors.HexColor("#cccccc")),
    ("FONTNAME",      (3,1),(-1,-1), "Helvetica-Bold"),
    ("TEXTCOLOR",     (3,1),(3,4),   colors.HexColor("#e67e22")),
    ("TEXTCOLOR",     (3,5),(-1,-1), GRAY),
]))
story.append(gl_tbl)
story.append(Spacer(1, 10))
story.append(Paragraph(
    "The dashboard is built once and expands automatically as each agent goes live. "
    "By Month 3, Randy has full visibility across every channel, every dollar, and every lead - in one URL.",
    s("final", fontName="Helvetica-Bold", fontSize=10, textColor=NAVY, leading=15, alignment=TA_CENTER)))

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 8 - EMAIL MARKETING + BUYING INTENT PANELS
# ══════════════════════════════════════════════════════════════════════════════
story.append(PageBreak())
story.append(banner("EMAIL MARKETING DASHBOARD - DEEP DIVE"))
story.append(Spacer(1, 6))
story.append(cs_bar())
story.append(Spacer(1, 8))
story.append(Paragraph(
    "Beyond open rates - this panel tracks the full health of Randy's email list, "
    "every active sequence, deliverability, and exactly how much revenue email is generating.",
    s("em_intro", fontName="Helvetica-Oblique", fontSize=9, textColor=GRAY, leading=13)))
story.append(Spacer(1, 8))

# Top 4 email metrics
_em_metrics = ["Total List Size", "Weekly Growth", "Avg Open Rate", "Revenue from Email"]
_em_r1 = [Paragraph(m, s(f"em1{i}", fontName="Helvetica-Bold", fontSize=7, textColor=GRAY, leading=9, alignment=TA_CENTER)) for i,m in enumerate(_em_metrics)]
_em_r2 = [Paragraph("- -", s(f"em2{i}", fontName="Helvetica-Bold", fontSize=18, textColor=MGRAY, leading=22, alignment=TA_CENTER)) for i in range(4)]
_em_r3 = [Paragraph("coming soon", s(f"em3{i}", fontName="Helvetica-Oblique", fontSize=6.5, textColor=CS_TEXT, leading=9, alignment=TA_CENTER)) for i in range(4)]
_em_top = Table([_em_r1, _em_r2, _em_r3], colWidths=[1.75*inch]*4)
_em_top.setStyle(TableStyle([
    ("BACKGROUND",    (0,0),(-1,-1), CS_BG),
    ("GRID",          (0,0),(-1,-1), 0.5, colors.HexColor("#eeeeee")),
    ("TOPPADDING",    (0,0),(-1,0), 8), ("BOTTOMPADDING",(0,0),(-1,0), 2),
    ("TOPPADDING",    (0,1),(-1,1), 2), ("BOTTOMPADDING",(0,1),(-1,1), 2),
    ("TOPPADDING",    (0,2),(-1,2), 0), ("BOTTOMPADDING",(0,2),(-1,2), 8),
]))
story.append(_em_top)
story.append(Spacer(1, 8))

_email_rows = [
    ("Total List Size",        TEAL,   "All active contacts enrolled in at least one GHL email sequence"),
    ("Weekly List Growth",     TEAL,   "New contacts added to email sequences this week from all sources"),
    ("Active Sequences",       TEAL,   "Number of email sequences currently running (confirmation, nurture, reactivation, post-webinar)"),
    ("Best Sequence",          TEAL,   "Highest open-rate sequence this cycle - name + open rate %"),
    ("Confirmation Seq.",      NAVY,   "Open rate for Agent 1 confirmation + reminder emails - benchmark: 55%+"),
    ("Nurture Seq.",           NAVY,   "Open rate for pre-webinar authority emails - benchmark: 35%+"),
    ("Post-Webinar Seq.",      NAVY,   "Open rate for attended + no-show follow-up emails - benchmark: 40%+"),
    ("Reactivation Seq.",      NAVY,   "Open rate for cold lead re-engagement - benchmark: 22%+"),
    ("Deliverability Score",   ORANGE, "% of emails landing in inbox vs spam - target: 98%+ (SPF/DKIM/DMARC health)"),
    ("Bounce Rate",            ORANGE, "Hard + soft bounces - healthy: < 2%. High bounce = list hygiene needed"),
    ("Unsubscribe Rate",       ORANGE, "% opting out - healthy: < 0.5%. Spike = messaging or frequency issue"),
    ("Revenue Attributed",     GREEN,  "Closed deal value where email was the last touch before call booking"),
    ("A/B Tests Running",      GREEN,  "Current subject line or CTA split tests and which variant is winning"),
    ("List Health Score",      GREEN,  "Combined score: engagement + deliverability + bounce + unsubscribe rate"),
]

for _label, _col, _desc in _email_rows:
    _row = Table([[
        Paragraph(_label, s(f"eml{_label}", fontName="Helvetica-Bold", fontSize=8.5, textColor=_col, leading=12)),
        Paragraph(_desc,  s(f"emd{_label}", fontName="Helvetica", fontSize=8.5, textColor=DGRAY, leading=12)),
        Paragraph("- coming soon -", s(f"emv{_label}", fontName="Helvetica-Oblique", fontSize=8, textColor=MGRAY, leading=12, alignment=TA_RIGHT)),
    ]], colWidths=[1.65*inch, 3.65*inch, 1.7*inch])
    _row.setStyle(TableStyle([
        ("BACKGROUND",    (0,0),(-1,-1), CS_BG),
        ("TOPPADDING",    (0,0),(-1,-1), 5), ("BOTTOMPADDING",(0,0),(-1,-1), 5),
        ("LEFTPADDING",   (0,0),(-1,-1), 8), ("RIGHTPADDING", (0,0),(-1,-1), 8),
        ("LINEBELOW",     (0,0),(-1,-1), 0.4, colors.HexColor("#dddddd")),
        ("VALIGN",        (0,0),(-1,-1), "MIDDLE"),
    ]))
    story.append(_row)

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 9 - BUYING INTENT DASHBOARD
# ══════════════════════════════════════════════════════════════════════════════
story.append(PageBreak())
story.append(banner("BUYING INTENT DASHBOARD - WHO IS READY TO BUY?"))
story.append(Spacer(1, 6))
story.append(cs_bar())
story.append(Spacer(1, 8))
story.append(Paragraph(
    "<b>The most powerful panel in the dashboard.</b> n8n tracks every behavioral signal across all agents - "
    "email opens, webinar attendance, link clicks, page visits, DM replies - and assigns each lead "
    "an Intent Score from 0-100. Randy sees exactly who is ready to buy right now, without lifting a finger.",
    s("bi_intro", fontName="Helvetica", fontSize=10, textColor=DGRAY, leading=15, spaceAfter=8)))

# Intent tier visual
def make_intent_tiers():
    TW = 504; H = 72
    d  = Drawing(TW, H)
    tiers = [
        ("HOT",        "Score 80-100",  "Ready to buy - call today",   "#e74c3c", 0),
        ("WARM",       "Score 50-79",   "Prioritise for outreach",     "#e67e22", 1),
        ("INTERESTED", "Score 25-49",   "Keep in sequence",            "#f1c40f", 2),
        ("COLD",       "Score 0-24",    "Long-term nurture",           "#95a5a6", 3),
    ]
    bw = TW / 4; bh = 58
    for i, (label, score, desc, col, _) in enumerate(tiers):
        x = i * bw
        c = colors.HexColor(col)
        d.add(Rect(x, 14, bw-4, bh, fillColor=c, strokeWidth=0))
        d.add(String(x+bw/2-2, 14+bh-14, label, fontName="Helvetica-Bold", fontSize=9,  fillColor=WHITE, textAnchor="middle"))
        d.add(String(x+bw/2-2, 14+bh-28, score, fontName="Helvetica-Bold", fontSize=8,  fillColor=WHITE, textAnchor="middle"))
        d.add(String(x+bw/2-2, 14+bh-42, desc,  fontName="Helvetica",      fontSize=7,  fillColor=WHITE, textAnchor="middle"))
        d.add(String(x+bw/2-2, 4,         "- -", fontName="Helvetica-Bold", fontSize=10, fillColor=colors.HexColor(col), textAnchor="middle"))
    return d

story.append(make_intent_tiers())
story.append(Spacer(1, 6))
story.append(Paragraph("Each tier count updates automatically as leads cross scoring thresholds throughout the day.", S_note))
story.append(Spacer(1, 10))

# How intent is scored
story.append(banner("HOW INTENT SCORE IS CALCULATED"))
story.append(Spacer(1, 8))

def make_scoring_visual():
    TW = 504; H = 180
    d  = Drawing(TW, H)
    signals = [
        ("Clicked Calendly link (didn't book)",  "+50 pts", "#e74c3c"),
        ("Booked call + cancelled",              "+45 pts", "#e74c3c"),
        ("Attended live webinar",                "+40 pts", "#e67e22"),
        ("Replied to DM / email",                "+35 pts", "#e67e22"),
        ("Clicked call-to-action in email",      "+30 pts", "#f39c12"),
        ("Visited landing page 2+ times",        "+25 pts", "#f39c12"),
        ("Opened 3+ emails in 7 days",           "+20 pts", "#27ae60"),
        ("Watched webinar replay",               "+20 pts", "#27ae60"),
        ("Clicked any email link",               "+15 pts", "#2980b9"),
        ("Opened single email",                  "+10 pts", "#2980b9"),
    ]
    row_h = 16; col1_w = 340; col2_w = 70
    for i, (signal, pts, col) in enumerate(signals):
        y = H - 10 - i * row_h
        bg = colors.HexColor("#f8f8f8") if i % 2 == 0 else WHITE
        d.add(Rect(0, y-12, TW, row_h-1, fillColor=bg, strokeWidth=0))
        d.add(Rect(0, y-12, 6, row_h-1, fillColor=colors.HexColor(col), strokeWidth=0))
        d.add(String(14, y-4, signal, fontName="Helvetica", fontSize=8, fillColor=DGRAY, textAnchor="start"))
        d.add(Rect(col1_w+10, y-11, col2_w, row_h-2, fillColor=colors.HexColor(col), strokeWidth=0))
        d.add(String(col1_w+10+col2_w/2, y-4, pts, fontName="Helvetica-Bold", fontSize=8, fillColor=WHITE, textAnchor="middle"))
        d.add(String(TW-5, y-4, "n8n tracks automatically", fontName="Helvetica-Oblique", fontSize=6.5, fillColor=GRAY, textAnchor="end"))
    return d

story.append(make_scoring_visual())
story.append(Spacer(1, 10))

# Intent dashboard detail rows
story.append(banner("INTENT DASHBOARD - METRIC DETAIL"))
story.append(Spacer(1, 8))

_intent_rows = [
    ("Hot Leads (80-100)",    colors.HexColor("#e74c3c"),
     "Leads with score 80+ - n8n alerts BrndGuru + Randy via email/Slack the moment a lead crosses this threshold"),
    ("Warm Leads (50-79)",    colors.HexColor("#e67e22"),
     "High-priority nurture targets - these leads need one more touchpoint to push them to Hot"),
    ("Interested (25-49)",    colors.HexColor("#f39c12"),
     "Engaged but not urgent - keep in email + LinkedIn sequence, monitor for score jumps"),
    ("Cold Leads (0-24)",     colors.HexColor("#95a5a6"),
     "Low engagement - in long-term nurture sequence, retargeted via Agent 2 LinkedIn ads"),
    ("Avg. Intent Score",     NAVY,
     "Average score across all active leads - rising average = messaging is working"),
    ("Score Velocity",        NAVY,
     "How fast leads are moving up the scoring tiers week over week - measures funnel momentum"),
    ("Top Signal This Week",  PURPLE,
     "The intent signal that fired most frequently - tells you which touchpoint is driving the most engagement"),
    ("Intent -> Booked Rate",  GREEN,
     "% of Hot leads (80+) who go on to book a Calendly call - target: 60%+"),
    ("Avg. Days to Intent",   GREEN,
     "Average days from registration to reaching score 80+ - shorter = better funnel efficiency"),
    ("Re-Engaged This Week",  GREEN,
     "Cold leads who were inactive and suddenly triggered an intent signal - flag for personal outreach"),
    ("Top 5 Hottest Leads",   ORANGE,
     "Ranked list of the 5 contacts with the highest intent scores right now - their name, score, and last action"),
]

for _label, _col, _desc in _intent_rows:
    _row = Table([[
        Paragraph(_label, s(f"il{_label}", fontName="Helvetica-Bold", fontSize=8.5, textColor=_col, leading=12)),
        Paragraph(_desc,  s(f"id{_label}", fontName="Helvetica",      fontSize=8.5, textColor=DGRAY, leading=12)),
        Paragraph("- coming soon -", s(f"iv{_label}", fontName="Helvetica-Oblique", fontSize=8, textColor=MGRAY, leading=12, alignment=TA_RIGHT)),
    ]], colWidths=[1.65*inch, 3.65*inch, 1.7*inch])
    _row.setStyle(TableStyle([
        ("BACKGROUND",    (0,0),(-1,-1), CS_BG),
        ("TOPPADDING",    (0,0),(-1,-1), 5), ("BOTTOMPADDING",(0,0),(-1,-1), 5),
        ("LEFTPADDING",   (0,0),(-1,-1), 8), ("RIGHTPADDING", (0,0),(-1,-1), 8),
        ("LINEBELOW",     (0,0),(-1,-1), 0.4, colors.HexColor("#dddddd")),
        ("VALIGN",        (0,0),(-1,-1), "MIDDLE"),
    ]))
    story.append(_row)

story.append(Spacer(1, 10))

# How intent data flows
_flow = Table([[
    Paragraph("HOW IT WORKS", s("hw", fontName="Helvetica-Bold", fontSize=9, textColor=NAVY, leading=13)),
    Paragraph(
        "Every time a lead opens an email, clicks a link, visits the landing page, attends the webinar, "
        "or replies to a DM - n8n detects that signal via GHL webhooks, LinkedIn API, or Zoom API, "
        "adds the points to a custom field in GHL, and updates the Intent Score. "
        "When a lead crosses 80 points, n8n sends an instant alert. "
        "The 'Top 5 Hottest Leads' list refreshes every hour. "
        "Randy always knows exactly who to call - without checking anything manually.",
        s("hwt", fontName="Helvetica", fontSize=9, textColor=DGRAY, leading=14)),
]], colWidths=[1.3*inch, 5.7*inch])
_flow.setStyle(TableStyle([
    ("BACKGROUND",    (0,0),(-1,-1), colors.HexColor("#fff5ee")),
    ("BOX",           (0,0),(-1,-1), 1.5, ORANGE),
    ("TOPPADDING",    (0,0),(-1,-1), 10), ("BOTTOMPADDING",(0,0),(-1,-1), 10),
    ("LEFTPADDING",   (0,0),(-1,-1), 10), ("RIGHTPADDING", (0,0),(-1,-1), 10),
    ("VALIGN",        (0,0),(-1,-1), "TOP"),
]))
story.append(_flow)

doc.build(story, onFirstPage=cover_draw, onLaterPages=later_pages)
print("PDF generated:", OUTPUT)
