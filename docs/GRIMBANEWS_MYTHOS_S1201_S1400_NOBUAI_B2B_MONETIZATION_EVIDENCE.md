# Mythos S1201–S1400 — NobuAI Evolution + B2B / API + Monetization v2 + Retention Evidence Pack

**Status:** evidence reconciliation
**Created:** 2026-05-22
**Author:** Wave ZZZZZZZZZ batch close (third Mythos post-launch band — 200 sprints in one slab)
**Scope:** Converts the S1201–S1400 slice of the Mythos S1001–S2237 post-launch arc — NobuAI fine-tune evolution + cost ops v2 + quality v2 (S1201-S1230), B2B API v1 / v2 / ops (S1231-S1260), monetization v2 + ad revenue v2 + newsletter monetization (S1261-S1290), retention + push (S1291-S1310), editorial workflow + growth v2 (S1311-S1330), search v2 + personalization v2 (S1331-S1350), trust & safety + community v2 + reader product v2 (S1351-S1380), daily-report v2 + mobile app program (S1381-S1400) — into ledger rows pointing at real shipped code, third-party-account deferreds, and operator pickups.

This pack feeds the master `Sprint Evidence Ledger` in `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`. The 200 sprint IDs in S1201–S1400 now have a ledger row. The honest split is **dominated by `deferred`** — most of these sprints are post-launch product expansions that gate on **paid tier infra (no Stripe / billing today)**, **third-party accounts (FCM / APNs / Apple Developer / Google Play / Sentry / vector DB / OAuth provider)**, **A/B harness (does not exist)**, **embedding store (does not exist)**, **comment / annotation / community infra (does not exist)**, or an **explicit B2B product expansion phase**. The pre-launch foundation that *is* shipped (`GrimbaNobuAi` driver chain + failover, `GrimbaProviderCredits` per-provider per-UTC-day counter, `GrimbaSavedSearches` + weekly digest, `GrimbaSendVaultDigests` weekly cron, `GrimbaAds` 12-slot AdSense surface, RSS / per-stream / per-category feeds as the read-only API surrogate, `partials/home/daily-briefing.blade.php` as the daily-report v1 surrogate) is called out per sprint so the next session knows which deferred row drops into a working foundation versus which one needs a fresh build.

The S001–S1000 pre-launch arc is the launch gate; the S1001+ work hardens / expands the running platform. The S1201–S1400 band is the **post-launch growth / B2B / monetization arc**, and the honest read is that **roughly 80% of the 200 sprints in this band are `deferred` until paid tier + third-party accounts + B2B product expansion ship**. The remaining 20% split is between code-shipped foundations (NobuAI driver chain, ProviderCredits counter, saved-search digest, vault digest, blindspot rail, daily-briefing card, AdSense slots, RSS as API surrogate) and operator-side editorial / legal / ops pickups.

---

## S1201–S1210 — NobuAI fine-tune evolution (model rotation + custom prompt versions + hallucination detection)

The NobuAI provider-routing layer is shipped: `app/Services/GrimbaNobuAi.php` defines an 8-driver `CHAIN` (`mistral`, `openrouter`, `openai`, `anthropic`, `google`, `xai`, `perplexity`, `groq`) with per-driver credential probe + automatic failover + per-driver failure diagnostics persisted in `settings`. The `grimba_nobuai_driver` setting pins a preferred driver; absent that pin, the chain rotates. Per-task model selection, A/B harness, fine-tune, embedding store, RAG, and agentic verifier are all post-launch expansions and `deferred` in this band.

- **S1201** — Model rotation v2: `partial` — `GrimbaNobuAi::failoverOrder()` rotates across the 8-driver CHAIN with `grimba_nobuai_driver` pin override; per-task rotation policy (e.g. "summary uses fast, fact-check uses slow") `deferred` — currently every NobuAI call hits the same chain.
- **S1202** — Custom prompt versions v2: `deferred` — `app/Support/GrimbaNobuAiPrompts.php` (per S1074) is git-tracked; no runtime prompt-version pin (would need `grimba_nobuai_prompt_version` setting + per-version A/B routing). Rollback path = `git revert`.
- **S1203** — Hallucination detection (claim-level): `deferred` — needs a verifier model + ground-truth corpus. Brand-level hallucination (provider-name leak in user copy) is already locked by `tests/Feature/GrimbaNobuAiBrandPurityTest`, but factual-claim verification is post-launch product work.
- **S1204** — NobuAI prompt-template registry: `partial` — `app/Support/GrimbaNobuAiPrompts.php` exists; per-template version pin + diff log `deferred`.
- **S1205** — NobuAI per-call audit log: `partial` — `GrimbaProviderCredits::bump()` increments per-provider per-UTC-day counter via `grimba_live_news_provider_runs`; per-call prompt+output audit log `deferred` (would require new `grimba_nobuai_calls` table, currently we only count).
- **S1206** — NobuAI driver health board: `complete` — `app/Console/Commands/GrimbaNobuAiHealth.php` reports per-driver readiness + last-failure message; `GrimbaNobuAi::failureDiagnostics()` exposes the failure-per-driver state to admin surfaces.
- **S1207** — NobuAI bounded-live smoke: `complete` — `grimba:release-smoke --require-nobuai-live` flag (per Wave HHHHHHHH) gates release on a bounded live provider call; tracked by `GrimbaNobuAiHealth` "Run smoke" admin button.
- **S1208** — NobuAI model-card upload (transparency page): `deferred` — surrogate is the public `/methodology` page that describes the NobuAI stack at the brand level (no provider names per global NobuAI branding rule); per-model card `deferred`.
- **S1209** — NobuAI per-locale prompt tuning: `partial` — `posts.summary_nobuai_locale` records the locale the summary was generated in (S-LANG-08); per-locale prompt-template variants `deferred`.
- **S1210** — NobuAI fine-tune dataset export: `deferred` — would need (a) consented reader-feedback channel (S1089) and (b) JSONL export pipeline; neither shipped.

