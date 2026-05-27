# GrimbaNews — Ad Viewability Tracking Plan

**Status:** plan v0
**Owner:** Rajesh Kumar (Backend) + David Chen (Data) + Maya Patel (Compliance)
**Walks:** Mythos S1279 (viewability tracking) deferred → partial
**Gating dependency:** viewability SDK integration (MOAT / IAS / Google ActiveView) OR home-built IntersectionObserver-based tracker.

## Why this exists

Sponsor invoicing without viewability data is industry-out-of-date. IAB defines viewable as ≥ 50% pixels in-viewport for ≥ 1 continuous second for display, ≥ 2 seconds for video.

## v1 design (no third-party SDK)

Avoid third-party SDK at v1 to keep payload small + privacy-respecting:

- IntersectionObserver on each direct-sponsor card.
- On 50% threshold sustained ≥ 1s, fire `viewability` event to `/api/internal/ad-viewability` (rate-limited).
- Server aggregates per-sponsor per-day in `direct_sponsor_viewability_daily`.

## Schema

```sql
CREATE TABLE direct_sponsor_viewability_daily (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  sponsor_slug VARCHAR(64) NOT NULL,
  placement_key VARCHAR(64) NOT NULL,
  visit_date DATE NOT NULL,
  impressions INT DEFAULT 0,
  viewable_impressions INT DEFAULT 0,
  UNIQUE KEY uq_sponsor_placement_date (sponsor_slug, placement_key, visit_date)
);
```

## Privacy

- No third-party SDK at v1 (no MOAT cookie, no IAS pixel).
- Event payload is sponsor + placement, never reader-identifying.
- Aggregate-only reports surfaced to sponsors.

## Cross-references

Master plan: S1279. Sister: S1278 (frequency cap), S867-S895 (ads pack), S894 (consent dashboard). Memory: `feedback_nobuai_model_branding.md` (no provider names in sponsor-side dashboards either).
