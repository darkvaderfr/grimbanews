# Mythos S1601–S1800 — Local v2 + Tools (Journalists / Educators / Researchers) + Data & ML Platform + Per-Region Editorial v2 + Reader Literacy v2 Evidence Pack

**Status:** evidence reconciliation
**Created:** 2026-05-22
**Author:** Wave FFFFFFFFFF batch close (fifth Mythos post-launch band)
**Scope:** Converts the fifth 200-sprint slice of the Mythos S1001–S2237 post-launch arc — Local v2 (per-city deep landing, admin taxonomy, France/Africa/UK/US/Canada pilots), Tools for journalists (embed widgets, bias chart embeds), Tools for educators (classroom view), Tools for researchers (dataset CSV exports, API rate tier), Data & ML platform (vector store, ML feature store, A/B test harness, analytics warehouse, observability v2), per-region editorial v2 (Africa / International), and reader literacy v2 (bias-bar tutorial, fact-check primer, methodology video) — into ledger rows pointing at real shipped code, third-party-account deferreds, post-launch product expansions, and operator pickups.

This pack feeds the master `Sprint Evidence Ledger` in `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`. The 200 sprint IDs in S1601–S1800 now have a ledger row.

The honest split is **overwhelmingly `deferred`**, with a modest tail of `partial` rows backed by shipped surrogates. The S1601-S1800 band is by design a post-launch product-expansion arc — most of these features are explicitly gated on either (a) a paid tier (`S1211`+ monetization) that doesn't exist yet, (b) an embedding / vector store (`S1076`+) that requires infra not yet provisioned, (c) an A/B harness (`S1073`) that has not been wired, (d) an analytics warehouse / event-bus that would replicate `vault_events` + `grimba_automation_runs` into a query layer, (e) a third-party account (Discourse / Mixpanel / OneTrust / academic-API gating service) the operator hasn't created, or (f) a multimedia production pipeline (video / podcast) that GrimbaNews has not started.

The genuinely shipped pieces in this band are the **`/local` geo-personalized landing route** (IP geolocation via `GrimbaGeoLocator` cascade with cookie persistence), the **`Regions` four-bucket editorial split** (`africa` / `europe` / `americas` / `international` with per-region landing + per-region home-feed scope), the **France-source coverage** (FR / Le Monde / RFI / France 24 / Le Monde Afrique already populated via `RssFeedsSeeder` + `GrimbaSeedImmigrationSources`), the **Africa pilot substrate** (54-country `Regions::AFRICA`, `GrimbaArticleRegion` topical anchors for African capitals + demonyms, `GrimbaBackfillEditorialRegions` + `GrimbaRetagEditorialRegionByTopic` commands), the **vault-event CSV archive** (`grimba:archive-vault-events` → `storage/exports/vault_events_YYYY-MM.csv`), the **personal-history CSV exports** (`/pour-vous/export.csv` + `/coffre/export.csv`), the **per-job duration tracking** (`grimba_automation_runs.duration_ms`), and the **methodology page** (`/methodologie` with TechArticle JSON-LD).

The S001–S1000 pre-launch arc is the launch gate; the S1601-S1800 work is staged post-launch product depth. The valuable evidence is that the foundations (`/local` route + `GrimbaGeoLocator` + `grimba_local_*` cookies; `Regions::AFRICA/EUROPE/AMERICAS/international`; `GrimbaArticleRegion` topical-region detector; `grimba_automation_runs` per-job latency table; `vault_events` privacy-safe ledger + monthly CSV archive; `/pour-vous/export.csv` + `/coffre/export.csv`; `/methodologie` + `/explainer-bias-bar` explainer routes) are in place so each deferred row drops into a working substrate the moment the missing tier / vendor / multimedia pipeline is provisioned.

---

## S1601–S1610 — Local v2 — per-city deep landing (geolocation, custom feed, regional sources)

The `/local` route ships today as a working geo-personalized landing (`platform/themes/echo/routes/web.php:1538-1594` `public.local` handler) with `GrimbaGeoLocator` IP-cascade (ip-api.com → ipapi.co), 1-year cookie persistence (`grimba_local_city` / `grimba_local_country` / `grimba_local_cc`), source-country filter, and city keyword scan. A "deep" per-city landing (dedicated `/local/{city-slug}` with city editorial briefs, follow-city control, per-city RSS, city sponsor inventory) is **not yet shipped** — the current surface is one shared route that personalizes a single posts list.

- **S1601** — Per-city deep landing route: `partial` — `/local` ships at `platform/themes/echo/routes/web.php:1538-1594` with `Theme::scope('local', …)` → `platform/themes/echo/views/local.blade.php`; per-city slug routes (`/local/{slug}`) `deferred`.
- **S1602** — Per-city geolocation: `complete` — `App\Services\GrimbaGeoLocator::locate()` cascades ip-api.com → ipapi.co with 24h `Cache::remember`; returns `{city, country, country_code, region, lat, lon}`; localhost / 192.168.* short-circuited; raw IP never persisted (only `sha1` cache key).
- **S1603** — Per-city custom feed: `partial` — `/local` filters posts by `source_id IN (news_sources WHERE country = cc)` + city keyword `LIKE` against `name` / `description` (max 36 results). City-only feed (no source-country gate) `deferred`.
- **S1604** — Per-city regional sources list: `partial` — `news_sources.country` (ISO-2) + `news_sources.city` slot exists at the source level via `GrimbaSourceClassifier`; explicit per-city source-pool admin UI `deferred`.
- **S1605** — Per-city cookie persistence: `complete` — `Route::post('local/set', …)` at `platform/themes/echo/routes/web.php:1597-1610` persists `grimba_local_city` / `grimba_local_country` / `grimba_local_cc` cookies for 1 year (`60*24*365` min); `bootstrap/app.php:31-33` whitelists all three cookies for the encrypted-cookies pipeline.
- **S1606** — Per-city consent posture: `complete` — IP geolocation only fires when both `grimba_local_city` and `grimba_local_country` cookies are empty (manual selection short-circuits geo); raw IP never lands on disk (only the `sha1`-trunc-12 cache key).
- **S1607** — Per-city manual override: `complete` — `Route::post('local/set', …)` accepts `city` / `country` / `cc` form fields and writes cookies; UI surface in `platform/themes/echo/views/local.blade.php`.
- **S1608** — Per-city dedicated landing copy / editorial brief: `deferred` — no `local_cities` table, no per-city brief CMS; the single `/local` view shares one heading template.
- **S1609** — Per-city sponsor / advertiser slot: `deferred` — `app/Support/GrimbaAds.php` ships single global ad slot; per-city ad-targeting `deferred`.
- **S1610** — Per-city launch playbook: `deferred` — operator-side editorial playbook; gates on S1601 + S1608 + S1609.

## S1611–S1620 — Local v2 — admin (city/region taxonomy, local source priority)

The taxonomy substrate exists in `app/Ground/Regions.php` (4-region editorial split — africa / europe / americas / international — with 54 / 48 / 35 ISO-2 country lists). There is no per-city taxonomy admin, no "local source priority" admin (operator can edit `news_sources.country` + `editorial_category` directly via `/admin/grimba/news-sources` but no dedicated "boost this source for /local in this city" surface).

