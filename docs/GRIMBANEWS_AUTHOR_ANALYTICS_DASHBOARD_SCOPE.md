# GrimbaNews — Author Analytics Dashboard Scope

**Status:** plan v0 (no per-author view counter or dashboard)
**Owner:** David Chen (Data Scientist) defines metrics + Hannah Kim (Platform) on aggregation queries + Larry Ellison on read-replica strategy at scale + Liam Smith (PM) on journalist-facing vs operator-facing split
**Walks:** Mythos S1418 (Author analytics dashboard) deferred → partial
**Gating dependency:** `author_contributions_daily/lifetime` (S1415) + per-post view counter (deferred — needs server-side tracking not currently shipped)

## Why this exists

S1418 gives journalists insight into their own work performance + gives editors data for editorial decisions. Without it, journalists fly blind on what resonates; editors have only gut-feel for who to assign what.

## Today's surrogate

- **Server-side Plausible-style page views** — bare aggregate level (per route).
- **`author_contributions_*` rollups** (per S1415) cover publication cadence but not reader engagement.

## Per-author dashboard `/admin/grimba/journalists/{id}/analytics`

### Top KPIs (last 30 days)

- Posts published.
- Total views.
- Avg views per post.
- Top 3 posts by views.
- Avg time-on-page (when shipped).
- Vault save count (per `vault_events` if tracked per-post).

### Per-post breakdown

- Title + date + views + saves + cluster context.
- Sortable.

### Per-category share

- Donut: % of posts per category.
- Donut: % of views per category.

### Per-locale breakdown

- If journalist writes in multiple locales: per-locale split.

### Cluster impact

- "How many posts contributed to multi-source clusters" — signals story origination.
- "How many MG-tagged clusters journalist participated in" — alignment with editorial priority.

### Comparison band (operator-only)

- Vs cohort: journalist's metrics vs all-journalists median + p75.

## Journalist-facing view `/account/my-analytics` (self-service)

Same as above minus the cohort comparison band — journalists see own numbers, not other-journalist numbers.

## Per-post view counter primitive

This is the prerequisite that needs to ship before dashboard data is real:

```sql
CREATE TABLE post_views_daily (
  post_id BIGINT NOT NULL,
  date DATE NOT NULL,
  view_count INT DEFAULT 0,
  unique_viewer_estimate INT DEFAULT 0,   -- HyperLogLog or distinct-cookie count
  PRIMARY KEY (post_id, date),
  INDEX (date)
);
```

Tracking: middleware on `/dossier/{id}` + `/blog/{slug}` increments daily counter. Server-side, no cookie required for raw count; unique viewer estimate uses anonymized cookie hash.

## Cron

- `grimba:author-analytics-rollup` daily at 03:30 — after S1415 rollup runs at 03:00.
- Joins `post_views_daily` × `post_journalists` × `journalists`.
- Writes per-author summary to `author_engagement_daily` (new table).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1418)
- Sister docs: `docs/GRIMBANEWS_AUTHOR_TABLE_SCHEMA.md`, `docs/GRIMBANEWS_AUTHOR_CONTRIBUTION_LOG_SCHEMA.md`, `docs/GRIMBANEWS_AUTHOR_PROFILE_PAGE_SCOPE.md`, `docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
