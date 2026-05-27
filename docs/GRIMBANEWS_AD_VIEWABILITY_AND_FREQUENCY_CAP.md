# GrimbaNews — Ad Viewability + Frequency Cap Plan

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Lisa Nguyen (data)
**Walks:** Mythos S1687 (ad viewability tracking) deferred → partial
**Gating dependency:** Ad networks integrated.

## v1 design — viewability

IntersectionObserver fires when ≥ 50% pixels in viewport ≥ 1 sec (IAB MRC display spec). Per-ad-slot tracker records served vs viewable.

## v1 design — frequency cap

Per-reader (cookie-based, anonymized) cap:
- 3× same ad per session
- 8× same ad per week
- Hard cap per advertiser per reader per month

## Reporting

`/admin/grimba/ad-viewability` weekly dashboard:
- Per-network viewability rate
- Per-position viewability rate
- Trend vs IAB benchmarks

## Cross-references

Master plan: S1687. Sister: `docs/GRIMBANEWS_AD_REVENUE_DASHBOARD_SCOPE.md` (Wave LLL).
