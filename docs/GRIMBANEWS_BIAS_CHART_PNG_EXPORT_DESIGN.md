# GrimbaNews — Bias Chart PNG Export Design

**Status:** plan v0
**Owner:** Nina Patel (Lead FE) + Jacob Lee (DevOps)
**Walks:** Mythos S1665 (bias chart embed — PNG export) deferred → partial
**Gating dependency:** server-side HTML→PNG rasterizer (Playwright / Puppeteer / Chromium-headless) + S1664 SVG endpoint to feed off.

## Why this exists

PNG is the lowest-common-denominator embed format (works in newsletters, slide decks, every social platform). Builds on S1664 SVG (rasterize from SVG instead of from live page).

## v1 design

- Pipeline: SVG (S1664) → rasterizer → PNG.
- Endpoint: `/groupes/{slug}/bias.png`.
- Sizes: 600x400 (default), 1200x800 (retina), 1920x1080 (HD).
- 24h cache.

## Rasterizer choice

- v1: librsvg + cairo (lightweight, no browser dependency).
- v2: Playwright if SVG features needed exceed librsvg coverage.
- Avoid headless Chrome at v1 (memory cost on VPS).

## Throughput

- Async render queue (per-cluster cache means first request slow, subsequent fast).
- Max 100 concurrent renders (queue beyond).
- 95p latency target: < 800ms when warm cache, < 4s cold.

## Cross-references

Master plan: S1665. Sister: S1664 (SVG source), S1660 (embed launch). Memory: `feedback_selfcheck_always.md`.