- **S1611** — City taxonomy schema: `deferred` — no `local_cities` table; only `news_sources.city` slot exists at the source level.
- **S1612** — City taxonomy admin UI: `deferred` — depends on S1611.
- **S1613** — Region taxonomy schema: `complete` — `app/Ground/Regions.php` is single source of truth (54 AFRICA + 48 EUROPE + 35 AMERICAS + negative INTERNATIONAL filter via `otherNamedCodes()`); `Regions::regionForCountry($iso2)` classifies sources at ingest; `posts.editorial_region` + `posts.editorial_secondary_region` columns hold the tag.
- **S1614** — Region taxonomy admin UI: `partial` — operator can override `posts.editorial_region` via Botble post-edit; `news_sources.editorial_category` editable via `/admin/grimba/news-sources`. Dedicated region-taxonomy admin `deferred`.
- **S1615** — Local source priority schema: `partial` — `news_sources.country` + `editorial_category` + `credibility_score` define source pool today; explicit `local_priority` column `deferred`.
- **S1616** — Local source priority admin: `deferred` — depends on S1615; operator surrogate is `/admin/grimba/news-sources` for direct edit.
- **S1617** — Local source backfill command: `complete` — `app/Console/Commands/GrimbaBackfillSourceCountries.php` infers `news_sources.country` from website TLD + `GrimbaSourceCountryBackfill::DOMAIN_COUNTRIES` lookup (~300+ domain → country mappings); `SourceCountryBackfillCommandTest` locks contract.
- **S1618** — Local source classification scheduler: `complete` — `app/Console/Commands/GrimbaClassifySources.php` runs scheduled classification; `news_sources.bias_rating` / `factuality_score` / `country` / `editorial_category` populated; `SourceClassifierCommandTest` covers.
- **S1619** — Local source coverage map admin: `partial` — `/admin/grimba/news-sources/coverage-map` ships per `CoverageMapAdminTest`; per-city drill-in `deferred`.
- **S1620** — Local v2 admin launch playbook: `deferred` — gates on S1611-S1619; operator-side editorial playbook.

## S1621–S1630 — Local v2 — France pilot (DOM-TOM, overseas territories, language variants)

France is the launch-baseline market. FR sources are populated (Le Monde / Le Figaro / Libération / Le Point / France 24 / RFI / France-Guyane via `GrimbaSourceClassifier::DOMAIN_PROFILES`); FR is the canonical UI locale via `lang/fr.json` + `GrimbaLocaleEnforce::PRIMARY_LOCALES`. DOM-TOM / overseas-territory editorial brackets (Réunion / Guadeloupe / Martinique / Guyane / Mayotte / Nouvelle-Calédonie / Polynésie) are **not separately bucketed** — they fold into `country='FR'` today.

- **S1621** — France pilot — source coverage: `complete` — `news_sources` ships Le Monde, Le Figaro, Libération, Le Point, France 24, RFI, Le Monde Afrique, France-Guyane (per `GrimbaSourceClassifier::DOMAIN_PROFILES`) + `RssFeedsSeeder` registers the canonical FR RSS endpoints.
- **S1622** — France pilot — DOM-TOM editorial bucket: `deferred` — no separate `editorial_region='dom-tom'`; surrogate is `France-Guyane` source pinned `country='FR'` per `GrimbaSourceClassifier`. DOM-TOM-specific landing `deferred`.
- **S1623** — France pilot — overseas-territory source pickup: `partial` — `GrimbaSourceCountryBackfill::DOMAIN_COUNTRIES` does not yet enumerate Réunion / Guadeloupe / Martinique TLDs (`.re`, `.gp`, `.mq`); operator pickup via `RssFeedsSeeder`.
- **S1624** — France pilot — language variant (FR vs FR-Canadian): `deferred` — `posts.original_language='fr'` is single-bucket; FR-CA detection `deferred` per `GrimbaLanguageDetector` (covers FR but not FR-CA dialect).
- **S1625** — France pilot — `/local` city pool (Paris / Lyon / Marseille / Bordeaux / Toulouse / Nice / Lille / Strasbourg / Nantes / Rennes): `partial` — `/local` city keyword `LIKE` scans `posts.name` + `posts.description`; no pre-populated city pool dropdown.
- **S1626** — France pilot — hreflang locking: `complete` — `GrimbaLocaleEnforce::PRIMARY_LOCALES = ['fr','en']` enforces FR canonical; `platform/themes/echo/layouts/grimba-chrome.blade.php` emits `hreflang="fr"` + `hreflang="en"` on every public page.
- **S1627** — France pilot — Méthodologie page in FR: `complete` — `/methodologie` ships as primary FR surface per `platform/themes/echo/views/methodology.blade.php` + TechArticle JSON-LD; EN translations added via Wave LLLLLLLLL + WWWWWWWWW (2026-05-22).
- **S1628** — France pilot — newsletter FR locale: `partial` — `newsletter_subscriptions` table per `NewsletterBiasSignalTest`; subscribe form ships in FR/EN per Wave CCCCCCCCCC locale-aware email placeholders; per-DOM-TOM list `deferred`.
- **S1629** — France pilot — FR advertiser leads pipeline: `complete` — `app/Mail/GrimbaAdvertiserLeadNotification.php` + `grimba_advertiser_leads_sales_mailbox` setting routes per-region; `GrimbaAdvertiserLeadsTest` covers.
- **S1630** — France pilot launch retrospective: `deferred` — operator-side retro; gates on S1622-S1625 DOM-TOM coverage.

## S1631–S1640 — Local v2 — Africa pilot (per-country feed, francophone Africa, lusophone)

Africa is a launch-priority market per Vader directive (Wave EE / `IBOGA_VENTURES_BUSINESS_PLAN` / `GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md`). Substrates are strong: 54-country `Regions::AFRICA`, `GrimbaArticleRegion::ANCHORS['africa']` covers ~30 capitals + demonyms in FR + EN, `GrimbaBackfillEditorialRegions` + `GrimbaRetagEditorialRegionByTopic` retag posts by topical signal (Mali / Senegal / Cameroon stories no longer mis-routed to EUROPE). Per-country dedicated landings (`/africa/{iso2}`) `deferred`.

