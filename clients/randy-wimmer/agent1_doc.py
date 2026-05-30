from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import ParagraphStyle
from reportlab.lib.units import inch
from reportlab.lib import colors
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle,
    KeepTogether, PageBreak
)
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.graphics.shapes import Drawing, Rect, String, Line, Polygon
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

OUTPUT = "/home/user/Brnd-Guru/clients/randy-wimmer/Agent1_Webinar_Funnel.pdf"

doc = SimpleDocTemplate(
    OUTPUT, pagesize=letter,
    leftMargin=0.75*inch, rightMargin=0.75*inch,
    topMargin=0.88*inch, bottomMargin=0.65*inch,
)

def s(name, **kw):
    return ParagraphStyle(name, **kw)

S_body  = s("bd", fontName="Helvetica",       fontSize=10, textColor=DGRAY, leading=15, spaceAfter=4)
S_note  = s("nt", fontName="Helvetica-Oblique",fontSize=8, textColor=GRAY,  leading=12, alignment=TA_CENTER)
S_white = s("wh", fontName="Helvetica-Bold",  fontSize=10, textColor=WHITE, leading=14, alignment=TA_CENTER)

def banner(title):
    tbl = Table([["", Paragraph(title, s("bt", fontName="Helvetica-Bold", fontSize=13,
                  textColor=WHITE, leading=18))]], colWidths=[0.08*inch, 6.92*inch])
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
    canvas.drawString(bx, H - 165, "A G E N T   1   —   E V E N T   E N G I N E")

    canvas.setFont("Helvetica-Bold", 42)
    canvas.setFillColor(WHITE)
    canvas.drawString(bx, H - 210, "Webinar Funnel")
    canvas.setFillColor(ORANGE)
    canvas.drawString(bx, H - 258, "Agent")
    canvas.setFont("Helvetica", 13)
    canvas.setFillColor(colors.HexColor("#aaaaaa"))
    canvas.drawString(bx, H - 284, "Your Core Revenue Machine — Running 24/7 on Autopilot")

    canvas.setFillColor(ORANGE)
    canvas.rect(bx, H - 304, W - bx - 52, 2, fill=1, stroke=0)

    bw2   = W - bx - 52
    box_y = H - 422
    box_h = 108
    # Dark body of box
    canvas.setFillColor(colors.HexColor("#1a1a1a"))
    canvas.rect(bx, box_y, bw2, box_h, fill=1, stroke=0)
    # Orange header strip at top of box
    canvas.setFillColor(ORANGE)
    canvas.rect(bx, box_y + box_h - 22, bw2, 22, fill=1, stroke=0)
    # Orange border around full box
    canvas.setStrokeColor(ORANGE); canvas.setLineWidth(1)
    canvas.rect(bx, box_y, bw2, box_h, fill=0, stroke=1)
    # Label — white text inside the orange strip
    canvas.setFont("Helvetica-Bold", 8.5)
    canvas.setFillColor(WHITE)
    canvas.drawString(bx + 12, box_y + box_h - 15, "W H A T   T H I S   A G E N T   D O E S :")
    # Body lines — inside the dark area
    canvas.setFont("Helvetica", 11)
    canvas.setFillColor(WHITE)
    for i, line in enumerate([
        "Fills your webinars with the right people,",
        "follows up automatically, and converts",
        "attendees into booked sales calls — 24/7.",
    ]):
        canvas.drawString(bx + 12, box_y + box_h - 44 - i*18, line)

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
    for i, (t, sub) in enumerate([("Automated","Zero manual work"),("24/7 Active","Works while you sleep"),("Fully Tracked","Every click measured")]):
        cx = bx + i * box_w + box_w / 2
        if i > 0:
            canvas.setStrokeColor(colors.HexColor("#333333")); canvas.setLineWidth(0.5)
            canvas.line(bx + i*box_w, H-570, bx + i*box_w, H-510)
        canvas.setFont("Helvetica-Bold", 12); canvas.setFillColor(ORANGE)
        canvas.drawCentredString(cx, H - 536, t)
        canvas.setFont("Helvetica", 8); canvas.setFillColor(colors.HexColor("#aaaaaa"))
        canvas.drawCentredString(cx, H - 552, sub)

    canvas.setFillColor(colors.HexColor("#080808"))
    canvas.rect(0, 0, W, 44, fill=1, stroke=0)
    canvas.setFont("Helvetica-Bold", 9); canvas.setFillColor(ORANGE)
    canvas.drawString(50, 16, "brndguruofficial@gmail.com")
    canvas.setFont("Helvetica", 8); canvas.setFillColor(colors.HexColor("#666666"))
    canvas.drawRightString(W - 50, 16, "Government Contracting Academy — Agent 1 Breakdown")
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
    canvas.drawString(0.75*inch + 58, H - 18, "Agent 1 — Webinar Funnel Agent")
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
# PAGE 2 — WHAT IS AGENT 1
# ══════════════════════════════════════════════════════════════════════════════
story.append(banner("WHAT IS AGENT 1?"))
story.append(Spacer(1, 10))
story.append(Paragraph(
    "<b>Agent 1 is the heart of your entire revenue system.</b><br/><br/>"
    "It takes a complete stranger — someone who saw an ad or received an email — and walks them through "
    "a structured journey until they're sitting on a booked call with you, ready to hear about ISO certification. "
    "No manual follow-up. No missed leads. No forgetting to send reminders. It runs 24/7.",
    s("intro", fontName="Helvetica", fontSize=10.5, textColor=DGRAY, leading=17, spaceAfter=12)))

