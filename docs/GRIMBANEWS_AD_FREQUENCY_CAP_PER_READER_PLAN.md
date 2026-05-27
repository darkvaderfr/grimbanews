# GrimbaNews — Ad Frequency Cap (Per Reader) Plan

**Status:** plan v0
**Owner:** Rajesh Kumar (Backend) + Olivia Davis (Marketing) + Maya Patel (Compliance — consent interaction)
**Walks:** Mythos S1278 (frequency cap per reader) deferred → partial
**Gating dependency:** client-side cap logic + consent layer coordination (AdSense is server-mediated; direct sponsors are config-driven).

## Why this exists

Without a per-reader frequency cap on direct-sponsor placements, the same sponsor card can show 8+ times to a single returning reader in a day — degrading both reader experience and sponsor brand health (banner-blindness).

## v1 design (direct sponsors only)

AdSense placements are governed by Google-side capping; this plan covers direct-sponsor inventory only.

- Cookie `_grimba_ad_cap` (JSON: `{ sponsor_slug: { count, last_at } }`).
- Per-sponsor `max_impressions_per_day` set in `config/grimba_ads.php`.
- Per-sponsor `max_impressions_per_hour` for high-frequency campaigns.
- Server-side `GrimbaAds::resolve()` checks cookie before returning a card; returns next-eligible sponsor or empty if all capped.

## Privacy

- Cookie is first-party + session-scoped (max 24h).
- No cross-site tracking.
- No personal identifier; sponsor counter is opaque to reader.

## Anti-patterns

- No retargeting (out of scope per Iboga editorial principles).
- No reader-segment exclusions in v1 (uniform cap across all sessions).

## Cross-references

Master plan: S1278. Sister: S873 (capping general), S867-S895 (ads pack), S1279 (viewability), S894 (consent dashboard).
