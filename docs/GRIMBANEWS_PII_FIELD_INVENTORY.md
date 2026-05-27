# GrimbaNews — PII Field Inventory

**Status:** inventory v0
**Owner:** Sara Chen (CISO) + Larry Ellison (DBA)
**Walks:** Mythos S1081 (PII field inventory) deferred → partial
**Gating dependency:** Annual review cadence + per-field encryption decision.

## Per-table PII fields

| Table | Field | Encryption | Retention | DSAR-export |
|---|---|---|---|---|
| members | email | hashed-for-lookup | account-life + 30d | yes |
| members | first_name | none | same | yes |
| members | last_name | none | same | yes |
| members | locale | none | same | yes |
| members | password | bcrypt hash | same | no |
| members | last_login_at | none | same | yes |
| members | newsletter_subscribed | none | same | yes |
| members | vault_digest_post_ids | none | same | yes |
| sessions | user_id + ip + user_agent | none | 30d TTL | yes |
| comments | author_email | none | comment-life | yes |
| comments | author_ip | anonymized | 7d then prune | yes |
| advertiser_leads | email + phone + company | encrypted at rest (S1561 planned) | 2y | yes |
| audit_logs | actor_id + action + ip | none | 1y then prune | yes (caveats) |

## DSAR pattern

Per `docs/GRIMBANEWS_VOS_DROITS_READER_RIGHTS_PAGE_SCOPE.md` (Wave KKKK):

1. Reader requests via `/vos-droits`.
2. System bundles all rows WHERE email matches.
3. Includes anonymized session metadata.
4. Excludes hash-only fields per GDPR carve-out.
5. JSON + CSV bundle encrypted with reader-provided passphrase.

## Right-to-be-forgotten

- Anonymize: replace name + email with hash; preserve aggregate metrics.
- Hard-delete: vault entries, comments authored by user, audit-log entries older than 90 days.
- Soft-keep: anonymized newsletter unsubscribe ID for compliance.

## Cross-references

Master plan: S1081. Sister: `docs/GRIMBANEWS_VOS_DROITS_READER_RIGHTS_PAGE_SCOPE.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`, `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md`.
