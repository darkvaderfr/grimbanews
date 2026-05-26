# GrimbaNews — DMCA / Takedown Counts Ledger Plan

**Status:** plan v0 (no takedown intake; no `takedown_requests` table)
**Owner:** Sara Chen (CISO) + Maya Patel + counsel
**Walks:** Mythos S2003 (DMCA / takedown counts) deferred → partial
**Gating dependency:** mailto:legal@grimbanews.com alias provisioned + counsel-defined per-jurisdiction rules + operator log.

## Why this exists

S2003 makes the annual transparency report's takedown numbers fillable. Today no intake exists — a takedown request would land in the generic contact form, untracked.

## Today's surrogate

- `/api/contact` generic intake. Operator manually triages.

## Schema (target)

```sql
CREATE TABLE takedown_requests (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  received_at TIMESTAMP NOT NULL,
  jurisdiction CHAR(2) NOT NULL,            -- US, FR, DE, etc.
  basis ENUM('dmca','gdpr_erasure','court_order','rgpd_droit_oubli','other'),
  requester_type ENUM('individual','company','government','attorney') NOT NULL,
  target_post_id BIGINT NULL,               -- FK posts when single-article
  target_url VARCHAR(500) NULL,
  action_taken ENUM('granted','denied','partial','counter_notice','pending') DEFAULT 'pending',
  reviewed_by BIGINT NULL,
  reviewed_at TIMESTAMP NULL,
  notes TEXT NULL,
  redacted BOOLEAN DEFAULT FALSE,           -- public log redacted version
  INDEX (received_at), INDEX (jurisdiction), INDEX (action_taken)
);
```

## Intake channel

- Dedicated `legal@grimbanews.com` alias (deferred — needs DNS + Acelle inbox routing).
- `/dmca` public form with structured fields → posts to `takedown_requests`.
- Confirmation email auto-acknowledges within 24h.

## SLA

- Initial response: 14 days statutory (US DMCA).
- Decision: 60 days (GDPR Article 12).

## Public log

- Annual report (S2001) aggregates: total received, per-jurisdiction, per-basis, action breakdown.
- Per-request public-redacted page (optional v2; counsel review).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2003)
- Sister docs: `docs/GRIMBANEWS_ANNUAL_TRANSPARENCY_SCOPE_DECISION.md`, `docs/GRIMBANEWS_DMCA_RIGHT_OF_REPLY_POLICY.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
