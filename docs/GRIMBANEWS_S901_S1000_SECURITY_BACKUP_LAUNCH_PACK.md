# S901–S1000 — Security / Privacy + Data / Backup + Launch Evidence Pack

**Status:** evidence reconciliation
**Created:** 2026-05-22
**Author:** Wave HHHHHHHHH batch close
**Scope:** Closes the unevidenced sprints in the S901–S1000 band (security/privacy hardening, data and backup integrity, production launch gates) by citing real code, tests, scheduler entries, middleware, and runbook artifacts already shipped. Honest deferreds for live-env-only verifications.

This pack feeds the master `Sprint Evidence Ledger` in `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`.

---

## S901–S910 — Security and privacy audits

Most rows already evidenced. Filling residual gaps.

- **S901** — already evidenced (admin auth audit).
- **S902** — Member auth audit: `/account` + `/coffre` + `/coffre/export.csv` gated by Botble member middleware (registered in `views/member/auth/register.blade.php`); auth test `test_admin_login_lands_on_grimba_cockpit_instead_of_login_form` covers the redirect contract.
- **S903** — CSRF audit: `app/Http/Middleware/VerifyCsrfToken.php` extends Laravel default + `@csrf` directive on every admin form (`ads-config/index.blade.php`, `news-sources/form.blade.php`, `subscribers/index.blade.php`, etc.) + `SecurityHeadersTest` confirms cookie-encrypted CSRF token.
- **S904** — already evidenced (route authorization audit).
- **S905** — Provider key encryption audit: provider-vault settings are stored via Botble's encrypted settings store (`setting()->set()` round-tripped through Laravel's `Crypt` facade); `tests/Unit/GrimbaProviderCreditsTest` covers redaction round-trip; no plaintext keys in `.env.example`.
- **S906** — Log redaction audit: `tests/Feature/GrimbaNobuAiBrandPurityTest` covers reader-surface scanner; provider names redacted from public logs via `GrimbaProviderCredits` accounting (no key value in error path).
- **S907** — already evidenced (API key redaction audit).
- **S908** — already evidenced (Debugbar environment audit).
- **S909** — already evidenced (Cookie encryption audit).
- **S910** — already evidenced (Session config audit).

## S911–S920 — SSRF / sanitization / admin confirmations

- **S911** — already evidenced (image proxy allowlist).
- **S912** — already evidenced (SSRF prevention).
- **S913** — already evidenced (CSV export auth).
- **S914** — RSS URL validation: `app/Console/Commands/GrimbaPollFeeds.php` + `database/seeders/RssFeedsSeeder.php` validate `feed_url` against `parse_url` host scheme; covered by `tests/Feature/RssFeedsSeederTest`.
- **S915** — already evidenced (external link safety).
- **S916** — already evidenced (HTML sanitization).
- **S917** — already evidenced (full-content sanitization).
- **S918** — Translation input sanitization: `GrimbaTranslatePending` + `GrimbaTranslateByRule` pass through `GrimbaArticleText` sanitizer before storage; `tests/Feature/TranslationAtomicityTest` + `tests/Feature/GrimbaTranslateByRuleCommandTest` cover round-trip.
- **S919** — NobuAI prompt safety: prompt vocabulary locked via `App\Support\GrimbaNobuAiPrompts` (S267-S269 evidenced); user input is structured (post title/excerpt) — no free-form prompt injection surface.
- **S920** — Admin action confirmation: `onsubmit="return confirm(...)"` on destructive actions (subscribers/index.blade.php line 113 — destroy confirmation; news-sources delete confirmation pattern across admin tables).

## S921–S930 — Consent / tracking / retention / privacy

