# Mythos S1101–S1200 — i18n Expansion + Mobile App + Public API Evidence Pack

**Status:** evidence reconciliation
**Created:** 2026-05-22
**Author:** Wave VVVVVVVVV batch close (second Mythos post-launch band)
**Scope:** Converts the second 100-sprint slice of the Mythos S1001–S2237 post-launch arc — i18n catalog expansion (ES / PT-BR / DE / IT / AR / JA / ZH / KO / RU / HE / HI / SW), per-locale ops (RTL / typo / formatting / moderation / ad-consent / legal / support / pricing / launch), the mobile app program (PWA-to-app-store wrapper, native iOS / Android shells, push, deep-link, offline, A/B, ASO, retro), the public API v2 (OAuth client / rate limits / partner sandbox / SLA) and OEM whitelabel — into ledger rows pointing at real shipped code, third-party-account deferreds, and operator pickups.

This pack feeds the master `Sprint Evidence Ledger` in `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`. The 100 sprint IDs in S1101–S1200 now have a ledger row. The honest split is **heavy on `deferred` and `partial`** — almost every one of these is either a third-party account (Apple Developer / Google Play / FCM / Firebase Crashlytics / OAuth provider / external translation vendor), a post-launch product expansion that depends on tiering / paid plan, or an operator-side editorial / legal / pricing pickup. The current GrimbaNews surface is **FR-canonical with EN parity** plus a working PWA shell, JSON-LD + hreflang + sitemap for FR / EN, and the language-detector + translator stack ready to consume more locales once a translation vendor is provisioned and catalogs are produced.

The S001–S1000 pre-launch arc is the launch gate; the S1001+ work hardens / expands the running platform. Some S1101+ items have a shipped *surrogate* already (PWA shell, hreflang, lang-switch, share-kit with native intents, mobile bottom nav, FR↔EN catalog completeness) and are marked `partial` so the next session knows what's load-bearing and what still needs a real account or vendor.

---

## S1101–S1110 — i18n expansion (Spanish catalog + landing + editorial + feed + sitemap + JSON-LD + OG + robots + hreflang + launch)

The ES catalog band assumes a working FR / EN catalog as the template. FR ↔ EN parity is shipped: `lang/fr.json` 555 keys, `lang/en.json` 506 keys, both validated by `tests/Feature/StaticUiTranslationTest::test_saved_translation_catalogs_are_valid_json`. The per-locale catalogs (ES / PT-BR / DE / IT / AR / JA / ZH / KO / RU / HE / HI / SW) **do not exist** — operator needs to either commission a translator account or wire NobuTranslation to bulk-emit them. The HTTP machinery (`GrimbaLocaleEnforce` middleware, `?lang=` query param, `grimba_lang` cookie, hreflang `<link rel="alternate">` in `grimba-chrome.blade.php` + `grimba-home.blade.php`) is locale-agnostic and will pick the new catalog up the moment it's added to `lang/{locale}.json`.

