# GrimbaNews — B2B API Partner Sandbox Plan

**Status:** plan v0 (no sandbox env; mirrors S1185 with B2B-tier specifics)
**Owner:** Jacob Lee (DevOps) provisions + Rajesh Kumar on B2B-specific seed data + Liam Smith (PM) on partner onboarding via sandbox + Hannah Kim on injected-failure mode flags
**Walks:** Mythos S1236 (B2B API v1 partner sandbox) deferred → partial
**Gating dependency:** Public API v2 sandbox (S1185) shipped + B2B-specific endpoints (S1231) available

## Why this exists

S1236 is the B2B-specific sandbox layer. Same architecture as S1185 with extras: webhook delivery simulation, bulk-export sample bundles, mTLS testing fixtures, per-event failure-mode toggles.

## Today's surrogate

- **Public API v2 sandbox plan** (S1185) — broader pattern.
- **Local docker-compose** — partners can run locally but not GrimbaNews-hosted.

## B2B-specific sandbox additions

### Webhook delivery simulation

- Partner registers sandbox webhook URL.
- Sandbox sends synthetic events at 1/min cadence.
- Partner can trigger specific events via `POST /api/b2b/v1/sandbox/trigger-webhook` with `event_type`.
- Failure-mode flags via header: `X-Sandbox-Force-Error: 500` for next webhook delivery.

### Bulk-export samples

- Pre-built NDJSON bundles at `https://sandbox.grimbanews.com/api/b2b/v1/bulk/posts.ndjson`.
- Always 10,000 posts (consistent for benchmarking).
- Refresh weekly with reset.

### mTLS testing

- Sandbox accepts mTLS connections at `mtls.sandbox.grimbanews.com:8443`.
- Partner can upload test CA cert to sandbox to verify client-cert auth.
- Real production mTLS gates on contract.

### Per-key failure injection

Partner header `X-Sandbox-Inject-Error: rate_limit | timeout | malformed_json` triggers specific failure for next N requests.

Helps partners validate retry / backoff logic.

## Quotas (sandbox)

- 10000 requests/hour flat (vs production tier-based).
- Daily cap: unlimited.
- Bulk export: 1/day per key.

## Sandbox tier scopes

- All scopes available regardless of partner tier (so partners can build for tier upgrades).
- Per-tier headers in response simulate production tier:
  - `X-RateLimit-Tier: starter` + `X-RateLimit-Limit: 1000` even if sandbox flat-quota.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1236)
- Sister docs: `docs/GRIMBANEWS_PARTNER_SANDBOX_PLAN.md`, `docs/GRIMBANEWS_B2B_API_V1_DESIGN.md`, `docs/GRIMBANEWS_B2B_API_KEY_ISSUANCE_PLAN.md`, `docs/GRIMBANEWS_API_PARTNER_ANALYTICS_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
