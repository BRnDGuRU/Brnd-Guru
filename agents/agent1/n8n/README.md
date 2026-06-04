# Agent 1 — n8n (deploy + workflows)

This folder holds everything for the automation layer: the 3 importable workflows and a
Docker stack to run n8n behind HTTPS on Randy's VPS.

```
n8n/
  docker-compose.yml              n8n + Caddy (auto-HTTPS)
  Caddyfile                       reverse proxy (set your subdomain)
  .env.example                    copy to .env, fill in
  wf1-update-webinar-details.json  T1  — admin form → GHL custom values
  wf2-zoom-attendance-split.json   T7+T8 — Zoom attendees → tag attended/no-show
  wf3-calendly-webhook.json        T11 — Calendly booking → tag call-booked
```

## Deploy

1. **DNS:** point an A record (e.g. `n8n.fedgovstartup.com`) at the VPS IP. Update the hostname
   in `Caddyfile` and `.env`.
2. **Configure:** `cp .env.example .env` and fill in (generate the key with `openssl rand -hex 24`).
3. **Run:** `docker compose up -d` — Caddy fetches a TLS cert automatically.
4. **Open** `https://n8n.<domain>`, sign in with the basic-auth creds, create the n8n owner account.

## Wire up the workflows

1. **Credentials** (n8n → Credentials → New):
   - **HTTP Header Auth** named `GHL API Key (Bearer)` → header `Authorization` = `Bearer <GHL token>`
   - **HTTP Basic Auth** named `Zoom Client (Basic id:secret)` → user = Zoom Client ID, password = Zoom Client Secret
2. **Import** each `wf*.json` (Workflows → Import from File).
3. Confirm the HTTP nodes show the credential and that `$env.*` values resolve (they come from `.env`).
4. **Activate** all three.
5. Copy the production webhook URLs into:
   - GHL admin form action → `…/webhook/update-webinar` (WF1)
   - Zoom event subscription → `…/webhook/zoom-webinar-ended` (WF2)
   - Calendly webhook → `…/webhook/calendly` (WF3)

## Notes
- Zoom and Calendly require a publicly reachable HTTPS endpoint — that's why Caddy is here.
- WF2 waits 75 min after `webinar.ended` for Zoom's attendee report to settle, then tags contacts.
- See `../RUNBOOK.md` §7–§9 for the Zoom OAuth app, Calendly webhook, and scope details.
