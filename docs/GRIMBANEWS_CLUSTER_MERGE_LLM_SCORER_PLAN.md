# GrimbaNews — Cluster Merge LLM Scorer Plan

**Status:** plan v0 (current `findOrFormCluster()` is canonical-URL + title-similarity)
**Owner:** Rajesh Kumar (backend) + Lisa Nguyen (data)
**Walks:** Mythos S1051 + S1052 (cluster-merge LLM scorer + cluster-split LLM scorer) deferred → partial
**Gating dependency:** NobuAI budget + vector store (per `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md` Wave LLL).

## v1 (current) — `findOrFormCluster()`

Per `app/Services/GrimbaClusterer.php` (or similar — wires the cluster assignment on ingest):

1. Canonical-URL match: if the URL matches an existing cluster's canonical, merge.
2. Title-similarity (Levenshtein normalized): if > 0.85 against any cluster head, merge.
3. Else: form new cluster.

Coverage estimate: 90% of cases handled cleanly. Edge cases:
- Translated headlines (FR original + EN coverage from different source).
- Reworded same-story from same outlet (e.g. evening update of morning story).
- Aggregator stories that splice multiple narratives.

## v2 — LLM judge for borderline merges

When v1 similarity score lands in [0.65, 0.85] (gray zone), invoke NobuAI with the prompt:

```
Are these two article headlines covering the SAME news story?
Answer ONLY "yes" or "no".

A: <title A> [from <source A>, <pubdate A>]
B: <title B> [from <source B>, <pubdate B>]
```

If "yes": merge. If "no": form separate clusters.

Vector-store enhancement (lands with `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md`):

1. Per-article embedding computed at ingest.
2. Cosine-similarity against cluster-centroid embedding.
3. Combined score = 0.4 × title-similarity + 0.6 × embedding-similarity.
4. Threshold > 0.78 = merge.
5. Gray zone [0.65, 0.78] = LLM judge fallback.

## Cluster-split scorer (S1052)

Mirror but inverse: if a cluster grows past 50 articles AND article-pair similarity within the cluster shows a bimodal distribution, NobuAI judges whether to split.

```
Cluster <id> currently contains <N> articles spanning these subtopics:
- Subtopic A (sample headlines): ...
- Subtopic B (sample headlines): ...

Should this cluster be split into two? Answer "yes" or "no" and if yes,
list the headlines that belong to each subgroup.
```

## Wiring

- `GrimbaClusterMerger::scoreV2($candidateA, $candidateB)` — returns merge/no-merge decision with score.
- Per-decision logged to `cluster_merge_decisions` (new table — gates on Vader migration approval).
- Editor reviews disagreements weekly.

## Cost

~3% of posts hit the gray zone × ~200 posts/day = ~6 LLM calls/day. Plus weekly cluster-split sweep at ~2-3 calls/week. Well within budget.

## Cross-references

Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1051, S1052).
Sister: `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md`, `docs/GRIMBANEWS_SEARCH_V2_LAUNCH_PLAYBOOK.md`.
Code: cluster assignment in `app/Services/GrimbaClusterer.php`.