- **S1101** — Site UI catalog ES: `deferred` — `lang/es.json` does not exist. FR ↔ EN catalogs at `lang/fr.json` (555 keys) + `lang/en.json` (506 keys) are the template; operator needs translator account or NobuTranslation bulk pass. Detection-side coverage already shipped: `App\Services\GrimbaLanguageDetector::detect()` returns `'es'` for Spanish content per `tests/Unit/GrimbaLanguageDetectorTest`. Catalog is the missing piece.
- **S1102** — ES landing: `deferred` — depends on S1101 catalog; no landing copy to render against until catalog ships.
- **S1103** — ES editorial pages: `deferred` — same; editorial-category labels (`GrimbaEditorialCategories::all()`) are FR-canonical with EN translation in `lang/en.json` ("À la une" → "Top stories" etc.); ES translation per-bucket is the missing piece.
- **S1104** — ES feed: `partial` — `routes/web.php` + `platform/themes/echo/routes/web.php:333` register `/feed.xml` + `/feed.breaking.xml` + `/feed.latest.xml` + per-category streams; the feed handler emits posts in their `original_language` regardless of UI locale, so ES posts already surface on the feed once `posts.original_language = 'es'` is set by the detector. Locale-scoped `/feed.es.xml` variant `deferred`.
- **S1105** — ES sitemap: `partial` — `public.sitemap.grimba` handler at `platform/themes/echo/routes/web.php:345-404` emits a single XML sitemap with all reader URLs. Per-locale sitemap variants + hreflang sitemap extensions `deferred` until ES catalog ships.
- **S1106** — ES JSON-LD: `partial` — `test_category_dossier_source_pages_ship_jsonld` (`GrimbaLaunchReadinessTest:704`) locks CollectionPage + canonical + hreflang FR/EN; JSON-LD `inLanguage` field will derive from `app()->getLocale()` once ES is registered as a primary locale.
- **S1107** — ES OG cards: `partial` — `App\Http\Controllers\GrimbaPageOgController` + `GrimbaOgImageController` render OG with title + lede in current locale; once `?lang=es` is honored (depends on S1101) the OG card will paint in ES.
- **S1108** — ES robots: `partial` — `public/robots.txt` is locale-agnostic and disallows admin + member paths; no per-locale variant needed (robots = site-wide).
- **S1109** — ES hreflang: `deferred` — `layouts/grimba-chrome.blade.php:115-117` ships `<link rel="alternate" hreflang="fr|en|x-default">`. Adding `hreflang="es"` is a one-line edit once `App\Http\Middleware\GrimbaLocaleEnforce::PRIMARY_LOCALES` is widened to include `'es'`.
- **S1110** — ES launch readiness: `deferred` — gates on S1101-S1109 + per-locale support contact (S1148) + per-locale ad consent (S1146) + per-locale legal pages (S1147).

## S1111–S1120 — i18n expansion (Portuguese Brazilian + landing + editorial + feed + sitemap + JSON-LD + OG + robots + hreflang + launch)

Same pattern as ES — the HTTP machinery is locale-agnostic, the catalog is the missing piece. PT-BR is one of the five locales that `GrimbaLanguageDetector` already returns on ingest, so the upstream detection-side is shipped.

- **S1111** — Site UI catalog PT-BR: `deferred` — `lang/pt_BR.json` does not exist. Detector covers PT-BR per `GrimbaLanguageDetectorTest` n-gram + TLD coverage.
- **S1112** — PT-BR landing: `deferred` — depends on S1111.
- **S1113** — PT-BR editorial pages: `deferred` — same.
- **S1114** — PT-BR feed: `partial` — same as S1104, feed handler emits posts in their `original_language` so PT-BR posts surface once detector tags them.
- **S1115** — PT-BR sitemap: `partial` — same as S1105.
- **S1116** — PT-BR JSON-LD: `partial` — same as S1106.
- **S1117** — PT-BR OG cards: `partial` — same as S1107.
- **S1118** — PT-BR robots: `partial` — same as S1108.
- **S1119** — PT-BR hreflang: `deferred` — same as S1109, one-line edit to add `hreflang="pt-BR"` once middleware widens.
- **S1120** — PT-BR launch readiness: `deferred` — gates on S1111-S1119 + per-locale ops.

## S1121–S1130 — i18n expansion (German + landing + editorial + feed + sitemap + JSON-LD + OG + robots + hreflang + launch)

Same pattern. DE is in the detector's covered set per S-LANG-02.

- **S1121** — Site UI catalog DE: `deferred` — `lang/de.json` does not exist.
- **S1122** — DE landing: `deferred` — depends on S1121.
- **S1123** — DE editorial pages: `deferred`.
- **S1124** — DE feed: `partial` — same as S1104.
- **S1125** — DE sitemap: `partial` — same as S1105.
- **S1126** — DE JSON-LD: `partial` — same as S1106.
- **S1127** — DE OG cards: `partial` — same as S1107.
- **S1128** — DE robots: `partial` — same as S1108.
- **S1129** — DE hreflang: `deferred` — one-line edit.
- **S1130** — DE launch readiness: `deferred` — gates on S1121-S1129 + per-locale ops.

## S1131–S1140 — i18n expansion (IT + AR + JA + ZH + KO + RU + HE + HI + SW + multi-language launch ops)

Wider set: Italian, Arabic, Japanese, Chinese, Korean, Russian, Hebrew, Hindi, Swahili. AR / HE pull in RTL (S1142). Everything in this band is `deferred` on the same catalog axis as the ES / PT-BR / DE bands — only the IT detector path is in the existing detector coverage; AR / JA / ZH / KO / RU / HE / HI / SW need detector extension before ingest will route them correctly.

