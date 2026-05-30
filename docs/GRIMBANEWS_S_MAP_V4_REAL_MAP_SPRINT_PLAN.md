# S-MAP-v4 — Real Open-Source Map Framework Rebuild

**Author:** Mythos under Vader directive 2026-05-29.
**Supersedes:** the v1-v3 hand-rolled SVG implementation (commits `73a281e2` → `b6b0fbb7`). v1-v3 stays in main as a working fallback; v4 ships as a parallel branch then replaces.
**Status:** ready for fresh session.

---

## Why we're starting over

Vader 2026-05-29: *"this design is not what I was looking for. I said real world map — use a real framework opensource map provider with real geolocation data that is hooked up to existing system."*

The v1-v3 SVG was a hand-traced approximation. Even at 60+ vertices per continent it reads as cartoonish — proportions are wrong, coastlines are smoothed past recognition, no countries visible, no real geocoding. The right move is to use a real mapping library with real GeoJSON country boundaries and real lat/lng coordinates for every news event.

## What "real map" means here

1. **Open-source mapping framework** — Leaflet.js (default), MapLibre GL JS (stretch), or D3 + TopoJSON (server-render). All MIT/BSD/Apache. No proprietary key-required services (no Mapbox/Google).
2. **Real geographic data** — GeoJSON country boundaries from Natural Earth (public domain) OR vector tiles from CARTO / Stadia (free tier with attribution) OR a self-hosted MapLibre style.
3. **Real coordinates per news event** — each post pinned at its source's country centroid (lookup table). Cluster when zoomed out (Leaflet.markercluster or MapLibre supercluster).
4. **Hooked into the existing system** — read from current `posts` + `news_sources` tables. Reuse `App\Support\GrimbaHomeFeed::breakingByContinent()` + `App\Support\Continents` from v2. Reuse `/api/middle-ground.json` as one data source. NobuAI brand rule applies: no provider names on reader surfaces (Leaflet/OSM attribution is fine — they're map *data*, not LLMs).

## Architecture

### Stack decision — **Leaflet.js + Natural Earth GeoJSON + CARTO dark tiles**

| Layer | Choice | Why |
|---|---|---|
| Map library | **Leaflet 1.9.x** (40KB gz, MIT) | Lightest, most-used, easy clustering, server can ship raw HTML+JS without Vite build chain |
| Tile provider | **CARTO Dark Matter** (free tier, attribution required) | Already dark and "futurist"-adjacent, free for reasonable traffic, no API key |
| Country boundaries | **Natural Earth 1:110m** simplified GeoJSON (~250KB gz, public domain) | Real shapes, properly geo-accurate, lightweight |
| Country centroids | **Static PHP lookup table** built from Natural Earth | ~200 ISO-2 entries with `[lat, lng]`, no runtime geocoding API needed |
| Clustering | **Leaflet.markercluster 1.5.x** (MIT) | Standard clustering plugin, handles thousands of markers |
| Pin styling | Inline SVG bias-dot markers (per Wave MMMMMMMM palette) | Matches the existing bias system (left blue / center grey / right red / MG purple) |

### Why NOT MapLibre GL JS for v4

MapLibre is better for production-grade vector tiles but requires (a) a vector tile source like MapTiler or self-hosted PMTiles, (b) a webpack/Vite build for the GL renderer (300KB+ gz), and (c) more wiring for marker clustering. Leaflet ships as a single CDN script + matches our "drop in and works" stage. MapLibre is the v5 upgrade path.

### Data flow

```
posts table ───┐
               │
news_sources ──┴─── join on source_id ───► GrimbaHomeFeed::pinsForMap($windowHours)
                                                  │
                                                  ▼
                                       returns array of:
                                         { lat, lng, country, bias_rating,
                                           title, url, source_name,
                                           published_at, cluster_id? }
                                                  │
                                                  ▼
                              /api/breaking-map.json (cached 60s)
                                                  │
                                                  ▼
                              Leaflet on /breaking-map fetches it once
                              + ConditionalGet ETag for cheap refresh
                                                  │
                                                  ▼
                              markercluster groups dense regions,
                              expands on zoom-in,
                              click pin → existing article URL
```

### Per-country centroid table

Static PHP lookup `App\Support\CountryCentroids` — ISO-2 → `[lat, lng]`. Built from Natural Earth `ne_110m_admin_0_countries` centroids. ~200 rows. Pure data, no I/O.

Centroids are intentionally not perfect — a story published by a US-based source is pinned at the US centroid (somewhere in Kansas), not at the actual story location. That's correct for v4 (Vader's brief says "where the source is," not "where the event is"). Phase 2 can add story-level geocoding via NobuAI.

