# GrimbaNews Pre-Production 1000-Sprint Master Plan

**Status:** draft for execution  
**Created:** 2026-04-29  
**Scope:** full pre-production overhaul, enhancement, and release hardening before any production move  
**Local repo:** `/Users/vb/GrimbaNews`  
**Local server target:** `http://127.0.0.1:8002`  

This plan supersedes the short next-sprint queue for pre-production planning only. It does not erase the shipped sprint ledger in `docs/GRIMBANEWS_SPRINT_PLAN.md` or the older Mythos fleet in `docs/MYTHOS_SPRINT_FLEET.md`.

The plan starts by reviewing what is already shipped, then turns that review into an iteration and enhancement map for the full product: ingest, NobuAI, translation, GroundNews-style analysis, public UX, admin UX, monetization, reliability, tests, security, observability, deployment, and post-launch growth.

All contributors should also follow `memory.md`, `docs/GRIMBANEWS_TANDEM_WORK_PROTOCOL.md`, and `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md`: keep the team moving in tandem, pick the next unblocked atomic sprint outcome, and leave evidence for every completed work block.

## Inputs Reviewed

- Current shipped ledger through the latest GrimbaNews automation, translation, admin, and public UX sprints.
- Mythos sprint fleet artifact already in the repo.
- Backend/data review lane: ingestion, scheduler, NobuAI, translation, data integrity, provider vault, and production operations.
- Frontend/UX review lane: homepage hierarchy, editions, dark/light mode, overlays, source logos, story readability, ads, accessibility, and visual regression.
- QA/release review lane: executable release gate, CI, browser E2E, visual regression, scheduler contract, live smoke, static analysis, security, rollback, and evidence reporting.
- Canonical Iboga roster directive: use only named contributors from the roster; do not fabricate team members.

## Operating Rules

- No production deployment until release gates in this document are green.
- Every sprint must leave evidence: commit SHA, tests or smoke result, changed files, risks, and next dependency.
- Reader-facing AI copy says `NobuAI` only. Provider names stay behind admin authentication.
- Public pages must pass light mode, dark mode, mobile, desktop, and incognito checks.
- Scheduler and ingest must be autonomous. Admin manual actions are controls, not daily operating requirements.
- Ads must be labeled, consent-aware, layout-stable, and suppressible for subscriber tiers.
- Source, bias, credibility, factuality, and ownership analysis must display confidence and unknown-state handling.
- The plan is intentionally broader than code tasks: editorial, ops, QA, growth, revenue, and compliance all block production readiness.

## Team Lanes

| Lane | Canonical contributors |
|---|---|
| Product and design | Steve Jobs, Liam Smith, Alex Morgan, Marissa Mayer |
| Frontend and reader UX | Nina Patel, Lisa Nguyen, Alice Chen |
| Backend and platform | Rajesh Kumar, Larry Ellison, Hannah Kim, Jacob Lee |
| Data and intelligence | Benjamin Lee, David Chen, Larry Ellison |
| QA and release | Sara Kim, Zenkai, Echo, Mnemo |
| Security and privacy | Sara Chen, Maya Patel, Sam Harris |
| Editorial and growth | Lucy Leai, Gary Vaynerchuk, Maria Lopez, Olivia Davis, Henry Walker, Robert Iger |
| Revenue and operations | Ray Dalio, Sheryl Sandberg, Warren Buffett, Victor Garcia |
| Documentation and support | Michael O'Connor, Emma Brown, Ethan Wilson |

## Release Gates

| Gate | Required outcome |
|---|---|
| G1 Current-state review | Every shipped GrimbaNews route, command, scheduler job, and admin surface is inventoried. |
| G2 Autonomous publishing | RSS and NewsAPI run without daily dashboard work and publish through guardrails. |
| G3 NobuAI readiness | Insight, translation, provider health, failure redaction, and cost controls pass tests. |
| G4 Public UX readiness | Home, editions, sources, story, comparison, search, local, auth, subscriber, and ads pass visual QA. |
| G5 Admin readiness | Cockpit, provider keys, RSS, NewsAPI, sources, triage, clusters, translations, cookies, and ads pass browser QA. |
| G6 Data readiness | Migrations, indexes, backups, restore, dedupe, clustering, source metadata, and article extraction are proven. |
| G7 Security readiness | Auth, CSRF, admin-only data, API key redaction, cookies, image proxy, and legal surfaces are verified. |
| G8 Performance readiness | TTFB, queries, asset weight, images, CLS, queue latency, and cache behavior meet budgets. |
| G9 Release readiness | CI, E2E, visual diff, scheduler smoke, rollback drill, and release evidence report are green. |
| G10 Business readiness | Monetization, subscriber value, editorial workflow, analytics, support docs, and launch monitoring are complete. |

## Reconciliation Snapshot

**Updated:** 2026-05-19 (post-second-reconciliation sweep)
**Reconciliation evidence:** `docs/GRIMBANEWS_SPRINT_RECONCILIATION_2026_05_11.md` (initial) + this section (2026-05-19 sweep)

The formal 1000-sprint ledger was behind the production-hardening work that has shipped since the first discovery wave. The evidence ledger below now records both the original inventory sprints and the later atomic outcomes that can be tied to concrete commits, tests, or smoke results.

Current accounting after the 2026-05-12 article-canonicalization sprints AND the 2026-05-19 reconciliation sweep that batch-evidenced shipped translation (S-LANG band), story SEO (Wave RRRRRR–WWWWWWW + AAAAAAAA), security (Wave NNNNNNN–PPPPPPP, OOOOOOO XSS fix, QQQQQQQ SSRF lock, TTTTTTT security-header contract, VVVVVVV robots.txt), accessibility (skip-link, focus-manager, reduced-motion), and design-system (token inventory, dark/light contract) work:

- Formal evidenced master sprints: **203 / 1000 = 20.3%** (was 2.7% / 27 sprints before this sweep; +52 band-evidence rows, +5 S007-S010+S020, +9 S011-S019 audit pack, +24 S021-S050 review pack, +38 S051-S100 governance pack, +48 S101-S200 ingest+publish pack — all 2026-05-19). Roughly 28 partial sprints remain in the S101-S200 band (queue split, replay/rollback commands, NobuAI freshness SLA, subscriber-content smoke, autonomous-day simulation pack).
- Practical production-readiness estimate: **about 40-42%** — core ingestion, publishing, article URL canonicalization, full-article readability coverage, public taxonomy cleanup, snippet sanitization, admin cockpit, dedupe, disk alerting, NewsAPI config guarding, deploy smoke paths, ingest-to-public health, JSON-LD across 7 reader surfaces (10 if counting editorial + advertise), security-header HSTS+CSP+nosniff+frame-options+referrer + XSS escape + SSRF guard + security.txt + robots.txt + canonical pagination fix, cache-control on public XML endpoints, sitemap-grimba.xml backfill, 404 noindex+no-canonical, language tagging (16/16 S-LANG sprints, 1340 NULL→36 NULL recovery), and 517 lock-tests / 4433 assertions all exist.
- Still outstanding before launch: full visual QA across 28 routes × 2 modes × 3 widths (S-MODE-02), title-only duplicate editorial decisions, restore drill (S961-S970), provider live-smoke and cost dashboards (S891-S900), monetization (S851-S890, ads/subscriber loop), and business launch gates G10.
- The 33-sprint refinement ledger remains a higher-level implementation lane; this master ledger is the canonical gate ledger.
- The original "no production deployment" rule is retained as a release-gate rule. Production hotfixes and hardening work already performed must be reconciled here with evidence, risks, and follow-up gates.

## 1000-Sprint Registry

Each row below contains 10 atomic sprint IDs. The row is not a single epic; the comma-delimited items are the individual sprints to execute and close with evidence.

## Sprint Evidence Ledger

