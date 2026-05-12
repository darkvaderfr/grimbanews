# GrimbaNews S003 Command Inventory

**Sprint:** S003  
**Outcome:** command inventory  
**Status:** complete  
**Date:** 2026-04-29  
**Verification:** command source reads under `app/Console/Commands`, `php artisan schedule:list`, command help probes for representative commands

S003 inventories custom Artisan commands and scheduled command entry points. This supports G1 Current-State Review, G2 Autonomous Publishing, G3 NobuAI readiness, and G9 Release Readiness.

## Summary

| Scope | Count |
|---|---:|
| Custom Grimba command classes | 14 |
| Scheduled Grimba command entries | 11 |
| Commands called from admin routes | 6 |
| Commands with network/provider calls | 7 |
| Commands with data writes | 12 |

Command registration is handled by Laravel command discovery in this project layout. There is no `app/Console/Kernel.php`; schedules live in `routes/console.php`.

## Command Registry

| Command | File | Default mode | Purpose |
|---|---|---|---|
| `grimba:health` | `app/Console/Commands/GrimbaHealth.php` | read/report | One-page ingest/editorial health summary. |
| `grimba:verify-backups` | `app/Console/Commands/GrimbaVerifyBackups.php` | read/temp restore smoke | Opens SQLite backup artifacts and runs `PRAGMA quick_check`. |
| `grimba:nobuai-health` | `app/Console/Commands/GrimbaNobuAiHealth.php` | read/report unless `--live` | NobuAI wrapper and provider configuration health check. |
| `grimba:poll-feeds` | `app/Console/Commands/GrimbaPollFeeds.php` | writes drafts | Poll RSS/Atom feeds, dedupe items, create draft posts, retro-cluster, flag unhealthy/stale feeds. |
| `grimba:fetch-newsapi` | `app/Console/Commands/GrimbaFetchNewsApi.php` | writes drafts | Fetch NewsAPI top-headlines/everything articles and ingest drafts. |
| `grimba:translate-pending` | `app/Console/Commands/GrimbaTranslatePending.php` | writes translations | Translate pending posts through configured provider chain. |
| `grimba:nobuai-summaries` | `app/Console/Commands/GrimbaGenerateNobuAiSummaries.php` | writes summaries | Generate cluster-level NobuAI story summaries. |
| `grimba:publish-trusted` | `app/Console/Commands/GrimbaPublishTrusted.php` | publishes drafts | Auto-publish trusted classified-source drafts. |
| `grimba:publish-guardrail-categories` | `app/Console/Commands/GrimbaPublishGuardrailCategories.php` | publishes drafts | Publish low-credibility/unclassified drafts into explicit review categories. |
| `grimba:fetch-full-articles` | `app/Console/Commands/GrimbaFetchFullArticles.php` | writes extracted content | Fetch and extract full article bodies for member/subscriber reading. |
| `grimba:enrich-drafts` | `app/Console/Commands/GrimbaEnrichDrafts.php` | writes images | Backfill hero images for RSS-backed posts. |
| `grimba:dedupe-posts` | `app/Console/Commands/GrimbaDedupePosts.php` | dry-run unless `--apply` | Merge/delete duplicate posts missed by RSS/NewsAPI dedupe. |
| `grimba:recluster` | `app/Console/Commands/GrimbaRecluster.php` | writes clusters unless `--dry-run` | Attach unclustered draft/published posts to likely story clusters. |
| `grimba:classify-categories` | `app/Console/Commands/GrimbaClassifyCategories.php` | writes category pivots | Classify posts into news categories using keyword/source heuristics. |
| `grimba:cleanup-slugs` | `app/Console/Commands/GrimbaCleanupSlugs.php` | deletes orphan slugs unless `--dry-run` | Remove slug rows whose model reference no longer exists. |

## Options Inventory

