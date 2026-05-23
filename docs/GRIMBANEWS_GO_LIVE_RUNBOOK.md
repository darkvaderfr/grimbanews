# GrimbaNews — Go-Live Runbook (Saturday-Morning Cutover)

**Status:** ready-to-execute
**Owner:** Vader (operator) · Sara Chen (CISO co-sign) · Jacob Lee (DevOps)
**Audience:** the person clicking the buttons during cutover

This runbook walks the production cutover for `grimbanews.com`. Estimated wall-clock: **2 hours** if all probes pass first try; up to 4 hours with rollback / retry.

Pre-launch ledger state at cutover: 2237/2237 sprints evidenced (981 complete + 386 partial + 889 deferred). DR drill passed 2026-05-23. Test suite 61 pass / 0 fail / 881 assertions.

---

## T-1 day (the night before)

### 1. Snapshot the VPS prod DB
```bash
ssh deploy@209.74.88.135
cd /var/www/grimbanews
mkdir -p database/backups
sqlite3 database/grimbanews.sqlite ".backup database/backups/grimbanews.pre-cutover-$(date +%Y%m%d).sqlite"
php artisan grimba:verify-backups --all
# Expected: `Backup store: ≥1 valid / 0 invalid · SQLite quick_check ok`
exit
```

### 2. Confirm latest deploy SHA matches local main
```bash
# Local
cd /Users/vb/GrimbaNews && git log -1 --oneline
# Remote
ssh deploy@209.74.88.135 'cd /var/www/grimbanews && git log -1 --oneline'
# Both should match. If not: deploy first.
```

### 3. Set environment variables (one-time)
SSH to the VPS and confirm `.env` has all of:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://grimbanews.com`
- **`APP_KEY=base64:...`** ← **CRITICAL** (without this, every encrypted cookie + session is invalid). Generate via `php artisan key:generate` IF the line is missing or blank; existing apps must preserve the original `APP_KEY` to keep current cookie/session signatures valid.
- **`APP_LOCALE=fr`** ← Wave BBBBBBBBBBB pin (Zen HIGH catch). Without this, mail digests + console-rendered strings fall back to whatever `config('app.locale')` returns; the code default is now `fr` so this is belt-and-suspenders but cheap to set.
- `APP_FALLBACK_LOCALE=fr`
- `DB_DATABASE=/var/www/grimbanews/database/grimbanews.sqlite`
- `MAIL_FROM_ADDRESS=newsletter@grimbanews.com`
- `MAIL_FROM_NAME=GrimbaNews`
- Optional: `GRIMBA_HEALTH_SLACK_WEBHOOK=https://hooks.slack.com/...` (per Wave YYYYYYYYYY) for hourly health alerts
- Optional: `SENTRY_LARAVEL_DSN=https://...` (see `docs/GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md`)
- `NEWSAPI_KEY=...`, `NEWSDATAIO_API_KEY=...` (already populated per session 5 work)
- `OPENAI_API_KEY=...` (NobuAI provider)

### 4. Confirm cron is running
```bash
ssh deploy@209.74.88.135 'crontab -l | grep schedule:run'
# Expected: `* * * * * cd /var/www/grimbanews && php artisan schedule:run >> /var/log/grimba-cron.log 2>&1`
# If missing: `crontab -e` and add the line.
```

---

## T-0 cutover morning

### 5. DNS A-record flip (THE BLOCKER)
At your DNS provider (Namecheap / Cloudflare / wherever `grimbanews.com` is registered):

- **A record** `@` → `209.74.88.135` · TTL `300` (5 min for fast rollback)
- **A record** `www` → `209.74.88.135` · TTL `300`
- Optional **AAAA record** if VPS has IPv6

Wait 5-10 min for propagation. Verify:
```bash
dig grimbanews.com +short
# Expected: 209.74.88.135
dig www.grimbanews.com +short
# Expected: 209.74.88.135
```

### 6. SSL cert via Let's Encrypt + HSTS header

```bash
ssh deploy@209.74.88.135
sudo certbot --nginx -d grimbanews.com -d www.grimbanews.com \
  --non-interactive --agree-tos --email security@grimbanews.com \
  --redirect  # adds HTTP→HTTPS 301 (does NOT add HSTS — added separately below)
```

certbot will edit the nginx config in place. Cert auto-renews via the certbot timer. Validate the redirect first:
```bash
curl -sI http://grimbanews.com | grep -E "HTTP|Location"
# Expected: HTTP/1.1 301, Location: https://grimbanews.com/
```