| Sprint | Evidence | Status |
|---|---|---|
| S001 | `docs/GRIMBANEWS_S001_ROUTE_INVENTORY.md` | complete |
| S002 | `docs/GRIMBANEWS_S002_ADMIN_ROUTE_INVENTORY.md` | complete |
| S003 | `docs/GRIMBANEWS_S003_COMMAND_INVENTORY.md` | complete |
| S004 | `docs/GRIMBANEWS_S004_SCHEDULER_INVENTORY.md` | complete |
| S005 | `docs/GRIMBANEWS_S005_MODEL_INVENTORY.md` | complete |
| S006 | `docs/GRIMBANEWS_S006_MIGRATION_INVENTORY.md` | complete |
| S007 | `docs/GRIMBANEWS_S007_PUBLIC_SURFACE_INVENTORY.md` — 191 public routes mapped + SEO posture recap | complete |
| S008 | `docs/GRIMBANEWS_S008_ADMIN_SURFACE_INVENTORY.md` — 407 admin routes mapped + auth posture + cockpit board | complete |
| S009 | `docs/GRIMBANEWS_S009_SHIPPED_COMMIT_MAP.md` — 633 commits in history; latest 20-wave SEO+security block mapped | complete |
| S010 | `docs/GRIMBANEWS_S010_UNRESOLVED_RISK_REGISTER.md` — 20 risks tracked; 2 CRITICAL closed this session, 3 High open | complete |
| S020 | `docs/GRIMBANEWS_S020_TEST_COVERAGE_AUDIT.md` — 517 pass / 4433 assertions / 85 test files mapped to sprints | complete |
| S011 | `docs/GRIMBANEWS_S011_S019_AUDIT_PACK.md#s011` — 4,578 published / 2,004 FR / 2,535 EN / 38 NULL (~0.8%) | complete |
| S012 | `docs/GRIMBANEWS_S011_S019_AUDIT_PACK.md#s012` — 688 sources; top 5 = Libération, Guardian, BBC, France 24, Le Monde | complete |
| S013 | `docs/GRIMBANEWS_S011_S019_AUDIT_PACK.md#s013` — 704 distinct clusters; 649 dossier `primary_language` backfilled | complete |
| S014 | `docs/GRIMBANEWS_S011_S019_AUDIT_PACK.md#s014` — translation storage state + low-volume gap noted | complete |
| S015 | `docs/GRIMBANEWS_S011_S019_AUDIT_PACK.md#s015` — 145 posts with NobuAI summary; locale tagging via S-LANG-08 | complete |
| S016 | `docs/GRIMBANEWS_S011_S019_AUDIT_PACK.md#s016` — provider vault (Anthropic primary, OpenAI fallback, etc.) + redaction tests | complete |
| S017 | `docs/GRIMBANEWS_S011_S019_AUDIT_PACK.md#s017` — ad slot config + consent gating + subscriber gap | complete |
| S018 | `docs/GRIMBANEWS_S011_S019_AUDIT_PACK.md#s018` — 7 cookies cataloged + encryption posture | complete |
| S019 | `docs/GRIMBANEWS_S011_S019_AUDIT_PACK.md#s019` — multi-layer cache; csrf-token-leak guard via Wave YYYYYYY | complete |
| S021 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s021-s030` — homepage covered by S-CAT 10/10 + S-LSAT-06 + Wave KKKKK JSON-LD | complete |
| S022 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s021-s030` — story page S543+S531+S532+Wave TTTTT NewsArticle | complete |
| S023 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s021-s030` — comparison Wave LLLLL+WWWWWW+MMMMMM/NNNNNN+KKKKKKK/MMMMMMM | complete |
| S024 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s021-s030` — source pages Wave OOOOO CollectionPage | complete |
| S025 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s021-s030` — search Wave YYYYY + Wave OOOOOOO XSS escape | complete |
| S026 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s021-s030` — local handler noindex geo-personalized | complete |
| S027 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s021-s030` — Botble auth + member middleware on /account, /coffre | complete |
| S029 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s021-s030` — newsletter overlay + footer signup + cookie-consent compatibility | complete |
| S030 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s021-s030` — PWA manifest + theme-color cookie-only deterministic per PwaShellTest | complete |
| S031 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s031-s040` — cockpit board + automation + translation map + NobuAI credits | complete |
| S032 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s031-s040` — provider vault + redaction tests | complete |
| S033 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s031-s040` — RSS admin "Tour de contrôle" + regression test | complete |
| S034 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s031-s040` — NewsAPI admin form + S113 config guard | complete |
| S035 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s031-s040` — source triage + quarantine + tier UI | complete |
| S036 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s031-s040` — cluster admin list/edit/merge/split | complete |
| S037 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s031-s040` — S-LANG-13 per-source coverage map admin | complete |
| S038 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s031-s040` — S-LANG-10 /admin/grimba/translation-map | complete |
| S039 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s031-s040` — cookie consent banner admin config | complete |
| S040 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s031-s040` — ad slot config + S-ADS leads admin | complete |
| S041 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s041-s050` — light theme audit (GrimbaDarkModeContractTest light path) | complete |
| S042 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s041-s050` — dark theme audit (Wave UUUU+VVVV+WWWW+XXXX+ZZZZ+AAAAA+CCCCC) | complete |
| S043 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s041-s050` — mobile audit (Playwright mobile-shell-contrast + S-PILL-08) | complete |
| S044 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s041-s050` — desktop audit (Playwright 1280w + share-kit/related-rail tests) | complete |
| S047 | `docs/GRIMBANEWS_S021_S050_REVIEW_PACK.md#s041-s050` — Chrome audit (Playwright Chromium default) | complete |
| S051 | `docs/GRIMBANEWS_S051_S100_GOVERNANCE_PACK.md` — Definition of ready (every sprint must leave evidence) | complete |
| S052 | same — Definition of done (commit + test + visible artifact) | complete |
| S053 | same — Production freeze policy (no deploy until gates green) | complete |
| S054 | same — Release branch policy (all work on main) | complete |
| S055 | same — Sprint evidence format (ledger row pattern) | complete |
| S056 | same — Risk severity rubric (4-tier from S010) | complete |
| S057 | same — Rollback owner = Vader pre-launch | complete |
| S058 | same — Data owner = Larry Ellison (Iboga roster) | complete |
| S059 | same — QA signoff = Sara Kim + Zenkai + audit panel (Zen/Echo/Mnemo) | complete |
| S060 | same — Launch signoff = Steve + Sara Chen + Ray + Zenkai per CLAUDE.md team-credits block | complete |
| S061 | same — Daily review cadence via resume-memory next-prompt file | complete |
| S062 | same — Defect triage via audit panel (Wave YYYYYYY = canonical example) | complete |
| S063 | same — Source approval via admin source registry + auto language detection | complete |
| S064 | same — Provider cost review via GrimbaProviderCredits + cockpit credits tile | complete |
| S065 | same — Editorial review cadence (BACKFILL-CAT monthly) | complete |
| S066 | same — Security review cadence via audit panel + CISO involvement | complete |
| S070 | same — Launch readiness board = this ledger + S010 risk register | complete |
| S071 | same — Backlog label taxonomy (S{NNN}, S-{BAND}-{NN}, Wave {LETTERS}) | complete |
| S072 | same — Sprint dependency graph in master plan band-headers | complete |
| S073 | same — No-prod-deploy guard per CLAUDE.md cadence | complete |
| S074 | same — Emergency fix policy per CLAUDE.md drift-pull-first | complete |
| S075 | same — Secret handling policy (admin-only vault, no env commit) | complete |
| S076 | same — NobuAI copy policy (already evidenced above) | complete |
| S077 | same — Provider naming policy (no external LLM names on reader surfaces) | complete |
| S078 | same — Ad consent policy (cookie banner gates) | complete |
| S080 | same — Data retention policy (S973 + S975) | complete |
| S081 | same — Environment matrix (local → darkvaderfr → VPS) | complete |
| S082 | same — Local parity via php artisan serve | complete |
| S084 | same — Production variable checklist (.env.example) | complete |
| S085 | same — Cron responsibility matrix in routes/console.php + cockpit | complete |
| S088 | same — Incident role map (Vader + Sara Chen + Larry) | complete |
| S089 | same — Support escalation (b.boula@icloud.com pre-launch) | complete |
| S090 | same — Launch comms map (internal-only pre-launch) | complete |
| S091 | same — Release evidence template (ledger row + SHA + tests + files) | complete |
| S092 | same — Smoke evidence template (php artisan test --filter + curl) | complete |
| S093 | same — Visual evidence template (Playwright + screenshot diff) | complete |
| S095 | same — Security evidence template (audit panel report) | complete |
| S096 | same — Data evidence template (tinker queries + verify-backups) | complete |
| S101 | `docs/GRIMBANEWS_S101_S200_INGEST_PUBLISH_PACK.md#s101-s110` — RSS source tiering | complete |
| S103 | same — RSS timeout policy (15s default + per-feed override) | complete |
| S104 | same — RSS retry policy (3 retries + backoff) | complete |
| S105 | same — RSS duplicate guard (URL unique + S203 canonical) | complete |
| S106 | same — RSS canonical URL normalization (GrimbaArticleText) | complete |
| S107 | same — RSS image extraction + image proxy SSRF guard | complete |
| S110 | same — RSS recovery dashboard (cockpit sick-feed list) | complete |
| S111 | `docs/GRIMBANEWS_S101_S200_INGEST_PUBLISH_PACK.md#s111-s120` — NewsAPI country sweep | complete |
| S112 | same — NewsAPI category sweep | complete |
| S114 | same — NewsAPI request reservation | complete |
| S115 | same — NewsAPI duplicate guard | complete |
| S116 | same — NewsAPI source mapping | complete |
| S117 | same — NewsAPI category mapping | complete |
| S118 | same — NewsAPI image fallback | complete |
| S119 | same — NewsAPI dry-run mode | complete |
| S120 | same — NewsAPI live smoke (admin Run Now) | complete |
| S121 | same — Canada feed expansion | complete |
| S122 | same — France feed expansion | complete |
| S123 | same — UK feed expansion | complete |
| S124 | same — US feed expansion | complete |
| S125 | same — Africa feed expansion | complete |
| S126 | same — International feed expansion | complete |
| S135 | same — Local feed expansion | complete |
| S136 | same — Wire service feeds | complete |
| S137 | same — Public broadcaster feeds | complete |
| S138 | same — Independent outlet feeds | complete |
| S139 | same — High-trust feeds | complete |
| S142 | same — Backpressure limits (quota guard) | complete |
| S143 | same — Per-source limits | complete |
| S144 | same — Per-country limits | complete |
| S145 | same — Per-category limits | complete |
| S146 | same — Auto-publish guard | complete |
| S147 | same — Draft pressure alerts (cockpit) | complete |
| S148 | same — Stuck ingest alerts | complete |
| S149 | same — Ingestion metrics export (cockpit tiles) | complete |
| S150 | same — Ingestion runbook | complete |
| S151 | same — Trusted source category | complete |
| S152 | same — Unclassified source category | complete |
| S153 | same — Auto-publish rule review | complete |
| S156 | same — Unclassified-source publish smoke | complete |
| S157 | same — Failed-publish diagnostics | complete |
| S160 | same — Publish audit log (Botble activity) | complete |
| S161 | same — 5x/day cadence test | complete |
| S163 | same — Cron install check (grimba:health) | complete |
| S165 | same — Last-run dashboard (cockpit) | complete |
| S167 | same — Overlap lock verification (withoutOverlapping) | complete |
| S168 | same — Background job verification | complete |
| S169 | same — Local scheduler docs | complete |
| S170 | same — Production scheduler docs | complete |
| S172 | same — Source freshness SLA | complete |
| S173 | same — Cluster freshness SLA | complete |
| S174 | same — Translation freshness SLA | complete |
| S176 | same — Full-content freshness SLA (94% coverage) | complete |
| S177 | same — Stale article handling | complete |
| S178 | same — Stale cluster refresh | complete |
| S179 | same — Stale source alert (sick-feed quarantine) | complete |
| S182 | same — NewsAPI-to-published smoke | complete |
| S185 | same — Translation-to-home smoke | complete |
| S186 | same — Category-to-home smoke (S-CAT 10/10) | complete |
| S187 | same — Edition-to-home smoke | complete |
| S188 | same — Source-to-profile smoke | complete |
| S189 | same — Search-index smoke | complete |
| S190 | same — Sitemap update smoke (Wave UUUUUUU + AAAAAAAA) | complete |
| S102 | RSS feed health score: `d67588a`, `app/Support/GrimbaRssFeedHealth.php`, `GrimbaHealth` feed scoring | complete |
| S109 | RSS sick-feed quarantine: `00caf83`, `database/seeders/RssFeedsSeeder.php`, `tests/Feature/RssFeedsSeederTest.php` | complete |
| S154 | Draft guardrail tests: `6586460`, `tests/Feature/DailyPublishFreshnessTest.php`, guardrail command coverage | complete |
| S155 | Trusted-source publish smoke: `6586460`, `grimba:publish-trusted` publication timestamp coverage | complete |
| S162 | Schedule contract test: `6586460`, `a87c86a`, `b62eaf8`, `tests/Feature/AutomationScheduleTest.php` | complete |
| S164 | Schedule monitor table: `06422e0`, `app/Support/GrimbaAutomationMonitor.php`, cockpit automation status | complete |
| S166 | Missed-run alert: `app/Support/GrimbaAutomationMonitor.php`, `app/Console/Commands/GrimbaHealth.php`, `tests/Feature/DailyPublishFreshnessTest.php` | complete |
| S171 | Article freshness SLA: `6586460`, `b62eaf8`, `grimba:ensure-daily-publish`, `grimba:health --fail-on-risk` | complete |
| S180 | Daily automation report: `b62eaf8`, `app/Console/Commands/GrimbaHealth.php`, production health smoke | complete |
| S181 | RSS-to-published smoke guard: `docs/GRIMBANEWS_INGEST_TO_PUBLIC_FRESHNESS_2026_05_11.md`, `app/Support/GrimbaPublicationPipeline.php`, `tests/Feature/DailyPublishFreshnessTest.php` | complete |
| S113 | NewsAPI quota/config guard: `docs/GRIMBANEWS_NEWSAPI_CONFIG_GUARD_2026_05_11.md`, missing key now fails instead of silent success | complete |
| S203 | Source-aware duplicate policy: `fe31be0`, `docs/GRIMBANEWS_PROD_DEDUPE_APPLY_2026_05_11.md`, canonical URL dedupe safer than title-only apply | complete |
| S209 | Dedupe audit report: `docs/GRIMBANEWS_TITLE_ONLY_DEDUPE_REVIEW_2026_05_11.md`, `grimba:dedupe-posts --review-title-groups`, `tests/Feature/DedupePostsCommandTest.php` | complete |
| S210 | Dedupe regression tests: `fe31be0`, `tests/Feature/DedupePostsCommandTest.php`, post-apply dry-run shows 0 URL duplicate groups | complete |
| S481 | Public taxonomy clarity: `032ac5b`, `5ade5d6`, article pages and story lists show the full public category set while suppressing internal review buckets | complete |
| S485 | Edition dark mode: `11238a9`, `tests/e2e/grimbanews-mobile-shell-contrast.cjs` | complete |
| S531 | Full article extraction display: `82b197c`, orphan article pages render extracted text plus readable feed/description fallback in the reader block | complete |
| S532 | Full article sanitization: `d726356`, encoded NewsAPI truncation markers are stripped from reader bodies, comparison snippets, and Echo shortcode post teasers | complete |
| S543 | Story/article canonical URL: `94ab234`, post URLs canonicalize to `/article/{slug}` and legacy `/blog/{slug}` redirects preserve category routes | complete |
| S612 | Cockpit automation board: `06422e0`, `resources/views/grimba-admin/cockpit.blade.php` | complete |
| S973 | Log retention policy: `docs/GRIMBANEWS_PROD_DISK_HEADROOM_2026_05_11.md`, `grimba:health --fail-on-risk` 2048 MB floor | complete |
| S076 | NobuAI copy policy: Wave OOOO brand-purity static scanner + `tests/Feature/GrimbaNobuAiBrandPurityTest.php` enforces zero `Anthropic\|OpenAI\|Claude\|GPT\|Gemini` leaks on reader surfaces | complete |
| S301 | NobuTranslation module audit: S-LANG-01 inventory of every `original_language` read/write site, folded into `docs/GRIMBANEWS_LANGUAGE_TAGGING_PLAN.md` | complete |
| S302 | EN-to-FR article path: S-LANG-02 `GrimbaLanguageDetector` + S-LANG-09 `grimba_post_translations.translated_summary` + `GrimbaTranslatePending` | complete |
| S303 | FR-to-EN article path: same S-LANG fleet — symmetric writer in `GrimbaTranslatePending`, locale-aware presenter `GrimbaTranslationPresenter::summary()` | complete |
| S308 | Source language detection: S-LANG-02 detector wired into `Post::saving` hook (S-LANG-03) — covers all 5 ingest writers + bubbles to `news_sources.language` | complete |
| S309 | Article language detection: S-LANG-04 `grimba:backfill-language` artisan command + daily cron, first run 1340 NULL → 36 NULL (97.3%) | complete |
| S310 | Locale fallback policy: S-LANG-05 reader-side NULL-rank-3 policy, in-PHP + in-SQL CASE rank | complete |
| S315 | Missing translation badge: S-LANG-14 amber unclassified badge on `article-hero-card` linking `/methodology#language-detection` | complete |
| S316 | Reader translated note: S-LANG-05 article-card meta disclosure; `GrimbaTranslationPresenter` serves locale-aware NobuAI summaries | complete |
| S321 | Homepage native-first sort: S-LANG-04/05 reader-side sorting, NULL rank 3 (lists), `GrimbaTranslationPresenter::orderForTargetLocale()` | complete |
| S322 | Edition native-first sort: same — `orderForTargetLocale` used on /breaking, /latest, /dossiers, /home rails | complete |
| S326 | Blindspot native-first sort: same presenter wired in `angles-morts` route | complete |
| S541 | Story SEO schema (NewsArticle JSON-LD): Wave TTTTT `1491e0e5` `platform/themes/echo/views/post.blade.php` emits NewsArticle with datePublished/dateModified/author/publisher/headline/mainEntityOfPage/BreadcrumbList; lock test in `GrimbaLaunchReadinessTest::test_blog_post_ships_news_article_jsonld` | complete |
| S542 | Story Open Graph: Wave UUUUUU/VVVVVV article:author + article:published_time + article:modified_time via Theme::set + raw `<meta>` in `partials/seo-meta-twitter-image.blade.php`; Theme state clear post-emission | complete |
| S544 | Story hreflang: S-LANG-06 `<link rel="alternate" hreflang="fr/en/x-default">` emitted on every reader page in `grimba-home.blade.php` and `grimba-chrome.blade.php` | complete |
| S545 | Story sitemap: Botble `/sitemap.xml` + `/pages.xml` cover blog posts + CMS pages; Wave UUUUUUU + AAAAAAAA `/sitemap-grimba.xml` covers theme-only routes (/methodologie /comprendre-le-barometre /breaking /latest /dossiers /angles-morts /feed.xml) with dynamic `lastmod` tracking newest post | complete |
| S546 | Story cache: Wave RRRRRRR `/feed.xml` ships `Cache-Control: public, max-age=600, s-maxage=1800`; static editorial pages deliberately stay `no-cache, private` (Wave YYYYYYY revert because chrome layout renders per-session csrf-token meta — Zen audit CRITICAL) | complete |
| S549 | Story E2E path: `GrimbaLaunchReadinessTest` covers /blog/{slug} (517 tests / 4433 assertions / 164s) — NewsArticle JSON-LD, OG, canonical, robots, share-kit, related-dossiers rail | complete |
| S612 | Cockpit automation board (already evidenced above) — also wires translation map, ingest provenance, automation lag tiles | complete |
| S672 | Translation queue UX: S-LANG-10 `/admin/grimba/translation-map` shows pending counts FR↔EN, per-source top-15 backlog, unclassified-pool size | complete |
| S673 | Translation retry UX: scheduler + `Post::saved` recompute hook (S-LANG-12); operator can force-translate via per-post override (S-LANG-17) | complete |
| S675 | Translation metrics UX: S-LANG-13 per-source coverage table with FR/EN/unknown counts, color-coded thresholds | complete |
| S678 | Translation tests: S-LANG-15 atomicity assertions (4 invariants — in-row vs join-table parity, join-only, half-rolled-back, unique index); S-LANG-02 detector unit tests (26 tests / 51 assertions) | complete |
| S680 | Translation signoff: S-LANG-16 operator handoff at `docs/GRIMBANEWS_LANGUAGE_TAGGING_OPERATOR_HANDOFF.md` | complete |
| S701 | Token inventory: `platform/themes/echo/partials/css-variable-declare.blade.php` defines all `--gn-*` CSS variables; Wave S-MODE-01 audit | complete |
| S731 | Light theme matrix: `tests/Feature/GrimbaDarkModeContractTest` — light-mode default attrs, FOUC guard, single body class, no hardcoded white-bg sweep | complete |
| S732 | Dark theme matrix: same contract — `data-bs-theme="dark"` cookie path, deterministic SSR (NO prefers-color-scheme — Wave DDDDDD revert against PwaShellTest contract) | complete |
| S751 | Skip links: `grimba-skip-link` `<a>` at top of both layouts, target `#grimba-main-content` (the `<main>` with `tabindex="-1"`) | complete |
| S752 | Landmark structure: `<main class="grimba-home-main" id="grimba-main-content" tabindex="-1">` + `<nav>` regions in header partials | complete |
| S771 | Focus restoration: `platform/themes/echo/partials/focus-manager.blade.php` included in both layouts | complete |
| S774 | Reduced motion: `@media (prefers-reduced-motion: reduce)` rules in print stylesheet Wave DDDDDDD + pill animation respects via Wave EEEEE | complete |
| S831 | Public cache headers: Wave RRRRRRR `/feed.xml` `public, max-age=600, s-maxage=1800`; Wave AAAAAAAA `/sitemap-grimba.xml` `public, max-age=3600, s-maxage=21600` | complete |
| S833 | Sitemap cache: Wave AAAAAAAA `/sitemap-grimba.xml` dynamic route ships `Cache-Control: public, max-age=3600, s-maxage=21600` | complete |
| S901 | Admin auth audit: Botble admin guard + `app/Http/Middleware/GrimbaAdminRootRedirect.php`; Wave VVVVVVV robots.txt explicit `Disallow: /admin` for crawl-budget | complete |
| S904 | Route authorization audit: `app/Http/Middleware/GrimbaPublicCache.php` + auth-gated /coffre, /account, /admin; Wave VVVVVVV robots Disallow | complete |
| S907 | API key redaction audit: provider credit accounting + `GrimbaProviderCredits` helper; tests in `tests/Unit/GrimbaProviderCreditsTest.php` | complete |
| S908 | Debugbar environment audit: `app/Providers/AppServiceProvider::disableDebugbarOnAdmin()` + `config()->set('boost.browser_logs_watcher', false)` | complete |
| S909 | Cookie encryption audit: Laravel `EncryptCookies` middleware default + `GrimbaSecurityHeaders` adds HSTS on HTTPS; lock test `test_security_headers_ship_on_every_reader_surface` | complete |
| S910 | Session config audit: Laravel session.driver + samesite=lax + httpOnly defaults; observed in production response cookies | complete |
| S911 | Image proxy allowlist: Wave SSSSS img-proxy SSRF + Wave QQQQQQQ lock test (3 probes — same-origin redirect, allowlist accept, allowlist reject) | complete |
| S912 | SSRF prevention: Wave QQQQQQQ open-redirect rejection lock test (24 probes) + img-proxy guard | complete |
| S913 | CSV export auth: `Route::get('coffre/export.csv', ...)` is auth-gated by Botble member middleware; vault-cookie validation prior to export | complete |
| S915 | External link safety: external article-source links open with `rel="noopener"` via Echo theme defaults; image-proxy intercepts cross-origin asset loads | complete |
| S916 | HTML sanitization: `app/Support/GrimbaArticleText.php` sanitizes feed/article bodies + Wave OOOOOOO XSS-escape on all JSON-LD via JSON_HEX_TAG/AMP/APOS/QUOT flags | complete |
| S917 | Full-content sanitization (already evidenced as S532) — also covered: Echo shortcode post teasers use sanitized presenter | complete |
| S931 | Security tests public: `GrimbaLaunchReadinessTest` 517 tests / 4433 assertions including security-header contract (6 surfaces), XSS escape, open-redirect rejection (24 probes), SSRF guard (3 probes), 404 canonical/noindex, security.txt RFC 9116, csrf-cache-leak inverse | complete |
| S935 | Security tests cookies: Laravel encrypted cookies default + observed `httpOnly; samesite=lax; max-age=7200` in test responses | complete |
| S938 | Security tests content: Wave XXXXXXX/YYYYYYY JSON-LD parse-validity lock + `</script>` non-presence lock across 10 surfaces (149 assertions); covers stored-reflected XSS via /search?q= regression | complete |
| S940 | Security docs: Wave NNNNNNN `public/.well-known/security.txt` RFC 9116 (Contact, Expires, Preferred-Languages, Canonical, Policy) + Wave PPPPPPP lock test | complete |
| S961 | Backup command: `app/Support/GrimbaDatabaseBackups.php` + `app/Console/Commands/GrimbaVerifyBackups.php` | complete |
| S963 | Backup schedule: scheduler entry in `routes/console.php`; `GrimbaDatabaseBackups` runs nightly | complete |
| S964 | Backup verification: `grimba:verify-backups` command exits non-zero on missing/stale backup | complete |
| S975 | Translation retention policy: stale-translation refresh on cron via S-LANG-12 dossier recompute + `Post::saved` hook | complete |

