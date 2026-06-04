# Agent 1 — GHL provisioning (PowerShell, self-contained)
# Creates the 9 custom values + 5 tags in Randy's GHL sub-account. Idempotent.
# Usage: open PowerShell, set the two values below, paste the whole script, press Enter.

[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

# ── FILL THESE IN ───────────────────────────────────────────
$token = "pit-d7409343-2ea8-441c-a89b-2ff9978ea5d5"
$loc   = "qIn16rBUD211TncDFdQd"
# ────────────────────────────────────────────────────────────

$base = "https://services.leadconnectorhq.com"
$headers = @{ Authorization = "Bearer $token"; Version = "2021-07-28"; Accept = "application/json" }

$customValues = @(
  @{ name = "webinar_topic";    value = "Get On Contract Vehicles Without Extensive Past Performances" },
  @{ name = "webinar_date";     value = "Tuesday, June 17, 2026" },
  @{ name = "webinar_time";     value = "7:00 PM EST" },
  @{ name = "zoom_webinar_id";  value = "000 0000 0000" },
  @{ name = "zoom_link";        value = "https://zoom.us/j/000000000" },
  @{ name = "lead_magnet_link"; value = "https://fedgovstartup.com/guide.pdf" },
  @{ name = "replay_link";      value = "https://fedgovstartup.com/replay" },
  @{ name = "calendly_link";    value = "https://calendly.com/randy-wimmer/30-minute-zoom-mtg-with-randy" },
  @{ name = "bootcamp_link";    value = "https://fedgovstartup.com/bootcamp" }
)
$tags = @("webinar-registered","webinar-attended","webinar-noshow","call-booked","unsubscribed")

Write-Host "`n=== Custom Values ===" -ForegroundColor Cyan
try {
  $resp = Invoke-RestMethod -Uri "$base/locations/$loc/customValues" -Headers $headers -Method Get
  $existing = @($resp.customValues | ForEach-Object { $_.name.ToLower() })
} catch { $existing = @(); Write-Host "  (could not list existing: $($_.Exception.Message))" -ForegroundColor Yellow }

foreach ($cv in $customValues) {
  if ($existing -contains $cv.name.ToLower()) { Write-Host "  exists:  $($cv.name)"; continue }
  try {
    Invoke-RestMethod -Uri "$base/locations/$loc/customValues" -Headers $headers -Method Post `
      -Body ($cv | ConvertTo-Json) -ContentType "application/json" | Out-Null
    Write-Host "  created: $($cv.name)" -ForegroundColor Green
  } catch { Write-Host "  FAILED:  $($cv.name) -> $($_.Exception.Message)" -ForegroundColor Red }
}

Write-Host "`n=== Tags ===" -ForegroundColor Cyan
try {
  $existingTags = @((Invoke-RestMethod -Uri "$base/locations/$loc/tags" -Headers $headers -Method Get).tags | ForEach-Object { $_.name.ToLower() })
} catch { $existingTags = @() }

foreach ($t in $tags) {
  if ($existingTags -contains $t.ToLower()) { Write-Host "  exists:  $t"; continue }
  try {
    Invoke-RestMethod -Uri "$base/locations/$loc/tags" -Headers $headers -Method Post `
      -Body (@{ name = $t } | ConvertTo-Json) -ContentType "application/json" | Out-Null
    Write-Host "  created: $t" -ForegroundColor Green
  } catch { Write-Host "  FAILED:  $t -> $($_.Exception.Message)" -ForegroundColor Red }
}

Write-Host "`n=== Pipeline (build manually in GHL > Settings > Pipelines) ===" -ForegroundColor Cyan
"Registered","Reminder Sent","Attended","No-Show","Call Booked","Proposal Sent","Client Won","Client Lost" |
  ForEach-Object { Write-Host "  - $_" }
Write-Host "`nDone." -ForegroundColor Green