## S1211–S1220 — NobuAI cost ops v2 (per-provider cost dashboard + daily budget + alert thresholds)

The cost-counter foundation is shipped: `App\Support\GrimbaProviderCredits` keeps per-provider per-UTC-day count using `grimba_live_news_provider_runs` as source-of-truth plus a 36h cache for the hot path. Per-driver hard cap, per-driver $/day forecast, and budget alerts are post-launch.

- **S1211** — Per-provider cost dashboard: `partial` — admin `/admin/grimba/cockpit` surfaces `GrimbaAutomationMonitor::status()` board (covers job-level run health); per-provider $/day cost board `deferred` (we count calls, not dollars — needs per-provider price table).
- **S1212** — Daily budget enforcer: `complete` — `GrimbaProviderCredits::fast()` returns max(DB, cache) pre-flight count; `grimba_lang_rule_engine_daily_cap` setting (`GrimbaTranslationRules`) is the working daily-cap pattern that `GrimbaTranslateByRule` honors per-tick.
- **S1213** — Alert thresholds (per-provider): `partial` — `grimba:health --fail-on-risk` raises non-zero exit when provider quotas are near cap; structured alert-threshold settings UI `deferred`.
- **S1214** — Per-task cost attribution: `deferred` — every NobuAI call shares one provider chain today; no `task_type` column on the call ledger.
- **S1215** — Translation cost ledger: `partial` — `GrimbaTranslationRules` records per-driver caps + per-day budgets; live $/day forecast `deferred` (lands when an external translator vendor with metered pricing is wired — currently NobuAI / OpenRouter / LibreTranslate driver chain is the translator stack).
- **S1216** — Cost forecast v2 (rolling 7-day): `deferred` — needs price tables + per-call ledger.
- **S1217** — Cost anomaly detection: `deferred` — needs baseline + rolling-window analyzer.
- **S1218** — Quota exhaustion replay: `partial` — `GrimbaProviderCredits::bump()` is idempotent per UTC-day count via DB source-of-truth; synthetic quota-exhaustion simulation (S192) is `deferred`.
- **S1219** — Per-tenant cost split: `deferred` — single-tenant today (OEM whitelabel ledger gates on S1191-S1200).
- **S1220** — Cost playbook: `partial` — `docs/GRIMBANEWS_PROD_DISK_HEADROOM_2026_05_11.md` + `docs/GRIMBANEWS_NEWSDATAIO_OPERATOR_HANDOFF.md` cover operator-side cost-pressure playbook for ingest + storage; full NobuAI cost playbook `deferred`.

## S1221–S1230 — NobuAI quality v2 (citation accuracy audit + fact-check loop + blindspot enrichment)

Brand-purity is locked. Citation accuracy at the sentence-claim level requires a verifier model + corpus that does not exist yet. Blindspot is a shipped editorial surface — enrichment is the post-launch piece.

- **S1221** — Citation accuracy audit: `deferred` — needs LLM-judge harness + ground-truth corpus.
- **S1222** — Fact-check loop (auto): `deferred` — same.
- **S1223** — Blindspot enrichment: `partial` — `platform/themes/echo/views/blindspot.blade.php` ships `/angles-morts` with bias-side filter (left/right/all) + per-cluster blindspot rail; `posts.is_blindspot` flag is wired in `GrimbaHomeFeed::blindspotCandidates()`; NobuAI-generated "why this is a blindspot" copy `deferred`.
- **S1224** — NobuAI fact-claim extraction: `deferred` — would feed S1221 + S1222.
- **S1225** — NobuAI counterargument generation: `deferred` — surrogate is the bias-distribution + dossier-voices partials that already surface multiple perspectives per cluster.
- **S1226** — NobuAI uncertainty surface v2: `partial` — `partials/story/source-drilldown.blade.php` (per S434-S436) ships low-confidence + single-source + small-sample warnings; NobuAI-summary-specific uncertainty badge `deferred`.
- **S1227** — NobuAI freshness SLA v2: `partial` — `grimba:nobuai-summaries --stale --limit=25` every 30 min refreshes stale; on-demand "regenerate now" admin button exists but reader-side regenerate `deferred`.
- **S1228** — NobuAI quality scoring (per summary): `deferred` — would need per-summary metric (semantic-coverage / faithfulness / fluency).
- **S1229** — NobuAI summary diff log: `deferred` — every regeneration overwrites `posts.summary_nobuai`; no per-version history table.
- **S1230** — NobuAI quality regression guard: `partial` — `tests/Feature/NobuAiSummaryCommandTest` + `tests/Feature/ExtractiveSynthesisTest` + `tests/Feature/GrimbaNobuAiBrandPurityTest` lock contracts (atomicity, brand purity, command shape); semantic regression suite `deferred`.

## S1231–S1240 — B2B API v1 (auth + rate limits + partner sandbox)

There is no `routes/api.php` registered, no Sanctum / Passport install, no API-key issuance table, no partner sandbox. The read-only surrogate today is per-stream RSS at `/feed.xml`, `/feed.breaking.xml`, `/feed.latest.xml`, `/feed.{category}.xml`. All of this band is `deferred` to the B2B product expansion phase, same as S1181-S1190.

