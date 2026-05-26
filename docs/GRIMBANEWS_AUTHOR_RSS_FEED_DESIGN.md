# GrimbaNews — Author RSS Feed Design

**Status:** plan v0 (no per-author feed route)
**Owner:** Rajesh Kumar (Backend) implements route + Michael O'Connor on docs + Nina Patel on link discoverability
**Walks:** Mythos S1417 (Author RSS feed) deferred → partial
**Gating dependency:** `journalists` table (S1411) + author profile page (S1412)

## Why this exists

S1417 gives readers + tools a programmatic feed of one journalist's bylines. Aligns with the existing per-stream RSS pattern (`/feed.xml`, `/feed.breaking.xml`, `/feed/categorie/{slug}.xml`, `/feed/middle-ground.xml`) which already serves the public-feed substrate.

## Today's surrogate

- **`/feed.xml`** + per-category feeds — broad but not per-author.
- **Per-source filter** in search — but no source-specific RSS today.

## Routes

```
GET /feed/auteur/{slug}.xml    (FR)
GET /feed/author/{slug}.xml    (EN)
GET /feed/auteur/{slug}.atom   (Atom 1.0)
```

## Feed shape

Standard RSS 2.0 + Atom 1.0 (parallel files like other GrimbaNews per-stream feeds).

```xml
<rss version="2.0">
  <channel>
    <title>Jane Doe — bylines from GrimbaNews</title>
    <link>https://grimbanews.com/journaliste/jane-doe</link>
    <description>Latest stories by Jane Doe (climate, politics)</description>
    <language>fr-FR</language>
    <lastBuildDate>...</lastBuildDate>
    <atom:link href="https://grimbanews.com/feed/auteur/jane-doe.xml" rel="self"/>
    <item>
      <title>Climate summit opens...</title>
      <link>https://grimbanews.com/dossier/12345</link>
      <pubDate>...</pubDate>
      <author>Jane Doe</author>
      <category>climate</category>
      <description><![CDATA[lede...]]></description>
      <guid isPermaLink="false">post-12345</guid>
    </item>
    ...
  </channel>
</rss>
```

## Performance

- Cached 5 minutes (same TTL as other per-stream feeds).
- Per-author query already indexed (per `journalists.slug` + `post_journalists`).
- Pre-filter: only verified-active journalists; 404 for unverified or inactive.

## Discoverability

- `<link rel="alternate" type="application/rss+xml" href=".../feed/auteur/{slug}.xml">` on author profile page.
- "Subscribe via RSS" CTA on profile page (mirrors existing "subscribe to feed" patterns).
- Feed URL exposed at `/api/v2/journalists/{slug}` response under `feed_url`.

## Item count

- 20 most recent posts (matches RSS 2.0 convention).
- Older posts via web-side pagination (no feed pagination).

## Test surface

- Per-author feed validation:
  - Smoke test asserts well-formed XML.
  - Smoke test asserts atom + rss return.
  - 404 for unknown slug.
  - 404 for inactive journalist.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1417)
- Sister docs: `docs/GRIMBANEWS_AUTHOR_TABLE_SCHEMA.md`, `docs/GRIMBANEWS_AUTHOR_PROFILE_PAGE_SCOPE.md`, `docs/GRIMBANEWS_AUTHOR_FOLLOW_DESIGN.md`
- Existing parallel feeds: `/feed.xml`, `/feed.breaking.xml`, `/feed/categorie/{slug}.xml`, `/feed/middle-ground.xml`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
