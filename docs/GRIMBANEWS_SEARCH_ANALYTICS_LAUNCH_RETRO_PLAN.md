# GrimbaNews — Search Analytics Launch Retrospective Plan

**Status:** plan v0 (gates on S1491-S1499 shipping; nothing live)
**Owner:** Liam Smith (PM) on retro structure + Benjamin Lee on metrics + David Chen on quality interpretation
**Walks:** Mythos S1500 (Search-analytics launch retrospective) deferred → partial
**Gating dependency:** Search event logging (S1491), top-searches dashboard (S1492), zero-result tracking (S1493), per-source / bias / date / CTR / A/B (S1495-S1499) all shipped + ≥30 days of data.

## Why this exists

S1500 closes the analytics band. Pre-stage the retro template now so it's a fill-in exercise once data exists.

## Retro template

### Section 1 — Telemetry coverage
- % of search requests that produced a `search_events` row (should be 100%)
- Click-tracking attachment rate (clicks per search × CTR sanity check)
- Privacy compliance audit (no PII / no IP confirmed)

### Section 2 — Editorial signals captured
- Top-100 query digest delivered weekly
- Zero-result digest delivered daily
- Coverage gaps identified → drafts created (count this)

### Section 3 — Quality metrics
- P@5 estimated CTR (clicks in positions 1-5 / searches with ≥5 results)
- Top-1 CTR (relevance signal)
- Zero-result rate (overall + per-locale)

### Section 4 — Performance impact
- Logging overhead per search (target <2ms)
- Storage growth rate (rows/day, GB/month)
- Roll-up job duration

### Section 5 — Operator behavior
- How often dashboard accessed
- How often zero-result digest acted on
- Top editorial decisions driven by this telemetry

### Section 6 — Decisions
- Retention adjustment
- Roll-up schema changes
- Alert threshold tuning

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1500)
- Sister docs: `docs/GRIMBANEWS_SEARCH_EVENT_LOGGING_SCHEMA.md`, `docs/GRIMBANEWS_TOP_SEARCHES_DASHBOARD_SCOPE.md`, `docs/GRIMBANEWS_ZERO_RESULT_SEARCH_TRACKING_PLAN.md`, `docs/GRIMBANEWS_PER_SOURCE_SEARCH_POPULARITY_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