- **S1631** — Africa pilot — region landing: `complete` — `/africa` route per `GrimbaHomeFeed` region scoping; reads `grimba_region='africa'` cookie via `GrimbaRegionQuery::selectedRegion()` + `Regions::AFRICA` 54-country filter.
- **S1632** — Africa pilot — per-country feed: `partial` — `news_sources.country` (e.g. SN / ML / CI / CM) + `GrimbaArticleRegion::ANCHORS['africa']` topical match cover per-country source resolution; dedicated `/africa/{iso2}` route `deferred`.
- **S1633** — Africa pilot — francophone Africa source pool: `partial` — Le Monde Afrique + RFI + France 24 + UNHCR + La Cimade seeded via `GrimbaSeedImmigrationSources` + `GrimbaSeedThinCategorySources`; per-country francophone sources (`seneweb.com`, `lefaso.net`, `cameroonweb.com`) require operator RSS pickup.
- **S1634** — Africa pilot — lusophone Africa source pool: `deferred` — Angola / Mozambique / Cabo Verde / Guiné-Bissau PT-language sources not seeded; `posts.original_language='pt'` detector covers per `GrimbaLanguageDetectorTest` but UI locale catalog `lang/pt_BR.json` `deferred` per S1111.
- **S1635** — Africa pilot — anglophone Africa source pool: `partial` — `Regions::AFRICA` includes `NG` / `KE` / `GH` / `ZA` / `TZ` / `UG` / `ZW` / `ZM` / `MW` / `RW` / `ET` / `RW`; operator pickup via `RssFeedsSeeder` of `nation.africa`, `dailymaverick.co.za`, `premiumtimesng.com` etc.
- **S1636** — Africa pilot — swahili / arabic source pool: `deferred` — Swahili (`sw`) detector path + `lang/sw.json` `deferred` per S1139; Arabic (`ar`) covers North Africa but `lang/ar.json` + RTL chrome `deferred` per S1132 + S1142.
- **S1637** — Africa pilot — Africa-edition newsletter: `partial` — `newsletter_subscriptions.bias_signal` per-region segmentation exists per `NewsletterBiasSignalTest`; explicit `edition='afrique'` toggle `deferred`.
- **S1638** — Africa pilot — Africa-edition advertiser inventory: `partial` — `grimba_advertiser_leads.source_pack_tier` (per `add_source_pack_tier_to_grimba_advertiser_leads_table` migration 2026-05-18) bands inventory; Africa-pack `deferred`.
- **S1639** — Africa pilot — Africa-edition retrospective doc: `partial` — `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md` is the editorial brief; per-pilot results retro `deferred`.
- **S1640** — Africa pilot launch retrospective: `deferred` — operator-side editorial retro; gates on S1632-S1638.

## S1641–S1650 — Local v2 — UK / US / Canada pilot

`Regions::AMERICAS` includes CA / US / MX; `Regions::EUROPE` includes GB. Source coverage exists for GB / US / CA via `GrimbaSourceClassifier::DOMAIN_PROFILES` (BBC / Guardian / NYT / WaPo / CBC / Globe and Mail) but no English-language ad-pack, no per-pilot newsletter, no per-pilot landing copy.

- **S1641** — UK pilot — source coverage: `partial` — `news_sources` covers BBC + Guardian + Independent + Telegraph + Sky News + Reuters per `GrimbaSourceClassifier::DOMAIN_PROFILES` (subset); operator pickup for full UK pool via `RssFeedsSeeder`.
- **S1642** — UK pilot — `/local` city pool (London / Manchester / Edinburgh / Glasgow / Birmingham / Liverpool / Cardiff / Belfast): `partial` — `/local` city keyword scan covers any UK city when geolocation resolves `country_code='GB'`; no pre-populated dropdown.
- **S1643** — UK pilot — UK-edition newsletter: `deferred` — `newsletter_subscriptions` table has no per-edition column; surrogate is `bias_signal` segmentation.
- **S1644** — US pilot — source coverage: `partial` — NYT / WaPo / WSJ / Fox / CNN / Reuters US covered via `GrimbaSourceClassifier::DOMAIN_PROFILES` subset; full US pool requires operator `RssFeedsSeeder` pickup.
- **S1645** — US pilot — `/local` city pool (NYC / LA / Chicago / Houston / Phoenix / Philadelphia / San Antonio / San Diego / Dallas / Miami): `partial` — same shape as S1642; city keyword scan covers any US city when geolocation resolves `country_code='US'`.
- **S1646** — US pilot — US-edition newsletter: `deferred` — same shape as S1643.
- **S1647** — Canada pilot — source coverage: `partial` — CBC / Globe and Mail / National Post / Radio-Canada / La Presse / Le Devoir covered via `GrimbaSourceClassifier::DOMAIN_PROFILES`; operator pickup for full FR-CA + EN-CA pool.
- **S1648** — Canada pilot — bilingual FR / EN routing: `complete` — `GrimbaLocaleEnforce::PRIMARY_LOCALES = ['fr','en']` covers both; CA geolocation routes per `grimba_local_country='Canada'` cookie.
- **S1649** — Canada pilot — Quebec / FR-CA dialect handling: `deferred` — `GrimbaLanguageDetector` returns `'fr'` for both FR-FR and FR-CA; per-dialect routing `deferred`.
- **S1650** — UK / US / Canada pilot launch retrospective: `deferred` — operator-side editorial retro.

## S1651–S1660 — Tools for journalists — embed widget (story-cluster card embeddable)

GrimbaNews has no `/embed/{cluster-id}` route, no iframe-friendly stripped chrome, no embed-token API. The `GrimbaArticleExtractor` strips iframes on ingest (line 110), which is the inverse of what an embed product would ship. Surrogate for external journalist use: per-cluster `/dossier/{id}` deep link with `og:image` + `twitter:summary_large_image` (so social embeds render a card).

- **S1651** — Embed widget — `/embed/{cluster-id}` route: `deferred` — no embed route shipped.
- **S1652** — Embed widget — iframe-friendly stripped chrome layout: `deferred` — no `embed.blade.php` layout; `grimba-chrome.blade.php` ships full nav.
- **S1653** — Embed widget — JS-snippet generator (`<script src="grimbanews.com/embed.js?cluster=…">`): `deferred` — no embed.js bundle.
- **S1654** — Embed widget — embed-token API (rate-limit per publisher): `deferred` — no `embed_tokens` table; no public API yet (per S1181-S1190).
- **S1655** — Embed widget — embed CSS isolation (Shadow DOM or scoped CSS): `deferred` — depends on S1653.
- **S1656** — Embed widget — embed analytics (per-embed impressions): `deferred` — no `embed_impressions` table.
- **S1657** — Embed widget — embed click-through tracking: `deferred` — same.
- **S1658** — Embed widget — embed branding ("Powered by NobuAI / GrimbaNews"): `deferred` — depends on S1653; brand-purity locked by `GrimbaNobuAiBrandPurityTest` (user-facing copy says "NobuAI" never "Anthropic" / "Claude" / "OpenAI").
- **S1659** — Embed widget — embed responsive sizing: `deferred` — depends on S1653.
- **S1660** — Embed widget — embed launch playbook: `deferred` — gates on S1651-S1659.

## S1661–S1670 — Tools for journalists — bias chart embed

The bias-distribution chart exists in `platform/themes/echo/partials/story/bias-distribution.blade.php` (server-rendered SVG/HTML), wired via `app/Support/GrimbaSourceBreakdown.php` (counts per bias bucket per cluster) and `app/Support/GrimbaClusterBias.php` (dominant-bias + L+R percentage). An embeddable, standalone variant `/embed/bias-chart/{cluster-id}` `deferred` for the same reasons as the S1651 widget.

