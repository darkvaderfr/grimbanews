# GrimbaNews — Per-Partner Analytics Scope

**Status:** plan v0 (no per-source partner analytics; `grimba_vault_events` has aggregate vault stats)
**Owner:** David Chen (Data Scientist) on metric definitions + Hannah Kim (Platform) on aggregation pipeline + Larry Ellison on schema + Liam Smith on partner-facing view
**Walks:** Mythos S1325 (Per-partner analytics) deferred → partial
**Gating dependency:** Partner relationship at content-share level (not API, per `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`) + per-source attribution column on events

## Why this exists

S1325 supports the newsroom-partnership program (per Wave LLL): each partner needs to know "how is my content performing on GrimbaNews?" — read counts, save counts, share counts, push reach.

## Today's surrogate

- **`grimba_vault_events`** — aggregate vault save count exists.
- **Per-source RSS subscribe count** — not tracked.
- **Per-source bias-distribution rail** — exists at source page.

## Schema

```sql
CREATE TABLE partner_attribution_events_daily (
  partner_source_id BIGINT NOT NULL,     -- FK news_sources where source is partner
  date DATE NOT NULL,
  posts_ingested INT DEFAULT 0,
  posts_published INT DEFAULT 0,
  post_views BIGINT DEFAULT 0,
  vault_saves INT DEFAULT 0,
  cluster_member_count INT DEFAULT 0,    -- how many posts ended up in clusters
  middle_ground_appearances INT DEFAULT 0,
  rss_subscriber_estimate INT DEFAULT 0,
  push_reach BIGINT DEFAULT 0,
  computed_at TIMESTAMP NOT NULL,
  PRIMARY KEY (partner_source_id, date),
  INDEX (date),
  INDEX (partner_source_id, date)
);

-- Lifetime rollup
CREATE TABLE partner_attribution_lifetime (
  partner_source_id BIGINT PRIMARY KEY,
  first_ingested_at TIMESTAMP NULL,
  posts_total INT DEFAULT 0,
  views_total BIGINT DEFAULT 0,
  saves_total INT DEFAULT 0,
  middle_ground_count INT DEFAULT 0,
  computed_at TIMESTAMP NOT NULL
);
```

## Metric definitions (David Chen)

- `posts_ingested` = count of posts from `news_source_id = partner_source_id` ingested that day.
- `posts_published` = count where `status='published'` (drops drafts/rejects).
- `post_views` = SUM(`post_views_daily.view_count`) joined per post (gates on per-post view counter).
- `vault_saves` = count of `grimba_vault_events` for posts from this source.
- `cluster_member_count` = count of posts where `story_cluster_id IS NOT NULL`.
- `middle_ground_appearances` = count where parent cluster is MG-tagged.
- `rss_subscriber_estimate` = N/A (proxy: feed access count from nginx logs).
- `push_reach` = count of `push_deliveries` for posts from this source.

## Operator dashboard `/admin/grimba/partners/{source_id}/analytics`

- KPI cards (last 30d).
- Per-day line chart (posts published vs post views).
- Top 10 posts by views.
- Cluster contribution waterfall.
- MG-tag share of total publications.

## Partner-facing self-service `/partner/analytics` (gates on partner-side login UX)

- Same metrics minus operator-only fields.
- Monthly CSV export.
- Quarterly business review packet (auto-generated PDF — gates on PDF lib).

## Privacy posture (Sara Chen)

- No per-reader identifiers in partner-facing data.
- Vault save counts: aggregate only (no "reader X saved this post").
- Per-source view counts: aggregate, never per-IP.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1325)
- Sister docs: `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`, `docs/GRIMBANEWS_SYNDICATION_AGREEMENT_TEMPLATE.md`, `docs/GRIMBANEWS_PARTNERSHIP_PROGRAM_LAUNCH_PLAYBOOK.md`, `docs/GRIMBANEWS_PER_PARTNER_CASE_STUDY_SCOPE.md`, `docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
