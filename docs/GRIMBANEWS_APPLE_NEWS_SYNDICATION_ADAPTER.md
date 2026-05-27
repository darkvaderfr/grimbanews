# GrimbaNews — Apple News Syndication Adapter

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Liam Smith (PM) + Lisa Nguyen (data)
**Walks:** Mythos S1596 (Apple News syndication adapter) deferred → partial
**Gating dependency:** Apple News Publisher account + Apple News Format (ANF) transformer pipeline.

## Why this exists

Apple News drives meaningful traffic for European + US publishers. ANF format adapter lets GrimbaNews syndicate content there with full attribution + per-article cluster context.

## v1 design

Per-published-cluster, generate ANF JSON-Document:

1. Cluster-level wrapper: headline + summary + L/C/R bar viz.
2. Per-article inline: title + source + first 200 chars + link.
3. Middle Ground / Blindspot signal badge.
4. Cross-link to GrimbaNews `/comparatif/{id}` for full coverage.

## Push cadence

- Per-cluster: pushed when cluster reaches ≥ 5 sources.
- Per-update: re-pushed when cluster gains 2+ sources or 24h elapses.
- Per-deletion: pushed when cluster archived (Wave AAEE).

## Schema (gates on Vader migration approval)

```
apple_news_publications:
  cluster_id | apple_news_article_id | first_pushed_at | last_updated_at | status (active|archived)
```

## Apple News Publisher requirements

- Publisher account approval (manual review process; days to weeks)
- ANF schema validation (tight specs on inline image dimensions etc.)
- Per-channel content guidelines compliance

## Editorial benefits

- Apple News audience drives ~+15% traffic per published cluster (industry avg).
- Per-article attribution preserves source-credit chain.
- Apple News revenue share (if monetized) per `docs/GRIMBANEWS_PARTNERSHIP_PROGRAM_LAUNCH_PLAYBOOK.md` (Wave LLL).

## Cross-references

Master plan: S1596. Sister: `docs/GRIMBANEWS_GOOGLE_NEWS_SYNDICATION_ADAPTER.md` (companion), `docs/GRIMBANEWS_PARTNERSHIP_PROGRAM_LAUNCH_PLAYBOOK.md`.
