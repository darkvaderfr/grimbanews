# GrimbaNews — Per-Region Homepage Hero Localization

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Nina Patel (Lead FE) + per-region editor
**Walks:** Mythos S1582 (per-region homepage hero localization) deferred → partial
**Gating dependency:** Per-region cluster volume + per-region editor onboarded.

## Why this exists

A reader from Brazil hitting / sees the same FR-default homepage as a Paris reader. With multiple regions live, regional homepages let each see their own headlines first while preserving the global brand.

## v1 design

Edition cookie (already exists) determines `?region=fr|br|de|...`. Per-region:

- Hero headline: top story by per-region cluster ranking
- Below-hero rail: regional cluster summary
- Per-region MG/BS rails (the editorial signals scoped to region)
- "Voir le monde" toggle: switches back to global default

## Per-region content surface

Each `/region/{slug}` (or query-param-driven /) renders:
- Per-region top dossiers (cluster filter `editorial_region={slug}`)
- Per-region Middle Ground (cluster filter mg_ AND region match)
- Per-region Blindspots
- Per-region trust dashboard (Wave AABB) deep-link
- Per-region newsletter signup CTA

## Editor cadence

- Per-region editor curates daily 1 hour ahead of regional prime-time.
- Override mechanism: editor can pin a story to per-region hero.

## Cross-references

Master plan: S1582. Sister: `docs/GRIMBANEWS_PER_REGION_TRUST_DASHBOARD_PLAN.md`, `docs/GRIMBANEWS_PER_REGION_DAILY_DIGEST_CADENCE.md` (Wave KKKK).
