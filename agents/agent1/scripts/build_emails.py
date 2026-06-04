#!/usr/bin/env python3
"""
Generate branded, responsive HTML emails for Agent 1 from a single content table.

Run:  python3 scripts/build_emails.py
Out:  emails/html/*.html  (one file per email, paste-ready into GHL)

The copy mirrors emails/SEQUENCE.md. Merge tags use GHL syntax: {{contact.first_name}},
{{custom_values.webinar_topic}}, etc. Buttons and links are styled inline so they survive
email clients. Edit CONTENT below and re-run to regenerate everything.
"""
import os
import re

OUT_DIR = os.path.join(os.path.dirname(__file__), "..", "emails", "html")

NAVY = "#0a1f44"
NAVY2 = "#0d2756"
GOLD = "#c8a44d"
GOLD2 = "#e0bd66"
INK = "#1a2230"
MUTED = "#5b6470"
PAPER = "#f4f6fa"

# (slug, subject, [paragraph-or-block...], optional primary CTA (label, href))
# A block that is a tuple ("cta", label, href) renders a button.
# A block that is a tuple ("list", [items]) renders a checkmark list.
# A plain string renders as a paragraph; \n inside becomes <br>.
CV = lambda k: "{{custom_values." + k + "}}"
FN = "{{contact.first_name}}"
CALENDLY = CV("calendly_link")

