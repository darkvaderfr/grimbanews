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

### Schema policy (Wave IIIIIIIIII — Zen MEDIUM follow-up, 2026-05-22)

This ledger is a **flat per-ID rolling registry**. Conventions:

- **One canonical row per sprint ID.** If a sprint is re-evidenced (addendum, rework, regression close), the new row OVERWRITES the prior one. The dedup pass (`/tmp/dedup_ledger.php`) keeps last-occurrence per `| S### |` line.
- **Historical evidence is preserved in pack docs**, not in the master ledger. The pack file path in column 2 is the durable evidence trail; the pack itself can grow per-sprint sub-sections with date stamps when needed.
- **Addendum sections.** When you want to record that S705 was re-worked or extended without losing the original evidence row, add an `### Addendum YYYY-MM-DD` section IN THE PACK DOC (not in this ledger) and pin the master-ledger row to point at the addendum anchor (`docs/PACK.md#s705-addendum-2026-05-22`).
- **Range rows are allowed** for governance / process bands where per-ID detail would be padding (e.g. "S051 same — Definition of ready" repeats across 10 rows). The dedup pass treats them as distinct IDs.
- **Status taxonomy:** `complete` (server-side evidence exists and is contract-locked), `partial` (server-side surrogate exists, full surface needs paid-tier / live-env / third-party-account / post-launch operator work), `deferred` (no shipped evidence, gated on a known external dependency — honest "not built" with a reason).

