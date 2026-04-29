# GrimbaNews S004 Scheduler Inventory

**Sprint:** S004  
**Outcome:** scheduler inventory  
**Status:** complete  
**Date:** 2026-04-29  
**Verification:** `php artisan schedule:list`, `routes/console.php`, `app/Support/GrimbaAutomationMonitor.php`, `tests/Feature/AutomationScheduleTest.php`, deploy bootstrap cron read

S004 inventories Laravel scheduler behavior for GrimbaNews. It builds on S003 command inventory and focuses on cadence, locking, monitoring, production cron installation, and release-gate risks.

## Summary

| Scope | Count |
|---|---:|
| Total local scheduled entries | 20 |
| Grimba-owned scheduled entries | 11 |
| Grimba monitored scheduled entries | 9 |
| Grimba direct scheduled entries | 2 |
| Grimba entries using `onOneServer()` | 11 |
| Grimba entries using `withoutOverlapping()` | 11 |
| Grimba entries using `runInBackground()` | 10 |
| Grimba entries with provider/network cost | 8 |

Schedules are defined in `routes/console.php`; this project does not have `app/Console/Kernel.php`.

## Production Cron Entry

First-time provisioning installs this root crontab line from `deploy/bootstrap.sh`:

```cron
* * * * * cd /var/www/grimbanews/current && sudo -u www-data php artisan schedule:run >> /var/log/grimbanews-cron.log 2>&1
```

Release verification should tail `/var/log/grimbanews-cron.log` after deploy and confirm Botble's admin cron status is also updating.

## Grimba Scheduler Table

| Cadence | Command | Job key | Monitor | Lock | Background | Risk |
|---|---|---|---|---|---|---|
| Daily `03:15` | `grimba:cleanup-slugs` | none | no | `onOneServer`, `withoutOverlapping()` | no | Deletes orphan slugs. |
| Every 30 minutes | `grimba:poll-feeds` | `rss_ingest` | yes | `onOneServer`, `withoutOverlapping(20)` | yes | Network ingest and draft writes. |
| `15,45 * * * *` | `grimba:translate-pending --to=fr --limit=50` | `translate_fr` | yes | `onOneServer`, `withoutOverlapping(20)` | yes | Provider cost once keys exist. |
| `20,50 * * * *` | `grimba:translate-pending --to=en --limit=50` | `translate_en` | yes | `onOneServer`, `withoutOverlapping(20)` | yes | Provider cost once keys exist. |
| `5,35 * * * *` | `grimba:publish-trusted` | `publish_trusted` | yes | `onOneServer`, `withoutOverlapping(15)` | yes | Publishes drafts automatically. |
| `8,38 * * * *` | `grimba:publish-guardrail-categories` | `publish_guardrails` | yes | `onOneServer`, `withoutOverlapping(15)` | yes | Publishes drafts into review categories. |
| `12,42 * * * *` | `grimba:fetch-full-articles --limit=80` | `full_articles` | yes | `onOneServer`, `withoutOverlapping(25)` | yes | Upstream fetch and content writes. |
| `18,48 * * * *` | `grimba:nobuai-summaries --limit=80` | `nobuai_summaries` | yes | `onOneServer`, `withoutOverlapping(25)` | yes | LLM cost once keys exist. |
| `25,55 * * * *` | `grimba:nobuai-summaries --stale --limit=25` | `nobuai_stale` | yes | `onOneServer`, `withoutOverlapping(25)` | yes | LLM cost once keys exist. |
| `15 6,10,14,18,22 * * *` | `grimba:fetch-newsapi` | `newsapi_fetch` | yes | `onOneServer`, `withoutOverlapping(20)` | yes | NewsAPI quota/cost; gated by `grimba_newsapi_active`. |
| Monday `04:10` | `grimba:enrich-drafts` | none | no | `onOneServer`, `withoutOverlapping(60)` | yes | Upstream fetch and image writes. |

## Automation Monitor

`GrimbaAutomationMonitor` tracks scheduled runs in `grimba_automation_runs` when the table exists. It records:

- `job_key`
- `command`
- `status`
- `exit_code`
- `started_at`
- `finished_at`
- `duration_ms`
- `error_message`

Indexed lookup paths:

- `job_key`, `finished_at`
- `status`, `finished_at`

