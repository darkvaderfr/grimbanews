# GrimbaNews — ML Feed: Embedding-Based Recommendations

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Rajesh Kumar (backend)
**Walks:** Mythos S1503 (ML feed — embedding-based recs) deferred → partial
**Gating dependency:** Embedding store (S1076, partial via `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md` Wave LLL).

## Why this exists

Current `/pour-vous` recommendations are rule-based: followed categories + recent reads. An embedding-based layer adds semantic similarity: "you read X about climate negotiations; here are 5 similar stories about climate policy in other regions."

## v1 design

1. Per-article embedding computed at ingest (via embedding store).
2. Per-reader: average their last 20 reads' embeddings → "reader-profile vector".
3. For new articles, compute cosine vs reader-profile → score 0.0-1.0.
4. Blend with existing rule-based score (60% embedding, 40% rule).
5. Show top-N in `/pour-vous`.

## Cold start

For readers with < 5 reads, fall back to rule-based default (already shipped).

## Schema (additions to existing `pour-vous` infra)

```
member_profile_vectors:
  member_id PK | vector (BLOB) | last_updated_at
```

## Privacy guardrails

- Reader-profile vector recomputed nightly (no real-time tracking).
- Anonymized — vector is derivable but not reversible to reading list (one-way aggregation).
- Reader can wipe via `/account/preferences` → "réinitialiser mes recommandations".
- DSAR includes the vector + reading list (Wave KKKK).

## Fairness audit (gates on S1507)

Quarterly:
- Per-reader diversity score (don't recommend only one bias camp).
- Per-cluster fairness (don't favor large-source clusters over small).
- Lucy + Sara Chen review.

## Cross-references

Master plan: S1503. Sister: `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md`, `docs/GRIMBANEWS_ML_FEED_FAIRNESS_AUDIT_PLAN.md`, `docs/GRIMBANEWS_AB_PERSONALIZATION_FLEET_DESIGN.md`.