- **S1661** — Bias chart embed — `/embed/bias-chart/{cluster-id}` route: `deferred` — same root reason as S1651.
- **S1662** — Bias chart embed — standalone bias-chart partial: `partial` — `platform/themes/echo/partials/story/bias-distribution.blade.php` ships the chart; standalone iframe-friendly variant `deferred`.
- **S1663** — Bias chart embed — server-side source-of-truth: `complete` — `app/Support/GrimbaSourceBreakdown.php::resolve(Post $cluster)` returns `{left, center, right, unknown}` counts + percentages; `app/Support/GrimbaClusterBias.php` returns dominant + L+R%; `GrimbaSourceBreakdownTest` locks contract.
- **S1664** — Bias chart embed — SVG export: `deferred` — current chart is HTML+CSS, not exported SVG.
- **S1665** — Bias chart embed — PNG export: `deferred` — would need server-side HTML→PNG rasterizer (not provisioned).
- **S1666** — Bias chart embed — embed parameters (`?style=compact|wide`): `deferred` — depends on S1661.
- **S1667** — Bias chart embed — embed click-through ("see full dossier on GrimbaNews"): `deferred` — depends on S1661.
- **S1668** — Bias chart embed — embed accessibility (alt text + table fallback): `partial` — current chart ships table-fallback markup per a11y baseline locked by `tests/e2e/grimbanews-keyboard-navigation.cjs`; standalone embed variant `deferred`.
- **S1669** — Bias chart embed — embed bot-detection (no headless-Chrome scraping): `deferred` — would gate on S1654 token system.
- **S1670** — Bias chart embed — launch playbook: `deferred` — gates on S1661-S1669.

## S1671–S1680 — Tools for educators — classroom view (no-ads, simplified UI, teacher-curated lists)

No `/classroom` route, no `educator_seats` table, no teacher-account scope, no curated-list primitive scoped to a teacher. Surrogate: ad-slot rendering is gated by `app/Support/GrimbaAds.php::shouldRender()` so an env-flag could disable site-wide; reader-side reading-list primitive exists via `app/Support/GrimbaVault.php` (cookie + member sync) but per-classroom sharing `deferred`.

- **S1671** — Classroom — `/classroom` route: `deferred` — no classroom route.
- **S1672** — Classroom — no-ads mode: `partial` — `app/Support/GrimbaAds.php::shouldRender()` is the single gate; a `?no-ads=1` query-param or `member.role='educator'` short-circuit `deferred`.
- **S1673** — Classroom — simplified UI layout: `partial` — `platform/themes/echo/layouts/grimba-chrome.blade.php` is the global shell; classroom-stripped variant `deferred`.
- **S1674** — Classroom — teacher-account schema: `deferred` — no `educator_seats` / `classrooms` / `students` tables; Botble member auth single-role.
- **S1675** — Classroom — teacher-curated list primitive: `partial` — `app/Support/GrimbaVault.php` (cookie + member sync via `members.vault_digest_post_ids`) is the surrogate single-user reading list; per-classroom share `deferred`.
- **S1676** — Classroom — teacher-share link (read-only): `partial` — `/coffre/share` route ships at `platform/themes/echo/views/coffre-share.blade.php` for one-off vault share; teacher → student-list semantics `deferred`.
- **S1677** — Classroom — student progress dashboard: `deferred` — no `student_reads` table.
- **S1678** — Classroom — assignment primitive: `deferred` — no `assignments` table.
- **S1679** — Classroom — teacher discount tier: `deferred` — no paid tier (lands with S1211).
- **S1680** — Classroom — launch playbook: `deferred` — gates on S1671-S1679.

## S1681–S1690 — Tools for researchers — dataset CSV exports (per-source, per-cluster, per-day)

CSV exports exist today for two reader-side datasets: `/pour-vous/export.csv` (cookie-only read history, see `platform/themes/echo/routes/web.php:1235-1287`) and `/coffre/export.csv` (vault picks). For researcher-targeted dataset exports (per-source / per-cluster / per-day rollups), the most-shipped surrogate is the monthly vault-events archive (`app/Console/Commands/GrimbaArchiveVaultEvents.php` → `storage/exports/vault_events_YYYY-MM.csv`).

- **S1681** — Dataset — per-source CSV export: `partial` — operator-side via Botble admin `news_sources` table CSV export (Botble base-table); dedicated researcher endpoint `deferred`.
- **S1682** — Dataset — per-cluster CSV export: `deferred` — no `/datasets/clusters.csv` endpoint; surrogate is per-cluster `/dossier/{id}` API-shape JSON via `seo-meta-config.blade.php`.
- **S1683** — Dataset — per-day CSV export: `deferred` — no `/datasets/daily.csv` endpoint.
- **S1684** — Dataset — read-history CSV export: `complete` — `Route::get('pour-vous/export.csv', …)` at `platform/themes/echo/routes/web.php:1235-1287` streams the per-reader read-history CSV with `Cache-Control: no-store` + `X-GN-Privacy: cookie-only-no-server-record` header; UTF-8 BOM for Excel.
- **S1685** — Dataset — vault picks CSV export: `complete` — `Route::get('coffre/export.csv', …)` at `platform/themes/echo/routes/web.php:1913+` streams the vault picks CSV.
- **S1686** — Dataset — vault-events monthly CSV: `complete` — `app/Console/Commands/GrimbaArchiveVaultEvents.php` archives `vault_events` to `storage/exports/vault_events_YYYY-MM.csv` (privacy-preserving: ip_hash only, no raw IP); scheduled via `GrimbaAutomationMonitor` `vault_events_archive` job.
- **S1687** — Dataset — dataset license / terms-of-use page: `deferred` — no `/datasets/license` page; surrogate is `/methodologie` (open + revisable).
- **S1688** — Dataset — dataset citation guidance: `deferred` — depends on S1687 + paper review (S1718).
- **S1689** — Dataset — dataset versioning: `deferred` — current CSV exports are point-in-time; no `dataset_versions` table.
- **S1690** — Dataset — dataset launch playbook: `deferred` — gates on S1681-S1689.

## S1691–S1700 — Tools for researchers — API rate-tier for academic use

GrimbaNews has no `/api/v2` (or any partner API). The only public read-only data surfaces are `/feed.xml` (+ per-stream `/feed.breaking.xml`, `/feed.latest.xml`), per-category feeds, `/health` JSON, and `/sitemap-grimba.xml`. There is no token system, no per-tier rate limit, no academic-tier signup, no usage dashboard.

- **S1691** — API — `/api/v2` route base: `deferred` — no v2 routes per S1181.
- **S1692** — API — OAuth client / API-key model: `deferred` — no Sanctum / Passport install per S1182.
- **S1693** — API — academic-tier signup: `deferred` — depends on S1692.
- **S1694** — API — academic-tier rate limit (higher than free tier): `deferred` — depends on S1691 + S1692; surrogate is per-IP RateLimiter on advertiser-lead endpoint (`AdvertiserLeadController` 5/10min).
- **S1695** — API — academic-tier usage dashboard: `deferred` — depends on S1691 + S1692.
- **S1696** — API — academic-tier API docs: `deferred` — depends on S1691.
- **S1697** — API — academic-tier citation requirement: `deferred` — depends on S1693.
- **S1698** — API — academic-tier dataset license: `deferred` — same as S1687.
- **S1699** — API — academic-tier renewal cadence: `deferred` — depends on S1693.
- **S1700** — API — academic-tier launch playbook: `deferred` — gates on S1691-S1699.

## S1701–S1710 — Data platform — vector embeddings store for semantic search

No vector / embedding store provisioned today. `S1076` honestly defers this. Substrates that would feed embeddings: `posts.name` + `posts.description` + `posts.full_content` + `posts.summary_nobuai` already populated for ingested posts (storage-side); embedding generation needs an external model (or pgvector / qdrant / pinecone) the operator hasn't picked.