**HSTS is not auto-installed by certbot --redirect.** Add it explicitly to the SSL server block in nginx:
```nginx
# /etc/nginx/sites-available/grimbanews.com (or wherever certbot placed it)
server {
    listen 443 ssl http2;
    server_name grimbanews.com www.grimbanews.com;
    # ... existing certbot ssl_certificate / ssl_certificate_key lines ...

    # Wave BBBBBBBBBBB (Vader 2026-05-23) — explicit HSTS. Without
    # this line, the application-level Strict-Transport-Security
    # set by GrimbaSecurityHeaders middleware only ships on requests
    # that hit Laravel; certbot static redirects miss it.
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    # ... rest of config ...
}
```

Reload + validate:
```bash
sudo nginx -t
sudo systemctl reload nginx
curl -sI https://grimbanews.com | grep -iE "HTTP|strict-transport-security"
# Expected: HTTP/2 200
#           strict-transport-security: max-age=31536000; includeSubDomains
```

### 7. Smoke 6 surfaces
```bash
for p in '/' '/?lang=en' '/breaking?lang=en' '/methodologie?lang=en' '/sources' '/health'; do
  printf "%-35s " "$p"
  curl -s -o /dev/null -w "%{http_code}\n" -m 8 "https://grimbanews.com${p}"
done
# Expected: all 200
```

### 8. Confirm /health DB freshness
```bash
curl -s https://grimbanews.com/health | jq .
# Expected: {"status":"ok","service":"grimbanews","time":"...","db":"up","last_post_at":"<recent>"}
```

If `last_post_at` is more than 24h old: ingest cron isn't firing. Check `tail /var/log/grimba-cron.log` and re-run `php artisan grimba:health --fail-on-risk` manually.

### 9. Confirm ingest is firing
```bash
ssh deploy@209.74.88.135 'cd /var/www/grimbanews && php artisan grimba:health' | tail -40
# Look for: "8. Ingest last 24h" showing RSS + NewsAPI + Live counts ≠ 0
# Look for: "✓ operating floors are clear" at the bottom
```

### 10. Run category backfill + NobuAI summaries once
```bash
ssh deploy@209.74.88.135
cd /var/www/grimbanews
php artisan grimba:backfill-category   # rate-limited; pulls ~50-100 articles
php artisan grimba:nobuai-summaries --limit=20  # ~20 cluster summaries
exit
```

---

## T+30 min (post-cutover validation)

### 11. Visual smoke in actual browser
Open in a private/incognito window (no cached cookies):
- `https://grimbanews.com/` — Steve's cinematic glass design + EN/FR locale picker + working ad slots
- `https://grimbanews.com/breaking?lang=en` — EN locale renders (Wave DDDDDDDDD locale-enforce middleware)
- `https://grimbanews.com/?lang=en` — Cookie banner says "Cookies / Accept / Reject non-essentials" not "Cookies / Accepter / Refuser"
- `https://grimbanews.com/methodologie?lang=en` — Section H2s in English (1. Our commitment, etc.)
- `https://grimbanews.com/login?lang=en` — Placeholder `you@example.com`, button "Sign in"

### 12. /admin login
- `https://grimbanews.com/admin` — Botble admin login. Confirm DKIM / SMTP / NobuAI providers / RSS feeds tabs all load.

### 13. Cookie consent + GDPR
- Banner fires on first visit. Click Accept → cookie set, banner hides. Click Reject → cookie set with different value, banner hides.
- `https://grimbanews.com/confidentialite` (or wherever the privacy page lives) loads.

---

## Rollback (if any of T+30 fails)

### Plan A — DNS rollback (5 min)
At DNS provider: flip A records back to old IP (or remove). 5 min TTL means propagation is fast.

### Plan B — DB rollback (15 min)
```bash
ssh deploy@209.74.88.135
cd /var/www/grimbanews

# Wave BBBBBBBBBBB (Zen LOW) — stop cron first. Without this,
# schedule:run continues firing every minute and a poller running
# mid-rollback can write to the freshly-restored DB before nginx
# is back up. crontab -r removes the cron line; re-add after.
sudo systemctl stop cron       # OR `crontab -r` if no systemd cron unit
sudo systemctl stop nginx      # stop traffic

php artisan down --message="Rollback in progress" --retry=120  # serves a 503 if cron-not-stopped path executes
cp database/backups/grimbanews.pre-cutover-<date>.sqlite database/grimbanews.sqlite
php artisan cache:clear
php artisan up

sudo systemctl start nginx
sudo systemctl start cron       # OR `crontab` from your saved /tmp/crontab.bak
```