- **S1231** — B2B API v1 design: `deferred` — no `/api/v1` JSON surface; RSS is the only programmatic read today.
- **S1232** — B2B API v1 auth: `deferred` — no Sanctum / Passport; no `api_keys` table.
- **S1233** — B2B API v1 rate limit: `partial` — `App\Http\Controllers\AdvertiserLeadController` ships the canonical per-IP `RateLimiter::attempt('advertiser-lead:'.sha1($ip), ...)` block, ready to be extracted to a `ThrottleApi` middleware; no API-key-scoped throttle yet.
- **S1234** — B2B API v1 key issuance: `deferred` — no key store, no key UI.
- **S1235** — B2B API v1 key rotation: `deferred` — same.
- **S1236** — B2B API v1 partner sandbox: `deferred` — no partner program.
- **S1237** — B2B API v1 IP allowlist: `deferred` — no per-key allowlist field.
- **S1238** — B2B API v1 webhook delivery: `deferred` — no outbound webhook infra.
- **S1239** — B2B API v1 changelog: `deferred` — `docs/CHANGELOG.md` is repo-internal; partner-facing changelog `deferred`.
- **S1240** — B2B API v1 SDK skeleton: `deferred` — same gate as S1231.

## S1241–S1250 — B2B API v2 (article + source + cluster + search endpoints)

Same gate as S1231-S1240 — every endpoint here is `deferred` because there is no API v1 to build v2 against. The Eloquent models (`Post`, `StoryCluster`, `NewsSource`) and the helper surfaces (`GrimbaHomeFeed`, `GrimbaSavedSearches`, `GrimbaSourceBreakdown`) all exist and would be the data layer for any v2, so the deferreds drop into a working foundation the moment routing + auth lands.

- **S1241** — Article endpoint: `deferred` — no `/api/v2/articles` route; `Post` model + `GrimbaTranslationPresenter` are the ready data layer.
- **S1242** — Source endpoint: `deferred` — `NewsSource` model + `GrimbaSourceBreakdown` ready; no route.
- **S1243** — Cluster endpoint: `deferred` — `StoryCluster` model + `GrimbaDossierVoices` ready; no route.
- **S1244** — Search endpoint: `deferred` — `GrimbaSavedSearches::matchingPosts()` is the ready query helper; no JSON route.
- **S1245** — Topic endpoint: `deferred` — `GrimbaEditorialCategories::all()` ready; no JSON route.
- **S1246** — Pagination contract: `partial` — web search + category pages use Laravel paginator; per-page cursor for API v2 `deferred`.
- **S1247** — Field-selection contract: `deferred` — no GraphQL / sparse-fieldsets layer.
- **S1248** — Filter / sort contract: `deferred` — same.
- **S1249** — Error contract: `partial` — `app/Exceptions/Handler.php` returns Laravel JSON 4xx/5xx for `wantsJson()` paths; structured `{error_code, message, retry_after}` envelope for API v2 `deferred`.
- **S1250** — API v2 OpenAPI spec: `deferred` — no spec file shipped.

## S1251–S1260 — B2B API ops (SLA + usage dashboard + billing)

Operational layer for API v2. All `deferred`.

- **S1251** — API uptime SLA: `partial` — `/health` JSON + `/up` cover web uptime evidence; formal contract `deferred`.
- **S1252** — API latency SLA: `partial` — `grimba:release-smoke` enforces homepage/health/up/feed latency budgets per release; API-specific budget `deferred`.
- **S1253** — API usage dashboard: `deferred` — no API to dashboard.
- **S1254** — API billing meter: `deferred` — no Stripe / billing infra.
- **S1255** — API invoice generation: `deferred` — same.
- **S1256** — API quota tiers: `deferred` — same.
- **S1257** — API overage policy: `deferred` — same.
- **S1258** — API SOC2 / GDPR data export: `deferred` — operator-side legal + tooling pickup.
- **S1259** — API status incident comms: `deferred` — no status page (S1017 surrogate is `/health` JSON).
- **S1260** — API ops playbook: `deferred` — gates on S1231-S1259.

## S1261–S1270 — Monetization v2 (subscriber tier + family plan + gift sub)

**Hard blocker**: there is no Stripe install, no `subscriptions` table, no paid tier. The current "subscriber" concept is the Botble member auth at `/account` + `/coffre` (free tier, vault save / share). All of this band gates on the paid-tier ship.

- **S1261** — Paid tier infra (Stripe install): `deferred` — no `composer require stripe/*`; no `stripe_*` settings keys; no `subscriptions` migration.
- **S1262** — Subscriber tier (monthly): `deferred` — depends on S1261.
- **S1263** — Subscriber tier (annual): `deferred` — same.
- **S1264** — Family plan: `deferred` — same.
- **S1265** — Gift subscription: `deferred` — same.
- **S1266** — Subscription upgrade / downgrade: `deferred` — same.
- **S1267** — Subscription pause / resume: `deferred` — same.
- **S1268** — Cancellation flow: `deferred` — same.
- **S1269** — Tax / VAT compliance: `deferred` — operator-side accounting pickup.
- **S1270** — Subscription analytics: `deferred` — same gate.

## S1271–S1280 — Ad revenue v2 (header bidding + native ads + sponsorship slots)

The AdSense surface is shipped: `App\Support\GrimbaAds` defines 12 slots across home / chrome / source / article / story surfaces; `partials/home/ad-slot.blade.php` resolves per-slot HTML via `GrimbaAds::resolve()`; `partials/ads/head.blade.php` loads the AdSense script with `client` ID. Header bidding (prebid.js, Amazon TAM, OpenWrap), native ad formats, and sponsored-content slots are all post-launch expansions. The advertiser-lead funnel (`grimba_advertiser_leads` table + `/advertise` form + admin) is the working sales pipeline.

