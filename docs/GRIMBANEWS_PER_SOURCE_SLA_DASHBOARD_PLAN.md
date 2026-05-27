# GrimbaNews — Per-Source SLA Dashboard Plan

**Status:** plan v0
**Owner:** Jacob Lee (DevOps) + Hannah Kim (Platform) + Lucy Leai (Strategy)
**Walks:** Mythos S1147 (per-source SLA dashboard) deferred → partial
**Gating dependency:** 30+ days of `news_sources.last_polled_at` + `news_sources.last_error` history.

## Why this exists

Some sources poll cleanly; others throw 500s, time out, or change feed format silently. An SLA dashboard surfaces which sources are healthy and which need editorial / ops attention. Helps prioritize source-roster refresh.

## v1 design

`/admin/grimba/source-sla` admin-only page renders per-source over 7-day window:

- **Uptime:** % of attempted polls that succeeded.
- **Freshness:** median lag from publisher publish-time to GrimbaNews ingest.
- **Feed-format stability:** count of parse failures (RSS schema changes etc.).
- **Per-source last error:** human-readable.
- **Per-source last successful poll.**
- **Per-source articles-per-day average.**
- **Per-source bias-classifier confidence trend.**

## Schema (already exists via `grimba_automation_runs`)

Per-poll rows in `grimba_automation_runs` with `job_key='rss_ingest_per_source_<id>'` (or aggregated `rss_ingest`).

Per-source `news_sources.last_polled_at`, `last_error`, `consecutive_failures`.

## Alerting

Per Wave FFFFFFFFFFF pattern: when `consecutive_failures` >= 5 for a source, `slack_webhook` fires.

## Editorial-side action

- Source down > 7 days → flag for retire.
- Feed format change → editor adjusts ingest config.
- Sustained bias-drift on a source → editor reviews per `docs/GRIMBANEWS_BIAS_SHIFT_DETECTION_PLAN.md`.

## Cross-references

Master plan: S1147. Sister: `docs/GRIMBANEWS_PER_REGION_TRUST_DASHBOARD_PLAN.md`, `docs/GRIMBANEWS_BIAS_SHIFT_DETECTION_PLAN.md`, `docs/GRIMBANEWS_PAGING_MATRIX.md`.
Code: `app/Support/GrimbaAutomationMonitor.php`.
