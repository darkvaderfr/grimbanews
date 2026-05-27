# GrimbaNews — Embed Click-Through Tracking

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Rajesh Kumar (backend)
**Walks:** Mythos S1657 (embed click-through tracking) deferred → partial
**Gating dependency:** Wave SUB-22 embed_impressions table + embed analytics live.

## Why this exists

Per-embed view-rate matters but click-through-rate (CTR) matters more. CTR = embed_clicks / embed_impressions. CTR informs:
- Per-cluster embed UX optimization
- Per-publisher embed-placement quality
- Per-cluster embed-format A/B tests

## v1 tracking

- Embed "Voir comparaison complète" link wrapped with click-track endpoint:
  - `/embed/click?token={t}&cluster={id}&dest=/comparatif/{id}`
- Server records + 302-redirects to destination.
- Per-click logged in `embed_clicks` (per Wave SUB-22).

## CTR dashboard

`/admin/grimba/embed-ctr`:
- Per-cluster CTR (last 30d).
- Per-publisher CTR.
- Per-cluster format A/B comparison.
- Top-10 most-clicked embeds.

## Privacy

- Click endpoint uses session-hash, no per-reader tracking.
- Per-publisher monthly export aggregate.

## Cross-references

Master plan: S1657. Sister: `docs/GRIMBANEWS_EMBED_ANALYTICS_PLAN.md`, `docs/GRIMBANEWS_EMBED_JS_SNIPPET_GENERATOR.md`.
