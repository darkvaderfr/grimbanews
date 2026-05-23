# GrimbaNews — Incident Response Runbook v1

**Status:** runbook v1 (operational, no paging vendor)
**Owner:** Sara Chen (CISO) + Jacob Lee (DevOps)
**Walks:** Mythos S1805 (SOC 2 incident-response evidence) deferred → partial
**Gating dependency:** None — runbook is operational today. Paging-vendor wiring (S1014/S1016) will extend the auto-page step.

## Why this exists

S1805 was honest-deferred with the note "no IR runbook ledger; surrogate is /health + /up + grimba:health --fail-on-risk." Sister product NobuReach has shipped an IR runbook; GrimbaNews equivalent was the missing piece. This document is the runbook v1.

## Scope

Covers: site outage, data loss, ingest stall, scheduler failure, backup failure, suspected breach, confirmed breach.

Out of scope (handled by separate docs): cosmetic regressions (`issue tracker`), copy errors (`editorial workflow`), planned maintenance (`docs/GRIMBANEWS_STATUS_PAGE_PLAN.md` maintenance windows).

## Phases

### Phase 1 — Detect (0-5 min)

- **Signal sources:** `grimba_automation_runs` row `status='failed'` (cockpit at `/admin/grimba/cockpit`), `/health` JSON `status:err`, scheduled `grimba:health --fail-on-risk` exit code ≠ 0 (hourly via `routes/console.php:173-176`), reader report via `/contact`.
- **Severity classification:** Per `docs/GRIMBANEWS_ESCALATION_TIERS.md`.
- **First responder:** On-call per `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md`.

### Phase 2 — Triage (5-15 min)

- Open war-room (Iboga ops voice channel for P0).
- Page Tier 2 if not already ack.
- Page Tier 3 discipline owner (Sara Chen / Larry Ellison / Lucy Leai+Steve Jobs) if domain match.
- Run `php artisan grimba:health --fail-on-risk` locally to capture current signal.
- Pull last 30 min of `storage/logs/laravel.log`.
- Check cockpit board at `/admin/grimba/cockpit` for failed/stale jobs.
- For P0, post status-page initial within 5 min using template 1 (`docs/GRIMBANEWS_INCIDENT_COMMS_TEMPLATES.md`).

### Phase 3 — Mitigate (15 min - 4 hours)

Decision tree:

- **Site down (`/up` non-200)** → check VPS Nginx + PHP-FPM status; restart if needed; rollback last deploy if recent (per `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md` rollback section).
- **Database error (`/health` `db:err`)** → page Larry Ellison; run `grimba:verify-backups --min=1`; consider restore from latest verified backup (per `app/Console/Commands/GrimbaVerifyBackups.php`).
- **Ingest stalled (`rss_poll_main` failed >2 cycles)** → run `php artisan grimba:rss-poll` manually with `--verbose`; check `grimba_live_news_provider_runs` for per-provider failures; check provider-vault credentials (newsdata.io, NewsAPI keys).
- **Scheduler entirely silent (no `grimba_automation_runs` rows in last hour)** → check cron service on VPS; check Laravel scheduler boot; run `php artisan schedule:list` to verify registration.
- **NobuAI provider chain exhausted** → check `GrimbaNobuAi::CHAIN` failover order; flip `grimba_nobuai_driver` setting to a known-healthy provider; run `php artisan grimba:nobuai-health` for diagnosis.
- **Suspected breach** → Phase 3.5 (Sara Chen lead).

### Phase 3.5 — Breach response (Sara Chen lead)

- Preserve evidence: copy `storage/logs/` + last DB dump to immutable archive.
- Rotate all secrets per `docs/GRIMBANEWS_VENDOR_REGISTER.md` and `.env` (DB creds, API keys, session keys).
- Identify scope (which member rows / which posts / which routes hit).
- Engage retained counsel (per `docs/GRIMBANEWS_GDPR_ROPA.md` Article 33/34 process).
- If PII exposure confirmed: prepare breach notification within 72 hours (template 8 in `docs/GRIMBANEWS_INCIDENT_COMMS_TEMPLATES.md`).
- Loop Vader + Zenkai immediately.

### Phase 4 — Resolve (1-24 hours)

- Confirm fix via `grimba:health --fail-on-risk` (exit 0) + `grimba:release-smoke` (all budgets green).
- Post status-page resolved using template 3.
- Email stakeholders using template 5 (resolution variant).
- Close war-room.

### Phase 5 — Post-mortem (within 7 days)

- Blameless post-mortem doc under `docs/incidents/YYYY-MM-DD-{slug}.md` (directory does not yet exist; create on first incident).
- Sections: timeline, root cause (5 whys), contributing factors, mitigations, action items.
- Cross-link to relevant ledger rows in `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`.
- Action items go into the issue tracker.
- For breach-class: append to risk register (`docs/GRIMBANEWS_S010_UNRESOLVED_RISK_REGISTER.md`).

## Existing tooling that this runbook leans on

- **Health probe:** `app/Console/Commands/GrimbaHealth.php` (`--fail-on-risk` + freshness/coverage SLO knobs)
- **Automation monitor:** `app/Support/GrimbaAutomationMonitor.php` (per-job status ledger, surfaces on cockpit)
- **Backup verifier:** `app/Console/Commands/GrimbaVerifyBackups.php` (daily 03:05 per `routes/console.php:33`)
- **Release smoke:** `app/Console/Commands/GrimbaReleaseSmoke.php` (post-deploy gate with HTTP budgets)
- **NobuAI health:** `app/Console/Commands/GrimbaNobuAiHealth.php` (provider chain diagnosis)
- **Provider credits:** `app/Support/GrimbaProviderCredits.php` (per-provider daily counters, surfaces over-quota issues)
- **Cockpit:** `/admin/grimba/cockpit` (job ledger board + one-click runbook actions per `platform/themes/echo/functions/grimba-admin-cockpit.php:302-398`)

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1805 row)
- Existing operator runbooks (granular): `docs/GRIMBANEWS_PROD_DEDUPE_APPLY_2026_05_11.md`, `docs/GRIMBANEWS_PROD_DISK_HEADROOM_2026_05_11.md`, `docs/GRIMBANEWS_NEWSDATAIO_OPERATOR_HANDOFF.md`, `docs/GRIMBANEWS_LANGUAGE_TAGGING_OPERATOR_HANDOFF.md`, `docs/GRIMBANEWS_ADMIN_PROD_READINESS_SMOKE.md`
- Sister docs: `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md`, `docs/GRIMBANEWS_ESCALATION_TIERS.md`, `docs/GRIMBANEWS_INCIDENT_COMMS_TEMPLATES.md`, `docs/GRIMBANEWS_STATUS_PAGE_PLAN.md`, `docs/GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md`
