# GrimbaNews S006 Migration Inventory

**Sprint:** S006  
**Outcome:** migration inventory  
**Status:** complete  
**Date:** 2026-04-29  
**Verification:** `php artisan migrate:status`, migration file listing, migration schema scan

S006 inventories GrimbaNews migrations, local migration status, schema responsibilities, and migration risks. This supports G1 Current-State Review, G6 Data Readiness, G7 Security Readiness, and G9 Release Readiness.

## Summary

| Scope | Count |
|---|---:|
| Repo-root migration files in `database/migrations` | 34 |
| Grimba-specific migration files | 26 |
| Laravel/support migration files | 8 |
| Grimba migrations shown as ran locally | 22 |
| Grimba migrations shown as pending locally | 4 |
| Custom tables created by Grimba migrations | 10 |
| Existing tables extended by Grimba migrations | 4 |
| SQLite-specific migrations | 1 |

`php artisan migrate:status` was run for inventory only. No migrations were applied during S006.

## Pending Local Migrations

The local database currently reports these Grimba migrations as pending:

| Migration | Purpose | Release impact |
|---|---|---|
| `2026_04_28_180000_create_grimba_translation_failures_table` | Translation failure queue. | Required for failed translation diagnostics and retry flow. |
| `2026_04_28_181500_create_grimba_automation_runs_table` | Scheduler monitor run history. | Required for cockpit automation status and S004 monitor evidence. |
| `2026_04_28_190500_create_grimba_newsapi_runs_table` | NewsAPI request/run accounting. | Required for NewsAPI budget/run dashboard. |
| `2026_04_28_201800_add_logo_status_to_news_sources` | Source logo status/cache metadata. | Required for source logo QA and missing-logo workflows. |

These should be applied in local/pre-prod before browser QA depends on cockpit automation, translation failure diagnostics, NewsAPI run history, or logo-status state.

## Grimba Migration Ledger

| Migration | Status in local DB | Main schema change |
|---|---|---|
| `2026_04_23_191855_add_bias_columns_to_posts_table` | Ran | Adds post bias/trust/blindspot columns and indexes. |
| `2026_04_23_200000_add_story_cluster_to_posts_table` | Ran | Adds `posts.story_cluster_id`, `posts.source_name`, and cluster index. |
| `2026_04_23_210000_create_news_sources_table` | Ran | Creates `news_sources`. |
| `2026_04_23_220000_add_source_id_to_posts_table` | Ran | Adds `posts.source_id` index. |
| `2026_04_23_230000_create_newsletter_subscriptions_table` | Ran | Creates Grimba newsletter subscription table. |
| `2026_04_23_240000_create_story_clusters_table` | Ran | Creates `story_clusters` and backfills labels for existing cluster IDs. |
| `2026_04_24_000000_add_original_language_to_posts` | Ran | Adds `posts.original_language` and index. |
| `2026_04_24_100000_create_rss_feeds_table` | Ran | Creates RSS feed config/health table. |
| `2026_04_24_100100_create_rss_feed_items_table` | Ran | Creates RSS ingest ledger. |
| `2026_04_24_110000_create_posts_fts_table` | Ran | Creates SQLite FTS5 virtual table and sync triggers. |
| `2026_04_24_120000_add_translation_columns_to_posts` | Ran | Adds legacy translated title/description metadata and composite index. |
| `2026_04_24_130000_rebrand_echo_settings_to_grimba` | Ran | One-way settings rewrite for visible Grimba branding. |
| `2026_04_24_140000_add_translated_content_to_posts` | Ran | Adds `posts.translated_content`. |
| `2026_04_25_120000_add_slug_and_description_to_news_sources` | Ran | Adds source slug/description and unique slug index. |
| `2026_04_25_140000_create_newsapi_items_and_extend_news_sources` | Ran | Adds `news_sources.api_id`; creates `newsapi_items`. |
| `2026_04_25_160000_add_owner_name_to_news_sources` | Ran | Adds `news_sources.owner_name` and seeds known owners. |
| `2026_04_26_000000_add_canonical_url_hash_to_rss_feed_items` | Ran | Adds RSS canonical URL hash index. |
| `2026_04_26_140000_add_full_content_to_posts` | Ran | Adds full article extraction columns. |
| `2026_04_27_120000_add_nobuai_summary_columns_to_posts` | Ran | Adds NobuAI summary columns. |
| `2026_04_27_140000_add_bias_score_to_news_sources` | Ran | Adds numeric source bias score and index. |
| `2026_04_27_141500_add_bias_signal_to_newsletter_subscriptions` | Ran | Adds newsletter reader bias counters and digest variant. |
| `2026_04_27_151500_create_grimba_post_translations_table` | Ran | Creates normalized per-locale translation table. |
| `2026_04_28_180000_create_grimba_translation_failures_table` | Pending | Creates translation failure queue. |
| `2026_04_28_181500_create_grimba_automation_runs_table` | Pending | Creates automation monitor history. |
| `2026_04_28_190500_create_grimba_newsapi_runs_table` | Pending | Creates NewsAPI run accounting table. |
| `2026_04_28_201800_add_logo_status_to_news_sources` | Pending | Adds source logo status/cache/error columns. |