CONTENT = [
    ("01-confirmation",
     "You're registered! Here's everything you need, " + FN,
     [
        f"Hi {FN},",
        "You're confirmed for:",
        ("list", [
            f"📌 <b>{CV('webinar_topic')}</b>",
            f"🗓 {CV('webinar_date')} at {CV('webinar_time')}",
        ]),
        "This is the link you'll use to join — save it now:",
        ("cta", "Add to Calendar & Save My Link", CV("zoom_link")),
        f"While you wait, grab the free guide I put together for attendees: <a href=\"{CV('lead_magnet_link')}\">download it here</a>.",
        "This session is built for small businesses that want to win more government contracts without guessing. Come with your toughest question — I read them live.",
        "See you there,<br>Randy Wimmer<br><span style='color:#5b6470'>Founder, Government Contracting Academy · Author of “GOOD ENOUGH! to Launch Your Company”</span>",
     ], None),

    ("02-authority",
     "The #1 mistake small businesses make with government contracts",
     [
        f"Hi {FN},",
        f"Quick story before our session on {CV('webinar_date')}.",
        "Most small businesses chasing government work make the same mistake: they wait until they have “enough” past performance before they go after real contracts. So they sit on the sidelines for years.",
        "Here's the truth I've watched play out hundreds of times: you do <b>not</b> need a long track record to win. You need the right vehicle, the right teaming strategy, and a proposal that scores. That's exactly what we cover on the webinar.",
        "On the webinar I'll walk you through the parts that matter most.",
        ("cta", "Here's Your Join Link", CV("zoom_link")),
        "See you there,<br>Randy",
     ], None),

    ("03-agenda",
     "Here's exactly what we're covering on " + CV("webinar_date"),
     [
        f"Hi {FN},",
        f"We're 3 days out from “{CV('webinar_topic')}.” Here's the plan:",
        ("list", [
            "The biggest barrier small businesses <i>think</i> they have — and why it's mostly a myth",
            "The exact path to get in the game without years of past performance",
            "How to position so evaluators score you higher than bigger competitors",
            "Live Q&amp;A — bring your specific situation",
        ]),
        "Quick win before we meet: pull your SAM.gov registration and confirm your NAICS codes are current. Half the businesses I talk to leave easy opportunities on the table because their codes are wrong or incomplete.",
        ("cta", "Make Sure You're Set to Join", CV("zoom_link")),
        "See you soon,<br>Randy",
     ], None),

    ("04-reminder-24h",
     "Tomorrow at " + CV("webinar_time") + " — don't forget, " + FN,
     [
        f"Hi {FN},",
        "We go live <b>tomorrow</b>:",
        ("list", [
            f"📌 {CV('webinar_topic')}",
            f"🗓 {CV('webinar_date')} at {CV('webinar_time')}",
        ]),
        "Bring your single biggest question about winning government contracts. The people who ask usually walk away with the clearest next step.",
        ("cta", "Join Here", CV("zoom_link")),
        "See you tomorrow,<br>Randy",
     ], None),

    ("05-reminder-3h",
     "TODAY at " + CV("webinar_time") + " — we start in 3 hours",
     [
        f"Hi {FN},",
        "We're on in 3 hours. Short and simple:",
        ("cta", "Join the Webinar", CV("zoom_link")),
        f"Topic: {CV('webinar_topic')}<br>Time: {CV('webinar_time')}",
        "See you soon,<br>Randy",
     ], None),

    ("06-attended-replay-book",
     "Here's the replay + your next step, " + FN,
     [
        f"Hi {FN},",
        "Great to have you on today's session. As promised, here's the replay in case you want to revisit anything:",
        ("cta", "Watch the Replay", CV("replay_link")),
        "Now the important part. On the webinar I showed you the <i>what</i>. A strategy call is where we map the <i>how</i> for your business specifically — your codes, your targets, your fastest path to an award.",
        "I've opened a few free 30-minute strategy calls this week:",
        ("cta", "Book My Free Strategy Call", CALENDLY),
        "No pitch trap — just a focused look at your situation and the next move. Talk soon,<br>Randy",
     ], None),

    ("07-attended-followup",
     "Did you get a chance to watch? " + FN,
     [
        f"Hi {FN},",
        f"Following up on yesterday's session on {CV('webinar_topic')}.",
        "The contractors who move fastest turn what they learned into a plan within a few days — while it's fresh. That's the whole point of the free 30-minute strategy call.",
        "I still have a couple of spots open this week:",
        ("cta", "Grab a Time", CALENDLY),
        "If you've already booked — perfect, ignore this and I'll see you on the call.<br><br>Randy",
     ], None),

    ("08-attended-urgency",
     "Last chance, " + FN + " — only a couple of call spots left",
     [
        f"Hi {FN},",
        "I'm closing out this week's strategy calls and only have a couple of slots left.",
        "If getting on contract vehicles and winning real awards is on your radar this year, this is the easiest way to get a clear, personalized next step:",
        ("cta", "Book Before They Fill", CALENDLY),
        f"Not ready for a 1-on-1 yet? The Bootcamp is the lower-commitment way to get the full system at your own pace — <a href=\"{CV('bootcamp_link')}\">take a look here</a>.",
        "Either way, don't let what you learned go cold.<br><br>Randy",
     ], None),

    ("09-noshow-replay",
     "You missed it — but here's the recording, " + FN,
     [
        f"Hi {FN},",
        "No worries — life gets busy and you couldn't make it live. I don't want you to miss what we covered, so here's the full replay:",
        ("cta", "Watch the Replay (about 45 min)", CV("replay_link")),
        "It's the same session everyone showed up for: how to get in the game and win government contracts without years of past performance.",
        "Watch it when you get a quiet 45 minutes — it's worth it.<br><br>Randy",
     ], None),

    ("10-noshow-reengage",
     "One thing you should know about winning government contracts",
     [
        f"Hi {FN},",
        "If you only take one thing from the webinar you missed, make it this:",
        "You don't win government contracts by being the biggest. You win by being the best <i>positioned</i> — the right vehicle, the right teaming move, and a proposal that scores well. Small businesses do this every single day.",
        "That's just one piece. The full breakdown is in the replay:",
        ("cta", "Watch the Replay", CV("replay_link")),
        "And if you'd rather just talk through your situation, grab a free 30-min call:",
        ("cta", "Book a Call", CALENDLY),
        "Randy",
     ], None),

    ("11-noshow-nudge",
     "Before these call spots fill, " + FN,
     [
        f"Hi {FN},",
        "I open a limited number of free strategy calls each week and they tend to go fast.",
        "If you've been meaning to get serious about government contracts, this is the no-pressure way to get a clear next step for your business:",
        ("cta", "Book My Free Call", CALENDLY),
        "Two minutes to book. Thirty minutes that could change your pipeline.<br><br>Randy",
     ], None),

    ("12-noshow-next-webinar",
     "New session coming up — want in, " + FN + "?",
     [
        f"Hi {FN},",
        "We run these sessions on a rolling basis, and the next one is coming up.",
        "If the timing didn't work last round, here's your chance to catch a live one and bring your questions:",
        ("cta", "Register for the Next Session", "https://fedgovstartup.com/webinar"),
        "Hope to see you live this time,<br>Randy",
     ], None),

    ("13-noshow-bootcamp",
     "A different way in, " + FN,
     [
        f"Hi {FN},",
        "Not everyone wants to jump on a call — and that's fine.",
        "If you'd rather learn the whole system at your own pace, the Bootcamp is built exactly for that: the same frameworks I teach on the webinars, broken into steps you can work through whenever you have time.",
        ("cta", "Explore the Bootcamp", CV("bootcamp_link")),
        f"And whenever you're ready for a 1-on-1, <a href=\"{CALENDLY}\">my calendar's here</a>.",
        "Rooting for you,<br>Randy",
     ], None),

    ("15-call-prep",
     "You're booked, " + FN + " — here's how to prep",
     [
        f"Hi {FN},",
        "Your strategy call is locked in — looking forward to it.",
        "To make our 30 minutes count, come with:",
        ("list", [
            "What you sell (your core product/service)",
            "Whether you're registered in SAM.gov yet",
            "Any contract or agency you're specifically eyeing",
        ]),
        "That's it — I'll do the rest. Need to reschedule? Use the link in your Calendly confirmation.",
        "Talk soon,<br>Randy Wimmer<br><span style='color:#5b6470'>Government Contracting Academy</span>",
     ], None),
]


