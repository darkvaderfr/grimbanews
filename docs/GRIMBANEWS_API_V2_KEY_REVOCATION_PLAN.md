# GrimbaNews — API v2 Key Revocation Plan

**Status:** plan v0 (no api_keys table; no revocation surface)
**Owner:** Sara Chen (CISO) on procedure + Rajesh Kumar (Backend) on enforcement + Maya Patel (Compliance) on audit log
**Walks:** Mythos S1184 (Key revocation) deferred → partial
**Gating dependency:** `api_keys` table shipped (S1234) + admin /api-keys/* surface

## Why this exists

S1184 is the operator playbook for "kill this key now". Suspected compromise, contract terminated, partner offboarding, regulator request — all need predictable, fast revocation.

## Today's surrogate

- **No API keys to revoke** — surrogate is `.env` rotation for shared secrets which already follows the pattern.

## Revocation triggers

| Trigger | Severity | SLA |
|---|---|---|
| Partner reports suspected key leak | P0 | revoke within 1 hour |
| GrimbaNews detects abuse (spike + abnormal pattern) | P0 | revoke within 1 hour |
| Partner offboarding (contract end) | P1 | revoke at end-of-contract date |
| Partner upgrades to new key (rotation) | P2 | revoke old after 24h overlap |
| Key expires per `expires_at` | P3 | revoke automatically via cron |
| Inactivity (no use >180d) | P3 | flag for review, not auto-revoke |

## Revocation procedure (operator)

1. Admin navigates to `/admin/grimba/api-keys/{id}`.
2. Click "Revoke" — confirmation modal with key fingerprint + partner name.
3. Enter reason (free-text).
4. Confirm — sets `api_keys.is_active = false`, writes audit log row.
5. Email partner with revocation reason (template: "We have revoked your key effective immediately. Reason: ...").

## Enforcement layer

- All `/api/v2/*` middleware checks `api_keys.is_active` per request — no caching beyond 60s.
- Revoked key request → 401 Unauthorized + body: `{"error":"key_revoked","contact":"api@grimbanews.com"}`.
- Per-key request count BEFORE revocation captured for audit.

## Audit log (Maya Patel)

`api_key_audit_log`:

| Field | Type | Use |
|---|---|---|
| api_key_id | BIGINT | FK |
| action | ENUM('created','rotated','revoked','reactivated') | event |
| actor | VARCHAR(255) | admin user OR 'system' (for cron-driven expiry) |
| reason | TEXT | required for revocation |
| ip | VARCHAR(45) | actor IP |
| timestamp | TIMESTAMP | NOT NULL |

Retained 7 years per GDPR ROPA.

## Mass revocation

- Per-partner: revoke all keys for partner.id (cascade).
- Per-tier: revoke all academic keys (rare; only for tier-wide policy change).
- "Big red button" admin action requires Vader sign-off (2-person rule).

## Communication

- Per-partner revocation: email partner + post to `#grimba-api-incidents` Slack.
- Mass revocation: prepared comms via Henry Walker / Gary Vaynerchuk.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1184)
- Sister docs: `docs/GRIMBANEWS_PUBLIC_API_V2_DESIGN.md`, `docs/GRIMBANEWS_B2B_API_KEY_ISSUANCE_PLAN.md`, `docs/GRIMBANEWS_B2B_API_KEY_ROTATION_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
