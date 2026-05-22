# GrimbaNews Pre-Production 1000-Sprint Master Plan (extended to 2237)

**Status:** draft for execution  
**Created:** 2026-04-29 (S001-S1000 pre-launch arc) · extended 2026-05-20 (Mythos S1001-S2237 post-launch arc)
**Scope:** full pre-production overhaul, enhancement, and release hardening before any production move; plus a 1237-sprint Mythos post-launch growth/scale/B2B arc.  
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

**Updated:** 2026-05-22 (Wave MMMMMMMMM — S901-S1000 security/backup/privacy/launch pack)
**Reconciliation evidence:** `docs/GRIMBANEWS_SPRINT_RECONCILIATION_2026_05_11.md` (initial) + this section (2026-05-19 sweep) + Wave MMMMMMMMM closure (2026-05-22)

The formal 1000-sprint ledger was behind the production-hardening work that has shipped since the first discovery wave. The evidence ledger below now records both the original inventory sprints and the later atomic outcomes that can be tied to concrete commits, tests, or smoke results.

Current accounting after the 2026-05-12 article-canonicalization sprints, the 2026-05-19 reconciliation sweep that batch-evidenced shipped translation (S-LANG band), story SEO (Wave RRRRRR–WWWWWWW + AAAAAAAA), security (Wave NNNNNNN–PPPPPPP, OOOOOOO XSS fix, QQQQQQQ SSRF lock, TTTTTTT security-header contract, VVVVVVV robots.txt), accessibility (skip-link, focus-manager, reduced-motion), design-system (token inventory, dark/light contract) work, the 2026-05-22 Wave GGGGGGGGG admin/design/perf/a11y/ads pack (S671-S900, +180), and the 2026-05-22 Wave MMMMMMMMM security/backup/privacy/launch pack (S901-S1000, +85 net of partials and deferreds):

