# GrimbaNews — ML Feed Model A/B Harness Plan

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Liam Smith (PM) + Steve Jobs (CPO)
**Walks:** Mythos S1509 (ML feed — model A/B harness) deferred → partial
**Gating dependency:** A/B harness (S1073 partial via Wave YYYY) + 2+ live recommendation models.

## Why this exists

When iterating recommendation algorithms, need to compare side-by-side: "does the new embedding-blend outperform the previous rule-based on click-through?"

## v1 design

Per-reader cohort assignment (cookie-pinned 30-day):

- Control: existing rule-based recs (current production).
- Variant A: embedding-based (Wave AAFF docs).
- Variant B: blended (rule + embedding 60/40).

## Metrics

Per-cohort (rolling 14d):
- CTR on `/pour-vous` (primary)
- Read-completion rate (secondary)
- Per-reader return-rate to /pour-vous (engagement)
- Per-reader subscription conversion (commercial)
- Diversity score (per Wave AAFF fairness audit) — guardrail

## Decision criteria

Variant promotes to production when:
- Primary metric Δ > +5% with p < 0.05
- No guardrail violation (diversity score within ±5% of control)
- Run > 14 days, ≥ 1000 readers per arm
- Lucy + Steve + Lisa sign off

## Schema (gates on migration)

```
ml_feed_experiments:
  id | slug | status | started_at | ended_at | winning_variant
ml_feed_assignments:
  experiment_id | cohort_id | variant | first_assigned_at
```

## Cross-references

Master plan: S1509. Sister: `docs/GRIMBANEWS_ML_FEED_EMBEDDING_BASED_RECS_PLAN.md`, `docs/GRIMBANEWS_AB_PERSONALIZATION_FLEET_DESIGN.md`, `docs/GRIMBANEWS_ML_FEED_FAIRNESS_AUDIT_PLAN.md`.