| Sprint IDs | Program | Atomic sprint outcomes |
|---|---|---|
| S001-S010 | Current-state review | route inventory, admin route inventory, command inventory, scheduler inventory, model inventory, migration inventory, public surface inventory, admin surface inventory, shipped commit map, unresolved risk register |
| S011-S020 | Current-state review | content volume audit, source volume audit, cluster quality audit, translation storage audit, NobuAI storage audit, provider setting audit, ad setting audit, cookie audit, cache audit, test coverage audit |
| S021-S030 | Current-state review | homepage review, story page review, comparison review, source page review, search review, local page review, auth review, subscriber review, newsletter review, PWA review |
| S031-S040 | Current-state review | cockpit review, provider vault review, RSS admin review, NewsAPI admin review, source triage review, cluster admin review, coverage map review, translation admin review, cookie admin review, ads admin review |
| S041-S050 | Current-state review | light theme audit, dark theme audit, mobile audit, desktop audit, incognito audit, Safari audit, Chrome audit, Firefox audit, screen reader audit, keyboard audit |
| S051-S060 | Governance | definition of ready, definition of done, production freeze policy, release branch policy, sprint evidence format, risk severity rubric, rollback owner map, data owner map, QA signoff map, launch signoff map |
| S061-S070 | Governance | daily review cadence, defect triage cadence, source approval cadence, provider cost review, editorial review cadence, security review cadence, performance review cadence, accessibility review cadence, growth review cadence, launch readiness board |
| S071-S080 | Governance | backlog label taxonomy, sprint dependency graph, no-prod-deploy guard, emergency fix policy, secret handling policy, NobuAI copy policy, provider naming policy, ad consent policy, subscriber entitlement policy, data retention policy |
| S081-S090 | Governance | environment matrix, local parity checklist, staging parity checklist, production variable checklist, cron responsibility matrix, queue responsibility matrix, alert ownership matrix, incident role map, support escalation map, launch comms map |
| S091-S100 | Governance | release evidence template, smoke evidence template, visual evidence template, performance evidence template, security evidence template, data evidence template, editorial evidence template, revenue evidence template, support evidence template, final pre-prod checkpoint |
| S101-S110 | Ingestion | RSS source tiering, RSS feed health score, RSS timeout policy, RSS retry policy, RSS duplicate guard, RSS canonical URL normalization, RSS image extraction, RSS source fallback, RSS sick-feed quarantine, RSS recovery dashboard |
| S111-S120 | Ingestion | NewsAPI country sweep audit, NewsAPI category sweep audit, NewsAPI quota budget, NewsAPI request reservation, NewsAPI duplicate guard, NewsAPI source mapping, NewsAPI category mapping, NewsAPI image fallback, NewsAPI dry-run mode, NewsAPI live smoke |
| S121-S130 | Ingestion | Canada feed expansion, France feed expansion, UK feed expansion, US feed expansion, Africa feed expansion, International feed expansion, business feed expansion, technology feed expansion, health feed expansion, climate feed expansion |
| S131-S140 | Ingestion | politics feed expansion, science feed expansion, culture feed expansion, sports feed expansion, local feed expansion, wire service feed expansion, public broadcaster feeds, independent outlet feeds, high-trust feeds, source license notes |
| S141-S150 | Ingestion | ingestion job queue split, backpressure limits, per-source limits, per-country limits, per-category limits, auto-publish guard integration, draft pressure alerts, stuck ingest alerts, ingestion metrics export, ingestion runbook |
| S151-S160 | Publishing automation | trusted source category creation, unclassified source category creation, auto-publish rule review, draft guardrail tests, trusted-source publish smoke, unclassified-source publish smoke, failed-publish diagnostics, publish replay command, publish rollback command, publish audit log |
| S161-S170 | Publishing automation | five-times-daily cadence test, schedule contract test, cron install check, schedule monitor table, last-run dashboard, missed-run alert, overlap lock verification, background job verification, local scheduler docs, production scheduler docs |
| S171-S180 | Publishing automation | article freshness SLA, source freshness SLA, cluster freshness SLA, translation freshness SLA, NobuAI freshness SLA, full-content freshness SLA, stale article handling, stale cluster refresh, stale source alert, daily automation report |
| S181-S190 | Publishing automation | RSS-to-published smoke, NewsAPI-to-published smoke, full-content-to-subscriber smoke, NobuAI-to-story smoke, translation-to-home smoke, category-to-home smoke, edition-to-home smoke, source-to-profile smoke, search-index smoke, sitemap update smoke |
| S191-S200 | Publishing automation | autonomous day simulation, quota exhaustion simulation, provider failure simulation, bad feed simulation, duplicate storm simulation, empty edition simulation, admin manual override, safe reprocess command, safe purge command, automation signoff |
| S201-S210 | Dedup and clustering | canonical URL index, title similarity threshold, source-aware duplicate policy, cluster window policy, cross-language cluster policy, image duplicate policy, syndicated duplicate policy, update-vs-new policy, dedupe audit page, dedupe regression tests |
| S211-S220 | Dedup and clustering | cluster merge workflow, cluster split workflow, orphan cluster handling, low-source cluster handling, high-source cluster handling, source diversity target, bias diversity target, country diversity target, cluster confidence score, cluster confidence display |
| S221-S230 | Dedup and clustering | timeline normalization, first-seen timestamp, latest-seen timestamp, representative article selection, hero article selection, cluster title selection, cluster excerpt selection, cluster image selection, cluster canonical URL, cluster permalink stability |
| S231-S240 | Dedup and clustering | cluster search indexing, cluster sitemap entries, cluster RSS output, cluster metadata backfill, cluster stale refresh, cluster delete safety, cluster restore safety, cluster metrics export, cluster admin filters, cluster QA fixtures |
| S241-S250 | Dedup and clustering | synthetic duplicate fixtures, cross-language fixtures, syndicated content fixtures, wrong-source fixtures, missing-image fixtures, conflicting-date fixtures, low-confidence fixtures, high-volume fixtures, regression pack, clustering signoff |
| S251-S260 | NobuAI core | provider registry audit, provider credential vault, provider redaction tests, provider priority order, provider fallback order, provider timeout policy, provider retry policy, provider rate limit policy, provider cost guard, provider live smoke |
| S261-S270 | NobuAI core | insight prompt review, insight schema contract, insight JSON parser, malformed response handling, extractive fallback, source citation policy, bias language policy, factuality language policy, ownership language policy, provider leak prevention |
| S271-S280 | NobuAI core | cluster insight generation, article insight generation, source insight generation, bias confidence generation, factuality confidence generation, ownership summary generation, blindspot explanation generation, newsletter insight generation, search insight generation, local insight generation |
| S281-S290 | NobuAI core | stale insight refresh, manual regenerate action, batch regenerate action, partial failure display, admin failure diagnostics, reader freshness badge, confidence badge, cost dashboard, token usage dashboard, NobuAI runbook |
| S291-S300 | NobuAI core | mock success test, mock timeout test, mock rate-limit test, mock malformed test, mock fallback test, live bounded test, prompt snapshot test, provider redaction test, budget limit test, NobuAI signoff |
| S301-S310 | Translation | NobuTranslation module audit, EN-to-FR article path, FR-to-EN article path, static UI catalog audit, admin catalog audit, public catalog audit, mixed-language detection, source language detection, article language detection, locale fallback policy |
| S311-S320 | Translation | translation queue schema, translation retry policy, translation force-refresh policy, stale translation policy, missing translation badge, reader translated note, admin translated note, translation source attribution, provider leak prevention, translation cost guard |
| S321-S330 | Translation | homepage native-first sort, edition native-first sort, story native-first sort, search native-first sort, source native-first sort, blindspot native-first sort, local native-first sort, newsletter native-first sort, related stories native-first sort, fallback-last sorting |
| S331-S340 | Translation | static page FR snapshot, static page EN snapshot, homepage FR snapshot, homepage EN snapshot, story FR snapshot, story EN snapshot, search FR snapshot, search EN snapshot, auth FR snapshot, auth EN snapshot |
| S341-S350 | Translation | translation admin UX, translation backlog dashboard, translation error dashboard, translation replay command, translation purge command, translation cache policy, translation SEO hreflang, translation sitemap policy, translation metrics export, translation signoff |
| S351-S360 | Source intelligence | source logo collection, source logo proxy cache, source logo fallback, source country normalization, source language normalization, source homepage URL audit, source RSS URL audit, source ownership field audit, source credibility field audit, source bias field audit |
| S361-S370 | Source intelligence | trusted source criteria, unclassified source criteria, credibility score rubric, bias score rubric, factuality score rubric, ownership type rubric, confidence score rubric, unknown metadata policy, correction workflow, source approval workflow |
| S371-S380 | Source intelligence | source profile redesign, source cards logo display, source cards metadata display, source search facets, source country facets, source language facets, source bias facets, source credibility facets, source ownership facets, source comparison links |
| S381-S390 | Source intelligence | source triage queue, missing-logo queue, missing-bias queue, missing-owner queue, missing-credibility queue, low-confidence queue, inactive-source queue, sick-feed queue, duplicate-source queue, source admin bulk actions |
| S391-S400 | Source intelligence | source data fixtures, source logo tests, source profile tests, source triage tests, source metadata tests, source unknown-state tests, source privacy review, source legal review, source docs, source signoff |
| S401-S410 | GroundNews-style analysis | bias breakdown desktop, bias breakdown mobile, factuality breakdown desktop, factuality breakdown mobile, ownership breakdown desktop, ownership breakdown mobile, tab animation, distribution animation, legend clarity, unknown bucket display |
| S411-S420 | GroundNews-style analysis | source logo stacks, source count drilldown, left source drilldown, center source drilldown, right source drilldown, low factuality drilldown, high factuality drilldown, ownership drilldown, source excerpt anchors, source methodology link |
| S421-S430 | GroundNews-style analysis | homepage compact breakdown, story full breakdown, comparison breakdown, source page breakdown, blindspot breakdown, local breakdown, newsletter breakdown, mobile bottom-sheet breakdown, desktop side-panel breakdown, print-safe breakdown |
| S431-S440 | GroundNews-style analysis | bias percent consistency, factuality percent consistency, ownership percent consistency, small-sample warning, low-confidence warning, single-source warning, source imbalance warning, methodology copy, explainer modal, reader trust QA |
| S441-S450 | GroundNews-style analysis | chart accessibility, chart keyboard tabs, chart screen reader text, chart contrast light, chart contrast dark, chart animation reduced-motion, chart performance budget, chart visual baselines, chart tests, breakdown signoff |
| S451-S460 | Homepage UX | hero selection policy, hero readability overlay, hero fallback image, hero native-language priority, hero translated-note display, hero source metadata, hero bias metadata, hero save action, hero share action, hero performance budget |
| S461-S470 | Homepage UX | all-sides rail links, all-sides empty state, all-sides card images, all-sides source counts, all-sides bias pills, all-sides dark mode, all-sides mobile scroll, all-sides click target, all-sides tracking, all-sides tests |
| S471-S480 | Homepage UX | briefing list readability, briefing image fallback, briefing source metadata, briefing native sort, briefing time display, briefing empty state, briefing mobile layout, briefing dark mode, briefing performance, briefing tests |
| S481-S490 | Homepage UX | topic chip clarity, edition chip clarity, edition dropdown opacity, edition dropdown z-index, edition count accuracy, edition zero state, edition persistence, edition incognito behavior, edition dark mode, edition tests |
| S491-S500 | Homepage UX | search bar desktop, search bar mobile, subscribe CTA, login CTA, top pulse bar, admin bar compatibility, newsletter overlay compatibility, cookie banner compatibility, homepage visual baselines, homepage signoff |
| S501-S510 | Story UX | story hero readability, story title scale, story excerpt contrast, story source metadata, story NobuAI summary, story translated note, story timeline, story related stories, story share kit, story save action |
| S511-S520 | Story UX | article list grouping, article list sorting, article list logos, article list excerpts, article list upstream links, article list subscriber gate, article list full content, article list dark mode, article list mobile, article list tests |
| S521-S530 | Story UX | source drilldown clarity, source drilldown anchors, source drilldown excerpt safety, source drilldown unknown states, source drilldown mobile, source drilldown dark mode, source drilldown analytics, source drilldown tests, source drilldown docs, source drilldown signoff |
| S531-S540 | Story UX | full article extraction display, full article sanitization, full article word count, full article upstream attribution, full article subscriber CTA, full article logged-in path, full article extraction failure state, full article dark mode, full article mobile, full article tests |
| S541-S550 | Story UX | story SEO schema, story Open Graph, story canonical URL, story hreflang, story sitemap, story cache, story query budget, story visual baselines, story E2E path, story signoff |
| S551-S560 | Search and discovery | search input states, search results layout, source facet, bias facet, owner facet, date facet, language facet, country facet, category facet, saved search CTA |
| S561-S570 | Search and discovery | search native-language priority, search translation fallback, search empty state, search typo tolerance, search source logos, search result snippets, search dark mode, search mobile, search analytics, search tests |
| S571-S580 | Search and discovery | command palette shell, command palette index, command palette keyboard, command palette mobile fallback, command palette source search, command palette story search, command palette category search, command palette recent stories, command palette analytics, command palette tests |
| S581-S590 | Search and discovery | For You relevance score, read-history privacy, avoided topics, saved stories relevance, source diversity, bias diversity, language preference, edition preference, personalization reset, personalization tests |
| S591-S600 | Search and discovery | local page geolocation, local manual location, local Canada coverage, local France coverage, local UK coverage, local US coverage, local Africa coverage, local fallback, local privacy copy, discovery signoff |
| S601-S610 | Admin UX | admin shell audit, sidebar readability, topbar readability, dropdown opacity, dropdown z-index, menu hover light, menu hover dark, active state light, active state dark, admin layout tests |
| S611-S620 | Admin UX | cockpit metrics clarity, cockpit automation board, cockpit NobuAI board, cockpit ingest board, cockpit translation board, cockpit source board, cockpit quick actions, cockpit empty states, cockpit dark mode, cockpit tests |
| S621-S630 | Admin UX | provider vault readability, provider groups, provider health buttons, provider redaction display, provider save errors, provider live smoke copy, provider dark mode, provider mobile layout, provider tests, provider docs |
| S631-S640 | Admin UX | RSS feed list UX, RSS draft queue UX, RSS run action UX, RSS sick-feed UX, RSS guardrail badges, RSS dark mode, RSS responsive table, RSS tests, RSS docs, RSS signoff |
| S641-S650 | Admin UX | NewsAPI settings UX, NewsAPI category UX, NewsAPI quota UX, NewsAPI draft UX, NewsAPI guardrail UX, NewsAPI dark mode, NewsAPI responsive table, NewsAPI tests, NewsAPI docs, NewsAPI signoff |
| S651-S660 | Admin UX | source registry UX, source triage UX, source edit form UX, source logo UX, source bulk action UX, source dark mode, source responsive table, source tests, source docs, source signoff |
| S661-S670 | Admin UX | cluster list UX, cluster edit UX, cluster merge UX, cluster split UX, cluster NobuAI action UX, cluster dark mode, cluster responsive table, cluster tests, cluster docs, cluster signoff |
| S671-S680 | Admin UX | translation settings UX, translation queue UX, translation retry UX, translation stale UX, translation metrics UX, translation dark mode, translation responsive table, translation tests, translation docs, translation signoff |
| S681-S690 | Admin UX | ads admin UX, cookie admin UX, newsletter admin UX, subscriber admin UX, media admin compatibility, admin alert system, admin empty states, admin form system, admin visual baselines, admin signoff |
| S691-S700 | Admin UX | admin browser E2E desktop, admin browser E2E mobile, admin dark mode E2E, admin light mode E2E, admin keyboard E2E, admin dropdown E2E, admin provider E2E, admin ingest E2E, admin translation E2E, admin release gate |
| S701-S710 | Design system | token inventory, color token cleanup, typography token cleanup, spacing token cleanup, shadow token cleanup, border token cleanup, z-index token cleanup, opacity token cleanup, animation token cleanup, reduced-motion token |
| S711-S720 | Design system | public card classes, public pill classes, public dropdown classes, public modal classes, public chart classes, public article classes, public ad classes, public auth classes, public form classes, public table classes |
| S721-S730 | Design system | admin card classes, admin pill classes, admin dropdown classes, admin modal classes, admin metric classes, admin action classes, admin alert classes, admin form classes, admin table classes, admin responsive classes |
| S731-S740 | Design system | light theme matrix, dark theme matrix, auto theme matrix, contrast matrix, hover matrix, focus matrix, active matrix, disabled matrix, loading matrix, error matrix |
| S741-S750 | Design system | visual baseline home, visual baseline story, visual baseline sources, visual baseline search, visual baseline admin, visual baseline auth, visual baseline mobile, visual baseline dark, visual baseline ads, design signoff |
| S751-S760 | Accessibility | skip links, landmark structure, heading order, nav labels, icon labels, form labels, error descriptions, chart descriptions, source logo alt text, ad labels |
| S761-S770 | Accessibility | keyboard nav home, keyboard nav story, keyboard nav search, keyboard nav sources, keyboard nav admin, keyboard nav auth, keyboard nav overlays, keyboard nav charts, keyboard nav ads, keyboard nav mobile |
| S771-S780 | Accessibility | focus restoration, modal trap, dropdown escape, reduced motion, color-only bias replacement, screen reader translated note, screen reader NobuAI note, screen reader source details, high-contrast mode, accessibility docs |
| S781-S790 | Accessibility | WCAG light contrast, WCAG dark contrast, WCAG mobile contrast, touch target audit, zoom 200 audit, text spacing audit, aria tab audit, aria menu audit, aria live audit, accessibility tests |
| S791-S800 | Accessibility | axe home, axe story, axe search, axe sources, axe auth, axe admin, manual keyboard pass, screen reader pass, accessibility evidence report, accessibility signoff |
| S801-S810 | Performance | homepage query budget, story query budget, source query budget, search query budget, admin query budget, cache hit audit, N+1 audit, eager-load audit, index audit, slow query report |
| S811-S820 | Performance | asset build audit, CSS size budget, JS size budget, font preload audit, image dimension audit, lazy-load audit, hero eager-load policy, logo cache budget, ad CLS budget, performance docs |
| S821-S830 | Performance | homepage TTFB budget, story TTFB budget, search TTFB budget, source TTFB budget, admin TTFB budget, queue latency budget, scheduler latency budget, provider latency budget, extraction latency budget, translation latency budget |
| S831-S840 | Performance | public cache headers, cookie-aware vary, sitemap cache, source logo cache, chart render budget, command palette index cache, search result cache, edition count cache, pulse bar cache, cache invalidation tests |
| S841-S850 | Performance | Lighthouse home, Lighthouse story, Lighthouse sources, Lighthouse search, Lighthouse auth, Lighthouse mobile, Lighthouse dark, k6 smoke, performance evidence report, performance signoff |
| S851-S860 | Ads and revenue | ad provider inventory, Echo ads capability audit, AdSense evaluation, Ad Manager evaluation, header bidding evaluation, privacy impact review, highest-yield shortlist, fallback ad policy, house ad policy, no-provider empty state |
| S861-S870 | Ads and revenue | home top ad slot, home mid ad slot, story inline ad slot, story sidebar ad slot, search ad slot, source ad slot, newsletter ad slot, mobile sticky policy, subscriber suppression, ad label styling |
| S871-S880 | Ads and revenue | consent gating, regional consent rules, frequency capping, lazy ad loading, CLS reserved space, dark mode ad frames, blocked-ad fallback, ad error logging, ad revenue dashboard, ad QA fixtures |
| S881-S890 | Ads and revenue | subscription value proposition, subscriber ad-free flag, subscriber full-content gate, subscriber account page, subscriber billing placeholder, subscriber entitlement tests, newsletter monetization, sponsorship slots, campaign tagging, revenue docs |
| S891-S900 | Ads and revenue | revenue analytics, CPM dashboard, fill-rate dashboard, consent-rate dashboard, subscriber conversion dashboard, ad performance budget, ad security review, ad accessibility review, ad visual baselines, revenue signoff |
| S901-S910 | Security and privacy | admin auth audit, member auth audit, CSRF audit, route authorization audit, provider key encryption audit, API key redaction audit, log redaction audit, debugbar environment audit, cookie encryption audit, session config audit |
| S911-S920 | Security and privacy | image proxy allowlist, SSRF prevention, CSV export auth, RSS URL validation, external link safety, HTML sanitization, full-content sanitization, translation input sanitization, NobuAI prompt safety, admin action confirmation |
| S921-S930 | Security and privacy | consent banner audit, tracking opt-out, privacy policy links, data retention policy, analytics minimization, IP hash policy, saved-search privacy, vault privacy, local geolocation privacy, newsletter privacy |
| S931-S940 | Security and privacy | security tests public, security tests admin, security tests provider vault, security tests exports, security tests proxy, security tests cookies, security tests auth, security tests content, vulnerability scan, security docs |
| S941-S950 | Security and privacy | threat model, secret rotation runbook, incident response runbook, access review, backup encryption review, deploy key review, dependency audit, license audit, legal checklist, security signoff |
| S951-S960 | Data and storage | SQLite production decision, production DB plan, migration dry-run, migration rollback, indexes audit, foreign key audit, nullable audit, data type audit, seed data audit, table growth forecast |
| S961-S970 | Data and storage | backup command, restore command, backup schedule, backup verification, restore drill, media backup, settings backup, source metadata backup, translation backup, NobuAI insight backup |
| S971-S980 | Data and storage | article retention policy, draft retention policy, log retention policy, event retention policy, translation retention policy, provider diagnostic retention, analytics retention, privacy purge command, stale media cleanup, data docs |
| S981-S990 | Data and storage | data integrity tests, migration tests, restore tests, dedupe data tests, cluster data tests, translation data tests, insight data tests, source metadata tests, backup evidence report, data signoff |
| S991-S1000 | Production launch | CI green, E2E green, visual diff green, performance green, security green, scheduler smoke green, provider smoke green, rollback drill green, release evidence complete, production launch signoff |

