# S020 — Test Coverage Audit

**Generated:** 2026-05-19
**Method:** filesystem scan + `php artisan test` baseline.

## Headline

- **Suite total:** 517 passed / 0 failed / 20 incomplete / 2 skipped / **4433 assertions** / 164s
- **Test files:** 85 (`find tests -name '*Test.php' | xargs grep -l 'public function test_' | wc -l`)
- **Feature tests:** 75+ files in `tests/Feature/`
- **Unit tests:** 6 files in `tests/Unit/` (article text, language detector, etc.)
- **E2E (Playwright):** 14 specs in `tests/e2e/` (mobile-shell-contrast, golden-path, CSP smoke, etc.)

## Coverage by area

| Area | Test file | Sprint reference |
|---|---|---|
| Launch readiness (master contract) | `tests/Feature/GrimbaLaunchReadinessTest.php` | 49 tests / 700+ assertions; SEO + security + sitemap + canonical + 404 + JSON-LD validity + share-kit + RSS + open-redirect/SSRF |
| Dark mode contract | `tests/Feature/GrimbaDarkModeContractTest.php` | S-MODE band — light/dark attrs, FOUC guard, single body class, no hardcoded white bg |
| Daily publish freshness | `tests/Feature/DailyPublishFreshnessTest.php` | S154, S155, S171, S180, S181 (7 tests, 37 assertions) |
| Automation schedule | `tests/Feature/AutomationScheduleTest.php` | S162, S164, S166 (4 tests, 64 assertions) |
| RSS feeds seeder | `tests/Feature/RssFeedsSeederTest.php` | S102, S109 |
| Dedupe posts command | `tests/Feature/DedupePostsCommandTest.php` | S203, S209, S210 |
| Editorial categories | `tests/Feature/EditorialCategoriesTest.php` | S481 |
| Story breakdown | `tests/Feature/StoryBreakdownTest.php` | S531, S532, S543 |
| Article text sanitize | `tests/Unit/GrimbaArticleTextTest.php` | S532, S916 |
| Language detector | `tests/Unit/GrimbaLanguageDetectorTest.php` | S-LANG-02 (26 tests, 51 assertions) |
| Dossier language | `tests/Feature/GrimbaDossierLanguageTest.php` | S-LANG-11 |
| Category badge cross-locale | `tests/Feature/GrimbaCategoryBadgeCrossLocaleTest.php` | S-CAT |
| Category badge smoke | `tests/Feature/GrimbaCategoryBadgeSmokeTest.php` | S-CAT-07 |
| Filter for target locale | `tests/Feature/GrimbaFilterForTargetLocaleTest.php` | S-LSAT |
| Home rails | `tests/Feature/GrimbaHomeRailsTest.php` | S-CAT |
| Info-pill | `tests/Feature/GrimbaInfoPillTest.php` | S-PILL-09 |
| NobuAI brand purity | `tests/Feature/GrimbaNobuAiBrandPurityTest.php` | S076 |
| Translation presenter | `tests/Feature/GrimbaTranslationPresenterTest.php` | S-LANG-09 |
| Security headers | `tests/Feature/SecurityHeadersTest.php` | S909, S910 |
| Admin route smoke | `tests/Feature/AdminRouteSmokeTest.php` | G5 admin readiness |
| Admin settings | `tests/Feature/AdminSettingsTest.php` | G5 admin readiness |
| Admin chrome assets | `tests/Feature/AdminChromeAssetsTest.php` | G5 admin readiness |
| Provider credits | `tests/Unit/GrimbaProviderCreditsTest.php` | S253, S907 |
| Sponsor leads | `tests/Feature/GrimbaAdvertiserLeadsTest.php` | S-ADS-12 |
| Cluster page | `tests/Feature/ClusterPageTest.php` | dossier integrity |
| Image proxy SSRF | (covered in GrimbaLaunchReadinessTest 24+3 probes) | S911, S912 |
| Vault (coffre) | `tests/Feature/PublicVaultTest.php` etc. | S913 |

## Coverage gaps (flagged for closure)

- **20 tests marked `markTestIncomplete`** — legacy dossier-reinvention markup; doc at `docs/GRIMBANEWS_TEST_DEBT_DOSSIER_REINVENTION.md`
- **2 tests `markTestSkipped`** — AdRevenueSurfaceTest home-rendering tests, pass in isolation, fail with RefreshDatabase pollution from other tests. Resolved by Wave JJJJJ skip-on-missing-seed guards.
- **No Playwright screenshot diff in CI** — S-MODE-02 + S741-S750 visual baseline matrix pending.
- **No NobuAI provider live-smoke in CI** — S300 NobuAI signoff blocked on this.
- **No backup-restore drill test** — S964 verification exists; full restore loop hasn't run.

## Closes

- S020 (test coverage audit)
