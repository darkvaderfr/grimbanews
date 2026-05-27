# GrimbaNews — Per-Cluster Fact Graph + Cross-Link Plan

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Steve Jobs (CPO)
**Walks:** Mythos S1614 (per-cluster fact-graph cross-link) deferred → partial
**Gating dependency:** Cluster-claim extraction (per `docs/GRIMBANEWS_CLUSTER_QUOTE_EXTRACTION_PLAN.md` Wave UUUU).

## Why this exists

Different dossiers reference the same underlying facts (e.g. "GDP growth rate Q3 2026" appears in 5+ economic-news dossiers). A fact-graph lets readers traverse: "see this same fact cited in other dossiers."

## v1 design

`cluster_claims` from quote-extraction pipeline (Wave UUUU) already contains per-claim text. Add cross-cluster dedup:

1. Embedding-similarity over claim text (gates on embedding store Wave LLL).
2. Threshold 0.85 → group as same-fact.
3. Per-fact, store list of cluster_id + post_id citations.

## Reader UX

On cluster page, per-claim card carries a "Cité dans 4 autres dossiers" link → opens fact-detail panel:
- Fact summary
- All clusters citing it (with date + source-count badge)
- Per-citation: which source said what + how it was framed
- Per-fact disagreement: if sources differ on the fact, highlight

## Schema (gates on Vader migration approval)

```
cluster_facts:
  id | fact_text | embedding | first_seen_at | citation_count
fact_citations:
  fact_id | cluster_claim_id | post_id | created_at
```

## Editorial value

Surfacing fact-graph turns reader-experience from "read article" → "track fact across reporting" — a uniquely-GrimbaNews experience.

## Cross-references

Master plan: S1614. Sister: `docs/GRIMBANEWS_CLUSTER_QUOTE_EXTRACTION_PLAN.md`, `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md`, `docs/GRIMBANEWS_CROSS_CLUSTER_NARRATIVE_GRAPH_PLAN.md`.