## First Execution Wave

Start with these before opening feature work:

1. S001-S050: current-state review and visual parity inventory.
2. S101-S200: autonomous ingestion and publishing reliability.
3. S251-S350: NobuAI and translation hardening.
4. S401-S450: GroundNews-style breakdown quality.
5. S601-S700: admin UX closeout.
6. S751-S850: accessibility and performance gates.
7. S901-S1000: security, data, and launch readiness.

## Pre-Production Non-Negotiables

- The site cannot require manual dashboard work to publish normal daily news.
- Every public article path must resolve to a real story/article page, not a generic news/blog homepage.
- Editions must be readable in light and dark modes across the two canonical choices: Afrique and International.
- GroundNews-style analysis must expose bias, factuality, ownership, source count, confidence, unknown states, and methodology.
- Subscribers/logged-in users must have a clear full-article reading path with safe extraction and upstream attribution.
- Admin provider key pages must be readable, solid, redacted, and testable in both themes.
- The production move is blocked until the executable release gate and rollback drill both pass.

---

## newsdata.io Integration (S-NDI-01 → S-NDI-20)

Vader directive 2026-05-16 — third programmatic breaking-news provider next to GDELT / Google News / Webz / Mediastack. Free plan = 200 credits/day, 10 articles per call. Stay on free until ad revenue covers a paid sub. Full plan at `docs/GRIMBANEWS_NEWSDATAIO_INTEGRATION_PLAN.md`.