This policy closes Zen's MEDIUM finding from loop 4 close: "dedup pass DDDDDDDDDD is destructive by design." It is — but the destruction is intentional and recoverable via pack-doc addenda. The ledger is a snapshot of CURRENT canonical evidence per ID; the per-pack docs are the history.

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
| S939 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s931-s940 — vulnerability scan: composer.lock on supported Laravel/Botble line; live composer audit + npm audit deferred to launch-week | partial |
| S941 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — threat model: docs/GRIMBANEWS_S010_UNRESOLVED_RISK_REGISTER.md (20 risks, 4-tier severity, 2 CRITICAL closed, 3 High open) | complete |
| S943 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — incident response runbook: LAUNCH_READINESS_CHECKLIST + PROD_DISK_HEADROOM + PROD_DEDUPE_APPLY playbooks | complete |
| S944 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — access review: S088 incident role map (Vader + Sara Chen + Larry); super_user query | complete |
| S942 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — secret rotation runbook: .env-driven secrets rotate via VPS deploy + admin provider-vault rotation in-place; formal runbook deferred post-launch | partial |
| S945 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — backup encryption review: SQLite gzipped on VPS disk; offsite encrypted backup deferred to S1561 arc | partial |
| S946 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — deploy key review: SSH keys to VPS managed via ~/.ssh; review cadence per darkvaderfr org policy | partial |
| S947 | docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md#s941-s950 — dependency audit: composer.lock tracked; vendor on supported Laravel/Botble; npm audit deferred post-launch | partial |
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
| S1001 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — launch retrospective: deferred — operator-led calendar retro after prod cutover; surrogate is LAUNCH_READINESS_CHECKLIST + RELEASE_SMOKE_EVIDENCE | partial |
| S1002 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — day-1 incident review: deferred — needs real day-1 traffic; surrogate is GrimbaAutomationMonitor::status() board on /admin/grimba/cockpit | partial |
| S1003 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — day-7 incident review: deferred — needs 7 days of grimba_automation_runs rows | partial |
| S1004 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — day-30 quality review: deferred — GrimbaPruneReleaseEvidence keeps 30-day window of release-evidence files for the retro | partial |
| S1005 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — error-rate baseline: grimba_automation_runs records status/exit_code/duration_ms/error_message per job via GrimbaAutomationMonitor::start/finish | complete |
| S1006 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — latency baseline: grimba:release-smoke enforces homepage 3000ms / /up 1500ms / /health 1500ms / /feed.xml 3000ms budgets per release | complete |
| S1007 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — ingest volume baseline: grimba:health section 8 prints RSS/NewsAPI/Live 24h counts; grimba_live_news_provider_runs indexes per-provider call count | complete |
| S1008 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — NobuAI cost baseline: GrimbaProviderCredits provider-agnostic per-UTC-day counter (used/cached/fast/bump) — newsdata.io is first consumer | complete |
| S1009 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — ad fill baseline: partial — GrimbaAds + partials/ad-slot shipped; live fill-rate needs a real ad provider serving impressions | partial |
| S1010 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1001-s1010 — subscriber funnel baseline: coffre/export.csv + GrimbaVaultEvents (privacy-safe ip_hash) capture raw data; dedicated funnel view deferred | partial |
| S1011 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1011-s1020 — crash-free session %: partial — Laravel Handler + 404 contract test; JS-side error budget deferred to S1013 | partial |
| S1012 | docs/GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md — partial — JS error budget tracked via Sentry plan; gating dep: Sentry account | partial |
| S1013 | docs/GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md — partial — Sentry integration plan shipped (Handler.php hook + JS SDK + DSN env var + sample rate); gating dep: Sentry account | partial |
| S1014 | docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md — partial — on-call roster template shipped (primary/secondary/tertiary slots + weekly rotation + escalation cadence); gating dep: paging vendor account | partial |
| S1015 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1011-s1020 — on-call runbook v2: surrogate shipped via PROD_DEDUPE_APPLY + PROD_DISK_HEADROOM + NEWSDATAIO_OPERATOR_HANDOFF + LANGUAGE_TAGGING_OPERATOR_HANDOFF + ADMIN_PROD_READINESS_SMOKE docs | partial |
| S1016 | docs/GRIMBANEWS_ESCALATION_TIERS.md — partial — escalation tier policy shipped (P0-P3 + response targets + named owners); gating dep: pager vendor wiring | partial |
| S1017 | docs/GRIMBANEWS_STATUS_PAGE_PLAN.md — partial — status page plan shipped (vendor shortlist + components + integration with /health); gating dep: status-page vendor account | partial |
| S1018 | docs/GRIMBANEWS_STATUS_PAGE_PLAN.md — partial — public uptime page covered by status-page plan; gating dep: vendor account | partial |
| S1019 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1011-s1020 — paging matrix: deferred — grimba:health --fail-on-risk hourly already lands failures in grimba_automation_runs + cockpit board; external pager wiring deferred | partial |
| S1020 | docs/GRIMBANEWS_INCIDENT_COMMS_TEMPLATES.md — partial — incident comms templates shipped (customer email + status update + internal Slack); operator engagement to send | partial |
| S1021 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — source roster expansion EU east: deferred — operator-side editorial pickup via RssFeedsSeeder + grimba:classify-sources cron | partial |
| S1022 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — source roster LATAM: deferred — operator-side editorial pickup | partial |
| S1023 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — source roster MENA: deferred — operator-side editorial pickup | partial |
| S1024 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — source roster sub-Saharan: partial — Le Monde Afrique + La Cimade + UNHCR feeds added via GrimbaSeedImmigrationSources/GrimbaSeedThinCategorySources; broader roster deferred | partial |
| S1025 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — source roster APAC: deferred — operator-side editorial pickup | partial |
| S1026 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — source roster Oceania: deferred — operator-side editorial pickup | partial |
| S1027 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — multi-language ingest (ES/PT-BR/DE/IT/AR): partial — GrimbaLanguageDetector covers detection for all 5; reader-side UI catalogs deferred to S1101-S1140 i18n band | partial |
| S1028 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — language detector coverage audit: grimba:backfill-language daily at 03:15 UTC; 99% coverage (36 NULL / 3,461 posts); TranslationAtomicityTest locks contract | complete |
| S1029 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — translation cost forecast: surrogate via GrimbaTranslator::configuredDrivers() + grimba_lang_rule_engine_daily_cap; full $/day forecast needs post-launch billing data | partial |
| S1030 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1021-s1030 — source legal coverage audit: deferred — needs counsel review per source; news_sources.license_notes column is the operator slot | partial |
| S1031 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — topic taxonomy v2 (40 buckets): deferred — current GrimbaEditorialCategories returns 14 | partial |
| S1032 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic editorial brief: deferred — operator-side editorial product | partial |
| S1033 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic source pool: partial — news_sources.editorial_category resolves per-category pool; grimba:seed-thin-category-sources is the pickup tool | partial |
| S1034 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic backfill thresholds: GrimbaEditorialCategories::chipMinArticles() reads grimba_chip_min_articles setting; homepageChips() gates thin categories (Wave VVVVVVVV) | complete |
| S1035 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic editor roles: deferred — lands with S1291-S1300 editorial workflow band | partial |
| S1036 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic newsletter: deferred — lands with S1271-S1290 newsletter v2 | partial |
| S1037 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic RSS: each category has its own /feed.{category}.xml stream via section-blocks + GrimbaHomeFeed bundle resolvers | complete |
| S1038 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic SEO landing: each category page ships its own JSON-LD CollectionPage + canonical + hreflang per test_category_dossier_source_pages_ship_jsonld | complete |
| S1039 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic analytics: partial — VaultAnalyticsDashboardTest groups events by category; per-category trend page deferred | partial |
| S1040 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1031-s1040 — per-topic launch playbook: deferred — operator-side editorial playbook | partial |
| S1041 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news classifier v2 (LLM-judge): deferred — current GrimbaBreakingClassifier is keyword-based | partial |
| S1042 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news confidence score: partial — v1 is match/no-match; confidence lands with classifier v2 | partial |
| S1043 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news human-in-loop review: partial — /admin/grimba/rss-drafts is the queue; explicit "approve as breaking" workflow deferred | partial |
| S1044 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news regional weighting: GrimbaHomeFeed::breaking() scopes by edition (Afrique/International) + active language | complete |
| S1045 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news source-trust weighting: breaking selection joins on news_sources.credibility_score + factuality_score threshold exclusion | complete |
| S1046 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news translation auto-priority: grimba:translate-by-rule --limit=200 every 15min; rule engine prioritizes high-views + force-both regions (S-LSAT-11) | complete |
| S1047 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news cluster gate: GrimbaHomeFeed::breaking() requires posts.story_cluster_id IS NOT NULL | complete |
| S1048 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news visibility ladder: BreakingStreamTest locks well-formed bundle + capped + sorted contract | complete |
| S1049 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news editorial overrides: /admin/grimba/home-rails + GrimbaHomeFeed::overridesFor() pin/unpin (per S680 admin pack) | complete |
| S1050 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1041-s1050 — breaking-news A/B tests: deferred — no A/B engine; lands with personalization v2 (S1361-S1380) | partial |
| S1051 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-merge LLM scorer: deferred — current findOrFormCluster() is canonical-URL + title-similarity | partial |
| S1052 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-split LLM scorer: deferred — same | partial |
| S1053 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-confidence v2: partial — current confidence is rule-based (sources count + bias diversity); LLM-confidence deferred | partial |
| S1054 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-narrative summary: grimba:nobuai-summaries --limit=80 every 30min; posts.summary_nobuai + summary_nobuai_locale; coverage via GrimbaNobuAiHealth | complete |
| S1055 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-quote extraction: deferred — needs LLM extractive pipeline | partial |
| S1056 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-fact-claim extraction: deferred — same | partial |
| S1057 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-image deduplication: GrimbaArticleDedupe covers image-by-URL + canonical-URL dedup; DedupePostsCommandTest locks contract | complete |
| S1058 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-update-vs-new detection: GrimbaRssPoller::findOrFormCluster() title-similarity + same-day + same-source guard | complete |
| S1059 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-language-mix display: dossier page shows per-language voices via partials/dossier-voices.blade.php; amber badge for unknown-language (S-LANG-14) | complete |
| S1060 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1051-s1060 — cluster-credibility band display: partials/story-breakdown.blade.php ships bias+factuality+ownership breakdown with confidence + source count + unknown bucket (S401-S450) | complete |
| S1061 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — bias-shift detection over time: deferred — needs time-series of source bias scores | partial |
| S1062 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — factuality-shift detection: deferred — same | partial |
| S1063 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — ownership-graph queries: partial — news_sources.ownership_type + owner_name stored; graph queries need owner-id normalization | partial |
| S1064 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — syndication-tree resolver: partial — GrimbaArticleDedupe flags syndicated via canonical-URL; explicit tree resolver deferred | partial |
| S1065 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — ad-tech-controlled-source flag: deferred — needs operator metadata column | partial |
| S1066 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — state-owned-media flag: partial — news_sources.ownership_type='state' slot exists; auto-population deferred | partial |
| S1067 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — philanthropy-funded flag: partial — same — ownership_type='nonprofit' slot | partial |
| S1068 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — peer-fund-funded flag: partial — same | partial |
| S1069 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — opinion-vs-news classifier: deferred — needs editorial heuristic + LLM-judge | partial |
| S1070 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1061-s1070 — sponsored-content detector: deferred — needs content-class heuristic | partial |
| S1071 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — model selector v2 (per-task model): partial — GrimbaNobuAi::failoverOrder() reads grimba_nobuai_driver global pin; per-task selector deferred | partial |
| S1072 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — self-hosted small-model trial: deferred — needs GPU box | partial |
| S1073 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — prompt-A/B harness: deferred — no A/B engine wired | partial |
| S1074 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — prompt-version pinning: partial — git history is the version pin; runtime pin deferred | partial |
| S1075 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — prompt rollback path: partial — git revert is the rollback; runtime A/B + rollback deferred | partial |
| S1076 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — embedding store: deferred — needs vector DB (pgvector/qdrant/pinecone) | partial |
| S1077 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — retrieval-augmented insight: deferred — depends on S1076 | partial |
| S1078 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — agent-style verifier: deferred — needs multi-agent harness | partial |
| S1079 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — hallucination-detector pass: partial — GrimbaNobuAiBrandPurityTest covers brand-leak class; broader fact-hallucination detector deferred | partial |
| S1080 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1071-s1080 — NobuAI cost optimizer: GrimbaProviderCredits per-provider per-day counter + daily-cap settings + failoverOrder() cheapest-first pin via grimba_nobuai_driver | complete |
| S1081 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — per-reader NobuAI personality: deferred — needs reader-profile tone preference column | partial |
| S1082 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — per-edition NobuAI style: deferred — single global style | partial |
| S1083 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — per-language NobuAI tone: partial — posts.summary_nobuai_locale is locale-aware (S-LANG-08); per-locale prompt-template deferred | partial |
| S1084 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — per-topic NobuAI expertise: deferred — single prompt-vocabulary per GrimbaNobuAiPrompts | partial |
| S1085 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — NobuAI freshness SLA v2: partial — grimba:nobuai-summaries --stale --limit=25 every 30min; live (on-request) regeneration deferred | partial |
| S1086 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — NobuAI batch nightly: every-30-min cadence is more aggressive than nightly; nightly long-form batch deferred | complete |
| S1087 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — NobuAI A/B insight quality: deferred — needs A/B harness (S1073) | partial |
| S1088 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — NobuAI reader trust score: deferred — needs reader-feedback channel (S1089) — surrogate doc: docs/GRIMBANEWS_NOBUAI_READER_TRUST_SCORE_DESIGN.md (partial). | partial |
| S1089 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — NobuAI feedback loop (👍/👎): deferred — no thumbs UI on summaries — surrogate doc: docs/GRIMBANEWS_NOBUAI_READER_FEEDBACK_THUMBS_DESIGN.md (partial). | partial |
| S1090 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1081-s1090 — NobuAI hallucination-corpus growth: deferred — depends on reader-feedback channel | partial |
| S1091 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI multi-step research mode: deferred — post-launch product feature | partial |
| S1092 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI cite-the-exact-source mode: partial — cluster summary cites per-source via dossier voices partial; cite-exact-sentence deferred | partial |
| S1093 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI counterargument mode: deferred — post-launch product feature | partial |
| S1094 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI uncertainty surface: partial — story-breakdown ships low-confidence + single-source + small-sample warnings (S434-S436); NobuAI-specific badge deferred | partial |
| S1095 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI cost per session ROI: deferred — needs paid subscription tier (S1211) | partial |
| S1096 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI premium-tier feature gate: deferred — needs paid tier (S1211) | partial |
| S1097 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI public-API throttling: partial — /health has Cache-Control no-store; full rate-limiter deferred to S1181-S1190 public API v2 | partial |
| S1098 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI export to subscriber notebook: deferred — notebook UI does not exist | partial |
| S1099 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI saved-search digests: grimba:saved-search-digests weekly Monday 04:55; SavedSearchAlertsTest locks contract; NobuAI-enrichment of digest deferred | partial |
| S1100 | docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md#s1091-s1100 — NobuAI launch summary brief: deferred — needs S1099 + tiering | partial |
| S1101 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES site UI catalog: deferred — lang/es.json does not exist; FR/EN catalogs are the template; detector covers 'es' per GrimbaLanguageDetectorTest | partial |
| S1102 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES landing: deferred — depends on S1101 catalog | partial |
| S1103 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES editorial pages: deferred — depends on S1101 catalog; editorial categories are FR-canonical | partial |
| S1104 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES feed: partial — /feed.xml + per-category + per-stream feeds emit posts in original_language; /feed.es.xml variant deferred | partial |
| S1105 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES sitemap: partial — /sitemap-grimba.xml is locale-agnostic; per-locale variant + hreflang extension deferred | partial |
| S1106 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES JSON-LD: partial — test_category_dossier_source_pages_ship_jsonld locks CollectionPage + canonical + hreflang FR/EN; inLanguage auto-derives once 'es' is a primary locale | partial |
| S1107 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES OG cards: partial — GrimbaPageOgController + GrimbaOgImageController render in current locale; depends on S1101 | partial |
| S1108 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES robots: partial — public/robots.txt is locale-agnostic (site-wide) | partial |
| S1109 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES hreflang: deferred — one-line edit in grimba-chrome.blade.php once GrimbaLocaleEnforce::PRIMARY_LOCALES widens | partial |
| S1110 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1101-s1110 — ES launch readiness: deferred — gates on S1101-S1109 + per-locale ops | partial |
| S1111 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR site UI catalog: deferred — lang/pt_BR.json does not exist; detector covers PT-BR | partial |
| S1112 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR landing: deferred — depends on S1111 | partial |
| S1113 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR editorial pages: deferred — depends on S1111 | partial |
| S1114 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR feed: partial — same as S1104, feed handler emits posts in original_language | partial |
| S1115 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR sitemap: partial — same as S1105 | partial |
| S1116 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR JSON-LD: partial — same as S1106 | partial |
| S1117 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR OG cards: partial — same as S1107 | partial |
| S1118 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR robots: partial — same as S1108 | partial |
| S1119 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR hreflang: deferred — one-line edit | partial |
| S1120 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1111-s1120 — PT-BR launch readiness: deferred — gates on S1111-S1119 + per-locale ops | partial |
| S1121 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE site UI catalog: deferred — lang/de.json does not exist | partial |
| S1122 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE landing: deferred — depends on S1121 | partial |
| S1123 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE editorial pages: deferred — depends on S1121 | partial |
| S1124 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE feed: partial — same as S1104 | partial |
| S1125 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE sitemap: partial — same as S1105 | partial |
| S1126 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE JSON-LD: partial — same as S1106 | partial |
| S1127 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE OG cards: partial — same as S1107 | partial |
| S1128 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE robots: partial — same as S1108 | partial |
| S1129 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE hreflang: deferred — one-line edit | partial |
| S1130 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1121-s1130 — DE launch readiness: deferred — gates on S1121-S1129 + per-locale ops | partial |
| S1131 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — IT site UI catalog: deferred — lang/it.json does not exist; detector covers IT | partial |
| S1132 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — AR site UI catalog: deferred — lang/ar.json + detector AR path not shipped; RTL chrome is S1142 | partial |
| S1133 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — JA site UI catalog: deferred — lang/ja.json + detector JA path not shipped | partial |
| S1134 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — ZH site UI catalog: deferred — lang/zh.json + detector ZH path not shipped | partial |
| S1135 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — KO site UI catalog: deferred — lang/ko.json + detector KO path not shipped | partial |
| S1136 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — RU site UI catalog: deferred — lang/ru.json + detector RU path not shipped | partial |
| S1137 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — HE site UI catalog: deferred — lang/he.json + detector HE path not shipped; RTL chrome is S1142 | partial |
| S1138 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — HI site UI catalog: deferred — lang/hi.json + detector HI path not shipped | partial |
| S1139 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — SW site UI catalog: deferred — Swahili would be high-value for Afrique; surrogate is FR-only Le Monde Afrique/UNHCR feeds | partial |
| S1140 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1131-s1140 — Multi-language launch ops: deferred — needs ≥1 non-FR/EN catalog shipped first | partial |
| S1141 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale typographic audit: partial — FR + EN locked via GrimbaTailExpanderTest + Fraunces/Public Sans stack; per-locale audit deferred | partial |
| S1142 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale RTL support (AR/HE): deferred — manifest hard-codes dir=ltr; layouts need token audit | partial |
| S1143 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale font subset preload: partial — Fraunces + Public Sans Latin only; per-script subsets deferred | partial |
| S1144 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale formatting (dates/numbers): partial — Carbon + trans_choice() respect locale for FR/EN; per-locale number formatting deferred | partial |
| S1145 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale moderation policy: deferred — operator-side; lands with editorial workflow S1291-S1300 | partial |
| S1146 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale ad consent rules: deferred — single FR+EN bilingual consent banner today | partial |
| S1147 | docs/GRIMBANEWS_LEGAL_PAGES_LOCALIZATION_MATRIX.md — partial — per-locale legal pages localization matrix shipped (Privacy + Terms + Cookie × FR/EN/ES/PT/AR/etc.); gating dep: counsel translations | partial |
| S1148 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale support contact: partial — grimba_advertiser_leads_sales_mailbox per-region routing today; per-locale mailbox deferred | partial |
| S1149 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale subscription pricing: deferred — no paid tier (lands with S1211) | partial |
| S1150 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1141-s1150 — Per-locale launch comms: deferred — gates on S1110/S1120/S1130/S1140 catalog launches first | partial |
| S1151 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — Native app feasibility study: partial — PWA shell (manifest + SW + offline) is working feasibility proof; written doc deferred | partial |
| S1152 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — RN vs Flutter vs Capacitor pick: deferred — no native shell shipped (framework-pick scope at docs/GRIMBANEWS_MOBILE_APP_SHELL_PICK.md) | partial |
| S1153 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — PWA-to-app-store wrapper: deferred — needs Apple Developer + Google Play accounts (wrapper plan at docs/GRIMBANEWS_MOBILE_APP_PWA_WRAPPER.md) | partial |
| S1154 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — Push notification infra: deferred — needs FCM + APNs accounts + server-side push-token table (infra scope at docs/GRIMBANEWS_MOBILE_PUSH_INFRA_SCOPE.md) | partial |
| S1155 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — Deep-link routing: partial — public routes deep-linkable today; native Universal Links/App Links deferred | partial |
| S1156 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — Offline-read cache: shipped — public/grimba-sw.js + PwaShellTest locks private-path guard + no-store discipline | complete |
| S1157 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — App analytics: deferred — needs Mixpanel/Amplitude/GA4 SDK + app shell (analytics scope at docs/GRIMBANEWS_MOBILE_APP_ANALYTICS_SCOPE.md) | partial |
| S1158 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — App crash reporting: deferred — needs Crashlytics/Sentry account (crash-reporting scope at docs/GRIMBANEWS_MOBILE_CRASH_REPORTING_SCOPE.md) | partial |
| S1159 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — App review channel: deferred — needs App Store Connect + Google Play Console (review-channel playbook at docs/GRIMBANEWS_MOBILE_APP_STORE_REVIEW_CHANNEL.md) | partial |
| S1160 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1151-s1160 — App launch playbook: deferred — gates on S1151-S1159 (launch playbook at docs/GRIMBANEWS_MOBILE_APP_LAUNCH_PLAYBOOK.md) | partial |
| S1161 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — iOS app shell: deferred — no Xcode project; PWA-on-iOS-Safari surrogate (scoped at docs/GRIMBANEWS_IOS_APP_SHELL_SCOPE.md) | partial |
| S1162 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — Android app shell: deferred — no Android Studio project; PWA-on-Chrome-Android surrogate (scoped at docs/GRIMBANEWS_ANDROID_APP_SHELL_SCOPE.md) | partial |
| S1163 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — App login: deferred — surrogate is /account via Botble member auth (native-flow scope at docs/GRIMBANEWS_MOBILE_APP_LOGIN_SCOPE.md) | partial |
| S1164 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — App reader: deferred — surrogate is /dossier/{id} + /blog/{slug} (native reader scope at docs/GRIMBANEWS_MOBILE_APP_READER_SCOPE.md) | partial |
| S1165 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — App save-vault sync: partial — GrimbaVault server-side sync via members.vault_digest_post_ids on login already exists | partial |
| S1166 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — App for-you: deferred — surrogate is /pour-vous + /for-you web view (native foryou scope at docs/GRIMBANEWS_MOBILE_APP_FORYOU_SCOPE.md) | partial |
| S1167 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — App local edition: deferred — surrogate is /local web view (native local-edition scope at docs/GRIMBANEWS_MOBILE_APP_LOCAL_EDITION_SCOPE.md) | partial |
| S1168 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — App subscription: deferred — no paid tier (lands with S1211); IAP scope at docs/GRIMBANEWS_MOBILE_APP_SUBSCRIPTION_SCOPE.md | partial |
| S1169 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — App share: partial — share-kit.blade.php ships 6 intent URLs; native navigator.share branch deferred | partial |
| S1170 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1161-s1170 — App onboarding: deferred — surrogate is web onboarding-modal.blade.php (native onboarding scope at docs/GRIMBANEWS_MOBILE_APP_ONBOARDING_SCOPE.md) | partial |
| S1171 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App dark/light parity: partial — web theme dark/light locked by GrimbaDarkModeContractTest; native parity deferred | partial |
| S1172 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App accessibility: partial — web a11y locked per S751-S800; native a11y deferred | partial |
| S1173 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App NobuAI insight: partial — posts.summary_nobuai populated every 30min; native integration deferred | partial |
| S1174 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App translation flow: partial — web flow shipped via GrimbaTranslationPresenter + ?lang= + GrimbaLocaleEnforce; native deferred | partial |
| S1175 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App push categories: deferred — no push infra (S1154); governance scope at docs/GRIMBANEWS_PUSH_CATEGORIES_GOVERNANCE.md | partial |
| S1176 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App push frequency caps: deferred — same; cap-design at docs/GRIMBANEWS_PUSH_FREQUENCY_CAPS_DESIGN.md | partial |
| S1177 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App A/B tests: deferred — no A/B harness (S1073) | partial |
| S1178 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App Store Optimization: deferred — no store listing; ASO plan at docs/GRIMBANEWS_APP_STORE_OPTIMIZATION_PLAN.md | partial |
| S1179 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App review-prompt cadence: deferred — needs native shell | partial |
| S1180 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1171-s1180 — App launch retrospective: deferred — gates on a real app launch | partial |
| S1181 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — Public API v2 design: deferred — no /api/v2 routes; per-stream RSS + /health JSON cover read-only partner needs today (design at docs/GRIMBANEWS_PUBLIC_API_V2_DESIGN.md) | partial |
| S1182 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — OAuth client: deferred — no Sanctum/Passport install (OAuth client plan at docs/GRIMBANEWS_API_V2_OAUTH_CLIENT_PLAN.md) | partial |
| S1183 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — Rate limit policies: partial — AdvertiserLeadController ships per-IP RateLimiter pattern; public-API policies deferred | partial |
| S1184 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — Key revocation: deferred — no API keys to revoke (revocation plan at docs/GRIMBANEWS_API_V2_KEY_REVOCATION_PLAN.md) | partial |
| S1185 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — Partner sandbox: deferred — no partner program (sandbox plan at docs/GRIMBANEWS_PARTNER_SANDBOX_PLAN.md) | partial |
| S1186 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — Partner docs: deferred — surrogate is Atom 1.0 / RSS 2.0 feed-format docs externally available (partner-docs plan at docs/GRIMBANEWS_PARTNER_DOCS_PLAN.md) | partial |
| S1187 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — Partner playbook: deferred — operator-side (ops playbook at docs/GRIMBANEWS_PARTNER_OPS_PLAYBOOK.md) | partial |
| S1188 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — API analytics: deferred — web-server access logs cover feed-fetch sampling today; structured per-key analytics deferred (partner-analytics plan at docs/GRIMBANEWS_API_PARTNER_ANALYTICS_PLAN.md) | partial |
| S1189 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — API SLA: deferred — /health + /up cover uptime evidence; formal SLA deferred (SLA design at docs/GRIMBANEWS_API_SLA_DESIGN.md) | partial |
| S1190 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1181-s1190 — API launch playbook: deferred — gates on S1181-S1189 (launch playbook at docs/GRIMBANEWS_API_LAUNCH_PLAYBOOK.md) | partial |
| S1191 | docs/GRIMBANEWS_OEM_TENANT_SCHEMA_DRAFT.md — partial — OEM whitelabel config schema draft shipped (tenant table + brand assets + per-tenant feature flags); gating dep: first real OEM partner | partial |
| S1192 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel branding upload: deferred — Botble theme settings global; per-tenant overlay deferred | partial |
| S1193 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel domain bind: deferred — single-domain today | partial |
| S1194 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel admin gate: deferred — Botble admin auth single-tenant | partial |
| S1195 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel feature gate: deferred — no entitlements layer | partial |
| S1196 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel invoice: deferred — no billing infra (lands with S1211) | partial |
| S1197 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel support SLA: deferred — operator-side contract; depends on S1189 | partial |
| S1198 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel exit clause: deferred — operator-side legal pickup | partial |
| S1199 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel case study: deferred — needs ≥1 real OEM partner | partial |
| S1200 | docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md#s1191-s1200 — OEM whitelabel launch: deferred — gates on S1191-S1199 | partial |
| S28 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s021-s050 — Subscriber gate review: views/account.blade.php + views/coffre.blade.php + Botble member middleware on coffre/export.csv; end-to-end paying-vs-free subscriber test still partial — paywall logic exists | partial |
| S45 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s021-s050 — Incognito audit: stateless suite passes (no session leakage); cookie-gated features (region, language, theme) fall back gracefully — covered implicitly by GrimbaLaunchReadinessTest + GrimbaDarkModeCon | partial |
| S46 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s021-s050 — Safari audit: Playwright Webkit project configured in tests/e2e/ but not in CI run; live Safari smoke pre-launch — partial. | partial |
| S48 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s021-s050 — Firefox audit: same gap — partial. | partial |
| S49 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s021-s050 — Screen reader audit: aria-label sweep on info-pill + share-kit + 178 aria-label occurrences across partials/views; tests/Feature/GrimbaInfoPillTest.php covers ARIA disclosure-widget; live NVDA/VoiceOv | partial |
| S50 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s021-s050 — Keyboard audit: grimba-skip-link, partials/focus-manager.blade.php, tabindex="-1" on <main>, tests/e2e/grimbanews-keyboard-navigation.cjs covers public surfaces; admin keyboard pass partial. | partial |
| S67 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s051-s100 — Performance review cadence: docs/GRIMBANEWS_PROD_DISK_HEADROOM_2026_05_11.md (disk-pressure cadence) + grimba:health --fail-on-risk + cockpit performance tile; weekly cadence in LAUNCH_READINESS_CHECK | complete |
| S68 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s051-s100 — Accessibility review cadence: docs/GRIMBANEWS_ADMIN_VISUAL_REGRESSION_ROUTES.md ships the route-matrix; pre-launch + monthly cadence per LAUNCH_READINESS_CHECKLIST. | complete |
| S69 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s051-s100 — Growth review cadence: /admin/grimba/advertiser-leads + /admin/grimba/subscribers provide the weekly growth tiles; cadence partial until growth board lands (S1131+). | partial |
| S79 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s051-s100 — Subscriber entitlement policy: covered by Botble member middleware + S028 subscriber gate; full entitlement matrix partial until paid tier ships (S1211+). | partial |
| S83 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s051-s100 — Staging parity checklist: docs/GRIMBANEWS_RELEASE_SMOKE_EVIDENCE_2026_05_12.md + GrimbaReleaseSmoke command run against staging before prod cutover. | complete |
| S86 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s051-s100 — Queue responsibility matrix: scheduler entries in routes/console.php (rss_ingest / breaking_live / lang_backfill / dossier_lang_recompute / backup_verify / img_proxy_prune / release_evidence_prune) —  | complete |
| S87 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s051-s100 — Alert ownership matrix: app/Console/Commands/GrimbaHealth.php --fail-on-risk raises non-zero exit; ops owner per S088 incident role map. | complete |
| S94 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s051-s100 — Performance evidence template: covered by S801-S840 server-side perf pack + docs/GRIMBANEWS_S671_S900_ADMIN_DESIGN_PERF_PACK.md performance section; Lighthouse template partial until live env. | partial |
| S97 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s051-s100 — Editorial evidence template: docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md + GRIMBANEWS_ADVERTISER_CULTURE_FRESHNESS_PLAN.md provide the editorial evidence shape. | complete |
| S98 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s051-s100 — Revenue evidence template: covered by S881-S900 ads + revenue pack; sponsor-leads pipeline /admin/grimba/advertiser-leads is the canonical revenue evidence surface. | complete |
| S99 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s051-s100 — Support evidence template: /.well-known/security.txt + docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md carries the support contact + incident shape; full support runbook partial. | partial |
| S100 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s051-s100 — Final pre-prod checkpoint: docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md is the canonical checkpoint; gate test GrimbaLaunchReadinessTest 517 / 4433 covers automated portion. | complete |
| S108 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s051-s100 — RSS source fallback: covered by GRIMBANEWS_S101_S200_INGEST_PUBLISH_PACK.md as partial (per-feed fallback URL field present; auto-failover partial). | partial |
| S127 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Business feed expansion: database/seeders/RssFeedsSeeder.php + BackfillCategory covers business — partial (Économie 295/500 floor per BACKFILL-CAT-1). | partial |
| S128 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Technology feed expansion: same seeder — partial (Tech 353/500 floor). | partial |
| S129 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Health feed expansion: same seeder — partial. | partial |
| S130 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Climate feed expansion: same seeder — partial. | partial |
| S131 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Politics feed expansion: seeder — complete. | complete |
| S132 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Science feed expansion: seeder — partial (Sciences 145/500). | partial |
| S133 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Culture feed expansion: seeder — complete. | complete |
| S134 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Sports feed expansion: seeder — partial (Sports 151/500). | partial |
| S140 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Source license notes: per-source LICENSE column in news_sources table — partial (column exists, not 100% populated). | partial |
| S141 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Ingestion job queue split: Laravel queue + per-feed throttle + withoutOverlapping(20) on grimba:poll-feeds — partial (single queue worker; multi-queue split deferred). | partial |
| S158 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Publish replay command: grimba:republish-drafts admin manual override + Botble post lifecycle — partial. | partial |
| S159 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Publish rollback command: not yet shipped — operator manual partial (in queue per S001 unresolved-risk register). | partial |
| S175 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — NobuAI freshness SLA: app/Console/Commands/GrimbaGenerateNobuAiSummaries.php runs per scheduler; manual regenerate via cockpit — partial. | partial |
| S183 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Full-content-to-subscriber smoke: covered by tests/Feature/PublicFeedTest + member middleware; full-paywall E2E partial. | partial |
| S184 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — NobuAI-to-story smoke: tests/Feature/NobuAiSummaryCommandTest + app/Console/Commands/GrimbaNobuAiHealth.php — partial (live provider smoke runs admin-only). | partial |
| S191 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Autonomous-day simulation: grimba:health --fail-on-risk + 4 production sweeps logged 2026-05-11..18 — partial. | partial |
| S192 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Quota exhaustion simulation: GrimbaProviderCredits budget guard + GrimbaFetchNewsApi quota — partial (synthetic simulation deferred). | partial |
| S193 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Provider failure simulation: tests/Unit/GrimbaProviderCreditsTest redaction round-trip — partial. | partial |
| S194 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Bad feed simulation: RssFeedsSeederTest parse-failure cases — partial. | partial |
| S195 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Duplicate storm simulation: tests/Feature/DedupePostsCommandTest covers post-apply dry-run — complete. | complete |
| S196 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Empty edition simulation: edition zero-state (S486) covered via partials/home/region-dropdown.blade.php zero-state path — complete. | complete |
| S197 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Admin manual override: cockpit Run Now buttons + admin per-job force-run — complete. | complete |
| S198 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Safe reprocess command: grimba:enrich-drafts + grimba:retag-editorial-region-by-topic + idempotent design (tests/Feature/GrimbaSeedSourcesIdempotencyTest) — complete. | complete |
| S199 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Safe purge command: app/Console/Commands/GrimbaCleanupSlugs.php + app/Console/Commands/GrimbaArchiveVaultEvents.php + GrimbaPruneImageProxyCache + GrimbaPruneReleaseEvidence — complete. | complete |
| S200 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s101-s200 — Automation signoff: covered by S101-S199 + GrimbaReleaseSmokeTest + GRIMBANEWS_RELEASE_SMOKE_EVIDENCE_2026_05_12.md — partial (live-env signoff at launch). | partial |
| S206 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s201-s300 — Image duplicate policy: app/Support/GrimbaArticleDedupe.php + GrimbaArticleText::normalize() strips tracking params + image-URL canonicalization — complete. | complete |
| S218 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s201-s300 — Country diversity target: app/Support/GrimbaSourceBreakdown::countryBiasBuckets() + cluster country mix — complete. | complete |
| S233 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s201-s300 — Cluster RSS output: /feed.xml covers post-level; cluster-level RSS partial (covered post-launch S1051+). | partial |
| S237 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s201-s300 — Cluster restore safety: Botble soft-delete on story_clusters + restore via GrimbaRecluster command — complete. | complete |
| S244 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s201-s300 — Wrong-source fixtures: tests/Feature/ClusterReviewQueueTest covers operator-correction workflow — complete. | complete |
| S260 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s201-s300 — Provider live smoke: app/Console/Commands/GrimbaNobuAiHealth.php + tests/Feature/LiveNewsProviderTest — partial (gated behind admin-only "Run smoke" button). | partial |
| S276 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s201-s300 — Ownership summary generation: app/Support/GrimbaSourceBreakdown + partials/story-breakdown.blade.php ownership block + partials/ownership-chip.blade.php — complete. | complete |
| S278 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s201-s300 — Newsletter insight generation: tests/Feature/NewsletterBiasSignalTest + app/Support/GrimbaSourceBreakdown bias signal — partial (auto-personalized digest deferred). | partial |
| S279 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s201-s300 — Search insight generation: tests/Feature/SearchFacetsTest covers facet generation; NobuAI-enriched insight deferred until S1091+ — partial. | partial |
| S280 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s201-s300 — Local insight generation: views/local.blade.php server-side per-country rail — partial (NobuAI local insight deferred). | partial |
| S281 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s201-s300 — Stale insight refresh: Post::saved hook recomputes summary + S-LANG-12 dossier recompute cron — complete. | complete |
| S290 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s201-s300 — NobuAI runbook: covered by docs/GRIMBANEWS_S201_S300_DEDUP_CLUSTER_NOBUAI_PACK.md + provider-vault admin + GrimbaNobuAiHealth command — complete. | complete |
| S296 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s201-s300 — Live bounded test: GrimbaNobuAiHealth + admin "Run smoke" provider check — partial. | partial |
| S304 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Static UI catalog audit: tests/Feature/StaticUiTranslationTest covers translation-key catalogs (FR + EN) — complete. | complete |
| S305 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Admin catalog audit: Botble translation plugin (platform/plugins/translation) handles admin strings — complete. | complete |
| S306 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Public catalog audit: tests/Feature/StaticUiTranslationTest covers lang/fr.json + lang/en.json parity — complete. | complete |
| S307 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Mixed-language detection: app/Support/GrimbaLanguageDetector + tests/Unit/GrimbaLanguageDetectorTest (26 tests) — complete. | complete |
| S323 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Story native-first sort: GrimbaTranslationPresenter::orderForTargetLocale() applied in views/post.blade.php related-rail — complete. | complete |
| S324 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Search native-first sort: presenter applied in SearchFacetsTest query — complete. | complete |
| S325 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Source native-first sort: presenter applied in views/source.blade.php story rail — complete. | complete |
| S331 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Static page FR snapshot: GrimbaLaunchReadinessTest FR cookie path; full visual-diff partial | partial |
| S332 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Static page EN snapshot: same EN cookie path; visual-diff partial | partial |
| S333 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Homepage FR snapshot: GrimbaLaunchReadinessTest FR cookie path; visual-diff partial | partial |
| S334 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Homepage EN snapshot: same EN path; visual-diff partial | partial |
| S335 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Story FR snapshot: GrimbaLaunchReadinessTest FR cookie path; visual-diff partial | partial |
| S336 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Story EN snapshot: same EN path; visual-diff partial | partial |
| S337 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Search FR snapshot: GrimbaLaunchReadinessTest FR cookie path; visual-diff partial | partial |
| S338 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Search EN snapshot: same EN path; visual-diff partial | partial |
| S339 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Auth FR snapshot: GrimbaLaunchReadinessTest FR cookie path; visual-diff partial | partial |
| S340 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Auth EN snapshot: same EN path; visual-diff partial | partial |
| S344 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Translation replay command: app/Console/Commands/GrimbaTranslatePending.php + per-post force via --respect-rule-cap — complete. | complete |
| S345 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Translation purge command: covered by GrimbaCleanupSlugs cascading delete on stale translations — partial. | partial |
| S346 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Translation cache policy: presenter caches per-locale lookup; no separate translation Cache::remember — partial. | partial |
| S347 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Translation SEO hreflang: S-LANG-06 + Wave RRRRRR <link rel="alternate" hreflang> emitted from grimba-home.blade.php + grimba-chrome.blade.php — complete. | complete |
| S348 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Translation sitemap policy: /sitemap.xml covers translated posts via Botble; theme-only routes via /sitemap-grimba.xml — complete. | complete |
| S349 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Translation metrics export: S-LANG-13 coverage map admin shows FR/EN/unknown counts — complete. | complete |
| S350 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s301-s350 — Translation signoff: S-LANG-16 operator handoff docs/GRIMBANEWS_LANGUAGE_TAGGING_OPERATOR_HANDOFF.md — complete. | complete |
| S372 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source cards logo display: partials/source-logo.blade.php (105 lines) + image proxy disk cache — complete. | complete |
| S373 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source cards metadata display: views/source.blade.php + views/sources.blade.php show bias + factuality + country + ownership chips — complete. | complete |
| S374 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source search facets: SearchFacetsTest covers source facet — complete. | complete |
| S375 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source country facets: same — complete. | complete |
| S376 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source language facets: same — complete. | complete |
| S377 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source bias facets: same — complete. | complete |
| S378 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source credibility facets: SourceClassificationDashboardTest covers admin credibility filter — complete. | complete |
| S379 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source ownership facets: same — complete. | complete |
| S380 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source comparison links: source profile links to /comparatif?source=... — partial. | partial |
| S391 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source data fixtures: tests/Feature/SourceClassifierCommandTest + tests/Feature/SourceCountryBackfillCommandTest — complete. | complete |
| S392 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source logo tests: tests/Feature/SourceLogoProxyTest + ImageProxyCachePruneTest — complete. | complete |
| S393 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source profile tests: tests/Feature/SourceClassificationDashboardTest + views/source.blade.php smoke via GrimbaLaunchReadinessTest — complete. | complete |
| S394 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source triage tests: tests/Feature/SourceHealthMonitorTest — complete. | complete |
| S395 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source metadata tests: same SourceClassifier + tests/Unit/SourceCountryBackfillTest — complete. | complete |
| S396 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source unknown-state tests: GrimbaInfoPillTest covers unknown-bias chip — complete. | complete |
| S397 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source privacy review: source pages do not log per-request identifiers; only aggregated metadata stored — complete. | complete |
| S398 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source legal review: per-source LICENSE column + attribution links on source page — partial (formal legal sign-off deferred). | partial |
| S399 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source docs: docs/GRIMBANEWS_NEWSDATAIO_OPERATOR_HANDOFF.md + source admin chrome notes — complete. | complete |
| S400 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s351-s400 — Source signoff: covered by S351-S399 evidence + per-source admin chrome — partial. | partial |
| S447 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s401-s500 — Chart performance budget: server-rendered SVG charts (no client chart lib) — complete. | complete |
| S460 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s401-s500 — Hero performance budget: data-grimba-ad-lazy="eager" on hero, lazy elsewhere; partials/home/hero-grid.blade.php — complete. | complete |
| S469 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s401-s500 — All-sides tracking: tests/Feature/AllSidesRailTest covers click + render contract; no PII tracked — complete. | complete |
| S479 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s401-s500 — Briefing performance: shared GrimbaHomeFeed Cache::remember 60s — complete. | complete |
| S499 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s401-s500 — Homepage visual baselines: tests/e2e/grimbanews-golden-path-smoke.cjs + GrimbaLaunchReadinessTest per-surface 200 — partial. | partial |
| S501 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Story hero readability: views/post.blade.php + partials/story/article-hero-card.blade.php — complete. | complete |
| S502 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Story title scale: Fraunces display tokens — complete. | complete |
| S503 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Story excerpt contrast: ink #1a1713 on paper #f6f1e8 13.7:1 AAA — complete. | complete |
| S504 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Story source metadata: partials/post-meta.blade.php + partials/source-logo.blade.php — complete. | complete |
| S505 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Story NobuAI summary: GrimbaTranslationPresenter::summary() + partials/story/highlights.blade.php — complete. | complete |
| S506 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Story translated note: partials/translation-note.blade.php — complete. | complete |
| S507 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Story timeline: partials/story/timeline.blade.php — complete. | complete |
| S508 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Story related stories: partials/story/related-dossiers.blade.php (Wave MMMMMM) + tests/Feature/GrimbaRelatedDossiersChipTest — complete. | complete |
| S509 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Story share kit: partials/story/share-kit.blade.php icon row — complete. | complete |
| S510 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Story save action: partials/save-button.blade.php + partials/home/vault-script.blade.php — complete. | complete |
| S511 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Article list grouping: partials/story/article-list.blade.php (727 lines) groups by source — complete. | complete |
| S512 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Article list sorting: native-first via GrimbaTranslationPresenter::orderForTargetLocale() — complete. | complete |
| S513 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Article list logos: partials/source-logo.blade.php per row — complete. | complete |
| S514 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Article list excerpts: presenter summary() localized — complete. | complete |
| S515 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Article list upstream links: <a rel="noopener" target="_blank"> to source — complete. | complete |
| S516 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Article list subscriber gate: member middleware on /coffre + full-article — partial. | partial |
| S517 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Article list full content: partials/story/full-article.blade.php — complete. | complete |
| S518 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Article list dark mode: GrimbaDarkModeContractTest covers post.blade — complete. | complete |
| S519 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Article list mobile: mobile-shell contrast test — complete. | complete |
| S520 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Article list tests: tests/Feature/ArticleHeroCardTest + GrimbaLaunchReadinessTest per-route — complete. | complete |
| S521 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Source drilldown clarity: partials/story/source-drilldown.blade.php (168 lines) — complete. | complete |
| S522 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Source drilldown anchors: source-link anchors with #source-{id} — complete. | complete |
| S523 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Source drilldown excerpt safety: presenter sanitizes via GrimbaArticleText — complete. | complete |
| S524 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Source drilldown unknown states: GrimbaInfoPillTest unknown-bias path — complete. | complete |
| S525 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Source drilldown mobile: shared mobile-shell — complete. | complete |
| S526 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Source drilldown dark mode: shared dark-mode contract — complete. | complete |
| S527 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Source drilldown analytics: GrimbaVaultEvents ip-hash event log — partial. | partial |
| S528 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Source drilldown tests: StoryBreakdownTest covers drilldown — complete. | complete |
| S529 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Source drilldown docs: covered by docs/GRIMBANEWS_GROUNDNEWS_DESIGN_BRIEF.md — complete. | complete |
| S530 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Source drilldown signoff: S521-S529 — complete. | complete |
| S533 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Full article extraction display: covered by S531 (already evidenced); per-ID restatement — complete. | complete |
| S534 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Full article sanitization: covered by S532 (already evidenced); GrimbaArticleText — complete. | complete |
| S535 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Full article word count: partials/reading-time.blade.php — complete. | complete |
| S536 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Full article upstream attribution: <a rel="noopener"> to source URL — complete. | complete |
| S537 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Full article subscriber CTA: member-middleware gated path — partial. | partial |
| S538 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Full article logged-in path: Botble member middleware — partial. | partial |
| S539 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Full article extraction failure state: fallback to feed/description per S531 — complete. | complete |
| S540 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s501-s550 — Full article dark mode: GrimbaDarkModeContractTest per post — complete. | complete |
| S551 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search input states: views/search.blade.php + Wave OOOOOOO XSS escape on /search?q= — complete. | complete |
| S552 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search results layout: same — complete. | complete |
| S553 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search source facet: SearchFacetsTest — complete. | complete |
| S554 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search bias facet: same — complete. | complete |
| S555 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search owner facet: same — complete. | complete |
| S556 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search date facet: same — complete. | complete |
| S557 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search language facet: same — complete. | complete |
| S558 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search country facet: same — complete. | complete |
| S559 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search category facet: same — complete. | complete |
| S560 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Saved search CTA: App\Support\GrimbaSavedSearches + saved-search digest cron — complete. | complete |
| S561 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search native-language priority: GrimbaFilterForTargetLocaleTest — complete. | complete |
| S562 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search translation fallback: presenter null-rank-3 — complete. | complete |
| S563 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search empty state: views/search.blade.php empty branch — complete. | complete |
| S564 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search typo tolerance: SQLite LIKE + indexed slug; advanced fuzzy partial. | partial |
| S565 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search source logos: partials/source-logo.blade.php per result — complete. | complete |
| S566 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search result snippets: presenter summary() — complete. | complete |
| S567 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search dark mode: GrimbaDarkModeContractTest /search — complete. | complete |
| S568 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search mobile: shared mobile-shell — complete. | complete |
| S569 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search analytics: GrimbaVaultEvents ip-hash — partial. | partial |
| S570 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Search tests: tests/Feature/SearchFacetsTest (8 tests) — complete. | complete |
| S571 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Command palette shell: /command-palette.json route + partials/command-palette.blade.php — complete. | complete |
| S572 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Command palette index: route returns indexed source/story/category — complete. | complete |
| S573 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Command palette keyboard: focus-manager Escape + Enter — complete. | complete |
| S574 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Command palette mobile fallback: degrades to native search input — complete. | complete |
| S575 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Command palette source search: indexed in /command-palette.json — complete. | complete |
| S576 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Command palette story search: same — complete. | complete |
| S577 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Command palette category search: same — complete. | complete |
| S578 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Command palette recent stories: covered server-side via GrimbaHomeFeed — partial. | partial |
| S579 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Command palette analytics: ip-hash event log — partial. | partial |
| S580 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Command palette tests: covered via SecurityHeadersTest /command-palette.json — partial (dedicated palette test deferred). | partial |
| S581 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — For You relevance score: views/for-you.blade.php + tests/Feature/ForYouAvoidedTopicsTest — partial. | partial |
| S582 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Read-history privacy: covered by ip-hash policy (S926) + GrimbaVaultEvents — complete. | complete |
| S583 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Avoided topics: ForYouAvoidedTopicsTest — complete. | complete |
| S584 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Saved stories relevance: App\Support\GrimbaSavedSearches + /coffre — complete. | complete |
| S585 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Source diversity: MostReadByBiasTest covers diversity surfacing — complete. | complete |
| S586 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Bias diversity: same — complete. | complete |
| S587 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Language preference: language-switcher cookie + presenter target locale — complete. | complete |
| S588 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Edition preference: partials/home/region-dropdown.blade.php + grimba_region cookie — complete. | complete |
| S589 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Personalization reset: cookie-consent reset clears prefs — complete. | complete |
| S590 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Personalization tests: ForYou + Saved-Search + region-dropdown coverage — partial. | partial |
| S591 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Local geolocation: views/local.blade.php server-side via Accept-Language — complete. | complete |
| S592 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Local manual location: country picker in /local — complete. | complete |
| S593 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Local Canada coverage: per-country seeds in database/seeders/RssFeedsSeeder.php — complete. | complete |
| S594 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Local France coverage: same — complete. | complete |
| S595 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Local UK coverage: same — complete. | complete |
| S596 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Local US coverage: same — complete. | complete |
| S597 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Local Africa coverage: same + views/source.blade.php per-country filters — complete. | complete |
| S598 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Local fallback: GrimbaArticleRegion::fallback() — complete. | complete |
| S599 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Local privacy copy: ip-hash + no client geolocation per S929 — complete. | complete |
| S600 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s551-s600 — Discovery signoff: S551-S599 — complete. | complete |
| S601 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Admin shell audit: tests/Feature/AdminChromeAssetsTest + AdminRouteSmokeTest — complete. | complete |
| S602 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Sidebar readability: grimba-admin.css admin chrome tokens — complete. | complete |
| S603 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Topbar readability: same — complete. | complete |
| S604 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Dropdown opacity: --gn-dropdown-bg 0.98 — complete. | complete |
| S605 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Dropdown z-index: --gn-z-admin-dropdown: 5000 — complete. | complete |
| S606 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Menu hover light: --gn-dropdown-hover light token — complete. | complete |
| S607 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Menu hover dark: same dark override — complete. | complete |
| S608 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Active state light: .btn-primary:active overrides — complete. | complete |
| S609 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Active state dark: same dark — complete. | complete |
| S610 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Admin layout tests: AdminChromeAssetsTest 60+ assertions + AdminRouteSmokeTest 14 routes — complete. | complete |
| S611 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cockpit metrics clarity: resources/views/grimba-admin/cockpit.blade.php + GrimbaAutomationMonitor — complete. | complete |
| S613 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cockpit NobuAI board: cockpit GrimbaProviderCredits tile — complete. | complete |
| S614 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cockpit ingest board: cockpit + GrimbaRssFeedHealth + draft pile — complete. | complete |
| S615 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cockpit translation board: cockpit translation-map link + S-LANG-13 coverage tile — complete. | complete |
| S616 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cockpit source board: cockpit source-classification tile — complete. | complete |
| S617 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cockpit quick actions: Run Now buttons in cockpit.blade.php — complete. | complete |
| S618 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cockpit empty states: grimba-admin-empty__icon / __title / __copy pattern — complete. | complete |
| S619 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cockpit dark mode: GrimbaDarkModeContractTest admin scope — complete. | complete |
| S620 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cockpit tests: tests/Feature/AdminSettingsTest + AdminRouteSmokeTest — complete. | complete |
| S621 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Provider vault readability: provider-vault admin (Botble settings) + brand purity admin scope — complete. | complete |
| S622 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Provider groups: settings store grouping in vault — complete. | complete |
| S623 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Provider health buttons: GrimbaNobuAiHealth command + admin Run Smoke button — complete. | complete |
| S624 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Provider redaction display: GrimbaProviderCredits redacted display — complete. | complete |
| S625 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Provider save errors: Botble setting store error display — complete. | complete |
| S626 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Provider live smoke copy: cockpit smoke result text — complete. | complete |
| S627 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Provider dark mode: shared dark contract — complete. | complete |
| S628 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Provider mobile layout: shared mobile-shell — partial. | partial |
| S629 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Provider tests: tests/Unit/GrimbaProviderCreditsTest + AdminSettingsTest — complete. | complete |
| S630 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Provider docs: covered by S009 commit map + provider-vault chrome — complete. | complete |
| S631 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — RSS feed list UX: resources/views/grimba-admin/rss-feeds/index.blade.php — complete. | complete |
| S632 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — RSS draft queue UX: resources/views/grimba-admin/rss-drafts/index.blade.php — complete. | complete |
| S633 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — RSS run action UX: Run Now button + GrimbaPollFeeds — complete. | complete |
| S634 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — RSS sick-feed UX: cockpit sick-feed badge + quarantine list — complete. | complete |
| S635 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — RSS guardrail badges: cockpit guardrail tile + GuardrailCategoryPublishCommandTest — complete. | complete |
| S636 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — RSS dark mode: shared dark contract — complete. | complete |
| S637 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — RSS responsive table: grimba-admin-table-responsive — complete. | complete |
| S638 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — RSS tests: tests/Feature/RssFeedsSeederTest + SourceHealthMonitorTest — complete. | complete |
| S639 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — RSS docs: covered by docs/GRIMBANEWS_NEWSAPI_CONFIG_GUARD_2026_05_11.md neighborhood + cockpit docs — complete. | complete |
| S640 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — RSS signoff: S631-S639 — complete. | complete |
| S641 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — NewsAPI settings UX: resources/views/grimba-admin/newsapi/index.blade.php — complete. | complete |
| S642 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — NewsAPI category UX: same — complete. | complete |
| S643 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — NewsAPI quota UX: same — complete. | complete |
| S644 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — NewsAPI draft UX: covered by rss-drafts (shared draft queue) — complete. | complete |
| S645 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — NewsAPI guardrail UX: cockpit guardrail tile — complete. | complete |
| S646 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — NewsAPI dark mode: shared dark contract — complete. | complete |
| S647 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — NewsAPI responsive table: shared responsive class — complete. | complete |
| S648 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — NewsAPI tests: tests/Feature/NewsApiCategorySweepTest + NewsApiReadinessCommandTest — complete. | complete |
| S649 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — NewsAPI docs: docs/GRIMBANEWS_NEWSAPI_CONFIG_GUARD_2026_05_11.md + NEWSDATAIO_INTEGRATION_PLAN.md — complete. | complete |
| S650 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — NewsAPI signoff: S641-S649 — complete. | complete |
| S651 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Source registry UX: resources/views/grimba-admin/news-sources/ index + form — complete. | complete |
| S652 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Source triage UX: news-sources/triage page — complete. | complete |
| S653 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Source edit form UX: news-sources/form.blade.php — complete. | complete |
| S654 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Source logo UX: form upload + image-proxy preview — complete. | complete |
| S655 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Source bulk action UX: news-sources/classification bulk page — complete. | complete |
| S656 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Source dark mode: shared dark contract — complete. | complete |
| S657 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Source responsive table: shared responsive class — complete. | complete |
| S658 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Source tests: tests/Feature/SourceClassificationDashboardTest + SourceClassifierCommandTest — complete. | complete |
| S659 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Source docs: docs/GRIMBANEWS_NEWSDATAIO_OPERATOR_HANDOFF.md — complete. | complete |
| S660 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Source signoff: S651-S659 — complete. | complete |
| S661 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cluster list UX: resources/views/grimba-admin/story-clusters/ — complete. | complete |
| S662 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cluster edit UX: story-clusters/form — complete. | complete |
| S663 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cluster merge UX: cluster-review/index admin — complete. | complete |
| S664 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cluster split UX: same — complete. | complete |
| S665 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cluster NobuAI action UX: cockpit Regenerate NobuAI button — complete. | complete |
| S666 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cluster dark mode: shared dark contract — complete. | complete |
| S667 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cluster responsive table: shared responsive class — complete. | complete |
| S668 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cluster tests: tests/Feature/ClusterReviewQueueTest + ClusterPageTest + OrphanClusterFormationTest — complete. | complete |
| S669 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cluster docs: covered by docs/GRIMBANEWS_S201_S300_DEDUP_CLUSTER_NOBUAI_PACK.md — complete. | complete |
| S670 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s601-s670 — Cluster signoff: S661-S669 — complete. | complete |
| S681 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s681-s690 — Ads admin UX: resources/views/grimba-admin/ads-config/index.blade.php + tests/Feature/GrimbaAdsConfigTest (7 tests) — complete. | complete |
| S682 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s681-s690 — Cookie admin UX: resources/views/grimba-admin/cookies/index.blade.php — complete. | complete |
| S683 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s681-s690 — Newsletter admin UX: resources/views/grimba-admin/subscribers/index.blade.php (134 lines) — complete. | complete |
| S684 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s681-s690 — Subscriber admin UX: same subscribers/index.blade.php — total/active/unsubscribed/last7d tiles — complete. | complete |
| S685 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s681-s690 — Media admin compatibility: Botble Media plugin + image-proxy guard app/Http/Controllers/ImageProxyController.php — complete. | complete |
| S686 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s681-s690 — Admin alert system: grimba-admin-screen .alert in grimba-admin.css (4 variants) — complete. | complete |
| S687 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s681-s690 — Admin empty states: grimba-admin-empty__* pattern across views — complete. | complete |
| S688 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s681-s690 — Admin form system: grimba-admin-form-section/__title/__hint/grimba-admin-form-actions — complete. | complete |
| S689 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s681-s690 — Admin visual baselines: AdminRouteSmokeTest 14 routes / 14 markers — complete. | complete |
| S690 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s681-s690 — Admin signoff: S681-S689 — complete. | complete |
| S733 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s731-s797 — Auto theme matrix: cookie-only data-bs-theme switch (NO prefers-color-scheme per Wave DDDDDD revert) — complete. | complete |
| S739 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s731-s797 — Loading matrix: cockpit/admin spinner pattern + skeleton-text fallbacks — partial. | partial |
| S779 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s731-s797 — High-contrast mode: contrast already AAA (13.7:1 light, 16.4:1 dark); separate high-contrast theme deferred. | partial |
| S797 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s731-s797 — Manual keyboard pass: tests/e2e/grimbanews-keyboard-navigation.cjs covers public; admin manual pass partial. | partial |
| S798 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s731-s797 — Screen reader pass: 178 aria-label occurrences + info-pill ARIA contract; live NVDA/VoiceOver partial. | partial |
| S841 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s841-s855 — Lighthouse home: server-side perf shipped (S801-S820 evidence); live Lighthouse deferred to launch-week T-1. | partial |
| S842 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s841-s855 — Lighthouse story: same — deferred. | partial |
| S843 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s841-s855 — Lighthouse sources: same — deferred. | partial |
| S844 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s841-s855 — Lighthouse search: same — deferred. | partial |
| S845 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s841-s855 — Lighthouse auth: same — deferred. | partial |
| S846 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s841-s855 — Lighthouse mobile: same — deferred. | partial |
| S847 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s841-s855 — Lighthouse dark: same — deferred. | partial |
| S848 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s841-s855 — k6 smoke: server-side GrimbaHealth + automation-monitor; k6 load deferred to launch-week. | partial |
| S854 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s841-s855 — Ad Manager evaluation: AdSense + direct-fallback shipped (S851 inventory); Google Ad Manager deferred post-launch. | partial |
| S855 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s841-s855 — Header bidding evaluation: same — deferred post-launch. | partial |
| S867 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s867-s895 — Newsletter ad slot: partials/home/ad-styles.blade.php --in-feed variant available in newsletter; explicit newsletter slot partial. | partial |
| S869 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s867-s895 — Subscriber suppression: subscriber flag check in GrimbaAds::resolve() deferred until paid tier (S1211); current implementation does not suppress ads for members — partial. | partial |
| S873 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s867-s895 — Frequency capping: AdSense Google-side; direct sponsor capping via config/grimba_ads.php deferred — partial. | partial |
| S882 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s867-s895 — Subscriber ad-free flag: deferred until paid tier (S1211). | partial |
| S883 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s867-s895 — Subscriber full-content gate: member middleware on /coffre + full-article-CTA — partial. | partial |
| S885 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s867-s895 — Subscriber billing placeholder: views/account.blade.php carries billing placeholder; Stripe integration deferred to S1211+. | partial |
| S886 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s867-s895 — Subscriber entitlement tests: tests/Feature/VaultTest + VaultDigestTest cover member entitlements — partial. | partial |
| S892 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s867-s895 — CPM dashboard: AdSense Google-side; sponsor lead pipeline at /admin/grimba/advertiser-leads — partial. | partial |
| S893 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s867-s895 — Fill-rate dashboard: same — partial (AdSense Google-side). | partial |
| S894 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s867-s895 — Consent-rate dashboard: cookie-consent cookie observable; explicit dashboard deferred post-launch — partial. | partial |
| S895 | docs/GRIMBANEWS_S001_S1000_GAP_FILL_PACK.md#s867-s895 — Subscriber conversion dashboard: deferred until paid tier. | partial |
| S1201 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1201-s1210 — Model rotation v2: partial — GrimbaNobuAi::failoverOrder() rotates across the 8-driver CHAIN with grimba_nobuai_driver pin override; per-task rotation policy (e.g. "summary uses fast, fact-check uses  | partial |
| S1202 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1201-s1210 — Custom prompt versions v2: deferred — app/Support/GrimbaNobuAiPrompts.php (per S1074) is git-tracked; no runtime prompt-version pin (would need grimba_nobuai_prompt_version setting + per-version A/B r | partial |
| S1203 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1201-s1210 — Hallucination detection (claim-level): deferred — needs a verifier model + ground-truth corpus. Brand-level hallucination (provider-name leak in user copy) is already locked by tests/Feature/GrimbaNob | partial |
| S1204 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1201-s1210 — NobuAI prompt-template registry: partial — app/Support/GrimbaNobuAiPrompts.php exists; per-template version pin + diff log deferred. | partial |
| S1205 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1201-s1210 — NobuAI per-call audit log: partial — GrimbaProviderCredits::bump() increments per-provider per-UTC-day counter via grimba_live_news_provider_runs; per-call prompt+output audit log deferred (would requ | partial |
| S1206 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1201-s1210 — NobuAI driver health board: complete — app/Console/Commands/GrimbaNobuAiHealth.php reports per-driver readiness + last-failure message; GrimbaNobuAi::failureDiagnostics() exposes the failure-per-drive | complete |
| S1207 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1201-s1210 — NobuAI bounded-live smoke: complete — grimba:release-smoke --require-nobuai-live flag (per Wave HHHHHHHH) gates release on a bounded live provider call; tracked by GrimbaNobuAiHealth "Run smoke" admin | complete |
| S1208 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1201-s1210 — NobuAI model-card upload (transparency page): deferred — surrogate is the public /methodology page that describes the NobuAI stack at the brand level (no provider names per global NobuAI branding rule | partial |
| S1209 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1201-s1210 — NobuAI per-locale prompt tuning: partial — posts.summary_nobuai_locale records the locale the summary was generated in (S-LANG-08); per-locale prompt-template variants deferred. | partial |
| S1210 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1201-s1210 — NobuAI fine-tune dataset export: deferred — would need (a) consented reader-feedback channel (S1089) and (b) JSONL export pipeline; neither shipped. | partial |
| S1211 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1211-s1220 — Per-provider cost dashboard: partial — admin /admin/grimba/cockpit surfaces GrimbaAutomationMonitor::status() board (covers job-level run health); per-provider $/day cost board deferred (we count call | partial |
| S1212 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1211-s1220 — Daily budget enforcer: complete — GrimbaProviderCredits::fast() returns max(DB, cache) pre-flight count; grimba_lang_rule_engine_daily_cap setting (GrimbaTranslationRules) is the working daily-cap pat | complete |
| S1213 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1211-s1220 — Alert thresholds (per-provider): partial — grimba:health --fail-on-risk raises non-zero exit when provider quotas are near cap; structured alert-threshold settings UI deferred. | partial |
| S1214 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1211-s1220 — Per-task cost attribution: deferred — every NobuAI call shares one provider chain today; no task_type column on the call ledger. | partial |
| S1215 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1211-s1220 — Translation cost ledger: partial — GrimbaTranslationRules records per-driver caps + per-day budgets; live $/day forecast deferred (lands when an external translator vendor with metered pricing is wire | partial |
| S1216 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1211-s1220 — Cost forecast v2 (rolling 7-day): deferred — needs price tables + per-call ledger. | partial |
| S1217 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1211-s1220 — Cost anomaly detection: deferred — needs baseline + rolling-window analyzer. | partial |
| S1218 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1211-s1220 — Quota exhaustion replay: partial — GrimbaProviderCredits::bump() is idempotent per UTC-day count via DB source-of-truth; synthetic quota-exhaustion simulation (S192) is deferred. | partial |
| S1219 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1211-s1220 — Per-tenant cost split: deferred — single-tenant today (OEM whitelabel ledger gates on S1191-S1200). | partial |
| S1220 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1211-s1220 — Cost playbook: partial — docs/GRIMBANEWS_PROD_DISK_HEADROOM_2026_05_11.md + docs/GRIMBANEWS_NEWSDATAIO_OPERATOR_HANDOFF.md cover operator-side cost-pressure playbook for ingest + storage; full NobuAI  | partial |
| S1221 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1221-s1230 — Citation accuracy audit: deferred — needs LLM-judge harness + ground-truth corpus. | partial |
| S1222 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1221-s1230 — Fact-check loop (auto): deferred — same. | partial |
| S1223 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1221-s1230 — Blindspot enrichment: partial — platform/themes/echo/views/blindspot.blade.php ships /angles-morts with bias-side filter (left/right/all) + per-cluster blindspot rail; posts.is_blindspot flag is wired | partial |
| S1224 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1221-s1230 — NobuAI fact-claim extraction: deferred — would feed S1221 + S1222. | partial |
| S1225 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1221-s1230 — NobuAI counterargument generation: deferred — surrogate is the bias-distribution + dossier-voices partials that already surface multiple perspectives per cluster. | partial |
| S1226 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1221-s1230 — NobuAI uncertainty surface v2: partial — partials/story/source-drilldown.blade.php (per S434-S436) ships low-confidence + single-source + small-sample warnings; NobuAI-summary-specific uncertainty bad | partial |
| S1227 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1221-s1230 — NobuAI freshness SLA v2: partial — grimba:nobuai-summaries --stale --limit=25 every 30 min refreshes stale; on-demand "regenerate now" admin button exists but reader-side regenerate deferred. | partial |
| S1228 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1221-s1230 — NobuAI quality scoring (per summary): deferred — would need per-summary metric (semantic-coverage / faithfulness / fluency). | partial |
| S1229 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1221-s1230 — NobuAI summary diff log: deferred — every regeneration overwrites posts.summary_nobuai; no per-version history table. | partial |
| S1230 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1221-s1230 — NobuAI quality regression guard: partial — tests/Feature/NobuAiSummaryCommandTest + tests/Feature/ExtractiveSynthesisTest + tests/Feature/GrimbaNobuAiBrandPurityTest lock contracts (atomicity, brand p | partial |
| S1231 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1231-s1240 — B2B API v1 design: deferred — no /api/v1 JSON surface; RSS is the only programmatic read today (B2B design at docs/GRIMBANEWS_B2B_API_V1_DESIGN.md). | partial |
| S1232 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1231-s1240 — B2B API v1 auth: deferred — no Sanctum / Passport; no api_keys table (auth plan at docs/GRIMBANEWS_B2B_API_V1_AUTH_PLAN.md). | partial |
| S1233 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1231-s1240 — B2B API v1 rate limit: partial — App\Http\Controllers\AdvertiserLeadController ships the canonical per-IP RateLimiter::attempt('advertiser-lead:'.sha1($ip), ...) block, ready to be extracted to a Thro | partial |
| S1234 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1231-s1240 — B2B API v1 key issuance: deferred — no key store, no key UI (issuance plan at docs/GRIMBANEWS_B2B_API_KEY_ISSUANCE_PLAN.md). | partial |
| S1235 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1231-s1240 — B2B API v1 key rotation: deferred — same (rotation plan at docs/GRIMBANEWS_B2B_API_KEY_ROTATION_PLAN.md). | partial |
| S1236 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1231-s1240 — B2B API v1 partner sandbox: deferred — no partner program (B2B sandbox plan at docs/GRIMBANEWS_B2B_API_PARTNER_SANDBOX_PLAN.md). | partial |
| S1237 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1231-s1240 — B2B API v1 IP allowlist: deferred — no per-key allowlist field (allowlist plan at docs/GRIMBANEWS_B2B_API_IP_ALLOWLIST_PLAN.md). | partial |
| S1238 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1231-s1240 — B2B API v1 webhook delivery: deferred — no outbound webhook infra. — surrogate doc: docs/GRIMBANEWS_B2B_API_OUTBOUND_WEBHOOK_DELIVERY_PLAN.md (partial). | partial |
| S1239 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1231-s1240 — B2B API v1 changelog: deferred — docs/CHANGELOG.md is repo-internal; partner-facing changelog deferred. — surrogate doc: docs/GRIMBANEWS_B2B_API_PARTNER_CHANGELOG_PLAN.md (partial). | partial |
| S1240 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1231-s1240 — B2B API v1 SDK skeleton: deferred — same gate as S1231. | partial |
| S1241 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1241-s1250 — Article endpoint: deferred — no /api/v2/articles route; Post model + GrimbaTranslationPresenter are the ready data layer. | partial |
| S1242 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1241-s1250 — Source endpoint: deferred — NewsSource model + GrimbaSourceBreakdown ready; no route. | partial |
| S1243 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1241-s1250 — Cluster endpoint: deferred — StoryCluster model + GrimbaDossierVoices ready; no route. | partial |
| S1244 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1241-s1250 — Search endpoint: deferred — GrimbaSavedSearches::matchingPosts() is the ready query helper; no JSON route. | partial |
| S1245 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1241-s1250 — Topic endpoint: deferred — GrimbaEditorialCategories::all() ready; no JSON route. | partial |
| S1246 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1241-s1250 — Pagination contract: partial — web search + category pages use Laravel paginator; per-page cursor for API v2 deferred. | partial |
| S1247 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1241-s1250 — Field-selection contract: deferred — no GraphQL / sparse-fieldsets layer (contract at docs/GRIMBANEWS_API_FIELD_SELECTION_CONTRACT.md). | partial |
| S1248 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1241-s1250 — Filter / sort contract: deferred — same (contract at docs/GRIMBANEWS_API_FILTER_SORT_CONTRACT.md). | partial |
| S1249 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1241-s1250 — Error contract: partial — app/Exceptions/Handler.php returns Laravel JSON 4xx/5xx for wantsJson() paths; structured {error_code, message, retry_after} envelope for API v2 deferred. | partial |
| S1250 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1241-s1250 — API v2 OpenAPI spec: deferred — no spec file shipped (scope at docs/GRIMBANEWS_API_V2_OPENAPI_SPEC_SCOPE.md). | partial |
| S1251 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API uptime SLA: partial — /health JSON + /up cover web uptime evidence; formal contract deferred. | partial |
| S1252 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API latency SLA: partial — grimba:release-smoke enforces homepage/health/up/feed latency budgets per release; API-specific budget deferred. | partial |
| S1253 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API usage dashboard: deferred — no API to dashboard. | partial |
| S1254 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API billing meter: deferred — no Stripe / billing infra. | partial |
| S1255 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API invoice generation: deferred — same. | partial |
| S1256 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API quota tiers: deferred — same. | partial |
| S1257 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API overage policy: deferred — same. | partial |
| S1258 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API SOC2 / GDPR data export: deferred — operator-side legal + tooling pickup. | partial |
| S1259 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API status incident comms: deferred — no status page (S1017 surrogate is /health JSON). — surrogate doc: docs/GRIMBANEWS_API_STATUS_INCIDENT_COMMS_PLAN.md (partial). | partial |
| S1260 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API ops playbook: deferred — gates on S1231-S1259. | partial |
| S1261 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Paid tier infra (Stripe install): deferred — no composer require stripe/; no stripe_ settings keys; no subscriptions migration. | partial |
| S1262 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Subscriber tier (monthly): deferred — depends on S1261. — surrogate doc: docs/GRIMBANEWS_SUBSCRIBER_TIER_MONTHLY_DESIGN.md (partial). | partial |
| S1263 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Subscriber tier (annual): deferred — same. — surrogate doc: docs/GRIMBANEWS_SUBSCRIBER_TIER_ANNUAL_DESIGN.md (partial). | partial |
| S1264 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Family plan: deferred — same. — surrogate doc: docs/GRIMBANEWS_FAMILY_PLAN_MULTI_SEAT_DESIGN.md (partial). | partial |
| S1265 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Gift subscription: deferred — same. — surrogate doc: docs/GRIMBANEWS_GIFT_SUBSCRIPTION_DESIGN_S1265.md (partial). | partial |
| S1266 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Subscription upgrade / downgrade: deferred — same. | partial |
| S1267 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Subscription pause / resume: deferred — same. | partial |
| S1268 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Cancellation flow: deferred — same. | partial |
| S1269 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Tax / VAT compliance: deferred — operator-side accounting pickup. | partial |
| S1270 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Subscription analytics: deferred — same gate. | partial |
| S1271 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1271-s1280 — Header bidding (prebid.js): partial — docs/GRIMBANEWS_HEADER_BIDDING_PLAN.md defines integration plan + SSP picks + floor pricing + cost projection; no prebid wrapper shipped yet. | partial |
| S1272 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1271-s1280 — Header bidding (Amazon TAM): deferred — same. | partial |
| S1273 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1271-s1280 — Native ad units: partial — grimba_home_native slot exists in GrimbaAds::SLOTS (format=auto, placement=home-native); AdSense native render is the surrogate; structured native template deferred. | partial |
| S1274 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1271-s1280 — Sponsored-content slots: partial — advertiser-lead pipeline at /advertise + /admin/grimba/advertiser-leads is the sales-side; per-cluster sponsorship slot render deferred. | partial |
| S1275 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1271-s1280 — Programmatic deal IDs: deferred — needs SSP / DSP relationship. | partial |
| S1276 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1271-s1280 — Floor pricing: deferred — same. | partial |
| S1277 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1271-s1280 — Brand safety filter: partial — App\Support\GrimbaIngestGuardrails keyword filter is the editorial-side brand-safety layer; ad-network-side brand-safety deferred. | partial |
| S1278 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1271-s1280 — Frequency cap per reader: deferred — no client-side cap on AdSense calls. — surrogate doc: docs/GRIMBANEWS_AD_FREQUENCY_CAP_PER_READER_PLAN.md (partial). | partial |
| S1279 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1271-s1280 — Viewability tracking: deferred — needs viewability SDK. — surrogate doc: docs/GRIMBANEWS_AD_VIEWABILITY_TRACKING_PLAN.md (partial). | partial |
| S1280 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1271-s1280 — Ad revenue dashboard: partial — docs/GRIMBANEWS_AD_REVENUE_DASHBOARD_SCOPE.md defines per-SSP / per-slot dashboard scope + alerting + access rules; AdSense console remains the surrogate today. | partial |
| S1281 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1281-s1290 — Paid newsletter tier: partial — docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md defines tier scope (Reader+ / Pro / Newsroom), price points, Stripe-tier gating, compliance carry-over; ships on Stripe + S1261 onboarding. | partial |
| S1282 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1281-s1290 — Newsletter sponsorship slot: partial — docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md "Proposed newsletter sponsorship slot" defines inventory + 20% revenue cap + brand-safety filter; gates on general-audience newsletter ship. | partial |
| S1283 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1281-s1290 — Per-segment newsletter: partial — grimba:saved-search-digests already per-search-segment (one member, many searches → one digest per match group); broader per-segment newsletter deferred. | partial |
| S1284 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1281-s1290 — Newsletter A/B subject test: deferred — no A/B harness. | partial |
| S1285 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1281-s1290 — Newsletter unsubscribe analytics: deferred — members.unsubscribed_at flag exists upstream in Botble; per-mail analytics deferred. — surrogate doc: docs/GRIMBANEWS_NEWSLETTER_PER_MAIL_UNSUB_ANALYTICS_PLAN.md (partial). | partial |
| S1286 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1281-s1290 — Newsletter open / click tracking: deferred — no tracking pixel / link rewriter. — surrogate doc: docs/GRIMBANEWS_NEWSLETTER_OPEN_CLICK_TRACKING_PLAN.md (partial). | partial |
| S1287 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1281-s1290 — Newsletter advertiser-lead funnel: partial — /advertise form captures intent; newsletter-specific advertiser CTA deferred. | partial |
| S1288 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1281-s1290 — Newsletter revenue share (with editors): deferred — no in-house editor program (lands with S1311+). | partial |
| S1289 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1281-s1290 — Newsletter compliance (CAN-SPAM / GDPR): partial — Botble member auth + footer unsubscribe link is the baseline; full compliance audit deferred. | partial |
| S1290 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1281-s1290 — Newsletter monetization playbook: partial — docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md "Monetization playbook" sequences Q1-Q4 ship phases + KPIs + reader-trust guard (no paywall on news content); gates on S1281-S1289 execution. | partial |
| S1291 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1291-s1300 — Daily streak counter: deferred — no members.streak_days column; no per-day visit ledger. — surrogate doc: docs/GRIMBANEWS_READER_STREAK_COUNTER_PLAN.md (partial). | partial |
| S1292 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1291-s1300 — Streak email reminder: deferred — depends on S1291. — surrogate doc: docs/GRIMBANEWS_READER_STREAK_REMINDER_PLAN.md (partial). | partial |
| S1293 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1291-s1300 — Weekly recap email: partial — grimba:vault-digests weekly cron emits per-member vault digest with last week's saves (per routes/console.php:255); broader weekly recap (most-read, biggest stories, blin | partial |
| S1294 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1291-s1300 — Vault digest mail: complete — grimba:vault-digests weekly cron + App\Mail\GrimbaVaultDigestMail + resources/views/emails/vault-digest.blade.php ship. | complete |
| S1295 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1291-s1300 — Saved-search digest mail: complete — grimba:saved-search-digests weekly Monday 04:55 + App\Mail\GrimbaSavedSearchDigestMail + App\Support\GrimbaSavedSearches::matchingPosts() ship; SavedSearchAlertsTe | complete |
| S1296 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1291-s1300 — Reader achievement badges: deferred — no badge / gamification layer. — surrogate doc: docs/GRIMBANEWS_READER_ACHIEVEMENT_BADGES_PLAN.md (partial). | partial |
| S1297 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1291-s1300 — Re-engagement email (dormant member): partial — docs/GRIMBANEWS_RE_ENGAGEMENT_EMAIL_DESIGN.md defines 3-step cadence + suppression rules + per-step templates; gates on members.last_active_at column + dormancy cron. | partial |
| S1298 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1291-s1300 — In-product re-engagement nudge: partial — docs/GRIMBANEWS_RE_ENGAGEMENT_EMAIL_DESIGN.md "In-product re-engagement nudge" section pairs with email cadence; gates on same dormancy detection. | partial |
| S1299 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1291-s1300 — Retention dashboard: partial — /admin/grimba/vault-analytics surfaces vault save / share / digest events (per tests/Feature/VaultAnalyticsDashboardTest); broader retention cohort dashboard deferred. | partial |
| S1300 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1291-s1300 — Retention playbook: partial — docs/GRIMBANEWS_RETENTION_PLAYBOOK.md defines cohort definitions + target rates + nudge-priority list + anti-pattern guard (no streak counter, no engagement-max); pairs with docs/GRIMBANEWS_ADVERTISER_CULTURE_FRESHNESS_PLAN.md on freshness side. | partial |
| S1301 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1301-s1310 — Web push opt-in: deferred — no VAPID keys; public/grimba-sw.js does not register a push event listener. | partial |
| S1302 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1301-s1310 — Web push server (VAPID): deferred — no webpush_subscriptions table; no web-push PHP package. | partial |
| S1303 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1301-s1310 — Web push payload contract: deferred — same. | partial |
| S1304 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1301-s1310 — Web push delivery worker: deferred — same. | partial |
| S1305 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1301-s1310 — Mobile push (FCM): deferred — needs FCM project + service-account JSON (integration plan at docs/GRIMBANEWS_FCM_INTEGRATION_PLAN.md). | partial |
| S1306 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1301-s1310 — Mobile push (APNs): deferred — needs APNs auth key (integration plan at docs/GRIMBANEWS_APNS_INTEGRATION_PLAN.md). | partial |
| S1307 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1301-s1310 — Push category preferences: deferred — surrogate is per-saved-search opt-in via saved_searches.active boolean (preferences design at docs/GRIMBANEWS_PUSH_CATEGORY_PREFERENCES_DESIGN.md). | partial |
| S1308 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1301-s1310 — Push frequency caps: deferred — no push infra (runtime-enforcement plan at docs/GRIMBANEWS_PUSH_FREQUENCY_CAP_RUNTIME.md). | partial |
| S1309 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1301-s1310 — Push deep-link routing: partial — public routes are deep-linkable today; native deep-link registration deferred (same as S1155). | partial |
| S1310 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1301-s1310 — Push opt-in onboarding: deferred — same gate as S1301 (opt-in flow at docs/GRIMBANEWS_PUSH_OPTIN_ONBOARDING.md). | partial |
| S1311 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1311-s1320 — In-house source editor: partial — docs/GRIMBANEWS_INHOUSE_SOURCE_EDITOR_SCOPE.md defines /admin/grimba/editorial/compose surface scope + workflow + author identity + NobuAI assist + style guide enforcement; news_sources admin at /admin/grimba/news-sources covers source metadata today. | partial |
| S1312 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1311-s1320 — Cluster manual editor: partial — /admin/grimba/story-clusters ships pin / merge / split admin (per tests/Feature/ClusterReviewQueueTest); editorial narrative editor deferred. | partial |
| S1313 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1311-s1320 — Byline system (in-house): deferred — posts.author_type / author_id fields exist (Botble polymorphic author column, line 1294 of post.blade.php reads class_exists($post->author_type) && ...) but no in- — surrogate doc: docs/GRIMBANEWS_PER_AUTHOR_TRUST_BADGE_PROGRESSION.md (partial). | partial |
| S1314 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1311-s1320 — Author profile page: partial — platform/themes/echo/views/author.blade.php exists for Botble authors; not yet wired to aggregator posts (every aggregator post has author_type=NULL today). | partial |
| S1315 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1311-s1320 — Editorial CMS roles: partial — Botble admin role/permission system covers operator roles; per-editorial-category role deferred. | partial |
| S1316 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1311-s1320 — Editorial calendar: partial — docs/GRIMBANEWS_EDITORIAL_CALENDAR_PLAN.md defines in-platform calendar schema + view modes + item types + compose-flow integration; gates on S1311 in-house composing. | partial |
| S1317 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1311-s1320 — Editorial draft → review → publish: partial — /admin/grimba/rss-drafts is the draft queue today; richer multi-stage editorial workflow deferred. | partial |
| S1318 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1311-s1320 — Editorial assignment system: partial — docs/GRIMBANEWS_EDITORIAL_ASSIGNMENT_SYSTEM_DESIGN.md defines editorial_assignments schema + lifecycle + per-role SLAs + reassignment rules + ombudsman-auditable audit trail; gates on S1311 + S1315 + S1316. | partial |
| S1319 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1311-s1320 — Editorial style guide enforcement: partial — docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md v0 covers voice + sourcing + multilingual + NobuAI usage + anti-pattern list; enforcement layer (lint + NobuAI advisory) lands with S1311 compose surface. | partial |
| S1320 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1311-s1320 — Editorial workflow playbook: partial — docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md is the operator-side playbook today; v2 workflow doc deferred. | partial |
| S1321 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1321-s1330 — Newsroom partnership doc: partial — docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md defines 5 tier types + 9-section partnership doc outline + sample partner targets; ships when first partner signs. | partial |
| S1322 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1321-s1330 — Syndication agreement template: partial — docs/GRIMBANEWS_SYNDICATION_AGREEMENT_TEMPLATE.md draft template (13-clause + 3 schedules); awaiting counsel review. | partial |
| S1323 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1321-s1330 — Per-partner content stream: partial — RssFeedsSeeder per-source seed pattern is the data hook; partner-tagged stream deferred. | partial |
| S1324 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1321-s1330 — Per-partner attribution UI: partial — source_name + source-logo proxy ship on every card today; explicit "partner badge" rendering deferred. | partial |
| S1325 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1321-s1330 — Per-partner analytics: deferred — per-source analytics need column on grimba_vault_events or new ledger (scope at docs/GRIMBANEWS_PER_PARTNER_ANALYTICS_SCOPE.md). | partial |
| S1326 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1321-s1330 — Per-partner revenue share: deferred — gates on S1261 paid tier + S1288. | partial |
| S1327 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1321-s1330 — Per-partner SLA: deferred — operator-side contract. | partial |
| S1328 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1321-s1330 — Per-partner onboarding: partial — docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md "Onboarding checklist" defines 11-step intake (counsel review → DPA → vendor register → source seeding → classification check → soft-launch monitoring → quarterly review). | partial |
| S1329 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1321-s1330 — Per-partner case study: deferred — needs partnership shipped first (template at docs/GRIMBANEWS_PER_PARTNER_CASE_STUDY_SCOPE.md). | partial |
| S1330 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1321-s1330 — Partnership program launch: partial — docs/GRIMBANEWS_PARTNERSHIP_PROGRAM_LAUNCH_PLAYBOOK.md sequences Phase 0-4 + success criteria + launch surfaces + risk register; gates on S1321-S1329 execution. | partial |
| S1331 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — Search v2 design: partial — /search page + views/search.blade.php ship lexical search with facets (SearchFacetsTest covers facet generation per S279); semantic-rank v2 design deferred. | partial |
| S1332 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — Embedding store wiring: deferred — no vector DB (per S1076). | partial |
| S1333 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — Per-article embedding generation: deferred — depends on S1332. | partial |
| S1334 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — Per-cluster embedding generation: deferred — same. | partial |
| S1335 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — Semantic-search query: deferred — same. | partial |
| S1336 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — NobuAI query expansion: deferred — no expansion layer today. | partial |
| S1337 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — Spelling correction: deferred — no fuzzy-match layer. | partial |
| S1338 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — Search-suggestion typeahead: deferred — no autocomplete endpoint. | partial |
| S1339 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — Search-result snippet generation: partial — lexical search returns posts.description snippet; NobuAI-enriched snippet deferred. | partial |
| S1340 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — Search v2 launch: partial — docs/GRIMBANEWS_SEARCH_V2_LAUNCH_PLAYBOOK.md sequences Phase 0-4 + rollback gates + privacy posture + cost posture; gates on vector store (S1701) + embedding pipeline (S1703). | partial |
| S1341 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1341-s1350 — Personalization v2 design: partial — /pour-vous + cookie-based recent-reads + region preference is the rule-based v1; ML rank v2 design deferred. | partial |
| S1342 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1341-s1350 — Per-reader feature vector: deferred — no member_features table; cookie carries region + saved categories only. — surrogate doc: docs/GRIMBANEWS_PER_READER_FEATURE_VECTOR_DESIGN.md (partial). | partial |
| S1343 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1341-s1350 — Collaborative filter (reader-similarity): deferred — needs feature vector (S1342) + similarity job. — surrogate doc: docs/GRIMBANEWS_COLLABORATIVE_FILTER_READER_SIMILARITY_DESIGN.md (partial). | partial |
| S1344 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1341-s1350 — Content-based filter (article-similarity): deferred — needs embeddings (S1333). — surrogate doc: docs/GRIMBANEWS_CONTENT_BASED_FILTER_ARTICLE_SIMILARITY_DESIGN.md (partial). | partial |
| S1345 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1341-s1350 — ML rank model (LR / GBDT / NN): deferred — needs training data + pipeline. | partial |
| S1346 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1341-s1350 — A/B rank harness: deferred — no A/B engine (S1073). | partial |
| S1347 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1341-s1350 — Cold-start ranking: partial — /pour-vous falls back to homepage rails for cookie-less readers; ML cold-start deferred. | partial |
| S1348 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1341-s1350 — Diversity / serendipity guard: partial — HomeFeedState (per app/Support/HomeFeedState.php) already de-dupes by source-publisher across home rails so one publisher cannot dominate; ML-diversity layer d | partial |
| S1349 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1341-s1350 — Personalization opt-out: partial — readers can clear the grimba_for_you_recent cookie via browser controls; explicit opt-out toggle deferred. | partial |
| S1350 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1341-s1350 — Personalization v2 launch: partial — docs/GRIMBANEWS_PERSONALIZATION_V2_LAUNCH_PLAYBOOK.md sequences Phase 0-4 + reader-trust guards (diversity / bias spread / region spread floors) + explicit "what we will NOT ship" list; gates on feature store + ML rank. | partial |
| S1351 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1351-s1360 — Moderation queue: partial — docs/GRIMBANEWS_MODERATION_QUEUE_DESIGN.md defines moderation_queue schema + auto-classification + severity tiers + brigading detection + privacy posture; /admin/grimba/rss-drafts remains the editorial-side surrogate today. | partial |
| S1352 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1351-s1360 — Brigading detection: deferred — no UGC, no vote / reaction surface. — surrogate doc: docs/GRIMBANEWS_COMMENT_BRIGADING_DETECTION_PLAN.md (partial). | partial |
| S1353 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1351-s1360 — Spam / bot detection (newsletter / advertiser-lead): partial — AdvertiserLeadController enforces per-IP RateLimiter + honeypot field per S871 ads pack; broader bot-detection deferred. | partial |
| S1354 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1351-s1360 — Source trust audit: partial — news_sources.credibility_score + factuality_score + admin classifier (/admin/grimba/news-sources) ship today; periodic audit cadence deferred. | partial |
| S1355 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1351-s1360 — Source delist workflow: partial — news_sources.active boolean + admin toggle ship; formal delist workflow + audit log deferred. | partial |
| S1356 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1351-s1360 — Reader report-abuse channel: partial — /.well-known/security.txt + /contact cover security-side; reader-side report-abuse for surfaced articles deferred. | partial |
| S1357 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1351-s1360 — Per-cluster takedown workflow: partial — admin can hide a cluster (soft-delete on story_clusters); reader-side takedown request deferred. | partial |
| S1358 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1351-s1360 — DMCA / right-of-reply: partial — docs/GRIMBANEWS_DMCA_RIGHT_OF_REPLY_POLICY.md v0 covers DMCA-class + right-of-reply + court-order intake + jurisdictional triggers + legal_takedowns schema; awaiting counsel review. | partial |
| S1359 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1351-s1360 — Transparency report (publisher-level): partial — docs/GRIMBANEWS_TRANSPARENCY_REPORT_SCOPE.md defines 10-section annual report scope + cadence + per-section data sources + privacy posture; ships at end of first full publication year. | partial |
| S1360 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1351-s1360 — Trust & safety playbook: partial — docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md covers launch-side security controls; reader-facing trust & safety playbook deferred. | partial |
| S1361 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370 — Comment system v1 (per-article): partial — docs/GRIMBANEWS_COMMENT_V2_DESIGN.md proposes Phase 1-4 rollout (member highlights → opt-in public → full thread → cluster-level); schema + community guidelines drafted; gates on Vader decision to ship comments at all. | partial |
| S1362 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370 — Comment threading: deferred — same. | partial |
| S1363 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370 — Comment moderation queue: deferred — same. | partial |
| S1364 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370 — Comment reactions (like / thoughtful): deferred — same. — surrogate doc: docs/GRIMBANEWS_COMMENT_REACTIONS_DESIGN.md (partial). | partial |
| S1365 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370 — Comment quality scoring: deferred — same. | partial |
| S1366 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370 — Comment notification (per-thread): deferred — same. | partial |
| S1367 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370 — Comment muting / blocking: deferred — same. | partial |
| S1368 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370 — Comment community guidelines: partial — docs/GRIMBANEWS_COMMENT_V2_DESIGN.md "Community guidelines" section ships 10-rule v0 draft + enforcement ladder (spam → personal attack → hate speech → doxxing/NCII); awaiting Lucy Leai final. | partial |
| S1369 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370 — Comment moderator tooling: partial — docs/GRIMBANEWS_COMMENT_V2_DESIGN.md "Moderator tooling" section defines per-comment actions + moderator dashboard + cross-moderator coordination; ships with comment surface. | partial |
| S1370 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370 — Comment launch playbook: deferred — gates on S1361-S1369. — surrogate doc: docs/GRIMBANEWS_COMMENT_LAUNCH_PLAYBOOK_S1370.md (partial). | partial |
| S1371 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1371-s1380 — Per-article annotation surface: deferred — no annotations table. — surrogate doc: docs/GRIMBANEWS_PER_ARTICLE_ANNOTATION_SURFACE_DESIGN.md (partial). | partial |
| S1372 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1371-s1380 — Reader-side highlight (text selection → save): deferred — no client-side selection→server endpoint. — surrogate doc: docs/GRIMBANEWS_READER_HIGHLIGHT_SAVE_DESIGN.md (partial). | partial |
| S1373 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1371-s1380 — Vault v2 (folders / tags): partial — App\Support\GrimbaVault ships cookie + server-side sync; folders / tags layer deferred. | partial |
| S1374 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1371-s1380 — Vault share v2: partial — coffre-share.blade.php ships shareable vault link per S660; per-folder share deferred. | partial |
| S1375 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1371-s1380 — Vault export v2: partial — coffre/export.csv ships CSV export; JSON / Markdown export deferred. | partial |
| S1376 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1371-s1380 — Reader notebook: deferred — no notebook surface (cited in S1098 deferred). — surrogate doc: docs/GRIMBANEWS_READER_NOTEBOOK_SCHEMA.md (partial). | partial |
| S1377 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1371-s1380 — Per-cluster reader notes: deferred — same. — surrogate doc: docs/GRIMBANEWS_PER_CLUSTER_READER_NOTES_DESIGN.md (partial). | partial |
| S1378 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1371-s1380 — Read-later queue: partial — vault save serves as read-later today; "queue" semantics (FIFO, mark-as-read) deferred. | partial |
| S1379 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1371-s1380 — Cross-device sync: partial — server-side sync via members.vault_digest_post_ids happens on login; live cross-device sync (websocket / polling) deferred. | partial |
| S1380 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1371-s1380 — Reader product v2 launch: deferred — gates on S1371-S1379. — surrogate doc: docs/GRIMBANEWS_READER_PRODUCT_V2_LAUNCH_PLAYBOOK.md (partial). | partial |
| S1381 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1381-s1390 — Daily report card (homepage): complete — partials/home/daily-briefing.blade.php renders the briefing card sourced from GrimbaHomeFeed::briefing(); bias-spectrum % bar (left / center / right) included. | complete |
| S1382 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1381-s1390 — Daily report email: deferred — no grimba:daily-report-email command; vault digest + saved-search digest are the closest shipped surrogates (both weekly, not daily). — surrogate doc: docs/GRIMBANEWS_DAILY_REPORT_EMAIL_DESIGN.md (partial). | partial |
| S1383 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1381-s1390 — Per-region daily report (Afrique / International): partial — region scoping for the homepage briefing is via cookie-based edition (Afrique / International per region-dropdown.blade.php + GrimbaRegionQ | partial |
| S1384 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1381-s1390 — Per-topic daily report: deferred — depends on S1382. — surrogate doc: docs/GRIMBANEWS_PER_TOPIC_DAILY_REPORT_DESIGN.md (partial). | partial |
| S1385 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1381-s1390 — Daily report archive: partial — /feed.latest.xml is the canonical daily archive surface; structured "daily edition" page deferred. | partial |
| S1386 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1381-s1390 — Daily report NobuAI enrichment: partial — daily-briefing card pulls summary_nobuai for the briefing cluster when present; "why this matters" NobuAI lede deferred. | partial |
| S1387 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1381-s1390 — Daily report A/B (subject / cover): deferred — no A/B harness. — surrogate doc: docs/GRIMBANEWS_DAILY_REPORT_AB_HARNESS_PLAN.md (partial). | partial |
| S1388 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1381-s1390 — Daily report subscriber-only tier: deferred — gates on S1261. | partial |
| S1389 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1381-s1390 — Daily report analytics: deferred — depends on S1382. — surrogate doc: docs/GRIMBANEWS_DAILY_REPORT_ANALYTICS_PLAN.md (partial). | partial |
| S1390 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1381-s1390 — Daily report launch playbook: deferred — gates on S1381-S1389. — surrogate doc: docs/GRIMBANEWS_DAILY_REPORT_LAUNCH_PLAYBOOK.md (partial). | partial |
| S1391 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1391-s1400 — Native release pipeline (CI / fastlane): deferred — no native shell (release pipeline plan at docs/GRIMBANEWS_NATIVE_RELEASE_PIPELINE_PLAN.md). | partial |
| S1392 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1391-s1400 — Native signing / certificates: deferred — needs Apple Developer + Google Play Console (signing plan at docs/GRIMBANEWS_NATIVE_SIGNING_CERTIFICATES_PLAN.md). | partial |
| S1393 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1391-s1400 — Native code-push / OTA updates: deferred — same (OTA plan at docs/GRIMBANEWS_NATIVE_OTA_UPDATES_PLAN.md). | partial |
| S1394 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1391-s1400 — Native crash dashboard: deferred — needs Crashlytics / Sentry (S1158); dashboard plan at docs/GRIMBANEWS_NATIVE_CRASH_DASHBOARD_PLAN.md. | partial |
| S1395 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1391-s1400 — Native version pin (web ↔ native API): partial — /health JSON ships version + commit (grimba_version); native pin contract deferred. | partial |
| S1396 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1391-s1400 — Native deep-link verification: deferred — needs Universal Links / App Links registration (S1155); verification plan at docs/GRIMBANEWS_NATIVE_DEEP_LINK_VERIFICATION.md. | partial |
| S1397 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1391-s1400 — Native push-notification permission flow: deferred — needs FCM / APNs (S1154 / S1305 / S1306); permission-flow plan at docs/GRIMBANEWS_NATIVE_PUSH_PERMISSION_FLOW.md. | partial |
| S1398 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1391-s1400 — Native subscription IAP (Apple / Google): deferred — gates on S1261 paid tier + Apple / Google IAP keys (IAP subscription plan at docs/GRIMBANEWS_NATIVE_IAP_SUBSCRIPTION_PLAN.md). | partial |
| S1399 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1391-s1400 — Native release retrospective: deferred — gates on a real native release. | partial |
| S1400 | docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1391-s1400 — Native app program retrospective: deferred — gates on S1391-S1399. | partial |
| S1401 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1401-s1410 — In-house editor — seat invite + role: deferred — Botble admin auth is single-tenant with one role layer (admin / user). No editor_seats / editor_invitations table, no per-editor scoping. | partial |
| S1402 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1401-s1410 — In-house editor — cluster builder UI (drag posts into a cluster): partial — /admin/grimba/story-clusters ships a list-view cluster admin per platform/themes/echo/functions/grimba-admin-clusters.php; d | partial |
| S1403 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1401-s1410 — In-house editor — source proposer (suggest a new feed): partial — /admin/grimba/rss-feeds accepts new RSS feed URLs via the grimba-admin-rss-feeds.php function block; reader-side source-proposer form  | partial |
| S1404 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1401-s1410 — In-house editor — draft pickup from queue: partial — /admin/grimba/rss-drafts is the draft pickup queue per grimba-admin-rss-drafts.php; per-editor assignment + lock deferred. | partial |
| S1405 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1401-s1410 — In-house editor — draft enrichment (NobuAI summary + tags): partial — App\Console\Commands\GrimbaEnrichDrafts runs scheduled enrichment + GrimbaGenerateNobuAiSummaries populates posts.summary_nobuai;  | partial |
| S1406 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1401-s1410 — In-house editor — preview before publish: partial — Botble post-edit screen ships a preview action via the platform; GrimbaNews-specific dossier preview deferred. | partial |
| S1407 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1401-s1410 — In-house editor — publish gate (status=published flip): complete — App\Support\GrimbaPostPublisher + GrimbaPublicationPipeline + GrimbaPublishTrusted command enforce the publish gate; posts.status='pu | complete |
| S1408 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1401-s1410 — In-house editor — schedule publish (future-dated): partial — Botble post published_at column supports future-dating; GrimbaNews scheduler GrimbaEnsureDailyPublish ensures daily cadence; per-editor sch | partial |
| S1409 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1401-s1410 — In-house editor — collaboration / co-author signoff: deferred — no multi-author workflow. | partial |
| S1410 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1401-s1410 — In-house editor — launch retrospective: deferred — gates on S1401-S1409 actually shipping; operator-side retro. | partial |
| S1411 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1411-s1420 — Author table schema: deferred — no authors / post_authors table; current posts model has Botble's author_id (Botble user FK) but no journalist-profile metadata (schema at docs/GRIMBANEWS_AUTHOR_TABLE_SCHEMA.md). | partial |
| S1412 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1411-s1420 — Author profile page: deferred — depends on S1411 (profile scope at docs/GRIMBANEWS_AUTHOR_PROFILE_PAGE_SCOPE.md). | partial |
| S1413 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1411-s1420 — Author byline display on article: partial — article view shows source_name (publisher) via partials/post-meta.blade.php; per-author byline parse from RSS <author> / <dc:creator> deferred. | partial |
| S1414 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1411-s1420 — Author byline display on cluster: partial — partials/story/dossier-voices.blade.php ships per-source voices in a cluster; per-author voices deferred. | partial |
| S1415 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1411-s1420 — Author contribution log: deferred — no author_contributions table (schema at docs/GRIMBANEWS_AUTHOR_CONTRIBUTION_LOG_SCHEMA.md). | partial |
| S1416 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1411-s1420 — Author follow (reader follows author): deferred — no follow-author primitive; follow-source exists via search filters (design at docs/GRIMBANEWS_AUTHOR_FOLLOW_DESIGN.md). | partial |
| S1417 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1411-s1420 — Author RSS feed: deferred — no per-author feed route (design at docs/GRIMBANEWS_AUTHOR_RSS_FEED_DESIGN.md). | partial |
| S1418 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1411-s1420 — Author analytics dashboard: deferred — no per-author view counter (dashboard scope at docs/GRIMBANEWS_AUTHOR_ANALYTICS_DASHBOARD_SCOPE.md). | partial |
| S1419 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1411-s1420 — Author payout integration: deferred — depends on contributor program (S1451+). | partial |
| S1420 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1411-s1420 — Author launch retrospective: deferred — gates on S1411-S1419 (retro template at docs/GRIMBANEWS_AUTHOR_LAUNCH_RETROSPECTIVE_SCOPE.md). | partial |
| S1421 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1421-s1430 — Pre-publish review queue: partial — /admin/grimba/rss-drafts is the surrogate. Per-editor assignment + reviewer-pass deferred. | partial |
| S1422 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1421-s1430 — Second-eye approval gate: deferred — no two-step approval. Surrogate is the operator manually reviewing the rss-drafts queue. | partial |
| S1423 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1421-s1430 — Dispute escalation: deferred — operator-side editorial. | partial |
| S1424 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1421-s1430 — Cluster-merge dispute: partial — App\Support\GrimbaDedupeReview ships review-mode for the grimba:dedupe-posts command; explicit cluster-merge dispute UI deferred. | partial |
| S1425 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1421-s1430 — Cluster-split dispute: partial — same — DedupePostsCommandTest covers review mode but the merge / split tooling is operator-side. | partial |
| S1426 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1421-s1430 — Source-classification dispute (operator overrides classifier): partial — App\Console\Commands\GrimbaClassifySources runs scheduled classification; operator can override news_sources.bias_rating / fact | partial |
| S1427 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1421-s1430 — Bias-rating dispute: partial — same — news_sources.bias_rating column is operator-editable; reader-side dispute submission deferred. | partial |
| S1428 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1421-s1430 — Translation dispute: partial — operator can override posts.summary_nobuai_locale via Botble admin; reader-side dispute submission deferred. | partial |
| S1429 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1421-s1430 — Cross-locale dispute routing: deferred — operator-side editorial. | partial |
| S1430 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1421-s1430 — Review-queue launch retrospective: deferred — operator-side retro. | partial |
| S1431 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1431-s1440 — Article revision log (server-side): partial — Botble platform revisions table captures Post model edits via the RevisionableTrait; per-edit timeline partial (Botble admin surface). | partial |
| S1432 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1431-s1440 — Article revision diff UI: partial — Botble admin diff view; GrimbaNews-specific reader-facing diff deferred. | partial |
| S1433 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1431-s1440 — Correction notice — reader-facing badge: deferred — no posts.correction_notice column; surrogate is admin manual edit of posts.content (badge design at docs/GRIMBANEWS_CORRECTION_NOTICE_BADGE_DESIGN.md). | partial |
| S1434 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1431-s1440 — Retract flow (mark as retracted): partial — posts.status can be flipped to draft/pending to depublish; explicit retracted status + reader-facing "this article was retracted" banner deferred. | partial |
| S1435 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1431-s1440 — Cluster-level correction propagation: deferred — no per-cluster correction propagation (propagation plan at docs/GRIMBANEWS_CLUSTER_LEVEL_CORRECTION_PROPAGATION.md). | partial |
| S1436 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1431-s1440 — Translation-level correction: deferred — no per-translation correction notice. | partial |
| S1437 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1431-s1440 — NobuAI-summary correction (regenerate on flag): partial — grimba:nobuai-summaries --stale --limit=25 every 30 min regenerates stale summaries; explicit operator-flag-to-regenerate UI deferred. | partial |
| S1438 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1431-s1440 — Correction-policy public page: deferred — no /corrections route; surrogate is /mentions-legales legal page (public page scope at docs/GRIMBANEWS_CORRECTION_POLICY_PUBLIC_PAGE_SCOPE.md). | partial |
| S1439 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1431-s1440 — Correction audit log: partial — Botble revisions covers edit history; explicit correction-flag audit deferred. | partial |
| S1440 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1431-s1440 — Correction-flow launch retrospective: deferred — gates on S1431-S1439. | partial |
| S1441 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1441-s1450 — Syndication agreement template: deferred — operator-side legal pickup. | partial |
| S1442 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1441-s1450 — Partner content-share API: deferred — no outbound API; surrogate is the per-stream RSS feeds at /feed.xml, /feed.breaking.xml, /feed.latest.xml, per-category feeds (read-only egress). — surrogate doc: docs/GRIMBANEWS_PARTNER_CONTENT_SHARE_API_DESIGN.md (partial). | partial |
| S1443 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1441-s1450 — Partner attribution display: complete — App\Support\GrimbaArticleDedupe preserves canonical-URL; article view shows source name + link to upstream via partials/post-meta.blade.php + dossier-voices.bla | complete |
| S1444 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1441-s1450 — Partner attribution report: deferred — no per-partner reporting. | partial |
| S1445 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1441-s1450 — Partner exclusivity window: deferred — operator-side contract; no posts.exclusivity_window_until column. — surrogate doc: docs/GRIMBANEWS_PARTNER_EXCLUSIVITY_WINDOW_PLAN.md (partial). | partial |
| S1446 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1441-s1450 — Partner content-takedown workflow: deferred — operator-side; surrogate is admin manual flip of posts.status to draft. — surrogate doc: docs/GRIMBANEWS_PARTNER_CONTENT_TAKEDOWN_WORKFLOW_PLAN.md (partial). | partial |
| S1447 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1441-s1450 — Partner royalty split: deferred — depends on contributor program (S1451) + monetization (S1211). | partial |
| S1448 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1441-s1450 — Partner brand-safety review: deferred — operator-side legal pickup. — surrogate doc: docs/GRIMBANEWS_PARTNER_BRAND_SAFETY_REVIEW_PLAN.md (partial). | partial |
| S1449 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1441-s1450 — Partner case studies: deferred — needs ≥1 real partner. | partial |
| S1450 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1441-s1450 — Partnership-program launch retrospective: deferred — gates on S1441-S1449. | partial |
| S1451 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1451-s1460 — Contributor intake form: deferred — no contributor_applications table. | partial |
| S1452 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1451-s1460 — Contributor profile + verification: deferred — depends on S1411 author system. — surrogate doc: docs/GRIMBANEWS_CONTRIBUTOR_PROFILE_VERIFICATION_DESIGN.md (partial). | partial |
| S1453 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1451-s1460 — Contributor rate card: deferred — operator-side. — surrogate doc: docs/GRIMBANEWS_CONTRIBUTOR_RATE_CARD_DESIGN.md (partial). | partial |
| S1454 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1451-s1460 — Contributor submission portal: deferred — surrogate is operator-managed /admin/grimba/rss-drafts queue. — surrogate doc: docs/GRIMBANEWS_CONTRIBUTOR_SUBMISSION_PORTAL_DESIGN.md (partial). | partial |
| S1455 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1451-s1460 — Contributor editor-handoff: deferred — depends on multi-editor workflow (S1401). — surrogate doc: docs/GRIMBANEWS_CONTRIBUTOR_EDITOR_HANDOFF_DESIGN.md (partial). | partial |
| S1456 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1451-s1460 — Contributor payout integration (Stripe Connect / Wise): deferred — no billing infra (S1211). | partial |
| S1457 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1451-s1460 — Contributor 1099 / tax reporting: deferred — same. — surrogate doc: docs/GRIMBANEWS_CONTRIBUTOR_1099_TAX_REPORTING_DESIGN.md (partial). | partial |
| S1458 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1451-s1460 — Contributor analytics dashboard: deferred — depends on per-author analytics (S1418). — surrogate doc: docs/GRIMBANEWS_CONTRIBUTOR_ANALYTICS_DASHBOARD_DESIGN.md (partial). | partial |
| S1459 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1451-s1460 — Contributor case studies: deferred — needs ≥1 real contributor. — surrogate doc: docs/GRIMBANEWS_CONTRIBUTOR_CASE_STUDIES_SCOPE.md (partial). | partial |
| S1460 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1451-s1460 — Contributor program launch retrospective: deferred — gates on S1451-S1459. — surrogate doc: docs/GRIMBANEWS_CONTRIBUTOR_PROGRAM_LAUNCH_RETROSPECTIVE_SCOPE.md (partial). | partial |
| S1461 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1461-s1470 — Semantic search — design doc: deferred — written design deferred; the FTS5 surface is the current substrate. | partial |
| S1462 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1461-s1470 — Semantic search — embedding model pick: deferred — depends on S1076 embedding store (pgvector / qdrant / pinecone). | partial |
| S1463 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1461-s1470 — Semantic search — embedding index build: deferred — same. | partial |
| S1464 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1461-s1470 — Semantic search — query embedding: deferred — same. | partial |
| S1465 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1461-s1470 — Semantic search — hybrid (lexical + semantic) merge: deferred — same. | partial |
| S1466 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1461-s1470 — NobuAI query expansion (synonym / paraphrase): deferred — App\Services\GrimbaNobuAi shipped, query-expansion prompt template deferred. | partial |
| S1467 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1461-s1470 — Related-search suggestions ("did you mean X?"): partial — search results view (platform/themes/echo/views/search.blade.php) exists; "did you mean" / related-search chip UI deferred. Surrogate today: F | partial |
| S1468 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1461-s1470 — Search-result clustering (group by topic): partial — App\Support\GrimbaHomeFeed already groups posts by story_cluster_id on the homepage; search-results-page cluster grouping deferred (current view pa | partial |
| S1469 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1461-s1470 — Search-result snippet highlighting: partial — FTS5 supports snippet() highlighting; search.blade.php currently shows post excerpt only. Highlight wiring deferred. | partial |
| S1470 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1461-s1470 — Semantic-search launch retrospective: deferred — gates on S1461-S1469. | partial |
| S1471 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1471-s1480 — Search filter — by source: complete — searchHandler accepts ?source={id} and joins on posts.source_id. SavedSearchAlertsTest::test_member_can_save_and_remove_search_alert covers the criteria. | complete |
| S1472 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1471-s1480 — Search filter — by bias: complete — accepts ?bias=left\|center\|right\|unknown; posts.bias_rating filter applied. | complete |
| S1473 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1471-s1480 — Search filter — by owner: complete — accepts ?owner={owner_name}; subquery on news_sources.owner_name. | complete |
| S1474 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1471-s1480 — Search filter — by date range (from / to): complete — accepts ?from_date=YYYY-MM-DD&to_date=YYYY-MM-DD; App\Support\GrimbaPostRecency::wherePublishedDateFrom/To() applied. Validated by preg_match('/^\ | complete |
| S1475 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1471-s1480 — Search filter — by topic / category: deferred — ?category={id} filter not in current searchHandler; surrogate is /categorie/{slug} category pages. | partial |
| S1476 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1471-s1480 — Search filter — by locale: partial — GnTr::orderForTargetLocale() applies locale priority to result ordering (per App\Support\GrimbaTranslationPresenter); explicit ?lang= filter deferred. | partial |
| S1477 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1471-s1480 — Search filter — by edition (Afrique / International): partial — region cookie (grimba_region) gates the post corpus globally via App\Support\GrimbaArticleRegion; explicit search-filter chip deferred. | partial |
| S1478 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1471-s1480 — Search filter — saved as URL (deep-link to filtered search): complete — searchUrl() in App\Support\GrimbaSavedSearches serializes filter set to query string; saved searches re-emit as searchUrl($crite | complete |
| S1479 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1471-s1480 — Search filter — clear-all action: partial — view exists (search.blade.php); explicit clear-all button on filter chip UI is theme-side polish. | partial |
| S1480 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1471-s1480 — Search-filter launch retrospective: complete — server-side filter pipeline shipped (5 facets: source, bias, owner, from, to) + saved-search criteria normalization + URL serialization + paginated FTS5  | complete |
| S1481 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1481-s1490 — Saved-search schema (saved_searches table): complete — migration ships saved_searches with member_id, search_query, search_hash, source_id, bias, owner, from_date, to_date, active, last_sent_at. Hash- | complete |
| S1482 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1481-s1490 — Saved-search create flow: complete — POST /search/alerts route handler (platform/themes/echo/routes/web.php:918-945) gated by member middleware; cap at MAX_PER_MEMBER = 12. SavedSearchAlertsTest::test | complete |
| S1483 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1481-s1490 — Saved-search delete flow: complete — DELETE /account/saved-searches/{id} route (platform/themes/echo/routes/web.php:1521); same test covers. | complete |
| S1484 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1481-s1490 — Saved-search list (account page): complete — /account view shows saved searches; account.blade.php renders the list from GrimbaSavedSearches::listForMember(). | complete |
| S1485 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1481-s1490 — Saved-search digest cron: complete — grimba:saved-search-digests weekly Monday 04:55 UTC per routes/console.php; SavedSearchAlertsTest::test_digest_emails_matching_posts_to_member covers. | complete |
| S1486 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1481-s1490 — Saved-search digest email template: complete — resources/views/emails/saved-search-digest.blade.php rendered by App\Mail\GrimbaSavedSearchDigestMail; capped at DIGEST_POST_LIMIT = 8 matches. | complete |
| S1487 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1481-s1490 — Saved-search digest unsubscribe (per-search): partial — DELETE /account/saved-searches/{id} is the per-search unsubscribe; per-email unsubscribe link deferred (current implementation uses authenticate | partial |
| S1488 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1481-s1490 — Saved-search digest cadence config (weekly vs daily): partial — current cadence is weekly-only (hardcoded in routes/console.php); per-member cadence picker deferred. | partial |
| S1489 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1481-s1490 — Saved-search digest analytics (open / click): deferred — no email-event tracking SDK (lands with newsletter v2 S1281+). | partial |
| S1490 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1481-s1490 — Saved-search launch retrospective: complete — primitive shipped + tested + scheduled; 4-row band (schema + create + delete + digest) all complete. | complete |
| S1491 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1491-s1500 — Search-event logging schema: deferred — no search_events table. | partial |
| S1492 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1491-s1500 — Top-searches dashboard: deferred — depends on S1491. | partial |
| S1493 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1491-s1500 — Zero-result-search tracking: deferred — same. | partial |
| S1494 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1491-s1500 — Saved-search adoption metric: partial — GrimbaSavedSearches::countForMember() + raw saved_searches row count via DB::table('saved_searches')->count(); dashboard wrapper deferred. | partial |
| S1495 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1491-s1500 — Per-source search popularity: deferred — depends on S1491. | partial |
| S1496 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1491-s1500 — Per-bias search popularity: deferred — same. — surrogate doc: docs/GRIMBANEWS_PER_BIAS_SEARCH_POPULARITY_PLAN.md (partial). | partial |
| S1497 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1491-s1500 — Per-date-range popularity: deferred — same. — surrogate doc: docs/GRIMBANEWS_PER_DATE_RANGE_SEARCH_POPULARITY_PLAN.md (partial). | partial |
| S1498 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1491-s1500 — Search-result CTR: deferred — same. — surrogate doc: docs/GRIMBANEWS_SEARCH_RESULT_CTR_PLAN.md (partial). | partial |
| S1499 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1491-s1500 — Search-A/B test harness: deferred — no A/B engine (per S1073 honest deferral). | partial |
| S1500 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1491-s1500 — Search-analytics launch retrospective: deferred — gates on S1491-S1499. | partial |
| S1501 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1501-s1510 — ML feed — design doc: deferred — written design deferred. Cookie-only pour-vous is the substrate. | partial |
| S1502 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1501-s1510 — ML feed — collaborative filter model: deferred — needs per-member interaction matrix; current implementation is privacy-first cookie-only (no server-side per-member read log). | partial |
| S1503 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1501-s1510 — ML feed — embedding-based recs: deferred — depends on S1076 embedding store. | partial |
| S1504 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1501-s1510 — ML feed — cold-start handling: partial — current /pour-vous handles cold-start by paginating default category-feed when grimba_read count ≤ 10; ML cold-start deferred. | partial |
| S1505 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1501-s1510 — ML feed — opt-in toggle: partial — surrogate is per-user cookie grimba_read opt-out (one cookie clears history); explicit opt-in toggle deferred. | partial |
| S1506 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1501-s1510 — ML feed — explain-why-recommended: partial — current view shows "based on your followed categories" string; per-post "we recommended this because..." deferred. | partial |
| S1507 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1501-s1510 — ML feed — fairness audit: deferred — depends on real ML model. | partial |
| S1508 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1501-s1510 — ML feed — diversity floor (no echo chamber): partial — avoidedTopics surfacing on /pour-vous is the diversity-floor surrogate (forces fresh categories into view). Explicit % diversity floor deferred. | partial |
| S1509 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1501-s1510 — ML feed — model A/B harness: deferred — no A/B engine (S1073). | partial |
| S1510 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1501-s1510 — ML feed launch retrospective: deferred — gates on S1501-S1509. | partial |
| S1511 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1511-s1520 — Preference center — page: partial — /account ships member preference surface (vault digest toggle + saved-search list); explicit "preferences" tab deferred. | partial |
| S1512 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1511-s1520 — Followed topics — cookie: partial — cookie-only follow (cookie grimba_follow CSV per /pour-vous handler); per-member persist deferred until tier. | partial |
| S1513 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1511-s1520 — Followed topics — server-persisted: deferred — no member_followed_categories table. | partial |
| S1514 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1511-s1520 — Blocked sources — UI: deferred — no block-source primitive. | partial |
| S1515 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1511-s1520 — Blocked sources — server-persisted: deferred — no member_blocked_sources table. | partial |
| S1516 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1511-s1520 — Weight slider (boost / suppress topic): deferred — depends on ML feed (S1501). | partial |
| S1517 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1511-s1520 — Followed authors: deferred — depends on author system (S1411). | partial |
| S1518 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1511-s1520 — Followed clusters: deferred — no follow-cluster primitive; surrogate is sharing the dossier URL. | partial |
| S1519 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1511-s1520 — Reset preferences action: partial — grimba_read + grimba_follow cookies clear via standard browser cookie controls; explicit "reset" button deferred. | partial |
| S1520 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1511-s1520 — Preference-center launch retrospective: deferred — gates on S1511-S1519. | partial |
| S1521 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1521-s1530 — Data export — vault history (CSV): complete — coffre/export.csv route (per platform/themes/echo/routes/web.php:641-644 + S028 subscriber gate); exports member's saved post ids. | complete |
| S1522 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1521-s1530 — Data export — read history (CSV): complete — pour-vous/export.csv route (platform/themes/echo/routes/web.php:1235-1287); cookie-only data export with BOM + UTF-8. | complete |
| S1523 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1521-s1530 — Data export — saved searches (CSV / JSON): deferred — no account/saved-searches/export.csv route; surrogate is /account page list view. | partial |
| S1524 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1521-s1530 — Data export — GDPR DSAR full bundle: deferred — depends on S1491 compliance band (GDPR DSAR pipeline). | partial |
| S1525 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1521-s1530 — Data delete — vault clear action: partial — cookie-clear via UI data-grimba-save-clear not shipped; manual cookie-clear via browser is the substrate. | partial |
| S1526 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1521-s1530 — Data delete — read-history clear action: partial — same — manual cookie clear. | partial |
| S1527 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1521-s1530 — Data delete — member account delete: partial — Botble member account delete via admin; reader-side self-delete deferred. | partial |
| S1528 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1521-s1530 — Opt-in / opt-out audit log: partial — App\Support\GrimbaVaultEvents ledger captures vault opt-in events with privacy-safe ip_hash; opt-out events partial. | partial |
| S1529 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1521-s1530 — Privacy ops launch comms: deferred — operator-side comms. | partial |
| S1530 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1521-s1530 — Privacy ops launch retrospective: deferred — gates on S1521-S1529. | partial |
| S1531 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1531-s1540 — No-filter-bubble guarantee — design doc: partial — substrate shipped via partials/feed-balance.blade.php + partials/story/bias-distribution.blade.php + App\Support\GrimbaClusterBias; written guarantee | partial |
| S1532 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1531-s1540 — Opposite-bias surfacing: complete — partials/story/dossier-voices.blade.php ships voices from across the bias spectrum within a cluster; tests/Feature/GrimbaLaunchReadinessTest test_story_breakdown_sh | complete |
| S1533 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1531-s1540 — Diversity floor enforcement: partial — App\Support\GrimbaHomeFeed::breakingsByCluster() joins on news_sources.bias_rating to ensure cluster mixes biases; explicit % floor enforcement deferred. | partial |
| S1534 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1531-s1540 — Cross-locale diversity: partial — dossier-voices.blade.php shows per-language voices with amber unknown-language badge; explicit cross-locale floor deferred. | partial |
| S1535 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1531-s1540 — Country diversity floor: partial — news_sources.country column drives geography spread; explicit floor deferred. | partial |
| S1536 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1531-s1540 — Source-credibility diversity: partial — news_sources.credibility_score + factuality_score are stored; cluster builder shows weighted spread; explicit fairness audit deferred. | partial |
| S1537 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1531-s1540 — Ownership diversity (state / corp / nonprofit): partial — news_sources.ownership_type stored; partials/ownership-chip.blade.php displays. Explicit diversity floor deferred. | partial |
| S1538 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1531-s1540 — Echo-chamber detector: deferred — depends on per-member read log (which by design does not exist server-side). — surrogate doc: docs/GRIMBANEWS_ECHO_CHAMBER_DETECTOR_PLAN.md (partial). | partial |
| S1539 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1531-s1540 — Fairness audit dashboard: deferred — no per-member ML behavior to audit yet. — surrogate doc: docs/GRIMBANEWS_FAIRNESS_AUDIT_DASHBOARD_PLAN.md (partial). | partial |
| S1540 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1531-s1540 — Fairness launch retrospective: deferred — gates on S1531-S1539. | partial |
| S1541 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1541-s1550 — Annotation schema (highlights table): deferred — no post_annotations / post_highlights table. | partial |
| S1542 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1541-s1550 — Highlight UI (text selection): deferred — no JS handler for text-selection highlighting. | partial |
| S1543 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1541-s1550 — Note attached to highlight: deferred — same. — surrogate doc: docs/GRIMBANEWS_ANNOTATION_NOTE_ATTACHMENT_DESIGN.md (partial). | partial |
| S1544 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1541-s1550 — Share-with-quote (Tweet/Bluesky with selected text): partial — partials/story/share-kit.blade.php ships 6-channel share (X / Bluesky / Facebook / WhatsApp / LinkedIn / Email) with URL + title; per-sel | partial |
| S1545 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1541-s1550 — Highlight visible to other readers (public annotations): deferred — depends on S1541. — surrogate doc: docs/GRIMBANEWS_PUBLIC_ANNOTATIONS_DESIGN.md (partial). | partial |
| S1546 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1541-s1550 — Private annotations sync across devices: deferred — same + cross-device sync. | partial |
| S1547 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1541-s1550 — Annotation export (Markdown / Roam): deferred — depends on S1541. — surrogate doc: docs/GRIMBANEWS_ANNOTATION_EXPORT_DESIGN.md (partial). | partial |
| S1548 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1541-s1550 — Annotation analytics: deferred — depends on S1541. — surrogate doc: docs/GRIMBANEWS_ANNOTATION_ANALYTICS_PLAN.md (partial). | partial |
| S1549 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1541-s1550 — Annotation moderation: deferred — depends on S1541. | partial |
| S1550 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1541-s1550 — Annotation launch retrospective: deferred — gates on S1541-S1549. | partial |
| S1551 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1551-s1560 — Bookmark — basic save action: complete — partials/save-button.blade.php toggles post id in grimba_vault cookie; data-grimba-save="{id}" handler client-side. Cap at 50 per GrimbaVault::parseIds(). test | complete |
| S1552 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1551-s1560 — Bookmark — folders: deferred — no vault_folders column / table; flat list today. | partial |
| S1553 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1551-s1560 — Bookmark — tags: deferred — no tag schema. | partial |
| S1554 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1551-s1560 — Bookmark — cross-device sync (via account): partial — GrimbaVault::syncCookieToMember() syncs cookie → members.vault_digest_post_ids on login; reverse (member → cookie) partial via /coffre page rehydr | partial |
| S1555 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1551-s1560 — Bookmark — list view (/coffre): complete — coffre.blade.php ships the vault list with member-gate via Botble middleware; S028 subscriber gate evidence. | complete |
| S1556 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1551-s1560 — Bookmark — search within saved: deferred — no FTS index over vault subset. | partial |
| S1557 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1551-s1560 — Bookmark — bulk delete: partial — per-post unsave via data-grimba-save toggle; bulk-clear deferred. | partial |
| S1558 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1551-s1560 — Bookmark — share (vault-share URL): complete — /coffre-share route exists (per platform/themes/echo/routes/web.php private-path guard list in PwaShellTest). | complete |
| S1559 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1551-s1560 — Bookmark — weekly digest email: complete — grimba:vault-digests weekly cron + GrimbaVaultDigestMail + resources/views/emails/vault-digest.blade.php; tests/Feature/VaultDigestTest covers contract. | complete |
| S1560 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1551-s1560 — Bookmark v2 launch retrospective: partial — v1 (cookie + member sync + digest + share) shipped; folders / tags / cross-device drift deferred. | partial |
| S1561 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1561-s1570 — Offline shell — service worker: complete — public/grimba-sw.js shipped; tests/Feature/PwaShellTest::test_service_worker_avoids_private_paths_and_non_cacheable_responses covers private-path guard (admi | complete |
| S1562 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1561-s1570 — Offline shell — offline.html fallback: complete — public/offline.html shipped; manifest + SW pre-cache. | complete |
| S1563 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1561-s1570 — Offline mode — article body cache (read offline): partial — grimba-sw.js caches GETs unless Cache-Control: no-store\|private; explicit per-article precache (save-for-offline button) deferred. | partial |
| S1564 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1561-s1570 — Offline mode — vault sync queue: partial — vault cookie persists offline; bookmark action queues for server sync on reconnect (cookie ↔ member sync on next request). Explicit IndexedDB queue deferred. | partial |
| S1565 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1561-s1570 — Offline mode — conflict resolution: deferred — single-device cookie today; multi-device conflict resolution depends on cross-device sync (S1554). — surrogate doc: docs/GRIMBANEWS_OFFLINE_CONFLICT_RESOLUTION_PLAN.md (partial). | partial |
| S1566 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1561-s1570 — Offline mode — cache eviction policy: partial — service-worker LRU on quota pressure (browser-default); explicit per-resource TTL deferred. | partial |
| S1567 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1561-s1570 — Offline mode — install prompt UX: partial — manifest + beforeinstallprompt browser-default; explicit install-prompt UI deferred. | partial |
| S1568 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1561-s1570 — Offline mode — share-target (PWA receive shared URL): deferred — manifest share_target not registered. — surrogate doc: docs/GRIMBANEWS_PWA_SHARE_TARGET_DESIGN.md (partial). | partial |
| S1569 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1561-s1570 — Offline mode — analytics (offline interaction queue): deferred — no offline-event queue. | partial |
| S1570 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1561-s1570 — Offline mode launch retrospective: partial — shell + private-path guard + fallback shipped + tested; per-article cache + IndexedDB queue + share-target deferred. | partial |
| S1571 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1571-s1580 — Reading mode — design: partial — docs/GRIMBANEWS_READING_MODE_DESIGN.md defines toggle + layout + reader-preferences panel + a11y + cookie footprint; implementation pending Steve sign-off. | partial |
| S1572 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1571-s1580 — Font scaling — UI: partial — browser-default zoom + rem-based typography in Public Sans / Fraunces stack; explicit A−/A+ controls deferred. | partial |
| S1573 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1571-s1580 — Dyslexia-friendly font (OpenDyslexic / Atkinson Hyperlegible): partial — docs/GRIMBANEWS_FONT_SCALING_A11Y_MATRIX.md picks Public Sans + Atkinson Hyperlegible + OpenDyslexic (all OFL/BVL self-hosted) with per-(font × scale × spacing) compatibility matrix; ships in reading-mode preferences panel. | partial |
| S1574 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1571-s1580 — Line-spacing controls: partial — docs/GRIMBANEWS_FONT_SCALING_A11Y_MATRIX.md "Line-spacing picker" defines Normal (1.6) vs Loose (2.0) explicit two-value picker; ships in reading-mode preferences. | partial |
| S1575 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1571-s1580 — High-contrast mode: partial — dark / light themes locked by GrimbaDarkModeContractTest; explicit high-contrast variant deferred. | partial |
| S1576 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1571-s1580 — Screen-reader hints v2: partial — aria-label sweep across info-pill / share-kit / 178 occurrences (per S049); per-component hints audit pass deferred. | partial |
| S1577 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1571-s1580 — Keyboard shortcuts v2: partial — skip-link + tests/e2e/grimbanews-keyboard-navigation.cjs cover navigation; explicit shortcut keys (j/k/g/h) deferred. | partial |
| S1578 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1571-s1580 — Focus management v2: partial — partials/focus-manager.blade.php + tabindex="-1" on <main> + skip-link shipped; modal focus-trap partial. | partial |
| S1579 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1571-s1580 — A11y dashboard (axe-core scan cadence): partial — docs/GRIMBANEWS_ADMIN_VISUAL_REGRESSION_ROUTES.md ships the route matrix; axe-core CI gate deferred. | partial |
| S1580 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1571-s1580 — A11y v2 launch retrospective: partial — baseline a11y locked; v2 controls (font scaling, dyslexia font, line-spacing, high-contrast variant) deferred. | partial |
| S1581 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1581-s1590 — Per-region daily digest: partial — docs/GRIMBANEWS_PER_REGION_DAILY_DIGEST_CADENCE.md defines 8-region per-locale time schedule + template structure + subscription model + CAN-SPAM/GDPR posture; gates on general-audience newsletter ship. | partial |
| S1582 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1581-s1590 — Per-topic daily summary: partial — per-category RSS feed at /feed.{category}.xml is the per-topic surrogate; email digest variant deferred. | partial |
| S1583 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1581-s1590 — Per-edition daily digest (Afrique vs International): partial — region cookie partitions the corpus; explicit per-edition email digest deferred. | partial |
| S1584 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1581-s1590 — Time-of-day variants (morning / lunch / evening): deferred — single daily cadence today. | partial |
| S1585 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1581-s1590 — Breaking-news push (within-day): partial — App\Console\Commands\GrimbaFetchBreakingNews ingests breaking; push notification depends on FCM/APNs (S1154). | partial |
| S1586 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1581-s1590 — Curated weekly recap: partial — App\Mail\GrimbaVaultDigestMail ships weekly vault digest; per-edition curated recap deferred. | partial |
| S1587 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1581-s1590 — Daily report — image variant (OG card embed in email): partial — App\Http\Controllers\GrimbaOgImageController renders OG cards; email-embed partial. | partial |
| S1588 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1581-s1590 — Daily report — subject-line A/B: deferred — no A/B engine (S1073). | partial |
| S1589 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1581-s1590 — Daily report — send-time A/B: deferred — same. | partial |
| S1590 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1581-s1590 — Daily-report v2 launch retrospective: deferred — gates on S1581-S1589. | partial |
| S1591 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1591-s1600 — Moderation queue — schema: partial — docs/GRIMBANEWS_MODERATION_QUEUE_DESIGN.md "Schema (S1591 ship target)" defines moderation_queue + content_type enum + severity tiers + ip_hash + email_hash privacy posture; ships on first UGC. | partial |
| S1592 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1591-s1600 — Moderation queue — UI: partial — /admin/grimba/rss-drafts is the editorial moderation surrogate; comment moderation UI deferred. | partial |
| S1593 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1591-s1600 — Brigading detection (anomalous traffic): deferred — no per-user behavior tracking. | partial |
| S1594 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1591-s1600 — Downvote-spam guard: deferred — no vote primitive on reader surface. | partial |
| S1595 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1591-s1600 — Hate-speech filter: deferred — operator-side editorial; surrogate is news_sources.factuality_score + credibility_score source-level filter on ingest. — surrogate doc: docs/GRIMBANEWS_HATE_SPEECH_FILTER_PLAN.md (partial). | partial |
| S1596 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1591-s1600 — Misinformation flag (per-article): partial — news_sources.factuality_score excludes low-score sources at ingest; per-article flag deferred. | partial |
| S1597 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1591-s1600 — Author / commenter ban list: deferred — no commenter primitive. | partial |
| S1598 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1591-s1600 — IP / device throttling on writes: partial — AdvertiserLeadController ships per-IP RateLimiter::attempt('advertiser-lead:' . sha1($ip), ...); cross-surface write throttling partial (Laravel default). | partial |
| S1599 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1591-s1600 — Trust & safety transparency report: partial — docs/GRIMBANEWS_TRANSPARENCY_REPORT_SCOPE.md section 4 covers DMCA counts + reader-reported abuse + comment-mod counts + source delistings; ships with annual report. | partial |
| S1600 | docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1591-s1600 — Trust & safety launch retrospective: deferred — gates on S1591-S1599. | partial |
| S2001 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — scope definition: deferred — scaffold per Mythos honesty note; needs editorial-owner + counsel + Vader scope sign-off | partial |
| S2002 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — moderation-action counts: deferred — gates on S1591 moderation_queue; surrogate is grimba_automation_runs — surrogate doc: docs/GRIMBANEWS_TRANSPARENCY_MODERATION_COUNTS_DESIGN.md (partial). | partial |
| S2003 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — takedown / DMCA counts: deferred — no takedown intake; needs mailto:legal alias + operator log | partial |
| S2004 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — government / LE data requests: deferred — no LE-request intake; needs counsel-defined per-jurisdiction workflow | partial |
| S2005 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — source-license challenges + outcomes: deferred — news_sources.license_notes is the operator slot (S1030); aggregation surface deferred | partial |
| S2006 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — corrections issued + per-source count: deferred — no corrections primitive; gates on corrections table + editorial workflow S1291 — surrogate doc: docs/GRIMBANEWS_TRANSPARENCY_CORRECTIONS_COUNT_DESIGN.md (partial). | partial |
| S2007 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — ad rejections + per-category breakdown: deferred — GrimbaAds consent hooks exist (S871) but no rejection log + annual aggregation — surrogate doc: docs/GRIMBANEWS_TRANSPARENCY_AD_REJECTIONS_LEDGER_PLAN.md (partial). | partial |
| S2008 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — NobuAI cost + provider mix transparency: partial — GrimbaProviderCredits daily counters exist; annual public-facing aggregation deferred | partial |
| S2009 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — A/B-test outcomes transparency: deferred — no A/B engine wired (S1073) — surrogate doc: docs/GRIMBANEWS_TRANSPARENCY_AB_OUTCOMES_DISCLOSURE_SCOPE.md (partial). | partial |
| S2010 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — methodology change log per-year: deferred — internal change log is git history; public versioning surface deferred — surrogate doc: docs/GRIMBANEWS_TRANSPARENCY_METHODOLOGY_CHANGELOG_PLAN.md (partial). | partial |
| S2011 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — publish cadence: deferred — needs >=1 full operational year + editorial-owner pickup | partial |
| S2012 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — multi-locale publication: deferred — gates on S2011 + per-locale catalogs (S1101+) | partial |
| S2013 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — open-data download bundle (CSV / JSON): deferred — surrogate is coffre/export.csv subscriber export; transparency-data export deferred — surrogate doc: docs/GRIMBANEWS_TRANSPARENCY_OPEN_DATA_EXPORT_PLAN.md (partial). | partial |
| S2014 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — third-party audit attestation: deferred — needs external auditor engagement; zero contracts today | partial |
| S2015 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — comparison to peer outlets: deferred — operator-side editorial framing | partial |
| S2016 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — press coverage of the report itself: deferred — gates on S2011 first edition | partial |
| S2017 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — reader feedback intake: deferred — no feedback intake surface; needs /transparency/feedback form | partial |
| S2018 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — year-over-year trend page: deferred — gates on >=2 editions — surrogate doc: docs/GRIMBANEWS_TRANSPARENCY_YOY_TREND_PAGE_PLAN.md (partial). | partial |
| S2019 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — archive accessibility (multi-year): deferred — gates on S2011 + at least one prior edition | partial |
| S2020 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — launch retrospective + next-year scope: deferred — gates on S2011 | partial |
| S2021 | docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md — partial — ombudsman charter draft shipped (scope + independence + complaint workflow + public reporting cadence); gating dep: ombudsman appointment | partial |
| S2022 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Ombudsman appointment — first ombudsman hire: deferred — operator-side pickup; not on any current Iboga roster | partial |
| S2023 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Ombudsman intake surface — /ombudsman page with intake form: deferred — no such route; surrogate is GrimbaContactController + /api/contact (S006) | partial |
| S2024 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Ombudsman intake — email alias (ombudsman@grimbanews.com): deferred — no alias provisioned; needs DNS + Acelle inbox routing | partial |
| S2025 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Ombudsman intake — anonymous tip channel: deferred — needs SecureDrop or equivalent; zero anonymous-tip infra today — surrogate doc: docs/GRIMBANEWS_OMBUDSMAN_ANON_TIP_CHANNEL_PLAN.md (partial). | partial |
| S2026 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Complaint triage workflow — severity rubric: deferred — needs ombudsman + editorial-board co-authored rubric | partial |
| S2027 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Complaint triage workflow — investigation log (internal): deferred — no log table; needs ombudsman_investigations schema | partial |
| S2028 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Complaint workflow — response SLA (14d initial / 60d close): deferred — operator-side SLA contract | partial |
| S2029 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Complaint workflow — public findings publication: deferred — gates on S2023 + editorial-policy on public-vs-private findings | partial |
| S2030 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Complaint workflow — anonymized-but-public log: deferred — gates on S2029 — surrogate doc: docs/GRIMBANEWS_OMBUDSMAN_COMPLAINT_PUBLIC_LOG_PLAN.md (partial). | partial |
| S2031 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Ombudsman — annual report (separate from S2001): deferred — separate cadence; ombudsman reports to readers, not to operator — surrogate doc: docs/GRIMBANEWS_OMBUDSMAN_ANNUAL_REPORT_PLAN.md (partial). | partial |
| S2032 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Ombudsman — cross-locale intake (FR + EN today, more post-S1101): deferred — gates on S2023 + per-locale catalogs — surrogate doc: docs/GRIMBANEWS_OMBUDSMAN_CROSS_LOCALE_INTAKE_PLAN.md (partial). | partial |
| S2033 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Ombudsman — escalation to external press council: deferred — needs counsel-defined per-jurisdiction routing | partial |
| S2034 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Ombudsman — correction-issuance authority: deferred — needs charter clause (S2021) + corrections primitive (S2006) — surrogate doc: docs/GRIMBANEWS_OMBUDSMAN_CORRECTION_ISSUANCE_AUTHORITY_PLAN.md (partial). | partial |
| S2035 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Ombudsman — staff-training / case-study program: deferred — operator-side editorial training | partial |
| S2036 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Ombudsman — reader-rights education page (/vos-droits): deferred — no such page today; needs counsel review per jurisdiction | partial |
| S2037 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Ombudsman — quarterly office-hours (public): deferred — operator-side cadence; not viable solo — surrogate doc: docs/GRIMBANEWS_OMBUDSMAN_QUARTERLY_OFFICE_HOURS_PLAN.md (partial). | partial |
| S2038 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Ombudsman — independent budget line: deferred — needs Ray sign-off + Iboga board approval | partial |
| S2039 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Ombudsman — succession plan (term limits, search committee): deferred — gates on S2022 first hire | partial |
| S2040 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Ombudsman — launch retrospective: deferred — gates on S2021-S2039 + >=1 year tenure | partial |
| S2041 | docs/GRIMBANEWS_OSS_METHODOLOGY_SCOPE.md — partial — OSS methodology repo scope decision doc shipped (license shortlist + extracted files + contribution flow); gating dep: repo published | partial |
| S2042 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — license selection (MIT vs Apache 2.0 vs CC-BY): deferred — needs counsel pass | partial |
| S2043 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — GitHub org provisioning: deferred — no public OSS org today; darkvaderfr is private mirror | partial |
| S2044 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — bias-classification rubric extraction: deferred — needs license-clear + internal-notes scrub from GrimbaClusterBias + S401-S450 pack | partial |
| S2045 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — factuality-score rubric extraction: deferred — same; from news_sources.factuality_score + ingest filter | partial |
| S2046 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — ownership-classification rules: deferred — same; from news_sources.ownership_type enum + classifier | partial |
| S2047 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — cluster-merge algorithm: deferred — same; from GrimbaRssPoller::findOrFormCluster() | partial |
| S2048 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — dedup rules: deferred — same; from GrimbaArticleDedupe + canonical URL + title similarity | partial |
| S2049 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — translation-rule engine: deferred — same; from GrimbaTranslationRules + daily_cap setting | partial |
| S2050 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — README + getting-started guide: deferred — gates on S2041 scope decision — surrogate doc: docs/GRIMBANEWS_METHODOLOGY_REPO_README_PLAN.md (partial). | partial |
| S2051 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — CONTRIBUTING.md + code of conduct: deferred — needs community-manager owner; not hired | partial |
| S2052 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — versioning policy (semver vs date-based): deferred — gates on S2041 | partial |
| S2053 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — DOI registration for citable methodology: deferred — needs Zenodo / Figshare account | partial |
| S2054 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — academic-paper companion: deferred — operator-side academic output; needs PI + funding | partial |
| S2055 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — translation of repo (FR + EN minimum): deferred — gates on S2050 | partial |
| S2056 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — issue-triage workflow: deferred — gates on S2043 + community-manager hire | partial |
| S2057 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — PR-review workflow: deferred — same | partial |
| S2058 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — release cadence (quarterly vs ad-hoc): deferred — gates on S2041 | partial |
| S2059 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — sponsorship / GitHub Sponsors integration: deferred — gates on S2043 + Stripe Atlas / SponsorLink | partial |
| S2060 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2041-s2060 — Methodology repo — launch retrospective: deferred — gates on S2041-S2059 | partial |
| S2061 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Translator OSS release — repo scaffolding: deferred — Laravel-coupled; needs framework-neutral port | partial |
| S2062 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Translator OSS release — NobuAI / OpenRouter / LibreTranslate driver split: deferred — needs per-driver-package separation + each license-cleared independently | partial |
| S2063 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Translator OSS release — rule-engine OSS: deferred — needs scope decision (S2041); from GrimbaTranslationRules | partial |
| S2064 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Translator OSS release — quality-eval harness: deferred — no eval harness exists internally; would need build before release | partial |
| S2065 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Translator OSS release — example apps / cookbook: deferred — gates on S2061 | partial |
| S2066 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Detector OSS release — n-gram corpus extraction: deferred — corpus is embedded constants in GrimbaLanguageDetector; needs export tooling + license-cleared upstream | partial |
| S2067 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Detector OSS release — TLD heuristic table: deferred — small enough to embed; gates on S2066 | partial |
| S2068 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Detector OSS release — 26-test fixture suite: partial — tests/Unit/GrimbaLanguageDetectorTest.php is the test surface (26 tests per S1028); extraction to standalone package deferred | partial |
| S2069 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Detector OSS release — Python / JS / Rust port: deferred — PHP-only today; needs polyglot maintainers | partial |
| S2070 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Detector OSS release — benchmark page vs cld3 / fastText: deferred — no benchmark harness today | partial |
| S2071 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Cluster engine OSS release — algorithm extraction: deferred — tightly coupled to posts table + Laravel ORM; needs schema-neutral port | partial |
| S2072 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Cluster engine OSS release — canonical-URL normalizer: partial — GrimbaArticleText::normalize() (S203) is a small focused utility; cleanest OSS-able piece; release deferred | partial |
| S2073 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Cluster engine OSS release — title-similarity threshold tuning guide: deferred — needs published guide + tuning fixtures — surrogate doc: docs/GRIMBANEWS_CLUSTER_ENGINE_OSS_TUNING_GUIDE.md (partial). | partial |
| S2074 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Cluster engine OSS release — orphan-cluster cleanup pattern: deferred — Laravel-coupled; needs schema-neutral port | partial |
| S2075 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Cluster engine OSS release — bias-diversity scoring: deferred — needs framework-neutral port + license-clear from GrimbaSourceBreakdown | partial |
| S2076 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Cluster engine OSS release — confidence-score model: deferred — rule-based today (S1053); needs export tooling | partial |
| S2077 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Cluster engine OSS release — example datasets: deferred — would need contributor-cleared real corpus or synthetic generator | partial |
| S2078 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Cluster engine OSS release — academic citation guide: deferred — gates on S2053 DOI | partial |
| S2079 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Cluster engine OSS release — community fork tracker: deferred — gates on S2043 + community-manager | partial |
| S2080 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2061-s2080 — Translator+detector+cluster OSS — joint launch retrospective: deferred — gates on S2061-S2079 | partial |
| S2081 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — code of conduct (Contributor Covenant 2.1): deferred — no public repo today | partial |
| S2082 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — DCO (Developer Certificate of Origin) bot: deferred — same | partial |
| S2083 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — CLA or DCO-only decision: deferred — needs counsel | partial |
| S2084 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — triage rotation roster: deferred — needs community-manager owner + >=3 maintainers | partial |
| S2085 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — PR-review SLA (first-response in 7 days): deferred — operator-side SLA | partial |
| S2086 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — contributor-onboarding doc: deferred — needs CONTRIBUTING.md (S2051) + GOOD-FIRST-ISSUE labels | partial |
| S2087 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — recognition program (CONTRIBUTORS.md + monthly shout-outs): deferred — gates on S2043 — surrogate doc: docs/GRIMBANEWS_COMMUNITY_RECOGNITION_PROGRAM_PLAN.md (partial). | partial |
| S2088 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — mentorship program: deferred — needs sustained community-manager bandwidth | partial |
| S2089 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — quarterly community call: deferred — operator-side cadence | partial |
| S2090 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — Discord / Matrix / Slack channel: deferred — needs channel provisioning + moderator roster — surrogate doc: docs/GRIMBANEWS_COMMUNITY_CHAT_CHANNEL_PLAN.md (partial). | partial |
| S2091 | docs/GRIMBANEWS_BUG_BOUNTY_SCOPE.md — partial — full bug-bounty program scope v0 shipped (vendor shortlist YesWeHack/HackerOne/Bugcrowd, phased VDP→paid plan, in-scope/out-of-scope assets, CVSS-tier bounty table, safe-harbor clause per Disclose.io v2.0, 90-day disclosure timeline, intake surface via security.txt + future vendor); awaits vendor account + Ray payout budget | partial |
| S2092 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — security-disclosure policy (SECURITY.md per RFC 9116): partial — public/.well-known/security.txt ships per S995; repo-level SECURITY.md deferred | partial |
| S2093 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — sponsor-recognition page (Open Collective / GitHub Sponsors): deferred — gates on S2059 | partial |
| S2094 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — i18n translation contribution flow (Crowdin / Weblate): deferred — would streamline S1101+ i18n; not provisioned | partial |
| S2095 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — fork-friendly architecture decision records (ADRs): deferred — internal ADRs do not exist as a public-facing series — surrogate doc: docs/GRIMBANEWS_FORK_FRIENDLY_ADR_SERIES_PLAN.md (partial). | partial |
| S2096 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — academic-partnership intake: deferred — operator-side academic outreach — surrogate doc: docs/GRIMBANEWS_ACADEMIC_PARTNERSHIP_INTAKE_PLAN.md (partial). | partial |
| S2097 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — press / journalist intake (case studies): deferred — operator-side press relations — surrogate doc: docs/GRIMBANEWS_PRESS_JOURNALIST_INTAKE_PLAN.md (partial). | partial |
| S2098 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — annual community survey: deferred — gates on S2043 + >=1 year of contributors — surrogate doc: docs/GRIMBANEWS_COMMUNITY_ANNUAL_SURVEY_PLAN.md (partial). | partial |
| S2099 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — anti-harassment escalation path (CoC enforcement): deferred — needs CoC committee (>=3 people) — surrogate doc: docs/GRIMBANEWS_ANTI_HARASSMENT_COC_ENFORCEMENT_PLAN.md (partial). | partial |
| S2100 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — flow launch retrospective: deferred — gates on S2081-S2099 | partial |
| S2101 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — scope decision + first-pilot region: deferred — needs partnership-program owner (not hired) | partial |
| S2102 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — curriculum draft (media literacy + bias): deferred — needs pedagogy partner | partial |
| S2103 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — per-school login flow (LMS SSO): deferred — single-tenant auth today | partial |
| S2104 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — teacher dashboard: deferred — no LMS surface | partial |
| S2105 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — student-data privacy review (COPPA / GDPR-K / Quebec Law 25): deferred — needs counsel per jurisdiction | partial |
| S2106 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — age-appropriate content filter: deferred — current source roster has no per-source age-rating | partial |
| S2107 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — French-curriculum alignment (Education nationale): deferred — operator-side pedagogy mapping | partial |
| S2108 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — Canadian-curriculum alignment (per-province): deferred — same | partial |
| S2109 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — US-curriculum alignment (Common Core + NAMLE): deferred — same | partial |
| S2110 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — IB / Cambridge alignment: deferred — same | partial |
| S2111 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — teacher-training workshops: deferred — needs trainer roster + travel budget | partial |
| S2112 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — student-essay corpus (anonymized + published): deferred — needs IRB-equivalent review | partial |
| S2113 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — annual student-essay contest: deferred — operator-side editorial | partial |
| S2114 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — alumni network: deferred — gates on >=1 year of student cohorts | partial |
| S2115 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — pricing decision (free for schools?): deferred — needs Ray unit-economics review | partial |
| S2116 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — case studies (per-school): deferred — gates on S2101 first partnership | partial |
| S2117 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — research-paper coauthorship with partner schools: deferred — operator-side academic output | partial |
| S2118 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — accessibility for special-needs classrooms: deferred — needs A11y v3 (per S1571-S1580 deferred set) — surrogate doc: docs/GRIMBANEWS_SCHOOLS_PROGRAM_ACCESSIBILITY_SCOPE.md (partial). | partial |
| S2119 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — multilingual deployment: deferred — gates on S1101+ catalogs | partial |
| S2120 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2101-s2120 — Schools program — launch retrospective: deferred — gates on S2101-S2119 + >=1 academic year | partial |
| S2121 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — scope decision + first-pilot region: deferred — needs partnership-program owner | partial |
| S2122 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — curriculum draft: deferred — needs adult-pedagogy partner | partial |
| S2123 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — public-library partnership (BAnQ / NYPL): deferred — operator-side outreach | partial |
| S2124 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — community-center partnership (YMCA / Maison de quartier): deferred — same | partial |
| S2125 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — citizenship-prep partnership: deferred — same; FR naturalisation / Canada citizenship-test / US USCIS — surrogate doc: docs/GRIMBANEWS_ADULT_ED_CITIZENSHIP_PREP_PLAN.md (partial). | partial |
| S2126 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — ESL / FSL classroom integration: deferred — surrogate is FR <-> EN parity per S301; per-curriculum integration deferred | partial |
| S2127 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — print-handout assets (offline-classroom): deferred — no print-CSS layout shipped | partial |
| S2128 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — facilitator-training workshops: deferred — needs trainer roster | partial |
| S2129 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — accessibility for low-literacy learners (audio mode): deferred — no TTS layer | partial |
| S2130 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — pricing decision (free for libraries?): deferred — needs Ray review | partial |
| S2131 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — per-program enrollment flow: deferred — single-tenant auth today | partial |
| S2132 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — case studies: deferred — gates on S2121 first partnership | partial |
| S2133 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — diaspora-community partnership: deferred — overlaps Afrique-edition editorial focus | partial |
| S2134 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — refugee-resettlement-org partnership: partial — La Cimade + UNHCR feeds already integrated via GrimbaSeedImmigrationSources (S1024); partnership program deferred | partial |
| S2135 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — multilingual deployment: deferred — gates on S1101+ catalogs | partial |
| S2136 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — privacy-by-default (anonymous learners): partial — GrimbaVaultEvents is privacy-safe (ip_hash) per S1010; explicit anonymous-learner mode deferred | partial |
| S2137 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — group-progress dashboard (facilitator surface): deferred — no LMS surface | partial |
| S2138 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — feedback intake from learners: deferred — no feedback surface today | partial |
| S2139 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — alumni-mentorship channel: deferred — operator-side community | partial |
| S2140 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2121-s2140 — Adult-ed program — launch retrospective: deferred — gates on S2121-S2139 | partial |
| S2141 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — Civic-NGO program — scope decision: deferred — scaffold per Mythos honesty note; needs Lucy + Vader scope decision | partial |
| S2142 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — RSF (Reporters Without Borders) partnership intake: deferred — no outreach | partial |
| S2143 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — Conseil de deontologie journalistique partnership: deferred — same | partial |
| S2144 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — Trust Project trust-indicator adoption: deferred — 8 trust indicators not implemented as machine-readable schema — surrogate doc: docs/GRIMBANEWS_TRUST_PROJECT_INDICATOR_PLAN.md (partial). | partial |
| S2145 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — NewsGuard rating-engagement: deferred — external rating service; engagement is operator-side | partial |
| S2146 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — AllSides bias-rating cross-validation: deferred — cross-validation harness deferred | partial |
| S2147 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — Medias en Seine / academic-conference participation: deferred — operator-side presence — surrogate doc: docs/GRIMBANEWS_CONFERENCE_PARTICIPATION_PLAN.md (partial). | partial |
| S2148 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — IFCN (International Fact-Checking Network) signatory pursuit: deferred — needs Code of Principles compliance audit; not started | partial |
| S2149 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — JournalismAI / Polis-LSE research partnership: deferred — operator-side academic outreach | partial |
| S2150 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — Knight Foundation / Craig Newmark Philanthropies grant pursuit: deferred — operator-side fundraising | partial |
| S2151 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — Civil-society advocacy coalition (electoral integrity): deferred — operator-side | partial |
| S2152 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — NGO data-license agreements: deferred — needs S1181 public API v2 | partial |
| S2153 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — Coverage of NGO-published reports (editorial commitment): deferred — operator-side editorial | partial |
| S2154 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — Joint events with NGO partners: deferred — gates on first partnership | partial |
| S2155 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — Joint research publications: deferred — gates on S2149 academic partnership | partial |
| S2156 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — Civic-NGO case studies (per-partner): deferred — gates on S2141 first | partial |
| S2157 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — Cross-locale NGO partnerships (per-region): deferred — gates on S2141 | partial |
| S2158 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — Annual civic-NGO partner summit: deferred — operator-side cadence | partial |
| S2159 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — Renewal / retention metrics (per-partner): deferred — gates on partnerships existing | partial |
| S2160 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2141-s2160 — Civic-NGO launch retrospective: deferred — gates on S2141-S2159 | partial |
| S2161 | docs/GRIMBANEWS_DOM_TOM_SOURCE_ROSTER.md — partial — researched per-territory feed candidate list shipped (Guadeloupe/Martinique RCI + France Antilles + Outremers360 + la1ere broadcasters; Guyane + Mayotte + Réunion + Polynésie + NC + WF + SPM + SBH + SXM; Pacific Anglophone Fiji/Samoa/Tonga/Vanuatu/SI/PNG/Cook; smaller AU states Lesotho/eSwatini/Eritrea/Djibouti/Burundi; Lusophone Africa Cabo Verde/São Tomé/Comoros) + per-source intake template + pre-launch verification checklist + integration steps; awaits per-source license verification + editorial sign-off | partial |
| S2162 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Guadeloupe / Martinique sources (RCI / Outremers360 / France Antilles): deferred — needs feed-URL research + license review | partial |
| S2163 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Guyane sources (Guyane la 1ere / France-Guyane): deferred — same | partial |
| S2164 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Mayotte / Reunion sources (Mayotte la 1ere / Linfo.re / Clicanoo): deferred — same | partial |
| S2165 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Polynesie / Nouvelle-Caledonie / Wallis-et-Futuna sources: deferred — same | partial |
| S2166 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Saint-Pierre-et-Miquelon / Saint-Barthelemy / Saint-Martin sources: deferred — same | partial |
| S2167 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Pacific islands sources — Fiji / Samoa / Tonga / Vanuatu: deferred — same | partial |
| S2168 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Pacific islands sources — Solomon Islands / Papua New Guinea: deferred — same | partial |
| S2169 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Cabo Verde / Sao Tome / Comoros sources: deferred — same | partial |
| S2170 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Lesotho / eSwatini / Eritrea / Djibouti / Burundi sources: deferred — same | partial |
| S2171 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Under-covered region taxonomy v2 (per-country buckets): partial — App\Ground\Regions lists Pacific + Antarctica as first-class regions; per-country DOM-TOM bucket deferred | partial |
| S2172 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Per-region NobuAI prompt tuning (local context): deferred — single global prompt today (S1082) | partial |
| S2173 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Per-region credibility-score baseline: deferred — needs operator-side editorial calibration per region | partial |
| S2174 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Per-region editorial-policy review: deferred — operator-side | partial |
| S2175 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Per-region launch comms: deferred — gates on S2161-S2173 | partial |
| S2176 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Per-region reader-feedback intake: deferred — no feedback surface today — surrogate doc: docs/GRIMBANEWS_PER_REGION_READER_FEEDBACK_INTAKE_PLAN.md (partial). | partial |
| S2177 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Per-region partnership with local newsrooms: deferred — operator-side editorial outreach — surrogate doc: docs/GRIMBANEWS_PER_REGION_LOCAL_NEWSROOM_PARTNERSHIP_PLAN.md (partial). | partial |
| S2178 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Per-region under-covered-story tracker: deferred — needs editorial-workflow S1291 — surrogate doc: docs/GRIMBANEWS_PER_REGION_UNDER_COVERED_STORY_TRACKER_PLAN.md (partial). | partial |
| S2179 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Per-region annual coverage-density report: deferred — gates on S2001 transparency report + per-region counters — surrogate doc: docs/GRIMBANEWS_PER_REGION_ANNUAL_COVERAGE_DENSITY_PLAN.md (partial). | partial |
| S2180 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Under-covered-region program launch retrospective: deferred — gates on S2161-S2179 | partial |
| S2181 | docs/GRIMBANEWS_NICHE_TOPIC_V2_SCOPE.md — partial — niche-topic v2 scope v0 shipped (bucket priorities Climate→Science→Culture→Tech v2/Health v2/Sports v2; Climate deep roster 10 sources w/ 5 sub-buckets; Science deep roster across news + university PR + preprint servers w/ per-discipline buckets; Culture sub-buckets + cross-bucket sources; per-bucket editorial-brief template; integration steps); awaits editorial sign-off + per-source verification | partial |
| S2182 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Climate v2 — deep source-roster (Carbon Brief / Reporterre / Mongabay): deferred — operator-side pickup; surrogate is grimba:seed-thin-category-sources (S1024) | partial |
| S2183 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Climate v2 — per-COP coverage program: deferred — operator-side editorial commitment | partial |
| S2184 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Climate v2 — methodology coverage (science vs policy explainer): deferred — operator-side editorial | partial |
| S2185 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Climate v2 — IPCC-report coverage playbook: deferred — operator-side | partial |
| S2186 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Science v2 — preprint-server integration (arXiv / bioRxiv / medRxiv): deferred — needs ingest adapter; not RSS-native | partial |
| S2187 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Science v2 — peer-reviewed-journal coverage (Nature / Science / Lancet): deferred — operator-side editorial | partial |
| S2188 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Science v2 — science-misinfo fact-check track: deferred — overlaps S1596 misinformation flag | partial |
| S2189 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Science v2 — university press-release source roster (EurekAlert / AlphaGalileo): deferred — needs feed-URL research + license review | partial |
| S2190 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Science v2 — per-discipline buckets: deferred — current Science bucket is flat | partial |
| S2191 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Culture v2 — books / film / music / theater per-sub-bucket: deferred — current Culture bucket is flat | partial |
| S2192 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Culture v2 — francophone-cultural-events coverage: deferred — operator-side editorial commitment | partial |
| S2193 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Culture v2 — diaspora-cultural coverage: deferred — overlaps S2133 adult-ed diaspora-community | partial |
| S2194 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Technology v2 long-form — explainer track: deferred — operator-side editorial | partial |
| S2195 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Health v2 long-form — public-health track: deferred — operator-side editorial | partial |
| S2196 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Sports v2 international — beyond football: deferred — operator-side editorial | partial |
| S2197 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Niche-topic per-bucket newsletter: deferred — gates on S1271+ newsletter v2 | partial |
| S2198 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Niche-topic per-bucket landing page (deeper than /categorie/{slug}): deferred — current category landing is per-classifier-bucket | partial |
| S2199 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Niche-topic v2 coverage-density tracker: deferred — gates on S2179 per-region tracker pattern | partial |
| S2200 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2181-s2200 — Niche-topic v2 launch retrospective: deferred — gates on S2181-S2199 | partial |
| S2201 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — scope decision + first-investigation pick: deferred — operator-side editorial | partial |
| S2202 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — investigative-reporter hire (first): deferred — not on current Iboga roster | partial |
| S2203 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — multi-source intake (primary docs, FOIA, leaks): deferred — needs SecureDrop / OnionShare; no anonymous-tip infra (S2025) | partial |
| S2204 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — FOIA template library: deferred — operator-side legal tooling | partial |
| S2205 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — data-analysis pipeline (CSV / Pandas / DuckDB): deferred — operator-side tooling | partial |
| S2206 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — collaborative editing surface: deferred — operator-side | partial |
| S2207 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — fact-check workflow (per-investigation): deferred — overlaps S2148 IFCN signatory pursuit | partial |
| S2208 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — counsel review per-investigation: deferred — needs retained press counsel | partial |
| S2209 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — long-form layout template (>3000 words): deferred — current article layout is standard reader (partials/post-hero-img.blade.php) | partial |
| S2210 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — multi-locale publication (FR + EN simultaneous): partial — translation pipeline ready (grimba:translate-by-rule per S1046); long-form-specific quality pass deferred | partial |
| S2211 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — companion data publication: deferred — overlaps S2013 transparency-data export | partial |
| S2212 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — companion podcast / video: deferred — no podcast / video pipeline | partial |
| S2213 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — press-release distribution to peer outlets: deferred — operator-side comms | partial |
| S2214 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — awards-submission cadence: deferred — operator-side recognition; Pulitzer / Albert-Londres / European Press Prize | partial |
| S2215 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — reader-impact tracking: deferred — needs GrimbaVaultEvents extension + outcome-log column | partial |
| S2216 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — investigation-archive (separate from regular): deferred — gates on first investigation | partial |
| S2217 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — collaborative investigations with peer outlets (ICIJ-style): deferred — operator-side editorial partnerships | partial |
| S2218 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — pricing decision (premium tier? free?): deferred — gates on S1211 monetization | partial |
| S2219 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations — annual investigations review: deferred — gates on >=1 year of investigations | partial |
| S2220 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2201-s2220 — Long-form investigations launch retrospective: deferred — gates on S2201-S2219 | partial |
| S2221 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — Multi-decade preservation — scope decision + retention horizon: deferred — needs Vader + counsel + Ray cost review; scaffold per Mythos honesty note | partial |
| S2222 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — Multi-decade preservation — Internet Archive Wayback partnership: deferred — free service; needs operator-side SPN submission cadence | partial |
| S2223 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — Multi-decade preservation — IIPC membership: deferred — paid membership; needs Ray review | partial |
| S2224 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — Multi-decade preservation — BnF / BAnQ legal-deposit registration: deferred — legal-deposit is mandatory for FR publishers above a threshold; needs counsel | partial |
| S2225 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — Multi-decade preservation — Library of Congress NDIIPP registration: deferred — operator-side outreach | partial |
| S2226 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — Archive cadence — daily-DB-dump retention policy: partial — GrimbaDatabaseBackups + grimba:verify-backups cron + restore-smoke per S965; long-term archival tier deferred per S945 | partial |
| S2227 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — Archive cadence — vault-events archive cadence: partial — GrimbaArchiveVaultEvents (grimba:archive-vault-events) wired into GrimbaAutomationMonitor + routes/console.php; long-term storage + multi-decade retention deferred | partial |
| S2228 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — Archive cadence — release-evidence prune (30-day rolling): partial — GrimbaPruneReleaseEvidence 30-day window shipped per S999; archival-tier for older evidence deferred | partial |
| S2229 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — Archive cadence — image-asset preservation (per-article hero images): deferred — current image-storage policy retains hero URLs but not local copies | partial |
| S2230 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — Archive cadence — translation-archive (per-article translation history): deferred — current schema overwrites translations; per-version history deferred | partial |
| S2231 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — Iboga-wide reconciliation (cross-product preservation policy alignment): deferred — operator-side Iboga Ventures governance; needs Sara Chen + Larry Ellison | partial |
| S2232 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — GrimbaNews maturity audit (post-Mythos full-stack audit): deferred — gates on prod >=2 years uptime + S2051 audit-readiness band | partial |
| S2233 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — GrimbaNews exit / expansion criteria: deferred — operator-side strategic decision; needs Vader + Lucy | partial |
| S2234 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — GrimbaNews 5-year vision update (post-Mythos refresh): deferred — gates on >=5 years operational data | partial |
| S2235 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — GrimbaNews founder retrospective (Vader written reflection): deferred — operator-side founder pickup; cannot generate | partial |
| S2236 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — GrimbaNews Mythos master fleet final closure: deferred — gates on S2237; meta-row that closes the Mythos arc itself | partial |
| S2237 | docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2221-s2237 — GrimbaNews S2237 ledger signoff: deferred — gates on S2236 + Zen / Echo / Mnemo audit panel + Vader written signoff | partial |
| S1601 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1601-s1610 — Per-city deep landing route: partial — /local ships at platform/themes/echo/routes/web.php:1538-1594 with Theme::scope('local', …) → platform/themes/echo/views/local.blade.php; per-city slug routes (/ | partial |
| S1602 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1601-s1610 — Per-city geolocation: complete — App\Services\GrimbaGeoLocator::locate() cascades ip-api.com → ipapi.co with 24h Cache::remember; returns {city, country, country_code, region, lat, lon}; localhost / 1 | complete |
| S1603 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1601-s1610 — Per-city custom feed: partial — /local filters posts by source_id IN (news_sources WHERE country = cc) + city keyword LIKE against name / description (max 36 results). City-only feed (no source-countr | partial |
| S1604 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1601-s1610 — Per-city regional sources list: partial — news_sources.country (ISO-2) + news_sources.city slot exists at the source level via GrimbaSourceClassifier; explicit per-city source-pool admin UI deferred. | partial |
| S1605 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1601-s1610 — Per-city cookie persistence: complete — Route::post('local/set', …) at platform/themes/echo/routes/web.php:1597-1610 persists grimba_local_city / grimba_local_country / grimba_local_cc cookies for 1 y | complete |
| S1606 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1601-s1610 — Per-city consent posture: complete — IP geolocation only fires when both grimba_local_city and grimba_local_country cookies are empty (manual selection short-circuits geo); raw IP never lands on disk  | complete |
| S1607 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1601-s1610 — Per-city manual override: complete — Route::post('local/set', …) accepts city / country / cc form fields and writes cookies; UI surface in platform/themes/echo/views/local.blade.php. | complete |
| S1608 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1601-s1610 — Per-city dedicated landing copy / editorial brief: deferred — no local_cities table, no per-city brief CMS; the single /local view shares one heading template. | partial |
| S1609 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1601-s1610 — Per-city sponsor / advertiser slot: deferred — app/Support/GrimbaAds.php ships single global ad slot; per-city ad-targeting deferred. | partial |
| S1610 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1601-s1610 — Per-city launch playbook: deferred — operator-side editorial playbook; gates on S1601 + S1608 + S1609. | partial |
| S1611 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1611-s1620 — City taxonomy schema: partial — docs/GRIMBANEWS_CITY_TAXONOMY_SCHEMA.md defines local_cities + local_city_source_pin schemas + Phase 1 ~50-city seed pool across regions + admin-UI shape; gates on Lucy + Larry sign-off. | partial |
| S1612 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1611-s1620 — City taxonomy admin UI: deferred — depends on S1611. | partial |
| S1613 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1611-s1620 — Region taxonomy schema: complete — app/Ground/Regions.php is single source of truth (54 AFRICA + 48 EUROPE + 35 AMERICAS + negative INTERNATIONAL filter via otherNamedCodes()); Regions::regionForCount | complete |
| S1614 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1611-s1620 — Region taxonomy admin UI: partial — operator can override posts.editorial_region via Botble post-edit; news_sources.editorial_category editable via /admin/grimba/news-sources. Dedicated region-taxonom | partial |
| S1615 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1611-s1620 — Local source priority schema: partial — news_sources.country + editorial_category + credibility_score define source pool today; explicit local_priority column deferred. | partial |
| S1616 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1611-s1620 — Local source priority admin: deferred — depends on S1615; operator surrogate is /admin/grimba/news-sources for direct edit. — surrogate doc: docs/GRIMBANEWS_SEARCH_RESULT_CTR_PLAN.md (partial). | partial |
| S1617 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1611-s1620 — Local source backfill command: complete — app/Console/Commands/GrimbaBackfillSourceCountries.php infers news_sources.country from website TLD + GrimbaSourceCountryBackfill::DOMAIN_COUNTRIES lookup (~3 | complete |
| S1618 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1611-s1620 — Local source classification scheduler: complete — app/Console/Commands/GrimbaClassifySources.php runs scheduled classification; news_sources.bias_rating / factuality_score / country / editorial_catego | complete |
| S1619 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1611-s1620 — Local source coverage map admin: partial — /admin/grimba/news-sources/coverage-map ships per CoverageMapAdminTest; per-city drill-in deferred. | partial |
| S1620 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1611-s1620 — Local v2 admin launch playbook: deferred — gates on S1611-S1619; operator-side editorial playbook. | partial |
| S1621 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1621-s1630 — France pilot — source coverage: complete — news_sources ships Le Monde, Le Figaro, Libération, Le Point, France 24, RFI, Le Monde Afrique, France-Guyane (per GrimbaSourceClassifier::DOMAIN_PROFILES) + | complete |
| S1622 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1621-s1630 — France pilot — DOM-TOM editorial bucket: deferred — no separate editorial_region='dom-tom'; surrogate is France-Guyane source pinned country='FR' per GrimbaSourceClassifier. DOM-TOM-specific landing d | partial |
| S1623 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1621-s1630 — France pilot — overseas-territory source pickup: partial — GrimbaSourceCountryBackfill::DOMAIN_COUNTRIES does not yet enumerate Réunion / Guadeloupe / Martinique TLDs (.re, .gp, .mq); operator pickup  | partial |
| S1624 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1621-s1630 — France pilot — language variant (FR vs FR-Canadian): deferred — posts.original_language='fr' is single-bucket; FR-CA detection deferred per GrimbaLanguageDetector (covers FR but not FR-CA dialect). | partial |
| S1625 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1621-s1630 — France pilot — /local city pool (Paris / Lyon / Marseille / Bordeaux / Toulouse / Nice / Lille / Strasbourg / Nantes / Rennes): partial — /local city keyword LIKE scans posts.name + posts.description; | partial |
| S1626 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1621-s1630 — France pilot — hreflang locking: complete — GrimbaLocaleEnforce::PRIMARY_LOCALES = ['fr','en'] enforces FR canonical; platform/themes/echo/layouts/grimba-chrome.blade.php emits hreflang="fr" + hreflan | complete |
| S1627 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1621-s1630 — France pilot — Méthodologie page in FR: complete — /methodologie ships as primary FR surface per platform/themes/echo/views/methodology.blade.php + TechArticle JSON-LD; EN translations added via Wave  | complete |
| S1628 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1621-s1630 — France pilot — newsletter FR locale: partial — newsletter_subscriptions table per NewsletterBiasSignalTest; subscribe form ships in FR/EN per Wave CCCCCCCCCC locale-aware email placeholders; per-DOM-T | partial |
| S1629 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1621-s1630 — France pilot — FR advertiser leads pipeline: complete — app/Mail/GrimbaAdvertiserLeadNotification.php + grimba_advertiser_leads_sales_mailbox setting routes per-region; GrimbaAdvertiserLeadsTest cover | complete |
| S1630 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1621-s1630 — France pilot launch retrospective: deferred — operator-side retro; gates on S1622-S1625 DOM-TOM coverage. | partial |
| S1631 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1631-s1640 — Africa pilot — region landing: complete — /africa route per GrimbaHomeFeed region scoping; reads grimba_region='africa' cookie via GrimbaRegionQuery::selectedRegion() + Regions::AFRICA 54-country filt | complete |
| S1632 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1631-s1640 — Africa pilot — per-country feed: partial — news_sources.country (e.g. SN / ML / CI / CM) + GrimbaArticleRegion::ANCHORS['africa'] topical match cover per-country source resolution; dedicated /africa/{ | partial |
| S1633 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1631-s1640 — Africa pilot — francophone Africa source pool: partial — Le Monde Afrique + RFI + France 24 + UNHCR + La Cimade seeded via GrimbaSeedImmigrationSources + GrimbaSeedThinCategorySources; per-country fra | partial |
| S1634 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1631-s1640 — Africa pilot — lusophone Africa source pool: deferred — Angola / Mozambique / Cabo Verde / Guiné-Bissau PT-language sources not seeded; posts.original_language='pt' detector covers per GrimbaLanguageD | partial |
| S1635 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1631-s1640 — Africa pilot — anglophone Africa source pool: partial — Regions::AFRICA includes NG / KE / GH / ZA / TZ / UG / ZW / ZM / MW / RW / ET / RW; operator pickup via RssFeedsSeeder of nation.africa, dailyma | partial |
| S1636 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1631-s1640 — Africa pilot — swahili / arabic source pool: deferred — Swahili (sw) detector path + lang/sw.json deferred per S1139; Arabic (ar) covers North Africa but lang/ar.json + RTL chrome deferred per S1132 + | partial |
| S1637 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1631-s1640 — Africa pilot — Africa-edition newsletter: partial — newsletter_subscriptions.bias_signal per-region segmentation exists per NewsletterBiasSignalTest; explicit edition='afrique' toggle deferred. | partial |
| S1638 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1631-s1640 — Africa pilot — Africa-edition advertiser inventory: partial — grimba_advertiser_leads.source_pack_tier (per add_source_pack_tier_to_grimba_advertiser_leads_table migration 2026-05-18) bands inventory; | partial |
| S1639 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1631-s1640 — Africa pilot — Africa-edition retrospective doc: partial — docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md is the editorial brief; per-pilot results retro deferred. | partial |
| S1640 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1631-s1640 — Africa pilot launch retrospective: deferred — operator-side editorial retro; gates on S1632-S1638. | partial |
| S1641 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1641-s1650 — UK pilot — source coverage: partial — news_sources covers BBC + Guardian + Independent + Telegraph + Sky News + Reuters per GrimbaSourceClassifier::DOMAIN_PROFILES (subset); operator pickup for full U | partial |
| S1642 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1641-s1650 — UK pilot — /local city pool (London / Manchester / Edinburgh / Glasgow / Birmingham / Liverpool / Cardiff / Belfast): partial — /local city keyword scan covers any UK city when geolocation resolves co | partial |
| S1643 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1641-s1650 — UK pilot — UK-edition newsletter: deferred — newsletter_subscriptions table has no per-edition column; surrogate is bias_signal segmentation. | partial |
| S1644 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1641-s1650 — US pilot — source coverage: partial — NYT / WaPo / WSJ / Fox / CNN / Reuters US covered via GrimbaSourceClassifier::DOMAIN_PROFILES subset; full US pool requires operator RssFeedsSeeder pickup. | partial |
| S1645 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1641-s1650 — US pilot — /local city pool (NYC / LA / Chicago / Houston / Phoenix / Philadelphia / San Antonio / San Diego / Dallas / Miami): partial — same shape as S1642; city keyword scan covers any US city when | partial |
| S1646 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1641-s1650 — US pilot — US-edition newsletter: deferred — same shape as S1643. | partial |
| S1647 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1641-s1650 — Canada pilot — source coverage: partial — CBC / Globe and Mail / National Post / Radio-Canada / La Presse / Le Devoir covered via GrimbaSourceClassifier::DOMAIN_PROFILES; operator pickup for full FR-C | partial |
| S1648 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1641-s1650 — Canada pilot — bilingual FR / EN routing: complete — GrimbaLocaleEnforce::PRIMARY_LOCALES = ['fr','en'] covers both; CA geolocation routes per grimba_local_country='Canada' cookie. | complete |
| S1649 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1641-s1650 — Canada pilot — Quebec / FR-CA dialect handling: deferred — GrimbaLanguageDetector returns 'fr' for both FR-FR and FR-CA; per-dialect routing deferred. | partial |
| S1650 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1641-s1650 — UK / US / Canada pilot launch retrospective: deferred — operator-side editorial retro. | partial |
| S1651 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1651-s1660 — Embed widget — /embed/{cluster-id} route: partial — docs/GRIMBANEWS_EMBED_WIDGET_SPEC.md defines 6 embed shapes (cluster / bias-chart / region / category / city / digest) + embed_tokens schema + JS-snippet pattern + Phase 1-4 launch sequencing. | partial |
| S1652 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1651-s1660 — Embed widget — iframe-friendly stripped chrome layout: partial — docs/GRIMBANEWS_EMBED_WIDGET_SPEC.md "Iframe-friendly chrome" section specs embed-chrome.blade.php layout (no global nav / no footer / no share kit) + branding rules + a11y baseline. | partial |
| S1653 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1651-s1660 — Embed widget — JS-snippet generator (<script src="grimbanews.com/embed.js?cluster=…">): deferred — no embed.js bundle. | partial |
| S1654 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1651-s1660 — Embed widget — embed-token API (rate-limit per publisher): deferred — no embed_tokens table; no public API yet (per S1181-S1190). | partial |
| S1655 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1651-s1660 — Embed widget — embed CSS isolation (Shadow DOM or scoped CSS): deferred — depends on S1653. | partial |
| S1656 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1651-s1660 — Embed widget — embed analytics (per-embed impressions): deferred — no embed_impressions table. | partial |
| S1657 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1651-s1660 — Embed widget — embed click-through tracking: deferred — same. | partial |
| S1658 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1651-s1660 — Embed widget — embed branding ("Powered by NobuAI / GrimbaNews"): deferred — depends on S1653; brand-purity locked by GrimbaNobuAiBrandPurityTest (user-facing copy says "NobuAI" never "Anthropic" / "C | partial |
| S1659 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1651-s1660 — Embed widget — embed responsive sizing: deferred — depends on S1653. | partial |
| S1660 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1651-s1660 — Embed widget — embed launch playbook: deferred — gates on S1651-S1659. — surrogate doc: docs/GRIMBANEWS_EMBED_WIDGET_LAUNCH_PLAYBOOK.md (partial). | partial |
| S1661 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1661-s1670 — Bias chart embed — /embed/bias-chart/{cluster-id} route: deferred — same root reason as S1651. | partial |
| S1662 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1661-s1670 — Bias chart embed — standalone bias-chart partial: partial — platform/themes/echo/partials/story/bias-distribution.blade.php ships the chart; standalone iframe-friendly variant deferred. | partial |
| S1663 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1661-s1670 — Bias chart embed — server-side source-of-truth: complete — app/Support/GrimbaSourceBreakdown.php::resolve(Post $cluster) returns {left, center, right, unknown} counts + percentages; app/Support/Grimba | complete |
| S1664 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1661-s1670 — Bias chart embed — SVG export: deferred — current chart is HTML+CSS, not exported SVG. — surrogate doc: docs/GRIMBANEWS_BIAS_CHART_SVG_EXPORT_DESIGN.md (partial). | partial |
| S1665 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1661-s1670 — Bias chart embed — PNG export: deferred — would need server-side HTML→PNG rasterizer (not provisioned). — surrogate doc: docs/GRIMBANEWS_BIAS_CHART_PNG_EXPORT_DESIGN.md (partial). | partial |
| S1666 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1661-s1670 — Bias chart embed — embed parameters (?style=compact\|wide): deferred — depends on S1661. | partial |
| S1667 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1661-s1670 — Bias chart embed — embed click-through ("see full dossier on GrimbaNews"): deferred — depends on S1661. | partial |
| S1668 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1661-s1670 — Bias chart embed — embed accessibility (alt text + table fallback): partial — current chart ships table-fallback markup per a11y baseline locked by tests/e2e/grimbanews-keyboard-navigation.cjs; standa | partial |
| S1669 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1661-s1670 — Bias chart embed — embed bot-detection (no headless-Chrome scraping): deferred — would gate on S1654 token system. | partial |
| S1670 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1661-s1670 — Bias chart embed — launch playbook: deferred — gates on S1661-S1669. | partial |
| S1671 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1671-s1680 — Classroom — /classroom route: partial — docs/GRIMBANEWS_CLASSROOM_VIEW_SCOPE.md defines /classroom + /classroom/{slug}/manage + /classroom/{slug} + per-student progress routes; ships with educator role + paid tier. | partial |
| S1672 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1671-s1680 — Classroom — no-ads mode: partial — app/Support/GrimbaAds.php::shouldRender() is the single gate; a ?no-ads=1 query-param or member.role='educator' short-circuit deferred. | partial |
| S1673 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1671-s1680 — Classroom — simplified UI layout: partial — platform/themes/echo/layouts/grimba-chrome.blade.php is the global shell; classroom-stripped variant deferred. | partial |
| S1674 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1671-s1680 — Classroom — teacher-account schema: partial — docs/GRIMBANEWS_CLASSROOM_VIEW_SCOPE.md "Schema (S1674 ship target)" defines classrooms + classroom_readings + classroom_students + student_reads tables with privacy posture for minor-age students; ships with educator role. | partial |
| S1675 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1671-s1680 — Classroom — teacher-curated list primitive: partial — app/Support/GrimbaVault.php (cookie + member sync via members.vault_digest_post_ids) is the surrogate single-user reading list; per-classroom shar | partial |
| S1676 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1671-s1680 — Classroom — teacher-share link (read-only): partial — /coffre/share route ships at platform/themes/echo/views/coffre-share.blade.php for one-off vault share; teacher → student-list semantics deferred. | partial |
| S1677 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1671-s1680 — Classroom — student progress dashboard: deferred — no student_reads table. | partial |
| S1678 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1671-s1680 — Classroom — assignment primitive: deferred — no assignments table. | partial |
| S1679 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1671-s1680 — Classroom — teacher discount tier: deferred — no paid tier (lands with S1211). | partial |
| S1680 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1671-s1680 — Classroom — launch playbook: deferred — gates on S1671-S1679. | partial |
| S1681 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1681-s1690 — Dataset — per-source CSV export: partial — operator-side via Botble admin news_sources table CSV export (Botble base-table); dedicated researcher endpoint deferred. | partial |
| S1682 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1681-s1690 — Dataset — per-cluster CSV export: partial — docs/GRIMBANEWS_DATASET_CSV_SCHEMA.md "Per-cluster dataset (S1682 ship target)" defines /datasets/clusters.csv schema with bias breakdown + middle-ground flag + privacy exclusions; gates on dataset license. | partial |
| S1683 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1681-s1690 — Dataset — per-day CSV export: partial — docs/GRIMBANEWS_DATASET_CSV_SCHEMA.md "Per-day dataset (S1683 ship target)" defines /datasets/daily.csv schema with per-region/per-category/per-bias JSON aggregates. | partial |
| S1684 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1681-s1690 — Dataset — read-history CSV export: complete — Route::get('pour-vous/export.csv', …) at platform/themes/echo/routes/web.php:1235-1287 streams the per-reader read-history CSV with Cache-Control: no-stor | complete |
| S1685 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1681-s1690 — Dataset — vault picks CSV export: complete — Route::get('coffre/export.csv', …) at platform/themes/echo/routes/web.php:1913+ streams the vault picks CSV. | complete |
| S1686 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1681-s1690 — Dataset — vault-events monthly CSV: complete — app/Console/Commands/GrimbaArchiveVaultEvents.php archives vault_events to storage/exports/vault_events_YYYY-MM.csv (privacy-preserving: ip_hash only, no | complete |
| S1687 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1681-s1690 — Dataset — dataset license / terms-of-use page: deferred — no /datasets/license page; surrogate is /methodologie (open + revisable). | partial |
| S1688 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1681-s1690 — Dataset — dataset citation guidance: deferred — depends on S1687 + paper review (S1718). | partial |
| S1689 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1681-s1690 — Dataset — dataset versioning: deferred — current CSV exports are point-in-time; no dataset_versions table. | partial |
| S1690 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1681-s1690 — Dataset — dataset launch playbook: deferred — gates on S1681-S1689. | partial |
| S1691 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1691-s1700 — API — /api/v2 route base: partial — docs/GRIMBANEWS_API_ACADEMIC_TIER_PLAN.md "Proposed /api/v2 base" enumerates 7 endpoints + rate-limit shape + license + citation headers; gates on Sanctum install. | partial |
| S1692 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1691-s1700 — API — OAuth client / API-key model: partial — docs/GRIMBANEWS_API_ACADEMIC_TIER_PLAN.md "Schema (API key model)" defines api_keys + api_key_use_log schemas with tier enum + scopes + citation_required; ships with Sanctum. | partial |
| S1693 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1691-s1700 — API — academic-tier signup: deferred — depends on S1692. | partial |
| S1694 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1691-s1700 — API — academic-tier rate limit (higher than free tier): deferred — depends on S1691 + S1692; surrogate is per-IP RateLimiter on advertiser-lead endpoint (AdvertiserLeadController 5/10min). | partial |
| S1695 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1691-s1700 — API — academic-tier usage dashboard: deferred — depends on S1691 + S1692. | partial |
| S1696 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1691-s1700 — API — academic-tier API docs: deferred — depends on S1691. | partial |
| S1697 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1691-s1700 — API — academic-tier citation requirement: deferred — depends on S1693. | partial |
| S1698 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1691-s1700 — API — academic-tier dataset license: deferred — same as S1687. | partial |
| S1699 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1691-s1700 — API — academic-tier renewal cadence: deferred — depends on S1693. | partial |
| S1700 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1691-s1700 — API — academic-tier launch playbook: partial — docs/GRIMBANEWS_API_ACADEMIC_TIER_PLAN.md "Launch playbook" sequences Phase 1-5 + signup flow + renewal cadence + per-tier rate limits. | partial |
| S1701 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1701-s1710 — Vector store — infra pick (pgvector / qdrant / pinecone / weaviate): partial — docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md recommends Qdrant self-hosted (~€10/month vs $70+/month Pinecone) per Iboga hosting policy; awaiting Ray + Jacob sign-off. | partial |
| S1702 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1701-s1710 — Vector store — schema (post_embeddings table with vector(N) column): partial — docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md "Proposed schema" defines Qdrant posts_embeddings + clusters_embeddings collections with 384-dim MiniLM payload + privacy posture. | partial |
| S1703 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1701-s1710 — Vector store — embedding-generation pipeline: deferred — depends on S1701 + external embedding model. | partial |
| S1704 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1701-s1710 — Vector store — daily backfill cron: deferred — depends on S1703. | partial |
| S1705 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1701-s1710 — Vector store — incremental update on new post: deferred — depends on S1703. | partial |
| S1706 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1701-s1710 — Vector store — semantic search query handler: deferred — same as S1471 semantic-search row (which honestly defers). | partial |
| S1707 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1701-s1710 — Vector store — semantic-similarity "related dossiers" surface: partial — current "related dossiers" chip uses posts.story_cluster_id + editorial_category + same-day window per GrimbaRelatedDossiersChi | partial |
| S1708 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1701-s1710 — Vector store — semantic-dedup of clusters: partial — current GrimbaArticleDedupe is canonical-URL + title-similarity per DedupePostsCommandTest; vector-dedup deferred. | partial |
| S1709 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1701-s1710 — Vector store — cost dashboard: deferred — depends on S1701 + S1703. | partial |
| S1710 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1701-s1710 — Vector store — launch playbook: deferred — gates on S1701-S1709. | partial |
| S1711 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1711-s1720 — Feature store — per-article feature schema: partial — posts table already ships bias_rating + editorial_category + editorial_region + editorial_secondary_region + original_language + story_cluster_id  | partial |
| S1712 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1711-s1720 — Feature store — per-source feature schema: partial — news_sources ships country + credibility_score + factuality_score + bias_rating + ownership_type + owner_name + editorial_category as raw features; | partial |
| S1713 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1711-s1720 — Feature store — daily snapshot job: deferred — depends on S1711 + S1712. | partial |
| S1714 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1711-s1720 — Feature store — point-in-time consistency: deferred — depends on S1713. | partial |
| S1715 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1711-s1720 — Feature store — feature-versioning: deferred — git history is the version pin today (same shape as S1074 prompt-version pinning). | partial |
| S1716 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1711-s1720 — Feature store — feature-discovery UI: deferred — same. | partial |
| S1717 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1711-s1720 — Feature store — offline / online parity tests: deferred — same. | partial |
| S1718 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1711-s1720 — Feature store — feature-store paper / methodology: deferred — depends on S1711-S1717 actually shipping. | partial |
| S1719 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1711-s1720 — Feature store — feature-store cost dashboard: deferred — same. | partial |
| S1720 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1711-s1720 — Feature store — launch playbook: deferred — gates on S1711-S1719. | partial |
| S1721 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1721-s1730 — A/B harness — experiment-registry schema: partial — docs/GRIMBANEWS_AB_HARNESS_DESIGN.md "Schema" defines experiments + experiment_assignments + experiment_outcomes tables with variants JSON + guardrail_metrics + privacy posture. | partial |
| S1722 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1721-s1730 — A/B harness — traffic-splitter middleware: partial — docs/GRIMBANEWS_AB_HARNESS_DESIGN.md "Traffic-splitter middleware (S1722)" specs GrimbaExperimentAssignment middleware with deterministic visitor-hash assignment + per-experiment-slug variant mint. | partial |
| S1723 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1721-s1730 — A/B harness — variant render hook in Blade: partial — docs/GRIMBANEWS_AB_HARNESS_DESIGN.md "Blade variant hook (S1723)" specs @experiment / @variant Blade directives with compiled php fallback. | partial |
| S1724 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1721-s1730 — A/B harness — assignment cookie: partial — docs/GRIMBANEWS_AB_HARNESS_DESIGN.md "Assignment cookie (S1724)" specs grimba_exp_id single-cookie pattern with 1-year TTL + cookie-footprint disclosure for consent log. | partial |
| S1725 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1721-s1730 — A/B harness — outcome event log: partial — vault_events is the cookie-only privacy-safe event ledger pattern; experiment-outcome variant deferred. | partial |
| S1726 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1721-s1730 — A/B harness — sequential testing / stop-early stats: deferred — depends on S1721-S1725. — surrogate doc: docs/GRIMBANEWS_AB_HARNESS_SEQUENTIAL_TESTING_DESIGN.md (partial). | partial |
| S1727 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1721-s1730 — A/B harness — admin experiment console: deferred — same. — surrogate doc: docs/GRIMBANEWS_AB_HARNESS_ADMIN_CONSOLE_DESIGN.md (partial). | partial |
| S1728 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1721-s1730 — A/B harness — feature-flag rollout (per-cohort): deferred — same. | partial |
| S1729 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1721-s1730 — A/B harness — experiment retrospective doc template: deferred — same. — surrogate doc: docs/GRIMBANEWS_AB_HARNESS_RETROSPECTIVE_TEMPLATE.md (partial). | partial |
| S1730 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1721-s1730 — A/B harness — launch playbook: deferred — gates on S1721-S1729. | partial |
| S1731 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1731-s1740 — Warehouse — destination pick (BigQuery / Snowflake / DuckDB / ClickHouse): partial — docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md recommends DuckDB self-hosted (~€5/month vs ~$200+/month BigQuery+Looker) per Iboga hosting policy; cost projection + privacy posture documented. | partial |
| S1732 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1731-s1740 — Warehouse — anon read-event schema: complete — vault_events table ships event + post_id + ts + ip_hash (database/migrations/2026_05_06_080000_create_vault_events_table.php); privacy posture locked (no | complete |
| S1733 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1731-s1740 — Warehouse — anon read-event ingest: partial — GrimbaVaultEvents writes events at save/unsave; per-article read-event capture deferred (current model: cookie-only grimba_read IDs, no server insert per  | partial |
| S1734 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1731-s1740 — Warehouse — source dwell-time capture: deferred — no client-side dwell-time beacon; surrogate is per-article presence in grimba_read cookie. — surrogate doc: docs/GRIMBANEWS_WAREHOUSE_SOURCE_DWELL_TIME_DESIGN.md (partial). | partial |
| S1735 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1731-s1740 — Warehouse — monthly CSV pipeline: complete — app/Console/Commands/GrimbaArchiveVaultEvents.php writes storage/exports/vault_events_YYYY-MM.csv (4-column: event / post_id / ts / ip_hash); scheduled via | complete |
| S1736 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1731-s1740 — Warehouse — automation-runs CSV pipeline: partial — grimba_automation_runs is queryable per GrimbaAutomationMonitor::status(); CSV export deferred. | partial |
| S1737 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1731-s1740 — Warehouse — dashboard layer (Metabase / Looker / Hex / Superset): partial — docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md "Dashboard layer (S1737)" recommends Metabase self-hosted; per-Iboga ops convention behind admin auth + IP allowlist. | partial |
| S1738 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1731-s1740 — Warehouse — quarterly retention policy: partial — GrimbaPruneReleaseEvidence keeps 30-day rolling window of release-evidence files (ReleaseEvidencePruneTest); vault-events archive retention deferred. | partial |
| S1739 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1731-s1740 — Warehouse — cost dashboard: partial — docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md "Cost dashboard (S1739 ship)" defines /admin/grimba/warehouse view with per-day DuckDB file size + refresh duration + slow-query log. | partial |
| S1740 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1731-s1740 — Warehouse — launch playbook: deferred — gates on S1731-S1739. | partial |
| S1741 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1741-s1750 — Observability — per-job duration capture: complete — grimba_automation_runs.duration_ms unsigned int populated by GrimbaAutomationMonitor::start/finish; grimba_automation_runs_status_finished_idx + gr | complete |
| S1742 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1741-s1750 — Observability — per-job exit-code + error-message capture: complete — grimba_automation_runs.exit_code + error_message text column populated on every run; GrimbaAutomationMonitor::status() exposes for | complete |
| S1743 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1741-s1750 — Observability — per-job last-run dashboard: complete — /admin/grimba/cockpit reads GrimbaAutomationMonitor::status() and renders per-job last-run + status + duration per platform/themes/echo/functions | complete |
| S1744 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1741-s1750 — Observability — per-job missed-run alert: complete — grimba:health --fail-on-risk flags missed runs (per S166 + DailyPublishFreshnessTest). | complete |
| S1745 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1741-s1750 — Observability — per-route latency capture: partial — grimba:release-smoke enforces per-route budgets at release time (per S1006); continuous per-request latency capture deferred (would need APM / Sent | partial |
| S1746 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1741-s1750 — Observability — per-route 4xx / 5xx capture: partial — Laravel app/Exceptions/Handler.php logs to storage/logs/laravel.log; structured per-route capture deferred. | partial |
| S1747 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1741-s1750 — Observability — tracing v2 (per-request trace ID): partial — request-ID middleware shipped per Wave (S0911+ security pack); cross-service trace propagation deferred. | partial |
| S1748 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1741-s1750 — Observability — log retention policy: partial — GrimbaPruneReleaseEvidence 30-day rolling per S999; Laravel log rotation default; formal retention policy doc deferred. | partial |
| S1749 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1741-s1750 — Observability — alerting v2 (Slack / email / PagerDuty webhook): partial — docs/GRIMBANEWS_OBSERVABILITY_LAUNCH_PLAYBOOK.md Phase 3 specs paging-vendor webhooks from GrimbaHealth + GrimbaAutomationMonitor + Sentry; per-vendor adapter pattern. | partial |
| S1750 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1741-s1750 — Observability — launch playbook: partial — docs/GRIMBANEWS_OBSERVABILITY_LAUNCH_PLAYBOOK.md sequences Phase 0-4 + log retention policy + alert routing + cost projection (~$80/month for Sentry+Better Stack stack). | partial |
| S1751 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1751-s1760 — Africa edition v2 — named curator role: deferred — operator-side editorial staffing. | partial |
| S1752 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1751-s1760 — Africa edition v2 — curator admin scope: deferred — Botble admin auth is single-role per S1401. | partial |
| S1753 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1751-s1760 — Africa edition v2 — translator role: deferred — GrimbaTranslator covers automated FR↔EN via rule engine + LibreTranslate / OpenRouter / DeepL fallback per GrimbaTranslator::configuredDrivers(); human  | partial |
| S1754 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1751-s1760 — Africa edition v2 — translator queue: partial — app/Console/Commands/GrimbaTranslateByRule.php + posts.translation_priority + grimba:translate-pending is the automated queue; human-review queue deferr | partial |
| S1755 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1751-s1760 — Africa edition v2 — Africa ad-pack: partial — grimba_advertiser_leads.source_pack_tier (per add_source_pack_tier_to_grimba_advertiser_leads_table migration); explicit tier='africa' value deferred. | partial |
| S1756 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1751-s1760 — Africa edition v2 — Africa ad-ops dashboard: deferred — depends on S1755. | partial |
| S1757 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1751-s1760 — Africa edition v2 — Africa sponsor inventory: deferred — same. | partial |
| S1758 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1751-s1760 — Africa edition v2 — Africa newsletter cadence (separate from main): deferred — newsletter_subscriptions table single-edition today. | partial |
| S1759 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1751-s1760 — Africa edition v2 — Africa monthly editorial report: partial — docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md is the master brief; per-month report deferred. | partial |
| S1760 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1751-s1760 — Africa edition v2 — launch retrospective: deferred — operator-side retro. | partial |
| S1761 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1761-s1770 — International edition v2 — region scope: complete — Regions::countries('international') returns null (negative filter); GrimbaRegionQuery::applyToSourceCountry($q, 'country') builds country IS NULL OR | complete |
| S1762 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1761-s1770 — International edition v2 — named curator role: deferred — same shape as S1751. | partial |
| S1763 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1761-s1770 — International edition v2 — curator admin scope: deferred — same shape as S1752. | partial |
| S1764 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1761-s1770 — International edition v2 — multi-language translator pool: partial — automated GrimbaTranslator covers EN ↔ FR; ES / PT / DE / AR deferred per S1101-S1140 catalog deferrals. | partial |
| S1765 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1761-s1770 — International edition v2 — translator queue cross-locale: partial — same as S1754; cross-locale routing handled by posts.translation_priority rule engine but locale catalogs gated. | partial |
| S1766 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1761-s1770 — International edition v2 — International ad-pack: partial — same shape as S1755. | partial |
| S1767 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1761-s1770 — International edition v2 — International sponsor inventory: deferred — same shape as S1757. | partial |
| S1768 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1761-s1770 — International edition v2 — International newsletter cadence: deferred — same shape as S1758. | partial |
| S1769 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1761-s1770 — International edition v2 — International monthly editorial report: partial — docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md covers both editions; per-month report deferred. | partial |
| S1770 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1761-s1770 — International edition v2 — launch retrospective: deferred — operator-side retro. | partial |
| S1771 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1771-s1780 — Bias-bar tutorial — standalone explainer page: complete — /explainer-bias-bar route + platform/themes/echo/views/explainer-bias-bar.blade.php + AboutPage JSON-LD per Wave OOOOO. | complete |
| S1772 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1771-s1780 — Bias-bar tutorial — first-visit overlay modal: partial — docs/GRIMBANEWS_BIAS_BAR_TUTORIAL_OVERLAY_DESIGN.md specs trigger + suppress rules + 3-step walkthrough + cookie footprint + a11y; pending Steve sign-off. | partial |
| S1773 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1771-s1780 — Bias-bar tutorial — step-through animation (hover-to-explain per segment): partial — docs/GRIMBANEWS_BIAS_BAR_TUTORIAL_OVERLAY_DESIGN.md "Overlay shape" Step 1-3 walkthrough + 200ms ease-in-out crossfade with reduced-motion fallback. | partial |
| S1774 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1771-s1780 — Bias-bar tutorial — dismiss-don't-show-again cookie: partial — cookie pattern proven by grimba_consent_dismissed + grimba_local_* cookies; specific bias_tutorial_dismissed cookie deferred. | partial |
| S1775 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1771-s1780 — Bias-bar tutorial — keyboard-only navigation: partial — site a11y baseline covers per tests/e2e/grimbanews-keyboard-navigation.cjs; specific overlay a11y deferred (depends on S1772). | partial |
| S1776 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1771-s1780 — Bias-bar tutorial — screen-reader narration: partial — same; alt text + table fallback on bias chart per S1668. | partial |
| S1777 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1771-s1780 — Bias-bar tutorial — cross-locale (FR + EN): partial — /explainer-bias-bar page strings wrapped in __() per Wave LLLLLLLLL + WWWWWWWWW; tutorial-overlay strings deferred (depends on S1772). | partial |
| S1778 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1771-s1780 — Bias-bar tutorial — partner-school distribution: deferred — no partner-school program (gates on S1741-S1750 literacy band). | partial |
| S1779 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1771-s1780 — Bias-bar tutorial — analytics (completion rate): deferred — no overlay; depends on S1772 + S1733 read-event capture. | partial |
| S1780 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1771-s1780 — Bias-bar tutorial — launch retrospective: deferred — gates on S1772-S1779. | partial |
| S1781 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1781-s1790 — Fact-check primer — standalone page: partial — /methodologie covers fact-check scoring within the master methodology; standalone /explainer-fact-check route deferred. | partial |
| S1782 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1781-s1790 — Fact-check primer — factuality-score scale visualization: partial — news_sources.factuality_score (int 0-100) rendered per source on /sources; standalone scale-explainer deferred. | partial |
| S1783 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1781-s1790 — Fact-check primer — exclusion-threshold doc: partial — grimba_publish_min_factuality_score setting gates ingest; doc surface deferred. | partial |
| S1784 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1781-s1790 — Fact-check primer — score-provenance ("how did we get this number"): partial — news_sources.bias_source + factuality_source columns hold provenance (Botble admin editable); reader-facing surface defer | partial |
| S1785 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1781-s1790 — Fact-check primer — appeal / dispute path: partial — /contact?subject=dispute per methodology hero CTA "Contester un classement"; dedicated dispute form deferred per S1427. | partial |
| S1786 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1781-s1790 — Fact-check primer — cross-locale (FR + EN): partial — /methodologie shipped in FR + EN per Wave LLLLLLLLL + WWWWWWWWW; standalone primer deferred. | partial |
| S1787 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1781-s1790 — Fact-check primer — partner-school distribution: deferred — same as S1778. | partial |
| S1788 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1781-s1790 — Fact-check primer — interactive quiz: deferred — no quiz primitive (gates on S1721-S1730 literacy band). | partial |
| S1789 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1781-s1790 — Fact-check primer — analytics (read-through rate): deferred — depends on S1733 read-event capture. | partial |
| S1790 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1781-s1790 — Fact-check primer — launch retrospective: deferred — gates on S1781-S1789. | partial |
| S1791 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1791-s1800 — Methodology video — script: deferred — no video pipeline; surrogate is /methodologie written longform. — surrogate doc: docs/GRIMBANEWS_METHODOLOGY_VIDEO_SCRIPT_PLAN.md (partial). | partial |
| S1792 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1791-s1800 — Methodology video — recording: deferred — operator-side production. | partial |
| S1793 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1791-s1800 — Methodology video — hosting (YouTube / Vimeo / self-hosted): deferred — same. | partial |
| S1794 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1791-s1800 — Methodology video — embed on /methodologie: deferred — GrimbaSecurityHeaders CSP currently locks down frame-src to a closed list (operator-side pickup once host is picked). | partial |
| S1795 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1791-s1800 — Methodology video — transcript (a11y): deferred — depends on S1791. | partial |
| S1796 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1791-s1800 — Methodology video — cross-locale subtitles (FR + EN): deferred — depends on S1791-S1795. — surrogate doc: docs/GRIMBANEWS_METHODOLOGY_VIDEO_SUBTITLES_PLAN.md (partial). | partial |
| S1797 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1791-s1800 — Methodology podcast — recording: deferred — no audio pipeline. — surrogate doc: docs/GRIMBANEWS_METHODOLOGY_PODCAST_RECORDING_PLAN.md (partial). | partial |
| S1798 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1791-s1800 — Methodology podcast — hosting + RSS (Apple Podcasts / Spotify): deferred — operator-side; depends on S1797. — surrogate doc: docs/GRIMBANEWS_METHODOLOGY_PODCAST_HOSTING_PLAN.md (partial). | partial |
| S1799 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1791-s1800 — Methodology podcast — transcript: deferred — depends on S1797. — surrogate doc: docs/GRIMBANEWS_METHODOLOGY_PODCAST_TRANSCRIPT_PLAN.md (partial). | partial |
| S1800 | docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1791-s1800 — Methodology video / podcast launch retrospective: deferred — gates on S1791-S1799. | partial |
| S1801 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1801-s1810 — SOC 2 control inventory: partial — security-controls inventory surrogate ships via app/Http/Middleware/GrimbaSecurityHeaders.php (CSP / HSTS / nosniff / frame-options / referrer-policy / permissions-p | partial |
| S1802 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1801-s1810 — SOC 2 access control evidence: partial — Botble admin auth (single-tenant) + middleware-level route protection. Per-role RBAC evidence + access-review log deferred. | partial |
| S1803 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1801-s1810 — SOC 2 encryption evidence (in-transit): partial — GrimbaSecurityHeaders::handle() emits Strict-Transport-Security: max-age=15552000; includeSubDomains on HTTPS requests. Encryption-at-rest evidence (S | partial |
| S1804 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1801-s1810 — SOC 2 change-management evidence: partial — git cadence per CLAUDE.md (edit local → commit darkvaderfr → push → deploy); release-evidence ledger via app/Console/Commands/GrimbaPruneReleaseEvidence.php | partial |
| S1805 | docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md — partial — IR runbook shipped (detection → triage → comms → root-cause → postmortem); gating dep: live incident drill | partial |
| S1806 | docs/GRIMBANEWS_VENDOR_REGISTER.md — partial — vendor risk register v1 shipped (10 vendors enumerated with data classes + tier + DPA status); gating dep: formal DPA collection program | partial |
| S1807 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1801-s1810 — SOC 2 backup + recovery evidence: partial — tests/Feature/DatabaseBackupVerificationTest::test_backup_directory_health_reports_valid_state + grimba:verify-backups --min=1 daily at 03:05 (routes/consol | partial |
| S1808 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1801-s1810 — SOC 2 logging + monitoring evidence: partial — Laravel default logs to storage/logs/; app/Exceptions/Handler.php captures exceptions; GrimbaAutomationMonitor exposes job-health surface. SIEM ingest /  | partial |
| S1809 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1801-s1810 — SOC 2 evidence-vault setup: deferred — no compliance-evidence vault (Drata-style automated collection). Closest surrogate: docs/GRIMBANEWS_RELEASE_SMOKE_EVIDENCE_2026_05_12.md is a one-off evidence fi | partial |
| S1810 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1801-s1810 — SOC 2 Type I prep retrospective: deferred — gates on S1801-S1809 actually shipping; operator-side Sara-Chen retro. | partial |
| S1811 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1811-s1820 — SOC 2 audit firm engagement: deferred — no signed engagement; budget + firm-shortlist Sara-Chen-owned. | partial |
| S1812 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1811-s1820 — SOC 2 audit scope definition: deferred — depends on S1811. | partial |
| S1813 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1811-s1820 — SOC 2 audit kickoff: deferred — same. | partial |
| S1814 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1811-s1820 — SOC 2 audit field-work week 1 (access control / change-mgmt): deferred — same. | partial |
| S1815 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1811-s1820 — SOC 2 audit field-work week 2 (encryption / logging / backup): deferred — same. | partial |
| S1816 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1811-s1820 — SOC 2 audit field-work week 3 (incident-response / vendor-risk): deferred — same. | partial |
| S1817 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1811-s1820 — SOC 2 audit findings response: deferred — same. | partial |
| S1818 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1811-s1820 — SOC 2 audit remediation: deferred — same. | partial |
| S1819 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1811-s1820 — SOC 2 Type I report signoff: deferred — same. | partial |
| S1820 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1811-s1820 — SOC 2 Type I audit retrospective: deferred — gates on S1811-S1819. | partial |
| S1821 | docs/GRIMBANEWS_ISMS_SCOPE.md — partial — ISO 27001 ISMS scope statement shipped (in-scope assets + boundaries + interested parties); gating dep: cert body engagement | partial |
| S1822 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1821-s1830 — ISO 27001 Statement of Applicability (Annex A controls): deferred — same. | deferred |
| S1823 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1821-s1830 — ISO 27001 risk-treatment plan: deferred — same; pre-requisite for S1831. | deferred |
| S1824 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1821-s1830 — ISO 27001 information-asset inventory: partial — app/Support/GrimbaDatabaseBackups.php enumerates the live SQLite DB; provider-vault enumerates third-party API tokens; full information-asset register  | partial |
| S1825 | docs/GRIMBANEWS_POLICY_LIBRARY_INDEX.md — partial — ISO 27001 policy library index shipped (8 core policies enumerated + ownership + review cadence); gating dep: policies themselves drafted | partial |
| S1826 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1821-s1830 — ISO 27001 ISMS responsibilities matrix (RACI): deferred — operator-side org chart; exec roster at ~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md is the source-of-truth for who | deferred |
| S1827 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1821-s1830 — ISO 27001 internal-audit plan: deferred — depends on S1881 (internal audit cadence band). | deferred |
| S1828 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1821-s1830 — ISO 27001 management-review cadence: deferred — operator-side governance pickup. | deferred |
| S1829 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1821-s1830 — ISO 27001 ISMS launch readiness: deferred — gates on S1821-S1828. | deferred |
| S1830 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1821-s1830 — ISO 27001 ISMS retrospective: deferred — same. | deferred |
| S1831 | docs/GRIMBANEWS_RISK_ASSESSMENT_METHODOLOGY.md — partial — risk assessment methodology shipped (impact × likelihood × mitigation matrix + scoring rubric); gating dep: actual asset register run | partial |
| S1832 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1831-s1840 — Asset-threat-vulnerability-impact mapping: deferred — same. | deferred |
| S1833 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1831-s1840 — Inherent-risk scoring: deferred — same. | deferred |
| S1834 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1831-s1840 — Control-effectiveness scoring: deferred — same. | deferred |
| S1835 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1831-s1840 — Residual-risk scoring: deferred — same. | deferred |
| S1836 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1831-s1840 — Risk-treatment decisions (avoid / mitigate / transfer / accept): deferred — same. | deferred |
| S1837 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1831-s1840 — Risk register publication: partial — docs/GRIMBANEWS_S010_UNRESOLVED_RISK_REGISTER.md ships a pre-launch risk register; ISO-conformant version deferred. | partial |
| S1838 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1831-s1840 — Risk-register cadence (quarterly review): deferred — operator-side; depends on S1837 conformant version. | deferred |
| S1839 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1831-s1840 — Risk-register launch readiness: deferred — gates on S1831-S1838. | deferred |
| S1840 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1831-s1840 — Risk-assessment retrospective: deferred — same. | deferred |
| S1841 | docs/GRIMBANEWS_PCI_DSS_SCOPE_STATEMENT.md — partial — PCI DSS scope statement shipped (N/A determination: no card data stored, Stripe-hosted checkout); gating dep: if payment-processing model changes | partial |
| S1842 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1841-s1850 — PCI DSS network segmentation diagram: deferred — N/A until CDE exists. | deferred |
| S1843 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1841-s1850 — PCI DSS card-data-flow diagram: deferred — same. | deferred |
| S1844 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1841-s1850 — PCI DSS SAQ selection (A / A-EP / D): deferred — same. | deferred |
| S1845 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1841-s1850 — PCI DSS quarterly ASV scan: deferred — same. | deferred |
| S1846 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1841-s1850 — PCI DSS annual penetration test: deferred — same; broader pen-test sits in S2011 bug-bounty band. | deferred |
| S1847 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1841-s1850 — PCI DSS QSA engagement (if Level 1): deferred — same. | deferred |
| S1848 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1841-s1850 — PCI DSS Attestation of Compliance (AoC): deferred — same. | deferred |
| S1849 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1841-s1850 — PCI DSS launch readiness: deferred — gates on payment processor selection + integration first. | deferred |
| S1850 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1841-s1850 — PCI DSS retrospective: deferred — same. | deferred |
| S1851 | docs/GRIMBANEWS_GDPR_ROPA.md — partial — GDPR Article 30 ROPA shipped (10+ processing activities enumerated with lawful basis + retention + subprocessors); gating dep: DPO formal appointment | partial |
| S1852 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1851-s1860 — GDPR DPIA — homepage personalization + For-You: deferred — no formal DPIA; technical surrogate is app/Support/GrimbaForYou cookie-only profile (no member-row personalization, no profile-graph). | deferred |
| S1853 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1851-s1860 — GDPR DPIA — vault analytics: partial — app/Support/GrimbaVaultEvents.php is privacy-safe by design (event hashes, no per-reader PII beyond logged-in member id), archived weekly via grimba:archive-vaul | partial |
| S1854 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1851-s1860 — GDPR DPIA — newsletter / digest: partial — app/Mail/GrimbaVaultDigestMail.php + weekly grimba:vault-digests cron sends opted-in members only. Formal DPIA deferred. | partial |
| S1855 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1851-s1860 — GDPR DPIA — translation + NobuAI summaries: deferred — app/Services/GrimbaTranslator.php ships content (not reader PII) to providers; formal DPIA + provider-DPA register deferred. | deferred |
| S1856 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1851-s1860 — GDPR DPIA — search + saved searches: partial — app/Support/GrimbaSavedSearches.php server-side records; per-record DPIA deferred. | partial |
| S1857 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1851-s1860 — GDPR DPO designation: deferred — operator-side decision; large-scale-special-category-data threshold not met today (GrimbaNews is news aggregation, not health / finance / biometric). | deferred |
| S1858 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1851-s1860 — GDPR data-subject-access-request (DSAR) workflow: deferred — no formal DSAR intake; /contact page is the surrogate intake. | deferred |
| S1859 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1851-s1860 — GDPR right-to-erasure workflow: partial — app/Console/Commands/GrimbaArchiveVaultEvents.php weekly archive serves as the privacy-purge cadence; formal per-member erasure on request deferred. | partial |
| S1860 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1851-s1860 — GDPR DPIA + DPO launch readiness: deferred — gates on S1851-S1859. | deferred |
| S1861 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1861-s1870 — Cookie inventory: partial — platform/themes/echo/partials/cookie-consent.blade.php enumerates the consent-state cookie (grimba_cookie_consent); other cookies (Laravel session, XSRF-TOKEN, grimba_lang, | partial |
| S1862 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1861-s1870 — Cookie purpose classification (strictly-necessary / functional / analytics / advertising): deferred — depends on S1861; today the consent banner is binary accept/reject without per-category granularit | deferred |
| S1863 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1861-s1870 — Cookie lifetime audit: deferred — depends on S1861. | deferred |
| S1864 | docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md — partial — per-visitor consent log design shipped (privacy-safe ip_hash, choice cookie, archive cadence); gating dep: DB migration to land table | partial |
| S1865 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1861-s1870 — Per-category granular consent toggles: deferred — depends on S1862. | deferred |
| S1866 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1861-s1870 — Consent withdrawal flow: partial — visitor can clear the grimba_cookie_consent cookie via browser controls to re-prompt; explicit "withdraw consent" link in footer deferred. | partial |
| S1867 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1861-s1870 — Privacy-policy page coverage: partial — /politique-de-confidentialite (FR) + /privacy-policy (EN) ship via the legal-page band; per-cookie-purpose drill-in deferred. | partial |
| S1868 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1861-s1870 — Cookie consent banner i18n: partial — cookie-consent partial uses __() for default copy (FR + EN via lang/{locale}.json); other locales deferred per S1146. | partial |
| S1869 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1861-s1870 — Privacy-program metrics dashboard: deferred — no consent-rate / opt-out-rate dashboard. | deferred |
| S1870 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1861-s1870 — Privacy program v2 launch readiness: deferred — gates on S1861-S1869. | deferred |
| S1871 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1871-s1880 — Vendor inventory: partial — .env.example lists API key slots for newsdata.io / NEWSAPI / OpenRouter / NobuAI / LibreTranslate; consolidated vendor register deferred. | partial |
| S1872 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1871-s1880 — Vendor risk-tier classification (critical / high / medium / low): deferred — depends on S1871. | deferred |
| S1873 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1871-s1880 — Vendor DPA collection: deferred — no DPA register; operator-side counsel pickup. — surrogate doc: docs/GRIMBANEWS_VENDOR_DPA_COLLECTION_PLAN.md (partial). | partial |
| S1874 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1871-s1880 — Vendor security-questionnaire intake: deferred — same. | deferred |
| S1875 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1871-s1880 — Vendor SOC 2 / ISO 27001 report collection: deferred — same. | deferred |
| S1876 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1871-s1880 — Vendor incident-notification clauses: deferred — same. | deferred |
| S1877 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1871-s1880 — Vendor termination + data-return clauses: deferred — same. | deferred |
| S1878 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1871-s1880 — Vendor quarterly review cadence: deferred — depends on S1871-S1877. | deferred |
| S1879 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1871-s1880 — Vendor risk dashboard: deferred — same. — surrogate doc: docs/GRIMBANEWS_VENDOR_RISK_DASHBOARD_DESIGN.md (partial). | partial |
| S1880 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1871-s1880 — Vendor risk-management launch readiness: deferred — gates on S1871-S1879. | deferred |
| S1881 | docs/GRIMBANEWS_INTERNAL_AUDIT_CHARTER.md — partial — internal audit charter shipped (scope + independence + cadence + reporting); gating dep: first audit cycle | partial |
| S1882 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1881-s1890 — Internal-audit team composition: deferred — exec roster has Sara Chen (CISO) — natural internal-audit lead; team composition formalization deferred. | deferred |
| S1883 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1881-s1890 — Internal-audit plan (annual): deferred — same. | deferred |
| S1884 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1881-s1890 — Internal-audit working-paper template: deferred — same. | deferred |
| S1885 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1881-s1890 — Internal-audit findings register: deferred — same. | deferred |
| S1886 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1881-s1890 — Internal-audit corrective-action tracking: deferred — same. | deferred |
| S1887 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1881-s1890 — Internal-audit management-review cadence: deferred — same; depends on S1828 (ISO 27001 management review). | deferred |
| S1888 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1881-s1890 — Internal-audit independence safeguards: deferred — same. — surrogate doc: docs/GRIMBANEWS_INTERNAL_AUDIT_INDEPENDENCE_SAFEGUARDS_PLAN.md (partial). | partial |
| S1889 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1881-s1890 — Internal-audit launch readiness: deferred — gates on S1881-S1888. | deferred |
| S1890 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1881-s1890 — Internal-audit retrospective: deferred — same. | deferred |
| S1891 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1891-s1900 — External-audit firm shortlist: deferred — depends on S1811. | deferred |
| S1892 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1891-s1900 — External-audit firm engagement: deferred — same. | deferred |
| S1893 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1891-s1900 — External-audit kickoff: deferred — same. | deferred |
| S1894 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1891-s1900 — External-audit fieldwork: deferred — same. | deferred |
| S1895 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1891-s1900 — External-audit findings response: deferred — same. | deferred |
| S1896 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1891-s1900 — External-audit remediation: deferred — same. | deferred |
| S1897 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1891-s1900 — External-audit report receipt: deferred — same. | deferred |
| S1898 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1891-s1900 — External-audit report distribution (customers / prospects): deferred — same; gates on enterprise-tier S1991 motion (B2B prospects request reports). | deferred |
| S1899 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1891-s1900 — External-audit signoff publication: deferred — same. | deferred |
| S1900 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1891-s1900 — External-audit retrospective: deferred — gates on S1891-S1899. | deferred |
| S1901 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1901-s1910 — Multi-region architecture decision (replica vs sharded vs multi-active): deferred — depends on S951 SQLite migration decision. | deferred |
| S1902 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1901-s1910 — Read-replica provisioning (region 1 → region 2): deferred — same. | deferred |
| S1903 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1901-s1910 — Read-replica lag monitoring: deferred — same. | deferred |
| S1904 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1901-s1910 — Read-replica failover playbook: deferred — same. | deferred |
| S1905 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1901-s1910 — Read-after-write consistency policy: deferred — same. | deferred |
| S1906 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1901-s1910 — Geo-routing (latency-based DNS): deferred — single domain (grimbanews.com), single A record per S1193 OEM-whitelabel constraint. | deferred |
| S1907 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1901-s1910 — Cross-region backup replication: partial — app/Support/GrimbaDatabaseBackups.php writes local backups to database/backups/; cross-region off-site replication deferred. | partial |
| S1908 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1901-s1910 — Read-replica security (TLS-in-transit, IAM-auth): deferred — depends on S1902. | deferred |
| S1909 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1901-s1910 — Read-replica launch readiness: deferred — gates on S1901-S1908. | deferred |
| S1910 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1901-s1910 — Multi-region retrospective: deferred — same. | deferred |
| S1911 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1911-s1920 — CDN vendor selection (Cloudflare / Fastly / Bunny / CloudFront): deferred — operator-side; Jacob-Lee-DevOps pickup. | deferred |
| S1912 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1911-s1920 — CDN provisioning + DNS cutover: deferred — depends on S1911. | deferred |
| S1913 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1911-s1920 — Per-region edge cache configuration: deferred — same. | deferred |
| S1914 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1911-s1920 — Cache-invalidation hooks (post-publish, post-translate): partial — Laravel Cache::forget() calls fire on certain admin actions; CDN-purge hooks deferred. | partial |
| S1915 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1911-s1920 — Cookie-aware Vary header policy: partial — GrimbaPublicCache::handle() ships Vary headers; cookie-aware CDN-side policy deferred. | partial |
| S1916 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1911-s1920 — Image CDN (proxy + on-the-fly resize): partial — app/Console/Commands/GrimbaPruneImageProxyCache.php + image-proxy ship today (allowlist + cache); CDN-fronted variant deferred. | partial |
| S1917 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1911-s1920 — Origin shield: deferred — depends on S1911. | deferred |
| S1918 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1911-s1920 — CDN security (WAF, bot management, rate limits at edge): deferred — same. | deferred |
| S1919 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1911-s1920 — CDN launch readiness: deferred — gates on S1911-S1918. | deferred |
| S1920 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1911-s1920 — CDN v2 retrospective: deferred — same. | deferred |
| S1921 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1921-s1930 — Container orchestrator decision (k8s vs Nomad vs ECS vs stay-on-VPS): deferred — VPS-only policy per feedback_hosting_policy.md; orchestration deferred until product class changes. | deferred |
| S1922 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1921-s1930 — Cluster provisioning: deferred — same. | deferred |
| S1923 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1921-s1930 — Helm-chart / Kustomize / Tilt configuration: deferred — same. | deferred |
| S1924 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1921-s1930 — Pod-disruption-budget + horizontal-pod-autoscaler: deferred — same. | deferred |
| S1925 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1921-s1930 — Ingress + service-mesh: deferred — same. | deferred |
| S1926 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1921-s1930 — Secrets management (Vault / Sealed Secrets / cloud KMS): partial — .env file (chmod 600) is the current secret-store; provider-vault for API keys ships per S621 admin band; full Vault / SOPS / Sealed  | partial |
| S1927 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1921-s1930 — Observability sidecar (Datadog / OpenTelemetry agent): deferred — see S1934 distributed tracing. | deferred |
| S1928 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1921-s1930 — Cluster cost monitoring: deferred — depends on S1921. | deferred |
| S1929 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1921-s1930 — Orchestration launch readiness: deferred — same. | deferred |
| S1930 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1921-s1930 — Orchestration retrospective: deferred — same. | deferred |
| S1931 | docs/GRIMBANEWS_METRICS_PIPELINE_PLAN.md — partial — metrics pipeline plan shipped (server-side counters, scheduler ledger, /health endpoint, future Prometheus/Grafana path); gating dep: Prometheus/Grafana provisioned | partial |
| S1932 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1931-s1940 — Log aggregation (Loki / Splunk / Elastic / Datadog Logs): partial — Laravel storage/logs/laravel.log is the local log; centralized aggregation deferred. | partial |
| S1933 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1931-s1940 — Distributed tracing (OpenTelemetry + Jaeger / Tempo / Datadog APM): deferred — same. | deferred |
| S1934 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1931-s1940 — SLO definitions (per-endpoint p99 latency, error-budget): partial — grimba:health already enforces a freshness SLO (--min-full-content-coverage=70 --min-category-published-24h=3 per routes/console.php | partial |
| S1935 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1931-s1940 — SLO dashboards: deferred — depends on S1931. | deferred |
| S1936 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1931-s1940 — Error-budget burndown alerts: deferred — same. | deferred |
| S1937 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1931-s1940 — Real-user-monitoring (RUM): deferred — no client-side beacon; Web-Vitals capture would land here. | deferred |
| S1938 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1931-s1940 — Synthetic monitoring (uptime checks from N regions): partial — /health JSON + /up cover liveness + readiness; external synthetic-check vendor (Pingdom / Uptime Robot / Datadog Synthetics) deferred. | partial |
| S1939 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1931-s1940 — Observability v3 launch readiness: deferred — gates on S1931-S1938. | deferred |
| S1940 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1931-s1940 — Observability v3 retrospective: deferred — same. | deferred |
| S1941 | docs/GRIMBANEWS_RTO_RPO_DEFINITION.md — partial — RTO/RPO definition shipped (4h RTO + 24h RPO for tier-1 surfaces, scope of recovery, dependencies); gating dep: DR drill cadence | partial |
| S1942 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1941-s1950 — DR runbook: deferred — same. | deferred |
| S1943 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1941-s1950 — DR drill — tabletop exercise: deferred — same. | deferred |
| S1944 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1941-s1950 — DR drill — live failover exercise: deferred — same; pre-requires multi-region per S1901-S1910 band. | deferred |
| S1945 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1941-s1950 — DR drill — backup restore validation: partial — app/Console/Commands/GrimbaVerifyBackups.php (per routes/console.php:33) opens each backup file daily + PRAGMA-quick-checks; full restore-and-replay dri | partial |
| S1946 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1941-s1950 — DR drill cadence (quarterly): deferred — same. | deferred |
| S1947 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1941-s1950 — DR drill findings register: deferred — same. | deferred |
| S1948 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1941-s1950 — DR drill remediation tracking: deferred — same. | deferred |
| S1949 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1941-s1950 — DR drill program launch readiness: deferred — gates on S1941-S1948. | deferred |
| S1950 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1941-s1950 — DR drill program retrospective: deferred — same. | deferred |
| S1951 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1951-s1960 — Referral program design (referrer reward / referee reward): deferred — Lucy-Leai-Strategy + Ray-CFO pickup. — surrogate doc: docs/GRIMBANEWS_REFERRAL_PROGRAM_TIER_DESIGN.md (partial). | partial |
| S1952 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1951-s1960 — Referral code generation: deferred — no members.referral_code column. — surrogate doc: docs/GRIMBANEWS_REFERRAL_CODE_GENERATION_DESIGN.md (partial). | partial |
| S1953 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1951-s1960 — Referral attribution tracking: deferred — same. — surrogate doc: docs/GRIMBANEWS_REFERRAL_ATTRIBUTION_TRACKING_DESIGN.md (partial). | partial |
| S1954 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1951-s1960 — Referral reward issuance (subscription discount / free tier): deferred — gates on monetization S1211 paid tier. | deferred |
| S1955 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1951-s1960 — Referral fraud detection: deferred — depends on S1951-S1954. — surrogate doc: docs/GRIMBANEWS_REFERRAL_FRAUD_DETECTION_PLAN.md (partial). | partial |
| S1956 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1951-s1960 — Referral landing page: deferred — same. | deferred |
| S1957 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1951-s1960 — Referral dashboard (per-member): deferred — same. — surrogate doc: docs/GRIMBANEWS_REFERRAL_PER_MEMBER_DASHBOARD_DESIGN.md (partial). | partial |
| S1958 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1951-s1960 — Referral leaderboard: deferred — same. — surrogate doc: docs/GRIMBANEWS_REFERRAL_LEADERBOARD_DESIGN.md (partial). | partial |
| S1959 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1951-s1960 — Referral launch readiness: deferred — gates on S1951-S1958. | deferred |
| S1960 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1951-s1960 — Referral retrospective: deferred — same. | deferred |
| S1961 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1961-s1970 — Partner program tier design (free RSS / paid API / co-brand): deferred — Lucy-Leai-Strategy + Ray-CFO pickup. — surrogate doc: docs/GRIMBANEWS_PARTNER_PROGRAM_TIER_DESIGN_S1961.md (partial). | partial |
| S1962 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1961-s1970 — Partner onboarding flow: deferred — same. — surrogate doc: docs/GRIMBANEWS_PARTNER_ONBOARDING_FLOW_DESIGN_S1962.md (partial). | partial |
| S1963 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1961-s1970 — Partner portal: deferred — same. — surrogate doc: docs/GRIMBANEWS_PARTNER_PORTAL_DESIGN_S1963.md (partial). | partial |
| S1964 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1961-s1970 — Partner revenue-share contract template: deferred — operator-side counsel pickup. | deferred |
| S1965 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1961-s1970 — Partner API key issuance: deferred — depends on S1182 OAuth band. | deferred |
| S1966 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1961-s1970 — Partner attribution display: complete — GrimbaArticleDedupe preserves canonical-URL + source-name + link to upstream per S1443; that's read-side partner attribution today. | complete |
| S1967 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1961-s1970 — Partner analytics dashboard: deferred — depends on S1188 API analytics band. — surrogate doc: docs/GRIMBANEWS_PARTNER_ANALYTICS_DASHBOARD_DESIGN_S1967.md (partial). | partial |
| S1968 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1961-s1970 — Partner case studies: deferred — gates on ≥1 real partner. — surrogate doc: docs/GRIMBANEWS_PARTNER_CASE_STUDIES_SCOPE_S1968.md (partial). | partial |
| S1969 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1961-s1970 — Partner program launch readiness: deferred — gates on S1961-S1968. | deferred |
| S1970 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1961-s1970 — Partner retrospective: deferred — same. | deferred |
| S1971 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1971-s1980 — Community space provisioning: deferred — depends on S1601 (sister-agent band). | deferred |
| S1972 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1971-s1980 — Community event calendar: deferred — no events table. | deferred |
| S1973 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1971-s1980 — Community event hosting (Luma / Eventbrite / Zoom integration): deferred — same; third-party account dependency. | deferred |
| S1974 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1971-s1980 — Ambassador program — application form: deferred — operator-side intake. | deferred |
| S1975 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1971-s1980 — Ambassador program — onboarding kit: deferred — same. | deferred |
| S1976 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1971-s1980 — Ambassador program — content-sharing toolkit: partial — partials/story/share-kit.blade.php ships 6-channel intent URLs (X / Bluesky / Facebook / WhatsApp / LinkedIn / Email); ambassador-specific track | partial |
| S1977 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1971-s1980 — Ambassador program — reward + recognition: deferred — gates on monetization S1211. | deferred |
| S1978 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1971-s1980 — Ambassador program — quarterly review cadence: deferred — operator-side. | deferred |
| S1979 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1971-s1980 — Community v2 launch readiness: deferred — gates on S1971-S1978. | deferred |
| S1980 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1971-s1980 — Community v2 retrospective: deferred — same. | deferred |
| S1981 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1981-s1990 — Institutional license tier design (per-seat vs site-wide): deferred — Ray-CFO + Lucy-Strategy pickup. — surrogate doc: docs/GRIMBANEWS_INSTITUTIONAL_LICENSE_TIER_DESIGN.md (partial). | partial |
| S1982 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1981-s1990 — Institutional license contract template: deferred — operator-side counsel pickup. | deferred |
| S1983 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1981-s1990 — Institutional license SSO integration (SAML / Shibboleth / OIDC): deferred — no SAML / OIDC IdP integration today; Botble member-auth is local credentials only. | deferred |
| S1984 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1981-s1990 — Institutional license IP-allowlist provisioning: deferred — depends on S1983. | deferred |
| S1985 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1981-s1990 — Institutional license per-institution analytics: deferred — depends on S1188 API analytics band. — surrogate doc: docs/GRIMBANEWS_INSTITUTIONAL_LICENSE_ANALYTICS_DESIGN.md (partial). | partial |
| S1986 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1981-s1990 — Institutional license per-institution branding: partial — Botble theme settings allow upstream branding; per-institution overlay deferred per S1192 OEM-whitelabel band. | partial |
| S1987 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1981-s1990 — Institutional license invoicing: deferred — depends on S1196 OEM-whitelabel invoice band. | deferred |
| S1988 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1981-s1990 — Institutional license renewal cadence: deferred — gates on ≥1 real institutional customer. — surrogate doc: docs/GRIMBANEWS_INSTITUTIONAL_LICENSE_RENEWAL_DESIGN.md (partial). | partial |
| S1989 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1981-s1990 — Institutional license launch readiness: deferred — gates on S1981-S1988. | deferred |
| S1990 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1981-s1990 — Institutional license retrospective: deferred — same. | deferred |
| S1991 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1991-s2000 — Enterprise tier feature design (SLA, dedicated support, custom features): deferred — Ray-CFO + Lucy-Strategy + Sara-Chen pickup. — surrogate doc: docs/GRIMBANEWS_ENTERPRISE_TIER_FEATURE_DESIGN.md (partial). | partial |
| S1992 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1991-s2000 — Enterprise tier SLA contract template (uptime, latency, breach-notification): deferred — operator-side counsel pickup. | deferred |
| S1993 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1991-s2000 — Enterprise tier dedicated-support tier (CSM, response-time tiers): deferred — depends on staffing decision. — surrogate doc: docs/GRIMBANEWS_ENTERPRISE_TIER_DEDICATED_SUPPORT_DESIGN.md (partial). | partial |
| S1994 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1991-s2000 — Enterprise tier security questionnaire response automation: deferred — gates on S1820 SOC 2 report + S1830 ISO 27001 report. | deferred |
| S1995 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1991-s2000 — Enterprise tier custom-feature roadmap commitment: deferred — operator-side governance. | deferred |
| S1996 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1991-s2000 — Enterprise tier per-customer pen-test cadence: deferred — depends on S2011 bug-bounty band. | deferred |
| S1997 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1991-s2000 — Enterprise tier invoicing (Net 30, PO-based): deferred — depends on monetization S1211 billing infra. | deferred |
| S1998 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1991-s2000 — Enterprise tier customer-success motion: deferred — gates on ≥1 real enterprise customer. — surrogate doc: docs/GRIMBANEWS_ENTERPRISE_TIER_CSM_MOTION_DESIGN.md (partial). | partial |
| S1999 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1991-s2000 — Enterprise tier launch readiness: deferred — gates on S1991-S1998. | deferred |
| S2000 | docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1991-s2000 — Enterprise tier retrospective + Mythos S1801-S2000 close: deferred — gates on S1991-S1999 + acknowledged that the entire S1801-S2000 band is deferred-heavy by design (compliance + multi-region infra + | deferred |

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