- **S1701** — Vector store — infra pick (pgvector / qdrant / pinecone / weaviate): `deferred` — same as S1076.
- **S1702** — Vector store — schema (`post_embeddings` table with `vector(N)` column): `deferred` — depends on S1701.
- **S1703** — Vector store — embedding-generation pipeline: `deferred` — depends on S1701 + external embedding model.
- **S1704** — Vector store — daily backfill cron: `deferred` — depends on S1703.
- **S1705** — Vector store — incremental update on new post: `deferred` — depends on S1703.
- **S1706** — Vector store — semantic search query handler: `deferred` — same as S1471 semantic-search row (which honestly defers).
- **S1707** — Vector store — semantic-similarity "related dossiers" surface: `partial` — current "related dossiers" chip uses `posts.story_cluster_id` + `editorial_category` + same-day window per `GrimbaRelatedDossiersChipTest`; semantic-vector ranking `deferred`.
- **S1708** — Vector store — semantic-dedup of clusters: `partial` — current `GrimbaArticleDedupe` is canonical-URL + title-similarity per `DedupePostsCommandTest`; vector-dedup `deferred`.
- **S1709** — Vector store — cost dashboard: `deferred` — depends on S1701 + S1703.
- **S1710** — Vector store — launch playbook: `deferred` — gates on S1701-S1709.

## S1711–S1720 — Data platform — ML feature store (per-article + per-source vectors)

No feature store today. Substrates: `posts.bias_rating`, `posts.editorial_category`, `posts.editorial_region`, `posts.original_language`, `posts.story_cluster_id`, `news_sources.credibility_score`, `news_sources.factuality_score`, `news_sources.ownership_type`, `news_sources.owner_name` are all populated and queryable. A formal "feature store" (Feast / Hopsworks / custom) is `deferred`.

- **S1711** — Feature store — per-article feature schema: `partial` — `posts` table already ships `bias_rating` + `editorial_category` + `editorial_region` + `editorial_secondary_region` + `original_language` + `story_cluster_id` + `summary_nobuai` + `summary_nobuai_locale` as raw features; aggregated feature store `deferred`.
- **S1712** — Feature store — per-source feature schema: `partial` — `news_sources` ships `country` + `credibility_score` + `factuality_score` + `bias_rating` + `ownership_type` + `owner_name` + `editorial_category` as raw features; aggregated store `deferred`.
- **S1713** — Feature store — daily snapshot job: `deferred` — depends on S1711 + S1712.
- **S1714** — Feature store — point-in-time consistency: `deferred` — depends on S1713.
- **S1715** — Feature store — feature-versioning: `deferred` — git history is the version pin today (same shape as S1074 prompt-version pinning).
- **S1716** — Feature store — feature-discovery UI: `deferred` — same.
- **S1717** — Feature store — offline / online parity tests: `deferred` — same.
- **S1718** — Feature store — feature-store paper / methodology: `deferred` — depends on S1711-S1717 actually shipping.
- **S1719** — Feature store — feature-store cost dashboard: `deferred` — same.
- **S1720** — Feature store — launch playbook: `deferred` — gates on S1711-S1719.

## S1721–S1730 — Data platform — A/B test harness (experiment registry, traffic splitter)

No A/B harness today (S1073 + S1087 + S1177 honestly defer). Surrogate for traffic-splitter shape: cookie-based segmentation works today (`grimba_region` / `grimba_local_*` / `grimba_read` cookies persist segmentation server-side at render).

- **S1721** — A/B harness — experiment-registry schema: `deferred` — no `experiments` / `experiment_assignments` tables.
- **S1722** — A/B harness — traffic-splitter middleware: `deferred` — depends on S1721.
- **S1723** — A/B harness — variant render hook in Blade: `deferred` — depends on S1722.
- **S1724** — A/B harness — assignment cookie: `deferred` — depends on S1721; cookie-shape proven by existing `grimba_region` / `grimba_local_*` cookies.
- **S1725** — A/B harness — outcome event log: `partial` — `vault_events` is the cookie-only privacy-safe event ledger pattern; experiment-outcome variant `deferred`.
- **S1726** — A/B harness — sequential testing / stop-early stats: `deferred` — depends on S1721-S1725.
- **S1727** — A/B harness — admin experiment console: `deferred` — same.
- **S1728** — A/B harness — feature-flag rollout (per-cohort): `deferred` — same.
- **S1729** — A/B harness — experiment retrospective doc template: `deferred` — same.
- **S1730** — A/B harness — launch playbook: `deferred` — gates on S1721-S1729.

## S1731–S1740 — Data platform — analytics warehouse (anon read events, source dwell time)

`vault_events` is the privacy-safe (ip_hash only) reader-event ledger today (per `database/migrations/2026_05_06_080000_create_vault_events_table.php`); `grimba_automation_runs` is the per-job exec ledger. A formal warehouse (BigQuery / Snowflake / DuckDB / ClickHouse) is `deferred`; the monthly CSV archive (`grimba:archive-vault-events`) is the export path that would feed a warehouse.

- **S1731** — Warehouse — destination pick (BigQuery / Snowflake / DuckDB / ClickHouse): `deferred` — no warehouse provisioned.
- **S1732** — Warehouse — anon read-event schema: `complete` — `vault_events` table ships `event` + `post_id` + `ts` + `ip_hash` (`database/migrations/2026_05_06_080000_create_vault_events_table.php`); privacy posture locked (no raw IP).
- **S1733** — Warehouse — anon read-event ingest: `partial` — `GrimbaVaultEvents` writes events at save/unsave; per-article read-event capture `deferred` (current model: cookie-only `grimba_read` IDs, no server insert per S104 privacy contract).
- **S1734** — Warehouse — source dwell-time capture: `deferred` — no client-side dwell-time beacon; surrogate is per-article presence in `grimba_read` cookie.
- **S1735** — Warehouse — monthly CSV pipeline: `complete` — `app/Console/Commands/GrimbaArchiveVaultEvents.php` writes `storage/exports/vault_events_YYYY-MM.csv` (4-column: event / post_id / ts / ip_hash); scheduled via `GrimbaAutomationMonitor::JOBS['vault_events_archive']`.
- **S1736** — Warehouse — automation-runs CSV pipeline: `partial` — `grimba_automation_runs` is queryable per `GrimbaAutomationMonitor::status()`; CSV export `deferred`.
- **S1737** — Warehouse — dashboard layer (Metabase / Looker / Hex / Superset): `deferred` — depends on S1731.
- **S1738** — Warehouse — quarterly retention policy: `partial` — `GrimbaPruneReleaseEvidence` keeps 30-day rolling window of release-evidence files (`ReleaseEvidencePruneTest`); vault-events archive retention `deferred`.
- **S1739** — Warehouse — cost dashboard: `deferred` — depends on S1731.
- **S1740** — Warehouse — launch playbook: `deferred` — gates on S1731-S1739.

## S1741–S1750 — Data platform — observability v2 (per-route latency, per-job duration)

Per-job duration is fully captured today via `grimba_automation_runs.duration_ms` (`database/migrations/2026_04_28_181500_create_grimba_automation_runs_table.php` + `GrimbaAutomationMonitor::start/finish`); per-route latency is partially captured via `grimba:release-smoke` budgets (homepage 3000ms / `/up` 1500ms / `/health` 1500ms / `/feed.xml` 3000ms per S1006) but not continuously per-request.

