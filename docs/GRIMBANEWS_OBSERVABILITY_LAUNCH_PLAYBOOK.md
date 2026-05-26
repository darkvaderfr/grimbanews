# GrimbaNews — Observability Launch Playbook

**Status:** playbook v0 (per-job ledger ships; external alerting + APM deferred)
**Owner:** Jacob Lee (DevOps) on alerting + Sara Chen (CISO) on retention + Hannah Kim (Platform)
**Walks:** Mythos S1749 (alerting v2) + S1750 (observability launch) deferred → partial
**Gating dependency:** Paging vendor account (PagerDuty / Better Stack / Opsgenie per `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md`) + Sentry account (per `docs/GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md`). Playbook itself is operator-side.

## Why this exists

S1749 + S1750 are the closing rows of the observability band. The shipped surface is strong (per-job duration, exit code, missed-run alerts, cockpit dashboard) but external alerting is deferred per same gates as `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md`. This document sequences the launch so the moment paging-vendor and Sentry land the wiring is straight.

## Today's shipped surfaces

- `grimba_automation_runs` table — per-job duration + exit code + error message. Complete per S1741, S1742.
- `/admin/grimba/cockpit` — per-job last-run dashboard. Complete per S1743.
- `grimba:health --fail-on-risk` — missed-run alert flagger. Complete per S1744.
- `grimba:release-smoke` — per-route latency budgets at release time (per `docs/GRIMBANEWS_ADMIN_PROD_READINESS_SMOKE.md`). Partial per S1745.
- Request-ID middleware — per Wave S0911 security pack. Partial per S1747.
- `GrimbaPruneReleaseEvidence` — 30-day rolling retention per S999. Partial per S1748.
- Slack health webhook — per Wave YYYYYYYYYY + ZZZZZZZZZZ. Self-monitor per Wave EEEEEEEEEEE-FFFFFFFFFFF.

## Launch phases

### Phase 0 — Account + tooling

1. **Paging vendor selected** (PagerDuty Business OR Better Stack OR Opsgenie). Ray + Jacob decide on cost.
2. **Sentry account** provisioned per `docs/GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md` checklist.
3. **Vendor register update** per `docs/GRIMBANEWS_VENDOR_REGISTER.md` — both vendor #15 (Sentry) and #16 (paging) move from "pending" to "active".

### Phase 1 — Sentry wiring (low-risk)

1. **Install** `sentry/sentry-laravel` package.
2. **Configure** PII-scrubbing rules per Sentry integration plan.
3. **Backfill DSN** to `.env` + commit `.env.example` placeholder.
4. **Initial soak** — capture 7 days of exception traffic in staging-like volume.
5. **Per-environment routing** — dev/staging/prod project separation.
6. **Per-release tagging** — Sentry release = git SHA.
7. **Smoke** — synthetic exception + verify it reaches Sentry.

### Phase 2 — APM wiring

1. **Enable Sentry performance monitoring** with 10% sample rate (cost-friendly).
2. **Per-route latency** captured continuously (resolves S1745 partial → complete).
3. **Per-route 4xx/5xx** captured structured (resolves S1746 partial → complete).
4. **Per-request trace ID** propagated end-to-end including outbound HTTP (resolves S1747 partial → complete).
5. **Slow-query log** — auto-flag DB queries > 1s.

### Phase 3 — Paging wiring

1. **Import roster** from `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md` as paging-vendor schedule layers.
2. **Wire webhook** from `App\Console\Commands\GrimbaHealth::handle()` → vendor endpoint on `fail-on-risk` exit.
3. **Wire second webhook** from `GrimbaAutomationMonitor::finish()` for `status='failed'` on P0-class jobs (`backup_verify`, `ops_health`, `rss_poll_main`).
4. **Wire third webhook** from Sentry → paging vendor on `level=fatal` events.
5. **Synthetic page test** — touch a `.fail` flag, verify pager fires + acks correctly.
6. **Escalation rules** per `docs/GRIMBANEWS_ESCALATION_TIERS.md`.

### Phase 4 — Dashboard consolidation

