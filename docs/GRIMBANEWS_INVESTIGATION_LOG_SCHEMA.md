# GrimbaNews — Ombudsman Investigation Log Schema

**Status:** plan v0 (no log table; nothing tracked)
**Owner:** Larry Ellison (VP DBA) on schema + Rajesh Kumar (Backend) on integration + Sara Chen on access control
**Walks:** Mythos S2027 (Complaint triage workflow — investigation log internal) deferred → partial
**Gating dependency:** Ombudsman intake (S2023) + triage rubric (S2026) + ombudsman hire.

## Why this exists

S2027 is the internal audit trail of every investigation. Without it the annual report has no defensible numbers.

## Schema (target)

```sql
CREATE TABLE ombudsman_investigations (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  complaint_id BIGINT NOT NULL,
  severity ENUM('p0','p1','p2','p3') NOT NULL,
  opened_at TIMESTAMP NOT NULL,
  assigned_to BIGINT NOT NULL,              -- FK ombudsman or delegate
  status ENUM('triaged','investigating','awaiting_response','decided','closed') DEFAULT 'triaged',
  decision ENUM('upheld','partially_upheld','denied','withdrawn','out_of_scope') NULL,
  decision_summary TEXT NULL,
  decision_rationale TEXT NULL,
  public_excerpt TEXT NULL,                 -- anonymized version for public log
  remedy ENUM('correction','retraction','clarification','staff_training','policy_change','no_action') NULL,
  decided_at TIMESTAMP NULL,
  closed_at TIMESTAMP NULL,
  INDEX (severity, status), INDEX (opened_at), INDEX (assigned_to)
);

CREATE TABLE ombudsman_investigation_events (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  investigation_id BIGINT NOT NULL,
  event_type ENUM('note','complainant_response','source_response','staff_response','external_input','status_change'),
  event_body TEXT NOT NULL,
  recorded_by BIGINT NOT NULL,
  recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (investigation_id, recorded_at)
);
```

## Access control (Sara Chen)

- Read: ombudsman + delegates + Lucy (CEO override) + Vader.
- Write: ombudsman + delegates only.
- No member/reader exposure of internal log.
- Audit log of all reads (anti-snooping).

## Retention

- 7 years from close (counsel default).
- Annual report extracts redacted version into `public_excerpt`.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2027)
- Sister docs: `docs/GRIMBANEWS_OMBUDSMAN_INTAKE_PAGE_SCOPE.md`, `docs/GRIMBANEWS_COMPLAINT_TRIAGE_RUBRIC_PLAN.md`, `docs/GRIMBANEWS_COMPLAINT_PUBLIC_FINDINGS_PUBLICATION_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