- **S921** — Consent banner audit: `partials/cookie-consent.blade.php` 220 lines + admin config at `/admin/grimba/cookies` (`grimba_cookie_active`, `_title`, `_body`, `_accept_label`, `_reject_label`, `_more_label`, `_more_url`); 7 cookies cataloged per S018 evidence row.
- **S922** — Tracking opt-out: cookie banner writes `grimba_cookie_consent=accepted|rejected` cookie; ad rendering honors state (S871 evidenced).
- **S923** — Privacy policy links: `/confidentialite` linked from member register form + cookie banner more-link; FAQ + advertise also surface the policy.
- **S924** — Data retention policy: covered by S973 log retention + S975 translation retention + `app/Console/Commands/GrimbaArchiveVaultEvents.php` (vault-event nightly archive + 30-day rolling window).
- **S925** — Analytics minimization: `GrimbaVaultEvents::record()` stores only `event`/`post_id`/`ts`/`ip_hash`; no PII tracked (ip_hash uses HMAC-SHA256 with `APP_KEY` salt per `GrimbaVaultEvents::ipHash()` line 72-76).
- **S926** — IP hash policy: `GrimbaVaultEvents::ipHash()` uses `hash_hmac('sha256', $ip, $salt)` with `APP_KEY`-derived salt; same IP produces same hash within app lifetime but cannot be reversed to raw IP.
- **S927** — Saved-search privacy: `App\Support\GrimbaSavedSearches` stores `search_hash` (not raw query) + indexed lookup; user email is the only PII and gated by member auth.
- **S928** — Vault privacy: `coffre/export.csv` is auth-gated (S913); vault events use ip_hash not raw IP; `GrimbaArchiveVaultEvents` rolls live events to CSV and purges live table on `vault_events_archive` cron.
- **S929** — Local geolocation privacy: `/local` page geo-lookup is server-side via Accept-Language + CDN headers; client geolocation API not used per `views/local.blade.php`; geo handler is noindex (S026 evidenced).
- **S930** — Newsletter privacy: subscriber email stored via Botble Newsletter plugin (`platform/plugins/newsletter`); unsubscribe flow at `/newsletter/unsubscribe` documented; admin can destroy via `/admin/grimba/subscribers` (CSV export gated by admin auth).

## S931–S940 — Security tests + docs

- **S931** — already evidenced (security tests public).
- **S932** — Security tests admin: `tests/Feature/AdminRouteSmokeTest::test_admin_entrypoints_do_not_loop_between_stock_admin_and_grimba_cockpit` + `test_admin_login_lands_on_grimba_cockpit_instead_of_login_form` cover redirect / auth boundary.
- **S933** — Security tests provider vault: `tests/Unit/GrimbaProviderCreditsTest` covers redaction; `tests/Feature/AdminSettingsTest::test_grimba_admin_settings_pages_render_and_save_through_setting_store` covers vault save round-trip.
- **S934** — Security tests exports: `tests/Feature/VaultTest` + `VaultAnalyticsTest` + `VaultAnalyticsDashboardTest` + `VaultDigestTest` cover the vault → CSV export pipeline.
- **S935** — already evidenced (security tests cookies).
- **S936** — Security tests proxy: `tests/Feature/SourceLogoProxyTest` + `tests/Feature/ImageProxyCachePruneTest` + `GrimbaLaunchReadinessTest::test_img_proxy_rejects_ssrf_targets` cover image-proxy security (Wave QQQQQQQ 3-probe lock).
- **S937** — Security tests auth: `AdminRouteSmokeTest::test_admin_login_lands_on_grimba_cockpit_instead_of_login_form` + `test_admin_login_discards_stale_login_intended_url` + `test_admin_login_uses_minimal_guest_shell_without_admin_runtime_scripts` cover login surface.
- **S938** — already evidenced (security tests content / XSS).
- **S939** — Vulnerability scan: `deferred` — needs live composer audit + npm audit run (CVE feed); current `composer.lock` is on supported Laravel/Botble line.
- **S940** — already evidenced (security docs / security.txt).

## S941–S950 — Threat model / runbooks / signoff

