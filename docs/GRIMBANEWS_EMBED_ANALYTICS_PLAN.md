# GrimbaNews — Embed Analytics Plan (Per-Embed Impressions)

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Rajesh Kumar (backend)
**Walks:** Mythos S1656 (embed widget analytics — per-embed impressions) deferred → partial
**Gating dependency:** `embed_impressions` table; embed.js bundle live.

## Why this exists

Per-publisher embed analytics let partner pages see their embed engagement. Free-tier embeds get aggregate analytics; pro-tier gets per-embed-instance detail.

## Schema (gates on Vader migration approval)

```
embed_impressions:
  id | token_id (nullable) | cluster_id | referrer_domain
   | viewed_at | session_hash (privacy-hashed)
embed_clicks:
  id | token_id (nullable) | cluster_id | clicked_destination
   | clicked_at | session_hash
```

## Per-embed view counting

embed.js sends beacon on load:
- POST `/api/embed/beacon` with token + cluster + referrer.
- Throttled per-session-hash to prevent inflation.
- Per-domain aggregation respects privacy.

## Aggregation cadence

- Per-embed daily roll-up.
- Per-publisher monthly export (Wave SUB-21 dashboard).
- Per-cluster total-embed-impressions surfaced on /admin/grimba/cluster/{id}.

## Privacy posture

- Session hashed; no per-reader cross-page tracking.
- Per-domain aggregation; no per-IP retention.
- DSAR exclusion: aggregate-only data.

## Cross-references

Master plan: S1656. Sister: `docs/GRIMBANEWS_EMBED_TOKEN_API_RATE_LIMIT.md`.