1. **Sentry dashboard** = root-cause view per release.
2. **Cockpit board** = operator at-a-glance.
3. **Warehouse dashboards** (per `docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md`) = business analytics.
4. **Status page** (per `docs/GRIMBANEWS_STATUS_PAGE_PLAN.md`) = reader-facing.

Per-surface ownership documented in this doc + each linked plan.

## Log retention policy (S1748 ship)

Per source-class:

| Source | Retention | Storage |
|---|---|---|
| `grimba_automation_runs` | 90 days rolling | SQLite + monthly CSV archive |
| Laravel `storage/logs/laravel.log` | 30 days rolling | log rotation per Laravel default |
| Release-evidence files | 30 days rolling (per S999 complete) | `storage/app/grimba-release-evidence/` |
| Backup files | per `docs/GRIMBANEWS_RTO_RPO_DEFINITION.md` | per backup-target retention |
| Sentry events | per Sentry vendor (default 90 days on free tier; longer on paid) | vendor |
| DR drill logs | per drill date | `docs/GRIMBANEWS_DR_DRILL_*.md` |
| Webhook delivery logs (paging vendor) | per vendor | vendor |

**GDPR carve-out:** any log containing reader IP / member email / member ID is purged within 30 days OR replaced with sha256 hash at write time (per existing `vault_events` pattern).

## Alert routing

Per `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md` severity → who-pages mapping:

| Severity | Trigger | Routes to |
|---|---|---|
| P0 | `grimba:health` exit-on-risk + backup-verify fail | Primary + Secondary + Tertiary CISO simultaneously |
| P1 | ingest stalled > N minutes; scheduler failing | Primary, Secondary if no ack in 15 min |
| P2 | single-feature broken; non-critical job failure | Primary, business hours |
| P3 | cosmetic | Issue tracker, no page |

Webhook payload format normalized to vendor-agnostic JSON; per-vendor adapter at `app/Services/Paging/{Vendor}Adapter.php`.

## Cost projection (Ray review)

| Item | Cost / month |
|---|---|
| Sentry self-hosted (if chosen) | ~€8 (extra VPS) |
| Sentry SaaS (Team plan, 50k events) | $26 |
| PagerDuty (Business, 6 users) | $228 |
| Better Stack (Better Uptime + Logs Team) | $58 |
| Opsgenie (Standard, 6 users) | $54 |

**Recommended stack:** Sentry SaaS Team + Better Stack OR Opsgenie Standard ≈ ~$80/month total. Ray decides between Better Stack vs Opsgenie based on UX preference.

## Vendor register update

Once Phase 0 lands, `docs/GRIMBANEWS_VENDOR_REGISTER.md` row updates:
- Vendor #15 (Sentry) — status → active, DPA → signed (Sentry standard DPA at https://sentry.io/legal/dpa/).
- Vendor #16 (Paging) — populate per chosen vendor + sign DPA.

## Engineering effort estimate

- Sentry install + configure + PII scrub: 2 sprints.
- Paging vendor adapter + webhooks: 3 sprints.
- Per-vendor escalation rules: 1 sprint.
- Synthetic page test + on-call dry-run: 1 sprint.
- Log-retention policy enforcement (additional cron tasks): 1 sprint.
- Dashboard documentation pass: 1 sprint.
- **Full ship: ~9 sprints once vendor accounts provisioned.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1749, S1750; sister S1741-S1748)
- Sister docs: `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md`, `docs/GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md`, `docs/GRIMBANEWS_STATUS_PAGE_PLAN.md`, `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`, `docs/GRIMBANEWS_ESCALATION_TIERS.md`, `docs/GRIMBANEWS_VENDOR_REGISTER.md`, `docs/GRIMBANEWS_RTO_RPO_DEFINITION.md`, `docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md`
- Health probe: `app/Console/Commands/GrimbaHealth.php`
- Automation monitor: `app/Support/GrimbaAutomationMonitor.php`
- Cockpit board: `resources/views/grimba-admin/cockpit.blade.php`
- Iboga hosting policy: `~/.claude/projects/-Users-vb-kaizen/memory/feedback_hosting_policy.md`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