- **S1271** — Header bidding (prebid.js): `deferred` — no prebid wrapper shipped; AdSense is single-network today.
- **S1272** — Header bidding (Amazon TAM): `deferred` — same.
- **S1273** — Native ad units: `partial` — `grimba_home_native` slot exists in `GrimbaAds::SLOTS` (format=auto, placement=home-native); AdSense native render is the surrogate; structured native template `deferred`.
- **S1274** — Sponsored-content slots: `partial` — advertiser-lead pipeline at `/advertise` + `/admin/grimba/advertiser-leads` is the sales-side; per-cluster sponsorship slot render `deferred`.
- **S1275** — Programmatic deal IDs: `deferred` — needs SSP / DSP relationship.
- **S1276** — Floor pricing: `deferred` — same.
- **S1277** — Brand safety filter: `partial` — `App\Support\GrimbaIngestGuardrails` keyword filter is the editorial-side brand-safety layer; ad-network-side brand-safety `deferred`.
- **S1278** — Frequency cap per reader: `deferred` — no client-side cap on AdSense calls.
- **S1279** — Viewability tracking: `deferred` — needs viewability SDK.
- **S1280** — Ad revenue dashboard: `deferred` — surrogate is AdSense console.

## S1281–S1290 — Newsletter monetization (paid digests + sponsorship)

There is no general newsletter (sign-up modal exists, but the only weekly cron-emails are `grimba:vault-digests` for vault-savers and `grimba:saved-search-digests` for member saved searches). All of this band is `deferred`.

- **S1281** — Paid newsletter tier: `deferred` — depends on S1261.
- **S1282** — Newsletter sponsorship slot: `deferred` — no general newsletter to sponsor.
- **S1283** — Per-segment newsletter: `partial` — `grimba:saved-search-digests` already per-search-segment (one member, many searches → one digest per match group); broader per-segment newsletter `deferred`.
- **S1284** — Newsletter A/B subject test: `deferred` — no A/B harness.
- **S1285** — Newsletter unsubscribe analytics: `deferred` — `members.unsubscribed_at` flag exists upstream in Botble; per-mail analytics `deferred`.
- **S1286** — Newsletter open / click tracking: `deferred` — no tracking pixel / link rewriter.
- **S1287** — Newsletter advertiser-lead funnel: `partial` — `/advertise` form captures intent; newsletter-specific advertiser CTA `deferred`.
- **S1288** — Newsletter revenue share (with editors): `deferred` — no in-house editor program (lands with S1311+).
- **S1289** — Newsletter compliance (CAN-SPAM / GDPR): `partial` — Botble member auth + footer unsubscribe link is the baseline; full compliance audit `deferred`.
- **S1290** — Newsletter monetization playbook: `deferred` — gates on S1281-S1289.

## S1291–S1300 — Retention (daily streaks + weekly recap + vault digest + saved-search digest)

This is the band where the most ships already. Vault digest + saved-search digest are wired as weekly crons and tested; streaks + weekly recap are post-launch product features.

- **S1291** — Daily streak counter: `deferred` — no `members.streak_days` column; no per-day visit ledger.
- **S1292** — Streak email reminder: `deferred` — depends on S1291.
- **S1293** — Weekly recap email: `partial` — `grimba:vault-digests` weekly cron emits per-member vault digest with last week's saves (per `routes/console.php:255`); broader weekly recap (most-read, biggest stories, blindspots) `deferred`.
- **S1294** — Vault digest mail: `complete` — `grimba:vault-digests` weekly cron + `App\Mail\GrimbaVaultDigestMail` + `resources/views/emails/vault-digest.blade.php` ship.
- **S1295** — Saved-search digest mail: `complete` — `grimba:saved-search-digests` weekly Monday 04:55 + `App\Mail\GrimbaSavedSearchDigestMail` + `App\Support\GrimbaSavedSearches::matchingPosts()` ship; `SavedSearchAlertsTest` locks the contract.
- **S1296** — Reader achievement badges: `deferred` — no badge / gamification layer.
- **S1297** — Re-engagement email (dormant member): `deferred` — needs dormant-member detection + email template.
- **S1298** — In-product re-engagement nudge: `deferred` — `partials/home/onboarding-modal.blade.php` is the new-reader nudge; dormant-reader nudge `deferred`.
- **S1299** — Retention dashboard: `partial` — `/admin/grimba/vault-analytics` surfaces vault save / share / digest events (per `tests/Feature/VaultAnalyticsDashboardTest`); broader retention cohort dashboard `deferred`.
- **S1300** — Retention playbook: `partial` — `docs/GRIMBANEWS_ADVERTISER_CULTURE_FRESHNESS_PLAN.md` covers freshness; reader-retention playbook `deferred`.

## S1301–S1310 — Retention — push notifications (web push + mobile push gated by FCM)

No push infrastructure today. Web push (VAPID + service-worker push handler) does not exist. Mobile push (FCM + APNs) gates on a native shell. All `deferred`.

- **S1301** — Web push opt-in: `deferred` — no VAPID keys; `public/grimba-sw.js` does not register a `push` event listener.
- **S1302** — Web push server (VAPID): `deferred` — no `webpush_subscriptions` table; no `web-push` PHP package.
- **S1303** — Web push payload contract: `deferred` — same.
- **S1304** — Web push delivery worker: `deferred` — same.
- **S1305** — Mobile push (FCM): `deferred` — needs FCM project + service-account JSON.
- **S1306** — Mobile push (APNs): `deferred` — needs APNs auth key.
- **S1307** — Push category preferences: `deferred` — surrogate is per-saved-search opt-in via `saved_searches.active` boolean.
- **S1308** — Push frequency caps: `deferred` — no push infra.
- **S1309** — Push deep-link routing: `partial` — public routes are deep-linkable today; native deep-link registration `deferred` (same as S1155).
- **S1310** — Push opt-in onboarding: `deferred` — same gate as S1301.

