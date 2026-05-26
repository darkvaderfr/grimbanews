# GrimbaNews — Author Contribution Log Schema

**Status:** plan v0 (no author_contributions table; per-author totals derived from `post_journalists` join at query time)
**Owner:** Larry Ellison (VP DBA) on table shape + Rajesh Kumar (Backend) on cron rollup + David Chen on metric definitions
**Walks:** Mythos S1415 (Author contribution log) deferred → partial
**Gating dependency:** `journalists` + `post_journalists` tables (S1411)

## Why this exists

S1415 caches per-journalist + per-period activity for fast profile pages + analytics. Joining `post_journalists` × `posts` on demand works at 10 journalists; at 100+ with profile page traffic, the JOIN dies.

## Today's surrogate

- **None** — no journalist primitive exists.

## Schema

```sql
CREATE TABLE author_contributions_daily (
  journalist_id BIGINT NOT NULL,
  date DATE NOT NULL,
  posts_published INT DEFAULT 0,
  posts_breaking INT DEFAULT 0,
  posts_in_clusters INT DEFAULT 0,
  unique_clusters INT DEFAULT 0,
  posts_corrected INT DEFAULT 0,
  total_words BIGINT DEFAULT 0,
  per_category JSON NULL,             -- {"climate":3,"politics":2}
  per_locale JSON NULL,               -- {"fr":4,"en":1}
  per_source JSON NULL,               -- {"42":3,"87":2} — if journalist contributed across multiple source bylines
  computed_at TIMESTAMP NOT NULL,
  PRIMARY KEY (journalist_id, date),
  INDEX (date),
  INDEX (journalist_id, date)
);

-- Lifetime rollup for fast profile-page cards
CREATE TABLE author_contributions_lifetime (
  journalist_id BIGINT PRIMARY KEY,
  posts_total INT DEFAULT 0,
  posts_breaking INT DEFAULT 0,
  unique_clusters INT DEFAULT 0,
  first_published_at TIMESTAMP NULL,
  last_published_at TIMESTAMP NULL,
  total_words BIGINT DEFAULT 0,
  specialty_top3 JSON NULL,           -- derived from per-category aggregation
  locale_primary CHAR(5) NULL,
  computed_at TIMESTAMP NOT NULL
);
```

## Rollup cron

- `grimba:author-contributions-rollup` daily at 03:00.
- For each journalist with activity in last 24h: insert/update `daily` row.
- Then update `lifetime` row.

## Metric definitions (David Chen)

- `posts_published` = COUNT(post_journalists WHERE date(posts.created_at) = date).
- `posts_in_clusters` = COUNT(... WHERE posts.story_cluster_id IS NOT NULL).
- `unique_clusters` = COUNT(DISTINCT posts.story_cluster_id).
- `posts_corrected` = COUNT(... WHERE posts.correction_issued_at IS NOT NULL).
- `total_words` = SUM(LENGTH(posts.content) words estimate).
- `specialty_top3` = top-3 categories by count, recalculated lifetime.

## Privacy

- Public-facing surfaces (profile page) show: posts_total, unique_clusters, first/last published, specialty_top3.
- Operator-only surfaces show: per_source breakdown, per-day cadence (workload analysis).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1415)
- Sister docs: `docs/GRIMBANEWS_AUTHOR_TABLE_SCHEMA.md`, `docs/GRIMBANEWS_AUTHOR_PROFILE_PAGE_SCOPE.md`, `docs/GRIMBANEWS_AUTHOR_ANALYTICS_DASHBOARD_SCOPE.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
