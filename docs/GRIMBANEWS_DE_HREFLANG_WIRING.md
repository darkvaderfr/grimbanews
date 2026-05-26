# GrimbaNews — German (DE) hreflang Wiring Plan

**Status:** plan v0 (one-line change to `GrimbaLocaleEnforce::PRIMARY_LOCALES`)
**Owner:** Nina Patel (Lead Frontend) + Olivia Davis (Marketing Strategist) on SEO sign-off
**Walks:** Mythos S1129 (DE hreflang) deferred → partial
**Gating dependency:** DE catalog (S1121) ships first.

## Change set

```php
private const PRIMARY_LOCALES = ['fr', 'en', 'es', 'pt_BR', 'de'];  // add de
```

## hreflang spec

```html
<link rel="alternate" hreflang="de" href="https://grimbanews.com/de/{path}">
```

Optional finer-grained: `de-DE`, `de-AT`, `de-CH` — defer until per-DACH split is justified by traffic.

## Acceptance gates

Same as ES hreflang.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1129 row)
- Sister docs: `docs/GRIMBANEWS_ES_HREFLANG_WIRING.md`, `docs/GRIMBANEWS_DE_LOCALE_CATALOG_PLAN.md`
- Existing infrastructure: `app/Http/Middleware/GrimbaLocaleEnforce.php`