- **S1741** — Observability — per-job duration capture: `complete` — `grimba_automation_runs.duration_ms` unsigned int populated by `GrimbaAutomationMonitor::start/finish`; `grimba_automation_runs_status_finished_idx` + `grimba_automation_runs_job_finished_idx` indexes for query.
- **S1742** — Observability — per-job exit-code + error-message capture: `complete` — `grimba_automation_runs.exit_code` + `error_message` text column populated on every run; `GrimbaAutomationMonitor::status()` exposes for cockpit board (`/admin/grimba/cockpit`).
- **S1743** — Observability — per-job last-run dashboard: `complete` — `/admin/grimba/cockpit` reads `GrimbaAutomationMonitor::status()` and renders per-job last-run + status + duration per `platform/themes/echo/functions/grimba-admin-cockpit.php:219+249`.
- **S1744** — Observability — per-job missed-run alert: `complete` — `grimba:health --fail-on-risk` flags missed runs (per S166 + `DailyPublishFreshnessTest`).
- **S1745** — Observability — per-route latency capture: `partial` — `grimba:release-smoke` enforces per-route budgets at release time (per S1006); continuous per-request latency capture `deferred` (would need APM / Sentry per S1013).
- **S1746** — Observability — per-route 4xx / 5xx capture: `partial` — Laravel `app/Exceptions/Handler.php` logs to `storage/logs/laravel.log`; structured per-route capture `deferred`.
- **S1747** — Observability — tracing v2 (per-request trace ID): `partial` — request-ID middleware shipped per Wave (S0911+ security pack); cross-service trace propagation `deferred`.
- **S1748** — Observability — log retention policy: `partial` — `GrimbaPruneReleaseEvidence` 30-day rolling per S999; Laravel log rotation default; formal retention policy doc `deferred`.
- **S1749** — Observability — alerting v2 (Slack / email / PagerDuty webhook): `deferred` — `grimba:health` writes to `grimba_automation_runs` + cockpit board; external alert webhook `deferred` per S1014 / S1019.
- **S1750** — Observability — launch playbook: `deferred` — gates on S1745-S1749.

## S1751–S1760 — Per-region editorial v2 — Africa edition (curators, translators, ad ops)

Africa-edition substrates are strong (per S1631-S1640); the v2 expansion (named per-region curators, dedicated translators, per-region ad ops) is operator-side workflow that has not been staffed. The `posts.editorial_region='africa'` + `editorial_secondary_region` tagging + `GrimbaArticleRegion` topical-anchor sweep + `GrimbaBackfillEditorialRegions` retag-by-source-country + `GrimbaRetagEditorialRegionByTopic` retag-by-topic cover the data side.

- **S1751** — Africa edition v2 — named curator role: `deferred` — operator-side editorial staffing.
- **S1752** — Africa edition v2 — curator admin scope: `deferred` — Botble admin auth is single-role per S1401.
- **S1753** — Africa edition v2 — translator role: `deferred` — `GrimbaTranslator` covers automated FR↔EN via rule engine + LibreTranslate / OpenRouter / DeepL fallback per `GrimbaTranslator::configuredDrivers()`; human translator workflow `deferred`.
- **S1754** — Africa edition v2 — translator queue: `partial` — `app/Console/Commands/GrimbaTranslateByRule.php` + `posts.translation_priority` + `grimba:translate-pending` is the automated queue; human-review queue `deferred`.
- **S1755** — Africa edition v2 — Africa ad-pack: `partial` — `grimba_advertiser_leads.source_pack_tier` (per `add_source_pack_tier_to_grimba_advertiser_leads_table` migration); explicit `tier='africa'` value `deferred`.
- **S1756** — Africa edition v2 — Africa ad-ops dashboard: `deferred` — depends on S1755.
- **S1757** — Africa edition v2 — Africa sponsor inventory: `deferred` — same.
- **S1758** — Africa edition v2 — Africa newsletter cadence (separate from main): `deferred` — `newsletter_subscriptions` table single-edition today.
- **S1759** — Africa edition v2 — Africa monthly editorial report: `partial` — `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md` is the master brief; per-month report `deferred`.
- **S1760** — Africa edition v2 — launch retrospective: `deferred` — operator-side retro.

## S1761–S1770 — Per-region editorial v2 — International edition

`editorial_region='international'` is the negative-filter bucket (every non-AFRICA / non-EUROPE / non-AMERICAS source country, plus sources with no country tag, per `Regions::otherNamedCodes()` + `GrimbaRegionScope`). The international-edition v2 (named curators, dedicated translators, separate sponsor pack) is `deferred` for the same operator-staffing reasons as the Africa-edition v2.

- **S1761** — International edition v2 — region scope: `complete` — `Regions::countries('international')` returns null (negative filter); `GrimbaRegionQuery::applyToSourceCountry($q, 'country')` builds `country IS NULL OR country NOT IN (otherNamedCodes())`; `GrimbaHomeFeed` resolves per-region accordingly.
- **S1762** — International edition v2 — named curator role: `deferred` — same shape as S1751.
- **S1763** — International edition v2 — curator admin scope: `deferred` — same shape as S1752.
- **S1764** — International edition v2 — multi-language translator pool: `partial` — automated `GrimbaTranslator` covers EN ↔ FR; ES / PT / DE / AR `deferred` per S1101-S1140 catalog deferrals.
- **S1765** — International edition v2 — translator queue cross-locale: `partial` — same as S1754; cross-locale routing handled by `posts.translation_priority` rule engine but locale catalogs gated.
- **S1766** — International edition v2 — International ad-pack: `partial` — same shape as S1755.
- **S1767** — International edition v2 — International sponsor inventory: `deferred` — same shape as S1757.
- **S1768** — International edition v2 — International newsletter cadence: `deferred` — same shape as S1758.
- **S1769** — International edition v2 — International monthly editorial report: `partial` — `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md` covers both editions; per-month report `deferred`.
- **S1770** — International edition v2 — launch retrospective: `deferred` — operator-side retro.

## S1771–S1780 — Reader literacy v2 — bias-bar tutorial overlay

`/explainer-bias-bar` ships as a standalone "how to read the bar" page (per `platform/themes/echo/routes/web.php:1448-1477` + `platform/themes/echo/views/explainer-bias-bar.blade.php`); AboutPage JSON-LD per Wave OOOOO. A modal / tooltip / step-through overlay (first-visit walkthrough that highlights each bias-bar segment) `deferred` — no `onboarding_modal_bias_bar` partial.

