# GrimbaNews — Native Crash Dashboard Plan

**Status:** plan v0 (no native; web Sentry plan is parallel)
**Owner:** Hannah Kim (Platform) builds dashboard + Jacob Lee provisions Sentry org + Sara Chen on PII guard
**Walks:** Mythos S1394 (Native crash dashboard) deferred → partial
**Gating dependency:** Native shell + Sentry SDK integrated (per `GRIMBANEWS_MOBILE_CRASH_REPORTING_SCOPE.md`)

## Why this exists

S1394 is the operational lens over crash data. Sentry's built-in dashboard is solid; this doc defines the GrimbaNews-specific views that bubble up signal vs noise.

## Today's surrogate

- **Web Sentry plan** — `docs/GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md` (not yet integrated).
- **GrimbaAutomationMonitor** at `/admin/grimba/cockpit` covers server-side error board.

## Dashboard views

### Top of dashboard — health KPIs

| KPI | Source | Threshold |
|---|---|---|
| Crash-free sessions (24h) | Sentry release metric | >99.5% green; 99.0-99.5% amber; <99.0% red |
| Crash-free users (24h) | Sentry release metric | >99% green |
| New issue count (24h) | Sentry issues | >5 = amber |
| ANR rate (Android) | Sentry ANR | >0.5% = amber |
| Slow frame rate (iOS+Android) | Sentry performance | >5% sessions slow = amber |

### Per-release view

- Per-version crash-free %, top crash signatures, new issues vs regressed.
- Side-by-side previous release for regression detection.

### Per-feature view

- Crashes tagged by route (`/dossier/*`, `/pour-vous`, `/account`).
- Top crashed feature → triage priority.

### Per-device view

- Crashes by device + OS — flags long-tail hardware issues.

## Sentry alert rules (Hannah Kim)

| Rule | Trigger | Channel |
|---|---|---|
| Spike alert | issue count +50% over 1h baseline | Slack `#grimba-mobile-oncall` |
| New release regression | crash rate of release X > release X-1 | Slack |
| New crash signature in release | first occurrence | Slack |
| ANR on Android > 1% | sustained 15min | PagerDuty when wired (S1019) |
| Symbol upload missing | per-release CI gate | CI failure |

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1394)
- Sister docs: `docs/GRIMBANEWS_MOBILE_CRASH_REPORTING_SCOPE.md`, `docs/GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md`, `docs/GRIMBANEWS_OBSERVABILITY_LAUNCH_PLAYBOOK.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
