# GrimbaNews — Search A/B Test Harness Surrogate Plan

**Sprint ID:** S1499
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1491-s1500 — Search-A/B test harness`
**Walk wave:** CCCC

## Gating dependency

Search A/B harness needs the same gates as ranking A/B (S1346) — plus:

- Per-search-query variant assignment (less obvious than per-visitor; usually per-query-hash so a refresh isn't a different variant)
- Search-quality signals (query → click-through, query → dwell, query → save-to-collection)
- A "no result" / "low-result" guardrail (a search variant that returns 0 results should auto-promote losing variant out)

## Surrogate-now infra

- **`docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`** — overall A/B design
- **`tests/Feature/SearchFacetsTest`** — search baseline lock
- **`GrimbaVaultEvents`** — partial click-event capture pattern (would extend for search-result clicks)

## Honest framing

Same gate as S1346 — A/B engine doesn't exist. Search-specific concerns (per-query stickiness, zero-result guardrails) are policy decisions, not engineering blockers.

## Owners

- **Data:** David Chen — query-level variant model
- **Product:** Liam Smith — quality signal definitions
- **Backend:** Rajesh Kumar — search-path variant dispatch
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1499 row)
- A/B harness: `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`
- A/B rank harness: `docs/GRIMBANEWS_AB_RANK_HARNESS_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
