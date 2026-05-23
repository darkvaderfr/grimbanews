# GrimbaNews — On-Call Roster Template

**Status:** template / pre-engagement (no paging vendor wired)
**Owner:** Jacob Lee (DevOps) + Sara Chen (CISO) review
**Walks:** Mythos S1014 (on-call rotation) deferred → partial
**Gating dependency:** PagerDuty / Opsgenie / Better Stack account not yet provisioned. This document is the **named-roster + rotation contract** that the paging vendor will be configured against on day one once an account ships.

## Why this exists

S1014 was honest-deferred in the Mythos S1001-S1100 ops pack because there is no paging vendor account today. The deferral note flagged the existence of a roster as the missing piece. This template provides that roster — primary + secondary slots, rotation cadence, contact methods — so the moment a vendor is picked the configuration is a straight import, not a "who's on the team" archaeology pass.

The named-owner column already lives in `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md` section 8 + `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`. This doc unifies them into a paging-ready format.

## Rotation cadence

- **Weekly rotation** (Monday 09:00 UTC handoff).
- **Primary** holds pager for the week.
- **Secondary** escalates if primary doesn't ack within 15 min for P0/P1.
- **Tertiary** = CISO + DevOps lead, always reachable for P0 escalations.
- Holiday + PTO swaps coordinated 2 weeks ahead in Iboga ops channel.

## Roster (real Iboga Ventures roster)

| Slot | Name | Role | Coverage | Contact |
|---|---|---|---|---|
| Primary week A | Jacob Lee | DevOps | Even ISO weeks | Iboga ops channel + phone (vendor-stored) |
| Primary week B | Hannah Kim | Platform | Odd ISO weeks | Iboga ops channel + phone (vendor-stored) |
| Secondary week A | Hannah Kim | Platform | Even ISO weeks | Iboga ops channel + phone |
| Secondary week B | Jacob Lee | DevOps | Odd ISO weeks | Iboga ops channel + phone |
| Tertiary (always) | Sara Chen | CISO | All weeks, P0 only | Iboga ops + phone + escalation email |
| Tertiary (always) | Larry Ellison | VP DBA | All weeks, DB-class P0 only | Iboga ops + phone |
| Editorial on-call (content P0) | rotating: Lucy Leai → Steve Jobs | Strategy / CPO | All weeks, content-class P0 | Iboga ops |
| Final escalation | Zenkai (signoff) | Founder-ops | All weeks, last resort | Iboga ops |

(Real names sourced from `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md` — Iboga Ventures Apr 2026 canonical roster. Never fabricate.)

## Severity → who-pages mapping

- **P0 — site down / data loss / breach** — primary + secondary + tertiary CISO simultaneously.
- **P1 — degraded service / ingest stalled / scheduler failing** — primary, secondary if no ack in 15 min.
- **P2 — single-feature broken / non-critical job failure** — primary, business hours.
- **P3 — cosmetic / non-urgent** — issue tracker, no page.

The internal signal `grimba:health --fail-on-risk` (scheduled hourly per `routes/console.php:173-176`) already lands failures in `grimba_automation_runs` with `status='failed'` and surfaces on the cockpit board. The pager-vendor webhook would consume the same `grimba_automation_runs` row class.

## Vendor activation checklist (day-1 when account ships)

1. Provision vendor account (PagerDuty Business tier or Better Stack equivalent).
2. Import this roster as schedule layers (primary A, primary B, secondary A, secondary B, tertiary always-on).
3. Wire webhook from `App\Console\Commands\GrimbaHealth::handle()` → vendor REST endpoint on `fail-on-risk` exit.
4. Add second webhook from `GrimbaAutomationMonitor::finish()` for `status='failed'` rows of P0-class jobs (`backup_verify`, `ops_health`, `rss_poll_main`).
5. Test with a synthetic failure (touch a `.fail` flag in `storage/`).
6. Document escalation runbook in `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`.
7. Update this template with vendor-side schedule URL.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1014 row)
- Iboga roster source-of-truth: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
- Launch checklist owners section: `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md` section 8
- Health probe: `app/Console/Commands/GrimbaHealth.php` (fail-on-risk surface that will feed the vendor webhook)
- Automation monitor: `app/Support/GrimbaAutomationMonitor.php` (the per-job failure ledger the webhook reads from)
- Scheduler: `routes/console.php:173-176` (`ops_health` hourly)
