# GrimbaNews — API Webhook Publisher Plan

**Status:** plan v0
**Owner:** Rajesh Kumar (backend) + Liam Smith (PM)
**Walks:** Mythos S1598 (API webhook publisher) deferred → partial
**Gating dependency:** B2B API tier (Wave AABB) + per-partner webhook config UI.

## Why this exists

Pull-based API (Wave NNNN /api/middle-ground.json) suits researchers polling on a schedule. For real-time partners (newsroom systems, brand-safety vendors), push-based webhooks are more efficient + less wasteful.

## v1 design

Per-partner webhook URLs registered via `/admin/grimba/partners/{id}/webhooks`:

```
partner_webhooks:
  id | partner_id | event_type (cluster.created | cluster.updated | mg.tagged | bs.tagged | source.shifted)
   | url | secret_token | active | last_fired_at | failure_count
```

When event fires (e.g. new cluster reaches 5+ sources), webhook delivery:

1. POST to partner's URL with HMAC-SHA256 signature.
2. Body: JSON event payload.
3. Retry 3x with exponential backoff if non-2xx.
4. After 5 consecutive failures, auto-deactivate + alert partner.

## Event types

- `cluster.created` — cluster reaches 5+ sources (high-signal threshold)
- `cluster.updated` — cluster gains 2+ new sources
- `mg.tagged` — cluster gets Middle Ground tag
- `bs.tagged` — cluster gets Blindspot tag
- `source.shifted` — source bias/factuality changed (per `docs/GRIMBANEWS_BIAS_SHIFT_DETECTION_PLAN.md`)

## Security

- HMAC signature verifies authenticity.
- Per-partner secret rotation (per `docs/GRIMBANEWS_SECRET_ROTATION_RUNBOOK.md`).
- Partner can opt-in per event type (don't push events they don't want).
- Rate-limit per partner (e.g. max 1000 webhooks/hr).

## Cross-references

Master plan: S1598. Sister: `docs/GRIMBANEWS_B2B_EDITORIAL_TRUST_SCORE_API_PLAN.md`, `docs/GRIMBANEWS_API_PARTNER_ANALYTICS_PLAN.md` (Wave DDDD).
