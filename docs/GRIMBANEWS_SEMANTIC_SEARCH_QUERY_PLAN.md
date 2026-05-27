# GrimbaNews — Semantic Search Query Surrogate Plan

**Sprint ID:** S1335
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — Semantic-search query`
**Walk wave:** CCCC

## Gating dependency

Semantic search needs:

- Per-article embeddings (S1333)
- Per-query embedding at request time (latency budget: ~200ms for embedding call + ~50ms for vector ANN)
- Hybrid scoring (semantic + keyword + freshness + bias-diversity)
- UX: confidence indicator + "matched on X" explainer

## Surrogate-now infra

- **`GrimbaSavedSearches::matchingPosts()`** — keyword search today; would add semantic as additional WHERE
- **`tests/Feature/SearchFacetsTest`** — locks current facet contract; semantic results would extend
- **`/search?q=` UI** — full UX surface ready to layer semantic atop keyword

## Honest framing

Today's keyword search (with bias/locale facets) is good enough for the article corpus size (~100k articles). Semantic search is a v2-tier feature where the per-query embedding cost ($0.0001 × 1M searches = $100/month) is offset by improved long-tail query satisfaction.

## Owners

- **Data:** David Chen — hybrid scoring formula
- **Backend:** Rajesh Kumar — query path + cache layer
- **Product:** Liam Smith — UX confidence indicator
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1335 row)
- Embedding store: `docs/GRIMBANEWS_EMBEDDING_STORE_WIRING_PLAN.md`
- Saved searches: `app/Support/GrimbaSavedSearches.php`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
