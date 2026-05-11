# GrimbaNews Ingest-To-Public Freshness Guard - 2026-05-11

**Scope:** close the daily-articles blind spot where the site can show enough recent publications while RSS/NewsAPI-backed articles are not actually reaching the public feed.

## Problem

The existing health guard counted total public posts in the last 24 hours and counted RSS/NewsAPI intake in the last 24 hours. Those are necessary, but not sufficient.

A manual publication, seed repair, or unrelated editorial post could satisfy the public post count while the automated ingest-to-public path stayed broken. That is exactly the class of failure that makes the homepage look stale to readers even when background jobs appear busy.

## Change

- Added `App\Support\GrimbaPublicationPipeline` as the shared source of truth for daily publication provenance.
- `grimba:health --fail-on-risk` now checks `ingest-published 24h`, meaning public posts backed by `rss_feed_items` or `newsapi_items`.
- The default ingest-backed floor follows `--min-published-24h`; operators can override it with `--min-ingested-published-24h`.
- The admin cockpit now shows a `Published 24h` operations tile with RSS-backed, NewsAPI-backed, and manual publication counts.
- Manual publications can no longer mask a broken RSS/NewsAPI-to-public path in the release-blocking health command.

## Verification

- `php artisan test tests/Feature/DailyPublishFreshnessTest.php`
  - 7 tests, 37 assertions.
- `php artisan test tests/Feature/AutomationScheduleTest.php`
  - 4 tests, 64 assertions.
- `GRIMBANEWS_BASE_URL=http://127.0.0.1:8001 npm run test:e2e:mobile-shell`
  - passed against the active local server; dark-mode editorial selection chip stayed readable at 320px and 390px.
- `GRIMBANEWS_BASE_URL=http://127.0.0.1:8001 npm run test:e2e:golden-path`
  - passed against the active local server.

New regression coverage proves:

- Health fails when the public 24-hour count is met only by manual publications.
- Health passes when RSS-backed public posts meet the daily floor.
- Existing scheduler stale-run failure behavior still works.

## Remaining Dependencies

- Production still needs a real NewsAPI key before NewsAPI can contribute to the ingest-backed publication floor.
- RSS is currently the reliable active freshness path.
- Title-only duplicate groups remain editorial-review only; do not bulk-delete them without source review.
