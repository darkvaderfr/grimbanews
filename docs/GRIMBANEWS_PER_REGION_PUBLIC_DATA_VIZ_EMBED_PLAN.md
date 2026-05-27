# GrimbaNews — Per-Region Public Data Viz Embed Plan

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Nina Patel (Lead FE) + Lucy Leai (Strategy) + per-region editor
**Walks:** Mythos S1145 (per-region public data viz embed) — actually walked by KKKK; using this slot for a sister-row pickup
**Gating dependency:** Per-region data source (INSEE for FR, IBGE for BR, IPUMS for international, OWID for climate).

## Why this exists

Editorial coverage of policy stories benefits from inline data viz embeds (e.g. unemployment trend chart, climate temperature anomaly chart). Per-region public data sources let us add context without leaving the article.

## v1 design

`@grimbaViz('<viz-key>')` Blade directive renders inline iframe or SVG chart:

- INSEE for FR economic + demographic data
- IBGE for BR same
- OWID for climate + global health
- Statista (paid) for industry data

## Per-viz schema

```php
'gdp_growth_fr_2020_2025' => [
    'source' => 'INSEE',
    'series_id' => '...',
    'url' => 'https://www.insee.fr/api/...',
    'cache_ttl' => 86400,  // 1 day
    'license_note' => 'INSEE public-data license',
]
```

## UX

- Inline below article paragraph that references the data point.
- Per-viz attribution badge (INSEE / OWID / etc.).
- Per-viz "download CSV" link for readers who want the raw data.
- Per-viz "embed this chart" widget for outside re-use.

## Cross-references

Master plan: S1145 sister area. Sister: `docs/GRIMBANEWS_DATASET_CSV_SCHEMA.md` (Wave LLL).
