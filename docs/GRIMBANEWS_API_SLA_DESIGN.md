# GrimbaNews — API SLA Design

**Status:** plan v0 (no formal SLA; /health + /up cover uptime evidence today)
**Owner:** Lucy Leai (Strategy) on commitment + Jacob Lee (DevOps) on monitoring + Ray Dalio (CFO) on credit-back accounting + Hannah Kim on incident escalation
**Walks:** Mythos S1189 (API SLA) deferred → partial
**Gating dependency:** API v2 in production + commercial-tier partner contract that requires SLA + statuspage tooling

## Why this exists

S1189 is the legal commitment to partners. Without a written SLA, commercial partners cannot enterprise-procure (their procurement requires written availability guarantees + credit-back terms).

## Today's surrogate

- **`/health` JSON** — health check endpoint.
- **`/up` simple ping** — service alive check.
- **GrimbaAutomationMonitor at `/admin/grimba/cockpit`** — operator-visible health.

## SLA tier matrix

| Tier | Monthly uptime | p95 latency | Support response | Credit-back |
|---|---|---|---|---|
| Free / Academic | best-effort 99% | <1000ms | 5 business days | none |
| Commercial Starter | 99.5% | <500ms | 1 business day | 5% / hour-down |
| Commercial Pro | 99.9% | <300ms | 4 business hours | 10% / hour-down + escalation |
| Commercial Enterprise | 99.95% | <200ms | 1 business hour | per-contract |

## Uptime definition

- "Up" = `/api/v2/health` returns 200 AND `/api/v2/posts?per_page=1` returns 200 within p95 latency target.
- Synthetic monitoring every 60s from 3 locations (Europe, NA, APAC).
- Monthly uptime = up-minutes ÷ total-minutes × 100.
- Excludes:
  - Scheduled maintenance (announced 7d in advance, max 4h/month, off-peak local time).
  - Force majeure (vendor outage at FCM/APNs propagated to API health).

## p95 latency definition

- Per-endpoint, per-tier-of-partner-key.
- Measured from request received → response written.
- Excludes network transit beyond our edge.

## Credit-back mechanism

- Per-hour downtime past SLA → percentage of monthly invoice as credit.
- Self-claim form at `/account/sla-credits`.
- Auto-credit if our monitoring caught it (no claim needed).
- Annual cap: 50% of annual invoice.

## Communication

- Per-incident: status page entry within 15 min of detection.
- Major incident: email to all partner contacts.
- Per-month: uptime report to commercial partners with link to historic data.

## Status page

- Subdomain `status.grimbanews.com`.
- Per-component: API, Web, Auth, RSS feeds, Newsletter.
- Public uptime history.
- RSS feed of incidents.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1189)
- Sister docs: `docs/GRIMBANEWS_PUBLIC_API_V2_DESIGN.md`, `docs/GRIMBANEWS_STATUS_PAGE_PLAN.md`, `docs/GRIMBANEWS_ESCALATION_TIERS.md`, `docs/GRIMBANEWS_PARTNER_OPS_PLAYBOOK.md`, `docs/GRIMBANEWS_API_PARTNER_ANALYTICS_PLAN.md`
- Existing surrogates: `/health`, `/up`, `/admin/grimba/cockpit`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