| Command | Options |
|---|---|
| `grimba:health` | `--fail-on-risk`, `--min-free-mb=`, `--min-published-24h=`, `--min-ingested-published-24h=`, `--min-full-content-coverage=`, `--full-content-retry-after-hours=`, `--backup-dir=` |
| `grimba:verify-backups` | `--backup-dir=`, `--min=`, `--all` |
| `grimba:nobuai-health` | `--live`, `--prompt=` |
| `grimba:poll-feeds` | `--feed=` |
| `grimba:fetch-newsapi` | none |
| `grimba:translate-pending` | `--limit=`, `--to=`, `--force`, `--failed-only`, `--dry-run` |
| `grimba:nobuai-summaries` | `--limit=`, `--cluster=`, `--force`, `--stale`, `--dry-run` |
| `grimba:publish-trusted` | `--threshold=`, `--age-hours=`, `--limit=`, `--dry-run` |
| `grimba:publish-guardrail-categories` | `--threshold=`, `--limit=`, `--dry-run` |
| `grimba:fetch-full-articles` | `--limit=`, `--force`, `--post=` |
| `grimba:enrich-drafts` | `--limit=`, `--feed=`, `--dry-run`, `--force` |
| `grimba:dedupe-posts` | `--apply`, `--limit=` |
| `grimba:recluster` | `--dry-run`, `--threshold=`, `--lookback=` |
| `grimba:classify-categories` | `--force`, `--category=`, `--limit=` |
| `grimba:cleanup-slugs` | `--dry-run` |

## Scheduled Grimba Entries

These are registered in `routes/console.php`.

| Schedule | Command | Monitor wrapper | Notes |
|---|---|---|---|
| Daily `03:05` | `grimba:verify-backups --min=1` | yes | Restore smoke before destructive cleanup; uses `onOneServer` and `withoutOverlapping(20)`. |
| Daily `03:15` | `grimba:cleanup-slugs` | no | Deletes orphan slugs; uses `onOneServer` and `withoutOverlapping`. |
| Every 30 minutes | `grimba:poll-feeds` | yes | RSS ingest; `runInBackground`, `onOneServer`, `withoutOverlapping(20)`. |
| `15,45 * * * *` | `grimba:translate-pending --to=fr --limit=50` | yes | FR translation cadence. |
| `20,50 * * * *` | `grimba:translate-pending --to=en --limit=50` | yes | EN translation cadence. |
| `5,35 * * * *` | `grimba:publish-trusted` | yes | Trusted-source auto-publish. |
| `8,38 * * * *` | `grimba:publish-guardrail-categories` | yes | Guardrail category publishing. |
| `12,42 * * * *` | `grimba:fetch-full-articles --limit=80` | yes | Full-content extraction. |
| `18,48 * * * *` | `grimba:nobuai-summaries --limit=80` | yes | New cluster insight generation. |
| `25,55 * * * *` | `grimba:nobuai-summaries --stale --limit=25` | yes | Stale insight refresh. |
| `15 6,10,14,18,22 * * *` | `grimba:fetch-newsapi` | yes | Five-times-daily NewsAPI sweep, conditional on `grimba_newsapi_active`. |
| Monday `04:10` | `grimba:enrich-drafts` | no | Weekly image backfill; `runInBackground`, `onOneServer`, `withoutOverlapping(60)`. |

`php artisan schedule:list` also shows core Botble/CMS schedules; this inventory only tracks Grimba-owned entries.

## Admin-Triggered Commands

| Admin route | Command invoked | Guardrails |
|---|---|---|
| `grimba.cockpit.nobuai-summaries` | `grimba:nobuai-summaries` | limit capped to 5; optional stale-only. |
| `grimba.cockpit.runbook` | `grimba:health` | bounded action list. |
| `grimba.cockpit.runbook` | `grimba:poll-feeds --feed=<active feed>` | chooses one active feed. |
| `grimba.cockpit.runbook` | `grimba:nobuai-health` | no live flag in runbook. |
| `grimba.cockpit.runbook` | `grimba:translate-pending --to=fr/en --limit=<1..5>` | limit capped to 5. |
| `grimba.cockpit.runbook` | `grimba:fetch-newsapi` | manual network/provider fetch. |
| `grimba.newsapi.run` | `grimba:fetch-newsapi` | synchronous admin action. |
| `grimba.story-clusters.nobuai-summary` | `grimba:nobuai-summaries --cluster=<id> --limit=1 --force` | requires at least two published posts in cluster. |

