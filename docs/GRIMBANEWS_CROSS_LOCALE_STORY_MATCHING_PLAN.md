# GrimbaNews — Cross-Locale Story Matching

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Rajesh Kumar (backend)
**Walks:** Mythos S1620 (cross-locale story matching) deferred → partial
**Gating dependency:** Embedding store (per Wave LLL vector plan) + translation service.

## Why this exists

Same event covered in FR + EN + DE today lives as 3 separate clusters. Reader on FR misses EN coverage; reader on EN misses FR coverage. Cross-locale matching identifies these and merges into a single multilingual cluster.

## v1 design

1. For each new cluster, compute embedding (gates on store).
2. Compare against embeddings of recent clusters in other locales (rolling 7-day window).
3. If cosine > 0.85: merge as same-event multilingual cluster.
4. If 0.65-0.85: flag for editor review.
5. Cluster carries `locales = ['fr', 'en', 'de']` array.

## UX

On /comparatif/{id} for multilingual cluster:
- Per-locale tab strip shows coverage in each language
- Translation badge on per-article cards
- Reader's preferred locale stays default; other locales accessible via tab

## Cost

- Per-new-cluster: 1 embedding call (already in pipeline).
- Per-pair-check: ~50 cluster comparisons average (cheap).
- ~10 cluster-merge decisions/day.

## Editor review

- Daily 10:00 UTC sweep of flagged 0.65-0.85 cases.
- Editor approves merge or splits to separate clusters.
- Per-merge logged with rationale in `cluster_merge_decisions`.

## Cross-references

Master plan: S1620. Sister: `docs/GRIMBANEWS_CLUSTER_MERGE_LLM_SCORER_PLAN.md`, `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md`.
