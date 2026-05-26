# GrimbaNews — B2B API Key Issuance Plan

**Status:** plan v0 (no api_keys schema; no admin UI)
**Owner:** Rajesh Kumar (Backend) builds + Sara Chen (CISO) signs + Larry Ellison on schema + Liam Smith (PM) on partner-facing UX
**Walks:** Mythos S1234 (B2B API v1 key issuance) deferred → partial
**Gating dependency:** `api_keys` table shipped + admin route + partner agreement signed

## Why this exists

S1234 is the operator playbook for "give this partner a working key". Without process, ad-hoc keys ship through hand-written DB inserts — recipe for missed audit log entries + misconfigured scopes.

## Today's surrogate

- **No keys issued.**
- **`.env` shared secrets** (internal-only) follow strict no-leak pattern via VPS-only `.env`.

## Issuance flow (operator)

1. Partner signs agreement → contract row in `partners` table.
2. Admin navigates to `/admin/grimba/partners/{id}/keys/new`.
3. Form fields:
   - Key name (human label): "Partner X production"
   - Tier: starter / pro / enterprise
   - Scopes: chip selector (defaults per tier)
   - Expiry: default 365d (editable per contract terms)
   - IP allowlist (CIDR ranges, optional)
   - Daily quota (per-tier default, editable)
4. On submit:
   - Generate cryptographically-random 32-char secret (`bin2hex(random_bytes(16))`).
   - Hash with sha256 → `key_hash`.
   - Compute prefix: `gn_b2b_{tier}_{first-4-chars}`.
   - Insert row.
   - Display **once** to admin: full key (plaintext) + curl example for partner.
5. Email partner with:
   - Plain-text key (one-time)
   - Sandbox + production URLs
   - Docs link
   - Support contact
6. Log issuance to audit log.

## Schema (extends what `GRIMBANEWS_API_ACADEMIC_TIER_PLAN.md` proposed)

```sql
ALTER TABLE api_keys ADD COLUMN partner_id BIGINT NULL;
ALTER TABLE api_keys ADD COLUMN ip_allowlist JSON DEFAULT NULL;  -- ["10.0.0.0/8", "192.168.1.5/32"]
ALTER TABLE api_keys ADD COLUMN webhook_secret VARCHAR(128) DEFAULT NULL;
```

## Partner-side self-service portal

`/account/api-keys` (for partner contact):

- View key list (prefix only, never plaintext).
- "Rotate key" CTA (creates new + invalidates old after 24h).
- "Revoke" CTA (immediate).
- Usage chart linked to S1188 analytics.
- IP allowlist editor.
- Webhook secret regenerate.

## Initial issuance security checks (Sara Chen)

- Partner contract must be in `partners.contract_active = true`.
- Partner must have at least one verified email on file.
- Tier must match contract.
- Admin issuing must be 2-person-rule confirmed for enterprise tier.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1234)
- Sister docs: `docs/GRIMBANEWS_B2B_API_V1_AUTH_PLAN.md`, `docs/GRIMBANEWS_B2B_API_KEY_ROTATION_PLAN.md`, `docs/GRIMBANEWS_API_V2_KEY_REVOCATION_PLAN.md`, `docs/GRIMBANEWS_API_ACADEMIC_TIER_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
