# GrimbaNews — Per-Cluster LinkedIn Newsletter

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Liam Smith (PM)
**Walks:** Mythos S1640 (per-cluster LinkedIn newsletter) deferred → partial
**Gating dependency:** LinkedIn API for organization page + per-newsletter cadence approval.

## Why this exists

LinkedIn is the dominant B2B distribution channel for editorial content. Professional readers expect daily/weekly editorial digests there. GrimbaNews "Le tour des perspectives" newsletter format adapted for LinkedIn.

## v1 design

Daily 09:00 UTC: auto-post one cluster summary to GrimbaNews LinkedIn page:

- Cluster headline
- 2-paragraph summary (NobuAI generated, editor reviewed)
- L/C/R coverage badge
- MG/BS signal badge if applicable
- Link to /comparatif/{id}
- Hashtag: #GrimbaNews + #regionalhashtag

## Editorial review

- Lucy reviews next-day post end-of-day prior.
- 30-min window 08:30-09:00 UTC to revise/reject.
- Per-post analytics tracked: impressions, engagements, click-throughs.

## Cross-platform sister docs

- Wave AALL: per-cluster X/Twitter thread
- Wave AAMM: per-cluster Mastodon + Bluesky post
- Wave AANN: per-cluster Threads post

## Cross-references

Master plan: S1640. Sister: cross-platform syndication docs above.
