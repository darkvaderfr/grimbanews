# S-MAP — World-Map Breaking News (feature brief)

**Authored:** 2026-05-20, Mythos under Vader directive.
**Slot in master plan:** new fleet `S-MAP-01 → S-MAP-22` (post-launch arc, sits alongside S1151-S1180 Mobile App and S1051-S1100 NobuAI Evolution).

---

## The pitch (Vader 2026-05-20 voice directive)

> "We want to show, especially on mobile device or when we build a mobile device app, bring users into a full-screen world map. We want to essentially show scrolling from right to left, breaking news for each country — but we want it to be moving on the screen left to right, right to left, different types of breaking news within that region, maybe not country, let's say continent."

The product idea: full-viewport world map; per-continent breaking-news tickers anchored over each continent; alternating direction (L→R / R→L) so the eye is drawn across the whole map, not just one corner.

## Why it matters

- **Editorial uniqueness:** every news site has a feed. Nobody has a *map* that lets you SEE the geographic distribution of "what's breaking right now." This is the kind of feature that turns "GrimbaNews is another news aggregator" into "GrimbaNews shows you the world."
- **Mobile-first:** vertical scroll feeds work but they hide spatial intuition. A map gives you the world at a glance. Phones still get full-viewport.
- **Bias signal at scale:** per-continent tickers make it visible when one continent is over-covered (Africa under-covered vs Europe, US-dominance, etc.) — a structural blindspot the bar chart can't show as viscerally.
- **NobuAI surface:** per-region NobuAI summary in the empty-continent state ("Quiet in Oceania today — last major story 6h ago") fits cleanly.

## Out-of-scope for v1

- Country-level granularity (Vader: "maybe not country, let's say continent" — continents only first).
- Real-time WebSocket push (cron + cache is fine).
- Drill-down navigation from continent to country page (post-v1).
- Heatmap / choropleth shading (post-v1).
- 3D globe / Mapbox / Cesium (post-v1; SVG works for v1).

## Architecture

### Data

- 5 continent buckets: `africa`, `americas`, `asia`, `europe`, `oceania`. Plus a 6th for `global / multi-region` (e.g. UN coverage, climate stories).
- Map a country to a continent at ingest. Existing `news_sources.country` column drives it; new lookup table `continent_for_country(iso2)`.
- New helper: `GrimbaHomeFeed::breakingByContinent($windowHours)` returning `[continent => Collection<Post>]`. Reuses existing keyword + recency + locale-strict logic; adds GROUP BY continent.

### Render

- Component: `partials/home/world-map-breaking.blade.php`. Includes SVG world map (public-domain Equirectangular projection, ~50KB) and 6 absolutely-positioned `<div>` tickers, one per continent + global.
- Animation: pure CSS `@keyframes scroll-left` and `@keyframes scroll-right`. JS only sets the duration based on content length so the speed feels consistent regardless of how many headlines a continent has.
- Direction alternation: africa=L→R, americas=R→L, asia=L→R, europe=R→L, oceania=L→R. (Pattern: random-but-deterministic, not "all in same direction.")
- Pause-on-hover (desktop) and pause-on-tap (mobile) for accessibility + reading.
- `prefers-reduced-motion: reduce` → static list, no scrolling.

### Locale + bias awareness

- Each ticker respects the reader locale (Wave LLLLLLLL contract): EN reader sees EN-eligible posts in each continent's ticker, FR reader sees FR.
- Each headline carries an inline bias dot (left blue / center grey / right red / middle-ground purple per Wave MMMMMMMM).
- "Quiet in X" empty state when a continent has zero breaking matches — NobuAI fills with a 1-line "what happened last" summary if cached.

### Mobile

- Full-viewport (`100dvh`) on screens narrower than 768px.
- Pinch-to-zoom disabled INSIDE the map container (would conflict with continent labels); browser-native zoom on the page itself still works.
- Tickers stack vertically on phones (continent labels above the ticker, scrolling text below) so the map remains the visual anchor.

### Performance budget

- TTFB ≤ 250ms (cached server-side).
- SVG map ≤ 60KB gzip (use a simplified topology).
- First contentful paint ≤ 1.5s on mobile.
- Animation runs at 60fps via `transform: translateX` (GPU compositor layer).

---

## Sprint fleet (S-MAP-01 → S-MAP-22)

22 sprints across audit / big / polish cadence per CLAUDE.md `feedback_sprint_cadence_audit_big_polish.md`. Estimated total ~28h.