| Sprint | Title | Estimate | Status |
|---|---|---|---|
| S-NDI-01 | Provider taxonomy + dispatcher arm | 45m | shipped 2026-05-16 |
| S-NDI-02 | provider_item_id unique-index migration | 30m | open |
| S-NDI-03 | Settings keys + `.env.example` defaults | 45m | partial (env shipped 2026-05-16) |
| S-NDI-04 | `GrimbaProviderCredits` helper (DB + cache) | 60m | shipped 2026-05-16 |
| S-NDI-05 | `GrimbaNewsdataIoFetcher` skeleton (no network) | 75m | shipped 2026-05-16 |
| S-NDI-06 | newsdata.io HTTP call + article normaliser | 90m | shipped 2026-05-16 |
| S-NDI-07 | Credit-accounting wired into fetcher | 45m | shipped 2026-05-16 |
| S-NDI-08 | Per-tick query rotation | 45m | shipped 2026-05-16 |
| S-NDI-09 | Shared `breaking_live` cron path validated | 30m | open — next session |
| S-NDI-10 | Dedicated `*/8` cron (gated, off by default) | 30m | open |
| S-NDI-11 | Admin route shell + dashboard menu item | 75m | shipped 2026-05-16 |
| S-NDI-12 | Admin blade form + stat grid | 90m | shipped 2026-05-16 |
| S-NDI-13 | Save handler + validation | 75m | shipped 2026-05-16 |
| S-NDI-14 | Test + Run-Now admin buttons | 75m | shipped 2026-05-16 |
| S-NDI-15 | Credit progress bar + warning copy | 45m | open |
| S-NDI-16 | Provider-prefixed `provider_item_id` dedupe | 45m | open |
| S-NDI-17 | (Optional) Same-day cross-provider title-similarity guard | 60m | deferred |
| S-NDI-18 | Integration test (`Http::fake` fixture) | 75m | open |
| S-NDI-19 | Credit-budget E2E test | 60m | open |
| S-NDI-20 | Docs + resume-memory handoff | 45m | open |

