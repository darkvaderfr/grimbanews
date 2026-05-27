# GrimbaNews — Per-City Sponsor / Advertiser Slot

**Status:** plan v0
**Owner:** Ray Dalio (CFO) + Lucy Leai (Strategy) + Jacob Lee (DevOps)
**Walks:** Mythos S1609 (per-city sponsor slot) deferred → partial
**Gating dependency:** `app/Support/GrimbaAds.php` ships single global ad slot; per-city ad-targeting needs `city_id` column on `posts` + per-ad geo-targeting config.

## Why this exists

Local advertisers (restaurants, retail, real-estate) want city-specific ad placement. National brands accept geo-untargeted slots; local SMEs need per-city.

## v1 design

`/local/{city-slug}` pages serve per-city ad slot:
- Operator configures per-city ad inventory.
- Per-ad: city_id + start_date + end_date + creative + click-tracker.
- Per-impression: capped per-reader frequency per Wave AAQQ.

## Schema (gates on Vader migration approval)

```
ads_per_city:
  id | city_id | advertiser_id | creative_url | landing_url
   | start_date | end_date | per_day_impression_cap | total_impressions_cap
   | created_at
```

## Per-ad reporting

- Per-ad impressions
- Per-ad clicks
- Per-ad CTR
- Per-ad spend (if paid)
- Per-advertiser monthly export

## Sales motion

- Per-city ad sales handled by per-region editor + Lucy.
- Per-ad creative review by editor (brand-safety + style match).
- Self-serve advertiser portal: phase 2.

## Cross-references

Master plan: S1609. Sister: `docs/GRIMBANEWS_PER_CITY_DEDICATED_LANDING_PLAN.md`, `docs/GRIMBANEWS_AD_VIEWABILITY_AND_FREQUENCY_CAP.md`.
