# GrimbaNews — Partner Exclusivity Window Plan

**Status:** plan v0
**Owner:** Victor Garcia (BD) + Rajesh Kumar (Backend) + Lucy Leai (Strategy)
**Walks:** Mythos S1445 (partner exclusivity window) deferred → partial
**Gating dependency:** operator-side partner contract + `posts.exclusivity_window_until` column + ingest filter that respects window.

## Why this exists

Some partnership tiers (premium syndication) require Grimba to hold content from open distribution for a defined window (24h / 72h) before public availability. The contract is real; the technical enforcement does not yet exist.

## v1 schema

```sql
ALTER TABLE posts ADD COLUMN exclusivity_partner_id BIGINT NULL;
ALTER TABLE posts ADD COLUMN exclusivity_window_until TIMESTAMP NULL;
ALTER TABLE posts ADD INDEX idx_exclusivity (exclusivity_window_until, exclusivity_partner_id);
```

## Behavior

- During exclusivity window:
  - Article visible only to exclusivity-partner via partner API + back-end admin.
  - Hidden from public surfaces (cluster pages, home rails, search, sitemap, feeds).
  - Indexable-only check on `Sitemap` regeneration.
- At window end:
  - Background job flips visibility.
  - Cluster pages re-render.

## Contract requirements (operator side)

- Window duration documented in partnership contract.
- Per-cluster vs per-article exclusivity declared at partnership signing.
- Default fallback if window not set: public immediately.

## Anti-patterns

- No silent extension of windows past contract.
- No retroactive exclusivity (article public → exclusivity is meaningless).

## Cross-references

Master plan: S1445. Sister: S1442 (content-share), S1444 (attribution), S1446 (takedown).
