# GrimbaNews — Search-As-You-Type Plan

**Status:** plan v0
**Owner:** Nina Patel (Lead FE) + Lisa Nguyen (data) + Rajesh Kumar (backend)
**Walks:** Mythos S1599 (search-as-you-type infrastructure) deferred → partial
**Gating dependency:** Search v2 (per `docs/GRIMBANEWS_SEARCH_V2_LAUNCH_PLAYBOOK.md` Wave LLL).

## Why this exists

Static `/search` requires submit + page-reload. Modern reader expectation is autocomplete dropdown: type "spac" → see "SpaceX", "espace", "spatiale" suggestions live as you type.

## v1 design

`/api/search/suggest?q={partial}` endpoint:

- Triggered on every keystroke (debounce 200ms).
- Returns top-10 matches across:
  - Cluster topics (e.g. "Réforme des retraites")
  - Article titles (most recent + most clicked)
  - Categories (per Wave UUUU v2 taxonomy)
  - Authors (per Wave DDDD byline pack)
- Renders inline dropdown below search input.

## Backend

- Prefix-trie over recent N topic + title + category + author strings.
- Updated daily via `grimba:rebuild-search-trie` cron.
- Cache 24h browser; 1h CDN.

## UX

- Below-input dropdown shows max 10 results.
- Per-result type icon (cluster / article / category / author).
- Arrow-key navigation + Enter to select.
- Mobile-first: full-screen overlay on small viewports.

## Cost

- Trie rebuild: < 1 minute cron.
- Per-suggest call: < 5ms (in-memory lookup).
- Negligible NobuAI use (no LLM in hot path).

## Cross-references

Master plan: S1599. Sister: `docs/GRIMBANEWS_SEARCH_V2_LAUNCH_PLAYBOOK.md`, `docs/GRIMBANEWS_AB_PERSONALIZATION_FLEET_DESIGN.md`.
