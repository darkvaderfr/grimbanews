# GrimbaNews — Per-Cluster Narrative Timeline Plan

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Nina Patel (Lead FE) + Lucy Leai (Strategy)
**Walks:** Mythos S1472 (per-cluster narrative timeline) deferred → partial
**Gating dependency:** Per-cluster article timestamps already in DB; visualization layer needed.

## Why this exists

A dossier with 12 sources covering a story over 5 days currently shows them in flat reverse-chronological order. A timeline visualization tells the unfolding-narrative story better: "Tuesday 9am Le Monde broke it, Tuesday noon AFP confirmed, Wednesday Le Figaro added critique, Thursday cluster widened with English coverage..."

## v1 design

On `/comparatif/{id}`, new "Chronologie" tab next to "Toutes les sources":

- Horizontal time axis (left = earliest, right = latest)
- Per-article dot: colored by bias (L blue / C green / R red / unknown gray)
- Hover: shows source + headline + time
- Click → opens article
- Bias-distribution-over-time band below timeline (cumulative L/C/R at each hour)

## Visualization

- D3.js or Chart.js timeline component
- Per-cluster scope: shortest interval = 1 hour, longest = 30 days
- Auto-zoom to most-active 80% of activity

## UX touchpoints

- "Comparer côte à côte" tab (current dossier view) stays default
- "Chronologie" tab as secondary view
- Per-article mini-cards on hover stay above timeline
- Mobile: vertical timeline (top → bottom)

## Schema

No schema change needed — uses existing `posts.story_cluster_id` + `posts.published_at`.

## Cross-references

Master plan: S1472. Sister: `docs/GRIMBANEWS_CROSS_CLUSTER_NARRATIVE_GRAPH_PLAN.md`, `docs/GRIMBANEWS_PER_CLUSTER_DECAY_ARCHIVE_POLICY.md`.