### Visual style

**Futurist HUD on top of a real map:**
- Base layer: CARTO Dark Matter tiles
- Overlay: subtle cyan grid + scan-line CSS over the map container (same as v2-v3 chrome)
- Bias-color pulsing markers (4-tier: left blue, center grey, right red, MG purple — uses `GrimbaClusterBias::biasMetaForBlade()` palette)
- Cluster bubbles styled in MG purple with bias-mix mini-donut (server-rendered or client-rendered)
- Top chrome carries the LIVE indicator + window + Pause + Fullscreen buttons (reused from v2-v3)
- Side panel (desktop) shows the 6-continent ticker breakdown (carried over from v3)
- Mobile: map fills viewport, scrollable card stack below

### Fullscreen mode

Same `Element.requestFullscreen` API as v3. Leaflet handles map resize on the `fullscreenchange` event via `map.invalidateSize()`.

### Performance budget

- First map paint ≤ 1.2s on mobile 4G
- GeoJSON country layer ≤ 250KB gzip
- CARTO tile loads ≤ 6 concurrent at the default zoom
- Marker cluster expand frame ≥ 30fps

### Accessibility

- Skip-to-list link at top: "Jump to the linear list of breaking stories"
- ARIA live region announces cluster updates
- Pins are keyboard-tabbable
- `prefers-reduced-motion` disables pin pulse animations
- Tile loading status announced

### NobuAI brand check