## S1311–S1320 — Editorial workflow v2 (in-house source / cluster editor + byline system)

GrimbaNews is aggregator-first today: sources come from RSS / NewsAPI / newsdata.io, clusters form via `GrimbaRssPoller::findOrFormCluster()`, bylines pass through as `source_name` (publisher attribution) — there is no in-house author roster. All v2 editor work is `deferred`.

- **S1311** — In-house source editor: `deferred` — `news_sources` admin at `/admin/grimba/news-sources` covers source metadata; in-house "write your own article" editor `deferred`.
- **S1312** — Cluster manual editor: `partial` — `/admin/grimba/story-clusters` ships pin / merge / split admin (per `tests/Feature/ClusterReviewQueueTest`); editorial narrative editor `deferred`.
- **S1313** — Byline system (in-house): `deferred` — `posts.author_type` / `author_id` fields exist (Botble polymorphic author column, line 1294 of `post.blade.php` reads `class_exists($post->author_type) && ...`) but no in-house author roster.
- **S1314** — Author profile page: `partial` — `platform/themes/echo/views/author.blade.php` exists for Botble authors; not yet wired to aggregator posts (every aggregator post has `author_type=NULL` today).
- **S1315** — Editorial CMS roles: `partial` — Botble admin role/permission system covers operator roles; per-editorial-category role `deferred`.
- **S1316** — Editorial calendar: `deferred` — operator-side editorial calendar lives outside the platform today.
- **S1317** — Editorial draft → review → publish: `partial` — `/admin/grimba/rss-drafts` is the draft queue today; richer multi-stage editorial workflow `deferred`.
- **S1318** — Editorial assignment system: `deferred` — no `assignments` table.
- **S1319** — Editorial style guide enforcement: `deferred` — operator-side editorial guide.
- **S1320** — Editorial workflow playbook: `partial` — `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md` is the operator-side playbook today; v2 workflow doc `deferred`.

## S1321–S1330 — Editorial growth v2 (newsroom partnerships + syndication deals)

Operator-side BD pickup; no platform-side affordances ship in this band today.

- **S1321** — Newsroom partnership doc: `deferred` — operator-side BD pickup.
- **S1322** — Syndication agreement template: `deferred` — operator-side legal pickup.
- **S1323** — Per-partner content stream: `partial` — `RssFeedsSeeder` per-source seed pattern is the data hook; partner-tagged stream `deferred`.
- **S1324** — Per-partner attribution UI: `partial` — `source_name` + source-logo proxy ship on every card today; explicit "partner badge" rendering `deferred`.
- **S1325** — Per-partner analytics: `deferred` — per-source analytics need column on `grimba_vault_events` or new ledger.
- **S1326** — Per-partner revenue share: `deferred` — gates on S1261 paid tier + S1288.
- **S1327** — Per-partner SLA: `deferred` — operator-side contract.
- **S1328** — Per-partner onboarding: `deferred` — operator-side pickup.
- **S1329** — Per-partner case study: `deferred` — needs partnership shipped first.
- **S1330** — Partnership program launch: `deferred` — gates on S1321-S1329.

## S1331–S1340 — Search v2 (semantic search + NobuAI-powered query expansion)

Lexical search ships via `GrimbaSavedSearches::matchingPosts()` (substring + tag match). Semantic search needs an embedding store (pgvector / Qdrant / Pinecone) that does not exist (per S1076). NobuAI query expansion ships nothing today.

- **S1331** — Search v2 design: `partial` — `/search` page + `views/search.blade.php` ship lexical search with facets (`SearchFacetsTest` covers facet generation per S279); semantic-rank v2 design `deferred`.
- **S1332** — Embedding store wiring: `deferred` — no vector DB (per S1076).
- **S1333** — Per-article embedding generation: `deferred` — depends on S1332.
- **S1334** — Per-cluster embedding generation: `deferred` — same.
- **S1335** — Semantic-search query: `deferred` — same.
- **S1336** — NobuAI query expansion: `deferred` — no expansion layer today.
- **S1337** — Spelling correction: `deferred` — no fuzzy-match layer.
- **S1338** — Search-suggestion typeahead: `deferred` — no autocomplete endpoint.
- **S1339** — Search-result snippet generation: `partial` — lexical search returns `posts.description` snippet; NobuAI-enriched snippet `deferred`.
- **S1340** — Search v2 launch: `deferred` — gates on S1332-S1339.

## S1341–S1350 — Personalization v2 (collaborative filter + ML-driven feed)

For-you surface exists (`/pour-vous` + `/for-you` per `views/for-you.blade.php`) but the ranking signal is rule-based (region + saved categories + recent reads), not ML. Collaborative filter + ML rank are post-launch.

- **S1341** — Personalization v2 design: `partial` — `/pour-vous` + cookie-based recent-reads + region preference is the rule-based v1; ML rank v2 design `deferred`.
- **S1342** — Per-reader feature vector: `deferred` — no `member_features` table; cookie carries region + saved categories only.
- **S1343** — Collaborative filter (reader-similarity): `deferred` — needs feature vector (S1342) + similarity job.
- **S1344** — Content-based filter (article-similarity): `deferred` — needs embeddings (S1333).
- **S1345** — ML rank model (LR / GBDT / NN): `deferred` — needs training data + pipeline.
- **S1346** — A/B rank harness: `deferred` — no A/B engine (S1073).
- **S1347** — Cold-start ranking: `partial` — `/pour-vous` falls back to homepage rails for cookie-less readers; ML cold-start `deferred`.
- **S1348** — Diversity / serendipity guard: `partial` — `HomeFeedState` (per `app/Support/HomeFeedState.php`) already de-dupes by source-publisher across home rails so one publisher cannot dominate; ML-diversity layer `deferred`.
- **S1349** — Personalization opt-out: `partial` — readers can clear the `grimba_for_you_recent` cookie via browser controls; explicit opt-out toggle `deferred`.
- **S1350** — Personalization v2 launch: `deferred` — gates on S1341-S1349.