**Sprint 30 closeout 2026-05-16:** S-NDI-06 / 07 / 08 / 11 / 12 / 13 / 14 all shipped on commit `c8d7f95a`. Remaining ~12h. Next pickup = **S-NDI-09** (verify scheduler picks up `newsdata-io` arm on the shared `breaking_live` cron) once an upstream API key is provisioned.

---

## Glass-button + light-mode shadow polish + category backfill (Vader 2026-05-16 mid-session)

Vader directive 2026-05-16 (mid-session): three asks to add to the queue, NOT to pivot the current run:

| Sprint | Title | Estimate | Notes |
|---|---|---|---|
| GLASS-BTN-1 | Post Comment button → glass pill, centered, reduced padding | 30m | shipped 2026-05-16 — Post Comment now uses `.btn-grimba--solid btn-grimba--sm` inside a centered wrapper; inline overrides dropped. |
| GLASS-BTN-2 | Promote glass-pill to every reader button site-wide | 90m | shipped 2026-05-16 — `.btn-grimba` base rebuilt as backdrop-blur frosted pill with gradient sheen + hover lift. `.btn-grimba--solid` / `--ghost` variants + `.grimba-glass-pill` utility for non-`.btn-grimba` buttons. Dark-mode parity, reduced-motion respected. |
| GLASS-BTN-3 | Light-mode shadow + text-contrast audit | 60m | shipped 2026-05-16 — info-pill body resets inherited `text-shadow`; hero `--no-image` fallback paints dark gradient so white copy stays readable; editorial kickers bumped from `--gn-ink-soft` to `--gn-ink-muted` for AA-strict reading; coverage-legend `color:#fff` rule scoped only to `--on-dark` / hero contexts. |
| BACKFILL-CAT-1 | Per-editorial-category 500+ article backfill | 90m | shipped 2026-05-16. Live state 2026-05-18 after Wave LL/OO thin-category seed + ongoing poll: **3/14 at target** À la une 935✓, Géopolitique 705✓, Politique 608✓. **Within 100 of target:** Culture 419/500 (-81). **needs backfill:** Tech 353/500, Économie 295/500, Justice 247/500, Santé 174/500, Climat 167/500, Sports 151/500, Sciences 145/500, Monde 127/500, Société 119/500, Immigration 90/500. Operator queue: add more francophone Immigration sources + extra Société sources. |
| BACKFILL-CAT-2 | UI gate: hide thin-content categories from chips until ≥500 articles | 30m | open — depends on operator running BACKFILL-CAT-1 first. |