- **S1131** — Site UI catalog IT: `deferred` — `lang/it.json` does not exist; detector covers IT per S-LANG-02.
- **S1132** — Site UI catalog AR: `deferred` — `lang/ar.json` does not exist; detector AR support `deferred` (n-gram corpus does not yet include Arabic script). RTL chrome work is S1142.
- **S1133** — Site UI catalog JA: `deferred` — same — `lang/ja.json` + detector JA path not shipped.
- **S1134** — Site UI catalog ZH: `deferred` — same.
- **S1135** — Site UI catalog KO: `deferred` — same.
- **S1136** — Site UI catalog RU: `deferred` — same.
- **S1137** — Site UI catalog HE: `deferred` — same; RTL chrome work is S1142.
- **S1138** — Site UI catalog HI: `deferred` — same.
- **S1139** — Site UI catalog SW: `deferred` — same; Swahili would be high-value for the Afrique edition, surrogate is the Le Monde Afrique / La Cimade / UNHCR FR feeds already shipped per S1024.
- **S1140** — Multi-language launch ops: `deferred` — needs ≥1 non-FR/EN catalog actually shipped before launch ops can run. Operator surrogate today = the FR ↔ EN launch (`docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md` is bilingual today).

## S1141–S1150 — per-locale typo / RTL / font / formatting / moderation / ad consent / legal / support / pricing / launch

The per-locale ops layer. Several items here are shipped in their FR ↔ EN form (typo audit via `GrimbaTailExpanderTest`, formatting via `trans_choice()` for plural rules, support contact via `grimba_advertiser_leads_sales_mailbox` per-region setting); RTL chrome + per-locale legal / pricing / ad consent are honest `deferred` until at least one RTL locale (AR or HE) catalog ships.

- **S1141** — Per-locale typographic audit: `partial` — FR + EN typography locked via `GrimbaTailExpanderTest` (S-LANG-tail-expander) + the FR-canonical font stack (Fraunces serif + Public Sans sans). Per-locale audit for ES / PT-BR / DE / IT `deferred` until those catalogs ship; per-script (Arabic / CJK / Devanagari) audit `deferred` until those catalogs ship.
- **S1142** — Per-locale RTL support (AR / HE): `deferred` — Botble theme has `BaseHelper::isRtlEnabled()` upstream hook (`platform/themes/echo/config.php:56`) but the GrimbaNews layouts hard-code `dir="ltr"` (manifest.webmanifest line 5: `"dir": "ltr"`). RTL flip requires layout + token audit, not just a manifest edit.
- **S1143** — Per-locale font subset preload: `partial` — `layouts/grimba-chrome.blade.php` preloads Fraunces + Public Sans Latin subset only; per-script font subsets (Arabic / CJK / Devanagari) `deferred` until those catalogs ship.
- **S1144** — Per-locale formatting (dates / numbers): `partial` — Carbon timestamps respect `app()->getLocale()`; `trans_choice()` handles per-locale plural rules via FR / EN catalogs (e.g. `account.blade.php:127` uses `trans_choice(':count article|:count articles', $vaultCount)`). Per-locale number formatting (e.g. comma-vs-dot decimal, lakh/crore grouping for HI) `deferred` until those locales ship.
- **S1145** — Per-locale moderation policy: `deferred` — operator-side editorial policy; lands with editorial workflow band S1291-S1300.
- **S1146** — Per-locale ad consent rules: `deferred` — current consent banner is a single FR+EN bilingual surface; per-locale + per-region (GDPR vs CCPA vs LGPD) variant `deferred`. Surrogate: `App\Support\GrimbaAds` consent-gating hooks exist per S871 ads pack but are region-agnostic today.
- **S1147** — Per-locale legal pages: `deferred` — `/mentions-legales`, `/politique-de-confidentialite`, `/cgu` ship in FR + EN today; per-locale variants `deferred` until additional catalogs ship + counsel review per jurisdiction.
- **S1148** — Per-locale support contact: `partial` — `grimba_advertiser_leads_sales_mailbox` setting per-region per `AdvertiserLeadController` ships region routing today (Afrique → one mailbox, International → another). Per-locale support-contact mailbox `deferred` until additional catalogs ship.
- **S1149** — Per-locale subscription pricing: `deferred` — no paid tier shipped (lands with monetization S1211). PPP pricing per locale `deferred` until then.
- **S1150** — Per-locale launch comms: `deferred` — operator-side launch comms playbook per locale; gates on S1110 / S1120 / S1130 / S1140 catalog launches first.