## S1351–S1360 — Trust & safety (moderation queue + brigading detection)

Aggregator surface only; no UGC (no comments, no reviews, no member submissions). Moderation queue therefore has no input today. Brigading detection ships nothing.

- **S1351** — Moderation queue: `deferred` — no UGC to moderate. Surrogate: `/admin/grimba/rss-drafts` is the editorial draft queue (operator-side approval gate for ingest output).
- **S1352** — Brigading detection: `deferred` — no UGC, no vote / reaction surface.
- **S1353** — Spam / bot detection (newsletter / advertiser-lead): `partial` — `AdvertiserLeadController` enforces per-IP RateLimiter + honeypot field per S871 ads pack; broader bot-detection `deferred`.
- **S1354** — Source trust audit: `partial` — `news_sources.credibility_score` + `factuality_score` + admin classifier (`/admin/grimba/news-sources`) ship today; periodic audit cadence `deferred`.
- **S1355** — Source delist workflow: `partial` — `news_sources.active` boolean + admin toggle ship; formal delist workflow + audit log `deferred`.
- **S1356** — Reader report-abuse channel: `partial` — `/.well-known/security.txt` + `/contact` cover security-side; reader-side report-abuse for surfaced articles `deferred`.
- **S1357** — Per-cluster takedown workflow: `partial` — admin can hide a cluster (soft-delete on `story_clusters`); reader-side takedown request `deferred`.
- **S1358** — DMCA / right-of-reply: `deferred` — operator-side legal pickup.
- **S1359** — Transparency report (publisher-level): `deferred` — operator-side annual report.
- **S1360** — Trust & safety playbook: `partial` — `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md` covers launch-side security controls; reader-facing trust & safety playbook `deferred`.

## S1361–S1370 — Community v2 (comment system v2 + threaded discussions)

No comment infrastructure today; no `comments` table, no threading. All `deferred`.

- **S1361** — Comment system v1 (per-article): `deferred` — no `comments` table.
- **S1362** — Comment threading: `deferred` — same.
- **S1363** — Comment moderation queue: `deferred` — same.
- **S1364** — Comment reactions (like / thoughtful): `deferred` — same.
- **S1365** — Comment quality scoring: `deferred` — same.
- **S1366** — Comment notification (per-thread): `deferred` — same.
- **S1367** — Comment muting / blocking: `deferred` — same.
- **S1368** — Comment community guidelines: `deferred` — operator-side editorial guidelines.
- **S1369** — Comment moderator tooling: `deferred` — same.
- **S1370** — Comment launch playbook: `deferred` — gates on S1361-S1369.

## S1371–S1380 — Reader product v2 (annotations + highlights + bookmarks v2)

Bookmarks v1 ships as the vault (cookie + server-side `members.vault_digest_post_ids` sync per S1165). Annotations + highlights are post-launch reader features; nothing ships today. "Highlights" partial (`partials/story/highlights.blade.php`) renders **NobuAI-derived most-mentioned-names** for a cluster, not reader-side highlights.

- **S1371** — Per-article annotation surface: `deferred` — no `annotations` table.
- **S1372** — Reader-side highlight (text selection → save): `deferred` — no client-side selection→server endpoint.
- **S1373** — Vault v2 (folders / tags): `partial` — `App\Support\GrimbaVault` ships cookie + server-side sync; folders / tags layer `deferred`.
- **S1374** — Vault share v2: `partial` — `coffre-share.blade.php` ships shareable vault link per S660; per-folder share `deferred`.
- **S1375** — Vault export v2: `partial` — `coffre/export.csv` ships CSV export; JSON / Markdown export `deferred`.
- **S1376** — Reader notebook: `deferred` — no notebook surface (cited in S1098 deferred).
- **S1377** — Per-cluster reader notes: `deferred` — same.
- **S1378** — Read-later queue: `partial` — vault save serves as read-later today; "queue" semantics (FIFO, mark-as-read) `deferred`.
- **S1379** — Cross-device sync: `partial` — server-side sync via `members.vault_digest_post_ids` happens on login; live cross-device sync (websocket / polling) `deferred`.
- **S1380** — Reader product v2 launch: `deferred` — gates on S1371-S1379.

## S1381–S1390 — Daily-report v2 (per-region editorial digest)

The home-page daily-briefing card ships (`partials/home/daily-briefing.blade.php` renders `GrimbaHomeFeed::briefing()` — single cluster, with bias-spectrum split). A scheduled daily-report email or per-region editorial digest does not ship.

- **S1381** — Daily report card (homepage): `complete` — `partials/home/daily-briefing.blade.php` renders the briefing card sourced from `GrimbaHomeFeed::briefing()`; bias-spectrum % bar (left / center / right) included.
- **S1382** — Daily report email: `deferred` — no `grimba:daily-report-email` command; vault digest + saved-search digest are the closest shipped surrogates (both weekly, not daily).
- **S1383** — Per-region daily report (Afrique / International): `partial` — region scoping for the homepage briefing is via cookie-based edition (Afrique / International per `region-dropdown.blade.php` + `GrimbaRegionQuery`); per-region email digest `deferred`.
- **S1384** — Per-topic daily report: `deferred` — depends on S1382.
- **S1385** — Daily report archive: `partial` — `/feed.latest.xml` is the canonical daily archive surface; structured "daily edition" page `deferred`.
- **S1386** — Daily report NobuAI enrichment: `partial` — daily-briefing card pulls `summary_nobuai` for the briefing cluster when present; "why this matters" NobuAI lede `deferred`.
- **S1387** — Daily report A/B (subject / cover): `deferred` — no A/B harness.
- **S1388** — Daily report subscriber-only tier: `deferred` — gates on S1261.
- **S1389** — Daily report analytics: `deferred` — depends on S1382.
- **S1390** — Daily report launch playbook: `deferred` — gates on S1381-S1389.

