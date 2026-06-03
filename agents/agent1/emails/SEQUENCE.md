# Agent 1 — Email & SMS Sequence (paste-ready)

All 10 emails + 2 SMS for the webinar funnel. Copy each block into the matching GHL workflow step.

## Merge tags used (GHL syntax)

| Tag | Source | Example |
|-----|--------|---------|
| `{{contact.first_name}}` | Contact field | Randy |
| `{{custom_values.webinar_topic}}` | Custom value | Get On Contract Vehicles Without Extensive Past Performances |
| `{{custom_values.webinar_date}}` | Custom value | Tuesday, June 17, 2026 |
| `{{custom_values.webinar_time}}` | Custom value | 7:00 PM EST |
| `{{custom_values.zoom_link}}` | Custom value* | https://zoom.us/j/123456789 |
| `{{custom_values.lead_magnet_link}}` | Custom value* | https://fedgovstartup.com/guide.pdf |
| `{{custom_values.replay_link}}` | Custom value* | https://fedgovstartup.com/replay |
| `{{custom_values.calendly_link}}` | Custom value* | https://calendly.com/randy-wimmer/30-minute-zoom-mtg-with-randy |
| `{{custom_values.bootcamp_link}}` | Custom value* | https://fedgovstartup.com/bootcamp |

> \* The build guide defines only 4 custom values. The 5 starred ones above are additional values you'll
> want to create in GHL (Settings > Custom Values) so links stay reusable across every webinar.
> See RUNBOOK §2. The guide's shorthand `{{custom.x}}` maps to GHL's real syntax `{{custom_values.x}}`.

> **Note on dates in pre-webinar emails:** GHL date-based triggers should anchor to a contact-level
> date field (`webinar_date_dt`), set at registration — not the custom value. See RUNBOOK §5.

---

# BEFORE THE WEBINAR

---

## Email 1 — Confirmation (fires instantly on registration)

**Subject:** You're registered! Here's everything you need, {{contact.first_name}}

```
Hi {{contact.first_name}},

You're confirmed for:

📌 {{custom_values.webinar_topic}}
🗓  {{custom_values.webinar_date}} at {{custom_values.webinar_time}}

Save your spot link — this is how you'll join:
👉 {{custom_values.zoom_link}}

While you wait, grab the free guide I put together for attendees:
📥 Download it here: {{custom_values.lead_magnet_link}}

Do this now so you don't forget:
✅ Add it to your calendar: {{custom_values.zoom_link}}

This session is built for small businesses that want to win more government
contracts without guessing. Come with your toughest question — I read them live.

See you there,
Randy Wimmer
Founder, Government Contracting Academy
Author of "GOOD ENOUGH! to Launch Your Company"
```

---

## SMS 1 — Confirmation (fires ~2 min after registration)

```
Hi {{contact.first_name}}, you're confirmed for "{{custom_values.webinar_topic}}" on {{custom_values.webinar_date}} at {{custom_values.webinar_time}}. Your Zoom link: {{custom_values.zoom_link}} — Randy Wimmer / GovCon Academy
```

> Reply STOP to opt out (GHL appends automatically if compliance is enabled).

---

## Email 2 — Authority (Day +1 after registration)

**Subject:** The #1 mistake small businesses make with government contracts

```
Hi {{contact.first_name}},

Quick story before our session on {{custom_values.webinar_date}}.

Most small businesses chasing government work make the same mistake: they wait
until they have "enough" past performance before they go after real contracts.

So they sit on the sidelines for years.

Here's the truth I've watched play out hundreds of times: you do NOT need a long
track record to win. You need the right vehicle, the right teaming strategy, and
a proposal that scores. That's exactly what we cover on the webinar.

I've spent my career helping companies go from "we've never won a federal
contract" to landing real awards — and I wrote the playbook in my book,
"GOOD ENOUGH! to Launch Your Company."

On {{custom_values.webinar_date}} I'll walk you through the parts that matter most.

See you there,
Randy

P.S. Here's your join link again so it's easy to find: {{custom_values.zoom_link}}
```