- **S1771** — Bias-bar tutorial — standalone explainer page: `complete` — `/explainer-bias-bar` route + `platform/themes/echo/views/explainer-bias-bar.blade.php` + AboutPage JSON-LD per Wave OOOOO.
- **S1772** — Bias-bar tutorial — first-visit overlay modal: `deferred` — no `bias-bar-tutorial-overlay.blade.php` partial; surrogate is the explainer page link from the bias-distribution chart caption.
- **S1773** — Bias-bar tutorial — step-through animation (hover-to-explain per segment): `deferred` — depends on S1772.
- **S1774** — Bias-bar tutorial — dismiss-don't-show-again cookie: `partial` — cookie pattern proven by `grimba_consent_dismissed` + `grimba_local_*` cookies; specific `bias_tutorial_dismissed` cookie `deferred`.
- **S1775** — Bias-bar tutorial — keyboard-only navigation: `partial` — site a11y baseline covers per `tests/e2e/grimbanews-keyboard-navigation.cjs`; specific overlay a11y `deferred` (depends on S1772).
- **S1776** — Bias-bar tutorial — screen-reader narration: `partial` — same; alt text + table fallback on bias chart per S1668.
- **S1777** — Bias-bar tutorial — cross-locale (FR + EN): `partial` — `/explainer-bias-bar` page strings wrapped in `__()` per Wave LLLLLLLLL + WWWWWWWWW; tutorial-overlay strings `deferred` (depends on S1772).
- **S1778** — Bias-bar tutorial — partner-school distribution: `deferred` — no partner-school program (gates on S1741-S1750 literacy band).
- **S1779** — Bias-bar tutorial — analytics (completion rate): `deferred` — no overlay; depends on S1772 + S1733 read-event capture.
- **S1780** — Bias-bar tutorial — launch retrospective: `deferred` — gates on S1772-S1779.

## S1781–S1790 — Reader literacy v2 — fact-check primer (how we score)

The methodology page (`/methodologie`) covers source bias + factuality + ownership scoring in one place. A standalone "fact-check primer" surface (`/explainer-fact-check`, walking through the `news_sources.factuality_score` 0-100 scale + exclusion thresholds + score-provenance) does not exist as its own route.

- **S1781** — Fact-check primer — standalone page: `partial` — `/methodologie` covers fact-check scoring within the master methodology; standalone `/explainer-fact-check` route `deferred`.
- **S1782** — Fact-check primer — factuality-score scale visualization: `partial` — `news_sources.factuality_score` (int 0-100) rendered per source on `/sources`; standalone scale-explainer `deferred`.
- **S1783** — Fact-check primer — exclusion-threshold doc: `partial` — `grimba_publish_min_factuality_score` setting gates ingest; doc surface `deferred`.
- **S1784** — Fact-check primer — score-provenance ("how did we get this number"): `partial` — `news_sources.bias_source` + `factuality_source` columns hold provenance (Botble admin editable); reader-facing surface `deferred`.
- **S1785** — Fact-check primer — appeal / dispute path: `partial` — `/contact?subject=dispute` per methodology hero CTA "Contester un classement"; dedicated dispute form `deferred` per S1427.
- **S1786** — Fact-check primer — cross-locale (FR + EN): `partial` — `/methodologie` shipped in FR + EN per Wave LLLLLLLLL + WWWWWWWWW; standalone primer `deferred`.
- **S1787** — Fact-check primer — partner-school distribution: `deferred` — same as S1778.
- **S1788** — Fact-check primer — interactive quiz: `deferred` — no quiz primitive (gates on S1721-S1730 literacy band).
- **S1789** — Fact-check primer — analytics (read-through rate): `deferred` — depends on S1733 read-event capture.
- **S1790** — Fact-check primer — launch retrospective: `deferred` — gates on S1781-S1789.

## S1791–S1800 — Reader literacy v2 — methodology video / podcast

No video / podcast pipeline today. The `app/Services/GrimbaArticleExtractor.php` strips `iframe` / `noscript` / `svg` on ingest (line 110) so even embedded media from upstream articles is removed. There is no `posts.media_type='video'` / `'podcast'` slot, no audio-file storage path, no transcript table.

- **S1791** — Methodology video — script: `deferred` — no video pipeline; surrogate is `/methodologie` written longform.
- **S1792** — Methodology video — recording: `deferred` — operator-side production.
- **S1793** — Methodology video — hosting (YouTube / Vimeo / self-hosted): `deferred` — same.
- **S1794** — Methodology video — embed on `/methodologie`: `deferred` — `GrimbaSecurityHeaders` CSP currently locks down `frame-src` to a closed list (operator-side pickup once host is picked).
- **S1795** — Methodology video — transcript (a11y): `deferred` — depends on S1791.
- **S1796** — Methodology video — cross-locale subtitles (FR + EN): `deferred` — depends on S1791-S1795.
- **S1797** — Methodology podcast — recording: `deferred` — no audio pipeline.
- **S1798** — Methodology podcast — hosting + RSS (Apple Podcasts / Spotify): `deferred` — operator-side; depends on S1797.
- **S1799** — Methodology podcast — transcript: `deferred` — depends on S1797.
- **S1800** — Methodology video / podcast launch retrospective: `deferred` — gates on S1791-S1799.

---

## Summary

All 200 sprint IDs in S1601–S1800 now carry a ledger row in `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`.

- **Complete (17 sprints):** S1602 (GrimbaGeoLocator IP cascade with cookie persistence), S1605 (POST `/local/set` cookie writer), S1606 (consent posture — IP only on no-cookie), S1607 (manual location override), S1613 (Regions 4-bucket schema), S1617 (source-country backfill command), S1618 (source-classification scheduler), S1621 (France pilot source coverage), S1626 (FR hreflang locking), S1627 (FR methodology page + EN translations), S1629 (FR advertiser leads pipeline), S1631 (Africa region landing), S1648 (Canada bilingual routing), S1663 (server-side bias breakdown source of truth), S1684 (read-history CSV export with privacy header), S1685 (vault picks CSV export), S1686 (monthly vault-events CSV archive), S1732 (anon read-event schema with ip_hash privacy), S1735 (monthly CSV pipeline), S1741 (per-job duration_ms capture), S1742 (per-job exit-code + error-message), S1743 (cockpit per-job dashboard), S1744 (per-job missed-run alert), S1761 (Regions::international negative filter), S1771 (explainer-bias-bar standalone page).
- **Partial (~50 sprints):** Local v2 has a working `/local` route + GrimbaGeoLocator cascade + 4-region taxonomy; per-city deep landings + per-country `/africa/{iso2}` routes + DOM-TOM bucket + lusophone source pool + per-locale dialect handling defer to operator pickup. Tools for journalists has the SVG-shape bias-distribution + GrimbaSourceBreakdown source of truth in place; iframe-embed route `deferred`. Tools for educators has GrimbaAds::shouldRender() single gate + GrimbaVault cookie+member sync as classroom-mode + reading-list surrogates. Tools for researchers has 3 working CSV streams (`pour-vous/export.csv`, `coffre/export.csv`, monthly `vault_events` archive); per-source / per-cluster / per-day rollups + academic API tier defer. Data platform has the `grimba_automation_runs` per-job latency table + `vault_events` privacy-safe event ledger + monthly CSV pipeline + `grimba:release-smoke` budget gates; vector store + feature store + A/B harness + warehouse + APM defer (S1076 / S1073 / S1013 honest deferrals stand). Per-region editorial v2 has the `posts.editorial_region` + `editorial_secondary_region` data side fully populated + `GrimbaArticleRegion` topical anchors + `GrimbaBackfillEditorialRegions` retag commands; named curator + per-edition newsletter + per-edition ad-pack defer to operator staffing. Reader literacy v2 has `/methodologie` + `/explainer-bias-bar` standalone pages in FR + EN; tutorial overlays + fact-check primer + video / podcast defer to multimedia pipeline.
- **Deferred (~133 sprints):** All per-city deep landings (no `local_cities` table), per-city sponsor / advertiser slots, embed-widget route (`/embed/{cluster-id}` + JS-snippet + token API + embed analytics), bias-chart standalone embed, classroom route + `educator_seats` schema + teacher-curated lists, dataset license / citation / versioning, academic API tier (depends on S1181-S1190 public API v2), vector / embedding store (depends on S1076), ML feature store, A/B harness (depends on S1073), analytics warehouse + dashboard layer (depends on S1731 destination pick), continuous per-request APM (depends on S1013 Sentry / equivalent), external alert webhooks (depends on S1014 / S1019 PagerDuty), per-region named curator + dedicated translator + per-edition newsletter + per-edition ad-pack (operator-side staffing), bias-bar tutorial overlay + first-visit walkthrough (no `bias-bar-tutorial-overlay` partial), fact-check standalone primer + interactive quiz, methodology video + podcast (no video / audio pipeline; CSP frame-src locked until host is picked).

