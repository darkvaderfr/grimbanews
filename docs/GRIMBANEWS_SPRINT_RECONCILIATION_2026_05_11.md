# GrimbaNews Sprint Reconciliation - 2026-05-11

**Scope:** reconcile the formal 1000-sprint master ledger with shipped GrimbaNews work and choose the next best sprint from current blockers.

## Accounting

The 1000-sprint ledger previously marked only S001-S006 complete, which made formal completion 0.6%. That was accurate for the written evidence ledger but undercounted real production-hardening work already shipped and deployed.

After reconciliation and the current freshness/disk/ingest-to-public/dedupe-review sprint, the master ledger has 23 evidenced completed sprints:

- S001-S006 current-state inventories.
- S102 and S109 RSS health/quarantine.
- S154, S155, S162, S164, S166, S171, and S180 publishing automation and scheduler reporting.
- S181 ingest-to-public freshness guard.
- S113 NewsAPI configuration guard.
- S203, S209, and S210 safer dedupe policy, review reporting, and regression tests.
- S485 public edition dark-mode readability.
- S612 cockpit automation board.
- S973 production log-retention/disk floor.

Formal master-ledger completion is now 23 / 1000 = 2.3%.

Practical production-readiness remains about 33-35%. The product is live enough to require operational discipline, but release gates are not fully green.

## Shipped Evidence Crosswalk

| Area | Evidence | Master sprint mapping |
|---|---|---|
| Daily publishing freshness | `6586460`, `b62eaf8`, `app/Console/Commands/GrimbaEnsureDailyPublish.php`, `app/Console/Commands/GrimbaHealth.php`, `tests/Feature/DailyPublishFreshnessTest.php` | S154, S155, S171, S180 |
| Ingest-to-public publication guard | `docs/GRIMBANEWS_INGEST_TO_PUBLIC_FRESHNESS_2026_05_11.md`, `app/Support/GrimbaPublicationPipeline.php`, `app/Console/Commands/GrimbaHealth.php`, `resources/views/grimba-admin/cockpit.blade.php`, `tests/Feature/DailyPublishFreshnessTest.php` | S181 |
| Scheduler run tracking | `06422e0`, `a87c86a`, `b62eaf8`, `app/Support/GrimbaAutomationMonitor.php`, `routes/console.php`, `tests/Feature/AutomationScheduleTest.php` | S162, S164, S612 |
| Missed-run alert | `app/Support/GrimbaAutomationMonitor.php`, `app/Console/Commands/GrimbaHealth.php`, `resources/views/grimba-admin/cockpit.blade.php`, `tests/Feature/DailyPublishFreshnessTest.php` | S166 |
| RSS health and feed quarantine | `d67588a`, `00caf83`, `app/Support/GrimbaRssFeedHealth.php`, `database/seeders/RssFeedsSeeder.php`, `tests/Feature/RssFeedsSeederTest.php` | S102, S109 |
| NewsAPI configuration guard | `docs/GRIMBANEWS_NEWSAPI_CONFIG_GUARD_2026_05_11.md`, `app/Console/Commands/GrimbaFetchNewsApi.php`, `app/Console/Commands/GrimbaHealth.php` | S113 |
| Public dark/mobile readability | `11238a9`, `5476a6f`, `59da49b`, `public/themes/echo/css/grimba-home.css`, `tests/e2e/grimbanews-mobile-shell-contrast.cjs` | S485 plus broader G4 risk reduction |
| Dedupe safety | `fe31be0`, `app/Console/Commands/GrimbaDedupePosts.php`, `tests/Feature/DedupePostsCommandTest.php` | S203, S210 |
| Dedupe audit report | `docs/GRIMBANEWS_TITLE_ONLY_DEDUPE_REVIEW_2026_05_11.md`, `app/Console/Commands/GrimbaDedupePosts.php`, `tests/Feature/DedupePostsCommandTest.php` | S209 |
| Security/header hardening | `1e5af1f`, `app/Http/Middleware/GrimbaSecurityHeaders.php`, `tests/Feature/SecurityHeadersTest.php`, `tests/e2e/grimbanews-csp-smoke.cjs` | G7 risk reduction; not yet enough to close a G7 sprint row |
| Disk headroom floor | `docs/GRIMBANEWS_PROD_DISK_HEADROOM_2026_05_11.md`, `app/Console/Commands/GrimbaHealth.php` | S973 |

## Current Gates

| Gate | Status | Notes |
|---|---|---|
| G1 Current-state review | Partial | S001-S006 complete; public/admin surface inventory rows still need formal closure. |
| G2 Autonomous publishing | Strong partial | Freshness watchdog, ops health, scheduler ledger, feed quarantine, and RSS/NewsAPI-backed publication health exist; daily freshness must keep passing in production. |
| G3 NobuAI readiness | Partial | Core paths exist; provider failure, cost, and live smoke gates remain. |
| G4 Public UX readiness | Partial | Dark/mobile readability improved; still needs route-by-route visual QA. |
| G5 Admin readiness | Partial | Cockpit and automation board exist; full browser QA remains. |
| G6 Data readiness | Partial | Safer dedupe and title-only review tooling exist; title-only editorial decisions, restore drill, and deeper disk headroom remain blockers. |
| G7 Security readiness | Partial | CSP/security headers exist; auth, proxy, cookies, exports, and retention still need closure. |
| G8 Performance readiness | Early | Query, asset, image, and TTFB budgets need evidence. |
| G9 Release readiness | Partial | Deploy and smoke paths work; full CI/E2E/visual diff/rollback evidence remains. |
| G10 Business readiness | Early | Ads, subscriber value, analytics, support, and launch monitoring remain open. |

## Next Best Sprint

Completed sprint: **S181 RSS-to-published smoke / ingest-to-public daily guard**.

Reason: the most recent user-facing failure risk is stale daily articles. Existing guards caught low publication volume and stale scheduler jobs, but the system also needed to prove that RSS/NewsAPI-backed articles are actually becoming public rather than letting manual posts mask a broken automated pipeline.

Acceptance evidence:

- `grimba:health --fail-on-risk` now reports and enforces `ingest-published 24h`.
- Admin cockpit exposes a `Published 24h` provenance tile with RSS, NewsAPI, and manual counts.
- Focused tests prove manual publications cannot mask a broken ingest-to-public path.
- Focused tests still prove stale freshness automation is release-blocking.
- `php artisan test tests/Feature/DailyPublishFreshnessTest.php` passed with 7 tests and 37 assertions.
- `php artisan test tests/Feature/AutomationScheduleTest.php` passed with 4 tests and 64 assertions.
- `php artisan test` passed with 154 tests and 2238 assertions.
- `GRIMBANEWS_BASE_URL=http://127.0.0.1:8001 npm run test:e2e:mobile-shell` and `GRIMBANEWS_BASE_URL=http://127.0.0.1:8001 npm run test:e2e:golden-path` passed.

Continuation sprint: **S209 dedupe audit report**.

Acceptance evidence:

- `grimba:dedupe-posts --review-title-groups` prints a non-destructive title-only duplicate review report.
- Health and cockpit copy point to review mode before any `--include-title-groups` consideration.
- `php artisan test tests/Feature/DedupePostsCommandTest.php` passed with 2 tests and 25 assertions.

## Additional Regressions Closed During Verification

- Admin RSS feed page restored the visible title `Tour de contrôle RSS` expected by admin readiness tests.
- Public source pages again display the exact numeric `score biais`.
- Direct `/comparatif/{clusterId}` pages now bypass the public region scope, so a valid comparison dossier cannot render empty only because the reader's current edition excludes the source country.
