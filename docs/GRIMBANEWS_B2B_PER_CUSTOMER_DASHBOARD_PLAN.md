# GrimbaNews — B2B Per-Customer Dashboard

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Liam Smith (PM) + Rajesh Kumar (backend)
**Walks:** Mythos S1650 (B2B per-customer dashboard) deferred → partial
**Gating dependency:** B2B API tier (Wave AABB) + per-customer onboarding.

## Why this exists

Pro + Enterprise tier API customers need a dashboard to monitor:
- API quota consumption + remaining
- Per-endpoint usage breakdown
- Per-day request count
- Per-error-code summary
- Per-IP rate-limit status

## v1 design

`/customers/{customer-slug}/dashboard` (gates on B2B API auth):

- Quota gauge: used vs allowed (per day, per month).
- Top-5 endpoints by call count.
- Daily call-count trend (last 30 days).
- Top-5 error codes (4xx + 5xx).
- Active rate-limit cohorts.

## Schema

```
api_customer_usage:
  customer_id | endpoint | called_at | status_code | latency_ms
  -- 90-day retention; aggregated to monthly after that
```

## Auth

OAuth bearer token (gates on Wave DDDD OAuth client plan).

## Cross-references

Master plan: S1650. Sister: `docs/GRIMBANEWS_B2B_EDITORIAL_TRUST_SCORE_API_PLAN.md`, Wave DDDD API+analytics plans.