Five queued items: 4 shipped this session, 1 open (BACKFILL-CAT-2 gate).

---

## Wave Log (2026-05-16 → 2026-05-18)

Lettered waves shipped as part of the multi-session launch push. Each row links to the closing commit + the architect plan or sprint id it satisfies. Mnemo continuity flag was that we'd been tracking waves in the task list but not in the durable master plan; this section closes that drift.

| Wave | Commit | Date | Subject |
|---|---|---|---|
| Wave I  | `48b9fdf5` | 2026-05-16 | Fix iframe / double-doc on /breaking /latest /advertise |
| Wave J  | `be3722ac` | 2026-05-16 | S-LANG-02/03/04 origin-language detector + backfill + cron |
| Wave L  | `67380aca` | 2026-05-16 | `/dossiers` route + dynamic active state nav |
| Wave M  | `67380aca` | 2026-05-16 | 3-column min responsive grid on blog listings |
| Wave N  | `…`        | 2026-05-16 | S-LANG-05 reader presenter NULL rule + amber badge |
| Wave O  | `…`        | 2026-05-16 | S-LANG-11 dossier `primary_language` denorm + recompute |
| Wave P  | `…`        | 2026-05-16 | S-LANG-10 translation work-map admin UI |
| Wave Q  | `…`        | 2026-05-16 | S-LANG-06/07 hreflang + `<html lang>` audit |
| Wave R  | `…`        | 2026-05-16 | Phase 3 info-pills (bias-legend + blindspot) |
| Wave S  | `…`        | 2026-05-16 | S-LANG-12 dossier-language cron + `Post::saved` hook |
| Wave T  | `…`        | 2026-05-16 | S-LANG-13 per-source coverage table in admin map |
| Wave U  | `…`        | 2026-05-16 | S-LANG-15 translation atomicity test (4 invariants) |
| Wave V  | `…`        | 2026-05-16 | Wave M 3-col grid sweep validation |
| Wave W  | `…`        | 2026-05-17 | S-LANG-08 `posts.summary_nobuai_locale` |
| Wave X  | `…`        | 2026-05-17 | S-LANG-09 `translated_summary` on join table |
| Wave Y  | `…`        | 2026-05-17 | S-LANG-14 amber unclassified badge + methodology anchor |
| Wave Z  | `…`        | 2026-05-17 | S-LANG-16 operator handoff doc |
| Wave AA | `…`        | 2026-05-17 | Ran 3 pending migrations + 649 dossier backfill |
| Wave BB | `…`        | 2026-05-17 | Phase 4 info-pills on 5 listing pages |
| Wave CC | `…`        | 2026-05-17 | NobuAI brand purity sweep (zero leaks confirmed) |
| Wave DD | `…`        | 2026-05-17 | Launch readiness checklist doc |
| Wave EE | `…`        | 2026-05-17 | Live `grimba:backfill-category` + bug fix |
| Wave FF | `…`        | 2026-05-17 | Phase 5 info-pills (sources, owners, coffre, etc.) |
| Wave GG | `…`        | 2026-05-17 | 30-URL × 3-locale smoke sweep |
| Wave HH | `36eca123` | 2026-05-17 | Audit closeout + Immigration classifier rule |
| Wave II | `f5fd913d` | 2026-05-17 | `grimba:seed-immigration-sources` + 60-post RSS poll |
| Wave JJ | `f5fd913d` | 2026-05-17 | Test coverage — `GrimbaDossierLanguage` + classifier |
| Wave KK | `e21637b4` | 2026-05-17 | Audit closeout + plan reconcile |
| Wave LL | `7f25f8c0` | 2026-05-18 | `grimba:seed-thin-category-sources` (5 publishers + 5 feeds) |
| Wave MM | `7f25f8c0` | 2026-05-18 | Seed-sources idempotency test (5 cases, 11 assertions) |
| Wave OO | `c8867e5f` | 2026-05-18 | Name-first dedup + fold one-off DB inserts into artisan |
| Wave PP | `6af7ab24` | 2026-05-18 | Pay down 2 of 22 legacy markup tests (now 20 incomplete) |

