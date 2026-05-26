# GrimbaNews — Spanish (ES) hreflang Wiring Plan

**Status:** plan v0 (one-line change to `GrimbaLocaleEnforce::PRIMARY_LOCALES`, gated on catalog)
**Owner:** Nina Patel (Lead Frontend) + Olivia Davis (Marketing Strategist) on SEO sign-off
**Walks:** Mythos S1109 (ES hreflang) deferred → partial
**Gating dependency:** ES catalog (S1101) ships first; hreflang exposes ES URLs Google can crawl — emitting hreflang for a locale with no content is a search-quality negative.

## Why this exists

S1109 is a one-line edit (per existing deferral note). The reason it's deferred is correctness: emit `hreflang="es"` only when ES content actually exists. This doc captures the wiring + safety guardrails.

## Change set (when ES catalog ships)

```php
// app/Http/Middleware/GrimbaLocaleEnforce.php
private const PRIMARY_LOCALES = ['fr', 'en', 'es'];  // was ['fr', 'en']
```

That single diff cascades:

- `resources/views/partials/grimba-seo-head.blade.php` already iterates `PRIMARY_LOCALES` to emit hreflang tags — no template change.
- `resources/views/partials/grimba-chrome.blade.php` language switcher gains ES entry — no template change.
- `routes/web.php` locale group already wraps every public route — no route change.
- Sitemap (`app/Http/Controllers/SitemapController`) regenerates with ES URLs on next rebuild.

## hreflang spec (per page)

```html
<link rel="alternate" hreflang="fr" href="https://grimbanews.com/fr/{path}">
<link rel="alternate" hreflang="en" href="https://grimbanews.com/en/{path}">
<link rel="alternate" hreflang="es" href="https://grimbanews.com/es/{path}">
<link rel="alternate" hreflang="x-default" href="https://grimbanews.com/fr/{path}">
```

## Per-page guardrails

- Emit `hreflang="es"` only if the resource has an ES translation OR the route is a chrome page (landing, about, methodology, categories, search) — never on an article that lacks ES translation.
- Article reader (`/dossier/{id}`, `/blog/{slug}`): check `post_translations` table for `es` row before emitting tag.
- Empty-translation guard: `GrimbaSeoHead::hreflangsFor($post)` filters available locales.

## Acceptance gates

1. View source of `/es` → see all three hreflang tags + x-default.
2. View source of `/dossier/{id}` where only FR translation exists → see only `hreflang="fr"` + x-default.
3. View source of `/dossier/{id}` where FR + EN + ES exist → all three emit.
4. Google Search Console "International Targeting" report shows ES as discovered locale within 7 days.
5. No broken-canonical errors.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1109 row)
- Sister docs: `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_PT_BR_HREFLANG_WIRING.md`, `docs/GRIMBANEWS_DE_HREFLANG_WIRING.md`
- Existing infrastructure: `app/Http/Middleware/GrimbaLocaleEnforce.php`, `resources/views/partials/grimba-seo-head.blade.php`, `App\Support\GrimbaSeoHead`
