# GrimbaNews — B2B API v1 Design

**Status:** plan v0 (no /api/v1 JSON surface; RSS is the only programmatic read today)
**Owner:** Rajesh Kumar (Backend) on shape + Victor Garcia (BD) on which fields matter for which partner verticals + Liam Smith (PM) on tiering
**Walks:** Mythos S1231 (B2B API v1 design) deferred → partial
**Gating dependency:** Sanctum install + API key schema (S1234) + first commercial contract

## Why this exists

S1231 covers the B2B-specific design lens — public API v2 (S1181) covers academic + light-commercial. B2B API v1 is the contract-attached, enterprise-ready surface: more fields, webhooks, deeper history, SLA-backed.

## Today's surrogate

- **RSS / Atom feeds** — public, no auth, no per-key analytics, no historical access beyond ~60 items.
- **Public API v2 plan** at `GRIMBANEWS_PUBLIC_API_V2_DESIGN.md` — overlapping endpoint shape but no contract terms attached.

## What B2B adds beyond v2

| Feature | Public v2 | B2B v1 |
|---|---|---|
| Endpoints | core read | core read + bulk export + webhooks + cluster-merge feed |
| History depth | 90 days | unlimited |
| Field coverage | metadata + summary | + raw extract + per-source citation chain |
| Latency target | 500ms p95 | 200ms p95 |
| Webhook delivery | not available | available |
| Bulk export | not available | nightly NDJSON dump per partner |
| SLA | best-effort | 99.5%+ per tier |
| Brand-safe filtering | not available | per-partner brand-safety filter (e.g., exclude adult content) |

## B2B-only endpoints (added to v2 surface)

| Endpoint | Purpose |
|---|---|
| `GET /api/b2b/v1/bulk/posts.ndjson` | Stream NDJSON for batch ingest |
| `POST /api/b2b/v1/webhooks` | Subscribe to event types |
| `GET /api/b2b/v1/clusters/{id}/lineage` | Full cluster-merge history |
| `GET /api/b2b/v1/sources/{slug}/history` | Per-source factuality / bias time series |
| `GET /api/b2b/v1/export/snapshot/{date}` | Daily snapshot archive |

## Webhook event types

| Event | Payload |
|---|---|
| `post.published` | `{id, slug, source_id, cluster_id, categories, published_at}` |
| `cluster.formed` | `{id, member_posts, bias_distribution}` |
| `cluster.bias_shifted` | `{id, before, after, delta}` |
| `correction.issued` | `{post_id, correction_text, issued_at}` |
| `source.classification_changed` | `{source_id, before, after, reason}` |

## Delivery semantics

- At-least-once delivery with 24h retry window.
- Partner endpoint must respond 200 within 10s.
- Failures retry: 1m, 5m, 30m, 6h, 24h.
- Per-partner failure rate >10% triggers Slack alert + comms.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1231)
- Sister docs: `docs/GRIMBANEWS_PUBLIC_API_V2_DESIGN.md`, `docs/GRIMBANEWS_B2B_API_V1_AUTH_PLAN.md`, `docs/GRIMBANEWS_B2B_API_KEY_ISSUANCE_PLAN.md`, `docs/GRIMBANEWS_API_SLA_DESIGN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
