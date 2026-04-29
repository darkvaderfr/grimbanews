# GrimbaNews S005 Model Inventory

**Sprint:** S005  
**Outcome:** model inventory  
**Status:** complete  
**Date:** 2026-04-29  
**Verification:** model file scan, migration scan, query-builder usage scan

S005 inventories the data model surface used by GrimbaNews. The project uses Botble/Echo Eloquent models for CMS entities and uses Grimba-specific tables directly through `DB::table()` in routes, admin functions, commands, and services.

## Summary

| Scope | Result |
|---|---:|
| App-owned Eloquent models | 1 |
| Botble/package/plugin model files present | 27 |
| Custom Grimba model classes | 0 |
| Grimba-owned tables | 10 |
| Existing tables extended by Grimba migrations | 2 |
| SQLite virtual/search tables | 1 |

## Eloquent Model Layer

| Model | Table | Source | Grimba usage |
|---|---|---|---|
| `App\Models\User` | `users` | `app/Models/User.php` | Base Laravel user model. Not the main reader/admin model in Grimba flows. |
| `Botble\Blog\Models\Post` | `posts` | `platform/plugins/blog/src/Models/Post.php` | Primary article/story entity. Grimba adds source, cluster, bias, translation, full-content, and NobuAI fields. |
| `Botble\Blog\Models\Category` | `categories` | `platform/plugins/blog/src/Models/Category.php` | Topic/category pivots and guardrail categories. |
| `Botble\Blog\Models\Tag` | `tags` | `platform/plugins/blog/src/Models/Tag.php` | Slug cleanup and Botble blog behavior. |
| `Botble\Page\Models\Page` | `pages` | `platform/packages/page/src/Models/Page.php` | Page slug cleanup and CMS pages. |
| `Botble\Slug\Models\Slug` | `slugs` | `platform/packages/slug/src/Models/Slug.php` | Article/category/page permalink rows; cleanup and dedupe touch it. |
| `Botble\Member\Models\Member` | `members` | `platform/plugins/member/src/Models/Member.php` | Reader auth/account plugin routes. |
| `Botble\Newsletter\Models\Newsletter` | plugin newsletter table | `platform/plugins/newsletter/src/Models/Newsletter.php` | Botble newsletter plugin, separate from Grimba `newsletter_subscriptions`. |
| `Botble\Ads\Models\Ads` | ads plugin table | `platform/plugins/ads/src/Models/Ads.php` | Public/admin ad plugin routes. |
| Other Botble/plugin models | package/plugin tables | `platform/**/Models/*.php` | Menus, widgets, contact, gallery, comments, audit log, request log, social login, language. |

There are no custom Eloquent models for `news_sources`, `story_clusters`, `rss_feeds`, `rss_feed_items`, `newsapi_items`, `newsletter_subscriptions`, `grimba_post_translations`, `grimba_translation_failures`, `grimba_automation_runs`, or `grimba_newsapi_runs`.

## Grimba Extensions On `posts`

`posts` remains the central article table through `Botble\Blog\Models\Post`. Grimba migrations add:

| Field group | Columns | Purpose |
|---|---|---|
| Bias and trust | `bias_rating`, `is_blindspot`, `credibility_score`, `ownership_type` | Reader-facing bias/trust display and blindspot detection. |
| Story/source join hints | `story_cluster_id`, `source_name`, `source_id` | Cluster grouping and outlet metadata connection. |
| Language | `original_language`, `translated_name`, `translated_description`, `translated_content`, `translated_to`, `translated_at`, `translation_driver` | Legacy/current translated presentation and provider tracking. |
| Full article | `full_content`, `full_fetched_at`, `full_extract_error` | Subscriber/member full article body extraction. |
| NobuAI | `summary_nobuai`, `summary_generated_at`, `summary_driver` | Cluster-level NobuAI insight copied onto posts in the cluster. |

Important note: most Grimba-added `posts` fields are not in the Botble `Post::$fillable` array. Commands/routes often write these through query builder or direct property updates.

## Grimba-Owned Tables

| Table | Created by | Purpose | Access style |
|---|---|---|---|
| `news_sources` | `2026_04_23_210000_create_news_sources_table.php` | Outlet/source metadata: name, website, bias, owner, credibility, country, language, API id, logo state. | Query builder. |
| `story_clusters` | `2026_04_23_240000_create_story_clusters_table.php` | News-event clusters/dossiers. | Query builder plus `posts.story_cluster_id`. |
| `rss_feeds` | `2026_04_24_100000_create_rss_feeds_table.php` | RSS feed config and feed health counters. | Query builder. |
| `rss_feed_items` | `2026_04_24_100100_create_rss_feed_items_table.php` | RSS ingest ledger keyed by `feed_id` and `guid`; links to produced post. | Query builder. |
| `newsapi_items` | `2026_04_25_140000_create_newsapi_items_and_extend_news_sources.php` | NewsAPI ingest ledger keyed by hashed article URL. | Query builder. |
| `newsletter_subscriptions` | `2026_04_23_230000_create_newsletter_subscriptions_table.php` | Grimba newsletter subscribers, locale, attribution, bias counts, unsubscribe state. | Query builder. |
| `grimba_post_translations` | `2026_04_27_151500_create_grimba_post_translations_table.php` | Per-post, per-locale translation records. | Query builder. |
| `grimba_translation_failures` | `2026_04_28_180000_create_grimba_translation_failures_table.php` | Translation failure queue and diagnostics. | Query builder. |
| `grimba_automation_runs` | `2026_04_28_181500_create_grimba_automation_runs_table.php` | Scheduler monitor run history. | Query builder via `GrimbaAutomationMonitor`. |
| `grimba_newsapi_runs` | `2026_04_28_190500_create_grimba_newsapi_runs_table.php` | NewsAPI request/run evidence and budget accounting. | Query builder. |

