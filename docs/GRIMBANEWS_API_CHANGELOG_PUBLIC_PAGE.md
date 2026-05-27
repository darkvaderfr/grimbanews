# GrimbaNews — API Changelog Public Page

**Status:** plan v0
**Owner:** Liam Smith (PM) + Michael O'Connor (Technical Writer)
**Walks:** Mythos S1704 (API changelog public page) deferred → partial
**Gating dependency:** Public API live (yes — Wave NNNN, RRRR).

## Why this exists

B2B API customers need to know about breaking changes, new endpoints, deprecations. Standard publisher API tooling.

## v1 design

`/api/changelog` (HTML + RSS feed):

```
## 2026-06-15 — v1.2
### Added
- New ?country=ISO-2 filter on /api/middle-ground.json
- /api/clusters.json endpoint (Pro+ tier)

### Changed
- /api/sources.json now includes ownership_type field

### Deprecated
- /api/middle-ground/legacy.json (sunsets 2026-09-15)
```

## Cadence

- Per-release: update changelog before deploying.
- RSS feed for API customers to subscribe.
- Per-deprecation: 90-day notice.

## Cross-references

Master plan: S1704. Sister: `docs/GRIMBANEWS_MIDDLE_GROUND_API_REFERENCE.md`, `docs/GRIMBANEWS_API_STATUS_PAGE_DESIGN.md`.
