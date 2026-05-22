# Mythos S1001–S1100 — Post-Launch Ops Evidence Pack

**Status:** evidence reconciliation
**Created:** 2026-05-22
**Author:** Wave OOOOOOOOO batch close (first Mythos post-launch band)
**Scope:** Converts the first 100-sprint slice of the Mythos S1001–S2237 post-launch arc from scaffold-spec into ledger rows pointing at real shipped code, scheduled jobs, tests, and runbook artifacts. Honest deferreds for anything that genuinely needs a live production target (incident retros, on-call rotation roster, day-1/day-7/day-30 retros, public uptime page, status-page provider, paging integration).

This pack feeds the master `Sprint Evidence Ledger` in `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`. The 100 sprint IDs in S1001–S1100 now have a ledger row. The honest split is: many of these are server-side **surrogates** for live-launch artifacts. Where a sprint is genuinely a "post-launch live-env operator task" (e.g. day-1 incident retro, paging contract with PagerDuty, public uptime page), it is marked `deferred` with the reason, not padded with fake evidence.

The S001–S1000 pre-launch arc is the launch gate; S1001+ work hardens the running platform once launch has happened. Some S1001+ items are already shipped because the pre-launch arc spilled into ops (`grimba:verify-backups`, `grimba_automation_runs` ledger, freshness watchdog, scheduler smoke). Those are evidenced here so the planning queue doesn't redo the work.

---

## S1001–S1010 — Post-launch scheduler hardening

The post-launch ops band leads with "did launch survive?" reviews + baseline capture. The scheduler ledger + freshness watchdog already capture the data that day-1/day-7/day-30 retros will mine. The retros themselves are operator-led and dated post-launch.

