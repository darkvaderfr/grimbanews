# GrimbaNews — RTO / RPO Definition

**Status:** definition v0 (operator-side governance; targets stated, drill cadence deferred)
**Owner:** Jacob Lee (DevOps) + Larry Ellison (VP DBA) + Sara Chen (CISO) co-sign
**Walks:** Mythos S1941 (RTO / RPO definition) deferred → partial
**Gating dependency:** None — RTO / RPO definition is operator-side governance. Actual drill exercises (S1943, S1944) follow.

## Why this exists

S1941 was honest-deferred as "operator-side governance doc." This doc states the recovery targets for each in-scope GrimbaNews component, justifies the targets against current control coverage, and identifies where drill cadence (S1943-S1946) needs to verify them.

## Definitions

- **RTO (Recovery Time Objective)** — maximum acceptable time from disaster declaration to service restored.
- **RPO (Recovery Point Objective)** — maximum acceptable data loss measured in time (e.g. RPO=1h means we can lose up to 1h of data).

## Targets per component

| Component | RTO | RPO | Justified by | Drill required |
|---|---|---|---|---|
| Reader site (grimbanews.com homepage + dossiers + categories + search) | 1 hour | 1 hour | VPS reboot or rollback to last green deploy is <1h; backup-verify daily ensures <24h data behind, intermediate hourly backups need to reduce to 1h | Live failover (S1944) |
| Health/up endpoints | 30 min | N/A (stateless) | Endpoint is stateless — restart = restore | Liveness probe in status page (S1017) |
| RSS feed endpoints | 1 hour | 1 hour | Same as reader site | Same drill |
| Live SQLite database | 4 hours | 1 hour | Restore from latest verified backup; `grimba:verify-backups` daily at 03:05 confirms backup viability | Tabletop (S1943) quarterly + live restore (S1945) annually |
| Scheduler / ingest pipeline | 6 hours | 24 hours | Ingest catch-up after restore; missing 24h of posts is acceptable (RSS sources re-emit) | Tabletop (S1943) |
| Translation cache | 24 hours | 7 days | Translations regenerate on demand via `grimba:translate-by-rule`; cache loss = re-translate cost, not data loss | Not required (regenerable) |
| NobuAI summaries | 24 hours | 7 days | Same — regenerate via `grimba:nobuai-summaries --stale` | Not required (regenerable) |
| Member accounts + vault | 4 hours | 1 hour | Same as live SQLite; vault items are unique data, must be preserved | Live restore (S1945) annually |
| Subscriber list | 4 hours | 1 hour | Same; subscribers are unique data, must be preserved | Live restore (S1945) annually |

## Currently-supported actuals vs targets

| Component | Currently supports | Target | Gap |
|---|---|---|---|
| Reader site | Untested — likely 1-4h depending on cause | 1h | Needs drill |
| Health/up | Inherent (stateless) | 30 min | None — passes |
| Live SQLite | Daily backup at 03:05; quick_check verifies; no restore test | 4h RTO / 1h RPO | RPO gap — current backup cadence is 24h, not 1h. Need hourly backup or write-ahead-log streaming |
| Scheduler | Inherent via cron restart | 6h / 24h | None — passes |
| Translation cache | Regenerable | 24h / 7 days | None — passes |
| Vault | Daily backup | 1h RPO | Same RPO gap as live DB |
| Subscriber list | Daily backup | 1h RPO | Same |

**Identified gap:** Database RPO is 24h today (last daily backup), but target is 1h. Closes by either:

- Option A: Hourly backup cron (cheap; small disk hit).
- Option B: SQLite WAL streaming to a sibling volume / S3 (more robust).

This gap is the highest-priority item from this RTO/RPO definition exercise. Recommendation: ship Option A this quarter; consider Option B when migrating off SQLite per master plan S951.

## What "disaster" means here

Triggers that activate RTO/RPO:

- VPS host failure (drive corruption, hardware loss, provider outage).
- Live SQLite corruption (PRAGMA quick_check fail at runtime — would be caught by `grimba:verify-backups`).
- Accidental destructive admin action (e.g. `posts` table truncated).
- Confirmed breach requiring isolated restore.
- Region-level provider outage.

Not in scope for these targets:

- Single-feature bug (handled by deploy rollback, not DR).
- CDN outage (cache layer, not data layer; gates on S1911-S1920).
- Vendor outage (translation provider down → degraded mode; not DR).

## Drill cadence (S1943-S1946 gates)

- **Q1:** Tabletop walkthrough — full restore from last verified backup; team walks the playbook without touching prod.
- **Q2:** Restore-validation drill on staging — actually run `grimba:verify-backups --restore-to=staging`; measure time.
- **Q3:** Tabletop — failure-mode brainstorm (what could go wrong in next 6 months).
- **Q4:** Annual live drill — provisional plan to fail over to a freshly-built VPS from backups, measure end-to-end RTO.

(Drill commands + outputs feed into `docs/internal-audit/YYYY-Q{1,2,3,4}/` per `docs/GRIMBANEWS_INTERNAL_AUDIT_CHARTER.md` Q3 cycle.)

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1941 row; gates for S1942 DR runbook, S1943-S1946 drill cadence)
- Existing backup primitive: `app/Support/GrimbaDatabaseBackups.php`
- Existing verify command: `app/Console/Commands/GrimbaVerifyBackups.php` (daily 03:05 per `routes/console.php:33`)
- Existing verify test: `tests/Feature/DatabaseBackupVerificationTest.php`
- Sister docs: `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`, `docs/GRIMBANEWS_INTERNAL_AUDIT_CHARTER.md`, `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md` (A1 availability TSC)
