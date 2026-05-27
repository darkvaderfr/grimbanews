# GrimbaNews — Embed Responsive Sizing Plan

**Status:** plan v0
**Owner:** Nina Patel (Lead FE) + Alex Morgan (UI/UX)
**Walks:** Mythos S1659 (embed responsive sizing) deferred → partial
**Gating dependency:** Wave SUB-21 embed.js bundle live.

## Why this exists

Embed renders on third-party pages of all widths (article body 600px, sidebar 320px, footer banner 100% width). Per-viewport rendering must adapt.

## v1 breakpoints

- **Compact (< 400px):** stacked layout, hide secondary metadata.
- **Default (400-700px):** standard 2-column layout (cluster info + L/C/R bar).
- **Wide (> 700px):** standard + source preview thumbnails.

## Implementation

embed.js queries host element's `getBoundingClientRect().width` on mount:
- ResizeObserver to re-render on host resize.
- Per-breakpoint, swap rendered template.
- Smooth transitions; no FOUC.

## Aspect ratio

- Per-cluster card: ~2:1 (wider than tall).
- Per-cluster expanded view: ~3:2.
- Per-embed mode: explicit `data-grimba-mode="card|expanded"` opt-in.

## Mobile-first defaults

Default to compact layout. Wide layout opt-in via `data-grimba-mode="wide"`.

## Cross-references

Master plan: S1659. Sister: `docs/GRIMBANEWS_EMBED_JS_SNIPPET_GENERATOR.md`, `docs/GRIMBANEWS_EMBED_CSS_ISOLATION_PLAN.md`.
