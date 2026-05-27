# GrimbaNews — Embed Token API + Rate Limit Plan

**Status:** plan v0
**Owner:** Rajesh Kumar (backend) + Sara Chen (CISO)
**Walks:** Mythos S1654 (embed-token API + rate-limit per publisher) deferred → partial
**Gating dependency:** Wave NNNN /api/middle-ground.json shipped + B2B API tier (Wave AABB).

## Why this exists

Embed widget (Wave SUB-21 sister) needs per-publisher rate limits + analytics. Token-gated API provides this.

## Schema (gates on Vader migration approval)

```
embed_tokens:
  id | token (UUID) | publisher_name | publisher_email | tier (free|pro|enterprise)
   | daily_render_cap | total_renders | created_at | revoked_at
embed_render_log:
  id | token_id | cluster_id | rendered_at | referrer_domain
```

## Per-tier limits

- **Free (no auth):** 100 renders/IP/day. Cluster-only metadata.
- **Pro ($49/mo):** 10K renders/day. Per-render analytics. Custom theming.
- **Enterprise (custom):** Unlimited. White-label option. Dedicated support.

## Token lifecycle

1. Publisher signs up at /partners/embed.
2. Per-token issued + emailed.
3. Per-token usage tracked + capped.
4. Per-token revocation API for security incidents.

## Analytics surface

`/admin/grimba/embed-analytics`:
- Top embedding domains
- Per-domain render trend
- Per-cluster most-embedded
- Per-token quota usage

## Cross-references

Master plan: S1654. Sister: `docs/GRIMBANEWS_EMBED_JS_SNIPPET_GENERATOR.md`, `docs/GRIMBANEWS_B2B_EDITORIAL_TRUST_SCORE_API_PLAN.md`.
