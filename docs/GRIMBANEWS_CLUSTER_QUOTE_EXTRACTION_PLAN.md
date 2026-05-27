# GrimbaNews — Cluster Quote Extraction Plan

**Status:** plan v0
**Owner:** Rajesh Kumar (backend) + Lisa Nguyen (data)
**Walks:** Mythos S1055 + S1056 (cluster-quote + fact-claim extraction) deferred → partial
**Gating dependency:** NobuAI extractive-pipeline budget + per-extract validation pipeline.

## Why this exists

For dossier pages showing N sources covering the same story, an extractive overlay of "what claim does each source actually make" would surface where sources agree (factual consensus) vs disagree (interpretive divergence) — distinct from the LCR bias bar which is opinion-direction.

## v1 design

Per-cluster nightly job:

1. For each cluster with ≥ 3 published articles:
2. NobuAI prompt extracts up to 5 fact claims per article in form:
   ```
   {"claim": "<concise sentence>", "is_quotation": true/false, "source_sentence_offset": int}
   ```
3. Cross-article dedup: cluster by claim-embedding cosine similarity (lands with `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md`).
4. Per-claim consensus score: percent of articles in cluster making this claim.
5. Per-claim divergence flag: when 2+ articles make contradictory claims about same fact.

## Schema (new tables, gates on Vader DB migration approval)

```
cluster_claims:
  id | cluster_id | claim_text | consensus_score | divergence_flag | first_seen_at | last_validated_at

cluster_claim_sources:
  id | cluster_claim_id | post_id | is_quotation | source_sentence_offset
```

## UX

On `/comparatif/{id}` dossier page, new "Faits saillants" panel:

- Top 3 high-consensus claims (≥ 80% agreement) — "ce qu'on sait"
- Top 3 divergent claims — "ce qui diffère"
- Click-through: per claim → article + sentence highlight

## Cost

- ~200 articles/day × 5 claims max = 1000 NobuAI calls/day for extraction.
- Plus per-pair dedup (gates on embedding store).
- Estimated < $5/day at current NobuAI pricing.

## Failure modes

- NobuAI hallucinates claims → per-claim validation against source text required.
- Cross-locale claims (FR + EN coverage of same event) — needs translation first.

## Cross-references

Master plan: S1055, S1056. Sister: `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md`, `docs/GRIMBANEWS_CLUSTER_MERGE_LLM_SCORER_PLAN.md`.
Code: `app/Services/GrimbaNobuAi.php`.