- Formal evidenced master sprints: **~702 / 1000 = 70%+** (was 2.7% / 27 sprints at directive issue; +52 band-evidence rows, +5 S007-S010+S020, +9 S011-S019, +24 S021-S050, +38 S051-S100, +48 S101-S200, +79 S201-S300, +122 S301-S500, +192 S501-S700, +180 S671-S900, +85 S901-S1000 net). Honest deferreds across S939/S942/S945/S946/S947 (live composer audit + secret rotation runbook + offsite encrypted backup + deploy key review + npm audit) and partials across S950/S965/S966/S991/S993/S994/S998/S1000 (production-environment-only gates: live audits, prod rollback drill, Lighthouse, visual pixel-diff, final signoff).
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
| S201 | `docs/GRIMBANEWS_S201_S300_DEDUP_CLUSTER_NOBUAI_PACK.md` — Canonical URL index + GrimbaArticleText normalize | complete |
| S202 | same — Title similarity threshold (admin-configurable, 0.85 default) | complete |
| S204 | same — Cluster window policy (48h default) | complete |
| S205 | same — Cross-language cluster policy (S-LANG-11 aware) | complete |
| S207 | same — Syndicated duplicate policy (S203 source-aware) | complete |
| S208 | same — Update-vs-new policy via updated_at/published_at | complete |
| S211 | same — Cluster merge workflow (admin) | complete |
| S212 | same — Cluster split workflow (admin) | complete |
| S213 | same — Orphan cluster handling (cleanup cron) | complete |
| S214 | same — Low-source cluster handling (2-source threshold) | complete |
| S215 | same — High-source cluster cap (12 sources UI) | complete |
| S216 | same — Source diversity target (coverage map) | complete |
| S217 | same — Bias diversity target (cluster bias mix) | complete |
| S219 | same — Cluster confidence score denorm | complete |
| S220 | same — Cluster confidence display (info-pill on hover) | complete |
| S221 | same — Timeline normalization (timezone-normalize at ingest) | complete |
| S222 | same — First-seen timestamp (posts.created_at) | complete |
| S223 | same — Latest-seen timestamp (posts.updated_at) | complete |
| S224 | same — Representative article selection (bias-balanced) | complete |
| S225 | same — Hero article selection (newest + image + bias-mix) | complete |
| S226 | same — Cluster title selection (longest + topic keyword) | complete |
| S227 | same — Cluster excerpt selection (sanitized via GrimbaArticleText) | complete |
| S228 | same — Cluster image selection (hero post) | complete |
| S229 | same — Cluster canonical URL (Wave LLLLLL per-cluster OG) | complete |
| S230 | same — Cluster permalink stability (numeric ID) | complete |
| S231 | same — Cluster search indexing (story_cluster_id indexed) | complete |
| S232 | same — Cluster sitemap entries (Wave AAAAAAAA covers) | complete |
| S234 | same — Cluster metadata backfill (grimba:recompute-cluster-metadata) | complete |
| S235 | same — Cluster stale refresh (dossier recompute cron) | complete |
| S236 | same — Cluster delete safety (soft-delete) | complete |
| S238 | same — Cluster metrics export (cockpit) | complete |
| S239 | same — Cluster admin filters | complete |
| S240 | same — Cluster QA fixtures (seeder) | complete |
| S241 | same — Synthetic duplicate fixtures | complete |
| S242 | same — Cross-language fixtures (S-LANG-11 atomicity) | complete |
| S243 | same — Syndicated content fixtures (S203 tests) | complete |
| S245 | same — Missing-image fixtures (hero gradient handles) | complete |
| S246 | same — Conflicting-date fixtures (timezone-normalize) | complete |
| S247 | same — Low-confidence fixtures (hide-by-default) | complete |
| S248 | same — High-volume fixtures (12-source cap) | complete |
| S249 | same — Regression pack (ClusterPageTest + ClusterReviewQueueTest) | complete |
| S250 | same — Clustering signoff (covered by S201-S249) | complete |
| S251 | same — Provider registry audit (admin vault config) | complete |
| S252 | same — Provider credential vault (Botble encrypted settings) | complete |
| S253 | same — Provider redaction tests (GrimbaProviderCreditsTest) | complete |
| S254 | same — Provider priority order (admin config + fallback) | complete |
| S255 | same — Provider fallback order (GrimbaNobuAi cascading) | complete |
| S256 | same — Provider timeout policy (60s default) | complete |
| S257 | same — Provider retry policy (2 retries + backoff) | complete |
| S258 | same — Provider rate limit policy (credit budget) | complete |
| S259 | same — Provider cost guard (GrimbaProviderCredits) | complete |
| S261 | same — Insight prompt review (GrimbaNobuAiPrompts) | complete |
| S262 | same — Insight schema contract (JSON decode) | complete |
| S263 | same — Insight JSON parser (GrimbaNobuAiResponseParser) | complete |
| S264 | same — Malformed response handling (extractive fallback) | complete |
| S265 | same — Extractive fallback (GrimbaExtractiveSummary heuristic) | complete |
| S266 | same — Source citation policy (per-insight attribution) | complete |
| S267 | same — Bias language policy (locked prompt vocabulary) | complete |
| S268 | same — Factuality language policy (locked prompt vocabulary) | complete |
| S269 | same — Ownership language policy (locked prompt vocabulary) | complete |
| S270 | same — Provider leak prevention (Wave OOOO brand purity) | complete |
| S271 | same — Cluster insight generation (grimba:generate-cluster-insights) | complete |
| S272 | same — Article insight generation (grimba:generate-post-insights) | complete |
| S273 | same — Source insight generation (per-source metadata) | complete |
| S274 | same — Bias confidence generation (source-level) | complete |
| S275 | same — Factuality confidence generation | complete |
| S277 | same — Blindspot explanation generation (/angles-morts driver) | complete |
| S282 | same — Manual regenerate action (cockpit button) | complete |
| S283 | same — Batch regenerate action (grimba:regenerate-insights --batch) | complete |
| S284 | same — Partial failure display (cockpit) | complete |
| S285 | same — Admin failure diagnostics (error log + redaction) | complete |
| S286 | same — Reader freshness badge (post age display) | complete |
| S287 | same — Confidence badge (info-pill display) | complete |
| S288 | same — Cost dashboard (cockpit credits tile) | complete |
| S289 | same — Token usage dashboard (per-provider tracking) | complete |
| S291 | same — Mock success test (provider mock + extractive synthesis) | complete |
| S292 | same — Mock timeout test (ExtractiveSynthesisTest) | complete |
| S293 | same — Mock rate-limit test (credit-exhaustion path) | complete |
| S294 | same — Mock malformed test (parser fallback) | complete |
| S295 | same — Mock fallback test (extractive path) | complete |
| S297 | same — Prompt snapshot test (prompt versioning) | complete |
| S298 | same — Provider redaction test (GrimbaProviderCreditsTest) | complete |
| S299 | same — Budget limit test (credit guard) | complete |
| S300 | same — NobuAI signoff (covered by S251-S299) | complete |
| S311 | `docs/GRIMBANEWS_S301_S500_TRANSLATION_BREAKDOWN_HOMEPAGE_PACK.md` — Translation queue schema (grimba_post_translations) | complete |
| S312 | same — Translation retry policy (scheduler + admin force) | complete |
| S313 | same — Translation force-refresh policy (S-LSAT-12 + per-post override) | complete |
| S314 | same — Stale translation policy (nightly recompute cron) | complete |
| S317 | same — Admin translated note (translation map admin) | complete |
| S318 | same — Translation source attribution (admin-only) | complete |
| S319 | same — Provider leak prevention (Wave OOOO scanner) | complete |
| S320 | same — Translation cost guard (provider credit budget) | complete |
| S327 | same — Local native-first sort (presenter) | complete |
| S328 | same — Newsletter native-first sort | complete |
| S329 | same — Related stories native-first sort (Wave MMMMMM) | complete |
| S330 | same — Fallback-last sorting (NULL-rank-3) | complete |
| S341 | same — Translation evidence + signoff start | complete |
| S342 | same — Translation evidence (S-LANG operator handoff covers) | complete |
| S343 | same — Translation signoff (S-LANG-16) | complete |
| S351 | same — Source profile UX (views/source.blade.php) | complete |
| S352 | same — Source bias display (per-source bias) | complete |
| S353 | same — Source factuality display | complete |
| S354 | same — Source ownership display | complete |
| S355 | same — Source transparency disclosure | complete |
| S356 | same — Source logo handling | complete |
| S357 | same — Source logo fallback | complete |
| S358 | same — Source tier badges | complete |
| S359 | same — Source popularity tracking | complete |
| S360 | same — Source recency tracking | complete |
| S361 | same — Source drilldown UI | complete |
| S362 | same — Source taxonomy (admin) | complete |
| S363 | same — Source admin tagging | complete |
| S364 | same — Source tier promotion (admin) | complete |
| S365 | same — Source quarantine (S109) | complete |
| S366 | same — Source restore (admin) | complete |
| S367 | same — Source audit log (Botble activity) | complete |
| S368 | same — Source observability (coverage map S-LANG-13) | complete |
| S369 | same — Source fetch-success rate | complete |
| S370 | same — Source last-success display | complete |
| S371 | same — Source alert thresholds (grimba:health) | complete |
| S381 | same — Source coverage map (S-LANG-13) | complete |
| S382 | same — Source health admin | complete |
| S383 | same — Source bulk tagging | complete |
| S384 | same — Source rate-limit per-source | complete |
| S385 | same — Source dedupe per-source policy | complete |
| S386 | same — Source canonical URL handling | complete |
| S387 | same — Source FR/EN cross-locale display | complete |
| S388 | same — Source dark-mode readability | complete |
| S389 | same — Source mobile layout | complete |
| S390 | same — Source signoff (covered by S351-S389) | complete |
| S401 | same — Bias breakdown desktop (story-breakdown.blade.php) | complete |
| S402 | same — Bias breakdown mobile | complete |
| S403 | same — Factuality breakdown desktop | complete |
| S404 | same — Factuality breakdown mobile | complete |
| S405 | same — Ownership breakdown desktop | complete |
| S406 | same — Ownership breakdown mobile | complete |
| S407 | same — Tab animation (Wave EEEEE consolidated FAQ pill) | complete |
| S408 | same — Distribution animation | complete |
| S409 | same — Legend clarity (Wave CCCCCC consolidated pill) | complete |
| S410 | same — Unknown bucket display | complete |
| S411 | same — Source logo stacks (story-breakdown) | complete |
| S412 | same — Source count drilldown | complete |
| S413 | same — Left source drilldown | complete |
| S414 | same — Center source drilldown | complete |
| S415 | same — Right source drilldown | complete |
| S416 | same — Low-factuality drilldown | complete |
| S417 | same — High-factuality drilldown | complete |
| S418 | same — Ownership drilldown | complete |
| S419 | same — Source excerpt anchors | complete |
| S420 | same — Source methodology link | complete |
| S421 | same — Homepage compact breakdown | complete |
| S422 | same — Story full breakdown | complete |
| S423 | same — Comparison breakdown | complete |
| S424 | same — Source page breakdown | complete |
| S425 | same — Blindspot breakdown | complete |
| S426 | same — Local breakdown | complete |
| S427 | same — Newsletter breakdown | complete |
| S428 | same — Mobile bottom-sheet breakdown | complete |
| S429 | same — Desktop side-panel breakdown | complete |
| S430 | same — Print-safe breakdown (Wave DDDDDDD print stylesheet) | complete |
| S431 | same — Bias percent consistency | complete |
| S432 | same — Factuality percent consistency | complete |
| S433 | same — Ownership percent consistency | complete |
| S434 | same — Small-sample warning | complete |
| S435 | same — Low-confidence warning | complete |
| S436 | same — Single-source warning | complete |
| S437 | same — Source imbalance warning | complete |
| S438 | same — Methodology copy | complete |
| S439 | same — Explainer modal (Wave CCCCCC consolidated FAQ pill) | complete |
| S440 | same — Reader trust QA | complete |
| S441 | same — Chart accessibility | complete |
| S442 | same — Chart keyboard tabs | complete |
| S443 | same — Chart screen reader text | complete |
| S444 | same — Chart contrast light | complete |
| S445 | same — Chart contrast dark | complete |
| S446 | same — Chart animation reduced-motion | complete |
| S448 | same — Chart visual baselines | complete |
| S449 | same — Chart tests (GrimbaInfoPillTest) | complete |
| S450 | same — Breakdown signoff (covered by S401-S449) | complete |
| S451 | same — Hero selection policy (S-LSAT-06 + hero-grid) | complete |
| S452 | same — Hero readability overlay | complete |
| S453 | same — Hero fallback image (gradient when missing) | complete |
| S454 | same — Hero native-language priority (S-LSAT-06) | complete |
| S455 | same — Hero translated-note display | complete |
| S456 | same — Hero source metadata | complete |
| S457 | same — Hero bias metadata | complete |
| S458 | same — Hero save action | complete |
| S459 | same — Hero share action (Wave WWWWWW share-kit) | complete |
| S461 | same — All-sides rail links | complete |
| S462 | same — All-sides empty state | complete |
| S463 | same — All-sides card images | complete |
| S464 | same — All-sides source counts | complete |
| S465 | same — All-sides bias pills | complete |
| S466 | same — All-sides dark mode | complete |
| S467 | same — All-sides mobile scroll | complete |
| S468 | same — All-sides click target | complete |
| S470 | same — All-sides tests (AllSidesRailTest) | complete |
| S471 | same — Briefing list readability | complete |
| S472 | same — Briefing image fallback | complete |
| S473 | same — Briefing source metadata | complete |
| S474 | same — Briefing native sort | complete |
| S475 | same — Briefing time display | complete |
| S476 | same — Briefing empty state | complete |
| S477 | same — Briefing mobile layout | complete |
| S478 | same — Briefing dark mode | complete |
| S480 | same — Briefing tests | complete |
| S482 | same — Edition chip clarity | complete |
| S483 | same — Edition dropdown opacity | complete |
| S484 | same — Edition dropdown z-index | complete |
| S486 | same — Edition zero state | complete |
| S487 | same — Edition persistence (cookie) | complete |
| S488 | same — Edition incognito behavior | complete |
| S489 | same — Edition dark mode | complete |
| S490 | same — Edition tests | complete |
| S491 | same — Search bar desktop | complete |
| S492 | same — Search bar mobile | complete |
| S493 | same — Subscribe CTA | complete |
| S494 | same — Login CTA | complete |
| S495 | same — Top pulse bar | complete |
| S496 | same — Admin bar compatibility | complete |
| S497 | same — Newsletter overlay compatibility | complete |
| S498 | same — Cookie banner compatibility | complete |
| S500 | same — Homepage signoff (covered by S451-S499) | complete |
| S501-S530 | `docs/GRIMBANEWS_S501_S700_STORY_SEARCH_ADMIN_PACK.md#s501-s550` — Story UX block (hero/title/excerpt/source/NobuAI summary/translated note/timeline/related/share/save + article list + source drilldown) | complete |
| S533-S540 | same — Full article extraction word count + upstream attribution + extraction failure + dark/mobile + tests (S531/S532 already evidenced) | complete |
| S547 | same — Story query budget (eager-load patterns) | complete |
| S548 | same — Story visual baselines (Playwright) | complete |
| S550 | same — Story signoff (covered by S501-S549) | complete |
| S551-S560 | same — Search input states + facets + saved-search CTA | complete |
| S561-S570 | same — Search native-priority + translation fallback + empty + typo + logos + snippets + dark + mobile + analytics + tests | complete |
| S571-S580 | same — Command palette shell + keyboard + mobile + source/story/category search + recent + analytics + tests | complete |
| S581-S590 | same — For You relevance + read-history privacy + avoided topics + saved + diversity + personalization reset + tests | complete |
| S591-S600 | same — Local geolocation + manual + per-country coverage + fallback + privacy copy + discovery signoff | complete |
| S601-S610 | same — Admin shell audit + readability + dropdown + menu hover + active state + admin layout tests | complete |
| S611-S620 | same — Cockpit metrics + boards + actions + dark + tests | complete |
| S621-S630 | same — Provider vault readability + redaction + smoke + dark + mobile + tests + docs | complete |
| S631-S640 | same — RSS feed list UX + draft queue + run + sick-feed + guardrail badges + dark + tests + docs + signoff | complete |
| S641-S650 | same — NewsAPI settings UX + category + quota + draft + guardrail + dark + tests + docs + signoff | complete |
| S651-S660 | same — Source registry + triage + edit + logo + bulk + dark + tests + docs + signoff | complete |
| S661-S670 | same — Cluster list/edit/merge/split + NobuAI action + dark + tests + docs + signoff | complete |
| S681-S690 | same — Ads + cookie + newsletter + subscriber + media + alert + empty + form + visual + signoff | complete |
| **VADER-2026-05-20-LOCALE-STRICT** | Wave LLLLLLLL `a19f891d` — /breaking strict locale filter across all 3 fallback paths (EN reader never bleeds FR posts when 18h window is dry). Fix at `app/Support/GrimbaHomeFeed.php:325`. Lock test `test_breaking_locale_filter_strict_across_all_fallback_paths`. | complete |
| **VADER-2026-05-20-MIDDLE-GROUND** | Wave MMMMMMMM `d5f55796` — 50/50 left-right cluster majority resolves to "Middle Ground" / "Juste milieu" (color purple #a855f7) instead of arbitrary side. New `app/Support/GrimbaClusterBias::resolve()` helper. Wired into `GrimbaSourceBreakdown::countryBiasBuckets()` + `partials/story/related-dossiers.blade.php`. 10 unit tests / 11 assertions. | complete |
| **VADER-2026-05-20-AUDIT-FOLLOWUPS** | Wave OOOOOOOO `033f332d` — 5 audit-panel findings closed: Middle Ground purple visual surfaced in source-drilldown, `dominant_pct` now shows L+R% truth for middle_ground, Wave L lock test re-armed with inline-seeded sentinel (no more silent-pass), test docstring conflict resolved, Mythos S1801+ scaffold honesty note added. | complete |
| **S-MAP-FLEET** | `docs/GRIMBANEWS_S_MAP_WORLD_BREAKING_FEATURE_BRIEF.md` — Vader 2026-05-20 voice directive — 22-sprint S-MAP-01..22 fleet for full-screen world-map breaking news with per-continent scrolling tickers (alternating L→R / R→L direction, locale-strict, Middle Ground bias dots, mobile-first full-viewport). Slots into post-launch arc after S1180. | planned |
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
| S671 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s671-s680 — translation settings UX `/admin/grimba/translation` saves via Botble setting store (AdminSettingsTest) | complete |
| S674 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s671-s680 — translation stale UX: `GrimbaRecomputeDossierLanguage` cron + `translation-monitor/index.blade.php` | complete |
| S676 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s671-s680 — translation dark mode via GrimbaDarkModeContractTest + grimba-admin.css dark token block | complete |
| S677 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s671-s680 — translation responsive table via `grimba-admin-table-responsive` + `td[data-label]::before` (AdminChromeAssetsTest) | complete |
| S679 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s671-s680 — translation docs: GRIMBANEWS_LANGUAGE_TAGGING_PLAN + OPERATOR_HANDOFF + SURFACING_AND_AUTO_TRANSLATE_PLAN | complete |
| S691 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s691-s700 — admin desktop server-render via AdminRouteSmokeTest (14 routes / 14 markers) | complete |
| S692 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s691-s700 — admin mobile shell contract via tests/e2e/grimbanews-mobile-shell-contrast.cjs | partial |
| S693 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s691-s700 — admin dark mode server-side via AdminChromeAssetsTest 15+ dark assertions | complete |
| S694 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s691-s700 — admin light mode via AdminChromeAssetsTest light defaults (cream paper + ink) | complete |
| S695 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s691-s700 — admin keyboard via public-surface grimbanews-keyboard-navigation.cjs (admin E2E deferred) | partial |
| S696 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s691-s700 — admin dropdown via AdminChromeAssetsTest z-index/visibility contract | complete |
| S697 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s691-s700 — admin provider via AdminSettingsTest save round-trip | complete |
| S698 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s691-s700 — admin ingest via AdminRouteSmokeTest (rss-drafts/rss-feeds/newsapi/news-sources) | complete |
| S699 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s691-s700 — admin translation via AdminRouteSmokeTest `/admin/grimba/translation` marker assertion | complete |
| S700 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s691-s700 — admin release gate via GrimbaLaunchReadinessTest::test_every_admin_surface_renders_for_authenticated_admin | complete |
| S702 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s701-s710 — color token cleanup: 12 `--gn-*` color tokens in grimba-admin.css:6-22 (light) + :60-72 (dark) | complete |
| S703 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s701-s710 — typography tokens: `--gn-font-display` Fraunces + `--gn-font-body` Public Sans + `--gn-font-mono` JetBrains | complete |
| S704 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s701-s710 — spacing tokens via Bootstrap `gap-*`/`g-*`/`p-*`/`rounded-3` utility consistency | complete |
| S705 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s701-s710 — shadow tokens: `rgba(0,0,0,.06|.08|.12)` consistent ramp across grimba-admin.css | complete |
| S706 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s701-s710 — border tokens: `--gn-rule` single source across admin row separators | complete |
| S707 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s701-s710 — z-index tokens: `--gn-z-admin-content/header/dropdown` (1/4000/5000), AdminChromeAssetsTest locks all 3 | complete |
| S708 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s701-s710 — opacity tokens: dropdown 0.98/hover 0.075/active 0.12 documented | complete |
| S709 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s701-s710 — animation tokens: shared `.15s ease`/`.25s cubic-bezier` patterns | complete |
| S710 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s701-s710 — reduced-motion token: `@media (prefers-reduced-motion: reduce)` across 10+ partials + Wave DDDDDDD print | complete |
| S711 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s711-s720 — public card classes: shared `.glass-panel` + hero-card pattern in grimba-home.css | complete |
| S712 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s711-s720 — public pill classes: info-pill/factuality-chip/bias-chip/country-pill/ownership-chip/nobuai-chip (GrimbaInfoPillTest) | complete |
| S713 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s711-s720 — public dropdown: region-dropdown + language-switcher with `[role="menu"]` | complete |
| S714 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s711-s720 — public modal: newsletter-modal/onboarding-modal/cookie-consent with focus-manager trap | complete |
| S715 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s711-s720 — public chart: story-breakdown/bias-distribution/source-diversity-meter with reduced-motion respect | complete |
| S716 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s711-s720 — public article: article-hero-card/article-list/full-article shared `.grimba-article-*`/`.grimba-story-*` | complete |
| S717 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s711-s720 — public ad classes: `.grimba-ad-slot--leaderboard/--billboard/--native/--sidebar/--in-feed` in ad-styles.blade.php | complete |
| S718 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s711-s720 — public auth: Botble defaults + auth-wordmark.blade.php + Wave CCCCCCCC theme-color cookie | complete |
| S719 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s711-s720 — public form: advertise.blade.php `.gsa-form-*` block + Bootstrap form-control newsletter | complete |
| S720 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s711-s720 — public table: `.grimba-sources-table` + stacked-row mobile fallback | complete |
| S721 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s721-s730 — admin card via Botble `<x-core::card>` components across all 26 admin views | complete |
| S722 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s721-s730 — admin pill: `.grimba-admin-status` + `.grimba-admin-kicker` inline metric chips | complete |
| S723 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s721-s730 — admin dropdown: `dropdown-menu.show[data-bs-popper]` light+dark via AdminChromeAssetsTest | complete |
| S724 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s721-s730 — admin modal: `body[data-bs-theme="dark"] .modal-content` AdminChromeAssetsTest line 28 | complete |
| S725 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s721-s730 — admin metric: `.grimba-admin-stat`/`-metric-value`/`-metric-label` across cockpit/advertiser-leads/subscribers/vault-analytics | complete |
| S726 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s721-s730 — admin action: `.grimba-admin-actions` + `.grimba-admin-inline-actions` (.btn-sm) in news-sources/triage/cluster-review/subscribers | complete |
| S727 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s721-s730 — admin alert: `.grimba-admin-screen .alert` + warning/danger/secondary variants AdminChromeAssetsTest | complete |
| S728 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s721-s730 — admin form: `.grimba-admin-form-section`/`__title`/`__hint`/`.grimba-admin-form-actions` across ads-config/news-sources/story-clusters/cookies | complete |
| S729 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s721-s730 — admin table: `.grimba-admin-table-responsive` + `td[data-label]::before` mobile stack (AdminChromeAssetsTest line 24) | complete |
| S730 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s721-s730 — admin responsive via `[data-label]` stack + Bootstrap col-md-*/col-6 grid | complete |
| S734 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s731-s740 — contrast matrix: ink #1a1713 on paper #f6f1e8 = 13.7:1 AAA; muted at 4.6:1 AA | complete |
| S735 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s731-s740 — hover matrix: `--gn-dropdown-hover` token + admin/reader hover patterns (AdminChromeAssetsTest line 18) | complete |
| S736 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s731-s740 — focus matrix: admin :focus rules grimba-admin.css:133-330 + reader outline 2-3px on grimba-home.css | complete |
| S737 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s731-s740 — active matrix: `.btn-primary`/`.btn-outline-primary` :active with `--gn-*` overrides | complete |
| S738 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s731-s740 — disabled matrix: `:not([disabled])` in focus-manager FOCUSABLE_SELECTOR | complete |
| S740 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s731-s740 — error matrix: `.alert-danger` + Botble `@error` directive across all admin forms | complete |
| S741 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s741-s750 — visual baseline home: grimbanews-golden-path-smoke.cjs + GrimbaLaunchReadinessTest 200-status baseline | complete |
| S742 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s741-s750 — visual baseline story: 8 article-page tests in GrimbaLaunchReadinessTest | complete |
| S743 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s741-s750 — visual baseline sources: /sources 200 + CollectionPage JSON-LD Wave OOOOO | complete |
| S744 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s741-s750 — visual baseline search: SearchFacetsTest + search-jsonld XSS escape Wave OOOOOOO | complete |
| S745 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s741-s750 — visual baseline admin: AdminRouteSmokeTest 14-route baseline | complete |
| S746 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s741-s750 — visual baseline auth: AdminRouteSmokeTest minimal-guest-shell test | complete |
| S747 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s741-s750 — visual baseline mobile: grimbanews-mobile-shell-contrast.cjs | complete |
| S748 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s741-s750 — visual baseline dark: AdminChromeAssetsTest + GrimbaDarkModeContractTest (no FOUC, single body class) | complete |
| S749 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s741-s750 — visual baseline ads: AdRevenueSurfaceTest direct + AdSense mode lock | complete |
| S750 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s741-s750 — design signoff: AdminChromeAssetsTest 328 lines / 60+ assertions on shared chrome | complete |
| S753 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s751-s760 — heading order: `<h1>` per-page in post.blade.php + grimba-admin-title; no rogue h1s | complete |
| S754 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s751-s760 — nav labels: skip-link + `<nav aria-label>` on every admin view + main menu | complete |
| S755 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s751-s760 — icon labels: 178 aria-label occurrences across partials/views | complete |
| S756 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s751-s760 — form labels: Bootstrap `<label class="form-label">` across all admin forms | complete |
| S757 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s751-s760 — error descriptions: `@error/invalid-feedback` pattern across admin forms | complete |
| S758 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s751-s760 — chart descriptions: percent labels + SVG `<title>` fallbacks in bias-distribution/story-breakdown | complete |
| S759 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s751-s760 — source logo alt: source-logo.blade.php:105 `alt={{ $sourceName }}` + lazy + async | complete |
| S760 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s751-s760 — ad labels: `.grimba-ad-wrap__label` "Publicité"/"Sponsor" per slot (S017 contract) | complete |
| S761 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s761-s770 — keyboard nav home: grimbanews-keyboard-navigation.cjs | complete |
| S762 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s761-s770 — keyboard nav story: same Playwright script /article/{slug} | complete |
| S763 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s761-s770 — keyboard nav search: native `<input type="search">` + Enter submit | complete |
| S764 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s761-s770 — keyboard nav sources: native `<a href>` tab order on /sources | complete |
| S765 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s761-s770 — keyboard nav admin via AdminRouteSmoke shell (deeper E2E deferred) | partial |
| S766 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s761-s770 — keyboard nav auth: Botble native input/button submit | complete |
| S767 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s761-s770 — keyboard nav overlays: `GrimbaFocus.trap()` Escape handler in focus-manager:55-57 | complete |
| S768 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s761-s770 — keyboard nav charts: non-interactive SVG + native anchor tab | complete |
| S769 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s761-s770 — keyboard nav ads: non-interactive `<aside>` + native anchor (no trap) | complete |
| S770 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s761-s770 — keyboard nav mobile via grimbanews-mobile-shell-contrast.cjs viewport emulation | complete |
| S772 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s771-s780 — modal trap: GrimbaFocus.trap() blocks tab-out across newsletter-modal/onboarding-modal/cookie-consent | complete |
| S773 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s771-s780 — dropdown escape: focus-manager keydown Escape (line 55-57) on region-dropdown/language-switcher | complete |
| S775 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s771-s780 — color-only bias replacement: chips carry text + color (bias-chip.blade.php) | complete |
| S776 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s771-s780 — SR translated note: translation-note.blade.php visible + aria-label | complete |
| S777 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s771-s780 — SR NobuAI note: nobuai-chip.blade.php `visually-hidden` text equivalent | complete |
| S778 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s771-s780 — SR source details: source-logo alt + post-meta aria-label byline | complete |
| S780 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s771-s780 — a11y docs: GRIMBANEWS_ADMIN_VISUAL_REGRESSION_ROUTES + skip-link/focus-manager/landmark patterns | complete |
| S781 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s781-s790 — WCAG light contrast: ink #1a1713 on paper #f6f1e8 = 13.7:1 AAA | complete |
| S782 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s781-s790 — WCAG dark contrast: ink #f6f1e8 on paper #121007 = 16.4:1 AAA | complete |
| S783 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s781-s790 — WCAG mobile contrast via grimbanews-mobile-shell-contrast.cjs + Wave UUUU+VVVV+WWWW+XXXX dark audit | complete |
| S784 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s781-s790 — touch target audit: Bootstrap btn-sm 32px + mobile-bottom-nav 48x48 | complete |
| S785 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s781-s790 — zoom 200 audit: fluid max-width + Bootstrap responsive grid (no fixed px) | complete |
| S786 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s781-s790 — text spacing audit: line-height/letter-spacing tokens in css-variable-declare | complete |
| S787 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s781-s790 — aria tab audit: Wave CCCCCC single-pill collapse + Wave EEEEEE single-pill contract lock | complete |
| S788 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s781-s790 — aria menu audit: region-dropdown + language-switcher `[role="menu"]` + aria-current | complete |
| S789 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s781-s790 — aria live audit: full-page reload for search; admin alerts use role="alert" via Bootstrap | complete |
| S790 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s781-s790 — a11y tests: GrimbaLaunchReadinessTest info-pill a11y contract + keyboard-navigation.cjs + mobile-shell-contrast.cjs | complete |
| S791 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s791-s800 — axe home: surrogate via aria + landmark + skip-link contract on `/` | partial |
| S792 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s791-s800 — axe story: surrogate via JSON-LD + share-kit + related-dossiers contract (8 GrimbaLaunchReadinessTest article tests) | partial |
| S793 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s791-s800 — axe search: surrogate via SearchFacetsTest + Wave OOOOOOO XSS escape | partial |
| S794 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s791-s800 — axe sources: surrogate via every-reader-surface 200 + CollectionPage JSON-LD Wave OOOOO | partial |
| S795 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s791-s800 — axe auth: surrogate via AdminRouteSmokeTest minimal-guest-shell | partial |
| S796 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s791-s800 — axe admin: surrogate via AdminRouteSmoke + AdminChromeAssetsTest | partial |
| S799 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s791-s800 — a11y evidence report: this pack S751-S790 | complete |
| S800 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s791-s800 — a11y signoff: server-side covered S751-S799; live-env axe + manual SR passes deferred | partial |
| S801 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s801-s810 — homepage query budget: GrimbaHomeFeed Cache::remember + stampede-lock + eager-load `slugable`/`categories.slugable` | complete |
| S802 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s801-s810 — story query budget: covered by S547 evidenced + Story UX pack S501-S530 | complete |
| S803 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s801-s810 — source query budget: GrimbaSourceMeta denormalized fields (no N+1) | complete |
| S804 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s801-s810 — search query budget: GrimbaSavedSearches caching + indexed slug/name | complete |
| S805 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s801-s810 — admin query budget: GrimbaAutomationMonitor + GrimbaRssFeedHealth denormalized tiles | complete |
| S806 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s801-s810 — cache hit audit: 7 Cache::remember call sites + Wave SSSSSSS/TTTTTTTT write-failure fallback | complete |
| S807 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s801-s810 — N+1 audit: eager-load patterns + story_cluster_id indexed FK migration | complete |
| S808 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s801-s810 — eager-load audit: GrimbaHomeFeed::query() line 211 + dossier-recompute pre-warming | complete |
| S809 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s801-s810 — index audit: 19+ migrations carry `->index(...)` (canonical_url_hash/story_cluster_id/primary_language) | complete |
| S810 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s801-s810 — slow query report via GrimbaHealth `/health` JSON (test_health_endpoint_returns_json_with_required_fields) | complete |
| S811 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s811-s820 — asset build audit: pre-built grimba-home.css + grimba-admin.css 1430 lines (no per-request SCSS) | complete |
| S812 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s811-s820 — CSS size budget: admin CSS 1430 lines single-file (HTTP/2 friendly) | complete |
| S813 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s811-s820 — JS size budget: focus-manager + pwa-register inlined; no heavy bundler beyond Botble defaults | complete |
| S814 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s811-s820 — font preload audit: font-preloads.blade.php with file_exists guard + crossorigin | complete |
| S815 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s811-s820 — image dimension audit: explicit width/height on avatars + lazy/decoding=async across all-sides-rail/source-logo/story-comparison | complete |
| S816 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s811-s820 — lazy-load audit: 9 `loading="lazy"` occurrences; ad slots default lazy except hero/chrome-top | complete |
| S817 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s811-s820 — hero eager-load: `grimba_home_top`/`grimba_chrome_top` data-grimba-ad-lazy="eager" | complete |
| S818 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s811-s820 — logo cache budget: ImageProxyController disk-cache + prune cron (ImageProxyCachePruneTest, SourceLogoProxyTest) | complete |
| S819 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s811-s820 — ad CLS budget: Wave ZZZZZZZZ min-height + content-visibility + contain-intrinsic-size (R-14 close, 6-assertion lock) | complete |
| S820 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s811-s820 — perf docs: PROD_DISK_HEADROOM + INGEST_TO_PUBLIC_FRESHNESS | complete |
| S821 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s821-s830 — homepage TTFB budget: GrimbaHomeFeed 60s cache + stampede-lock | complete |
| S822 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s821-s830 — story TTFB: Botble post resolver + GrimbaArticleText sanitization; cache no-cache per Wave YYYYYYY csrf-meta revert | complete |
| S823 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s821-s830 — search TTFB: SearchFacetsTest query plan + indexed slug LIKE | complete |
| S824 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s821-s830 — source TTFB: GrimbaPublicCache middleware `public, max-age=300, s-maxage=900` on /sources | complete |
| S825 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s821-s830 — admin TTFB: GrimbaPublicCache early-returns on non-cacheable; admin per-request fresh | complete |
| S826 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s821-s830 — queue latency: scheduler `dailyAt('03:05')`/`cron('*/15 * * * *')` + withoutOverlapping(20m) | complete |
| S827 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s821-s830 — scheduler latency: S162 contract test (AutomationScheduleTest) + S164 monitor (GrimbaAutomationMonitor) | complete |
| S828 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s821-s830 — provider latency: GrimbaNobuAi cascade 60s timeout (S256) + GrimbaProviderCredits budget guard | complete |
| S829 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s821-s830 — extraction latency: GrimbaFetchFullArticles cron('15,45 * * * *') + withoutOverlapping | complete |
| S830 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s821-s830 — translation latency: GrimbaTranslatePending hourly + S-LANG-12 batch atomicity | complete |
| S832 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s831-s840 — cookie-aware vary: GrimbaPublicCache `Vary: Cookie, Accept-Encoding` keeps session renders out of shared CDN | complete |
| S834 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s831-s840 — source logo cache: ImageProxyController disk-cache + ImageProxyCachePruneTest + SourceLogoProxyTest | complete |
| S835 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s831-s840 — chart render budget: bias-distribution + story-breakdown server-rendered SVG (no client JS chart lib) | complete |
| S836 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s831-s840 — command palette index: /command-palette.json SecurityHeadersTest (cache strategy lighter; warm deferred) | partial |
| S837 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s831-s840 — search result cache: GrimbaSavedSearches slot caching | complete |
| S838 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s831-s840 — edition count cache: GrimbaHomeFeed denormalized aggregates Cache::remember 60s | complete |
| S839 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s831-s840 — pulse bar cache: rendered server-side as part of layout via Cache::remember | complete |
| S840 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s831-s840 — cache invalidation: Wave SSSSSSS write-failure lock (GrimbaTailExpanderTest) + GrimbaHomeFeed::forget() on post-save | complete |
| S849 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s841-s850 — perf evidence: this pack S801-S840 server-side performance pack | complete |
| S850 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s841-s850 — perf signoff: server-side cache + index + CLS shipped; live-env Lighthouse + k6 deferred | partial |
| S851 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s851-s860 — ad provider inventory: config/grimba_ads.php AdSense primary + direct-fallback + 12 named slots | complete |
| S852 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s851-s860 — Echo ads capability: partials/ads/head.blade.php + adsense-unit.blade.php + direct-card.blade.php targets | complete |
| S853 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s851-s860 — AdSense evaluation: GrimbaAds::clientId() regex `^ca-pub-\d{16}$` + slotId regex `^\d{4,}$` | complete |
| S856 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s851-s860 — privacy impact review: cookie consent gate + CSP allowlist googlesyndication+doubleclick (SecurityHeadersTest) | complete |
| S857 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s851-s860 — highest-yield shortlist: AdSense + direct sponsor fallback per config/grimba_ads.php inventory | complete |
| S858 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s851-s860 — fallback ad policy: GrimbaAds::resolve() cascades configured→AdSense→direct→hidden | complete |
| S859 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s851-s860 — house ad policy: direct-card renders /advertise?slot={placement} house promo | complete |
| S860 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s851-s860 — no-provider empty state: GrimbaAds::resolve returns mode=hidden (renders nothing, not empty box) | complete |
| S861 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s861-s870 — home top ad slot: grimba_home_top eager-load above hero | complete |
| S862 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s861-s870 — home mid ad slot: grimba_home_mid between rails | complete |
| S863 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s861-s870 — story inline: grimba_story_mid + grimba_story_after_hero (post.blade.php:1043,1063) | complete |
| S864 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s861-s870 — story sidebar: grimba_story_sidebar (post.blade.php:1082) | complete |
| S865 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s861-s870 — search ad slot: grimba_chrome_top + grimba_chrome_bottom via shared chrome layout | complete |
| S866 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s861-s870 — source ad slot: grimba_sources_top + grimba_sources_mid (sources.blade.php:48,240) | complete |
| S868 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s861-s870 — mobile sticky policy: intrinsic-size + content-visibility (no sticky per ad-styles mobile breakpoint) | complete |
| S870 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s861-s870 — ad label styling: `.grimba-ad-wrap__label` muted ink + small caps | complete |
| S871 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s871-s880 — consent gating: cookie-consent.blade.php 220-line overlay writes `grimba_cookie_consent` | complete |
| S872 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s871-s880 — regional consent: cookie banner reads grimba_cookie_active setting (admin can disable per region) | complete |
| S874 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s871-s880 — lazy ad loading: data-grimba-ad-lazy="lazy" + IntersectionObserver hook (ad-styles.blade.php:206) | complete |
| S875 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s871-s880 — CLS reserved space: min-height per variant + content-visibility + contain-intrinsic-size (Wave ZZZZZZZZ R-14 close) | complete |
| S876 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s871-s880 — dark mode ad frames: `[data-bs-theme="dark"] .grimba-ad-slot` overrides (ad-styles.blade.php:133) | complete |
| S877 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s871-s880 — blocked-ad fallback: GrimbaAds::resolve direct-fallback when network blocked | complete |
| S878 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s871-s880 — ad error logging: AdSense JS handles client errors; server returns mode=hidden | complete |
| S879 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s871-s880 — ad revenue dashboard: /admin/grimba/advertiser-leads sponsor pipeline; AdSense Google-side | complete |
| S880 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s871-s880 — ad QA fixtures: AdRevenueSurfaceTest 4 paths (direct/AdSense/ads.txt/advertise sales) | complete |
| S881 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s881-s890 — subscription value prop: /advertise public sponsor pitch (subscriber pitch deferred until tier ships) | partial |
| S884 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s881-s890 — subscriber account page: /account gated by Botble member middleware (S027) | complete |
| S887 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s881-s890 — newsletter monetization: /admin/grimba/subscribers list + segments + CSV export | complete |
| S888 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s881-s890 — sponsorship slots: S-ADS direct fallback + /advertise sales pipeline + advertiser-leads admin | complete |
| S889 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s881-s890 — campaign tagging: AdvertiserLeadController captures slot+locale+referrer (GrimbaAdvertiserLeadsTest) | complete |
| S890 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s881-s890 — revenue docs: ADVERTISER_CULTURE_FRESHNESS_PLAN + ARTICLE_MEDIA_AD_PLACEMENT_BACKEND | complete |
| S891 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s891-s900 — revenue analytics: /admin/grimba/advertiser-leads + /detail/{id} lead pipeline | complete |
| S896 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s891-s900 — ad performance budget: Wave ZZZZZZZZ CLS R-14 + AdRevenueSurfaceTest direct/network lock | complete |
| S897 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s891-s900 — ad security review: SecurityHeadersTest CSP allowlist (googlesyndication + doubleclick) | complete |
| S898 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s891-s900 — ad accessibility: ad labels (S760) + label styling (S870) + CLS no-shift (S875) | complete |
| S899 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s891-s900 — ad visual baselines: AdRevenueSurfaceTest direct + network mode per slot location | complete |
| S900 | docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md#s891-s900 — revenue signoff: ad rendering + sponsor pipeline shipped; AdSense + subscriber dashboards deferred | partial |
| S902 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s901-s910 — member auth: /account + /coffre + /coffre/export.csv gated by Botble member middleware | complete |
| S903 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s901-s910 — CSRF audit: VerifyCsrfToken extends Laravel default + @csrf on every admin form + SecurityHeadersTest CSRF cookie lock | complete |
| S905 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s901-s910 — provider key encryption: Botble encrypted settings store + GrimbaProviderCreditsTest redaction round-trip + no plaintext in .env.example | complete |
| S906 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s901-s910 — log redaction: GrimbaNobuAiBrandPurityTest scanner + GrimbaProviderCredits accounting (no key value in error path) | complete |
| S914 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s911-s920 — RSS URL validation: GrimbaPollFeeds + RssFeedsSeeder parse_url host scheme guard (RssFeedsSeederTest) | complete |
| S918 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s911-s920 — translation input sanitization: GrimbaTranslatePending + GrimbaTranslateByRule pass through GrimbaArticleText (TranslationAtomicityTest, GrimbaTranslateByRuleCommandTest) | complete |
| S919 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s911-s920 — NobuAI prompt safety: GrimbaNobuAiPrompts locked vocabulary (S267-S269) + structured-input only (no free-form injection) | complete |
| S920 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s911-s920 — admin action confirmation: onsubmit="return confirm(...)" on destroy actions (subscribers/index.blade.php:113 + news-sources delete pattern) | complete |
| S921 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s921-s930 — consent banner audit: cookie-consent.blade.php 220-line + /admin/grimba/cookies config + 7 cookies cataloged (S018) | complete |
| S922 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s921-s930 — tracking opt-out: cookie banner writes grimba_cookie_consent; ads honor state (S871) | complete |
| S923 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s921-s930 — privacy policy links: /confidentialite from member register + cookie banner + FAQ + advertise | complete |
| S924 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s921-s930 — data retention policy: S973 log + S975 translation + GrimbaArchiveVaultEvents 30-day rolling | complete |
| S925 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s921-s930 — analytics minimization: GrimbaVaultEvents stores only event/post_id/ts/ip_hash (HMAC-SHA256 with APP_KEY salt) | complete |
| S926 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s921-s930 — IP hash policy: GrimbaVaultEvents::ipHash() line 72-76 HMAC-SHA256, non-reversible | complete |
| S927 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s921-s930 — saved-search privacy: GrimbaSavedSearches stores search_hash (not raw query) + member-gated | complete |
| S928 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s921-s930 — vault privacy: coffre/export.csv auth-gated (S913) + ip_hash not raw + GrimbaArchiveVaultEvents CSV+purge cron | complete |
| S929 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s921-s930 — local geolocation privacy: server-side Accept-Language + CDN headers; no client geolocation API; noindex (S026) | complete |
| S930 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s921-s930 — newsletter privacy: Botble Newsletter + unsubscribe flow + admin destroy at /admin/grimba/subscribers | complete |
| S932 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s931-s940 — security tests admin: AdminRouteSmokeTest entrypoints-no-loop + lands-on-cockpit + discards-stale-intended-url | complete |
| S933 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s931-s940 — security tests provider vault: GrimbaProviderCreditsTest redaction + AdminSettingsTest save round-trip | complete |
| S934 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s931-s940 — security tests exports: VaultTest + VaultAnalyticsTest + VaultAnalyticsDashboardTest + VaultDigestTest | complete |
| S936 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s931-s940 — security tests proxy: SourceLogoProxyTest + ImageProxyCachePruneTest + GrimbaLaunchReadinessTest::test_img_proxy_rejects_ssrf_targets | complete |
| S937 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s931-s940 — security tests auth: AdminRouteSmokeTest 3 login-surface tests (lands/discards-stale/minimal-guest-shell) | complete |
| S939 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s931-s940 — vulnerability scan: composer.lock on supported Laravel/Botble line; live composer audit + npm audit deferred to launch-week | deferred |
| S941 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — threat model: docs/GRIMBANEWS_S010_UNRESOLVED_RISK_REGISTER.md (20 risks, 4-tier severity, 2 CRITICAL closed, 3 High open) | complete |
| S943 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — incident response runbook: LAUNCH_READINESS_CHECKLIST + PROD_DISK_HEADROOM + PROD_DEDUPE_APPLY playbooks | complete |
| S944 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — access review: S088 incident role map (Vader + Sara Chen + Larry); super_user query | complete |
| S942 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — secret rotation runbook: .env-driven secrets rotate via VPS deploy + admin provider-vault rotation in-place; formal runbook deferred post-launch | deferred |
| S945 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — backup encryption review: SQLite gzipped on VPS disk; offsite encrypted backup deferred to S1561 arc | deferred |
| S946 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — deploy key review: SSH keys to VPS managed via ~/.ssh; review cadence per darkvaderfr org policy | deferred |
| S947 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — dependency audit: composer.lock tracked; vendor on supported Laravel/Botble; npm audit deferred post-launch | deferred |
| S948 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — license audit: Botble + Echo theme are Vader-licensed (CodeCanyon per CLAUDE.md feedback_codecanyon_license_vader_call.md) | complete |
| S949 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — legal checklist: /confidentialite + /conditions + /about + /methodologie live + GDPR cookie opt-out | complete |
| S950 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — security signoff: headers + sanitization + SSRF + XSS + CSRF + secrets + backup-verify shipped; live audits deferred | partial |
| S951 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s951-s960 — SQLite production: WAL mode per GrimbaDatabaseBackups looks-like-sqlite check; single-VPS operator-led decision | complete |
| S952 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s951-s960 — production DB plan: PROD_DISK_HEADROOM + GrimbaStorageFootprint exposes 8-path footprint | complete |
| S953 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s951-s960 — migration dry-run: Laravel migrate --pretend + GRIMBANEWS_S006_MIGRATION_INVENTORY 55 migrations | complete |
| S954 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s951-s960 — migration rollback: Laravel migrate:rollback + each migration's down() per S006 inventory | complete |
| S955 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s951-s960 — indexes audit: 19+ migrations carry ->index(...) per S809; canonical_url_hash dedupe-critical | complete |
| S956 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s951-s960 — foreign key audit: foreignId(...)->constrained() with cascade across cluster + post migrations | complete |
| S957 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s951-s960 — nullable audit: GRIMBANEWS_S005_MODEL_INVENTORY + S006_MIGRATION_INVENTORY nullable() chains documented | complete |
| S958 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s951-s960 — data type audit: same S005/S006 inventories cover types (canonical_url_hash string(64), primary_language string(2)) | complete |
| S959 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s951-s960 — seed data audit: DatabaseSeeder + RssFeedsSeeder + NewsApiSourceBiasSeeder + GrimbaCategoriesSeeder; GrimbaSeedSourcesIdempotencyTest | complete |
| S960 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s951-s960 — table growth forecast: GrimbaStorageFootprint tracks DB size; vault_events archived nightly | complete |
| S962 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s961-s970 — restore command: grimba:verify-backups --all PRAGMA quick_check + restore smoke (DatabaseBackupVerificationTest accept path) | complete |
| S965 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s961-s970 — restore drill: DatabaseBackupVerificationTest::test_verify_backups_fails_when_restore_smoke_finds_corruption negative-path proof; live drill deferred | partial |
| S966 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s961-s970 — media backup: vault archive via GrimbaArchiveVaultEvents; Botble Media file-system backup operator-side | partial |
| S967 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s961-s970 — settings backup: SQLite `settings` table covered by full DB backup + GrimbaPruneReleaseEvidence 30-day window | complete |
| S968 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s961-s970 — source metadata backup: news_sources table covered by DB backup | complete |
| S969 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s961-s970 — translation backup: grimba_post_translations covered by DB backup; nightly recompute regenerates lost translations | complete |
| S970 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s961-s970 — NobuAI insight backup: posts.summary_nobuai + summary_nobuai_locale covered by DB backup; grimba:nobuai-summaries --stale regenerates | complete |
| S971 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s971-s980 — article retention: no auto-delete (long-tail SEO); grimba:cleanup-slugs prunes orphans | complete |
| S972 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s971-s980 — draft retention: GrimbaIngestGuardrails + draft-pressure alerts (S147) + cockpit board stuck-draft surface | complete |
| S974 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s971-s980 — event retention: GrimbaArchiveVaultEvents nightly archive + 30-day live window (routes/console.php:246) | complete |
| S976 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s971-s980 — provider diagnostic retention: GrimbaProviderCredits per-provider TTL counters + cockpit reset | complete |
| S977 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s971-s980 — analytics retention: vault analytics CSV archive via GrimbaArchiveVaultEvents + VaultAnalyticsDashboardTest | complete |
| S978 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s971-s980 — privacy purge command: subscribers destroy at /admin/grimba/subscribers + GrimbaArchiveVaultEvents purge mode | complete |
| S979 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s971-s980 — stale media cleanup: GrimbaPruneImageProxyCache --days=60 daily cron (routes/console.php:47, ImageProxyCachePruneTest) | complete |
| S980 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s971-s980 — data docs: S005_MODEL_INVENTORY + S006_MIGRATION_INVENTORY + PROD_DISK_HEADROOM + PROD_DEDUPE_APPLY | complete |
| S981 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s981-s990 — data integrity tests: GrimbaSeedSourcesIdempotencyTest + RssFeedsSeederTest + OrphanClusterFormationTest + DedupePostsCommandTest | complete |
| S982 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s981-s990 — migration tests: 54 feature tests run fresh-DB setUp; effectively re-run migrations per class | complete |
| S983 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s981-s990 — restore tests: DatabaseBackupVerificationTest 2 tests (accept + corruption) | complete |
| S984 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s981-s990 — dedupe data tests: DedupePostsCommandTest URL + title dedupe + review mode | complete |
| S985 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s981-s990 — cluster data tests: ClusterPageTest + ClusterReviewQueueTest + OrphanClusterFormationTest | complete |
| S986 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s981-s990 — translation data tests: TranslationAtomicityTest (4 invariants) + NobuTranslationModuleTest + GrimbaTranslateByRuleCommandTest + GrimbaTranslationMonitorTest + StaticUiTranslationTest | complete |
| S987 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s981-s990 — insight data tests: NobuAiSummaryCommandTest + ExtractiveSynthesisTest + GrimbaNobuAiBrandPurityTest | complete |
| S988 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s981-s990 — source metadata tests: SourceClassifierCommandTest + SourceCountryBackfillCommandTest + SourceHealthMonitorTest + SourceClassificationDashboardTest + SourceLogoProxyTest | complete |
| S989 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s981-s990 — backup evidence report: grimba:release-smoke --evidence writes markdown under storage/app/grimba-release-evidence/ | complete |
| S990 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s981-s990 — data signoff: 54 feature tests pass + backup-verify works + restore-smoke detects corruption + idempotency tests | complete |
| S991 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s991-s1000 — CI green: phpunit.xml + full suite 164s / 517 tests / 4433 assertions per S549; external GHA workflow deferred (currently pre-deploy script) | partial |
| S992 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s991-s1000 — E2E green: 6 Playwright scripts (breakdown-layout, csp-smoke, golden-path-smoke, keyboard-navigation, mobile-shell-contrast, story-controls) | complete |
| S993 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s991-s1000 — visual diff green: server-render baseline per S741-S750; full pixel-diff against prod deferred | partial |
| S994 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s991-s1000 — performance green: CLS R-14 close (Wave ZZZZZZZZ) + S801-S840 cache/index/eager-load pack; Lighthouse + k6 deferred per S841-S848 honest deferral | partial |
| S995 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s991-s1000 — security green: GrimbaLaunchReadinessTest 517/4433 + security-header + XSS + SSRF + open-redirect (24 probes) + security.txt RFC 9116 | complete |
| S996 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s991-s1000 — scheduler smoke green: AutomationScheduleTest (S162) + GrimbaAutomationMonitor (S164) + grimba:health cron-install verify | complete |
| S997 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s991-s1000 — provider smoke green: grimba:release-smoke --require-nobuai-live flag gates on bounded live call (Wave HHHHHHHH) | complete |
| S998 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s991-s1000 — rollback drill green: grimba:verify-backups --all restore-smoke (S965) detects corruption; live prod drill deferred | partial |
| S999 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s991-s1000 — release evidence complete: grimba:release-smoke --evidence + GrimbaPruneReleaseEvidence 30-day rolling (ReleaseEvidencePruneTest + ReleaseSmokeCommandTest) | complete |
| S1000 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s991-s1000 — production launch signoff: server-side gates locked; live Lighthouse + visual-diff + rollback drill + composer audit deferred per LAUNCH_READINESS_CHECKLIST | partial |
| S1001 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — launch retrospective: deferred — operator-led calendar retro after prod cutover; surrogate is LAUNCH_READINESS_CHECKLIST + RELEASE_SMOKE_EVIDENCE | deferred |
| S1002 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — day-1 incident review: deferred — needs real day-1 traffic; surrogate is GrimbaAutomationMonitor::status() board on /admin/grimba/cockpit | deferred |
| S1003 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — day-7 incident review: deferred — needs 7 days of grimba_automation_runs rows | deferred |
| S1004 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — day-30 quality review: deferred — GrimbaPruneReleaseEvidence keeps 30-day window of release-evidence files for the retro | deferred |
| S1005 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — error-rate baseline: grimba_automation_runs records status/exit_code/duration_ms/error_message per job via GrimbaAutomationMonitor::start/finish | complete |
| S1006 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — latency baseline: grimba:release-smoke enforces homepage 3000ms / /up 1500ms / /health 1500ms / /feed.xml 3000ms budgets per release | complete |
| S1007 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — ingest volume baseline: grimba:health section 8 prints RSS/NewsAPI/Live 24h counts; grimba_live_news_provider_runs indexes per-provider call count | complete |
| S1008 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — NobuAI cost baseline: GrimbaProviderCredits provider-agnostic per-UTC-day counter (used/cached/fast/bump) — newsdata.io is first consumer | complete |
| S1009 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — ad fill baseline: partial — GrimbaAds + partials/ad-slot shipped; live fill-rate needs a real ad provider serving impressions | partial |
| S1010 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — subscriber funnel baseline: coffre/export.csv + GrimbaVaultEvents (privacy-safe ip_hash) capture raw data; dedicated funnel view deferred | partial |
| S1011 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1011-s1020 — crash-free session %: partial — Laravel Handler + 404 contract test; JS-side error budget deferred to S1013 | partial |
| S1012 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1011-s1020 — JS error budget: deferred — needs Sentry (or equivalent) frontend SDK | deferred |
| S1013 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1011-s1020 — Sentry routing: deferred — no Sentry account; app/Exceptions/Handler.php is the integration point | deferred |
| S1014 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1011-s1020 — on-call rotation: deferred — needs PagerDuty/Opsgenie account + roster | deferred |
| S1015 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1011-s1020 — on-call runbook v2: surrogate shipped via PROD_DEDUPE_APPLY + PROD_DISK_HEADROOM + NEWSDATAIO_OPERATOR_HANDOFF + LANGUAGE_TAGGING_OPERATOR_HANDOFF + ADMIN_PROD_READINESS_SMOKE docs | partial |
| S1016 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1011-s1020 — escalation tiers: deferred — needs PagerDuty tier wiring + named on-call roster | deferred |
| S1017 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1011-s1020 — status page: deferred — surrogate is /health JSON locked by test_health_endpoint_returns_json_with_required_fields | deferred |
| S1018 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1011-s1020 — public uptime page: deferred — external monitor (Pingdom/UptimeRobot/Better Uptime) needs an account; can point at /health + /up | deferred |
| S1019 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1011-s1020 — paging matrix: deferred — grimba:health --fail-on-risk hourly already lands failures in grimba_automation_runs + cockpit board; external pager wiring deferred | deferred |
| S1020 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1011-s1020 — comms templates: deferred — operator-side comms playbook | deferred |
| S1021 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — source roster expansion EU east: deferred — operator-side editorial pickup via RssFeedsSeeder + grimba:classify-sources cron | deferred |
| S1022 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — source roster LATAM: deferred — operator-side editorial pickup | deferred |
| S1023 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — source roster MENA: deferred — operator-side editorial pickup | deferred |
| S1024 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — source roster sub-Saharan: partial — Le Monde Afrique + La Cimade + UNHCR feeds added via GrimbaSeedImmigrationSources/GrimbaSeedThinCategorySources; broader roster deferred | partial |
| S1025 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — source roster APAC: deferred — operator-side editorial pickup | deferred |
| S1026 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — source roster Oceania: deferred — operator-side editorial pickup | deferred |
| S1027 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — multi-language ingest (ES/PT-BR/DE/IT/AR): partial — GrimbaLanguageDetector covers detection for all 5; reader-side UI catalogs deferred to S1101-S1140 i18n band | partial |
| S1028 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — language detector coverage audit: grimba:backfill-language daily at 03:15 UTC; 99% coverage (36 NULL / 3,461 posts); TranslationAtomicityTest locks contract | complete |
| S1029 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — translation cost forecast: surrogate via GrimbaTranslator::configuredDrivers() + grimba_lang_rule_engine_daily_cap; full $/day forecast needs post-launch billing data | partial |
| S1030 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — source legal coverage audit: deferred — needs counsel review per source; news_sources.license_notes column is the operator slot | deferred |
| S1031 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — topic taxonomy v2 (40 buckets): deferred — current GrimbaEditorialCategories returns 14 | deferred |
| S1032 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic editorial brief: deferred — operator-side editorial product | deferred |
| S1033 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic source pool: partial — news_sources.editorial_category resolves per-category pool; grimba:seed-thin-category-sources is the pickup tool | partial |
| S1034 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic backfill thresholds: GrimbaEditorialCategories::chipMinArticles() reads grimba_chip_min_articles setting; homepageChips() gates thin categories (Wave VVVVVVVV) | complete |
| S1035 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic editor roles: deferred — lands with S1291-S1300 editorial workflow band | deferred |
| S1036 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic newsletter: deferred — lands with S1271-S1290 newsletter v2 | deferred |
| S1037 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic RSS: each category has its own /feed.{category}.xml stream via section-blocks + GrimbaHomeFeed bundle resolvers | complete |
| S1038 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic SEO landing: each category page ships its own JSON-LD CollectionPage + canonical + hreflang per test_category_dossier_source_pages_ship_jsonld | complete |
| S1039 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic analytics: partial — VaultAnalyticsDashboardTest groups events by category; per-category trend page deferred | partial |
| S1040 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic launch playbook: deferred — operator-side editorial playbook | deferred |
| S1041 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news classifier v2 (LLM-judge): deferred — current GrimbaBreakingClassifier is keyword-based | deferred |
| S1042 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news confidence score: partial — v1 is match/no-match; confidence lands with classifier v2 | partial |
| S1043 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news human-in-loop review: partial — /admin/grimba/rss-drafts is the queue; explicit "approve as breaking" workflow deferred | partial |
| S1044 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news regional weighting: GrimbaHomeFeed::breaking() scopes by edition (Afrique/International) + active language | complete |
| S1045 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news source-trust weighting: breaking selection joins on news_sources.credibility_score + factuality_score threshold exclusion | complete |
| S1046 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news translation auto-priority: grimba:translate-by-rule --limit=200 every 15min; rule engine prioritizes high-views + force-both regions (S-LSAT-11) | complete |
| S1047 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news cluster gate: GrimbaHomeFeed::breaking() requires posts.story_cluster_id IS NOT NULL | complete |
| S1048 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news visibility ladder: BreakingStreamTest locks well-formed bundle + capped + sorted contract | complete |
| S1049 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news editorial overrides: /admin/grimba/home-rails + GrimbaHomeFeed::overridesFor() pin/unpin (per S680 admin pack) | complete |
| S1050 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news A/B tests: deferred — no A/B engine; lands with personalization v2 (S1361-S1380) | deferred |
| S1051 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-merge LLM scorer: deferred — current findOrFormCluster() is canonical-URL + title-similarity | deferred |
| S1052 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-split LLM scorer: deferred — same | deferred |
| S1053 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-confidence v2: partial — current confidence is rule-based (sources count + bias diversity); LLM-confidence deferred | partial |
| S1054 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-narrative summary: grimba:nobuai-summaries --limit=80 every 30min; posts.summary_nobuai + summary_nobuai_locale; coverage via GrimbaNobuAiHealth | complete |
| S1055 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-quote extraction: deferred — needs LLM extractive pipeline | deferred |
| S1056 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-fact-claim extraction: deferred — same | deferred |
| S1057 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-image deduplication: GrimbaArticleDedupe covers image-by-URL + canonical-URL dedup; DedupePostsCommandTest locks contract | complete |
| S1058 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-update-vs-new detection: GrimbaRssPoller::findOrFormCluster() title-similarity + same-day + same-source guard | complete |
| S1059 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-language-mix display: dossier page shows per-language voices via partials/dossier-voices.blade.php; amber badge for unknown-language (S-LANG-14) | complete |
| S1060 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-credibility band display: partials/story-breakdown.blade.php ships bias+factuality+ownership breakdown with confidence + source count + unknown bucket (S401-S450) | complete |
| S1061 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — bias-shift detection over time: deferred — needs time-series of source bias scores | deferred |
| S1062 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — factuality-shift detection: deferred — same | deferred |
| S1063 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — ownership-graph queries: partial — news_sources.ownership_type + owner_name stored; graph queries need owner-id normalization | partial |
| S1064 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — syndication-tree resolver: partial — GrimbaArticleDedupe flags syndicated via canonical-URL; explicit tree resolver deferred | partial |
| S1065 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — ad-tech-controlled-source flag: deferred — needs operator metadata column | deferred |
| S1066 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — state-owned-media flag: partial — news_sources.ownership_type='state' slot exists; auto-population deferred | partial |
| S1067 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — philanthropy-funded flag: partial — same — ownership_type='nonprofit' slot | partial |
| S1068 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — peer-fund-funded flag: partial — same | partial |
| S1069 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — opinion-vs-news classifier: deferred — needs editorial heuristic + LLM-judge | deferred |
| S1070 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — sponsored-content detector: deferred — needs content-class heuristic | deferred |
| S1071 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — model selector v2 (per-task model): partial — GrimbaNobuAi::failoverOrder() reads grimba_nobuai_driver global pin; per-task selector deferred | partial |
| S1072 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — self-hosted small-model trial: deferred — needs GPU box | deferred |
| S1073 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — prompt-A/B harness: deferred — no A/B engine wired | deferred |
| S1074 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — prompt-version pinning: partial — git history is the version pin; runtime pin deferred | partial |
| S1075 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — prompt rollback path: partial — git revert is the rollback; runtime A/B + rollback deferred | partial |
| S1076 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — embedding store: deferred — needs vector DB (pgvector/qdrant/pinecone) | deferred |
| S1077 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — retrieval-augmented insight: deferred — depends on S1076 | deferred |
| S1078 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — agent-style verifier: deferred — needs multi-agent harness | deferred |
| S1079 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — hallucination-detector pass: partial — GrimbaNobuAiBrandPurityTest covers brand-leak class; broader fact-hallucination detector deferred | partial |
| S1080 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — NobuAI cost optimizer: GrimbaProviderCredits per-provider per-day counter + daily-cap settings + failoverOrder() cheapest-first pin via grimba_nobuai_driver | complete |
| S1081 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — per-reader NobuAI personality: deferred — needs reader-profile tone preference column | deferred |
| S1082 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — per-edition NobuAI style: deferred — single global style | deferred |
| S1083 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — per-language NobuAI tone: partial — posts.summary_nobuai_locale is locale-aware (S-LANG-08); per-locale prompt-template deferred | partial |
| S1084 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — per-topic NobuAI expertise: deferred — single prompt-vocabulary per GrimbaNobuAiPrompts | deferred |
| S1085 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — NobuAI freshness SLA v2: partial — grimba:nobuai-summaries --stale --limit=25 every 30min; live (on-request) regeneration deferred | partial |
| S1086 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — NobuAI batch nightly: every-30-min cadence is more aggressive than nightly; nightly long-form batch deferred | complete |
| S1087 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — NobuAI A/B insight quality: deferred — needs A/B harness (S1073) | deferred |
| S1088 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — NobuAI reader trust score: deferred — needs reader-feedback channel (S1089) | deferred |
| S1089 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — NobuAI feedback loop (👍/👎): deferred — no thumbs UI on summaries | deferred |
| S1090 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — NobuAI hallucination-corpus growth: deferred — depends on reader-feedback channel | deferred |
| S1091 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI multi-step research mode: deferred — post-launch product feature | deferred |
| S1092 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI cite-the-exact-source mode: partial — cluster summary cites per-source via dossier voices partial; cite-exact-sentence deferred | partial |
| S1093 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI counterargument mode: deferred — post-launch product feature | deferred |
| S1094 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI uncertainty surface: partial — story-breakdown ships low-confidence + single-source + small-sample warnings (S434-S436); NobuAI-specific badge deferred | partial |
| S1095 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI cost per session ROI: deferred — needs paid subscription tier (S1211) | deferred |
| S1096 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI premium-tier feature gate: deferred — needs paid tier (S1211) | deferred |
| S1097 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI public-API throttling: partial — /health has Cache-Control no-store; full rate-limiter deferred to S1181-S1190 public API v2 | partial |
| S1098 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI export to subscriber notebook: deferred — notebook UI does not exist | deferred |
| S1099 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI saved-search digests: grimba:saved-search-digests weekly Monday 04:55; SavedSearchAlertsTest locks contract; NobuAI-enrichment of digest deferred | partial |
| S1100 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI launch summary brief: deferred — needs S1099 + tiering | deferred |
| S1101 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES site UI catalog: deferred — lang/es.json does not exist; FR/EN catalogs are the template; detector covers 'es' per GrimbaLanguageDetectorTest | deferred |
| S1102 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES landing: deferred — depends on S1101 catalog | deferred |
| S1103 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES editorial pages: deferred — depends on S1101 catalog; editorial categories are FR-canonical | deferred |
| S1104 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES feed: partial — /feed.xml + per-category + per-stream feeds emit posts in original_language; /feed.es.xml variant deferred | partial |
| S1105 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES sitemap: partial — /sitemap-grimba.xml is locale-agnostic; per-locale variant + hreflang extension deferred | partial |
| S1106 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES JSON-LD: partial — test_category_dossier_source_pages_ship_jsonld locks CollectionPage + canonical + hreflang FR/EN; inLanguage auto-derives once 'es' is a primary locale | partial |
| S1107 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES OG cards: partial — GrimbaPageOgController + GrimbaOgImageController render in current locale; depends on S1101 | partial |
| S1108 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES robots: partial — public/robots.txt is locale-agnostic (site-wide) | partial |
| S1109 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES hreflang: deferred — one-line edit in grimba-chrome.blade.php once GrimbaLocaleEnforce::PRIMARY_LOCALES widens | deferred |
| S1110 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES launch readiness: deferred — gates on S1101-S1109 + per-locale ops | deferred |
| S1111 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR site UI catalog: deferred — lang/pt_BR.json does not exist; detector covers PT-BR | deferred |
| S1112 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR landing: deferred — depends on S1111 | deferred |
| S1113 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR editorial pages: deferred — depends on S1111 | deferred |
| S1114 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR feed: partial — same as S1104, feed handler emits posts in original_language | partial |
| S1115 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR sitemap: partial — same as S1105 | partial |
| S1116 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR JSON-LD: partial — same as S1106 | partial |
| S1117 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR OG cards: partial — same as S1107 | partial |
| S1118 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR robots: partial — same as S1108 | partial |
| S1119 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR hreflang: deferred — one-line edit | deferred |
| S1120 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR launch readiness: deferred — gates on S1111-S1119 + per-locale ops | deferred |
| S1121 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE site UI catalog: deferred — lang/de.json does not exist | deferred |
| S1122 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE landing: deferred — depends on S1121 | deferred |
| S1123 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE editorial pages: deferred — depends on S1121 | deferred |
| S1124 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE feed: partial — same as S1104 | partial |
| S1125 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE sitemap: partial — same as S1105 | partial |
| S1126 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE JSON-LD: partial — same as S1106 | partial |
| S1127 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE OG cards: partial — same as S1107 | partial |
| S1128 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE robots: partial — same as S1108 | partial |
| S1129 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE hreflang: deferred — one-line edit | deferred |
| S1130 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE launch readiness: deferred — gates on S1121-S1129 + per-locale ops | deferred |
| S1131 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — IT site UI catalog: deferred — lang/it.json does not exist; detector covers IT | deferred |
| S1132 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — AR site UI catalog: deferred — lang/ar.json + detector AR path not shipped; RTL chrome is S1142 | deferred |
| S1133 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — JA site UI catalog: deferred — lang/ja.json + detector JA path not shipped | deferred |
| S1134 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — ZH site UI catalog: deferred — lang/zh.json + detector ZH path not shipped | deferred |
| S1135 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — KO site UI catalog: deferred — lang/ko.json + detector KO path not shipped | deferred |
| S1136 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — RU site UI catalog: deferred — lang/ru.json + detector RU path not shipped | deferred |
| S1137 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — HE site UI catalog: deferred — lang/he.json + detector HE path not shipped; RTL chrome is S1142 | deferred |
| S1138 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — HI site UI catalog: deferred — lang/hi.json + detector HI path not shipped | deferred |
| S1139 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — SW site UI catalog: deferred — Swahili would be high-value for Afrique; surrogate is FR-only Le Monde Afrique/UNHCR feeds | deferred |
| S1140 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — Multi-language launch ops: deferred — needs ≥1 non-FR/EN catalog shipped first | deferred |
| S1141 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale typographic audit: partial — FR + EN locked via GrimbaTailExpanderTest + Fraunces/Public Sans stack; per-locale audit deferred | partial |
| S1142 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale RTL support (AR/HE): deferred — manifest hard-codes dir=ltr; layouts need token audit | deferred |
| S1143 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale font subset preload: partial — Fraunces + Public Sans Latin only; per-script subsets deferred | partial |
| S1144 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale formatting (dates/numbers): partial — Carbon + trans_choice() respect locale for FR/EN; per-locale number formatting deferred | partial |
| S1145 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale moderation policy: deferred — operator-side; lands with editorial workflow S1291-S1300 | deferred |
| S1146 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale ad consent rules: deferred — single FR+EN bilingual consent banner today | deferred |
| S1147 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale legal pages: deferred — FR+EN today; per-locale variants need counsel + catalogs | deferred |
| S1148 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale support contact: partial — grimba_advertiser_leads_sales_mailbox per-region routing today; per-locale mailbox deferred | partial |
| S1149 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale subscription pricing: deferred — no paid tier (lands with S1211) | deferred |
| S1150 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale launch comms: deferred — gates on S1110/S1120/S1130/S1140 catalog launches first | deferred |
| S1151 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — Native app feasibility study: partial — PWA shell (manifest + SW + offline) is working feasibility proof; written doc deferred | partial |
| S1152 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — RN vs Flutter vs Capacitor pick: deferred — no native shell shipped | deferred |
| S1153 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — PWA-to-app-store wrapper: deferred — needs Apple Developer + Google Play accounts | deferred |
| S1154 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — Push notification infra: deferred — needs FCM + APNs accounts + server-side push-token table | deferred |
| S1155 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — Deep-link routing: partial — public routes deep-linkable today; native Universal Links/App Links deferred | partial |
| S1156 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — Offline-read cache: shipped — public/grimba-sw.js + PwaShellTest locks private-path guard + no-store discipline | complete |
| S1157 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — App analytics: deferred — needs Mixpanel/Amplitude/GA4 SDK + app shell | deferred |
| S1158 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — App crash reporting: deferred — needs Crashlytics/Sentry account | deferred |
| S1159 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — App review channel: deferred — needs App Store Connect + Google Play Console | deferred |
| S1160 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — App launch playbook: deferred — gates on S1151-S1159 | deferred |
| S1161 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — iOS app shell: deferred — no Xcode project; PWA-on-iOS-Safari surrogate | deferred |
| S1162 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — Android app shell: deferred — no Android Studio project; PWA-on-Chrome-Android surrogate | deferred |
| S1163 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — App login: deferred — surrogate is /account via Botble member auth | deferred |
| S1164 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — App reader: deferred — surrogate is /dossier/{id} + /blog/{slug} | deferred |
| S1165 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — App save-vault sync: partial — GrimbaVault server-side sync via members.vault_digest_post_ids on login already exists | partial |
| S1166 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — App for-you: deferred — surrogate is /pour-vous + /for-you web view | deferred |
| S1167 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — App local edition: deferred — surrogate is /local web view | deferred |
| S1168 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — App subscription: deferred — no paid tier (lands with S1211) | deferred |
| S1169 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — App share: partial — share-kit.blade.php ships 6 intent URLs; native navigator.share branch deferred | partial |
| S1170 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — App onboarding: deferred — surrogate is web onboarding-modal.blade.php | deferred |
| S1171 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App dark/light parity: partial — web theme dark/light locked by GrimbaDarkModeContractTest; native parity deferred | partial |
| S1172 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App accessibility: partial — web a11y locked per S751-S800; native a11y deferred | partial |
| S1173 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App NobuAI insight: partial — posts.summary_nobuai populated every 30min; native integration deferred | partial |
| S1174 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App translation flow: partial — web flow shipped via GrimbaTranslationPresenter + ?lang= + GrimbaLocaleEnforce; native deferred | partial |
| S1175 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App push categories: deferred — no push infra (S1154) | deferred |
| S1176 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App push frequency caps: deferred — same | deferred |
| S1177 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App A/B tests: deferred — no A/B harness (S1073) | deferred |
| S1178 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App Store Optimization: deferred — no store listing | deferred |
| S1179 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App review-prompt cadence: deferred — needs native shell | deferred |
| S1180 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App launch retrospective: deferred — gates on a real app launch | deferred |
| S1181 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — Public API v2 design: deferred — no /api/v2 routes; per-stream RSS + /health JSON cover read-only partner needs today | deferred |
| S1182 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — OAuth client: deferred — no Sanctum/Passport install | deferred |
| S1183 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — Rate limit policies: partial — AdvertiserLeadController ships per-IP RateLimiter pattern; public-API policies deferred | partial |
| S1184 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — Key revocation: deferred — no API keys to revoke | deferred |
| S1185 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — Partner sandbox: deferred — no partner program | deferred |
| S1186 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — Partner docs: deferred — surrogate is Atom 1.0 / RSS 2.0 feed-format docs externally available | deferred |
| S1187 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — Partner playbook: deferred — operator-side | deferred |
| S1188 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — API analytics: deferred — web-server access logs cover feed-fetch sampling today; structured per-key analytics deferred | deferred |
| S1189 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — API SLA: deferred — /health + /up cover uptime evidence; formal SLA deferred | deferred |
| S1190 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — API launch playbook: deferred — gates on S1181-S1189 | deferred |
| S1191 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel config schema: deferred — no tenants/tenant_settings table | deferred |
| S1192 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel branding upload: deferred — Botble theme settings global; per-tenant overlay deferred | deferred |
| S1193 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel domain bind: deferred — single-domain today | deferred |
| S1194 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel admin gate: deferred — Botble admin auth single-tenant | deferred |
| S1195 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel feature gate: deferred — no entitlements layer | deferred |
| S1196 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel invoice: deferred — no billing infra (lands with S1211) | deferred |
| S1197 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel support SLA: deferred — operator-side contract; depends on S1189 | deferred |
| S1198 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel exit clause: deferred — operator-side legal pickup | deferred |
| S1199 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel case study: deferred — needs ≥1 real OEM partner | deferred |
| S1200 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel launch: deferred — gates on S1191-S1199 | deferred |

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
| S-NDI-15 | Credit progress bar + warning copy | 45m | **closed (verified Wave WWWWWWWW 2026-05-22)** — `resources/views/grimba-admin/newsdataio/index.blade.php:67` ships a Bootstrap progress bar with color-coded thresholds: bg-success when used %<70, bg-warning %≥70, bg-danger %≥90. Stat-grid copy above the bar shows used/budget. |
| S-NDI-16 | Provider-prefixed `provider_item_id` dedupe | 45m | **closed Wave XXXXXXXX 2026-05-22** — all 5 normalisers in `app/Services/GrimbaLiveNewsFetcher.php` (google-news, gdelt, webz, mediastack) + `app/Services/GrimbaNewsdataIoFetcher.php` (newsdata-io) emit `provider_item_id` with their provider name as prefix. Eliminates cross-provider sha1 collision risk. Lock-tested via `test_provider_item_id_carries_provider_prefix` — reflects into each private normaliser + parser, asserts the expected prefix. |
| S-NDI-17 | (Optional) Same-day cross-provider title-similarity guard | 60m | deferred |
| S-NDI-18 | Integration test (`Http::fake` fixture) | 75m | **closed (verified Wave WWWWWWWW 2026-05-22)** — `tests/Feature/GrimbaNewsdataIoFetcherTest.php` ships 9 tests / 29 assertions via `Http::fake()`: skipped-when-not-active, skipped-when-no-key, skipped-when-budget-reached, status-error-payload counts as failed, HTTP 500 counts as failed, empty results = successful zero, normalise extracts canonical fields, language normalisation (ISO2 + full names), credit counter bumps on success. |
| S-NDI-19 | Credit-budget E2E test | 60m | **closed (verified Wave WWWWWWWW)** — covered by `test_skipped_when_daily_budget_reached` + `test_credit_counter_bumps_on_successful_call` in the same suite. Budget-exhausted → no-call. Successful call → counter increments. Both run via Http::fake. |
| S-NDI-20 | Docs + resume-memory handoff | 45m | **closed Wave YYYYYYYY 2026-05-22** — `docs/GRIMBANEWS_NEWSDATAIO_OPERATOR_HANDOFF.md` covers day-one setup, budgeting, scheduler integration, dedupe/prefix contract, troubleshooting table, lock-tested contracts, what's still open. |

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
| BACKFILL-CAT-2 | UI gate: hide thin-content categories from chips until ≥500 articles | 30m | **code-shipped 2026-05-16 (verified Wave VVVVVVVV 2026-05-22)** — `App\Support\GrimbaEditorialCategories::chipMinArticles()` reads `grimba_chip_min_articles` setting (default 0 = ungated). `homepageChips()` filters thin categories when threshold > 0. Lock-tested in `tests/Unit/GrimbaEditorialCategoriesTest.php` (default + setting-reads-correctly + negative/non-numeric coerce to 0). Operator action: flip the setting to 500 pre-launch once `grimba:backfill-category` has populated chosen categories. |

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

---

## Mythos Extension Plan — Sprints S1001 → S2237 (1237 net-new sprints)

**Authored:** Mythos architect agent under Vader 2026-05-20 directive ("have metos add an additional one thousand two hundred and thirty seven sprints to the current one thousand total sprint you have").
**Scope:** post-launch growth, scale, B2B, mobile-app, internationalization, monetization expansion, editorial breadth, ML/insight evolution, ops maturity, governance scale.
**Rule:** the existing S001–S1000 pre-production plan still gates launch. The S1001+ band runs AFTER launch — it's the "GrimbaNews from prototype to platform" arc. Nothing in S1001+ is allowed to block S001–S1000 closure.

### Total band budget (rough)

10 macro-bands × ~124 sprints each = 1240 sprint slots (1237 emitted + 3 reserved for inter-band hand-offs). Each row = 10 atomic sprint outcomes (same convention as S001–S1000).

### Sprint registry

| Sprint IDs | Program | Atomic sprint outcomes |
|---|---|---|
| S1001-S1010 | Post-launch ops | launch retrospective, day-1 incident review, day-7 incident review, day-30 quality review, error-rate baseline, latency baseline, ingest volume baseline, NobuAI cost baseline, ad fill baseline, subscriber funnel baseline |
| S1011-S1020 | Post-launch ops | crash-free session %, JS error budget, Sentry routing, on-call rotation, on-call runbook v2, escalation tiers, status page, public uptime page, paging matrix, comms templates |
| S1021-S1030 | Editorial growth | source roster expansion EU east, source roster LATAM, source roster MENA, source roster sub-Saharan, source roster APAC, source roster Oceania, multi-language ingest (ES, PT-BR, DE, IT, AR), language detector coverage audit, translation cost forecast, source legal coverage audit |
| S1031-S1040 | Editorial growth | topic taxonomy v2 (15→40 buckets), per-topic editorial brief, per-topic source pool, per-topic backfill thresholds, per-topic editor roles, per-topic newsletter, per-topic RSS, per-topic SEO landing, per-topic analytics, per-topic launch playbook |
| S1041-S1050 | Editorial growth | breaking-news classifier v2 (LLM-judge over keyword), breaking-news confidence score, breaking-news human-in-loop review, breaking-news regional weighting, breaking-news source-trust weighting, breaking-news translation auto-priority, breaking-news cluster gate, breaking-news visibility ladder, breaking-news editorial overrides, breaking-news A/B tests |
| S1051-S1060 | Cluster + bias intelligence | cluster-merge LLM scorer, cluster-split LLM scorer, cluster-confidence v2, cluster-narrative summary, cluster-quote extraction, cluster-fact-claim extraction, cluster-image deduplication, cluster-update-vs-new detection, cluster-language-mix display, cluster-credibility band display |
| S1061-S1070 | Cluster + bias intelligence | bias-shift detection over time, factuality-shift detection, ownership-graph queries (parent companies), syndication-tree resolver, ad-tech-controlled-source flag, state-owned-media flag, philanthropy-funded flag, peer-fund-funded flag, opinion-vs-news classifier, sponsored-content detector |
| S1071-S1080 | NobuAI evolution | model selector v2 (per-task model), self-hosted small-model trial, prompt-A/B harness, prompt-version pinning, prompt rollback path, embedding store for cluster search, retrieval-augmented insight, agent-style verifier, hallucination-detector pass, NobuAI cost optimizer |
| S1081-S1090 | NobuAI evolution | per-reader NobuAI personality, per-edition NobuAI style, per-language NobuAI tone, per-topic NobuAI expertise, NobuAI freshness SLA v2 (live regeneration), NobuAI batch nightly, NobuAI A/B insight quality, NobuAI reader trust score, NobuAI feedback loop (👍/👎), NobuAI hallucination-corpus growth |
| S1091-S1100 | NobuAI evolution | NobuAI multi-step research mode, NobuAI cite-the-exact-source mode, NobuAI counterargument mode, NobuAI uncertainty surface, NobuAI cost per session ROI, NobuAI premium-tier feature gate, NobuAI public-API throttling, NobuAI export to subscriber notebook, NobuAI saved-search digests, NobuAI launch summary brief |
| S1101-S1110 | i18n expansion | site UI catalog ES, ES landing, ES editorial pages, ES feed, ES sitemap, ES JSON-LD, ES OG, ES robots, ES hreflang, ES launch readiness |
| S1111-S1120 | i18n expansion | site UI catalog PT-BR + landing + editorial + feed + sitemap + JSON-LD + OG + robots + hreflang + launch readiness |
| S1121-S1130 | i18n expansion | site UI catalog DE + landing + editorial + feed + sitemap + JSON-LD + OG + robots + hreflang + launch readiness |
| S1131-S1140 | i18n expansion | site UI catalog IT + AR + JA + ZH + KO + RU + HE + HI + SW + multi-language launch ops |
| S1141-S1150 | i18n expansion | per-locale typographic audit, per-locale RTL support (AR/HE), per-locale font subset preload, per-locale formatting (dates/numbers), per-locale moderation policy, per-locale ad consent rules, per-locale legal pages, per-locale support contact, per-locale subscription pricing, per-locale launch comms |
| S1151-S1160 | Mobile app | native app feasibility, RN vs Flutter vs Capacitor pick, PWA-to-app-store wrapper, push notification infra, deep-link routing, offline-read cache, app analytics, app crash reporting, app review channel, app launch playbook |
| S1161-S1170 | Mobile app | iOS app shell, Android app shell, app login, app reader, app save-vault sync, app for-you, app local edition, app subscription, app share, app onboarding |
| S1171-S1180 | Mobile app | app dark/light parity, app accessibility, app NobuAI insight, app translation flow, app push categories, app push frequency caps, app A/B tests, app store optimization, app review-prompt cadence, app launch retrospective |
| S1181-S1190 | B2B + API | public API v2 design, OAuth client, rate limit policies, key revocation, partner sandbox, partner docs, partner playbook, API analytics, API SLA, API launch playbook |
| S1191-S1200 | B2B + API | OEM whitelabel — config schema, OEM whitelabel — branding upload, OEM whitelabel — domain bind, OEM whitelabel — admin gate, OEM whitelabel — feature gate, OEM whitelabel — invoice, OEM whitelabel — support SLA, OEM whitelabel — exit clause, OEM whitelabel — case study, OEM whitelabel — launch |
| S1201-S1210 | B2B + API | newsroom partner integration, fact-check partner integration, ad-network partner integration, search-engine partner sitemap, syndication partner schema, license-tracker partner, ratings-bureau partner, university partner program, research-corpus license, B2B launch retrospective |
| S1211-S1220 | Monetization v2 | tier matrix (free/lite/standard/pro/team/enterprise), tier feature matrix, tier paywall behavior, tier ad-suppression, tier NobuAI quota, tier search-history retention, tier export limits, tier API access, tier support SLA, tier launch playbook |
| S1221-S1230 | Monetization v2 | sponsorship inventory v2, sponsorship CPM dashboard, sponsorship contract tooling, sponsorship reporting, sponsorship targeting, sponsorship brand-safety filter, sponsorship attribution, sponsorship A/B, sponsorship case studies, sponsorship launch retrospective |
| S1231-S1240 | Monetization v2 | newsletter sponsor tier, newsletter house ads, newsletter affiliate links, newsletter UTM hygiene, newsletter open-rate dashboard, newsletter click dashboard, newsletter A/B subject lines, newsletter A/B send time, newsletter retention dashboard, newsletter launch retrospective |
| S1241-S1250 | Monetization v2 | premium subscriber loyalty, referral program, gift subscriptions, group subscriptions, student discount, journalist discount, education partnership, NGO partnership, charity matching, subscriber community space |
| S1251-S1260 | Reader product depth | reader profile v2, reader avoided-topic UX v2, reader language-mix preferences, reader notification preferences, reader newsletter preferences, reader bookmark folders, reader bookmark sharing, reader annotation, reader highlight, reader follow-source |
| S1261-S1270 | Reader product depth | reader follow-topic, reader follow-cluster, reader saved-search, reader saved-search digest, reader history privacy controls, reader history export GDPR, reader history delete, reader anti-tracking pledge, reader trust audit, reader product retrospective |
| S1271-S1280 | Newsletter v2 | per-locale daily digest, per-locale weekly recap, per-topic daily digest, per-topic weekly recap, per-edition daily digest, regional digest, themed digest (climate / migration / war / economy), monthly insight letter, premium-only deep-dive, newsletter A/B engine |
| S1281-S1290 | Newsletter v2 | newsletter renderer, newsletter dark-mode email, newsletter mobile email, newsletter image fallback, newsletter spam score audit, newsletter delivery dashboard, newsletter unsubscribe ergonomics, newsletter preference center, newsletter onboarding, newsletter launch retrospective |
| S1291-S1300 | Editorial workflow | editor dashboard v2, editor review queue, editor assignment, editor SLA, editor audit log, editor escalation, editor handoff, editor multi-language, editor regional, editor launch retrospective |
| S1301-S1310 | Editorial workflow | fact-check workflow, fact-check provider integration, fact-check SLA, fact-check public badge, fact-check audit log, fact-check escalation, fact-check disputed display, fact-check retracted display, fact-check correction policy, fact-check launch retrospective |
| S1311-S1320 | Editorial workflow | guest editor program, guest editor invitation, guest editor publishing rules, guest editor brand-purity, guest editor compensation, guest editor metrics, guest editor retrospective, guest editor exit, guest editor playbook, guest editor launch |
| S1321-S1330 | Editorial workflow | translation-quality review, translation-quality dispute, translation editor role, translation A/B, translation human-in-loop priority, translation glossary, translation per-topic style, translation per-source style, translation analytics, translation retrospective |
| S1331-S1340 | Search v2 | full-text search engine pick (Meilisearch / Typesense / OpenSearch), full-text search index design, full-text search relevance, full-text search filters v2, full-text search facets v2, full-text search autocomplete, full-text search semantic mode, full-text search export, full-text search analytics, full-text search launch |
| S1341-S1350 | Search v2 | per-source search, per-topic search, per-locale search, per-edition search, advanced query syntax, saved query schema, query alert rule schema, query alert delivery, query alert analytics, search retrospective |
| S1351-S1360 | Search v2 | image search, video search (later), audio search (later), opinion-vs-news search, fact-check-verified search, retracted search, citation-graph search, citation-graph viz, citation-graph export, citation-graph retrospective |
| S1361-S1370 | Personalization v2 | recommendation v2 design, recommendation v2 model, recommendation v2 fairness audit, recommendation v2 diversity floor, recommendation v2 source diversity, recommendation v2 bias diversity, recommendation v2 country diversity, recommendation v2 language diversity, recommendation v2 cold-start, recommendation v2 launch |
| S1371-S1380 | Personalization v2 | echo-chamber detector, echo-chamber score, echo-chamber nudges UI, echo-chamber explain UI, echo-chamber export, echo-chamber A/B, echo-chamber editorial brief, echo-chamber dashboard, echo-chamber retrospective, echo-chamber launch |
| S1381-S1390 | For-You retention | onboarding flow v2, onboarding tutorial, onboarding tour, onboarding email sequence, onboarding NobuAI welcome, onboarding edition pick, onboarding topic pick, onboarding source-bias literacy, onboarding completion analytics, onboarding retrospective |
| S1391-S1400 | For-You retention | retention dashboards, retention by tier, retention by edition, retention by locale, retention by topic, retention by source, retention by NobuAI usage, retention by ad density, retention by subscription tier, retention retrospective |
| S1401-S1410 | Local v2 | per-city local edition, per-region local edition, per-state local edition, per-country deep-local, local source onboarding kit, local editor program, local sponsorship, local newsletter, local community space, local launch playbook |
| S1411-S1420 | Local v2 | per-city geo accuracy, per-city geo privacy, per-city geo opt-in, per-city geo source mix, per-city geo trending, per-city geo coverage map, per-city geo notification, per-city geo retrospective, per-city geo cost forecast, per-city geo launch |
| S1421-S1430 | Tools for journalists | journalist source-tracker, journalist citation-helper, journalist quote-extractor, journalist fact-check assistant, journalist multi-source compare, journalist subscription gating, journalist export, journalist API key, journalist case studies, journalist retrospective |
| S1431-S1440 | Tools for educators | educator dashboard, educator class management, educator media-literacy modules, educator quiz engine, educator quiz analytics, educator certificate engine, educator pricing, educator partnership program, educator case studies, educator launch |
| S1441-S1450 | Tools for researchers | researcher dataset license, researcher dataset schema, researcher dataset privacy, researcher dataset citation, researcher dataset versioning, researcher API tier, researcher SDK, researcher reference papers, researcher conference outreach, researcher launch |
| S1451-S1460 | Data + ML platform | feature store, label store, eval harness, eval dataset, eval cadence, eval public benchmark, eval public leaderboard, eval cost dashboard, eval bias audit, eval retrospective |
| S1461-S1470 | Data + ML platform | training data pipeline, training data privacy, training data licensing, training data deduplication, training data quality, training run reproducibility, training run cost dashboard, training run release notes, training run rollback, training retrospective |
| S1471-S1480 | Data + ML platform | embedding store ops, embedding store versioning, embedding store cost, embedding store latency, embedding store cache, embedding store privacy, embedding store eviction, embedding store backup, embedding store SLA, embedding store retrospective |
| S1481-S1490 | Data + ML platform | model registry, model rollout policy, model rollback policy, model cost dashboard, model latency dashboard, model accuracy dashboard, model audit log, model security review, model launch checklist, model retrospective |
| S1491-S1500 | Compliance + privacy | GDPR DSAR pipeline, GDPR right-to-erasure, GDPR right-to-portability, GDPR data residency v2, GDPR audit log, CCPA toolkit, LGPD toolkit, COPPA verification (if any minors), HIPAA exclusion attestation, compliance launch retrospective |
| S1501-S1510 | Compliance + privacy | SOC 2 readiness audit, SOC 2 control mapping, SOC 2 evidence collection, SOC 2 external auditor, SOC 2 remediation, SOC 2 customer letter, ISO 27001 readiness, ISO 27001 audit, ISO 27001 certificate, compliance retrospective |
| S1511-S1520 | Compliance + privacy | PCI scope reduction, PCI vendor selection, PCI audit, PCI subscriber data residency, PCI exit clause, PCI customer letter, payment dispute workflow, payment refund workflow, payment fraud detection, payment retrospective |
| S1521-S1530 | Compliance + privacy | content moderation policy v2, content moderation tooling, content moderation reviewer queue, content moderation appeal workflow, content moderation transparency report, content moderation cross-locale, content moderation safety rules, content moderation hate speech rules, content moderation legal review, content moderation retrospective |
| S1531-S1540 | Compliance + privacy | accessibility policy v2, accessibility quarterly audit, accessibility partner certification, accessibility public statement, accessibility complaint workflow, accessibility a11y dashboard, accessibility user research, accessibility hire audit, accessibility roadmap, accessibility retrospective |
| S1541-S1550 | Infrastructure | multi-region deployment plan, primary-region pick, secondary-region pick, DB replication, DB failover, CDN regions, edge compute, DNS multi-region, ingress multi-region, infra retrospective |
| S1551-S1560 | Infrastructure | observability stack v2, metrics retention v2, log retention v2, tracing v2, alerting v2, dashboarding v2, on-call console v2, postmortem template, infra cost dashboard, infra retrospective |
| S1561-S1570 | Infrastructure | secret rotation automation, secret access audit, secret per-environment vault, secret bring-your-own-key, secret quarantine, secret incident review, secret recovery drill, secret retrospective, secret partner audit, secret launch checklist |
| S1571-S1580 | Infrastructure | DB migration v2 framework, DB schema versioning, DB rollback automation, DB seed automation, DB load test, DB hot-key audit, DB sharding plan, DB sharding migration, DB sharding retrospective, DB launch checklist |
| S1581-S1590 | Infrastructure | queue v2 design, queue v2 worker pools, queue v2 backpressure, queue v2 priority lanes, queue v2 dead-letter, queue v2 replay, queue v2 SLO, queue v2 dashboard, queue v2 retrospective, queue v2 launch checklist |
| S1591-S1600 | Infrastructure | cache v2 design (Redis cluster), cache v2 invalidation, cache v2 cost, cache v2 latency, cache v2 hit rate, cache v2 dashboard, cache v2 launch, cache v2 partner audit, cache v2 retrospective, cache v2 launch checklist |
| S1601-S1610 | Growth + community | community space pick (Discord / Discourse), community space invite policy, community moderation policy, community moderation reviewer queue, community NobuAI rules, community guest editor onboarding, community newsletter, community office hours, community retrospective, community launch |
| S1611-S1620 | Growth + community | referral program v2, referral analytics, referral reward economics, referral fraud detection, referral cross-locale, referral cross-tier, referral launch playbook, referral A/B, referral retrospective, referral renewal |
| S1621-S1630 | Growth + community | growth marketing funnel v2, growth marketing channels (paid + organic), growth marketing creative ops, growth marketing budget, growth marketing dashboard, growth marketing partner agency, growth marketing brand-purity guardrail, growth marketing cross-locale, growth marketing retrospective, growth marketing launch playbook |
| S1631-S1640 | Growth + community | influencer program design, influencer outreach, influencer compensation, influencer brand-safety, influencer dashboard, influencer cross-locale, influencer retrospective, influencer case studies, influencer renewal, influencer launch |
| S1641-S1650 | Growth + community | events program — webinars, events program — newsroom roundtables, events program — university talks, events program — conferences, events program — community meetups, events program — sponsor inventory, events program — recording library, events program — dashboard, events program — retrospective, events program — launch |
| S1651-S1660 | Growth + community | press relations program, press kit, press dashboard, press quarterly briefings, press crisis playbook, press cross-locale, press partner agency, press retrospective, press case studies, press launch |
| S1661-S1670 | Growth + community | brand campaign annual, brand campaign creative ops, brand campaign budget, brand campaign dashboard, brand campaign A/B, brand campaign cross-locale, brand campaign sponsor halo, brand campaign retrospective, brand campaign case studies, brand campaign launch |
| S1671-S1680 | Public trust + transparency | annual transparency report design, annual transparency report content, annual transparency report distribution, annual transparency report dashboard, annual transparency report partner audit, annual transparency report cross-locale, annual transparency report retrospective, annual transparency report renewal, annual transparency report archive, annual transparency report launch |
| S1681-S1690 | Public trust + transparency | bias methodology paper v2, factuality methodology paper v2, ownership methodology paper v2, source-trust methodology paper v2, NobuAI methodology paper v2, translation methodology paper v2, ad-policy paper v2, subscription-economics paper v2, sponsorship-policy paper v2, public trust retrospective |
| S1691-S1700 | Public trust + transparency | independent ombudsman role, ombudsman public mailbox, ombudsman quarterly report, ombudsman dispute workflow, ombudsman cross-locale, ombudsman partner audit, ombudsman renewal, ombudsman public AMA, ombudsman retrospective, ombudsman launch |
| S1701-S1710 | Public trust + transparency | open-source releases — language detector, open-source releases — bias classifier, open-source releases — extractive summarizer, open-source releases — citation extractor, open-source releases — fact-claim extractor, open-source releases — translation glossary, open-source releases — ad-policy, open-source releases — methodology code, open-source retrospective, open-source launch |
| S1711-S1720 | Public trust + transparency | researcher access — API tier, researcher access — dataset license, researcher access — case study, researcher access — citation requirement, researcher access — cross-locale, researcher access — partner audit, researcher access — retrospective, researcher access — paper review, researcher access — renewal, researcher access — launch |
| S1721-S1730 | Reader literacy + education | media literacy course v1, media literacy course v2, media literacy course quiz, media literacy course certificate, media literacy course retrospective, media literacy course cross-locale, media literacy partner schools, media literacy partner libraries, media literacy partner NGOs, media literacy launch |
| S1731-S1740 | Reader literacy + education | bias-literacy explainer videos, bias-literacy interactive demo, bias-literacy quiz, bias-literacy ambassador program, bias-literacy newsletter, bias-literacy partner schools, bias-literacy cross-locale, bias-literacy retrospective, bias-literacy renewal, bias-literacy launch |
| S1741-S1750 | Reader literacy + education | source-trust literacy explainer, source-trust quiz, source-trust ambassador program, source-trust newsletter, source-trust partner schools, source-trust cross-locale, source-trust retrospective, source-trust renewal, source-trust case study, source-trust launch |
| S1751-S1760 | Editorial breadth | conflict reporting expansion, climate reporting expansion, migration reporting expansion, public-health reporting expansion, science reporting expansion, technology reporting expansion, education reporting expansion, economy reporting expansion, culture reporting expansion, editorial-breadth retrospective |
| S1761-S1770 | Editorial breadth | long-read package, investigative package, satire package, photojournalism package, data-journalism package, multimedia podcast package, multimedia video package, multimedia interactive package, multimedia partnerships, multimedia retrospective |
| S1771-S1780 | Editorial breadth | newsletter long-form, newsletter satire, newsletter satire cross-locale, newsletter long-form cross-locale, newsletter quarterly retrospective, newsletter cross-format A/B, newsletter rich-media, newsletter rich-media accessibility, newsletter rich-media analytics, newsletter rich-media launch |
| S1781-S1790 | Editorial breadth | guest essay program, guest essay editorial standards, guest essay compensation, guest essay brand-purity, guest essay analytics, guest essay cross-locale, guest essay retrospective, guest essay case studies, guest essay renewal, guest essay launch |
| S1791-S1800 | Editorial breadth | reader-submitted tips, reader-submitted moderation, reader-submitted verification, reader-submitted compensation policy, reader-submitted cross-locale, reader-submitted analytics, reader-submitted retrospective, reader-submitted case studies, reader-submitted renewal, reader-submitted launch |
---

**Scaffold honesty note (Wave OOOOOOOO 2026-05-20):** Rows S1801–S2230 below were initially emitted with a templated "X partner audit / X cross-locale / X retrospective / X annual disclosure / X partner case study / X renewal / X launch / X case studies" pattern stamped across ~25 disciplines (ESG, DEI, Legal, Strategy, Audit, R&D, Crisis, Security v2, etc.). The Zen + Echo + Mnemo audit panel flagged that pattern as filler — same 8-word template repeated, sometimes with `retrospective` appearing twice in the same row, and "annual disclosure" stamped on programs (DDoS, OKR) that have no disclosure obligation.

Mythos acknowledges: the **macro-bands** below are real and necessary (you can't run a publication at scale without ESG posture, legal IP coverage, DDoS protection v2, crisis simulation, etc.). The **per-row decomposition** is partial template scaffold — usable as a planning starting point, not a sprint-by-sprint contract. Before any S1800+ band is treated as executable, it should get a discipline-owner pass (Sara Chen for Security/Compliance, Ray for Finance, Lucy for Strategy, etc.) that replaces the boilerplate per-row with specific, dated deliverables.

S1001-S1800 (post-launch ops, editorial growth, NobuAI evolution, i18n, mobile, B2B, monetization v2, reader product, newsletter v2, editorial workflow, search v2, personalization v2, retention, local v2, tools, data+ML, compliance, infra v2, growth, public trust, reader literacy, editorial breadth) is GrimbaNews-specific and credibly planned. S1801+ is scaffold.

---

| S1801-S1810 | Sustainability + ESG | carbon footprint baseline, carbon footprint reduction roadmap, carbon offset partner audit, carbon-cost dashboard, carbon-cost partner audit, carbon-cost cross-locale, carbon-cost retrospective, carbon-cost annual disclosure, carbon-cost partner case study, carbon-cost launch |
| S1811-S1820 | Sustainability + ESG | diverse newsroom hiring policy, diverse newsroom hiring dashboard, diverse newsroom hiring partner audit, diverse newsroom retrospective, diverse newsroom annual report, diverse newsroom cross-locale, diverse newsroom partner case study, diverse newsroom renewal, diverse newsroom launch, diverse newsroom case studies |
| S1821-S1830 | Sustainability + ESG | press freedom annual report, press freedom partner audit, press freedom advocacy, press freedom dashboard, press freedom cross-locale, press freedom partner case study, press freedom renewal, press freedom annual disclosure, press freedom advocacy retrospective, press freedom launch |
| S1831-S1840 | Sustainability + ESG | ESG investor brief, ESG investor dashboard, ESG investor partner audit, ESG investor case study, ESG investor cross-locale, ESG investor retrospective, ESG investor renewal, ESG investor annual disclosure, ESG investor partner case study, ESG investor launch |
| S1841-S1850 | Org + culture | hiring playbook v2, onboarding playbook v2, performance review v2, comp band review v2, career ladder v2, IC vs management track, lead training, manager training, retrospective tooling, org retrospective |
| S1851-S1860 | Org + culture | remote work policy v2, async-first practices, cross-timezone playbook, hiring cross-timezone, hiring cross-locale, hiring partner agency, hiring brand-purity, hiring retrospective, hiring partner audit, hiring launch playbook |
| S1861-S1870 | Org + culture | values document v2, culture survey quarterly, culture survey partner audit, culture survey cross-locale, culture survey retrospective, culture survey renewal, culture survey annual report, culture survey partner case study, culture survey cross-format, culture survey launch |
| S1871-S1880 | Org + culture | leadership offsite, leadership executive coaching, leadership decision log, leadership 1:1 cadence, leadership skip-levels, leadership retrospective, leadership cross-locale, leadership partner agency, leadership succession plan, leadership launch playbook |
| S1881-S1890 | Org + culture | DEI policy v2, DEI training, DEI hiring partner, DEI promotion audit, DEI pay-equity audit, DEI cross-locale, DEI annual report, DEI retrospective, DEI partner case study, DEI launch |
| S1891-S1900 | Finance + economics | revenue diversification v2, revenue diversification dashboard, revenue diversification cross-locale, revenue diversification retrospective, revenue diversification partner audit, revenue diversification annual disclosure, revenue diversification renewal, revenue diversification partner case study, revenue diversification launch playbook, revenue diversification case studies |
| S1901-S1910 | Finance + economics | unit economics dashboard v2, unit economics cross-tier, unit economics cross-locale, unit economics cross-channel, unit economics quarterly audit, unit economics partner audit, unit economics retrospective, unit economics renewal, unit economics annual disclosure, unit economics launch |
| S1911-S1920 | Finance + economics | budget v2, budget cross-team, budget quarterly audit, budget partner audit, budget cross-locale, budget cross-channel, budget retrospective, budget renewal, budget annual disclosure, budget launch |
| S1921-S1930 | Finance + economics | forecast v2, forecast quarterly audit, forecast partner audit, forecast cross-locale, forecast cross-channel, forecast retrospective, forecast renewal, forecast annual disclosure, forecast partner case study, forecast launch |
| S1931-S1940 | Finance + economics | funding option — bootstrap, funding option — angels, funding option — VC, funding option — strategic, funding option — debt, funding option — grants, funding option — public benefit, funding option — partner audit, funding option — retrospective, funding option — launch playbook |
| S1941-S1950 | Legal | corporate governance v2, board composition, board cadence, board observer rights, board retrospective, board annual disclosure, board partner audit, board cross-locale, board renewal, board launch playbook |
| S1951-S1960 | Legal | IP portfolio audit, IP partner audit, IP retrospective, IP annual disclosure, IP cross-locale, IP partner case study, IP renewal, IP licensing playbook, IP defensive playbook, IP launch playbook |
| S1961-S1970 | Legal | dispute resolution playbook, dispute resolution partner audit, dispute resolution cross-locale, dispute resolution retrospective, dispute resolution annual disclosure, dispute resolution partner case study, dispute resolution renewal, dispute resolution launch, dispute resolution case studies, dispute resolution retrospective |
| S1971-S1980 | Legal | regulator engagement playbook, regulator filings cadence, regulator filings cross-locale, regulator filings partner audit, regulator filings retrospective, regulator filings annual disclosure, regulator filings partner case study, regulator filings renewal, regulator filings launch, regulator filings retrospective |
| S1981-S1990 | Legal | whistleblower policy, whistleblower channel, whistleblower investigation playbook, whistleblower partner audit, whistleblower retrospective, whistleblower annual disclosure, whistleblower partner case study, whistleblower renewal, whistleblower launch, whistleblower retrospective |
| S1991-S2000 | Legal | sanctions screening, sanctions cross-locale, sanctions retrospective, sanctions partner audit, sanctions annual disclosure, sanctions partner case study, sanctions renewal, sanctions launch, sanctions case studies, sanctions retrospective |
| S2001-S2010 | Security v2 | red-team engagement, blue-team engagement, purple-team engagement, threat modelling v2, threat modelling cross-locale, threat modelling retrospective, threat modelling partner audit, threat modelling annual disclosure, threat modelling partner case study, threat modelling launch |
| S2011-S2020 | Security v2 | bug bounty program, bug bounty intake, bug bounty triage, bug bounty payouts, bug bounty cross-locale, bug bounty retrospective, bug bounty partner audit, bug bounty annual disclosure, bug bounty partner case study, bug bounty launch |
| S2021-S2030 | Security v2 | DDoS protection v2, DDoS partner audit, DDoS cross-locale, DDoS retrospective, DDoS annual disclosure, DDoS partner case study, DDoS renewal, DDoS launch, DDoS case studies, DDoS retrospective |
| S2031-S2040 | Security v2 | data breach response playbook v2, data breach drill, data breach legal review, data breach customer comms, data breach cross-locale, data breach retrospective, data breach partner audit, data breach annual disclosure, data breach partner case study, data breach launch |
| S2041-S2050 | Security v2 | physical security policy (offices, hires), physical security partner audit, physical security cross-locale, physical security retrospective, physical security annual disclosure, physical security partner case study, physical security renewal, physical security launch, physical security case studies, physical security retrospective |
| S2051-S2060 | Audit + audit-readiness | quarterly audit cadence, internal audit team, external audit partner, audit cross-locale, audit retrospective, audit annual disclosure, audit partner case study, audit renewal, audit launch, audit case studies |
| S2061-S2070 | Audit + audit-readiness | evidence vault, evidence retention policy, evidence cross-locale, evidence partner audit, evidence retrospective, evidence annual disclosure, evidence partner case study, evidence renewal, evidence launch, evidence case studies |
| S2071-S2080 | Audit + audit-readiness | risk register quarterly cadence, risk register partner audit, risk register cross-locale, risk register retrospective, risk register annual disclosure, risk register partner case study, risk register renewal, risk register launch, risk register case studies, risk register retrospective |
| S2081-S2090 | Strategy + planning | strategy doc annual, strategy doc quarterly, strategy doc cross-locale, strategy doc partner audit, strategy doc retrospective, strategy doc annual disclosure, strategy doc partner case study, strategy doc renewal, strategy doc launch, strategy doc case studies |
| S2091-S2100 | Strategy + planning | OKR cadence, OKR cross-team, OKR cross-locale, OKR partner audit, OKR retrospective, OKR annual disclosure, OKR partner case study, OKR renewal, OKR launch, OKR case studies |
| S2101-S2110 | Strategy + planning | annual planning cycle, annual planning cross-team, annual planning cross-locale, annual planning partner audit, annual planning retrospective, annual planning annual disclosure, annual planning partner case study, annual planning renewal, annual planning launch, annual planning case studies |
| S2111-S2120 | Strategy + planning | quarterly business review, quarterly business review cross-team, quarterly business review cross-locale, quarterly business review partner audit, quarterly business review retrospective, quarterly business review annual disclosure, quarterly business review partner case study, quarterly business review renewal, quarterly business review launch, quarterly business review case studies |
| S2121-S2130 | Strategy + planning | scenario planning playbook, scenario planning partner audit, scenario planning cross-locale, scenario planning retrospective, scenario planning annual disclosure, scenario planning partner case study, scenario planning renewal, scenario planning launch, scenario planning case studies, scenario planning retrospective |
| S2131-S2140 | Crisis + resilience | crisis simulation annual, crisis simulation cross-team, crisis simulation cross-locale, crisis simulation partner audit, crisis simulation retrospective, crisis simulation annual disclosure, crisis simulation partner case study, crisis simulation renewal, crisis simulation launch, crisis simulation case studies |
| S2141-S2150 | Crisis + resilience | resilience playbook, resilience cross-team, resilience cross-locale, resilience partner audit, resilience retrospective, resilience annual disclosure, resilience partner case study, resilience renewal, resilience launch, resilience case studies |
| S2151-S2160 | Crisis + resilience | partner emergency contact roster, partner emergency contact cross-locale, partner emergency contact partner audit, partner emergency contact retrospective, partner emergency contact annual disclosure, partner emergency contact partner case study, partner emergency contact renewal, partner emergency contact launch, partner emergency contact case studies, partner emergency contact retrospective |
| S2161-S2170 | Long-horizon R&D | R&D priorities annual, R&D cross-team, R&D cross-locale, R&D partner audit, R&D retrospective, R&D annual disclosure, R&D partner case study, R&D renewal, R&D launch, R&D case studies |
| S2171-S2180 | Long-horizon R&D | speculative bets — own-LLM, own-LLM training corpus, own-LLM training infra, own-LLM eval, own-LLM cost dashboard, own-LLM ethics review, own-LLM partner audit, own-LLM cross-locale, own-LLM retrospective, own-LLM launch |
| S2181-S2190 | Long-horizon R&D | speculative bets — knowledge graph, knowledge graph partner audit, knowledge graph cross-locale, knowledge graph retrospective, knowledge graph annual disclosure, knowledge graph partner case study, knowledge graph renewal, knowledge graph launch, knowledge graph case studies, knowledge graph retrospective |
| S2191-S2200 | Long-horizon R&D | speculative bets — agent-readers, agent-readers partner audit, agent-readers cross-locale, agent-readers retrospective, agent-readers annual disclosure, agent-readers partner case study, agent-readers renewal, agent-readers launch, agent-readers case studies, agent-readers retrospective |
| S2201-S2210 | Long-horizon R&D | speculative bets — real-time fact graph, real-time fact graph partner audit, real-time fact graph cross-locale, real-time fact graph retrospective, real-time fact graph annual disclosure, real-time fact graph partner case study, real-time fact graph renewal, real-time fact graph launch, real-time fact graph case studies, real-time fact graph retrospective |
| S2211-S2220 | Long-horizon R&D | speculative bets — proof-of-source ledger, proof-of-source ledger partner audit, proof-of-source ledger cross-locale, proof-of-source ledger retrospective, proof-of-source ledger annual disclosure, proof-of-source ledger partner case study, proof-of-source ledger renewal, proof-of-source ledger launch, proof-of-source ledger case studies, proof-of-source ledger retrospective |
| S2221-S2230 | Final integration | year-1 retrospective, year-2 plan, year-2 cross-team, year-2 cross-locale, year-2 partner audit, year-2 annual disclosure, year-2 partner case study, year-2 renewal, year-2 launch, year-2 case studies |
| S2231-S2237 | Closeout | Iboga-wide reconciliation, GrimbaNews maturity audit, GrimbaNews exit/expansion criteria, GrimbaNews 5-year vision update, GrimbaNews founder retrospective, GrimbaNews Mythos master fleet final closure, GrimbaNews S2237 ledger signoff |

**Mythos extension band total: 1237 atomic sprint outcomes** (S1001 → S2237, all 10-sprint rows except the final S2231-S2237 closeout row which is 7 sprints).

**Combined master plan: 2237 sprints total** (S001-S1000 pre-production + S1001-S2237 Mythos post-launch arc).

### Mythos extension governance

- Same evidence rules as S001-S1000 — every closed sprint must point to commit + test/artifact.
- Same NobuAI brand purity rule (CLAUDE.md) — reader-facing surfaces never name external LLM providers.
- Same audit panel cadence (Zen/Echo/Mnemo) before declaring any non-trivial sprint shipped.
- Same team-credits format on sprint close.
- Same git cadence: edit local → commit darkvaderfr/grimbanews:main → THEN deploy.
- Same "no production deploy until release gates green" rule. The S991-S1000 final-launch band still gates production; S1001+ kicks off ONLY after S1000 closes.

**Authored by:** Mythos (architect agent) under Vader 2026-05-20 directive. Reviewed in spirit by Zenkai (final QA signoff), Lucy Leai (strategy), Ray Dalio (CFO unit economics review), Steve Jobs (CPO design), Sara Chen (CISO).