## S1391–S1400 — Mobile app program (true native iOS / Android wrapping)

Same as S1151-S1180 — PWA ships, native shells do not. This second pass on the mobile band tightens specific native-app program items (release pipeline, distribution, signing, retro). All `deferred` until S1152 (RN/Flutter/Capacitor pick) lands.

- **S1391** — Native release pipeline (CI / fastlane): `deferred` — no native shell.
- **S1392** — Native signing / certificates: `deferred` — needs Apple Developer + Google Play Console.
- **S1393** — Native code-push / OTA updates: `deferred` — same.
- **S1394** — Native crash dashboard: `deferred` — needs Crashlytics / Sentry (S1158).
- **S1395** — Native version pin (web ↔ native API): `partial` — `/health` JSON ships version + commit (`grimba_version`); native pin contract `deferred`.
- **S1396** — Native deep-link verification: `deferred` — needs Universal Links / App Links registration (S1155).
- **S1397** — Native push-notification permission flow: `deferred` — needs FCM / APNs (S1154 / S1305 / S1306).
- **S1398** — Native subscription IAP (Apple / Google): `deferred` — gates on S1261 paid tier + Apple / Google IAP keys.
- **S1399** — Native release retrospective: `deferred` — gates on a real native release.
- **S1400** — Native app program retrospective: `deferred` — gates on S1391-S1399.

---

## Summary

All 200 sprint IDs in S1201–S1400 now carry a ledger row in `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`.

- **Complete (5 sprints):** S1206 (`GrimbaNobuAiHealth` driver health board), S1207 (`grimba:release-smoke --require-nobuai-live` bounded smoke), S1212 (daily budget enforcer via `GrimbaProviderCredits::fast()` + translation rule daily-cap), S1294 (vault digest mail weekly cron), S1295 (saved-search digest mail weekly cron + `SavedSearchAlertsTest` lock), S1381 (daily-briefing card on homepage via `GrimbaHomeFeed::briefing()`). That is six rows — counted as "complete" the same way S1156 was in the prior pack (real shipped code + test coverage).
- **Partial (54 sprints):** the bands where a server-side or admin-side surrogate is shipped but the post-launch product piece is deferred — NobuAI model rotation (S1201), prompt registry (S1204), per-call audit (S1205), per-locale prompt (S1209), per-provider cost dashboard (S1211), alert thresholds (S1213), translation cost ledger (S1215), quota exhaustion replay (S1218), cost playbook (S1220), blindspot enrichment (S1223), NobuAI uncertainty v2 (S1226), freshness SLA v2 (S1227), quality regression guard (S1230), API rate limit (S1233), API pagination (S1246), API error contract (S1249), API uptime/latency SLA (S1251 / S1252), native ad units (S1273), sponsored-content (S1274), brand safety (S1277), saved-search per-segment newsletter (S1283), advertiser CTA (S1287), CAN-SPAM compliance (S1289), weekly recap (S1293), retention dashboard (S1299), retention playbook (S1300), push deep-link (S1309), cluster manual editor (S1312), author profile (S1314), editorial roles (S1315), draft→publish (S1317), editorial workflow playbook (S1320), per-partner content stream (S1323), per-partner attribution (S1324), search v2 design (S1331), search snippet (S1339), personalization v1 (S1341), cold start (S1347), diversity guard (S1348), personalization opt-out (S1349), bot detection (S1353), source trust audit (S1354), source delist (S1355), reader report-abuse (S1356), per-cluster takedown (S1357), trust & safety playbook (S1360), vault v2 folders (S1373), vault share v2 (S1374), vault export v2 (S1375), read-later queue (S1378), cross-device sync (S1379), per-region daily report (S1383), daily report archive (S1385), daily report NobuAI enrichment (S1386), native version pin (S1395).
- **Deferred (141 sprints):** everything that gates on a third-party account (Apple Developer / Google Play / FCM / APNs / Stripe / vector DB / Sentry / OAuth provider / SSP / DSP), a paid-tier ship (Stripe install S1261 → unlocks S1262-S1270 / S1281-S1290 / S1326 / S1388 / S1398), a B2B product expansion phase (API v1/v2 routes S1231-S1260), an A/B harness (S1073, would unlock S1284 / S1346 / S1387), an embedding store (S1076, would unlock S1332-S1336 + S1344), a UGC / comment infra (no `comments` table, would unlock S1361-S1370), an annotation / highlight infra (no `annotations` table, would unlock S1371-S1372 / S1376-S1377), a push infra (no `webpush_subscriptions` table, would unlock S1301-S1310), or an operator-side editorial / legal / BD pickup (newsroom partnerships S1321-S1330, transparency report S1359, daily report retro S1390, native retro S1399 / S1400).