---

## Email 3 — Agenda Preview (3 days before)

**Subject:** Here's exactly what we're covering on {{custom_values.webinar_date}}

```
Hi {{contact.first_name}},

We're 3 days out from "{{custom_values.webinar_topic}}." Here's the plan:

1. The single biggest barrier small businesses think they have — and why it's
   mostly a myth.
2. The exact path to get in the game without years of past performance.
3. How to position so evaluators score you higher than bigger competitors.
4. Live Q&A — bring your specific situation.

Quick win before we meet: go pull your SAM.gov registration and confirm your
NAICS codes are current. Half the businesses I talk to are leaving easy
opportunities on the table because their codes are wrong or incomplete.

Make sure you're set to join:
👉 {{custom_values.zoom_link}}

See you on {{custom_values.webinar_date}},
Randy
```

---

## Email 4 — T-24 Hour Reminder

**Subject:** Tomorrow at {{custom_values.webinar_time}} — don't forget, {{contact.first_name}}

```
Hi {{contact.first_name}},

We go live TOMORROW:

📌 {{custom_values.webinar_topic}}
🗓  {{custom_values.webinar_date}} at {{custom_values.webinar_time}}
👉 Join here: {{custom_values.zoom_link}}

Bring your single biggest question about winning government contracts. I'll get
through as many as I can on the live Q&A — and the people who ask usually walk
away with the clearest next step.

Block the time now so nothing bumps it.

See you tomorrow,
Randy
```

---

## Email 5 — T-3 Hour Reminder (day of)

**Subject:** TODAY at {{custom_values.webinar_time}} — we start in 3 hours

```
Hi {{contact.first_name}},

We're on in 3 hours. Short and simple:

👉 JOIN HERE: {{custom_values.zoom_link}}

Topic: {{custom_values.webinar_topic}}
Time: {{custom_values.webinar_time}}

See you soon,
Randy
```

---

## SMS 2 — T-15 Minute Reminder

```
{{contact.first_name}} — we start in 15 minutes! Join now: {{custom_values.zoom_link}} — Randy
```

---

# AFTER THE WEBINAR

---

# PATH A — ATTENDED

---

## Email 6 — Replay + Book-a-Call (1 hour after)

**Subject:** Here's the replay + your next step, {{contact.first_name}}

```
Hi {{contact.first_name}},

Great to have you on today's session. As promised, here's the replay in case you
want to revisit anything:

▶ Watch the replay: {{custom_values.replay_link}}

Now the important part. On the webinar I showed you the *what*. A strategy call is
where we map the *how* for YOUR business specifically — your codes, your targets,
your fastest path to an award.

I've opened a few free 30-minute strategy calls this week:

📅 Grab a time: {{custom_values.calendly_link}}

There's no pitch trap here — just a focused look at your situation and the next
move. Talk soon,

Randy
```

---

## Email 7 — Attended Follow-Up (24 hours after)

**Subject:** Did you get a chance to watch? {{contact.first_name}}

```
Hi {{contact.first_name}},

Following up on yesterday's session on {{custom_values.webinar_topic}}.

The contractors who move fastest are the ones who turn what they learned into a
plan within a few days — while it's fresh. That's the whole point of the free
30-minute strategy call.

I still have a couple of spots open this week:
📅 {{custom_values.calendly_link}}

If you've already booked — perfect, ignore this and I'll see you on the call.

Randy
```

---

## Email 8 — Attended Urgency (48 hours after)

**Subject:** Last chance, {{contact.first_name}} — only a couple of call spots left

```
Hi {{contact.first_name}},

I'm closing out this week's strategy calls and only have a couple of slots left.

If getting on contract vehicles and winning real awards is on your radar this
year, this is the easiest way to get a clear, personalized next step:

📅 Book before they fill: {{custom_values.calendly_link}}

Not ready for a 1-on-1 yet? No problem — the Bootcamp is the lower-commitment way
to get the full system at your own pace:
🎓 {{custom_values.bootcamp_link}}

Either way, don't let what you learned go cold.

Randy
```

