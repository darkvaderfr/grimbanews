# GrimbaNews — Per-Cluster Narrative Phase Plan

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Lucy Leai (Strategy)
**Walks:** Mythos S1682 (per-cluster narrative arc visualization — phase: breaking → developing → settled) deferred → partial
**Gating dependency:** Per-cluster timeline data (Wave AAEE).

## Why this exists

Story unfolds in phases: 1) breaking (first hours), 2) developing (clarifying + filling gaps), 3) settled (consensus emerging), 4) archive. Reader benefits from knowing which phase a story is in.

## Phase rules

- **Breaking:** 0-6h since first article in cluster; high source-add velocity.
- **Developing:** 6h-72h; source additions slowing; details emerging.
- **Settled:** 72h+; minimal new sources; consensus established.
- **Archive:** 30d+ since last source addition.

## UX

Cluster header surfaces phase badge:
- Breaking: red pulsing dot
- Developing: amber
- Settled: green
- Archive: gray

Per-phase, reader gets different framing:
- Breaking: "Couverture en cours — informations préliminaires"
- Developing: "Récit en évolution"
- Settled: "Le récit s'est stabilisé"
- Archive: per Wave AAEE archive badge

## Auto-transition

`grimba:age-cluster-phases` cron walks clusters daily, transitions per rules.

## Cross-references

Master plan: S1682. Sister: `docs/GRIMBANEWS_PER_CLUSTER_NARRATIVE_TIMELINE.md`, `docs/GRIMBANEWS_PER_CLUSTER_DECAY_ARCHIVE_POLICY.md`.