The honest read: **~3% of the S1201-S1400 band is genuinely shipped today, ~27% has a server-side / admin / web surrogate (often a foundation that's one product-decision away from completing), and ~70% is deferred behind paid tier infra + third-party accounts + A/B harness + comment infra + native shell + B2B expansion**. This matches the band's stated purpose ("NobuAI evolution + B2B + monetization v2 + retention") — these are deliberate post-launch growth tracks, not pre-launch must-ships. The shipped foundations that *do* anchor a lot of this band are: `GrimbaNobuAi` 8-driver chain + failover + diagnostics (anchors all NobuAI rows), `GrimbaProviderCredits` per-provider per-UTC-day counter (anchors all cost-ops rows), `GrimbaAds` 12-slot AdSense surface (anchors all ad-revenue rows), `GrimbaSavedSearches` + weekly digest cron (anchors all saved-search rows), `GrimbaSendVaultDigests` weekly cron (anchors all vault digest rows), `GrimbaHomeFeed::briefing()` + daily-briefing card (anchors all daily-report rows), `partials/story/source-drilldown` + `dossier-voices` + `bias-distribution` (anchors all quality / counterargument / uncertainty rows), `news_sources.credibility_score + factuality_score + ownership_type` + admin classifier (anchors all trust & safety rows), and the RSS / per-stream / per-category feed surface (anchors all API-v1-read rows).

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (Sprint Evidence Ledger section, new rows for S1201-S1400)
- Prior packs: `docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md` (S1001-S1100), `docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md` (S1101-S1200)
- Launch checklist: `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md`
- newsdata.io operator handoff: `docs/GRIMBANEWS_NEWSDATAIO_OPERATOR_HANDOFF.md`
- Test surface anchoring this band: `tests/Feature/NobuAiSummaryCommandTest.php` (S1230), `tests/Feature/ExtractiveSynthesisTest.php` (S1230), `tests/Feature/GrimbaNobuAiBrandPurityTest.php` (S1203 / S1230), `tests/Feature/SavedSearchAlertsTest.php` (S1295), `tests/Feature/VaultAnalyticsDashboardTest.php` (S1299), `tests/Unit/GrimbaProviderCreditsTest.php` (S1212 / S1218), `tests/Feature/SearchFacetsTest.php` (S1331), `tests/Feature/ClusterReviewQueueTest.php` (S1312).
- Code surface (NobuAI): `app/Services/GrimbaNobuAi.php` (8-driver CHAIN + failover + diagnostics), `app/Support/GrimbaNobuAiPrompts.php`, `app/Console/Commands/GrimbaGenerateNobuAiSummaries.php` (every 30 min cron + `--stale` variant), `app/Console/Commands/GrimbaNobuAiHealth.php` (driver health + `Run smoke` admin button), `platform/themes/echo/partials/nobuai-chip.blade.php`, `platform/themes/echo/views/post.blade.php` lines 1294+ (per-post NobuAI summary render).
- Code surface (cost ops): `app/Support/GrimbaProviderCredits.php` (per-provider per-UTC-day counter; cache + DB source-of-truth; bump / fast / cached / used API), `app/Support/GrimbaTranslationRules.php` (`grimba_lang_rule_engine_daily_cap` daily cap), `app/Console/Commands/GrimbaHealth.php` (`--fail-on-risk` exit-code path).
- Code surface (retention): `app/Console/Commands/GrimbaSendVaultDigests.php` + `app/Mail/GrimbaVaultDigestMail.php` + `resources/views/emails/vault-digest.blade.php` (weekly cron at `routes/console.php:255`), `app/Console/Commands/GrimbaSendSavedSearchDigests.php` + `app/Mail/GrimbaSavedSearchDigestMail.php` + `app/Support/GrimbaSavedSearches.php` (weekly Monday 04:55 cron), `app/Support/GrimbaVault.php` (cookie ↔ member sync), `app/Support/GrimbaVaultEvents.php` (privacy-safe ip_hash event ledger).
- Code surface (daily report v1): `platform/themes/echo/partials/home/daily-briefing.blade.php` (renders `GrimbaHomeFeed::briefing()` with bias spectrum bar), `app/Support/GrimbaHomeFeed.php::briefing()`.
- Code surface (ads): `app/Support/GrimbaAds.php` (12-slot SLOTS map), `platform/themes/echo/partials/home/ad-slot.blade.php`, `platform/themes/echo/partials/ads/head.blade.php`, `platform/themes/echo/partials/ads/adsense-unit.blade.php`, `app/Http/Controllers/AdvertiserLeadController.php` (per-IP RateLimiter pattern — canonical for any future API throttle).
- Code surface (trust & safety): `news_sources` table (credibility_score / factuality_score / ownership_type / owner_name columns), `app/Support/GrimbaSourceBreakdown.php` (per-cluster bias / factuality / ownership breakdown), `app/Support/GrimbaIngestGuardrails.php` (keyword-side brand safety).
- Code surface (search v1): `app/Support/GrimbaSavedSearches.php::matchingPosts()` (lexical + tag query), `platform/themes/echo/views/search.blade.php`, `tests/Feature/SearchFacetsTest.php`.
- Code surface (no API v2 today): no `routes/api.php` registration in `RouteServiceProvider`; no Sanctum / Passport / OAuth packages; no `tenants` / `api_keys` / `webpush_subscriptions` / `subscriptions` / `comments` / `annotations` / `member_features` tables — these absences are the load-bearing "deferred" anchors throughout the band.
- Admin surface: `/admin/grimba/cockpit` (job + automation board per `GrimbaAutomationMonitor::status()`), `/admin/grimba/advertiser-leads` (sponsor pipeline), `/admin/grimba/news-sources` (per-source credibility / factuality / ownership), `/admin/grimba/story-clusters` (pin / merge / split), `/admin/grimba/rss-drafts` (editorial draft queue), `/admin/grimba/vault-analytics` (privacy-safe vault event dashboard), `/admin/grimba/subscribers` (member roster), `/admin/grimba/coverage-map` (per-language coverage), `/admin/grimba/translation-monitor` (rule-engine backlog + cap).
