# GrimbaNews — B2B API v1 Auth Plan

**Status:** plan v0 (no Sanctum/Passport install; no api_keys table)
**Owner:** Sara Chen (CISO) signs auth model + Rajesh Kumar (Backend) implements + Larry Ellison on schema
**Walks:** Mythos S1232 (B2B API v1 auth) deferred → partial
**Gating dependency:** Sanctum vs Passport pick (per `GRIMBANEWS_API_V2_OAUTH_CLIENT_PLAN.md`) + first B2B contract requires specific auth mode

## Why this exists

S1232 is the B2B-specific auth layer. Differs from public v2 OAuth (S1182) — B2B partners are operator-to-operator (server-to-server), so static keys + IP allowlist is sufficient; OAuth-style end-user-on-behalf flows generally not needed.

## Today's surrogate

- **No B2B auth surface.**
- **`.env` based shared secrets** internal-only (e.g., NEWSDATAIO_API_KEY pattern).

## Auth modes supported

| Mode | Use case | Implementation |
|---|---|---|
| Static bearer token | server-to-server batch ingest | `Authorization: Bearer gn_b2b_...` |
| Webhook signature | inbound webhooks from partners (rare) | HMAC-SHA256 with shared secret per partner |
| mTLS | enterprise partner with strict compliance | optional — gates on partner ask |

## Static bearer token format

- Prefix `gn_b2b_<tier>_<32-char-secret>`.
- Example: `gn_b2b_starter_a1b2c3d4e5f6g7h8...`.
- Hashed (sha256) at rest in `api_keys.key_hash`.
- Prefix kept plaintext in `key_prefix` for admin display.

## IP allowlist (per-key)

- Partner registers source IPs/CIDR ranges at key creation.
- Middleware checks request IP against allowlist.
- Mismatch → 403 Forbidden + Slack alert (potential leak).

## Tier-scoped scopes

| Tier | Scopes |
|---|---|
| Starter | `posts:read`, `clusters:read`, `sources:read` |
| Pro | Starter + `search:read`, `trends:read`, `webhooks:subscribe`, `bulk:read` |
| Enterprise | Pro + `historical:read`, `lineage:read` |

## Token lifecycle

| Phase | Action |
|---|---|
| Issuance | Admin creates via `/admin/grimba/api-keys/new` |
| Rotation | Partner can rotate via self-service portal; old key valid 24h |
| Revocation | Per `GRIMBANEWS_API_V2_KEY_REVOCATION_PLAN.md` |
| Expiry | Auto-disable past `expires_at` (default 365d) |

## Audit log

Every auth event written:
- successful auth (key, endpoint, IP, timestamp)
- failed auth (reason, IP, attempted-prefix)
- suspicious pattern (key used from new IP / new UA suddenly)

## Rate limit + auth interplay

- 401 (bad auth) does NOT count against rate limit.
- 403 (good auth, bad scope) counts against rate limit.
- 429 (good auth, over quota) counts.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1232)
- Sister docs: `docs/GRIMBANEWS_B2B_API_V1_DESIGN.md`, `docs/GRIMBANEWS_B2B_API_KEY_ISSUANCE_PLAN.md`, `docs/GRIMBANEWS_B2B_API_IP_ALLOWLIST_PLAN.md`, `docs/GRIMBANEWS_API_V2_OAUTH_CLIENT_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