The cockpit reads this table to display last run, failure, and stale status. A job is stale when the last finished run is missing or older than twice its expected interval.

## Monitor Coverage

| Job key | Label | Expected minutes | Covered by schedule |
|---|---|---:|---|
| `rss_ingest` | RSS ingest | 30 | yes |
| `translate_fr` | Translate to FR | 30 | yes |
| `translate_en` | Translate to EN | 30 | yes |
| `publish_trusted` | Trusted publish | 30 | yes |
| `publish_guardrails` | Guardrail publish | 30 | yes |
| `full_articles` | Full article extraction | 30 | yes |
| `nobuai_summaries` | NobuAI insights | 30 | yes |
| `nobuai_stale` | NobuAI stale refresh | 30 | yes |
| `newsapi_fetch` | NewsAPI sweep | 288 | yes |

Direct schedules not currently monitored:

- `grimba:cleanup-slugs`
- `grimba:enrich-drafts`

## Cache And Lock Store Notes

`onOneServer()` and `withoutOverlapping()` depend on Laravel cache locks. Current environment files set:

- `.env`: `CACHE_STORE=file`
- `.env.example`: `CACHE_STORE=file`
- `config/cache.php`: file store uses `storage/framework/cache/data` and `lock_path` under the same path.

This can work on a single VPS when permissions are correct. It is not a multi-server-safe lock store. If GrimbaNews moves to multiple app nodes or multiple scheduler runners, switch production locks to Redis, database, Memcached, or another shared lock store before enabling multiple schedulers.

## Timing Pipeline

The core publishing loop is intentionally staggered:

1. RSS ingest runs every 30 minutes.
2. Translation jobs run at `:15/:45` and `:20/:50`.
3. Trusted publish runs at `:05/:35`.
4. Guardrail publish runs at `:08/:38`.
5. Full article extraction runs at `:12/:42`.
6. NobuAI summaries run at `:18/:48`.
7. NobuAI stale refresh runs at `:25/:55`.
8. NewsAPI sweeps run five times daily at `06:15`, `10:15`, `14:15`, `18:15`, and `22:15`.
9. Image backfill runs weekly Monday `04:10`.

The stagger reduces contention but creates a high number of background jobs in the same hour once provider keys are configured.

## Existing Test Coverage

`tests/Feature/AutomationScheduleTest.php` currently verifies:

- `php artisan schedule:list` contains the main Grimba automation pipeline commands.
- `GrimbaAutomationMonitor::start()` and `finish()` record success/failure rows in `grimba_automation_runs`.

Coverage gaps:

- It does not assert `cleanup-slugs`, `fetch-newsapi`, or `enrich-drafts` schedule entries.
- It does not assert lock options, `runInBackground`, or `onOneServer`.
- It does not smoke `schedule:run` under the production crontab user.

## Release Risks And Follow-Ups

- `grimba:cleanup-slugs` is destructive and direct-scheduled. Consider wrapping it in `GrimbaAutomationMonitor`, adding a release evidence step, or requiring backup evidence before production.
- `grimba:enrich-drafts` is network-heavy and direct-scheduled. Consider monitor coverage so weekly failures surface in the cockpit.
- `CACHE_STORE=file` is acceptable for a single scheduler host but not for multi-node locking. Production release evidence should state the intended scheduler topology.
- `runInBackground()` means `schedule:run` can return quickly while jobs continue. Release smoke should check process behavior, log output, and overlapping locks under the `www-data` user.
- Provider-cost jobs are scheduled aggressively once keys exist. S251-S350 should add hard budget evidence and failure redaction before production.
- The bootstrap echo text still mentions only `grimba:poll-feeds` and `grimba:cleanup-slugs`; deployment docs should be updated later to reflect the full automation pipeline.

## Handoff

Sprint: S004  
Outcome: scheduler inventory complete  
Files: `docs/GRIMBANEWS_S004_SCHEDULER_INVENTORY.md`, `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`  
Verification: `php artisan schedule:list`; `routes/console.php`; `app/Support/GrimbaAutomationMonitor.php`; `tests/Feature/AutomationScheduleTest.php`; `deploy/bootstrap.sh` cron install read  
Risks: two direct unmonitored Grimba schedules, file cache locking is single-host only, background job smoke still needed, provider-cost schedules need budget gates  
Next: S005 model inventory  
Commit: recorded in sprint handoff after push