Leaflet attribution shows "Leaflet | © OpenStreetMap contributors | © CARTO" in the bottom-right. All three are open mapping projects — none are LLM providers. Brand rule is satisfied without modification. Do not add "Powered by NobuAI" pin (we don't generate the map).

---

## Sprint fleet (S-MAP-V4-01 → S-MAP-V4-22)

22 sprints across audit / big / polish cadence per `feedback_sprint_cadence_audit_big_polish.md`. Estimated total ~26h.

### Phase 1 — Foundation (5 sprints, ~5h)

| Sprint | Cat | Description | Est | Deps |
|---|---|---|---|---|
| V4-01 | audit | Pin v1-v3 in main as `/breaking-map-legacy` (just rename the route) before any v4 work touches the file. Confirms no rollback risk. | 30m | — |
| V4-02 | audit | Vendor Leaflet 1.9.4 + Leaflet.markercluster 1.5.3 to `public/vendor/leaflet/` from npm tarballs (no CDN — CSP-safe). Document license + version in `public/vendor/leaflet/README.md`. | 60m | — |
| V4-03 | audit | Vendor Natural Earth `ne_110m_admin_0_countries` GeoJSON (simplified to ≤250KB gz) to `public/vendor/natural-earth/world.geojson`. Document source URL + license (PD). | 60m | — |
| V4-04 | big | Build `App\Support\CountryCentroids` — ISO-2 → `[lat, lng]` static lookup. ~200 entries from Natural Earth centroids. Pure data class. Unit test pinning ~10 representative cases. | 90m | 03 |
| V4-05 | big | Extend `GrimbaHomeFeed` with `pinsForMap($windowHours, $perCountry = 5)` — joins `posts` → `news_sources`, groups by country, returns `[{ country, lat, lng, posts: [...] }]`. Reuses the cache + locale-strict pattern from `breakingByContinent`. | 90m | 04 |

### Phase 2 — Data layer (3 sprints, ~3h)

| Sprint | Cat | Description | Est | Deps |
|---|---|---|---|---|
| V4-06 | big | Public JSON endpoint `/api/breaking-map.json` — wraps `pinsForMap` with the same Cache-Control + CORS pattern as `/api/middle-ground.json`. Includes per-pin `bias_color`, `dossier_url`, `total_at_country`. | 60m | 05 |
| V4-07 | polish | Add `summary` block to `/api/breaking-map.json` (total countries, total pins, top-3 countries by volume) so chart widgets get aggregates without iterating. Same pattern as Sprint FF. | 45m | 06 |
| V4-08 | big | Lock test: `/api/breaking-map.json` shape contract (top-level keys, per-pin keys, ETag/Cache-Control headers, `summary` block, `pins[]` count ≤ requested cap). | 75m | 07 |

### Phase 3 — Map view (6 sprints, ~7h)

| Sprint | Cat | Description | Est | Deps |
|---|---|---|---|---|
| V4-09 | big | New view `breaking-map.blade.php` (replaces v3) — Leaflet map container, CARTO Dark Matter tile layer, world.geojson country fill layer, top chrome with LIVE + window + Pause + Fullscreen reused from v3. | 90m | 02, 03, 06 |
| V4-10 | big | Client JS — fetch `/api/breaking-map.json`, drop a marker per pin colored by bias_rating, wire markercluster with custom cluster icon (purple-glow circle with count). | 90m | 09 |
| V4-11 | polish | Per-pin popup — article title, source name, "Lire l'article →" CTA. Locale-aware. | 45m | 10 |
| V4-12 | polish | Per-cluster click expands to underlying pins (Leaflet.markercluster default behavior + tuned `disableClusteringAtZoom` + `maxClusterRadius`). | 30m | 10 |
| V4-13 | polish | Pause control freezes auto-zoom + tile loading animations + pin pulse (CSS `[data-paused="true"]`). | 45m | 09 |
| V4-14 | polish | Fullscreen toggle + Leaflet `map.invalidateSize()` on `fullscreenchange`. | 30m | 09 |

### Phase 4 — Side panel + interactions (4 sprints, ~5h)

| Sprint | Cat | Description | Est | Deps |
|---|---|---|---|---|
| V4-15 | big | Right-side panel (desktop) — 6 continent rows with post counts + bias donut + click-through to `/breaking?region=X`. Carries v3 functionality into v4 as a sidecar. | 75m | 09, 10 |
| V4-16 | polish | Mobile layout — map fills 60dvh, vertical card stack below. Sidecar collapses to a bottom-sheet that swipes up. | 90m | 15 |
| V4-17 | polish | Hover-on-pin highlights the corresponding continent row in the sidecar; hover-on-row pans the map to that continent + opens any cluster there. | 60m | 15 |
| V4-18 | polish | Bias filter chips above the map — toggle off Left / Center / Right / MG / Unknown; re-renders the pin layer without reloading the page. | 60m | 10 |

### Phase 5 — Lock + polish + signoff (4 sprints, ~6h)

| Sprint | Cat | Description | Est | Deps |
|---|---|---|---|---|
| V4-19 | big | Lock tests — feature test asserts: map container renders, JSON endpoint reachable, all 6 continent rows present in sidecar, Leaflet + markercluster vendored assets reachable, JSON-LD CollectionPage emitted, ARIA labels present. ~12 assertions. | 90m | 09-18 |
| V4-20 | polish | Playwright visual baselines — desktop + mobile, light + dark (this is dark-only by design, so dark mode is the only baseline), en + fr. 4 screenshots. | 60m | 19 |
| V4-21 | polish | Lighthouse mobile perf — target FCP ≤ 1.2s, LCP ≤ 2.5s, CLS ≤ 0.1. Likely needs CARTO tile preconnect + GeoJSON gzip. | 90m | 19 |
| V4-22 | polish | Audit panel (Zen/Echo/Mnemo) + remove `/breaking-map-legacy` route + nav stays "Carte" pointing at v4. Final smoke walk: desktop + 3 phone widths × 2 locales × fullscreen on. Update `project_grimbanews_next_prompt.md`. | 75m | 19, 20, 21 |

## Acceptance criteria

- Map renders real Natural Earth country boundaries.
- Each pin sits at the centroid of the source's country (not at a continent label).
- CARTO Dark Matter tile layer loads (verifiable in DevTools Network).
- Bias-color pin markers match the `GrimbaClusterBias::biasMetaForBlade()` palette.
- Clustering active at world zoom; expands by zoom level.
- Fullscreen API works (true browser fullscreen, ESC exits).
- Pause control freezes pulses + auto-zoom.
- `/api/breaking-map.json` shipped + cached + CORS-open + ETag-validated.
- Lock tests green; audit panel signs off.
- Lighthouse mobile ≥ 90.
- NobuAI brand rule preserved (no LLM provider names on the reader surface; map providers are fine).

## Out of scope for v4

- Story-level geocoding (every story still pinned at its source's country, not the event location) — Phase 2.
- Real-time WebSocket push of new pins — Phase 2.
- 3D globe / Cesium / deck.gl — Phase 3.
- Custom MapLibre vector tile theme — Phase 3 (vendoring CARTO tile theme is the v4 baseline).
- Heatmap layer — Phase 2.

## Risks + mitigations

- **CARTO free-tier traffic limit** — if `/breaking-map` becomes a viral surface, CARTO's free tier (~75K tiles/day) gets hit. Mitigation: cache tiles via CloudFlare or a tiny tile-proxy route in Phase 2.
- **GeoJSON size on slow connections** — 250KB gz is fine for desktop, marginal on 3G. Mitigation: lazy-load the GeoJSON layer (skeleton map shows tiles first, country fill loads in a second paint).
- **Marker cluster jank with 500+ pins** — Leaflet.markercluster handles this well, but custom cluster icons can regress perf. Mitigation: load-test in V4-21 against the full posts table (~4.8K posts), tune `maxClusterRadius` if needed.
- **NobuAI brand confusion** — readers might mistake the CARTO/OSM attribution for a vendor we use for AI. Mitigation: tiny "Map: © OpenStreetMap, CARTO" label is below the attribution chrome bar and visually distinct from product chrome.

## Where to slot in the master plan

- Insert as the V4 fleet replacing the v1-v3 brief (`docs/GRIMBANEWS_S_MAP_WORLD_BREAKING_FEATURE_BRIEF.md`) — that brief stays as the original requirements doc.
- Master plan row updates: S-MAP-FLEET row description changes from "v1 hand-rolled SVG" to "v4 Leaflet + Natural Earth real-map rebuild" — 22 sprints renumbered V4-01..22.

## Open questions for Vader (answer before V4-01 starts)

1. **CARTO Dark Matter vs Stadia Alidade Smooth Dark vs self-hosted MapLibre** — CARTO is the recommended default (no key required). Stadia is cleaner-looking but requires a free key. MapLibre+PMTiles is best long-term but adds 200KB to the bundle. Confirm CARTO is OK.
2. **Phase 2 (story-level geocoding) — green-light now or after v4 ships?** Story-level geocoding makes the map dramatically more useful (a story about Ukraine pinned in Kyiv, not at the source's HQ in DC). But it depends on a NobuAI geocoding pass over post bodies — non-trivial.
3. **Bias filter chips (V4-18) — show by default or only after user clicks "Filter"?** Default-on adds visual noise; default-hidden hides the discoverability.
4. **Cluster icon visual — donut showing the bias mix of pins under it, or solid purple with count?** Donut is richer but more work; purple+count is faster.
5. **Tile-proxy route in Phase 2 — yes/no, and if yes, where (own VPS vs CloudFlare workers)?** Affects CARTO traffic budget.

---

## Mythos prompt for fresh session

Paste-ready brief for a new `/resume grimbanews` session that picks this up cleanly. Copy everything between the `--- BEGIN ---` / `--- END ---` markers into a fresh session.

--- BEGIN ---

continue work on grimbanews · execute S-MAP-V4

I'm starting fresh. Read `~/.claude/projects/-Users-vb-kaizen/memory/project_grimbanews_next_prompt.md` for current state first, then read `/Users/vb/agidev/GrimbaNews/docs/GRIMBANEWS_S_MAP_V4_REAL_MAP_SPRINT_PLAN.md` for the full sprint plan.

Context recap:

The previous session built /breaking-map v1→v3 with a hand-rolled SVG world map. I (Vader) saw it and said: "this design is not what I was looking for. I said real world map — use a real framework opensource map provider with real geolocation data that is hooked up to existing system."

The v1-v3 implementation is at HEAD `b6b0fbb7` on `darkvaderfr/grimbanews` main. It stays in main as a working fallback. V4 is a parallel rebuild using Leaflet + Natural Earth GeoJSON + CARTO Dark Matter tiles, with real country centroids and real pin markers — actual GIS data, not hand-traced paths.

Execute the V4-01..V4-22 sprint fleet from the plan doc. Each sprint:
1. Real code change, tested, committed, pushed, audit/big/polish cadence per `feedback_sprint_cadence_audit_big_polish.md`.
2. Each commit on its own (one sprint = one commit) so I can review per-sprint.
3. NobuAI brand rule applies: no LLM provider names on the reader surface. Leaflet / OpenStreetMap / CARTO attribution is fine — they're map providers, not AI.
4. After each phase (5 phases total), surface the dream-team audit panel (Zen/Echo/Mnemo) before continuing.
5. Mandatory exec-team credit block at the end of each phase per `feedback_team_attribution_format.md` — pull names from `project_iboga_full_roster.md`.

Stack decisions already made in the plan (don't re-litigate, just execute):
- Leaflet 1.9.x + Leaflet.markercluster 1.5.x (vendored, not CDN)
- Natural Earth 1:110m country GeoJSON (vendored, ~250KB gz)
- CARTO Dark Matter tiles (free tier, no key)
- Static `App\Support\CountryCentroids` PHP lookup (~200 ISO-2 → [lat, lng])
- /api/breaking-map.json as the JSON data source, cached 60s, CORS-open
- Reuses `App\Support\Continents` + `GrimbaClusterBias::biasMetaForBlade()` from v2/v3

Open questions still pending Vader's answer (call them out at the start of V4-01 if not yet answered):
1. CARTO Dark Matter vs Stadia Alidade Smooth Dark vs self-hosted MapLibre — plan default is CARTO; confirm.
2. Phase 2 (story-level geocoding via NobuAI) green-light now or after v4?
3. Bias filter chips default-on or default-hidden?
4. Cluster icon visual: bias-mix donut vs solid purple+count?
5. Phase 2 tile-proxy route on VPS or CloudFlare workers?

Working directory: `/Users/vb/agidev/GrimbaNews/` (symlinked from `/Users/vb/GrimbaNews/`).
Local server: `php artisan serve` already running on port 8000 (zombie process from earlier — restart if config changes).
HEAD to branch from: `b6b0fbb7` (last v3 commit).

Push cadence: per Vader's CLAUDE.md — every commit goes to `darkvaderfr/grimbanews` main immediately after staging specific files (never `git add -A`). Co-author trailer `Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>` on every commit. No `--no-verify`.

Test cadence: existing 118 launch-readiness tests (1405 assertions) must stay green at every push. v4 adds ~20 new tests across the 22 sprints.

Run V4-01 (audit — pin v1-v3 as /breaking-map-legacy) first, then surface for Vader's go/no-go on the 5 open questions before starting V4-02. Don't blast through 22 sprints without those answers.

--- END ---

---

## Sign-off pending Vader

This plan supersedes the v1-v3 build. v1-v3 stays in main as `/breaking-map-legacy` per V4-01 so there's zero rollback risk. The 5 open questions above are the only items blocking V4-01 kickoff.
