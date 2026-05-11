# GrimbaNews Sprint Reconciliation - 2026-05-11

**Scope:** reconcile the formal 1000-sprint master ledger with shipped GrimbaNews work and choose the next best sprint from current blockers.

## Accounting

The 1000-sprint ledger previously marked only S001-S006 complete, which made formal completion 0.6%. That was accurate for the written evidence ledger but undercounted real production-hardening work already shipped and deployed.

After reconciliation and the current freshness sprint, the master ledger has 19 evidenced completed sprints:

- S001-S006 current-state inventories.
- S102 and S109 RSS health/quarantine.
- S154, S155, S162, S164, S166, S171, and S180 publishing automation and scheduler reporting.
- S203 and S210 safer dedupe policy and regression tests.
- S485 public edition dark-mode readability.
- S612 cockpit automation board.

Formal master-ledger completion is now 19 / 1000 = 1.9%.

Practical production-readiness remains about 28-30%. The product is live enough to require operational discipline, but release gates are not fully green.

## Shipped Evidence Crosswalk

| Area | Evidence | Master sprint mapping |
|---|---|---|
| Daily publishing freshness | `6586460`, `b62eaf8`, `app/Console/Commands/GrimbaEnsureDailyPublish.php`, `app/Console/Commands/GrimbaHealth.php`, `tests/Feature/DailyPublishFreshnessTest.php` | S154, S155, S171, S180 |
| Scheduler run tracking | `06422e0`, `a87c86a`, `b62eaf8`, `app/Support/GrimbaAutomationMonitor.php`, `routes/console.php`, `tests/Feature/AutomationScheduleTest.php` | S162, S164, S612 |
| Missed-run alert | `app/Support/GrimbaAutomationMonitor.php`, `app/Console/Commands/GrimbaHealth.php`, `resources/views/grimba-admin/cockpit.blade.php`, `tests/Feature/DailyPublishFreshnessTest.php` | S166 |
| RSS health and feed quarantine | `d67588a`, `00caf83`, `app/Support/GrimbaRssFeedHealth.php`, `database/seeders/RssFeedsSeeder.php`, `tests/Feature/RssFeedsSeederTest.php` | S102, S109 |
| Public dark/mobile readability | `11238a9`, `5476a6f`, `59da49b`, `public/themes/echo/css/grimba-home.css`, `tests/e2e/grimbanews-mobile-shell-contrast.cjs` | S485 plus broader G4 risk reduction |
| Dedupe safety | `fe31be0`, `app/Console/Commands/GrimbaDedupePosts.php`, `tests/Feature/DedupePostsCommandTest.php` | S203, S210 |
| Security/header hardening | `1e5af1f`, `app/Http/Middleware/GrimbaSecurityHeaders.php`, `tests/Feature/SecurityHeadersTest.php`, `tests/e2e/grimbanews-csp-smoke.cjs` | G7 risk reduction; not yet enough to close a G7 sprint row |

## Current Gates

| Gate | Status | Notes |
|---|---|---|
| G1 Current-state review | Partial | S001-S006 complete; public/admin surface inventory rows still need formal closure. |
| G2 Autonomous publishing | Strong partial | Freshness watchdog, ops health, scheduler ledger, and feed quarantine exist; daily freshness must keep passing in production. |
| G3 NobuAI readiness | Partial | Core paths exist; provider failure, cost, and live smoke gates remain. |
| G4 Public UX readiness | Partial | Dark/mobile readability improved; still needs route-by-route visual QA. |
| G5 Admin readiness | Partial | Cockpit and automation board exist; full browser QA remains. |
| G6 Data readiness | Partial | Safer dedupe exists; production dedupe apply, restore drill, and disk headroom remain blockers. |
| G7 Security readiness | Partial | CSP/security headers exist; auth, proxy, cookies, exports, and retention still need closure. |
| G8 Performance readiness | Early | Query, asset, image, and TTFB budgets need evidence. |
| G9 Release readiness | Partial | Deploy and smoke paths work; full CI/E2E/visual diff/rollback evidence remains. |
| G10 Business readiness | Early | Ads, subscriber value, analytics, support, and launch monitoring remain open. |

## Next Best Sprint

Completed sprint: **S166 missed-run alert / S180 daily automation report reinforcement**.

Reason: the most recent user-facing failure risk is stale daily articles. Existing guards catch low publication volume, but the system also needs to fail before content freshness drops when the scheduler ledger shows RSS/publish/watchdog/health jobs are stale or failing.

Acceptance evidence:

- `grimba:health --fail-on-risk` fails when critical freshness jobs have no recent successful scheduler run.
- Admin cockpit uses the same scheduler status logic as the health command.
- Focused tests prove stale freshness automation is release-blocking.
- `php artisan test` passed with 150 tests and 2220 assertions.

## Additional Regressions Closed During Verification

- Admin RSS feed page restored the visible title `Tour de contrôle RSS` expected by admin readiness tests.
- Public source pages again display the exact numeric `score biais`.
- Direct `/comparatif/{clusterId}` pages now bypass the public region scope, so a valid comparison dossier cannot render empty only because the reader's current edition excludes the source country.