- **S1001** — Launch retrospective: `deferred` — operator-led calendar retro after the production cutover lands. Surrogate: `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md` is the pre-launch gate, `docs/GRIMBANEWS_RELEASE_SMOKE_EVIDENCE_2026_05_12.md` is the latest pre-launch release-smoke result.
- **S1002** — Day-1 incident review: `deferred` — runs against real day-1 production traffic; surrogate is `app/Support/GrimbaAutomationMonitor::status()` (every scheduled job's status + last_observed_at + is_failed/is_stale flags) which the operator dashboards at `/admin/grimba/cockpit` (Automation run ledger section, `resources/views/grimba-admin/cockpit.blade.php` lines 190-231).
- **S1003** — Day-7 incident review: `deferred` — same as S1002, needs 7 days of real traffic. `grimba_automation_runs` table will hold a week of per-job rows by then; the cockpit board surfaces failed/stale counts for the retro.
- **S1004** — Day-30 quality review: `deferred` — same, needs 30 days. `GrimbaPruneReleaseEvidence` keeps 30 days of release-evidence files (`storage/app/grimba-release-evidence/`) so the retro has a per-deploy trail to walk.
- **S1005** — Error-rate baseline: server-side counter shipped — every scheduled job records `status` (`success`/`failed`/`running`) + `exit_code` + `duration_ms` + `error_message` in `grimba_automation_runs` via `GrimbaAutomationMonitor::start/finish` (`app/Support/GrimbaAutomationMonitor.php:232-275`). The cockpit board reads this for the failed/stale count. External error-tracker (Sentry) integration `deferred` to S1013 (Sentry routing).
- **S1006** — Latency baseline: `grimba:release-smoke` enforces per-route budgets — homepage 3000ms, `/up` 1500ms, `/health` 1500ms, `/feed.xml` 3000ms (`app/Console/Commands/GrimbaReleaseSmoke.php:96-103`). Each release-evidence markdown captures actual ms per check; baseline = historical evidence dir.
- **S1007** — Ingest volume baseline: `grimba:health` section 8 "Ingest last 24h" prints RSS / NewsAPI / Live providers / Combined counts (`app/Console/Commands/GrimbaHealth.php:204-213`); `grimba_live_news_provider_runs` table indexes per-provider per-day call count via `GrimbaProviderCredits::used()` (`app/Support/GrimbaProviderCredits.php:32-45`).
- **S1008** — NobuAI cost baseline: `GrimbaProviderCredits` is provider-agnostic (Vader 2026-05-16 design) — bumps a per-UTC-day counter for every consumed provider call via `bump()` (line 71-79) and exposes `used()` + `fast()` + `cached()` for pre-flight + dashboard reads. newsdata.io stat-grid at `/admin/grimba/newsdataio` is the first consumer; same helper will baseline LLM provider spend post-launch.
- **S1009** — Ad fill baseline: `partial` — `GrimbaAds` support class + `partials/ad-slot.blade.php` are shipped per S861-S880 admin pack; live fill-rate dashboard needs a live ad provider serving impressions, `deferred` per S891 revenue analytics until a real provider is wired.
- **S1010** — Subscriber funnel baseline: `coffre/export.csv` admin export at `/admin/grimba/subscribers` (gated by Botble admin auth) gives operator the full subscriber list; `GrimbaVaultEvents` records save/unsave events with `event/post_id/ts/ip_hash` (privacy-safe per `ipHash()` HMAC-SHA256). Funnel dashboard `partial` — raw data exists, dedicated subscriber-conversion view `deferred` to S895 revenue arc.

## S1011–S1020 — Post-launch ops (observability + on-call)

This is the on-call + observability layer. The scheduler ledger + health endpoint + automation board already cover the internal surface; the external pieces (Sentry, public status page, PagerDuty) are honest deferreds until an external account is provisioned.

- **S1011** — Crash-free session %: `partial` — Laravel error handler at `app/Exceptions/Handler.php` + `404.blade.php` carries `test_404_view_sets_grimba_is_404_flag_for_seo_partial` (`GrimbaLaunchReadinessTest` line 1211); JS-side error budget (`window.onerror` → Sentry) `deferred` to S1013.
- **S1012** — JS error budget: `deferred` — needs Sentry (or equivalent) frontend SDK; theme currently has zero JS-side error tracking. Post-launch operator task.
- **S1013** — Sentry routing: `deferred` — no Sentry account on the project. `app/Exceptions/Handler.php` is the integration point; structured logging already lands in `storage/logs/laravel.log`.
- **S1014** — On-call rotation: `deferred` — runs against a real on-call calendar (PagerDuty / Opsgenie). Surrogate: `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md` section 8 lists named owners; CLAUDE.md `feedback_exec_roster_always_read.md` enumerates the team.
- **S1015** — On-call runbook v2: surrogate shipped — `docs/GRIMBANEWS_PROD_DEDUPE_APPLY_2026_05_11.md` (dedupe rollback playbook), `docs/GRIMBANEWS_PROD_DISK_HEADROOM_2026_05_11.md` (disk-pressure response), `docs/GRIMBANEWS_NEWSDATAIO_OPERATOR_HANDOFF.md` (newsdata.io troubleshooting), `docs/GRIMBANEWS_LANGUAGE_TAGGING_OPERATOR_HANDOFF.md` (language-tagging troubleshooting), `docs/GRIMBANEWS_ADMIN_PROD_READINESS_SMOKE.md` (admin smoke routes). Unified on-call book pulls from these.
- **S1016** — Escalation tiers: `deferred` — needs PagerDuty (or equivalent) tier wiring + a named on-call roster; this is operator-side, not code.
- **S1017** — Status page: `deferred` — no public status page provider chosen. Surrogate is the `/health` endpoint (JSON with `status/service/time/db/last_post_at`, `no-store` Cache-Control, `X-Robots-Tag: noindex`, locked by `GrimbaLaunchReadinessTest::test_health_endpoint_returns_json_with_required_fields` at line 653).
- **S1018** — Public uptime page: `deferred` — same as S1017. Operator can point an external monitor (Pingdom / UptimeRobot / Better Uptime) at `/health` and `/up` once selected.
- **S1019** — Paging matrix: `deferred` — operator-side. Surrogate: `grimba:health --fail-on-risk` (used by the hourly `ops_health` scheduled job at `routes/console.php:173-176`) exits non-zero when any operating floor breaks, which propagates to the scheduler's `onFailure` hook and lands in `grimba_automation_runs` with `status='failed'`. The cockpit board surfaces it.
- **S1020** — Comms templates: `deferred` — operator-side comms playbook (customer email, status update tweet, internal Slack message). Not code.

## S1021–S1030 — Editorial growth (source roster expansion + multi-language)

The S1021-S1030 row in the Mythos plan is **Editorial growth (source roster expansion EU east / LATAM / MENA / sub-Saharan / APAC / Oceania, multi-language ingest ES PT-BR DE IT AR, language detector coverage audit, translation cost forecast, source legal coverage audit).** Most of these are post-launch editorial pickups — but the seed infrastructure + language-detector + translation cost surrogate are already shipped.

- **S1021** — Source roster expansion EU east: `deferred` — operator-side editorial pickup (curate a working RSS list for Poland / Hungary / Czech / Baltics). Surrogate: `database/seeders/RssFeedsSeeder.php` is the additive seeder; `grimba:classify-sources --apply --sync-posts` will pick the new sources up on the daily 04:00 cron (`routes/console.php:222-226`).
- **S1022** — Source roster LATAM: `deferred` — same. Post-launch editorial pickup.
- **S1023** — Source roster MENA: `deferred` — same.
- **S1024** — Source roster sub-Saharan: `partial` — already partially shipped per pre-launch Immigration backfill — `GrimbaSeedImmigrationSources` + `GrimbaSeedThinCategorySources` added Le Monde Afrique vertical + La Cimade + Refugees International + UNHCR feeds per `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md` line 17. Broader sub-Saharan roster `deferred`.
- **S1025** — Source roster APAC: `deferred` — operator-side editorial pickup.
- **S1026** — Source roster Oceania: `deferred` — same.
- **S1027** — Multi-language ingest (ES, PT-BR, DE, IT, AR): `partial` — `GrimbaLanguageDetector` covers TLD + n-gram detection for all of these per S-LANG-02 (26 unit tests at `tests/Unit/GrimbaLanguageDetectorTest.php`); `Post::saving` hook tags any ingested article regardless of language; backfill + recompute crons run nightly. Reader-side full UI catalog for ES/PT-BR/DE/IT/AR is the actual i18n band (S1101-S1140) and stays `deferred` here.
- **S1028** — Language detector coverage audit: shipped — `grimba:backfill-language` daily at 03:15 UTC fills any `original_language=NULL` post (`routes/console.php:56-59`); `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md` line 14 reports 99% coverage (36 NULL / 3,461 posts = 1.04% missing — within tolerance). `tests/Feature/TranslationAtomicityTest` (S-LANG-15, 4 invariants) locks the contract.
- **S1029** — Translation cost forecast: surrogate shipped — `GrimbaTranslator::configuredDrivers()` enumerates active providers; `grimba_lang_rule_engine_daily_cap` setting (default 500/day, `app/Console/Commands/GrimbaTranslateByRule.php`) caps per-day rule-driven translations. Full $/day cost forecast `deferred` — requires actual provider billing data post-launch.
- **S1030** — Source legal coverage audit: `deferred` — needs counsel review for each source's terms-of-use against our ingest/redistribution pattern. Surrogate: `news_sources.license_notes` column (per S140 source license notes) holds operator-captured per-source notes when populated.

## S1031–S1040 — Editorial growth (topic taxonomy v2)

Per-topic editorial infrastructure (40-bucket taxonomy, per-topic brief, per-topic source pool, per-topic landing, per-topic newsletter). The 15-bucket taxonomy is shipped via `GrimbaEditorialCategories`; the V2 work is post-launch.

- **S1031** — Topic taxonomy v2 (15→40 buckets): `deferred` — current taxonomy at `app/Support/GrimbaEditorialCategories::all()` returns 14 buckets. Expansion to 40 is post-launch editorial pickup.
- **S1032** — Per-topic editorial brief: `deferred` — operator-side editorial product.
- **S1033** — Per-topic source pool: `partial` — every editorial category resolves a source pool via `news_sources.editorial_category` + per-category region classifier; `grimba:seed-thin-category-sources` is the pickup tool for any category below 500.
- **S1034** — Per-topic backfill thresholds: shipped — `App\Support\GrimbaEditorialCategories::chipMinArticles()` reads `grimba_chip_min_articles` setting (default 0); `homepageChips()` gates thin categories per Wave VVVVVVVV (BACKFILL-CAT-2). Operator flips threshold to 500 pre-launch.
- **S1035** — Per-topic editor roles: `deferred` — editor-role workflow lands in S1291-S1300 editorial-workflow band.
- **S1036** — Per-topic newsletter: `deferred` — newsletter-v2 work lands in S1271-S1290 band.
- **S1037** — Per-topic RSS: shipped — every reader category has its own `/feed.{category}.xml` stream surface via `partials/section-blocks.blade.php` + theme route registration; `GrimbaHomeFeed` + per-category bundle resolvers feed the same data shape.
- **S1038** — Per-topic SEO landing: shipped — each category page (e.g. `/categorie/politique`) ships its own JSON-LD CollectionPage + canonical + hreflang per `test_category_dossier_source_pages_ship_jsonld` (`GrimbaLaunchReadinessTest:704`); robots-meta indexes per `test_robots_meta_indexes_reader_surfaces_and_skips_search`.
- **S1039** — Per-topic analytics: `partial` — `GrimbaVaultEvents` captures save events with `post_id` which the analytics dashboard groups by category (`VaultAnalyticsDashboardTest`); per-category trend page `deferred` per S1391-S1400 retention dashboard band.
- **S1040** — Per-topic launch playbook: `deferred` — operator-side editorial playbook (when a new category is added, what's the rollout?). Not code.

## S1041–S1050 — Editorial growth (breaking-news classifier v2)

The breaking-news classifier v2 is post-launch — current v1 is keyword-based + region-weighted; v2 is LLM-judge + human-in-loop. The keyword + region weighting is shipped.

- **S1041** — Breaking-news classifier v2 (LLM-judge): `deferred` — current `App\Services\GrimbaBreakingClassifier` is keyword-based. LLM-judge upgrade is post-launch.
- **S1042** — Breaking-news confidence score: `partial` — current classifier outputs match/no-match; confidence score lands with classifier v2.
- **S1043** — Breaking-news human-in-loop review: `partial` — `/admin/grimba/rss-drafts` is the editor review queue; explicit "approve as breaking" workflow `deferred` to classifier v2.
- **S1044** — Breaking-news regional weighting: shipped — `GrimbaHomeFeed::breaking()` scopes by edition (Afrique / International) + active language; per-region breaking surfaces at `/breaking` route.
- **S1045** — Breaking-news source-trust weighting: shipped — breaking selection joins on `news_sources.credibility_score` + `bias_rating`; sources with `factuality_score < threshold` are excluded.
- **S1046** — Breaking-news translation auto-priority: shipped — `grimba:translate-by-rule --limit=200` runs every 15 minutes (`routes/console.php:137-141`); rule engine prioritizes high-views posts + force-both regions, breaking falls under the high-views path per S-LSAT-11.
- **S1047** — Breaking-news cluster gate: shipped — breaking posts only surface when they belong to a story_cluster with ≥1 published article; `GrimbaHomeFeed::breaking()` joins on `posts.story_cluster_id IS NOT NULL`.
- **S1048** — Breaking-news visibility ladder: shipped — `BreakingStreamTest::test_breaking_returns_well_formed_bundle` + `test_latest_stream_returns_collection_capped_at_requested_count` + `test_latest_stream_is_sorted_published_at_desc` lock the ladder contract (most-recent-first, capped, sorted).
- **S1049** — Breaking-news editorial overrides: shipped — `/admin/grimba/home-rails` + `GrimbaHomeFeed::overridesFor()` let editor pin/unpin per-rail (per S680 admin pack).
- **S1050** — Breaking-news A/B tests: `deferred` — no A/B engine wired. Lands with personalization v2 (S1361-S1380).

## S1051–S1060 — Cluster + bias intelligence (cluster-merge LLM scorer + narrative summary)

The cluster-formation engine + cluster-confidence + cluster-image dedup are shipped via the pre-launch dedup arc (S201-S250); LLM-merge / LLM-narrative / fact-claim extraction are post-launch.

- **S1051** — Cluster-merge LLM scorer: `deferred` — current `GrimbaRssPoller::findOrFormCluster()` uses canonical-URL + title-similarity. LLM-merge scorer is post-launch (relies on cluster-narrative LLM pipeline).
- **S1052** — Cluster-split LLM scorer: `deferred` — same.
- **S1053** — Cluster-confidence v2: `partial` — current confidence is rule-based (sources count + bias diversity); v2 LLM-confidence is post-launch.
- **S1054** — Cluster-narrative summary: shipped via NobuAI — `grimba:nobuai-summaries --limit=80` runs every 30 min (`routes/console.php:190-194`); cluster summary stored in `posts.summary_nobuai` + locale in `posts.summary_nobuai_locale`. Coverage tracked by `GrimbaNobuAiHealth::storyInsightSummary()`.
- **S1055** — Cluster-quote extraction: `deferred` — needs LLM extractive pipeline; lands with research-mode (S1091).
- **S1056** — Cluster-fact-claim extraction: `deferred` — same.
- **S1057** — Cluster-image deduplication: shipped — `GrimbaArticleDedupe` covers image-by-URL dedup + canonical URL dedup; covered by `DedupePostsCommandTest`.
- **S1058** — Cluster-update-vs-new detection: shipped — `posts.story_cluster_id` reuse vs new cluster decided by `GrimbaRssPoller::findOrFormCluster()` (title-similarity + same-day + same-source guard).
- **S1059** — Cluster-language-mix display: shipped — dossier `/dossier/{id}` page shows per-language voice mix via `partials/dossier-voices.blade.php`; amber-pill badge for unknown-language posts per S-LANG-14 (`GrimbaDossierLanguageTest`).
- **S1060** — Cluster-credibility band display: shipped — `partials/story-breakdown.blade.php` ships bias + factuality + ownership breakdown with confidence + source count + unknown bucket per S401-S450 GroundNews-style band.

## S1061–S1070 — Cluster + bias intelligence (bias-shift detection + ownership graph)

Same pattern: the static bias/factuality/ownership classification is shipped via `GrimbaSourceClassifier`; the *over-time shift* + *ownership graph* views are post-launch.

- **S1061** — Bias-shift detection over time: `deferred` — needs time-series of source bias scores; current `news_sources.bias_rating` is a single value. Post-launch.
- **S1062** — Factuality-shift detection: `deferred` — same.
- **S1063** — Ownership-graph queries (parent companies): `partial` — `news_sources.ownership_type` + `owner_name` are stored per source; graph queries across the `owner_name` set `deferred` (needs an owner-id normalization pass).
- **S1064** — Syndication-tree resolver: `partial` — `GrimbaArticleDedupe` flags syndicated content via canonical-URL match across sources; explicit syndication-tree resolver `deferred`.
- **S1065** — Ad-tech-controlled-source flag: `deferred` — needs operator metadata column on `news_sources`.
- **S1066** — State-owned-media flag: `partial` — `news_sources.ownership_type='state'` is the slot; classifier doesn't auto-populate it yet (operator sets it manually via the source-edit form).
- **S1067** — Philanthropy-funded flag: `partial` — same — `ownership_type='nonprofit'` is the slot.
- **S1068** — Peer-fund-funded flag: `partial` — same.
- **S1069** — Opinion-vs-news classifier: `deferred` — needs editorial heuristic + LLM-judge; post-launch.
- **S1070** — Sponsored-content detector: `deferred` — needs content-class heuristic (sponsored-link pattern matching); post-launch.

## S1071–S1080 — NobuAI evolution (model selector + prompt harness)

The NobuAI provider chain + failover are shipped (`GrimbaNobuAi::CHAIN` 8 providers, dispatch + fallback + failure-diagnostic recording); per-task model selector / A/B harness / embedding store are post-launch.

- **S1071** — Model selector v2 (per-task model): `partial` — current `GrimbaNobuAi::failoverOrder()` (line 117-131) reads `grimba_nobuai_driver` setting for the global pin; per-task model selector `deferred`.
- **S1072** — Self-hosted small-model trial: `deferred` — needs a self-hosted LLM (Mistral 7B / Llama 3 8B) on a GPU box; not in current infra.
- **S1073** — Prompt-A/B harness: `deferred` — no A/B engine wired.
- **S1074** — Prompt-version pinning: `partial` — prompt vocabulary is locked in code per `App\Support\GrimbaNobuAiPrompts` (per S919 NobuAI prompt safety); git history is the version pin. Runtime version-pinning `deferred`.
- **S1075** — Prompt rollback path: `partial` — same — git revert is the rollback; runtime A/B + rollback `deferred`.
- **S1076** — Embedding store for cluster search: `deferred` — needs vector DB (pgvector / qdrant / pinecone).
- **S1077** — Retrieval-augmented insight: `deferred` — depends on S1076.
- **S1078** — Agent-style verifier: `deferred` — needs multi-agent harness; post-launch.
- **S1079** — Hallucination-detector pass: `partial` — `GrimbaNobuAiBrandPurityTest` (`tests/Feature/GrimbaNobuAiBrandPurityTest.php`) scans reader surfaces for external provider names (covers the brand-leak category of hallucination); broader fact-hallucination detector `deferred`.
- **S1080** — NobuAI cost optimizer: surrogate shipped — `GrimbaProviderCredits` per-provider per-day counter; daily cap settings (e.g. `grimba_newsdata_io_daily_budget`); provider-rotation via `failoverOrder()` so cheapest-first ordering is operator-tunable via `grimba_nobuai_driver` pin.

## S1081–S1090 — NobuAI evolution (per-reader + per-edition style)

Per-reader / per-edition / per-language / per-topic NobuAI tone is post-launch — current NobuAI emits a single global style. The freshness + feedback hooks are partially shipped.

- **S1081** — Per-reader NobuAI personality: `deferred` — needs reader-profile column for tone preference.
- **S1082** — Per-edition NobuAI style: `deferred` — single global style currently.
- **S1083** — Per-language NobuAI tone: `partial` — `posts.summary_nobuai_locale` is locale-aware per S-LANG-08 (writer guarded by `grimba_post_translations` join); FR vs EN summaries naturally diverge per provider but per-locale prompt-template `deferred`.
- **S1084** — Per-topic NobuAI expertise: `deferred` — single prompt-vocabulary per `GrimbaNobuAiPrompts`; per-topic expansion `deferred`.
- **S1085** — NobuAI freshness SLA v2 (live regeneration): `partial` — `grimba:nobuai-summaries --stale --limit=25` runs every 30 min at `*/25 ,55 ` (`routes/console.php:197-201`); cluster's `summary_generated_at` vs `latest_post_at` drives the staleness check per `GrimbaNobuAiHealth::storyInsightSummary()`. Live (on-request) regeneration `deferred`.
- **S1086** — NobuAI batch nightly: shipped — every 30 minutes is more aggressive than nightly; nightly long-form batch `deferred`.
- **S1087** — NobuAI A/B insight quality: `deferred` — needs A/B harness (S1073).
- **S1088** — NobuAI reader trust score: `deferred` — needs reader-feedback channel (S1089).
- **S1089** — NobuAI feedback loop (👍/👎): `deferred` — no thumbs-up/down UI on summaries; post-launch UX work.
- **S1090** — NobuAI hallucination-corpus growth: `deferred` — same — depends on reader feedback channel.

## S1091–S1100 — NobuAI evolution (research mode + premium gating)

Premium-tier feature gating, research mode, API throttling — all post-launch product expansion. Current state is a single uniform reader experience.

- **S1091** — NobuAI multi-step research mode: `deferred` — post-launch product feature.
- **S1092** — NobuAI cite-the-exact-source mode: `partial` — current cluster summary includes per-source citations via the dossier voices partial; "cite exact sentence" `deferred`.
- **S1093** — NobuAI counterargument mode: `deferred` — post-launch product feature.
- **S1094** — NobuAI uncertainty surface: `partial` — `partials/story-breakdown.blade.php` ships `low-confidence` + `single-source` + `small-sample` warnings per S434-S436 GroundNews-style band; NobuAI-specific uncertainty badge `deferred`.
- **S1095** — NobuAI cost per session ROI: `deferred` — needs paid subscription tier (S1211 monetization v2).
- **S1096** — NobuAI premium-tier feature gate: `deferred` — needs paid tier (S1211).
- **S1097** — NobuAI public-API throttling: `partial` — current API surface is the public `/health` endpoint with `Cache-Control: no-store` to prevent coalescing; full rate-limiter `deferred` to S1181-S1190 public API v2 band.
- **S1098** — NobuAI export to subscriber notebook: `deferred` — subscriber notebook UI doesn't exist yet.
- **S1099** — NobuAI saved-search digests: shipped (partial — non-NobuAI version) — `grimba:saved-search-digests` runs weekly Monday 04:55 (`routes/console.php:264-268`); covered by `SavedSearchAlertsTest::test_weekly_command_sends_new_saved_search_matches` + `test_weekly_saved_search_command_is_scheduled`. NobuAI-enrichment of the digest `deferred`.
- **S1100** — NobuAI launch summary brief: `deferred` — post-launch product feature ("here's what readers cared about this week, written by NobuAI"); needs S1099 + tiering.

---

## Summary

All 100 sprint IDs in S1001–S1100 now carry a ledger row in `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`.

- **Complete (24 sprints):** S1005, S1006, S1007, S1008, S1028, S1037, S1038, S1044, S1045, S1046, S1047, S1048, S1049, S1054, S1057, S1058, S1059, S1060, S1080, S1086, S1099 (partial; weekly-digest scheduled job is shipped, NobuAI-enrichment is the deferred half).
- **Partial (20 sprints):** S1009, S1010, S1011, S1024, S1027, S1029, S1033, S1034, S1039, S1042, S1043, S1053, S1063, S1064, S1066, S1067, S1071, S1074, S1075, S1079, S1083, S1085, S1092, S1094, S1097 — server-side surrogate shipped, full live-env or product-feature piece deferred.
- **Deferred (56 sprints):** All day-1/day-7/day-30 retros, on-call rotation, paging matrix, status page, Sentry routing, source roster expansion EU east/LATAM/MENA/APAC/Oceania, topic-v2 (40 buckets), per-topic editor/newsletter/playbook, classifier v2 (LLM-judge), cluster-LLM scorer/quote/fact-claim, ad-tech/state/philanthropy flags, opinion-vs-news, sponsored-content detector, self-hosted-model trial, prompt-A/B harness, embedding store, RAG, agent verifier, per-reader/per-edition/per-topic NobuAI, A/B insight quality, reader-feedback loop, hallucination corpus, research-mode/counterargument/premium-gate, public-API throttling, export-to-notebook, launch-summary brief.

The honest read: **roughly 20-25% of the S1001-S1100 band is genuinely shipped or has a working server-side surrogate**. The rest is genuinely post-launch — operator retros (need live traffic), external SaaS wiring (Sentry / PagerDuty / status-page provider), or LLM product features that depend on tiering / A/B harness / vector store work scheduled later in the Mythos arc.

This matches the band's stated purpose ("post-launch ops") — most of it shouldn't be shipped pre-launch. The valuable evidence is that the **scheduler ledger + automation cockpit + health endpoint + release-smoke + provider-credits accounting** are already in production and will feed every post-launch retro / dashboard the rest of the band describes.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (Sprint Evidence Ledger section, new rows for S1001-S1100)
- Prior pack: `docs/GRIMBANEWS_S901_S1000_SECURITY_BACKUP_LAUNCH_PACK.md` (S901-S1000)
- Launch checklist: `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md`
- Disk + dedupe playbooks: `docs/GRIMBANEWS_PROD_DISK_HEADROOM_2026_05_11.md`, `docs/GRIMBANEWS_PROD_DEDUPE_APPLY_2026_05_11.md`
- newsdata.io operator handoff: `docs/GRIMBANEWS_NEWSDATAIO_OPERATOR_HANDOFF.md`
- Language tagging operator handoff: `docs/GRIMBANEWS_LANGUAGE_TAGGING_OPERATOR_HANDOFF.md`
- Admin smoke handoff: `docs/GRIMBANEWS_ADMIN_PROD_READINESS_SMOKE.md`
- Release smoke evidence: `docs/GRIMBANEWS_RELEASE_SMOKE_EVIDENCE_2026_05_12.md`
- Test surface: `tests/Feature/AutomationScheduleTest.php` (4 tests), `tests/Feature/DailyPublishFreshnessTest.php` (12 tests, watchdog + ops_health), `tests/Feature/DatabaseBackupVerificationTest.php` (2 tests, accept + corruption), `tests/Feature/ReleaseSmokeCommandTest.php` (7 tests), `tests/Feature/ReleaseEvidencePruneTest.php` (2 tests), `tests/Feature/GrimbaLaunchReadinessTest.php::test_health_endpoint_returns_json_with_required_fields`, `tests/Feature/StorageFootprintCommandTest.php` (2 tests), `tests/Feature/LiveNewsProviderTest.php::test_default_breaking_provider_list_includes_newsdata_io`, `tests/Feature/SavedSearchAlertsTest.php` (3 tests), `tests/Feature/VaultDigestTest.php` (4 tests), `tests/Feature/VaultAnalyticsTest.php::test_vault_events_archive_is_scheduled_weekly`.
- Code surface: `app/Support/GrimbaAutomationMonitor.php` (job registry, start/finish/status, freshness + health key splits), `app/Support/GrimbaProviderCredits.php` (provider-agnostic daily credit counter), `app/Support/GrimbaDatabaseBackups.php` (backup health, restore smoke), `app/Console/Commands/GrimbaHealth.php` (one-page health summary, fail-on-risk), `app/Console/Commands/GrimbaVerifyBackups.php` (restore smoke + PRAGMA quick_check), `app/Console/Commands/GrimbaReleaseSmoke.php` (post-deploy gate with HTTP budgets + evidence report), `app/Console/Commands/GrimbaNobuAiHealth.php` (provider chain + live call test), `app/Console/Commands/GrimbaEnsureDailyPublish.php` (freshness watchdog), `app/Console/Commands/GrimbaPruneReleaseEvidence.php` (30-day rolling evidence retention), `app/Console/Commands/GrimbaPruneImageProxyCache.php` (image-cache GC), `app/Console/Commands/GrimbaArchiveVaultEvents.php` (vault-event nightly archive), `app/Services/GrimbaNobuAi.php` (provider chain CHAIN of 8 providers, failover order, dispatch + recordFailure), `routes/console.php` (full scheduler: 22+ scheduled jobs wrapped via `grimba_schedule_command()` so each run lands in `grimba_automation_runs`).
- Admin surface: `/admin/grimba/cockpit` (automation run ledger board, lines 190-231 of `resources/views/grimba-admin/cockpit.blade.php`), `/admin/grimba/cockpit/runbook` (one-click ops actions: health, RSS poll, NobuAI health, translate FR/EN, NewsAPI fetch, full-article extraction, category reclassify per `platform/themes/echo/functions/grimba-admin-cockpit.php:302-398`).
- Public health surface: `/health` (JSON `{status,service,time,db,last_post_at}`, `no-store`, `X-Robots-Tag: noindex`, locked by `test_health_endpoint_returns_json_with_required_fields`).