def render_block(b):
    if isinstance(b, tuple) and b[0] == "cta":
        _, label, href = b
        return (
            f'<table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0">'
            f'<tr><td style="border-radius:10px;background:{GOLD}">'
            f'<a href="{href}" style="display:inline-block;padding:15px 30px;font-size:16px;'
            f'font-weight:800;color:#1a1407;text-decoration:none;border-radius:10px">{label} →</a>'
            f'</td></tr></table>'
        )
    if isinstance(b, tuple) and b[0] == "list":
        items = "".join(
            f'<tr><td style="padding:7px 0;border-bottom:1px solid #e6e9ee;font-size:16px;color:{INK}">{it}</td></tr>'
            for it in b[1]
        )
        return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:8px 0">{items}</table>'
    return f'<p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:{INK}">{b}</p>'


def render_email(slug, subject, blocks):
    body = "\n".join(render_block(b) for b in blocks)
    return f"""<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>{subject}</title></head>
<body style="margin:0;padding:0;background:{PAPER}">
<!-- Agent 1 email: {slug}. Subject line: {subject} -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{PAPER};padding:24px 12px">
<tr><td align="center">
  <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 8px 30px rgba(10,31,68,.08)">
    <tr><td style="background:linear-gradient(160deg,{NAVY},{NAVY2});padding:26px 32px">
      <span style="color:{GOLD2};font-size:13px;font-weight:700;letter-spacing:.06em;text-transform:uppercase">Government Contracting Academy</span>
    </td></tr>
    <tr><td style="padding:32px">
      {body}
    </td></tr>
    <tr><td style="background:{NAVY};padding:20px 32px;text-align:center">
      <p style="margin:0;color:#9fb0cf;font-size:12px;line-height:1.5">
        Randy Wimmer · Government Contracting Academy · fedgovstartup.com<br>
        You're receiving this because you registered for one of our webinars.
        <a href="{{{{unsubscribe_link}}}}" style="color:#c8a44d">Unsubscribe</a>
      </p>
    </td></tr>
  </table>
</td></tr></table>
</body></html>
"""


def main():
    os.makedirs(OUT_DIR, exist_ok=True)
    written = []
    for slug, subject, blocks, _cta in CONTENT:
        html = render_email(slug, subject, blocks)
        path = os.path.join(OUT_DIR, f"{slug}.html")
        with open(path, "w", encoding="utf-8") as f:
            f.write(html)
        written.append(os.path.basename(path))
    # Subject-line index for quick paste reference.
    idx = "# Email subject lines (paste alongside each HTML body)\n\n"
    for slug, subject, _b, _c in CONTENT:
        idx += f"- **{slug}.html** — {subject}\n"
    with open(os.path.join(OUT_DIR, "SUBJECTS.md"), "w", encoding="utf-8") as f:
        f.write(idx)
    print(f"Wrote {len(written)} HTML emails + SUBJECTS.md to emails/html/")
    for w in sorted(written):
        print("  -", w)


if __name__ == "__main__":
    main()
