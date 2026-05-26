# GrimbaNews — Spanish (ES) Landing Page Scope

**Status:** scope v0 (no `/es/` landing; depends on ES catalog per `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`)
**Owner:** Steve Jobs (CPO) on hero layout + Liam Smith (PM) on copy spec + Alex Morgan (UI/UX) on visual sign-off
**Walks:** Mythos S1102 (ES landing) deferred → partial
**Gating dependency:** ES catalog (S1101) must ship first — landing copy keys reference catalog entries. ES-native reviewer required for above-the-fold copy.

## Why this exists

S1102 was honest-deferred as "depends on S1101 catalog." This is true at implementation time, but the **scope spec** for the ES landing page is operator-side and can be drafted now. When the catalog ships, landing wiring is hours, not days.

## Hero layout (mirrors FR canonical)

- Above-the-fold: Headline + sub + primary CTA (Lire les sources → Leer las fuentes)
- Bias bar demo: live preview of dossier voices on a featured cluster, translated to ES
- 3-card credibility row: methodology link, sources count (live count), transparency link
- Cookie consent: bilingual today (FR + EN); ES adds third tab

## Layout decisions

- Reuse `resources/views/marketing/landing.blade.php` template; swap copy keys via `__('landing.hero.title')` etc.
- ES sub-routes: `/es`, `/es/categorie/{slug}`, `/es/dossier/{id}` (English `category` mirrors; FR stays `categorie` per legacy URL stability — ES decision: use `categoria` for SEO clarity).
- Visual identity: same Steve cinematic tokens; no per-locale visual variant.

## Acceptance gates

1. `/es` returns 200 with all chrome strings resolved (no `__('key.missing')` leaks).
2. Lighthouse SEO score >= 90 on `/es`.
3. JSON-LD `inLanguage` = `es`.
4. OG card auto-emits ES title + description.
5. Sitemap `sitemap.xml` includes ES URLs.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1102 row)
- Sister docs: `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_ES_EDITORIAL_PAGES_SCOPE.md`
- Existing infrastructure: `resources/views/marketing/landing.blade.php`, `App\Http\Controllers\MarketingController`
