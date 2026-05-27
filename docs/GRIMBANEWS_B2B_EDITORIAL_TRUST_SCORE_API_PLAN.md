# GrimbaNews — B2B Editorial Trust Score API Tier

**Status:** plan v0 (no B2B API; v1 is the public read-only `/api/middle-ground.json` Wave NNNN)
**Owner:** Lucy Leai (Strategy) + Ray Dalio (CFO) on unit economics + Liam Smith (PM)
**Walks:** Mythos S1186 (B2B partner API tier) deferred → partial — enriches the existing S1181-S1190 partial-by-Wave-DDDD band

## Why this exists

Enterprise customers (ad-tech brand-safety vendors, social-platform moderation teams, academic researchers) want programmatic access to per-source + per-cluster trust signals. This is a paid-tier API beyond the public open dataset.

## Tier ladder

| Tier | Price | Rate limit | Scope |
|---|---|---|---|
| Free | $0 | 60 req/hr | /api/middle-ground.json + /api/middle-ground.atom (public Wave NNNN) |
| Trial | $0 | 600 req/hr | + /api/sources.json (per-source trust scores) |
| Pro | $499/mo | 10K req/day | + /api/clusters.json (full cluster catalog) |
| Enterprise | custom | unlimited | + per-source ownership-history, bias-shift logs, dedicated SLA |

## Endpoints by tier

- **Free:** `/api/middle-ground.json`, `/api/middle-ground.atom`
- **Trial:** + `/api/sources.json` (paginated source list with bias, factuality, credibility scores)
- **Pro:** + `/api/clusters.json` (paginated cluster list with topic, bias mix, source count, MG/BS tags)
- **Enterprise:** + `/api/sources/{id}/history.json` (bias-shift over time), `/api/clusters/{id}/lifecycle.json` (cluster transitions over time)

## Auth

- Free: no auth, IP-rate-limited
- Trial+: OAuth bearer token via `/api/v2/auth/token` (gates on Wave DDDD OAuth client plan)
- Per-key analytics in `/admin/grimba/api-keys`

## Unit economics

- Pro tier breakeven: 20 customers × $499/mo = $10K/mo. Covers per-key analytics dev + 0.5 FTE support.
- Enterprise: priced per scope; minimum $5K/mo per contract.

## Marketing channel

- /partners/api landing page (gates on /partners page Wave DDDD).
- LinkedIn outreach to brand-safety vendors.
- Academic outreach for free research-tier (waived auth, attribution required).

## Cross-references

Master plan: S1186 enrichment. Sister: `docs/GRIMBANEWS_MIDDLE_GROUND_API_REFERENCE.md` (Wave QQQQ), `docs/GRIMBANEWS_API_V2_OAUTH_CLIENT_PLAN.md` (Wave DDDD), `docs/GRIMBANEWS_API_PARTNER_ANALYTICS_PLAN.md` (Wave DDDD).
