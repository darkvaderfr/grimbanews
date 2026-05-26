# GrimbaNews — Top Searches Dashboard Scope

**Status:** plan v0 (no telemetry → no dashboard)
**Owner:** Alex Morgan (UI/UX) on layout + Benjamin Lee on aggregation + Liam Smith on operator UX
**Walks:** Mythos S1492 (Top-searches dashboard) deferred → partial
**Gating dependency:** Search event logging (S1491) shipped + ≥7 days of accumulated rows.

## Why this exists

S1492 gives the operator visibility into what readers actually want. Today the only signal is anecdotal (operator opens incognito and tests their own searches). A top-100 ranked list per locale per day is editorial gold.

## Today's surrogate

- None. Operator has no search-side telemetry.

## Dashboard layout (admin)

**Route:** `/admin/grimba/search-dashboard`

```
┌─────────────────────────────────────────────────────────────┐
│  Top searches — last 7 days                                 │
├─────────────────────────────────────────────────────────────┤
│  Locale: [fr ▾]   Date range: [7d 30d 90d]                  │
├─────────────────────────────────────────────────────────────┤
│  Rank  Query                  Searches    Zero  CTR         │
│  1     ukraine                  1,247      2%   34%         │
│  2     élections               1,019      0%   28%         │
│  3     climat                    893      1%   41%         │
│  4     iran                      702     12%   19%   ⚠     │
│  ...                                                        │
└─────────────────────────────────────────────────────────────┘
```

## Editorial value signals

- **High searches + high zero-result rate** → coverage gap (priority editorial)
- **Rising trend (vs last 7 days)** → breaking topic
- **High CTR + high searches** → strong existing coverage; consider feature box
- **Low CTR + high searches** → headline mismatch; consider rewriting

## Query implementation

```sql
SELECT query, COUNT(*) AS searches,
       AVG(zero_result::int)*100 AS zero_pct,
       (SELECT COUNT(*) FROM search_event_clicks WHERE search_event_id IN (...))
         / NULLIF(COUNT(*), 0) * 100 AS ctr
FROM search_events
WHERE locale = :locale
  AND created_at >= NOW() - INTERVAL :days DAY
GROUP BY query_hash
ORDER BY searches DESC
LIMIT 100;
```

## Performance

- Pre-aggregate to `search_events_daily` (per S1491 retention) — dashboard queries hit roll-up, not raw.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1492)
- Sister docs: `docs/GRIMBANEWS_SEARCH_EVENT_LOGGING_SCHEMA.md`, `docs/GRIMBANEWS_ZERO_RESULT_SEARCH_TRACKING_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
