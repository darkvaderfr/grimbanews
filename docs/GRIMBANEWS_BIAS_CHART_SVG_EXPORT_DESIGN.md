# GrimbaNews — Bias Chart SVG Export Design

**Status:** plan v0
**Owner:** Nina Patel (Lead FE) + Rajesh Kumar (Backend)
**Walks:** Mythos S1664 (bias chart embed — SVG export) deferred → partial
**Gating dependency:** current chart is HTML+CSS (not SVG); requires re-render path.

## Why this exists

Partners embedding bias charts want a static SVG asset they can:
- Drop into print articles.
- Include in newsletter (where iframe doesn't render).
- Use in academic papers.

Today the chart is HTML+CSS only. SVG export adds a second render path with identical data.

## v1 design

- New endpoint: `/groupes/{slug}/bias.svg`.
- Server-side SVG renderer (using existing `GrimbaSourceBreakdown` data).
- Default style matches on-site chart.
- `?theme=dark|light|print` for theme variants.
- `?width=NNN` for sizing (max 1600).

## Implementation

- Plain string-templated SVG (no headless browser).
- Embeds Grimba attribution text + cluster URL.
- Cacheable for 1h via HTTP cache header.

## Accessibility

- `<title>` element for screen readers.
- `<desc>` describing bias mix in prose.
- Color choices meet WCAG AA contrast.

## Cross-references

Master plan: S1664. Sister: S1665 (PNG export), S1660 (embed launch), `GRIMBANEWS_EMBED_WIDGET_SPEC.md`.
