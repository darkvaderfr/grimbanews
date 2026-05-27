# GrimbaNews — Internet Archive Wayback Partnership Plan

**Status:** plan v0
**Owner:** Jacob Lee (DevOps) + Lucy Leai (Strategy)
**Walks:** Mythos S2222 (Internet Archive Wayback partnership) deferred → partial
**Gating dependency:** Operator-side SPN submission cadence + per-article URL submission API.

## Why this exists

Internet Archive's Wayback Machine is the de-facto historical web archive. Submitting GrimbaNews URLs ensures snapshots are preserved even if grimbanews.com goes offline.

## v1 design

`grimba:archive-to-wayback` cron daily 04:00 UTC:
1. Iterate posts published in last 24h.
2. POST each canonical URL to `web.archive.org/save/<url>` via API.
3. Rate-limit per Wayback ToS (1 req/sec).
4. Log per-URL Wayback timestamp.

## Per-article archive metadata

```
posts.wayback_first_archived_at TIMESTAMP NULL
posts.wayback_archive_count INT DEFAULT 0
posts.wayback_last_archived_at TIMESTAMP NULL
```

## Recovery scenario

If GrimbaNews goes offline, readers can access archive via:
- `https://web.archive.org/web/*/grimbanews.com/article/{slug}`
- Per-article permalink preserved in Wayback's index.

## SPN (Save Page Now) submission

Free service; rate-limited. Per-article submission costs zero $. Operator-time only.

## Cross-references

Master plan: S2222. Sister: `docs/GRIMBANEWS_MULTI_DECADE_PRESERVATION_SCOPE.md`, S2223 IIPC plan.