## Search And Support Tables

| Table | Type | Purpose |
|---|---|---|
| `posts_fts` | SQLite FTS5 virtual table | Search index for `/search`, keyed to `posts.id`; synchronized through SQLite triggers. |
| `post_categories` | Botble pivot | Post-to-category relationship; Grimba classifier and guardrail categories write here. |
| `post_tags` | Botble pivot | Post-to-tag relationship; dedupe deletes dropped post pivots. |
| `slugs` | Botble slug table | Slug cleanup, dedupe cleanup, category slug creation. |
| `settings` | Botble setting store | Provider keys, automation toggles, cookie settings, NewsAPI budgets, Grimba flags. |
| `cache`, `cache_locks` | Laravel support | File/database-capable scheduler locks and app cache. |
| `jobs`, `failed_jobs` | Laravel support | Present, but current `.env` uses `QUEUE_CONNECTION=sync`. |
| `sessions` | Laravel support | Present, but current `.env` uses `SESSION_DRIVER=file`. |

## Relationship Map

| Relationship | Implementation |
|---|---|
| Source to posts | `posts.source_id` and `posts.source_name`; no custom `NewsSource` model. |
| Source to RSS feeds | `rss_feeds.source_id`. |
| RSS item to post | `rss_feed_items.post_id`. |
| NewsAPI item to post | `newsapi_items.post_id`. |
| NewsAPI source to source | `news_sources.api_id` and `newsapi_items.api_source_id`. |
| Story cluster to posts | `posts.story_cluster_id`. |
| Post translations | `grimba_post_translations.post_id` has a foreign key with cascade delete. |
| Translation failures | `grimba_translation_failures.post_id` has a foreign key with cascade delete. |
| Post categories | `post_categories.post_id`, `post_categories.category_id` through Botble pivot. |
| Slugs | `slugs.reference_id`, `slugs.reference_type` polymorphic Botble pattern. |

Most Grimba relationships are index-based but not database-enforced foreign keys. The two per-post translation tables do use foreign keys.

## High-Touch Model Consumers

| Consumer | Tables/models touched |
|---|---|
| `GrimbaRssPoller` | `rss_feeds`, `rss_feed_items`, `news_sources`, `posts`, `story_clusters`, `post_categories`. |
| `GrimbaNewsApiFetcher` | `grimba_newsapi_runs`, `newsapi_items`, `news_sources`, `posts`, `post_categories`. |
| `GrimbaTranslatePending` | `posts`, `grimba_post_translations`, `grimba_translation_failures`. |
| `GrimbaGenerateNobuAiSummaries` | `posts`. |
| `GrimbaDedupePosts` | `rss_feed_items`, `newsapi_items`, `post_categories`, `post_tags`, `slugs`, `posts`. |
| `GrimbaAutomationMonitor` | `grimba_automation_runs`. |
| Admin source routes | `news_sources`, `posts`. |
| Admin cluster routes | `story_clusters`, `posts`, `news_sources`. |
| Public routes | `posts`, `news_sources`, `story_clusters`, `newsletter_subscriptions`, `post_categories`, `categories`, `posts_fts`. |

## Risks And Follow-Ups

- Grimba has no dedicated Eloquent models for core custom tables. This keeps implementation simple but spreads table contracts across routes, services, commands, and views. S006 migration inventory should verify indexes and schema consistency carefully.
- Several relationships are not enforced by database foreign keys. Dedupe, delete, and source removal flows need regression coverage to prevent orphaned rows.
- `posts_fts` is SQLite-specific. Production database choice must be confirmed before launch; MySQL/Postgres search would need a different implementation.
- `newsletter_subscriptions` stores `ip_address` and `user_agent`; privacy/legal work should confirm retention and export rules.
- Botble `Post::$fillable` does not list Grimba-added fields, so future code using mass assignment may silently skip those columns unless it uses query builder or model property assignment.
- `settings` holds provider keys and operational toggles. Provider secrets should remain admin-only and redacted from logs, exports, and public pages.

## Handoff

Sprint: S005  
Outcome: model inventory complete  
Files: `docs/GRIMBANEWS_S005_MODEL_INVENTORY.md`, `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`  
Verification: model file scan; migration scan; query-builder table usage scan; Botble `Post` model read; Grimba migration reads for sources, clusters, RSS, NewsAPI, translation, automation, search, and post extensions  
Risks: no custom Eloquent models for Grimba tables, limited foreign-key enforcement, SQLite-only FTS, privacy-sensitive newsletter fields, mass-assignment mismatch on Grimba post columns  
Next: S006 migration inventory  
Commit: recorded in sprint handoff after push
