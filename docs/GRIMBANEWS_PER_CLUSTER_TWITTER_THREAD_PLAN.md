# GrimbaNews — Per-Cluster X/Twitter Thread Auto-Generation

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Lisa Nguyen (data)
**Walks:** Mythos S1641 (per-cluster X/Twitter thread auto-generation) deferred → partial
**Gating dependency:** X/Twitter API tier (subject to platform changes) + thread-formatter pipeline.

## Why this exists

Threads remain a top discovery channel for news on X. Per-cluster 5-tweet thread surfaces the editorial signal natively in the platform.

## v1 design

For top-3 dossiers daily:

1. NobuAI generates 5-tweet thread:
   - Tweet 1: headline hook + cluster ID
   - Tweet 2: L/C/R coverage breakdown
   - Tweet 3: Middle Ground / Blindspot signal explanation
   - Tweet 4: top-2 quotes (from cluster-claim extraction, Wave UUUU)
   - Tweet 5: link to /comparatif/{id}
2. Each tweet ≤ 280 chars.
3. Posted via X API.

## Editorial review

- Editor reviews thread draft before post.
- 30-min window 13:30-14:00 UTC (off-peak French time).

## Rate / cost

- 3 threads/day = 15 tweets/day.
- Well within X API free tier (when available).
- If API tier paywalled: defer to manual editorial post.

## Cross-references

Master plan: S1641. Sister: per-platform sister docs (LinkedIn, Mastodon, Bluesky, Threads).
