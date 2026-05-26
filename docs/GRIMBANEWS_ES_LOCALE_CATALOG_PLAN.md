# GrimbaNews — Spanish (ES) Locale Catalog Plan

**Status:** design v0 (no `lang/es.json` catalog shipped; detector covers `es` per `GrimbaLanguageDetectorTest`)
**Owner:** Liam Smith (PM) on string-coverage spec + Nina Patel (Lead Frontend) on i18n routing wiring + Michael O'Connor (Technical Writer) on draft string review
**Walks:** Mythos S1101 (ES site UI catalog), S1102 (ES landing), S1103 (ES editorial pages), S1109 (ES hreflang) deferred → partial
**Gating dependency:** ES translator + editorial reviewer (no current Spanish-speaker on the Iboga roster cleared for editorial sign-off; current FR/EN catalogs are reviewed by Lucy + Liam in-house). Catalog + UX wiring itself is operator-side; gates ship on hiring or contracting an ES reviewer.

## Why this exists

S1101-S1109 share a single blocker: `lang/es.json` does not exist. The deferral is honest — until the catalog ships, all dependent rows (landing, editorial pages, hreflang) are blocked. But the **operator-side preparation** can move ahead: define which keys need ES strings, draft the wiring change, pre-list the editorial categories that will need Spanish equivalents, and document the per-locale ops checklist. That's what this doc is.

## Current state

- Detector: `App\Support\GrimbaLanguageDetector` already returns `es` when the browser `Accept-Language` header advertises it. Test: `tests/Feature/GrimbaLanguageDetectorTest::test_detects_es_from_accept_language()`.
- Locale enforcement: `App\Http\Middleware\GrimbaLocaleEnforce::PRIMARY_LOCALES` currently equals `['fr', 'en']`. Adding `'es'` is a one-character diff but cascades to hreflang, URL prefixes, and the language switcher.
- Translation infrastructure: `App\Support\GrimbaTranslationPresenter` resolves per-post translations from `post_translations` table — already ES-capable on schema, no DB change needed.
- Catalog files: `lang/fr.json` (canonical, ~1,200 keys), `lang/en.json` (~1,200 keys). ES would mirror those keys 1:1.

## Catalog scope

### Tier 1 — Site chrome (~ 220 keys)

- Header nav (Acceuil, Catégories, Recherche, Local, Vault, Pour vous)
- Footer (mentions légales, RGPD, contact, à propos)
- Cookie consent banner (FR + EN baseline; ES = third locale)
- Language switcher labels
- 404 / 500 / 503 error pages
- PWA install prompt
- Sign-in / sign-up form labels
- /account preference center labels

### Tier 2 — Reader surfaces (~ 480 keys)

- Article reader chrome (Lire l'original, Voir les sources, Partager, Sauvegarder)
- Dossier voices ("X sources couvrant cette histoire" → "X fuentes cubriendo esta historia")
- Bias distribution bar tooltip
- Freshness pills (Frais → Fresco, Récent → Reciente, Daté → Antiguo)
- Translation banner ("Cet article a été traduit du français" → "Este artículo fue traducido del francés")
- Empty-state messages
- Pagination labels

### Tier 3 — Account + saved-search (~ 320 keys)

- Vault folder labels
- Saved-search digest copy
- Email digest subject lines (depend on per-locale send infra)
- Member preference labels
- GDPR data export labels

### Tier 4 — Marketing surfaces (~ 180 keys)

- /a-propos (about page) hero + body
- /methodology page
- /transparency page (when shipped; S2001+)
- Landing CTAs

**Total estimated key count: ~1,200 (mirrors FR canonical).**

## Wiring change

Once `lang/es.json` lands:

1. `App\Http\Middleware\GrimbaLocaleEnforce::PRIMARY_LOCALES = ['fr', 'en', 'es']` — one-line diff (S1109 hreflang follows automatically).
2. `resources/views/partials/grimba-chrome.blade.php` — language switcher already iterates `PRIMARY_LOCALES`; auto-picks ES.
3. URL prefix: `/es/` lands automatically (per existing `RouteServiceProvider` LocaleEnforce).
4. hreflang `<link>` tags auto-emit (per existing `grimba-seo-head.blade.php`).
5. JSON-LD `inLanguage` reflects active locale.

## Per-locale ops checklist (S1140 — multi-language launch ops)

- [ ] Draft Spanish strings for Tier 1 chrome (operator + LLM-assist draft, ES native review required before publish)
- [ ] Draft Spanish strings for Tier 2 reader surfaces
- [ ] Draft Spanish strings for Tier 3 account
- [ ] Draft Spanish strings for Tier 4 marketing
- [ ] Editorial: ES-native reviewer pass on every key
- [ ] Engineering: add `es` to `PRIMARY_LOCALES`
- [ ] QA (Sara Kim): Playwright smoke for `/es/`, `/es/categorie/{slug}`, `/es/dossier/{id}`, `/es/blog/{slug}`
- [ ] SEO (Olivia Davis): submit ES sitemap to Google Search Console
- [ ] Comms (Henry Walker): launch announcement on /es/blog + LinkedIn ES posts
- [ ] Analytics: confirm Plausible / GA splits visits by locale prefix

## Editorial considerations

ES catalog is the third locale; FR + EN gave us the bilingual baseline. ES introduces the **first non-Romance-pair test** of:
- Sentence length expansion (ES often 10-15% longer than FR for the same content — chrome buttons need width audit)
- Gendered grammar across UI labels (current FR uses masculine-by-default; need an audit pass for ES inclusive language conventions)
- Regional variants: catalog ships as **es-ES (Iberian)** baseline; future es-419 (LATAM) variant gates on demand signal.

## Things deliberately NOT in this plan

- **Auto-machine-translation publish at first launch** — every ES string gets human review; NobuAI may draft but not ship without editorial sign-off (per editorial style guide).
- **Per-region ES variants on day 1** — Iberian Spanish first; LATAM split when traffic justifies.
- **ES content sourcing** — `news_sources` ingest pipeline does not yet have an ES source roster (lands separately; not blocked by catalog).

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1101, S1102, S1103, S1109, S1110 rows)
- Sister docs: `docs/GRIMBANEWS_LANGUAGE_SPRINT_PLAN.md`, `docs/GRIMBANEWS_LANGUAGE_TAGGING_PLAN.md`, `docs/GRIMBANEWS_LEGAL_PAGES_LOCALIZATION_MATRIX.md`, `docs/GRIMBANEWS_PT_BR_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_DE_LOCALE_CATALOG_PLAN.md`
- Existing infrastructure: `lang/fr.json`, `lang/en.json`, `App\Support\GrimbaLanguageDetector`, `App\Http\Middleware\GrimbaLocaleEnforce`, `resources/views/partials/grimba-chrome.blade.php`
- Tests: `tests/Feature/GrimbaLanguageDetectorTest`, `tests/Feature/GrimbaLocaleEnforceTest`