| Sprint | Category | Description | Est | Deps |
|---|---|---|---|---|
| S-MAP-01 | audit | Continent-map data audit — every source has a country, but ~80 sources have NULL or non-ISO codes. Map all to continents; quarantine the rest. | 60m | — |
| S-MAP-02 | audit | World-map SVG candidate review — pick licensing-clean simplified topology (Natural Earth public-domain, simplified to ~50KB gzip). | 45m | — |
| S-MAP-03 | big | `App\Support\Continents` helper + lookup table (iso2 → continent). Includes per-source backfill command `grimba:backfill-continent`. | 90m | 01 |
| S-MAP-04 | big | `GrimbaHomeFeed::breakingByContinent($windowHours)` — reuses breaking() keyword + recency + locale-strict filter, GROUP BY continent. | 75m | 03 |
| S-MAP-05 | big | `partials/home/world-map-breaking.blade.php` — SVG world map + 6 positioned tickers (5 continents + global). | 90m | 02, 04 |
| S-MAP-06 | big | CSS keyframes `@keyframes ticker-scroll-left` + `ticker-scroll-right`; per-continent direction assignment; pause-on-hover / pause-on-tap. | 60m | 05 |
| S-MAP-07 | polish | Locale-strict ticker — every continent ticker respects `?lang=en`/`grimba_lang=en`. Reuses Wave LLLLLLLL fallback fix. | 30m | 04 |
| S-MAP-08 | polish | Bias-dot inline marker per headline (left blue / center grey / right red / middle-ground purple). Reuses Wave MMMMMMMM helper. | 45m | 05 |
| S-MAP-09 | polish | "Quiet in X" empty state per continent + optional NobuAI 1-line "last major story" summary. Cached 5 minutes. | 60m | 04 |
| S-MAP-10 | polish | `prefers-reduced-motion: reduce` → static list. Auto-scroll disabled, click-through to headlines preserved. | 30m | 06 |
| S-MAP-11 | polish | Mobile full-viewport layout (`100dvh`). Below 768px stack tickers below the map; pinch-to-zoom disabled inside the map only. | 75m | 06 |
| S-MAP-12 | polish | Pause-on-tap UX (mobile) + pause-on-hover (desktop) + ESC to resume. Single delegated touch handler. | 45m | 06, 11 |
| S-MAP-13 | polish | Accessibility — ARIA live regions per ticker (`aria-live="polite"`), screen-reader pause control, keyboard tab cycle through tickers. | 60m | 05 |
| S-MAP-14 | polish | Bias-distribution donut overlay per continent on hover/tap (mini Middle Ground / left / right percentages). | 60m | 08 |
| S-MAP-15 | big | Server-side cache — `breakingByContinent` cached 45s per locale+region, same TTL as breaking() singleton. | 30m | 04 |
| S-MAP-16 | big | Route `/breaking-map` + nav entry — full-page experience separate from home rail; home rail keeps the existing ticker. | 45m | 05 |
| S-MAP-17 | polish | Per-continent click-through to `/breaking?region={continent}` filtered view. Existing `/breaking` route gains optional region filter. | 60m | 16 |
| S-MAP-18 | polish | NobuAI integration — per-continent "What's happening" 2-sentence summary in the hover/tap popover. Cached 10 min. | 75m | 14, 09 |
| S-MAP-19 | big | Lock tests — feature test asserts: 6 tickers render, each respects locale, bias-dot color per Wave MMMMMMMM, empty-state copy, ARIA contract. | 90m | 13, 14 |
| S-MAP-20 | polish | Visual baselines (Playwright) — desktop + mobile, light + dark, ?lang=en + ?lang=fr (4 modes × 2 widths = 8 screenshots). | 60m | 19 |
| S-MAP-21 | polish | Performance budget audit — Lighthouse mobile, SVG gzip < 60KB, FCP < 1.5s, animation 60fps. | 60m | 19 |
| S-MAP-22 | polish | Release smoke — manual walk on desktop + 3 phone widths × 2 locales × 2 themes, audit panel run (Zen/Echo/Mnemo), launch readiness checklist tick. | 60m | 19, 20, 21 |

**Acceptance criteria:**

- Every continent ticker renders posts in the reader's locale (no cross-locale bleed — Wave LLLLLLLL contract).
- Bias dots match the Middle Ground / left / center / right / unknown palette (Wave MMMMMMMM contract).
- `prefers-reduced-motion` honored.
- Mobile full-viewport, tickers stack below map, pinch-zoom only on map container.
- 60fps animation via GPU compositor (`transform: translateX`).
- Lighthouse mobile ≥ 90.
- Lock tests green; audit panel signs off.

---

## Where it slots in the master plan

This brief defines the S-MAP-01..22 fleet. **Inserted into the master plan after S1180 (mobile app post-launch arc).** S-MAP can run partly in parallel with S1181-S1210 (B2B + API) — different layer, different ownership.

**Updated cumulative master-plan size: 2237 sprints (existing) + 22 S-MAP sprints = 2259 total.**

If Vader greenlights extension to country-level (Phase 2), a follow-on S-MAP-23..40 fleet picks up: per-country drill-in, choropleth shading, 3D globe (post-launch).

---

## Open questions for Vader

1. **Where does `/breaking-map` live in nav?** Top-of-page header tab next to `/breaking` and `/dossiers`? Or a hero-rail-replacement on home?
2. **Mobile-only or also desktop?** Vader's voice said "especially on mobile device" — does desktop get a smaller embedded version, or the same full-page experience?
3. **Bias distribution per continent — show by default or only on hover?** Hover-only is cleaner but invisible on mobile (no hover).
4. **Country-level Phase 2 — green-light now or wait for v1 data?** Phase 2 is a big build; might want to ship v1, measure engagement, then commit.
