# GrimbaNews — Brazilian Portuguese (PT-BR) hreflang Wiring Plan

**Status:** plan v0 (one-line change to `GrimbaLocaleEnforce::PRIMARY_LOCALES`)
**Owner:** Nina Patel (Lead Frontend) + Olivia Davis (Marketing Strategist) on SEO sign-off
**Walks:** Mythos S1119 (PT-BR hreflang) deferred → partial
**Gating dependency:** PT-BR catalog (S1111) ships first.

## Why this exists

S1119 mirrors S1109 (ES). One-line edit; wiring + guardrails operator-side.

## Change set

```php
private const PRIMARY_LOCALES = ['fr', 'en', 'es', 'pt_BR'];  // add pt_BR
```

## hreflang spec

```html
<link rel="alternate" hreflang="pt-BR" href="https://grimbanews.com/pt-BR/{path}">
```

Per-page guardrails identical to ES — never emit hreflang for a locale that lacks content on the resource.

## Acceptance gates

Same as ES hreflang.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1119 row)
- Sister docs: `docs/GRIMBANEWS_ES_HREFLANG_WIRING.md`, `docs/GRIMBANEWS_PT_BR_LOCALE_CATALOG_PLAN.md`
- Existing infrastructure: `app/Http/Middleware/GrimbaLocaleEnforce.php`, `resources/views/partials/grimba-seo-head.blade.php`
