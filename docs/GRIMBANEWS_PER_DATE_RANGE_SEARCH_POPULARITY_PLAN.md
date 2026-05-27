# GrimbaNews — Per-Date-Range Search Popularity Plan

**Status:** plan v0
**Owner:** David Chen (Data) + Liam Smith (PM)
**Walks:** Mythos S1615 (per-date-range popularity) deferred → partial
**Gating dependency:** search-event ledger with date-range filter usage.

## Why this exists

Knowing how readers slice time (last 24h vs last 7d vs custom range) informs newsletter cadence, archive UI investment, and breaking-news vs evergreen feature balance.

## v1 metrics

| Metric | Source |
|---|---|
| Per-preset usage | last 24h / 7d / 30d / 1y |
| Custom-range usage | bucket by range width |
| Range-shift behavior | how often reader expands a too-narrow range |
| Zero-result range rate | per-range bucket |
| Range vs bias filter cross-tab | per-range × per-bias |

## v1 schema

(Reuses `search_events.filters_json` from S1614.)

## v1 dashboard

- `/admin/grimba/search/date-range` — last-30-days heatmap.
- Insight: which time windows consistently zero-result → editorial gap signal.

## Cross-references

Master plan: S1615. Sister: S1614 (per-bias), S1616 (CTR), S1499 (A/B).
