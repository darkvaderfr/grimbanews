# GrimbaNews — Cross-Cluster Narrative Graph Plan

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Steve Jobs (CPO)
**Walks:** Mythos S1471 (cross-cluster narrative linking) deferred → partial
**Gating dependency:** Embedding store (`docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md` Wave LLL partial).

## Why this exists

Big stories aren't single dossiers — they're constellations of related dossiers. Example: an election cycle has dossiers for primary results, debates, polls, controversies, election-day reporting, post-mortems. Today these live as siblings; readers manually traverse. A narrative graph surfaces "here are 7 related dossiers that together tell the bigger story."

## v1 design

For each cluster, compute related-cluster recommendations via:

1. **Topic-overlap** — % of NER entities shared between two clusters.
2. **Embedding cosine** — vector-space similarity of cluster centroids (gates on embedding store).
3. **Temporal proximity** — bonus for clusters within ±7 days.
4. **Editorial graph** — operator-curated "narrative chain" tags (manual override).

Score cap at 0.65; top-5 surfaced.

## UX

On `/comparatif/{id}`, new "Dossiers liés à cette histoire" rail (right sidebar below share-kit):
- Per-related cluster: topic, date, source count, MG/BS badge if applicable.
- Click → opens related cluster.

## Schema (gates on Vader migration approval)

```
cluster_relations:
  source_cluster_id | related_cluster_id | score (float) | reason (topic|embed|temporal|editorial)
   | last_computed_at
```

## Operator-curated narrative chains

For high-importance narratives (election cycles, conflicts, major policy debates), operator manually defines a chain:

```
narrative_chains:
  id | name | description | created_by | created_at
cluster_chain_links:
  chain_id | cluster_id | sequence_order | annotation
```

Reader sees: "Cette histoire fait partie de la chronologie [chain name] (8 dossiers)."

## Cross-references

Master plan: S1471. Sister: `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md`, `docs/GRIMBANEWS_PER_CLUSTER_NARRATIVE_TIMELINE.md` (companion).
