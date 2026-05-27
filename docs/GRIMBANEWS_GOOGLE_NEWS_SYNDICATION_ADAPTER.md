# GrimbaNews — Google News Syndication Adapter

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Liam Smith (PM) + Lisa Nguyen (data)
**Walks:** Mythos S1597 (Google News syndication adapter) deferred → partial
**Gating dependency:** Google News Publisher Center account + Publisher Center sitemap.

## Why this exists

Google News indexing is critical for editorial publisher reach. Standard sitemap already submits content (Wave QQQ); Publisher Center adds richer per-cluster context.

## v1 design

`/news-sitemap.xml` route (separate from /sitemap-grimba.xml):
- Per-cluster as one news item
- per Google News sitemap spec: `<news:news>` with title, publication, publication_date, keywords
- Last 48h of clusters (Google's news-sitemap window)

```xml
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
  <url>
    <loc>https://grimbanews.com/comparatif/1234</loc>
    <news:news>
      <news:publication>
        <news:name>GrimbaNews</news:name>
        <news:language>fr</news:language>
      </news:publication>
      <news:publication_date>2026-05-27T08:00:00Z</news:publication_date>
      <news:title>Story headline</news:title>
      <news:keywords>politics, election, ...</news:keywords>
    </news:news>
  </url>
</urlset>
```

## Per-cluster Article schema.org

Already shipped per Wave AAAA enhancement on /juste-milieu. Extend pattern to /comparatif/{id} with Article + NewsArticle schema.

## Publisher Center setup

- Submit /news-sitemap.xml URL.
- Designate language: French + secondary EN.
- Editorial categories mapping (GrimbaNews v2 taxonomy → Google News topics).
- Per-locale Publisher Center entry (FR + EN + DE etc. as launched).

## Cross-references

Master plan: S1597. Sister: `docs/GRIMBANEWS_APPLE_NEWS_SYNDICATION_ADAPTER.md`, sitemap pipeline (Wave AAAA).