## Network, Cost, And Write Risk

| Command | Network/provider risk | Write risk |
|---|---|---|
| `grimba:poll-feeds` | Fetches upstream feeds. | Creates draft posts, feed item rows, cluster links, feed health state. |
| `grimba:fetch-newsapi` | Calls NewsAPI. | Creates draft posts and NewsAPI ledger rows. |
| `grimba:translate-pending` | Calls translation/LLM providers unless `--dry-run`. | Updates posts and `grimba_post_translations`; writes failure queue. |
| `grimba:nobuai-summaries` | Calls LLM providers unless `--dry-run`. | Updates NobuAI summary columns on posts. |
| `grimba:nobuai-health --live` | Makes one LLM call. | No app data writes. |
| `grimba:fetch-full-articles` | Fetches upstream article pages. | Updates `full_content`, `full_fetched_at`, and extraction errors. |
| `grimba:enrich-drafts` | Fetches feeds and article pages. | Updates `posts.image` unless `--dry-run`. |
| `grimba:publish-trusted` | None. | Publishes drafts unless `--dry-run`. |
| `grimba:publish-guardrail-categories` | None. | Creates/updates categories, category pivots, publishes drafts unless `--dry-run`. |
| `grimba:dedupe-posts --apply` | None. | Repoints ledgers, deletes pivots/slugs/duplicate posts. |
| `grimba:recluster` | None. | Updates `story_cluster_id`, may create clusters through helper unless `--dry-run`. |
| `grimba:classify-categories` | None. | Writes category pivots; `--force` replaces existing pivots; `--category=` scopes to current members of one category and reports before/after changes. |
| `grimba:cleanup-slugs` | None. | Deletes orphan slugs unless `--dry-run`. |

## Findings And Follow-Ups

- The automation monitor wrapper is used for most high-frequency Grimba scheduled jobs, but `grimba:cleanup-slugs` and `grimba:enrich-drafts` are scheduled directly. S004 scheduler inventory should decide whether these need monitor entries.
- Several scheduled commands run in background with overlapping locks. S004 should verify lock store behavior in pre-prod and production.
- `grimba:publish-guardrail-categories` publishes drafts automatically into review buckets. S151-S160 publishing automation should verify this is still the desired production policy.
- `grimba:cleanup-slugs` is destructive by default and is scheduled daily without `--dry-run`. S006/S007 should verify backup/restore posture before release.
- Provider-cost commands have dry-run or low-limit paths, but scheduled translation and NobuAI runs can still incur costs once keys are configured. S251-S300 and S301-S350 should add budget guard evidence.
- `grimba:dedupe-posts` is safely dry-run by default; the `--apply` path should only be used with a recent backup and evidence capture.

## Handoff

Sprint: S003  
Outcome: command inventory complete  
Files: `docs/GRIMBANEWS_S003_COMMAND_INVENTORY.md`, `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`  
Verification: `php artisan schedule:list`; command help probes for `grimba:health`, `grimba:poll-feeds`, `grimba:translate-pending`, `grimba:nobuai-summaries`, `grimba:dedupe-posts`, `grimba:fetch-full-articles`, `grimba:enrich-drafts`; source reads for all 14 command classes  
Risks: monitored vs direct schedules need review, daily cleanup is destructive, provider-cost commands need budget gates, route/admin manual command triggers need timeout QA  
Next: S004 scheduler inventory  
Commit: recorded in sprint handoff after push
