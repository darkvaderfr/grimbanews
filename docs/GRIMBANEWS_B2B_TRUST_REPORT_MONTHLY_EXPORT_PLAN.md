# GrimbaNews — B2B Trust Report Monthly Export

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Lisa Nguyen (data)
**Walks:** Mythos S1651 (B2B trust report monthly export) deferred → partial
**Gating dependency:** B2B Pro+ tier (Wave AABB) + trust-report generator pipeline.

## Why this exists

B2B customers (brand-safety vendors, ad-tech, academic) need monthly digestible trust report per source. Justifies paid tier.

## Report contents

Per-month aggregate:

- Per-source bias mix (L/C/R article distribution)
- Per-source factuality trend (Δ vs previous month)
- Per-source ownership changes (per Wave AAJJ)
- Per-source MG-cluster contribution count
- Per-source BS-cluster contribution count
- Per-source correction rate
- Per-source SLA compliance (uptime + freshness)

## Formats

- JSON (full structured)
- PDF (executive summary + per-source)
- CSV (per-source aggregate row)

## Delivery

- Automated monthly email per customer
- Available on demand via `/api/customer/trust-report/{YYYY-MM}.{json|pdf|csv}`

## Cross-references

Master plan: S1651. Sister: `docs/GRIMBANEWS_B2B_PER_CUSTOMER_DASHBOARD_PLAN.md`, `docs/GRIMBANEWS_PER_REGION_TRUST_DASHBOARD_PLAN.md`.