## S1151–S1160 — Mobile app program (feasibility + tech pick + PWA wrapper + push infra + deep-link + offline + analytics + crash + review + playbook)

The PWA shell + offline page + share kit + mobile bottom nav are shipped and tested. The native-app wrapper + push infrastructure + crash reporting + analytics SDK all need third-party accounts (Apple Developer, Google Play, FCM, Crashlytics, Mixpanel/Amplitude). The PWA is a working "install" surrogate on iOS Safari + Chrome Android today.

- **S1151** — Native app feasibility study: `partial` — surrogate is the working PWA: `public/manifest.webmanifest` + `public/grimba-sw.js` + `public/offline.html`. PWA-as-feasibility-proof shipped; written feasibility doc `deferred`.
- **S1152** — RN vs Flutter vs Capacitor pick: `deferred` — no native shell shipped. PWA is the current native surrogate.
- **S1153** — PWA-to-app-store wrapper: `deferred` — needs Apple Developer + Google Play accounts. PWABuilder / Bubblewrap are candidate tools but no wrapper packaged today.
- **S1154** — Push notification infra: `deferred` — needs FCM + Apple Push Notification Service (APNs) accounts + server-side push-token storage table (does not exist). No webpush integration today.
- **S1155** — Deep-link routing: `partial` — public routes are deep-linkable today (`/dossier/{id}`, `/categorie/{slug}`, `/source/{slug}`); native-app Universal Links / App Links registration `deferred` until shell ships.
- **S1156** — Offline-read cache: shipped (PWA surrogate) — `public/grimba-sw.js` caches `/offline.html` + favicons + cache-policy honors `Cache-Control: no-store|private` (per `tests/Feature/PwaShellTest::test_service_worker_avoids_private_paths_and_non_cacheable_responses`); private-path prefix list (`/admin`, `/account`, `/member`, `/coffre`, `/coffre-share`) prevents shared-device privacy leak. Article-body offline cache (full reader offline) `deferred` to native-app band.
- **S1157** — App analytics: `deferred` — needs Mixpanel / Amplitude / GA4 SDK + app shell. Server-side `GrimbaVaultEvents` privacy-safe event ledger is the web surrogate.
- **S1158** — App crash reporting: `deferred` — needs Firebase Crashlytics / Sentry account. Web-side surrogate is `app/Exceptions/Handler.php` + Laravel log per S1011.
- **S1159** — App review channel: `deferred` — needs App Store Connect + Google Play Console.
- **S1160** — App launch playbook: `deferred` — gates on S1151-S1159 actually shipping.

## S1161–S1170 — Mobile app shells (iOS + Android + login + reader + save-vault sync + for-you + local + subscription + share + onboarding)

Every item here is `deferred` because the native shell doesn't exist. Web surrogates are working today for each surface (login at `/account`, reader at `/dossier/{id}`, save-vault at `/coffre`, for-you at `/pour-vous`, local at `/local`, share kit at `partials/story/share-kit.blade.php`, onboarding at `partials/home/onboarding-modal.blade.php`). The web surrogates would feed the native-shell webview wrapper directly if Capacitor is picked.

- **S1161** — iOS app shell: `deferred` — no Xcode project. PWA-on-iOS-Safari is the surrogate (Add-to-Home-Screen + standalone display via `manifest.webmanifest`).
- **S1162** — Android app shell: `deferred` — no Android Studio project. PWA-on-Chrome-Android is the surrogate (`beforeinstallprompt` triggers on supported builds).
- **S1163** — App login: `deferred` — surrogate is `/account` via member auth (Botble member plugin, login at `/login`).
- **S1164** — App reader: `deferred` — surrogate is `/dossier/{id}` + `/blog/{slug}` reader surfaces.
- **S1165** — App save-vault sync: `partial` — `App\Support\GrimbaVault` already supports server-side sync via `members.vault_digest_post_ids` column when reader is logged in; cookie-based vault syncs to member row on login via `GrimbaVault::syncCookieToMember()`. Native-app sync = same endpoint, just from native client.
- **S1166** — App for-you: `deferred` — surrogate is `/pour-vous` (FR) / `/for-you` (EN) via `platform/themes/echo/views/for-you.blade.php`.
- **S1167** — App local edition: `deferred` — surrogate is `/local` (geolocation + manual location picker per S591-S600).
- **S1168** — App subscription: `deferred` — no paid tier (lands with monetization S1211).
- **S1169** — App share: `partial` — web `partials/story/share-kit.blade.php` ships intent URLs for X / Bluesky / Facebook / WhatsApp / LinkedIn / Email; native Web Share API (`navigator.share`) `deferred` (current implementation uses intent URLs only, no `if (navigator.share)` branch).
- **S1170** — App onboarding: `deferred` — surrogate is web `partials/home/onboarding-modal.blade.php`.

