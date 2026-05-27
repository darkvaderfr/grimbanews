# GrimbaNews — Per-Region Currency + Date Format Plan

**Status:** plan v0
**Owner:** Nina Patel (Lead FE) + Liam Smith (PM) + per-locale editor
**Walks:** Mythos S1143 (i18n beyond UI catalog — currency, date, address format) deferred → partial
**Gating dependency:** Per-locale catalog shipped (Wave WWW for ES/PT-BR/DE/IT/etc.).

## Why this exists

Per-locale UI catalogs handle string translation. Currency, date, and address formatting need locale-aware formatters separately:

- Date format: `Y-m-d` (US) vs `d/m/Y` (FR) vs `d.m.Y` (DE) vs `Y年m月d日` (JA)
- Currency: `$1,234.56` (US) vs `1 234,56 €` (FR) vs `1.234,56 €` (DE) vs `¥1,234` (JA)
- Number format: thousands separator, decimal mark, grouping
- Address format: street order, postal code position, country last vs first

## v1 — use PHP Intl extension

`GrimbaLocaleFormatter::date($timestamp, $locale)` wraps `IntlDateFormatter` with sensible per-locale defaults.

`GrimbaLocaleFormatter::currency($amount, $currency_iso, $locale)` wraps `NumberFormatter::CURRENCY`.

`GrimbaLocaleFormatter::number($amount, $locale, $decimals=0)` wraps `NumberFormatter::DECIMAL`.

## v2 — per-locale config overrides

Some publishers want non-standard format (e.g. "Le Monde uses dd/MM/yyyy not dd/MM/yy"). Per-locale config in `lang/<locale>/formats.json`:

```json
{
  "date_short": "{day}/{month}/{year}",
  "date_long": "{day} {month_name} {year}",
  "currency_default": "EUR",
  "thousands_sep": " ",
  "decimal_mark": ","
}
```

## Wiring

- Blade: `@grimbaDate($post->published_at)` → uses current locale.
- API: `/api/middle-ground.json` keeps ISO-8601 (machine-readable); HTML render uses formatter.
- Per-locale formatting tested via `tests/Feature/GrimbaLocaleFormatterTest.php` (gates on shipping the formatter).

## Cross-references

Master plan: S1143. Sister: `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_DE_LOCALE_CATALOG_PLAN.md` (Wave WWW).
Code: `app/Support/GrimbaLocaleFormatter.php` (planned).