### Plan C — code rollback (15 min)
```bash
ssh deploy@209.74.88.135
cd /var/www/grimbanews
git log --oneline -10  # find pre-cutover SHA
git checkout <sha>
composer install --no-dev --optimize-autoloader
php artisan config:cache && php artisan route:cache && php artisan view:cache
sudo systemctl reload nginx
```

---

## T+1 day (post-cutover ops)

### 14. Schedule + automation health
```bash
ssh deploy@209.74.88.135 'cd /var/www/grimbanews && php artisan schedule:list' | head -20
# Expected: backup_create at 02:55, backup_verify at 03:05, ops_health hourly, ingest crons, etc.
```

### 15. Backup created last night?
```bash
ls -lah /var/www/grimbanews/database/backups/ | tail -5
# Expected: ≥1 grimbanews.YYYYMMDD-HHMMSS.sqlite from 02:55 UTC
```

### 16. First incident-drill paper exercise
Per `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`:
- Sara Chen + Jacob walk a hypothetical scenario (e.g., "live DB dropped")
- Time the response: detection → triage → comms (skip live Slack post; use the template) → root-cause → postmortem
- Goal: confirm the runbook + escalation tiers + comms templates feel actionable

### 17. Confirm Slack health alerts
If `GRIMBA_HEALTH_SLACK_WEBHOOK` set in `.env`, force a fake risk to test the wire:
```bash
ssh deploy@209.74.88.135 'cd /var/www/grimbanews && php artisan grimba:health --min-published-24h=99999'
# Expected: ⚠ warnings printed AND `📡 slack-webhook POST ok` AND a message in the configured Slack channel
```

---

## Communications

### Pre-cutover (T-1 day)
**Internal**: Slack #grimbanews-eng — "Cutover tomorrow morning 09:00 UTC. Rollback window 30 min. Vader leading. Sara on CISO standby."

### Cutover (T-0)
**Internal**: live thread in #grimbanews-eng with timestamps for each step.

### Post-cutover (T+1h)
**Public** (optional, if there was downtime): tweet from `@grimbanews` or post on the eventual status page (per `docs/GRIMBANEWS_STATUS_PAGE_PLAN.md` — currently deferred to vendor account).

**Editorial**: Lucy Leai sends a launch note to seeded source-roster contacts.

---

## What's NOT in this runbook (defer to other docs)

- Stripe wiring → `docs/GRIMBANEWS_B2B_ADVERTISER_SELF_SERVE_PLAN.md` (needs Stripe key)
- Sentry wiring → `docs/GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md` (needs Sentry account)
- PagerDuty / on-call paging → `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md` + `docs/GRIMBANEWS_ESCALATION_TIERS.md` (needs paging vendor)
- SOC 2 audit firm engagement → `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md` (operator-led)
- Mobile native apps → `docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md` (post-launch, 1-2 quarters)

---

## Risk matrix for the cutover itself

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| DNS propagation slow | Medium | Low | 5 min TTL set; wait extra 10 min |
| Certbot cert issuance fails | Low | High | Manual cert via Let's Encrypt CLI; fallback Cloudflare flexible SSL |
| Cron not running on VPS | Medium | High | T-1 day step 4 verifies; manual `php artisan schedule:run` as workaround |
| Ingest dry on first run | Low | Medium | T+30 min step 10 forces it; T+1 day step 15 confirms |
| Cookie banner shows FR on EN | Low | Low | Wave EEEEEEEEEE shipped the ?: fallback; lock test guards |
| /admin login fails | Low | High | Vader has admin creds; reset via tinker if needed |

---

## Signoff

By executing this runbook, you (the operator) confirm:
- [ ] DR drill PASS evidence read (`docs/GRIMBANEWS_DR_DRILL_2026_05_23.md`)
- [ ] Backups verified (≥1 valid artifact in `database/backups/`)
- [ ] Cron is running on the VPS
- [ ] DNS A-records flipped
- [ ] HTTPS cert installed
- [ ] All 6 smoke surfaces return 200
- [ ] /health reports `db:up` with recent `last_post_at`
- [ ] Cookie consent banner renders in EN on `?lang=en`
- [ ] /admin login works

Signed: ________________________ Date: ____________