## Schema Areas

| Area | Tables/columns |
|---|---|
| Source metadata | `news_sources`, `posts.source_id`, `posts.source_name`. |
| Bias/trust | `posts.bias_rating`, `posts.is_blindspot`, `posts.credibility_score`, `posts.ownership_type`, `news_sources.bias_rating`, `news_sources.bias_score`, `news_sources.credibility_score`, `news_sources.ownership_type`, `news_sources.owner_name`. |
| Story clustering | `story_clusters`, `posts.story_cluster_id`. |
| RSS ingest | `rss_feeds`, `rss_feed_items`, `rss_feed_items.canonical_url_hash`. |
| NewsAPI ingest | `news_sources.api_id`, `newsapi_items`, `grimba_newsapi_runs`. |
| Search | `posts_fts` virtual table and `posts_ai`, `posts_ad`, `posts_au` triggers. |
| Translation | post legacy translation columns, `grimba_post_translations`, `grimba_translation_failures`. |
| NobuAI | `posts.summary_nobuai`, `posts.summary_generated_at`, `posts.summary_driver`. |
| Full article | `posts.full_content`, `posts.full_fetched_at`, `posts.full_extract_error`. |
| Newsletter | `newsletter_subscriptions`, reader bias counters, `digest_variant`. |
| Automation | `grimba_automation_runs`. |
| Branding/settings | `settings` rows updated by `2026_04_24_130000_rebrand_echo_settings_to_grimba`. |

## Index And Constraint Notes

- `news_sources.name` is unique.
- `news_sources.slug` is unique after the slug migration.
- `rss_feeds` has a unique composite key on `source_id`, `url`.
- `rss_feed_items` has a unique composite key on `feed_id`, `guid`.
- `rss_feed_items.canonical_url_hash` is indexed but not unique.
- `newsapi_items.article_url_hash` is unique.
- `grimba_post_translations` and `grimba_translation_failures` have unique `post_id`, `locale` pairs and cascade on post delete.
- `grimba_automation_runs` indexes `job_key`, `finished_at` and `status`, `finished_at`.
- `grimba_newsapi_runs` indexes scope/status/start timestamps.
- Many relationship-like columns are indexed but not foreign-key constrained: `posts.source_id`, `posts.story_cluster_id`, `rss_feeds.source_id`, `rss_feed_items.post_id`, `newsapi_items.post_id`, `newsapi_items.source_id`.

## Portability And Rollback Risks

- `posts_fts` is SQLite-specific and no-ops on non-SQLite drivers. A MySQL/Postgres production database needs a replacement search implementation.
- `2026_04_24_130000_rebrand_echo_settings_to_grimba` has a no-op `down()` and is intentionally one-way.
- `2026_04_23_240000_create_story_clusters_table` backfills cluster labels from existing `posts.story_cluster_id` values. Rollback would drop cluster metadata.
- Several `down()` methods drop columns or tables that can contain production data. Rollback drills should use a database backup/restore plan, not blind migration rollback.
- Some migrations rely on `Schema::hasColumn()` guards and are safer to rerun, but not all early Grimba migrations are fully idempotent.
- Pending migrations mean local browser/admin QA may not reflect code expectations until migrations are applied.

## Data Readiness Follow-Ups

- Apply the four pending local migrations before S031-S040 admin surface QA and S161-S170 scheduler contract testing.
- Confirm production database engine. If production is SQLite, keep `posts_fts`; if MySQL/Postgres, define the search migration replacement before launch.
- Add migration evidence to deployment: `php artisan migrate --force` output, migration status after deploy, and DB backup path.
- Add referential integrity checks for non-FK relationships: orphan sources, orphan clusters, orphan ledger rows, orphan slugs, and missing translation parent posts.
- Verify `newsletter_subscriptions` privacy fields and export rules before launch.

## Handoff

Sprint: S006  
Outcome: migration inventory complete  
Files: `docs/GRIMBANEWS_S006_MIGRATION_INVENTORY.md`, `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`  
Verification: `php artisan migrate:status`; migration file listing; schema operation scan; settings rebrand migration read  
Risks: four pending local Grimba migrations, SQLite-only FTS, mostly index-only relationships, one-way settings rebrand migration, rollback data-loss risk  
Next: S007 public surface inventory  
Commit: recorded in sprint handoff after push
