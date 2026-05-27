# GrimbaNews — Per-Cluster Mastodon + Bluesky Auto-Post

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Liam Smith (PM)
**Walks:** Mythos S1642 + S1643 (Mastodon + Bluesky auto-post) deferred → partial
**Gating dependency:** Mastodon + Bluesky API accounts + per-cluster post template.

## Why this exists

Fediverse readers (Mastodon) + Bluesky are growing journalism-adjacent audiences. Multi-platform syndication reaches readers who avoid X.

## v1 design

Per top-3 dossiers daily, post adapted to each platform's character limit + cultural norm:

- **Mastodon:** 500 chars allowed; multi-paragraph compact format. Hashtag #presse + #news.
- **Bluesky:** 300 chars + 4 images allowed; concise + bias-bar visualization image. Hashtag #news + #journalisme.

## Post template (per platform)

```
[Cluster headline]

L: {n} | C: {n} | R: {n} sources

[MG/BS signal badge if applicable]

→ Comparer les angles : grimbanews.com/comparatif/{id}
```

## Editorial review

- Same 30-min window as LinkedIn (Wave AALL).
- Per-platform editorial approval.

## Cross-references

Master plan: S1642, S1643. Sister: per-platform sister docs.
