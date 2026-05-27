# GrimbaNews — Per-Bias Search Popularity Plan

**Status:** plan v0
**Owner:** David Chen (Data) + Liam Smith (PM)
**Walks:** Mythos S1614 (per-bias search popularity) deferred → partial
**Gating dependency:** search-event ledger + bias-side filter exposed in search UI + aggregation job.

## Why this exists

Search analytics today are query-string-only. Layering bias-side filter usage (which bias buckets do readers filter to most?) reveals reader intent that informs editorial coverage decisions.

## v1 metrics

| Metric | Source |
|---|---|
| Per-bias-filter applied | `search_events` where `filters.bias != null` |
| Per-bias-filter CTR | clicks in filtered result-set |
| Per-bias zero-result rate | filtered searches returning 0 results |
| Bias-filter usage trend | week-over-week |

## v1 schema

```sql
ALTER TABLE search_events ADD COLUMN filters_json JSON NULL;
ALTER TABLE search_events ADD COLUMN result_count INT NULL;
```

## v1 dashboard

- `/admin/grimba/search/bias` — weekly heatmap of bias-filter usage.
- Quarterly summary feeds editorial planning.

## Anti-patterns

- No per-reader bias-search log surfaced.
- No advertiser exposure of bias-search aggregates.

## Cross-references

Master plan: S1614. Sister: S1615 (per-date-range popularity), S1616 (search result CTR), S1499 (search A/B harness).
