# GrimbaNews — Partner Content-Share API Design

**Status:** plan v0
**Owner:** Rajesh Kumar (Backend) + Victor Garcia (BD) + Liam Smith (PM)
**Walks:** Mythos S1442 (partner content-share API) deferred → partial
**Gating dependency:** outbound auth (S1228 already passed) + partner-scoped API key + delivery model decision (push webhook vs pull RSS).

## Why this exists

Partners (republishing partners, syndication partners, academic researchers) need a programmatic way to receive Grimba content with attribution. Today the only egress surfaces are read-only RSS feeds (`/feed.xml`, `/feed.breaking.xml`, `/feed.latest.xml`, per-category feeds) — fine for casual consumption, insufficient for high-volume partners.

## v1 design

Two-mode API:

1. **Pull (REST)** — `/api/v2/partner/feed?since=ISO8601&topics=climate,politics&max=100`
   - Returns canonical post JSON with attribution block.
   - Per-partner rate limit (default 600 req/hour).
2. **Push (Webhook)** — leverages S1238 webhook delivery infra; partner subscribes to `article.published` / `article.corrected` events filtered by topic.

## Attribution block (mandatory)

Every payload includes:

```json
{
  "attribution": {
    "publisher": "GrimbaNews",
    "publisher_url": "https://grimbanews.com",
    "original_url": "https://grimbanews.com/article/{slug}",
    "license": "CC-BY-SA-4.0 OR partner-agreement:{id}",
    "author": "...",
    "published_at": "..."
  }
}
```

Partner contract requires attribution back-link + license-line preservation.

## Anti-patterns

- No bulk-export endpoint (forces partners into pagination — keeps abuse low).
- No partner can self-revoke licensing terms mid-stream (changes go through Victor + counsel).

## Cross-references

Master plan: S1442. Sister: S1238 (webhook), S1444 (attribution report), S1446 (takedown), S1447 (royalty split), `/feed.xml` (surrogate today).
