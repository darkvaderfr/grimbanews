# GrimbaNews — Partner Sandbox Environment Plan

**Status:** plan v0 (no sandbox env; gates on partner program existence + API v2 shipping)
**Owner:** Jacob Lee (DevOps) provisions env + Rajesh Kumar (Backend) on sandbox data seeding + Michael O'Connor on docs + Liam Smith (PM) on partner onboarding flow
**Walks:** Mythos S1185 (Partner sandbox) deferred → partial
**Gating dependency:** API v2 shipped (S1181) + at least one partner relationship (today: none) + isolated VPS instance OR docker-compose target

## Why this exists

S1185 is the safe-to-experiment environment for new partners — no production rate limits, no PII risk, stable test data. Without it, every partner integration risks hitting prod limits or churning real reader data.

## Today's surrogate

- **Production with strict rate limits** — would be the only option until sandbox ships.
- **Local docker-compose** at `docker-compose.yml` — partner-side option, but not GrimbaNews-hosted.

## Architecture options

### Option A — Separate VPS

- Independent VPS instance running same Laravel/Botble stack.
- Domain: `sandbox.grimbanews.com`.
- Isolated DB seeded with synthetic data.
- **Cost:** ~$15/mo (small VPS).
- **Pro:** full isolation, can mock failure modes.
- **Con:** drift risk between prod and sandbox configs.

### Option B — Single instance, namespaced

- Same VPS, separate Laravel env (`sandbox`) on different port + subdomain.
- Sandbox DB = read-only snapshot of prod refreshed nightly.
- **Pro:** no extra hosting; lightweight.
- **Con:** can affect prod performance, less isolated.

**Recommendation:** Option A for partner work; Option B for internal QA.

## Sandbox data seeding

- 1000 synthetic posts across 50 synthetic sources.
- 100 clusters with full bias-distribution variety.
- 30 days of historical data with realistic timestamps.
- Reset cadence: weekly (Sunday 04:00 UTC) — partners warned via API headers + status page.

## API behavior differences

| Behavior | Production | Sandbox |
|---|---|---|
| Rate limit | per-tier (per S1181) | 10000/hour flat |
| Data | live ingest | static seeded (reset weekly) |
| Latency | actual VPS | deliberately slowed by ~50ms (avoids partners coding to no-latency baseline) |
| Webhooks (when shipped) | real | test webhooks fire to partner sandbox URL |
| Error modes | accurate | partner can request injected errors via header `X-Sandbox-Force-Error: 500` |

## Sandbox key issuance

- Per-partner sandbox key, prefix `gn_sbx_`.
- No production key shares the sandbox prefix (clear visual flag).
- No payment required for sandbox tier.

## Migration to production

- Partner self-service "promote key" — operator-approves migration of sandbox config to production.
- Sandbox key + production key co-exist during partner's transition window.

## Status

- Sandbox status visible at `https://sandbox.grimbanews.com/status` + production status page.
- Sandbox does NOT count toward production SLA.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1185)
- Sister docs: `docs/GRIMBANEWS_PUBLIC_API_V2_DESIGN.md`, `docs/GRIMBANEWS_B2B_API_PARTNER_SANDBOX_PLAN.md`, `docs/GRIMBANEWS_PARTNER_DOCS_PLAN.md`, `docs/GRIMBANEWS_API_LAUNCH_PLAYBOOK.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
