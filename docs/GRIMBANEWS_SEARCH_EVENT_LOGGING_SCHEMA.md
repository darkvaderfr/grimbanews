# GrimbaNews — Search Event Logging Schema

**Status:** plan v0 (no `search_events` table; no per-query telemetry)
**Owner:** Larry Ellison (VP DBA) on schema + Benjamin Lee (Data Engineer) on pipeline + Sara Chen (CISO) on PII posture
**Walks:** Mythos S1491 (Search event logging schema) deferred → partial
**Gating dependency:** Privacy review (no IP / no identifying cookie keys logged).

## Why this exists

S1491 is the foundation for top-searches dashboard (S1492), zero-result tracking (S1493), per-source/per-bias popularity (S1495/S1496), and CTR (S1498). Today no query telemetry is captured — operator has no visibility into what readers search for.

## Today's surrogate

- Laravel access logs (`storage/logs/laravel.log`) capture URLs but not parsed query terms or result-set metadata.

## Schema (target)

```sql
CREATE TABLE search_events (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  query VARCHAR(255) NOT NULL,            -- normalized lowercase
  query_hash VARCHAR(64) NOT NULL,        -- sha256 for grouping
  locale VARCHAR(8) NOT NULL,
  result_count INT NOT NULL,
  zero_result BOOLEAN GENERATED ALWAYS AS (result_count = 0) STORED,
  has_semantic BOOLEAN DEFAULT FALSE,     -- semantic channel ran
  has_expansion BOOLEAN DEFAULT FALSE,    -- NobuAI expansion ran
  top_source_id BIGINT NULL,              -- FK news_sources of top result
  filter_categories JSON NULL,            -- if any filter applied
  client_country CHAR(2) NULL,            -- coarse geo (no IP stored)
  session_hash VARCHAR(64) NULL,          -- per-session, rotated daily
  search_latency_ms INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (created_at),
  INDEX (query_hash, created_at),
  INDEX (zero_result, created_at)
);

CREATE TABLE search_event_clicks (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  search_event_id BIGINT NOT NULL,
  post_id BIGINT NOT NULL,
  click_rank TINYINT NOT NULL,            -- position in result set
  clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (search_event_id),
  INDEX (post_id, clicked_at)
);
```

## PII posture (Sara Chen sign-off)

- **No IP address stored** (only coarse country derived from edge geo).
- **No user_id stored** — session_hash is anonymous, rotates daily.
- **Query text retained** as research substrate; redact PII-like patterns (email regex, phone regex) before insert.

## Retention

- 90 days raw rows.
- After 90 days: aggregate-only roll-up to `search_events_daily` (per-query-hash, per-locale, per-day counts).
- Raw deletion via nightly `grimba:prune-search-events` Artisan command.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1491)
- Sister docs: `docs/GRIMBANEWS_TOP_SEARCHES_DASHBOARD_SCOPE.md`, `docs/GRIMBANEWS_ZERO_RESULT_SEARCH_TRACKING_PLAN.md`, `docs/GRIMBANEWS_PER_SOURCE_SEARCH_POPULARITY_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