# Before / After
ba = Table([
    [Paragraph("WITHOUT AGENT 1", s("bh", fontName="Helvetica-Bold", fontSize=11, textColor=WHITE, leading=15, alignment=TA_CENTER)),
     Paragraph("WITH AGENT 1",    s("ah", fontName="Helvetica-Bold", fontSize=11, textColor=WHITE, leading=15, alignment=TA_CENTER))],
    [Paragraph("✗  Manually send every follow-up\n✗  Registrants forget to show up\n✗  No-shows never hear from you again\n✗  No replay sent automatically\n✗  Inconsistent call bookings",
               s("bc", fontName="Helvetica", fontSize=9.5, textColor=RED, leading=17)),
     Paragraph("✓  All emails fire automatically\n✓  T-24hr → T-3hr → T-15min reminders\n✓  No-show replay sequence fires instantly\n✓  Re-engagement sequence built in\n✓  Every email has a call-booking CTA",
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
stats = [("40%+","Show-Up Rate\nTarget",ORANGE),("3–5","Calls Booked\nPer Webinar",GREEN),("11","Emails + SMS\nIn Sequence",NAVY),("$0","Extra Tools\nNeeded",BLUE)]
r1 = [Paragraph(n, s(f"n{i}", fontName="Helvetica-Bold", fontSize=24, textColor=c, leading=30, alignment=TA_CENTER)) for i,(n,l,c) in enumerate(stats)]
r2 = [Paragraph(l, s(f"l{i}", fontName="Helvetica",      fontSize=8,  textColor=DGRAY, leading=11, alignment=TA_CENTER)) for i,(n,l,c) in enumerate(stats)]
st = Table([r1,r2], colWidths=[1.75*inch]*4)
st.setStyle(TableStyle([
    ("GRID",          (0,0),(-1,-1), 0.5, colors.HexColor("#dddddd")),
    ("BACKGROUND",    (0,0),(-1,-1), LGRAY),
    ("TOPPADDING",    (0,0),(-1,0), 14), ("BOTTOMPADDING",(0,0),(-1,0), 4),
    ("TOPPADDING",    (0,1),(-1,1), 2),  ("BOTTOMPADDING",(0,1),(-1,1), 14),
    ("VALIGN",        (0,0),(-1,-1), "MIDDLE"),
]))
story.append(st)

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 3 — THE 7-STAGE JOURNEY
# ══════════════════════════════════════════════════════════════════════════════
story.append(PageBreak())
story.append(banner("THE 7-STAGE JOURNEY"))
story.append(Spacer(1, 8))
story.append(Paragraph(
    "Every person who registers goes through this exact automated journey — "
    "from clicking 'Register' to booking a call with you.",
    S_body))
story.append(Spacer(1, 10))

def make_pipeline():
    stages = [
        ("1","TRAFFIC",   "#3498db"),
        ("2","REGISTER",  "#8e44ad"),
        ("3","CONFIRM",   "#e67e22"),
        ("4","NURTURE",   "#16a085"),
        ("5","WEBINAR",   "#FF6600"),
        ("6","FOLLOW-UP", "#e74c3c"),
        ("7","CALL\nBOOKED","#27ae60"),
    ]
    TW = 504; n = len(stages); aw = 14
    bw = (TW - (n-1)*aw) / n; bh = 54
    d = Drawing(TW, bh + 14)
    for i, (num, lbl, col) in enumerate(stages):
        x = i*(bw+aw)
        d.add(Rect(x, 10, bw, bh, fillColor=colors.HexColor(col), strokeWidth=0))
        d.add(String(x+7, 10+bh-16, num, fontName="Helvetica-Bold", fontSize=11, fillColor=WHITE))
        lines = lbl.split('\n')
        mid = 10 + bh/2
        if len(lines) == 2:
            d.add(String(x+bw/2, mid,    lines[0], fontName="Helvetica-Bold", fontSize=7.5, fillColor=WHITE, textAnchor="middle"))
            d.add(String(x+bw/2, mid-12, lines[1], fontName="Helvetica-Bold", fontSize=7.5, fillColor=WHITE, textAnchor="middle"))
        else:
            d.add(String(x+bw/2, mid-5, lbl, fontName="Helvetica-Bold", fontSize=8, fillColor=WHITE, textAnchor="middle"))
        if i < n-1:
            ax = x+bw; ay = 10+bh/2
            d.add(Polygon([ax,ay+8, ax+aw-1,ay, ax,ay-8], fillColor=colors.HexColor(col), strokeWidth=0))
    return d

story.append(make_pipeline())
story.append(Spacer(1, 14))

stages_detail = [
    ("1","TRAFFIC",   "#3498db","People discover the webinar via LinkedIn ads, organic posts, email campaigns, or direct outreach. Every channel points to one place — the registration page."),
    ("2","REGISTER",  "#8e44ad","They land on a clean registration page built in GoHighLevel on fedgovstartup.com. Name, email, phone captured instantly. Lead enters Randy's CRM automatically."),
    ("3","CONFIRM",   "#e67e22","Instantly after registering: confirmation email, Zoom link, lead magnet download, and a calendar add button (Google / Apple / Outlook). They feel welcomed and prepared."),
    ("4","NURTURE",   "#16a085","2–3 emails over the next few days build trust and anticipation. They arrive at the live webinar already knowing who Randy is and why it matters to their business."),
    ("5","WEBINAR",   "#FF6600","Randy delivers the live session on Zoom. The system (n8n) tracks exactly who attended and who didn't. This triggers two completely different follow-up paths automatically."),
    ("6","FOLLOW-UP", "#e74c3c","Attendees get a post-webinar sequence. No-shows get a replay + recovery sequence. Every email in both tracks has one goal: get them to book a call with Randy."),
    ("7","CALL BOOKED","#27ae60","The lead clicks Randy's Calendly link and books a 30-min call. They enter the sales pipeline as a warm, educated, pre-qualified prospect ready to hear about ISO certification."),
]
for num, stage, col, desc in stages_detail:
    tbl = Table([[
        Paragraph(num, s(f"sn{num}", fontName="Helvetica-Bold", fontSize=15, textColor=WHITE, leading=20, alignment=TA_CENTER)),
        [Paragraph(stage, s(f"sh{num}", fontName="Helvetica-Bold", fontSize=10, textColor=colors.HexColor(col), leading=14, spaceAfter=2)),
         Paragraph(desc,  s(f"sd{num}", fontName="Helvetica",      fontSize=9,  textColor=DGRAY, leading=13))],
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
# PAGE 4 — EMAIL TIMELINE
# ══════════════════════════════════════════════════════════════════════════════
story.append(PageBreak())
story.append(banner("THE AUTOMATED EMAIL + SMS TIMELINE"))
story.append(Spacer(1, 8))
story.append(Paragraph(
    "Every message below fires automatically. Randy only needs to show up for the live webinar. "
    "Everything else — confirmation, reminders, follow-up, replay — is handled by the system.",
    S_body))
story.append(Spacer(1, 10))

def make_timeline():
    W = 504; H = 370; lx = 128
    d = Drawing(W, H)
    d.add(Line(lx, 8, lx, H-8, strokeColor=colors.HexColor("#e0e0e0"), strokeWidth=2))

    phases = [
        (H-8,  220, LBLUE,                       "BEFORE WEBINAR", BLUE),
        (215,  130, colors.HexColor("#fff3e0"),   "DAY OF WEBINAR", ORANGE),
        (125,  8,   LGREEN,                       "AFTER WEBINAR",  GREEN),
    ]
    for top, bot, bg, lbl, c in phases:
        d.add(Rect(lx+8, bot, W-lx-16, top-bot, fillColor=bg, strokeWidth=0))
        py = bot + (top-bot)/2
        d.add(Rect(W-90, py-9, 82, 18, fillColor=c, strokeWidth=0))
        d.add(String(W-49, py-4, lbl, fontName="Helvetica-Bold", fontSize=5.5, fillColor=WHITE, textAnchor="middle"))

    emails = [
        (H-30,  "Instant",       "EMAIL", "Confirmation email + Zoom link + Lead Magnet",      BLUE),
        (H-68,  "Instant",       "SMS",   "Confirmation SMS: 'You're in! Here's your link'",   BLUE),
        (H-106, "Day +1",        "EMAIL", "Authority email — key GovCon insight to build trust",BLUE),
        (H-144, "3 days before", "EMAIL", "Agenda preview + quick win tip",                     BLUE),
        (H-182, "T-24 hours",    "EMAIL", "Reminder + Zoom link + 'Bring your questions'",      BLUE),
        (196,   "T-3 hours",     "EMAIL", "Today reminder — single CTA to join",                ORANGE),
        (158,   "T-15 min",      "SMS",   "'Starting in 15 min! Click here to join'",           ORANGE),
        (110,   "1 hr after",    "EMAIL", "Replay link + Book-a-Call CTA (primary action)",     GREEN),
        (70,    "24 hrs after",  "EMAIL", "Follow-up: 'Did you get a chance to book?'",         GREEN),
        (30,    "48 hrs after",  "EMAIL", "Final chance: urgency email + Bootcamp offer",        GREEN),
    ]
    for y, timing, badge, desc, col in emails:
        d.add(Rect(lx-6, y-6, 12, 12, fillColor=col, strokeWidth=0))
        d.add(Line(lx+6, y, lx+16, y, strokeColor=col, strokeWidth=0.8))
        bw = 40 if badge=="EMAIL" else 28
        bc = NAVY if badge=="EMAIL" else ORANGE
        d.add(Rect(lx+16, y-7, bw, 14, fillColor=bc, strokeWidth=0))
        d.add(String(lx+16+bw/2, y-3, badge, fontName="Helvetica-Bold", fontSize=5.5, fillColor=WHITE, textAnchor="middle"))
        d.add(String(lx-10, y-4, timing, fontName="Helvetica-Bold", fontSize=7, fillColor=col, textAnchor="end"))
        d.add(String(lx+16+bw+6, y-4, desc, fontName="Helvetica", fontSize=8, fillColor=colors.HexColor("#333333"), textAnchor="start"))
    return d

story.append(make_timeline())
story.append(Spacer(1, 8))
story.append(Paragraph("* No-shows receive a separate replay + re-engagement sequence — see next page.", S_note))

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 5 — THE SPLIT (ATTENDED vs NO-SHOW)
# ══════════════════════════════════════════════════════════════════════════════
story.append(PageBreak())
story.append(banner("WHAT HAPPENS AFTER THE WEBINAR"))
story.append(Spacer(1, 8))
story.append(Paragraph(
    "This is where most businesses lose leads. Agent 1 handles every attendee "
    "and every no-show with a separate, targeted automatic sequence.",
    S_body))
story.append(Spacer(1, 10))

def make_fork():
    W = 504; H = 290; cx = W/2
    d = Drawing(W, H)

    # WEBINAR ENDS
    wx = cx-80; wy = H-48
    d.add(Rect(wx, wy, 160, 36, fillColor=ORANGE, strokeWidth=0))
    d.add(String(cx, wy+13, "WEBINAR ENDS", fontName="Helvetica-Bold", fontSize=12, fillColor=WHITE, textAnchor="middle"))

    # Fork lines
    d.add(Line(cx, wy,      W*0.25, 192, strokeColor=GREEN, strokeWidth=1.5))
    d.add(Line(cx, wy,      W*0.75, 192, strokeColor=RED,   strokeWidth=1.5))

    # ATTENDED (left)
    ax = W*0.25-80
    d.add(Rect(ax, 160, 160, 34, fillColor=GREEN, strokeWidth=0))
    d.add(String(W*0.25, 173, "ATTENDED  ✓", fontName="Helvetica-Bold", fontSize=11, fillColor=WHITE, textAnchor="middle"))

    # NO-SHOW (right)
    nx = W*0.75-80
    d.add(Rect(nx, 160, 160, 34, fillColor=RED, strokeWidth=0))
    d.add(String(W*0.75, 173, "NO-SHOW", fontName="Helvetica-Bold", fontSize=11, fillColor=WHITE, textAnchor="middle"))

    att = ["Post-webinar email (1 hr)","Replay + Book-a-Call CTA","24-hr follow-up email","48-hr urgency email","Bootcamp offer (if no book)"]
    nos = ["Replay delivery (1 hr)","Re-engagement email (Day 2)","Last nudge: book a call (Day 5)","Invite to next webinar","Bootcamp offer"]

    for i, item in enumerate(att):
        bg = LGREEN if i%2==0 else WHITE
        d.add(Rect(ax-2, 155-20-i*20, 164, 18, fillColor=bg, strokeWidth=0))
        d.add(String(ax+5, 157-20-i*20, f"• {item}", fontName="Helvetica", fontSize=7.5, fillColor=GREEN, textAnchor="start"))

    for i, item in enumerate(nos):
        bg = LRED if i%2==0 else WHITE
        d.add(Rect(nx-2, 155-20-i*20, 164, 18, fillColor=bg, strokeWidth=0))
        d.add(String(nx+5, 157-20-i*20, f"• {item}", fontName="Helvetica", fontSize=7.5, fillColor=RED, textAnchor="start"))

    # Converge to CALL BOOKED
    by = 155 - 20 - 5*20  # = 55-20 = 35... let me compute: 155-20-100 = 35
    d.add(Line(W*0.25, 35, cx, 8, strokeColor=colors.HexColor("#cccccc"), strokeWidth=1))
    d.add(Line(W*0.75, 35, cx, 8, strokeColor=colors.HexColor("#cccccc"), strokeWidth=1))
    d.add(Rect(cx-80, 0, 160, 20, fillColor=NAVY, strokeWidth=0))
    d.add(String(cx, 6, "CALL BOOKED  →  Sales Pipeline", fontName="Helvetica-Bold", fontSize=8.5, fillColor=WHITE, textAnchor="middle"))
    return d

story.append(make_fork())
story.append(Spacer(1, 12))

ins = Table([[
    Paragraph("💡", s("ii", fontName="Helvetica-Bold", fontSize=16, textColor=ORANGE, leading=20)),
    Paragraph("<b>No lead gets left behind.</b> Whether someone attended or missed the webinar entirely, "
              "Agent 1 has a sequence built specifically for them. Every registered lead stays in the funnel "
              "until they book a call — or choose to unsubscribe.",
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
story.append(banner("THE 5 PLATFORMS POWERING AGENT 1"))
story.append(Spacer(1, 10))

platforms = [
    ("GHL",  "GoHighLevel",   NAVY,                        "The command center. Hosts the landing page, captures leads, sends all emails and SMS, manages the pipeline, and handles call booking. Everything lives here.","✅ Already subscribed","$97/month"),
    ("ZOOM", "Zoom Webinars", BLUE,                        "Delivers the live webinar. Records automatically for replay delivery. After the session ends, attendance data is pulled and pushed into GHL automatically.","✅ Already subscribed","Existing plan"),
    ("n8n",  "n8n Automation",colors.HexColor("#ea4b71"), "The invisible connector. After the webinar, n8n pulls who attended vs who didn't from Zoom and tags them in GHL — triggering the right follow-up sequence.","✅ Free (open-source)","FREE"),
    ("SG",   "SiteGround",    colors.HexColor("#16a085"), "Hosts fedgovstartup.com. DNS records (SPF / DKIM) are set up here so all emails from GHL land in the inbox — not the spam folder.","✅ Already subscribed","Existing plan"),
    ("CAL",  "Calendly",      ORANGE,                      "Randy's call booking page. Every CTA email links here. Leads book directly, it syncs with Randy's calendar, and sends automatic reminders to both parties.","✅ Already set up","Free tier"),
]
for abbr, name, col, desc, status, cost in platforms:
    tbl = Table([[
        Paragraph(abbr, s(f"pa{abbr}", fontName="Helvetica-Bold", fontSize=11, textColor=WHITE, leading=16, alignment=TA_CENTER)),
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

story.append(Spacer(1, 12))
story.append(banner("RANDY — YOUR 5-ITEM CHECKLIST TO GO LIVE"))
story.append(Spacer(1, 10))

checklist = [
    ("01","Confirm the first webinar topic",     "Recommendation: (a) Get On Contract Vehicles Without Extensive Past Performances!"),
    ("02","Pick the first webinar date + time",  "Choose a date 2–3 weeks out. Bi-weekly cadence going forward."),
    ("03","Confirm GHL sub-account setup",       "BrndGuru needs admin access inside your GHL account to start building."),
    ("04","Verify Zoom Webinar license",          "Confirm it's a Webinar license — not just a Meetings license (they're different)."),
    ("05","SiteGround DNS access confirmed",     "BrndGuru sets up SPF/DKIM records so all emails land in the inbox, not spam."),
]
for num, task, detail in checklist:
    tbl = Table([[
        Paragraph(num, s(f"cn{num}", fontName="Helvetica-Bold", fontSize=12, textColor=WHITE, leading=16, alignment=TA_CENTER)),
        [Paragraph(f"<b>{task}</b>", s(f"ct{num}", fontName="Helvetica-Bold", fontSize=10, textColor=NAVY, leading=14)),
         Paragraph(detail,           s(f"cd{num}", fontName="Helvetica",      fontSize=8.5,textColor=DGRAY, leading=12))],
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
    "Once all 5 items are confirmed, BrndGuru begins building immediately. "
    "Target: Agent 1 live within 14 business days.",
    s("final", fontName="Helvetica-Bold", fontSize=10, textColor=NAVY, leading=15, alignment=TA_CENTER)))

# ══════════════════════════════════════════════════════════════════════════════
# PAGE 7 — TECHNICAL WORKFLOW & TRIGGER MAP
# ══════════════════════════════════════════════════════════════════════════════
story.append(PageBreak())
story.append(banner("AGENT 1 — TECHNICAL WORKFLOW & TRIGGER MAP"))
story.append(Spacer(1, 5))
story.append(Paragraph(
    "Full automation trigger map — every step, every tool, every API action. For implementation reference.",
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

_PINK  = colors.HexColor("#ea4b71")
_TCOLS = {"GoHighLevel": NAVY, "n8n + Zoom API": _PINK, "Calendly + GHL": BLUE}

_triggers = [
    ("T1",  "Form Submit\n(GHL landing page)",     "GoHighLevel",
     "Contact created → tagged 'webinar-registered' → pipeline stage 'Registered'",
     "Zoom API: register contact\nStart Confirmation Workflow"),
    ("T2",  "Workflow Start\n(Instant)",            "GoHighLevel",
     "Email: confirmation + Zoom join link + lead magnet PDF\nSMS: 'You're in! Here's your link → [Zoom]'",
     "Wait +24 hrs → T3"),
    ("T3",  "Wait: +1 Day",                         "GoHighLevel",
     "Email: Authority content — GovCon insight to build trust and anticipation before webinar",
     "Wait +2 days → T4"),
    ("T4",  "Date Trigger\n−3 days before",         "GoHighLevel",
     "Email: Agenda preview + quick win tip to prime attendees and raise show-up intent",
     "→ T-24hr date trigger"),
    ("T5",  "Date Trigger\n−24 hours",              "GoHighLevel",
     "Email: Full reminder + Zoom join link + 'Bring your top question'",
     "→ T-3hr date trigger"),
    ("T6",  "Date Trigger\n−3 hours",               "GoHighLevel",
     "Email: Day-of reminder — single bold 'Join the Webinar' button, no distractions",
     "→ T-15min SMS trigger"),
    ("T7",  "Date Trigger\n−15 minutes",            "GoHighLevel",
     "SMS: 'Starting in 15 min! Tap to join → [Zoom link]'",
     "→ Live webinar runs"),
    ("T8",  "Zoom Webhook\nwebinar.ended (POST)",   "n8n + Zoom API",
     "n8n receives webhook → GET /past_webinars/{id}/attendees from Zoom API\nLoop all registrants: attended? → GHL PATCH tag 'attended' : PATCH tag 'no-show'",
     "GHL tag fires T9\nor T10 branch"),
    ("T9",  "GHL Tag Applied\n'attended'",          "GoHighLevel",
     "+1hr: replay + Book-a-Call CTA  |  +24hr: follow-up email  |  +48hr: urgency  |  +72hr: bootcamp offer (if no booking yet)",
     "Every email CTA\n→ Calendly link"),
    ("T10", "GHL Tag Applied\n'no-show'",           "GoHighLevel",
     "+1hr: replay delivery  |  Day 2: re-engagement  |  Day 5: book-a-call nudge  |  Day 7: invite to next webinar",
     "Every email CTA\n→ Calendly link"),
    ("T11", "Calendly Webhook\nbooking.created",    "Calendly + GHL",
     "Tag 'call-booked' applied → pipeline stage 'Sales Call Scheduled' → confirmation + calendar invite sent to both parties",
     "→ Sales pipeline\n(Agent 2 takes over)"),
]

for _i, (_num, _trig, _tool, _action, _fires) in enumerate(_triggers):
    _bg   = LGRAY if _i % 2 == 0 else WHITE
    _tc   = _TCOLS.get(_tool, BLUE)
    _hi   = _num == "T8"
    _nb   = ORANGE if _hi else colors.HexColor("#2c3e50") if _i % 3 == 0 else NAVY
    _hbg  = colors.HexColor("#fff8f0") if _hi else _bg
    _row  = Table([[
        Paragraph(_num, s(f"rn{_i}", fontName="Helvetica-Bold", fontSize=7.5,
                          textColor=WHITE, leading=10, alignment=TA_CENTER)),
        Paragraph(_trig.replace("\n","<br/>"),   s(f"rt{_i}", fontName="Helvetica-Bold", fontSize=7.5,
                          textColor=WHITE if _hi else NAVY, leading=10)),
        Paragraph(_tool.replace("\n","<br/>"),   s(f"rl{_i}", fontName="Helvetica-Bold", fontSize=6.5,
                          textColor=WHITE, leading=9, alignment=TA_CENTER)),
        Paragraph(_action.replace("\n","<br/>"), s(f"ra{_i}", fontName="Helvetica",      fontSize=7,
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

story.append(Spacer(1, 12))

# ── n8n NODE FLOW DIAGRAM ──────────────────────────────────────────────────
story.append(banner("n8n AUTOMATION: ZOOM → GHL NODE FLOW (T8 Detail)"))
story.append(Spacer(1, 6))

def make_n8n_flow():
    TW = 504; bw = 72; bh = 44
    d  = Drawing(TW, 110)
    nodes4 = [
        ("ZOOM\nWEBHOOK",    "#ea4b71"),
        ("GET\nATTENDEES",   "#3498db"),
        ("LOOP\nCONTACTS",   "#8e44ad"),
        ("IF\nATTENDED?",    "#e67e22"),
    ]
    unit = bw + 16
    mid  = 44 + bh // 2   # vertical center of main row = 66
    for i, (lbl, col) in enumerate(nodes4):
        x = i * unit
        c = colors.HexColor(col)
        d.add(Rect(x, 44, bw, bh, fillColor=c, strokeWidth=0))
        ll = lbl.split("\n")
        cy = 44 + bh / 2
        d.add(String(x+bw/2, cy+3,  ll[0], fontName="Helvetica-Bold", fontSize=7.5, fillColor=WHITE, textAnchor="middle"))
        d.add(String(x+bw/2, cy-9,  ll[1], fontName="Helvetica-Bold", fontSize=7.5, fillColor=WHITE, textAnchor="middle"))
        if i < 3:
            ax = x + bw
            d.add(Polygon([ax, mid+6, ax+15, mid, ax, mid-6], fillColor=c, strokeWidth=0))
    # Fork from IF node right edge
    fx = 3 * unit + bw
    # Green branch (up → attended)
    gx = fx + 18; gnx = gx + 20
    d.add(Line(fx, mid, gx, mid+22, strokeColor=GREEN, strokeWidth=1.5))
    d.add(Line(gx, mid+22, gnx, mid+22, strokeColor=GREEN, strokeWidth=1.5))
    d.add(Rect(gnx, mid+22-22, bw, 44, fillColor=GREEN, strokeWidth=0))
    d.add(String(gnx+bw/2, mid+22-6,  "TAG",        fontName="Helvetica-Bold", fontSize=7.5, fillColor=WHITE, textAnchor="middle"))
    d.add(String(gnx+bw/2, mid+22-18, "'attended'", fontName="Helvetica-Bold", fontSize=7,   fillColor=WHITE, textAnchor="middle"))
    d.add(String(fx+5, mid+16, "YES", fontName="Helvetica-Bold", fontSize=6, fillColor=GREEN, textAnchor="start"))
    # Red branch (down → no-show)
    d.add(Line(fx, mid, gx, mid-22, strokeColor=RED, strokeWidth=1.5))
    d.add(Line(gx, mid-22, gnx, mid-22, strokeColor=RED, strokeWidth=1.5))
    d.add(Rect(gnx, mid-22-22, bw, 44, fillColor=RED, strokeWidth=0))
    d.add(String(gnx+bw/2, mid-22-6,  "TAG",       fontName="Helvetica-Bold", fontSize=7.5, fillColor=WHITE, textAnchor="middle"))
    d.add(String(gnx+bw/2, mid-22-18, "'no-show'", fontName="Helvetica-Bold", fontSize=7,   fillColor=WHITE, textAnchor="middle"))
    d.add(String(fx+5, mid-14, "NO",  fontName="Helvetica-Bold", fontSize=6, fillColor=RED,   textAnchor="start"))
    # Arrow labels
    d.add(String(TW/2, 4, "Both tags instantly trigger a separate GHL automation workflow",
                 fontName="Helvetica-Oblique", fontSize=7, fillColor=GRAY, textAnchor="middle"))
    d.add(String(TW/2, 14, "GHL Pipeline tag → GoHighLevel Automation fires within seconds of webinar end",
                 fontName="Helvetica-Oblique", fontSize=7, fillColor=GRAY, textAnchor="middle"))
    return d

story.append(make_n8n_flow())
story.append(Spacer(1, 10))

# ── DNS DELIVERABILITY REFERENCE ───────────────────────────────────────────
story.append(banner("DNS & EMAIL DELIVERABILITY SETUP (SiteGround / fedgovstartup.com)"))
story.append(Spacer(1, 6))

_dns = [
    ["Record", "Type",  "Host",              "Value",                                              "Purpose"],
    ["SPF",    "TXT",   "@",                 "v=spf1 include:sendgrid.net ~all",                   "Authorises GHL (SendGrid) to send from domain"],
    ["DKIM",   "CNAME", "em._domainkey",     "em.domainkey.[GHL-ACCOUNT-ID].sendgrid.net",         "Cryptographic sender authentication"],
    ["DMARC",  "TXT",   "_dmarc",            "v=DMARC1; p=quarantine; rua=mailto:postmaster@fedgovstartup.com", "Quarantine unauthenticated email"],
    ["MX",     "MX",    "@",                 "mail.fedgovstartup.com (existing — do not change)",  "Inbound email — verify before changing"],
]
_dns_tbl = Table(_dns, colWidths=[0.52*inch, 0.48*inch, 1.12*inch, 2.93*inch, 1.95*inch])
_dns_tbl.setStyle(TableStyle([
    ("BACKGROUND",    (0,0),(-1,0),  NAVY),
    ("FONTNAME",      (0,0),(-1,0),  "Helvetica-Bold"),
    ("FONTNAME",      (0,1),(-1,-1), "Helvetica"),
    ("FONTSIZE",      (0,0),(-1,-1), 7.5),
    ("TEXTCOLOR",     (0,0),(-1,0),  WHITE),
    ("TEXTCOLOR",     (0,1),(-1,-1), DGRAY),
    ("ROWBACKGROUNDS",(0,1),(-1,-1), [LGRAY, WHITE]),
    ("TOPPADDING",    (0,0),(-1,-1), 5), ("BOTTOMPADDING",(0,0),(-1,-1), 5),
    ("LEFTPADDING",   (0,0),(-1,-1), 6), ("RIGHTPADDING", (0,0),(-1,-1), 6),
    ("GRID",          (0,0),(-1,-1), 0.3, colors.HexColor("#cccccc")),
]))
story.append(_dns_tbl)
story.append(Spacer(1, 6))
story.append(Paragraph(
    "⚠  BrndGuru configures all three DNS records. Randy only needs to grant SiteGround cPanel / DNS zone access.",
    s("dns_w", fontName="Helvetica-Bold", fontSize=8, textColor=ORANGE, leading=12)))

doc.build(story, onFirstPage=cover_draw, onLaterPages=later_pages)
print("PDF generated:", OUTPUT)
