# GrimbaNews — Per-Region Partner Content-Share Statistics

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Lisa Nguyen (data)
**Walks:** Mythos S1683 (per-region partner content-share statistics) deferred → partial
**Gating dependency:** Per-partner stream filter + 30+ days of share data.

## Metrics tracked

- **Per-share platform:** which platform (X, LinkedIn, Mastodon, Bluesky, email, etc.) drove most partner-content shares.
- **Per-share-time:** when readers share partner content (correlation with publishing time).
- **Per-cluster partner contribution to share-velocity:** partner content with high share-rate.
- **Per-partner share-CTR-back:** clicks from share posts back to /partenaire/{slug}.

## Dashboard

`/admin/grimba/partners/{id}/shares` quarterly review:
- Top shared partner-tagged clusters
- Per-platform breakdown chart
- Per-time-of-day heatmap
- Share-to-attribution-click conversion rate

## Per-partner monthly export

PDF + CSV per partner contact with the above metrics.

## Cross-references

Master plan: S1683. Sister: `docs/GRIMBANEWS_PER_REGION_PARTNER_ATTRIBUTION_METRICS.md`, Wave AALL syndication docs.
