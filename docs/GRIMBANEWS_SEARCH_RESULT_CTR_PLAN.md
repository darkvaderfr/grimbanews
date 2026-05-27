# GrimbaNews — Search Result CTR Plan

**Status:** plan v0
**Owner:** David Chen (Data) + Nina Patel (Lead FE) + Liam Smith (PM)
**Walks:** Mythos S1616 (search result CTR) deferred → partial
**Gating dependency:** click-through tracking on `/recherche` result tiles + per-position attribution.

## Why this exists

Without per-position CTR, we cannot tell whether the ranker is good (top result wins) or bad (readers scroll past). CTR per position is the single most informative search metric.

## v1 metrics

| Metric | Source |
|---|---|
| Per-position CTR | rank 1..10 |
| Per-query CTR | sliced by query phrase |
| Per-result-type CTR | article / cluster / source |
| No-result-clicked rate | searches with no click |
| Time-to-first-click | from result render to click |

## v1 design

- Each result tile has `data-search-event-token` (HMAC-signed token containing `search_id + position + ref_kind + ref_id`).
- Click handler fires `/search/click?token=...` (server-side log only, no client-state).
- Tokens are write-once (idempotent in 60s window).

## v1 schema

```sql
CREATE TABLE search_clicks (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  search_id BIGINT NOT NULL,
  position TINYINT NOT NULL,
  ref_kind ENUM('post', 'cluster', 'source') NOT NULL,
  ref_id BIGINT NOT NULL,
  clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_search (search_id),
  INDEX idx_position (position, clicked_at)
);
```

## Privacy

- No reader identifier in `search_clicks`.
- 60-day raw retention; aggregate beyond.

## Cross-references

Master plan: S1616. Sister: S1614/S1615 (bias + date-range popularity), S1499 (A/B), S1336/S1337 (query expansion / spell correct).
