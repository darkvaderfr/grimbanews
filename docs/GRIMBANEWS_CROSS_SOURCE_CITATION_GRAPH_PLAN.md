# GrimbaNews — Cross-Source Citation Graph Plan

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Steve Jobs (CPO)
**Walks:** Mythos S1622 (cross-source citation graph) deferred → partial
**Gating dependency:** Article-text parser for hyperlink + name-cite extraction.

## Why this exists

Newsrooms cite each other: Le Monde cites Reuters, Reuters cites AP, etc. A cross-source citation graph maps these flows — showing reader who originates stories vs who picks them up, and revealing source-dependency patterns.

## v1 design

Per-article extraction pipeline:

1. Parse article body for hyperlinks to other sources in our `news_sources` table.
2. Extract per-link target source.
3. Per-article: store list of cited sources.
4. Aggregate: per-source-pair citation count.

## Schema (gates on Vader migration approval)

```
source_citations:
  source_a_id | source_b_id | citation_count | first_seen_at | last_seen_at
```

## Reader-facing visualization

On `/transparence/citation-graph` admin-only (operator-side) initially, then public after editorial review:

- Force-directed graph
- Nodes = sources, sized by article count
- Edges = citations, weighted by count
- Node colors = bias rating
- Highlight loops (mutual-citation pairs)

## Editorial value

- Surface "echo chambers" (sources citing only own-camp peers).
- Surface "bridge sources" (cited across bias spectrum).
- Per-source dependence metric (% of articles citing same upstream source).

## Cross-references

Master plan: S1622. Sister: `docs/GRIMBANEWS_CROSS_CLUSTER_NARRATIVE_GRAPH_PLAN.md`, `docs/GRIMBANEWS_PER_SOURCE_SLA_DASHBOARD_PLAN.md`.
