# GrimbaNews — Partner Content Takedown Workflow Plan

**Status:** plan v0
**Owner:** Maya Patel (Compliance) + Lucy Leai (Strategy) + Ethan Wilson (Support)
**Walks:** Mythos S1446 (partner content-takedown workflow) deferred → partial
**Gating dependency:** partner contract + ticket-tracking infra + admin flip surface.

## Why this exists

Partners (and rights holders generally) need a documented, time-bound takedown workflow. Surrogate today is admin manually flipping `posts.status` to draft. That's not auditable, not SLAed, and not multi-jurisdiction-aware.

## v1 workflow

1. Partner submits takedown via `/dev/takedown` form OR via partner-API endpoint.
2. Auto-acknowledgement within 15 minutes (audit log row).
3. Triage SLA: 24 hours for partner takedowns; 72 hours for general DMCA.
4. Compliance review (Maya): valid → flip `posts.status = takedown_pending`, write reason to `takedown_log`.
5. Final flip: `posts.status = removed`, with public-page returning 410 Gone + reason category (per ombudsman charter S2021).
6. Appeal path: 7-day window for original-source rebuttal.

## Schema

```sql
CREATE TABLE takedown_requests (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  post_id BIGINT NOT NULL,
  requested_by VARCHAR(255) NOT NULL,
  partner_id BIGINT NULL,
  reason_category ENUM('dmca', 'partner_exclusivity_breach', 'factual_error', 'consent_withdrawal', 'legal_order') NOT NULL,
  description TEXT,
  status ENUM('received', 'triaged', 'removed', 'rejected', 'appealed') DEFAULT 'received',
  received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  resolved_at TIMESTAMP NULL,
  reviewer_user_id BIGINT NULL,
  notes TEXT NULL,
  INDEX idx_status (status, received_at)
);
```

## Audit & transparency

- Aggregate counts published in annual transparency report (S2001).
- Per-request review log retained 7 years.

## Cross-references

Master plan: S1446. Sister: S1442 (content-share), S1448 (brand-safety), S2001 (transparency report). See also `GRIMBANEWS_DMCA_TAKEDOWN_COUNTS_LEDGER_PLAN.md`.