---

# PATH B — NO-SHOW

---

## Email 9 — No-Show Replay (1 hour after)

**Subject:** You missed it — but here's the recording, {{contact.first_name}}

```
Hi {{contact.first_name}},

No worries — life gets busy and you couldn't make it live. I don't want you to
miss what we covered, so here's the full replay:

▶ Watch the replay (about 45 min): {{custom_values.replay_link}}

It's the same session everyone showed up for: how to get in the game and win
government contracts without years of past performance.

Watch it when you get a quiet 45 minutes — it's worth it.

Randy
```

---

## Email 10 — No-Show Re-engagement (Day +2)

**Subject:** One thing you should know about winning government contracts

```
Hi {{contact.first_name}},

If you only take one thing from the webinar you missed, make it this:

You don't win government contracts by being the biggest. You win by being the
best *positioned* — the right vehicle, the right teaming move, and a proposal
that scores well. Small businesses do this every single day.

That's just one piece. The full breakdown is in the replay:
▶ {{custom_values.replay_link}}

And if you'd rather just talk through your situation, grab a free 30-min call:
📅 {{custom_values.calendly_link}}

Randy
```

---

## Email 11 — No-Show Last Nudge (Day +5)

**Subject:** Before these call spots fill, {{contact.first_name}}

```
Hi {{contact.first_name}},

I open a limited number of free strategy calls each week and they tend to go fast.

If you've been meaning to get serious about government contracts, this is the
no-pressure way to get a clear next step for your business:

📅 {{custom_values.calendly_link}}

Two minutes to book. Thirty minutes that could change your pipeline.

Randy
```

---

## Email 12 — No-Show Next Webinar Invite (Day +7)

**Subject:** New session coming up — want in, {{contact.first_name}}?

```
Hi {{contact.first_name}},

We run these sessions on a rolling basis, and the next one is coming up.

If the timing didn't work last round, here's your chance to catch a live one and
bring your questions:

👉 Register for the next session: https://fedgovstartup.com/webinar

Hope to see you live this time,
Randy
```

> If you prefer to re-enroll no-shows automatically rather than link out, point this CTA at the
> registration page (above) — re-registration re-tags them `webinar-registered` for the next cycle.

---

## Email 13 — No-Show Bootcamp Offer (Day +10)

**Subject:** A different way in, {{contact.first_name}}

```
Hi {{contact.first_name}},

Not everyone wants to jump on a call — and that's fine.

If you'd rather learn the whole system at your own pace, the Bootcamp is built
exactly for that: the same frameworks I teach on the webinars, broken into steps
you can work through whenever you have time.

🎓 Take a look: {{custom_values.bootcamp_link}}

And whenever you're ready for a 1-on-1, my calendar's here:
📅 {{custom_values.calendly_link}}

Rooting for you,
Randy
```

---

# INTERNAL / CALL BOOKED

---

## Email 14 — Internal notification to Randy (on call booked)

**Subject:** 🔔 New call booked: {{contact.first_name}} {{contact.last_name}}

```
New strategy call booked.

Name:  {{contact.first_name}} {{contact.last_name}}
Email: {{contact.email}}
Phone: {{contact.phone}}
Source tag: webinar funnel

Booking details came in via Calendly. Check your calendar for the time.
```

---

## Email 15 — Call prep to lead (on call booked)

**Subject:** You're booked, {{contact.first_name}} — here's how to prep

```
Hi {{contact.first_name}},

Your strategy call is locked in — looking forward to it.

To make our 30 minutes count, come with:
• What you sell (your core product/service)
• Whether you're registered in SAM.gov yet
• Any contract or agency you're specifically eyeing

That's it. I'll do the rest.

If something comes up and you need to reschedule, just use the link in your
Calendly confirmation email.

Talk soon,
Randy Wimmer
Government Contracting Academy
```
