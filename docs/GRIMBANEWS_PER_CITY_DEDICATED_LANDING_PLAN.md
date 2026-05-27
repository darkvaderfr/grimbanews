# GrimbaNews — Per-City Dedicated Landing Page

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Nina Patel (Lead FE) + per-region editor
**Walks:** Mythos S1608 (per-city dedicated landing copy / editorial brief) deferred → partial
**Gating dependency:** `local_cities` table + per-city CMS surface; current `/local` view shares one heading template.

## Why this exists

Reader in Toulouse wants Toulouse-specific local news + editorial framing. `/local?city=toulouse` filters posts but uses a generic heading. Dedicated per-city landing surfaces local-relevance editorial.

## v1 design

`/local/{city-slug}` (e.g. `/local/toulouse`):
- Per-city hero with city-specific headline (operator-curated)
- Per-city headline cluster
- Per-city editor-curated picks
- Per-city per-topic rails (politics + culture + sports + transport)
- Per-city events calendar
- Per-city methodology cross-link

## Schema (gates on Vader migration approval)

```
local_cities:
  id | name | slug | country | region | population | editor_brief TEXT
   | hero_image_url | tagline | created_at | updated_at
```

## Per-city editor brief

Each city has a 1-paragraph editor brief explaining the city's media landscape + GrimbaNews's editorial commitment to it.

## Per-city onboarding

Per-city launch:
- City selected by per-region editor.
- Per-city brief drafted + Lucy reviewed.
- Per-city source-roster expansion (ensure ≥ 3 sources per city).
- Per-city soft-launch + 30-day monitoring.

## Cross-references

Master plan: S1608. Sister: `docs/GRIMBANEWS_CITY_TAXONOMY_SCHEMA.md` (Wave LLL), `docs/GRIMBANEWS_DOM_TOM_SOURCE_ROSTER.md`.