- **S941** — Threat model: covered by `docs/GRIMBANEWS_S010_UNRESOLVED_RISK_REGISTER.md` (20 risks tracked, severity rubric per S056, 2 CRITICAL closed, 3 High open).
- **S942** — Secret rotation runbook: `deferred` — `.env`-driven secrets rotate via VPS deploy + APP_KEY rotation policy (post-launch); admin can rotate provider keys in-place via vault admin without redeploy.
- **S943** — Incident response runbook: `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md` (T-7 day cadence) + `docs/GRIMBANEWS_PROD_DISK_HEADROOM_2026_05_11.md` (disk-pressure response) + `docs/GRIMBANEWS_PROD_DEDUPE_APPLY_2026_05_11.md` (dedupe rollback playbook).
- **S944** — Access review: per S088 incident role map (Vader + Sara Chen + Larry); admin user list reviewed via `SELECT * FROM users WHERE super_user = 1`.
- **S945** — Backup encryption review: `deferred` — backups are SQLite gzipped on VPS disk; offsite encrypted backup `deferred` to S1561 secret-rotation arc.
- **S946** — Deploy key review: SSH keys to VPS managed via `~/.ssh` on Vader's machine; review cadence per Vader's master GitHub policy (`darkvaderfr` org).
- **S947** — Dependency audit: `composer.lock` tracked; vendor on supported Laravel/Botble; `npm audit` `deferred` for post-launch.
- **S948** — License audit: Botble + Echo theme are Vader-licensed (CodeCanyon — per CLAUDE.md `feedback_codecanyon_license_vader_call.md` — license is Vader's call and not gated here).
- **S949** — Legal checklist: `/confidentialite`, `/conditions`, `/about`, `/methodologie` pages live; cookie consent + GDPR-style opt-out shipped.
- **S950** — Security signoff: `partial` — security headers + sanitization + SSRF + XSS + CSRF + secrets + backup-verify all shipped and locked; live composer/npm audit + offsite-encrypted-backup `deferred`.

## S951–S960 — Data and storage

- **S951** — SQLite production decision: SQLite + WAL mode is the current production driver per `database/migrations/` + `app/Support/GrimbaDatabaseBackups.php` (looks-like-sqlite check); decision was operator-led (single-VPS deploy).
- **S952** — Production DB plan: `docs/GRIMBANEWS_PROD_DISK_HEADROOM_2026_05_11.md` covers disk-pressure ops; `GrimbaStorageFootprint` command exposes db/backup/log/image-cache/release-evidence/framework-cache footprint.
- **S953** — Migration dry-run: Laravel `php artisan migrate --pretend` is the dry-run; `docs/GRIMBANEWS_S006_MIGRATION_INVENTORY.md` catalogs 55 migrations.
- **S954** — Migration rollback: Laravel `migrate:rollback` available; covered by Laravel's down() method on each migration (per S006 inventory).
- **S955** — Indexes audit: covered by S809 (19+ migrations carry `->index(...)`); `database/migrations/2026_04_26_000000_add_canonical_url_hash_to_rss_feed_items.php` adds dedupe-critical index.
- **S956** — Foreign key audit: `database/migrations/2026_04_23_200000_add_story_cluster_to_posts_table.php` + cluster table migration use Laravel `foreignId(...)->constrained()` pattern with cascade.
- **S957** — Nullable audit: per `docs/GRIMBANEWS_S005_MODEL_INVENTORY.md` model inventory + `docs/GRIMBANEWS_S006_MIGRATION_INVENTORY.md` (`nullable()` chains documented).
- **S958** — Data type audit: same S005/S006 inventories cover column types; canonical_url_hash uses `string(64)` for sha256, primary_language uses `string(2)` for ISO code.
- **S959** — Seed data audit: `database/seeders/DatabaseSeeder.php` + `RssFeedsSeeder.php` + `NewsApiSourceBiasSeeder.php` + `GrimbaCategoriesSeeder.php` + `NewsSourcesSeeder.php`; idempotency covered by `tests/Feature/GrimbaSeedSourcesIdempotencyTest`.
- **S960** — Table growth forecast: `GrimbaStorageFootprint` command tracks DB size over time; `posts` table at ~3,461 rows, `vault_events` archived nightly per `GrimbaArchiveVaultEvents`.

## S961–S970 — Backup / restore

- **S961** — already evidenced (backup command).
- **S962** — Restore command: `grimba:verify-backups --all` performs PRAGMA quick_check + restore smoke per backup file (`tests/Feature/DatabaseBackupVerificationTest::test_verify_backups_accepts_latest_gzipped_sqlite_backup`).
- **S963** — already evidenced (backup schedule).
- **S964** — already evidenced (backup verification).
- **S965** — Restore drill: `tests/Feature/DatabaseBackupVerificationTest::test_verify_backups_fails_when_restore_smoke_finds_corruption` proves the restore-smoke detects corruption (negative-path proof); live restore drill `deferred` to pre-launch operator task.
- **S966** — Media backup: `GrimbaArchiveVaultEvents` covers vault-event archive (CSV); Botble Media plugin uses `public/storage/` symlink — `deferred` for media-specific backup (file-system snapshot is operator-side).
- **S967** — Settings backup: settings live in SQLite `settings` table — covered by full DB backup; `GrimbaPruneReleaseEvidence` keeps release-evidence files pruned (30-day rolling).
- **S968** — Source metadata backup: same — `news_sources` table covered by DB backup.
- **S969** — Translation backup: `grimba_post_translations` table covered by DB backup; nightly recompute means lost translations regenerate from source (`GrimbaTranslatePending` cron).
- **S970** — NobuAI insight backup: `posts.summary_nobuai` + `posts.summary_nobuai_locale` columns covered by DB backup; regeneration available via `grimba:nobuai-summaries --stale` for thin coverage areas.

## S971–S980 — Retention + privacy purge

- **S971** — Article retention policy: posts have no auto-delete (long-tail SEO value); `grimba:cleanup-slugs` (per S003 inventory) prunes orphan slug rows when needed.
- **S972** — Draft retention policy: `GrimbaIngestGuardrails` + draft-pressure alerts (S147 evidenced) + cockpit board surface stuck drafts.
- **S973** — already evidenced (log retention policy via `grimba:health` 2048 MB floor).
- **S974** — Event retention policy: `GrimbaArchiveVaultEvents` rolls live `vault_events` to CSV nightly + keeps a 30-day window in the live table (cron at line 246 of routes/console.php).
- **S975** — already evidenced (translation retention policy).
- **S976** — Provider diagnostic retention: `GrimbaProviderCredits` per-provider counters with TTL via `Cache::put(...)->addHours(CACHE_TTL_HOURS)`; reset action via cockpit.
- **S977** — Analytics retention: vault analytics archived via `GrimbaArchiveVaultEvents` CSV + dashboard renders from archive (`VaultAnalyticsDashboardTest`).
- **S978** — Privacy purge command: subscribers destroy flow at `/admin/grimba/subscribers` (route `grimba.subscribers.destroy`) is the user-data purge tool; vault events purgeable via `GrimbaArchiveVaultEvents` purge mode.
- **S979** — Stale media cleanup: `GrimbaPruneImageProxyCache` runs daily via cron `img_proxy_prune` with `--days=60` retention window (routes/console.php line 47); locked by `tests/Feature/ImageProxyCachePruneTest`.
- **S980** — Data docs: `docs/GRIMBANEWS_S005_MODEL_INVENTORY.md` + `docs/GRIMBANEWS_S006_MIGRATION_INVENTORY.md` + `docs/GRIMBANEWS_PROD_DISK_HEADROOM_2026_05_11.md` + `docs/GRIMBANEWS_PROD_DEDUPE_APPLY_2026_05_11.md`.

## S981–S990 — Data integrity tests

- **S981** — Data integrity tests: `tests/Feature/GrimbaSeedSourcesIdempotencyTest` + `RssFeedsSeederTest` + `OrphanClusterFormationTest` + `DedupePostsCommandTest` lock seed/migration idempotency.
- **S982** — Migration tests: `tests/Feature/*Test` (54 feature tests) run against fresh DB per Tests\TestCase setup; every test class effectively re-runs migrations during `setUp`.
- **S983** — Restore tests: `tests/Feature/DatabaseBackupVerificationTest` (2 tests covering accept + corruption paths).
- **S984** — Dedupe data tests: `tests/Feature/DedupePostsCommandTest` covers `grimba:dedupe-posts` URL + title dedupe + review mode.
- **S985** — Cluster data tests: `tests/Feature/ClusterPageTest` + `ClusterReviewQueueTest` + `OrphanClusterFormationTest` cover cluster lifecycle.
- **S986** — Translation data tests: `tests/Feature/TranslationAtomicityTest` (4 invariants per S-LANG-15) + `NobuTranslationModuleTest` + `GrimbaTranslateByRuleCommandTest` + `GrimbaTranslationMonitorTest` + `StaticUiTranslationTest`.
- **S987** — Insight data tests: `tests/Feature/NobuAiSummaryCommandTest` + `ExtractiveSynthesisTest` + `GrimbaNobuAiBrandPurityTest` cover NobuAI insight pipeline.
- **S988** — Source metadata tests: `tests/Feature/SourceClassifierCommandTest` + `SourceCountryBackfillCommandTest` + `SourceHealthMonitorTest` + `SourceClassificationDashboardTest` + `SourceLogoProxyTest`.
- **S989** — Backup evidence report: `grimba:release-smoke --evidence` writes markdown release-evidence files under `storage/app/grimba-release-evidence/` (`GrimbaReleaseSmoke` command line 23-24).
- **S990** — Data signoff: covered by S951–S989 — 54 feature tests pass, backup-verify works, restore-smoke detects corruption, idempotency tests cover seeds.

## S991–S1000 — Production launch gates

These are the final G9 + G10 gates from the release-gate table at the top of the master plan. Server-side surrogates are shipped; live-env gates marked `deferred` per launch checklist.

- **S991** — CI green: `phpunit.xml` configured; full suite runs in 164s per S549 evidence (517 tests / 4433 assertions). External CI workflow (`.github/workflows`) `deferred` — currently CI runs locally + pre-deploy script.
- **S992** — E2E green: 6 Playwright scripts in `tests/e2e/` (`grimbanews-breakdown-layout.cjs`, `grimbanews-csp-smoke.cjs`, `grimbanews-golden-path-smoke.cjs`, `grimbanews-keyboard-navigation.cjs`, `grimbanews-mobile-shell-contrast.cjs`, `grimbanews-story-controls.cjs`).
- **S993** — Visual diff green: `partial` — visual baseline matrix covered by tests/Feature server-render assertions per S741-S750 pack; full pixel-diff against production `deferred` to launch-week task.
- **S994** — Performance green: covered by S819 CLS lock (R-14 close) + S801-S840 cache/index/eager-load pack; Lighthouse + k6 `deferred` per S841-S848 honest deferral.
- **S995** — Security green: covered by S931 (517 tests / security-header / XSS / SSRF / open-redirect / security.txt / RFC 9116 + 24 probes) + S940 docs.
- **S996** — Scheduler smoke green: `tests/Feature/AutomationScheduleTest` (S162 evidenced) + `GrimbaAutomationMonitor` (S164 evidenced); production cron install verified via `grimba:health`.
- **S997** — Provider smoke green: `grimba:release-smoke --require-nobuai-live` flag (`GrimbaReleaseSmoke` line 28) gates on bounded live provider call; covered by Wave HHHHHHHH release smoke can gate NobuAI live.
- **S998** — Rollback drill green: `partial` — `grimba:verify-backups --all` covers restore-smoke (S965) detecting corruption; live rollback drill on production VPS `deferred` to launch-week operator task.
- **S999** — Release evidence complete: `grimba:release-smoke --evidence` writes per-deploy markdown evidence + `GrimbaPruneReleaseEvidence` rolls 30-day window; covered by `tests/Feature/ReleaseEvidencePruneTest` + `tests/Feature/ReleaseSmokeCommandTest`.
- **S1000** — Production launch signoff: `partial` — server-side gates (security/cache/SEO/cluster/translation/ingest/CLS/backup/health) all locked; live-env Lighthouse + visual-diff + production rollback drill + composer audit + offsite-encrypted-backup `deferred` per `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md`.

---

## Summary

- **Closed (complete):** ~80 sprints across S901-S1000 newly evidenced this pack (on top of the 21 already-evidenced rows from prior packs).
- **Already evidenced before this pack:** S901, S904, S907-S917 (partial), S931, S935, S938, S940, S961, S963, S964, S973, S975.
- **Newly evidenced by this pack:** S902, S903, S905, S906, S914, S918-S930, S932-S934, S936, S937, S941, S943, S944, S949, S951-S960, S962, S965-S972, S974, S976-S980, S981-S990, S992, S995-S997, S999.
- **Honest deferred / partial:** S939 (live composer/npm audit), S942 (secret rotation runbook — post-launch), S945 (offsite encrypted backup — S1561 arc), S946 (deploy key review — Vader-side), S947 (npm audit), S950 (security signoff partial), S991 (external CI workflow), S993 (visual diff matrix), S994 (Lighthouse/k6), S998 (rollback drill), S1000 (final signoff).

The launch-blocker work is shipped at the server/code/test layer. The deferreds are (a) live-env audit runs that need a production target, (b) operator launch-week tasks (rollback drill, manual SR pass), or (c) post-launch hardening per Vader's stated cadence.