## S1171–S1180 — Mobile app polish (dark / light parity + a11y + NobuAI insight + translation flow + push categories + freq caps + A/B tests + ASO + review prompt + retro)

Same pattern — the web surfaces are dark-mode + a11y-locked but the native app polish work is `deferred` until the native shell ships.

- **S1171** — App dark / light parity: `partial` — web theme has dark + light + auto modes locked by `GrimbaDarkModeContractTest` per S731-S740 design pack; PWA inherits the web theme; native-app parity `deferred`.
- **S1172** — App accessibility: `partial` — web a11y locked by `tests/Feature/SecurityHeadersTest` + S751-S800 a11y band (axe / keyboard / SR / contrast); native-app a11y `deferred`.
- **S1173** — App NobuAI insight: `partial` — `posts.summary_nobuai` populated by `grimba:nobuai-summaries` every 30 min; web surface renders via `partials/story/nobuai-summary.blade.php`; native-app integration `deferred`.
- **S1174** — App translation flow: `partial` — web translation flow shipped via `GrimbaTranslationPresenter` + `?lang=` query param + cookie + `App\Http\Middleware\GrimbaLocaleEnforce`; native-app integration `deferred`.
- **S1175** — App push categories: `deferred` — no push infra (S1154).
- **S1176** — App push frequency caps: `deferred` — same.
- **S1177** — App A/B tests: `deferred` — no A/B harness (S1073).
- **S1178** — App Store Optimization: `deferred` — no store listing.
- **S1179** — App review-prompt cadence: `deferred` — needs native shell.
- **S1180** — App launch retrospective: `deferred` — gates on a real app launch.

## S1181–S1190 — Public API v2 (OAuth + rate limits + key revocation + partner sandbox + docs + analytics + SLA + launch)

The public surface today is **read-only via XML feeds** — `/feed.xml`, `/feed.breaking.xml`, `/feed.latest.xml`, `/feed.{category}.xml`, `/sitemap-grimba.xml`, and the `/health` + `/up` endpoints. There is **no JSON API v2, no OAuth client, no API-key issuance, no partner sandbox**. All of this band is `deferred` to the B2B product expansion phase.

- **S1181** — Public API v2 design: `deferred` — no `/api/v2` routes registered. Surrogate: per-stream RSS feeds at `/feed.{breaking,latest,category}.xml` + JSON `/health` cover read-only partner needs today.
- **S1182** — OAuth client: `deferred` — no Sanctum / Passport install. Member auth uses Botble session cookie.
- **S1183** — Rate limit policies: `partial` — Laravel `ThrottleRequests` middleware groups exist (`AdvertiserLeadController` ships a per-IP `RateLimiter::attempt('advertiser-lead:' . sha1($ip), ...)` block at line 36). Public API rate-limit policies `deferred` until API v2 ships.
- **S1184** — Key revocation: `deferred` — no API keys to revoke.
- **S1185** — Partner sandbox: `deferred` — no partner program.
- **S1186** — Partner docs: `deferred` — same. Surrogate: feed format follows Atom 1.0 / RSS 2.0 standards documented widely.
- **S1187** — Partner playbook: `deferred` — same.
- **S1188** — API analytics: `deferred` — feed-fetch volume can be sampled from web-server access logs today; structured per-key analytics `deferred`.
- **S1189** — API SLA: `deferred` — `/health` + `/up` provide uptime evidence; formal SLA contract `deferred`.
- **S1190** — API launch playbook: `deferred` — gates on S1181-S1189.

