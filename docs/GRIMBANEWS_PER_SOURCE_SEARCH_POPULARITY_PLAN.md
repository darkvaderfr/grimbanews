# GrimbaNews — Per-Source Search Popularity Plan

**Status:** plan v0 (no per-source telemetry on search-result click-through)
**Owner:** Benjamin Lee on aggregation + Liam Smith on operator dashboard + David Chen on bias-popularity interpretation
**Walks:** Mythos S1495 (Per-source search popularity) deferred → partial
**Gating dependency:** Search event logging (S1491) shipped.

## Why this exists

S1495 shows which `news_sources` are surfaced (and clicked) most via search vs other discovery paths. Useful for: (a) editorial — which sources earn their inclusion, (b) partnership — surfacing partner-source utilization, (c) bias-distribution sanity check.

## Today's surrogate

- `GrimbaSourceBreakdown` shows distribution at cluster level — not search-specific.

## Aggregation (target)

```sql
SELECT s.id, s.name, s.bias, s.ownership_type,
       COUNT(DISTINCT sec.search_event_id) AS surfaced_in_searches,
       COUNT(sec.id) AS clicks,
       COUNT(sec.id)::float / NULLIF(COUNT(DISTINCT sec.search_event_id), 0) AS ctr
FROM news_sources s
JOIN posts p ON p.source_id = s.id
JOIN search_event_clicks sec ON sec.post_id = p.id
WHERE sec.clicked_at >= NOW() - INTERVAL :days DAY
GROUP BY s.id
ORDER BY surfaced_in_searches DESC
LIMIT 100;
```

## Dashboard panel

Lives under `/admin/grimba/search-dashboard` (S1492 doc) — third tab: "Per-source popularity"

```
  Source             Bias   Surfaced  Clicks  CTR
  Le Monde           CL     1,247     412     33%
  Reuters            C       983      287     29%
  Le Figaro          CR      672      198     29%
  France Info        CL      612      173     28%
  ...
```

## Per-bias roll-up (feeds S1496)

```sql
SELECT s.bias,
       SUM(sec.* count) AS clicks,
       ...
FROM ... GROUP BY s.bias;
```

## Editorial signals

- Source surfaced often but rarely clicked → maybe headline / topic mismatch
- Source clicked often → reader values the voice → consider featuring
- Bias bucket consistently winning clicks → check for unconscious editorial weighting

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1495)
- Sister docs: `docs/GRIMBANEWS_SEARCH_EVENT_LOGGING_SCHEMA.md`, `docs/GRIMBANEWS_TOP_SEARCHES_DASHBOARD_SCOPE.md`
- Existing infra: `app/Support/GrimbaSourceBreakdown.php`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