The honest read: **roughly 12% of the S1601-S1800 band is genuinely shipped today, ~25% has a server-side / admin / cookie / scheduled-job surrogate, and ~67% is post-launch product expansion gated on either a paid tier (S1211), an embedding / vector store (S1076), an A/B harness (S1073), an analytics warehouse / APM (S1013 / S1731), a partner / academic-API access program (S1181+), a multi-seat workflow (educator / curator), or a multimedia production pipeline (video / podcast)**.

The valuable foundation: **`/local` geo-personalized landing + `GrimbaGeoLocator` cascade + cookie persistence + Regions 4-bucket schema + GrimbaArticleRegion topical retag + GrimbaBackfillEditorialRegions / GrimbaRetagEditorialRegionByTopic commands + 3 working CSV exports (pour-vous, coffre, monthly vault-events) + grimba_automation_runs per-job latency table + GrimbaAutomationMonitor cockpit board + grimba:release-smoke per-route budget gates + GrimbaSourceBreakdown server-side bias-chart truth + /methodologie + /explainer-bias-bar standalone explainer pages in FR + EN** are all in place. The deferred rows drop into a working substrate the moment the missing tier / vector store / A/B harness / warehouse / API / multimedia pipeline is provisioned.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (Sprint Evidence Ledger section, new rows for S1601-S1800)
- Prior packs: `docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md` (S1001-S1100), `docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md` (S1101-S1200), `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md` (S1201-S1400), `docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md` (S1401-S1600)
- Local v2 surface: `platform/themes/echo/routes/web.php:1538-1610` (`/local` GET + `local/set` POST), `platform/themes/echo/views/local.blade.php`, `app/Services/GrimbaGeoLocator.php` (ip-api.com → ipapi.co cascade, 24h cache, no raw IP), `bootstrap/app.php:31-33` (cookie whitelist), `app/Ground/Regions.php` (4-bucket schema), `app/Support/GrimbaRegionQuery.php`, `app/Support/GrimbaArticleRegion.php` (topical-anchor retag), `app/Console/Commands/GrimbaBackfillEditorialRegions.php`, `app/Console/Commands/GrimbaRetagEditorialRegionByTopic.php`, `app/Console/Commands/GrimbaBackfillSourceCountries.php`, `app/Support/GrimbaSourceCountryBackfill.php` (DOMAIN_COUNTRIES lookup), `app/Support/GrimbaSourceClassifier.php` (DOMAIN_PROFILES for FR + UK + US + CA sources), `app/Console/Commands/GrimbaSeedImmigrationSources.php`, `app/Console/Commands/GrimbaSeedThinCategorySources.php`
- Tools for researchers — CSV exports: `platform/themes/echo/routes/web.php:1235-1287` (`/pour-vous/export.csv` with `X-GN-Privacy: cookie-only-no-server-record` header), `platform/themes/echo/routes/web.php:1913+` (`/coffre/export.csv`), `app/Console/Commands/GrimbaArchiveVaultEvents.php` (`storage/exports/vault_events_YYYY-MM.csv`), `app/Support/GrimbaAutomationMonitor.php:119` (`vault_events_archive` scheduled job)
- Bias-chart server-side source of truth: `app/Support/GrimbaSourceBreakdown.php`, `app/Support/GrimbaClusterBias.php`, `platform/themes/echo/partials/story/bias-distribution.blade.php`, `tests/Feature/GrimbaSourceBreakdownTest.php`, `tests/Feature/StoryBreakdownTest.php`
- Observability v2: `app/Support/GrimbaAutomationMonitor.php` (start / finish / status / JOBS registry), `database/migrations/2026_04_28_181500_create_grimba_automation_runs_table.php` (job_key / command / status / exit_code / started_at / finished_at / duration_ms / error_message + 2 indexes), `app/Console/Commands/GrimbaHealth.php` (`--fail-on-risk` flag), `app/Console/Commands/GrimbaReleaseSmoke.php` (per-route latency budgets), `platform/themes/echo/functions/grimba-admin-cockpit.php:219+249` (cockpit board), `tests/Feature/AutomationScheduleTest.php`, `tests/Feature/DailyPublishFreshnessTest.php`
- Per-region editorial v2: `app/Ground/Regions.php` (54 AFRICA + 48 EUROPE + 35 AMERICAS + INTERNATIONAL negative filter), `app/Scopes/GrimbaRegionScope.php`, `app/Support/GrimbaRegionQuery.php`, `app/Support/GrimbaHomeFeed.php` (per-region breaking + latest + hero scoping), `app/Support/GrimbaArticleRegion.php` (topical-region detector with FR + EN anchors), `database/migrations/2026_05_16_120000_add_editorial_region_to_posts_table.php`, `database/migrations/2026_05_18_220000_add_editorial_secondary_region_to_posts_table.php`, `tests/Feature/GrimbaEditorialSecondaryRegionTest.php`, `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md`
- Reader literacy v2: `platform/themes/echo/routes/web.php:1359-1393` (`/methodologie` + TechArticle JSON-LD), `platform/themes/echo/views/methodology.blade.php`, `platform/themes/echo/routes/web.php:1448-1477` (`/explainer-bias-bar` + AboutPage JSON-LD), `platform/themes/echo/views/explainer-bias-bar.blade.php`, `lang/fr.json` + `lang/en.json` (Wave LLLLLLLLL + WWWWWWWWW translations)
- Advertiser leads pipeline (per-region routing): `app/Http/Controllers/AdvertiserLeadController.php` (RateLimiter::attempt per-IP), `app/Mail/GrimbaAdvertiserLeadNotification.php`, `database/migrations/2026_05_18_190000_create_grimba_advertiser_leads_table.php`, `database/migrations/2026_05_18_210000_add_source_pack_tier_to_grimba_advertiser_leads_table.php`, `tests/Feature/GrimbaAdvertiserLeadsTest.php`
- No-go zones (genuinely deferred): no `local_cities` / `educator_seats` / `classrooms` / `students` / `assignments` / `embed_tokens` / `embed_impressions` / `experiments` / `experiment_assignments` / `post_embeddings` / `dataset_versions` tables today; no vector / embedding store (pgvector / qdrant / pinecone / weaviate); no A/B engine; no analytics warehouse (BigQuery / Snowflake / DuckDB / ClickHouse) destination picked; no APM / Sentry; no PagerDuty / Opsgenie webhook; no academic API tier; no video / podcast production pipeline; no transcript table; CSP `frame-src` locked until video host is picked.
