# GrimbaNews — Per-Cluster Correction History Tracking

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Lisa Nguyen (data)
**Walks:** Mythos S1666 (per-cluster correction-history tracking) deferred → partial
**Gating dependency:** Corrections schema (Wave KKKK).

## Why this exists

Per-cluster history of corrections shows reader the dossier's reliability arc. If multiple sources have been corrected on a story, reader gets context for skeptical reading.

## v1 design

`/comparatif/{id}/corrections` deep-link surfaces:
- Per-cluster correction timeline (when, what, by which source)
- Per-source correction badge (badge color: green=none, yellow=1-2, red=3+)
- Correction-rate visualization

## Schema (extends existing corrections table)

```
cluster_corrections (view):
  cluster_id | post_id | source_id | corrected_at | corrected_fact | severity
```

## Per-cluster reliability heuristic

Cluster reliability score 0-100 = 100 - (correction_count × severity_weight). Surfaces on cluster detail page (premium only or all readers TBD).

## Cross-references

Master plan: S1666. Sister: `docs/GRIMBANEWS_PER_AUTHOR_CORRECTION_TRACKING_PLAN.md`, corrections-badge from Wave DDDD.
