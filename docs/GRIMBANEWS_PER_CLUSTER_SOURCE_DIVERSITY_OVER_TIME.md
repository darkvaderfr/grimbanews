# GrimbaNews — Per-Cluster Source Diversity Over Time

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Lucy Leai (Strategy)
**Walks:** Mythos S1681 (per-cluster source-diversity score over time) deferred → partial
**Gating dependency:** Time-series of cluster article additions (already in posts.created_at).

## Why this exists

A cluster's source diversity evolves: starts one-sided, broadens as more outlets cover. Plotting this trajectory shows reader the "coverage maturity" of the story.

## v1 design

Per-cluster, compute diversity-score for each day (or each hour for fast-moving):

`diversity = (1 - Σ(bias_share²))` (1 - Herfindahl)
- 0.67 = perfectly diverse (33/33/33)
- 0.5 = bimodal (50/50)
- 0.0 = monopoly (100/0/0)

Plot as line chart over cluster age.

## UX

On `/comparatif/{id}/diversity`:
- Diversity score time-series chart
- Overlay marks: when cluster crossed thresholds (0.4 = mostly-balanced, 0.6 = highly-balanced, mg_ tag application)
- Per-day source-add log

## Cross-references

Master plan: S1681. Sister: `docs/GRIMBANEWS_PER_CLUSTER_NARRATIVE_TIMELINE.md`, `docs/GRIMBANEWS_PER_CLUSTER_FACT_GRAPH_CROSS_LINK_PLAN.md`.