## S1191–S1200 — OEM whitelabel (config schema + branding upload + domain bind + admin gate + feature gate + invoice + support SLA + exit + case study + launch)

GrimbaNews is single-tenant today. No multi-tenant / OEM / whitelabel infrastructure. All `deferred` to the B2B product expansion phase.

- **S1191** — OEM whitelabel — config schema: `deferred` — no `tenants` / `tenant_settings` table; current `settings` table is global.
- **S1192** — OEM whitelabel — branding upload: `deferred` — Botble theme settings allow upstream logo / color tokens, but per-tenant overlay `deferred`.
- **S1193** — OEM whitelabel — domain bind: `deferred` — single domain (grimbanews.com); no multi-domain routing.
- **S1194** — OEM whitelabel — admin gate: `deferred` — Botble admin auth is single-tenant.
- **S1195** — OEM whitelabel — feature gate: `deferred` — no entitlements layer.
- **S1196** — OEM whitelabel — invoice: `deferred` — no billing infra (lands with S1211 monetization).
- **S1197** — OEM whitelabel — support SLA: `deferred` — operator-side contract; depends on S1189.
- **S1198** — OEM whitelabel — exit clause: `deferred` — operator-side legal pickup.
- **S1199** — OEM whitelabel — case study: `deferred` — needs ≥1 real OEM partner.
- **S1200** — OEM whitelabel — launch: `deferred` — gates on S1191-S1199.

---

## Summary

All 100 sprint IDs in S1101–S1200 now carry a ledger row in `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`.

- **Complete (1 sprint):** S1156 (offline-read cache via PWA service worker, locked by `PwaShellTest`).
- **Partial (24 sprints):** S1104, S1105, S1106, S1107, S1108, S1114, S1115, S1116, S1117, S1118, S1124, S1125, S1126, S1127, S1128, S1141, S1143, S1144, S1148, S1151, S1155, S1165, S1169, S1171, S1172, S1173, S1174, S1183 — server-side / web-side surrogate shipped (HTTP machinery is locale-agnostic, PWA shell is the native-app surrogate, web a11y / dark-mode / NobuAI / translation flows are locked, advertiser-lead rate-limit is the API-v2 throttling pattern), additional catalog / native shell / API auth piece deferred.
- **Deferred (75 sprints):** All non-FR/EN UI catalogs (ES / PT-BR / DE / IT / AR / JA / ZH / KO / RU / HE / HI / SW × landing / editorial / launch readiness), RTL chrome (AR / HE), per-locale moderation / ad consent / legal / support / pricing / launch comms, native app program in full (RN/Flutter/Capacitor pick → app shells → push → analytics → crash → ASO → retro), public API v2 (OAuth / rate limits / keys / sandbox / docs / SLA), OEM whitelabel (config schema → branding → domain bind → admin gate → feature gate → invoice → SLA → exit → case study).

The honest read: **roughly 1% of the S1101-S1200 band is genuinely shipped today, ~25% has a server-side or web-side surrogate, and ~75% needs either a new catalog, a third-party account (Apple Developer / Google Play / FCM / Crashlytics / OAuth provider / external translation vendor), or a tiering / B2B product expansion that lands later in the Mythos arc**.