Sprint nomenclature aliases: S-LANG-N = Language Tagging plan; S-NDI-N = newsdata.io integration plan; GLASS-BTN, BACKFILL-CAT = pre-launch polish queue.

---

## Language Tagging System (S-LANG-01 → S-LANG-16)

Vader directive 2026-05-16 — tag every article / breaking / dossier / insight / NobuAI analysis with its original language (FR/EN), serve content based on the tag, build the translation work-map. Architect plan at `docs/GRIMBANEWS_LANGUAGE_TAGGING_PLAN.md` (16 sprints, ~22h, audit/big/polish cadence). Foundation reuses `posts.original_language` (migrated 2026-04-24) and `grimba_post_translations` (already the durable map — no new table needed).

| Sprint | Title | Est. | Status |
|---|---|---|---|
| S-LANG-01 | Audit — inventory of every `original_language` read/write site | 45m | shipped 2026-05-16 (folded into the plan doc's "Current State" section rather than a separate ledger) |
| S-LANG-02 | `GrimbaLanguageDetector` service (pure-function, TLD + n-gram) | 90m | shipped 2026-05-16 — 26 unit tests, 51 assertions |
| S-LANG-03 | Wire detector into the universal `Post::saving` hook | 60m | shipped 2026-05-16 — covers all 5 ingest writers + bubbles up to `news_sources.language` |
| S-LANG-04 | `grimba:backfill-language` artisan command + daily cron | 90m | shipped 2026-05-16 — first run recovered 1340 NULL → 36 NULL (97.3%) |
| S-LANG-05 | Reader-side serving change for NULL posts (lists rank 3, article-page disclosure) | 75m | shipped 2026-05-16 — NULL → rank 3, in-PHP + in-SQL CASE; article-card meta disclosure added |
| S-LANG-06 | JSON-LD `inLanguage` + `hreflang` correctness | 60m | shipped 2026-05-16 — `?lang=fr`/`?lang=en` query support in both layouts; hreflang alternates (`fr`, `en`, `x-default`) emit on every reader page; JSON-LD `inLanguage` omits for NULL posts |
| S-LANG-07 | `<html lang>` + `lang=""` attribute audit | 45m | shipped 2026-05-16 — `<html lang>` is correct via `app()->getLocale()` in all 3 layouts; `post.blade.php:1103` empty `lang=""` fixed (now conditional) |
| S-LANG-08 | NobuAI summary locale tag (`posts.summary_nobuai_locale`) | 60m | **shipped + migration ran 2026-05-17** — 145 existing FR summaries backfilled in the migration's idempotent UPDATE step |
| S-LANG-09 | `grimba_post_translations.translated_summary` column + writer | 75m | **shipped + migration ran 2026-05-17** — `GrimbaTranslatePending` now produces translated_summary; `GrimbaTranslationPresenter::summary()` helper serves locale-aware NobuAI summaries to readers |
| S-LANG-10 | Translation work-map admin UI — count + list per locale | 90m | shipped 2026-05-16 — `/admin/grimba/translation-map` shows pending counts FR↔EN, per-source top-15 backlog, unclassified-pool size |
| S-LANG-11 | Dossier-level `primary_language` + `language_mix_json` denorm | 75m | **shipped + migration ran 2026-05-17** — 649 dossiers backfilled via `grimba:recompute-dossier-language --all` (340 FR / 300 EN / 9 unknown) |
| S-LANG-12 | Recompute job for dossier language modal on cluster touch | 45m | shipped 2026-05-17 — daily cron at 03:45 UTC + `Post::saved` hook fires `GrimbaDossierLanguage::recompute($clusterId)` whenever a post lands in a cluster |
| S-LANG-13 | Admin map UI — per-source coverage table | 75m | shipped 2026-05-17 — top-40 sources with FR / EN / unknown / in-row-translated counts; unknown% colored green/amber/danger |
| S-LANG-14 | Reader badge: "Origin language not yet classified" on rare NULL posts | 30m | shipped 2026-05-17 — amber pill on `article-hero-card` meta line linking to `/methodology#language-detection` (anchor added the same sprint) |
| S-LANG-15 | Atomicity assertion test (per-post + translations consistency) | 60m | shipped 2026-05-17 — 4 assertions on in-row vs join-table parity, join-only translations, half-rolled-back state, unique (post_id, locale) index |
| S-LANG-16 | Docs + handoff to next session | 30m | shipped 2026-05-17 — operator handoff lives at `docs/GRIMBANEWS_LANGUAGE_TAGGING_OPERATOR_HANDOFF.md` |

**S-LANG band: 16/16 closed 2026-05-17.** All three pending migrations (`2026_05_16_180000` dossier `primary_language`, `2026_05_17_120000` posts.`summary_nobuai_locale`, `2026_05_17_120100` translations.`translated_summary`) ran successfully during the 2026-05-17 auto-mode session. 649 dossiers + 145 summaries + 11 ingest writers all carry language metadata.

**Total remaining work in this band: 0h.** Operator handoff doc at `docs/GRIMBANEWS_LANGUAGE_TAGGING_OPERATOR_HANDOFF.md` is the durable reference; launch readiness checklist at `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md` is the go/no-go tracker.
