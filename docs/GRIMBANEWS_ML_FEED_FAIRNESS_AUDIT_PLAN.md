# GrimbaNews — ML Feed Fairness Audit Plan

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Lucy Leai (Strategy) + Lisa Nguyen (data)
**Walks:** Mythos S1507 (ML feed — fairness audit) deferred → partial
**Gating dependency:** ML model live (S1503 partial above) + 30 days of recommendation logs.

## Why this exists

Per `feedback_steve_design_language.md` and editorial values: GrimbaNews must not produce filter-bubble effects. ML-feed recs without audit can over-fit a reader's existing biases. Audit catches this and forces diversity.

## Audit metrics

Per reader (rolling 30d):

1. **Per-bias-distribution** — actual reads vs corpus baseline. Acceptable drift ±10%.
2. **Per-topic distribution** — reader sees their followed topics + at least 20% outside.
3. **Per-region distribution** — reader sees their region + at least 15% other regions.
4. **Per-source distribution** — no single source > 25% of reads.
5. **Per-cluster-size distribution** — read both large (≥ 5 source) and small (≤ 3 source) clusters.

## Per-cluster recommendation fairness

- Don't favor large-source clusters in recs unless reader explicitly asks ("show me the biggest stories").
- Don't favor recent clusters at expense of high-quality stale clusters.
- Don't favor French clusters over per-region content for non-FR readers.

## Quarterly audit cadence

- Lisa Nguyen exports anonymized aggregate metrics (no per-reader data).
- Sara Chen + Lucy review.
- If drift > acceptable, retune model weights.

## Schema

No new schema — uses existing recommendation logs + reading history (Wave KKKK).

## Cross-references

Master plan: S1507. Sister: `docs/GRIMBANEWS_ML_FEED_EMBEDDING_BASED_RECS_PLAN.md`, `docs/GRIMBANEWS_TRANSPARENCY_REPORT_SCOPE.md` (Wave LLL).