This matches the band's stated purpose ("i18n expansion + mobile app + B2B / API") — these are deliberate post-launch growth tracks, not pre-launch must-ships. The valuable evidence is that the **HTTP machinery (locale middleware, hreflang, per-stream feeds, sitemap), PWA shell (manifest + service worker + offline + cache discipline), share kit, mobile bottom nav, save-vault sync, dark mode + a11y contracts, and NobuAI + translation pipelines** are all locale-agnostic / native-agnostic / API-ready today, so each deferred row drops into a working foundation the moment the missing catalog / account / tier ships.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (Sprint Evidence Ledger section, new rows for S1101-S1200)
- Prior pack: `docs/GRIMBANEWS_MYTHOS_S1001_S1100_OPS_EVIDENCE.md` (S1001-S1100)
- Launch checklist: `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md`
- Language plan: `docs/GRIMBANEWS_LANGUAGE_SPRINT_PLAN.md`, `docs/GRIMBANEWS_LANGUAGE_SURFACING_AND_AUTO_TRANSLATE_PLAN.md`, `docs/GRIMBANEWS_LANGUAGE_TAGGING_OPERATOR_HANDOFF.md`
- newsdata.io operator handoff: `docs/GRIMBANEWS_NEWSDATAIO_OPERATOR_HANDOFF.md`
- Test surface: `tests/Feature/StaticUiTranslationTest.php` (catalog validity), `tests/Feature/PwaShellTest.php` (manifest + SW + cache discipline + private-path guard), `tests/Feature/NobuTranslationModuleTest.php`, `tests/Feature/TranslationAtomicityTest.php` (4 invariants), `tests/Feature/GrimbaCategoryBadgeCrossLocaleTest.php`, `tests/Feature/GrimbaFilterForTargetLocaleTest.php`, `tests/Feature/GrimbaTailExpanderTest.php`, `tests/Feature/GrimbaDarkModeContractTest.php` (dark/light parity contract), `tests/Feature/SecurityHeadersTest.php`, `tests/Feature/GrimbaLaunchReadinessTest.php::test_category_dossier_source_pages_ship_jsonld`, `tests/Unit/GrimbaLanguageDetectorTest.php` (26 tests covering ES/PT-BR/DE/IT + n-gram + TLD).
- Code surface (i18n): `lang/en.json` (506 keys), `lang/fr.json` (555 keys), `lang/en/auth.php` + `pagination.php` + `passwords.php` + `validation.php`, `app/Services/GrimbaLanguageDetector.php`, `app/Services/GrimbaTranslator.php` (driver chain: NobuAI / OpenRouter / LibreTranslate), `app/Support/GrimbaTranslationPresenter.php`, `app/Support/GrimbaLanguageSettings.php` (13 cached settings keys + defaults), `app/Support/GrimbaTranslationRules.php`, `app/Http/Middleware/GrimbaLocaleEnforce.php`, `app/Console/Commands/GrimbaBackfillLanguage.php` + `GrimbaTranslateByRule.php` + `GrimbaTranslatePending.php` + `GrimbaRecomputeDossierLanguage.php`, `platform/themes/echo/partials/home/lang-switch.blade.php`, `platform/themes/echo/partials/lang/tail-expander.blade.php`, `platform/themes/echo/functions/grimba-admin-translation-rules.php` + `grimba-admin-translation-monitor.php`.
- Code surface (mobile / PWA): `public/manifest.webmanifest` (FR-canonical, LTR, standalone, portrait-primary, news + magazines categories), `public/grimba-sw.js` (origin-wide cache + private-path guard), `public/offline.html`, `platform/themes/echo/partials/home/mobile-bottom-nav.blade.php` (5-slot nav: Accueil / Dossiers / Pour vous / Local / Coffre, locale-aware via `__()` strings), `platform/themes/echo/partials/menu-sidebar/includes/mobile-menu.blade.php`, `platform/themes/echo/partials/story/share-kit.blade.php` (6 channels: X / Bluesky / Facebook / WhatsApp / LinkedIn / Email + intent URLs), `app/Support/GrimbaVault.php` (cookie ↔ member sync), `app/Support/GrimbaVaultEvents.php` (privacy-safe event ledger), `app/Console/Commands/GrimbaSendVaultDigests.php` (weekly cron via `routes/console.php:255`), `app/Mail/GrimbaVaultDigestMail.php` + `resources/views/emails/vault-digest.blade.php`.
- Code surface (API surrogates today): `routes/web.php` + `platform/themes/echo/routes/web.php` register `/feed.xml`, `/feed.breaking.xml`, `/feed.latest.xml`, per-category feeds, `/sitemap-grimba.xml`, `/health` JSON, `/up`. `app/Http/Controllers/AdvertiserLeadController.php` ships the per-IP rate-limiter pattern (`RateLimiter::attempt('advertiser-lead:' . sha1($ip), ...)`).
- Code surface (no API v2 today): no `routes/api.php` registration in `RouteServiceProvider`; no Sanctum / Passport / OAuth packages; no `tenants` / `api_keys` tables.
- Admin surface: `/admin/grimba/translation-rules` (rule-engine knobs per `grimba-admin-translation-rules.php`), `/admin/grimba/translation-monitor` (backlog + cap dashboard per `grimba-admin-translation-monitor.php`), `/admin/grimba/newsletter` (per S684 admin pack), `/admin/grimba/subscribers` + `coffre/export.csv` (per S1010 surrogate).
