# GrimbaNews — Internal Audit Corrective-Action Tracking

**Status:** plan v0
**Owner:** Sara Chen (CISO) + remediation owners (per finding)
**Walks:** Mythos S1886 (internal-audit corrective-action tracking) deferred → partial
**Gating dependency:** Findings register (Wave SUB-53, S1885) — corrective actions flow off finding entries.

## Why this exists

A finding without a tracked corrective action is just a complaint. ISO 27001 clause 10.1 explicitly requires the organization to "react to the nonconformity," "evaluate the need for action to eliminate the causes," and "implement any action needed." This doc operationalizes that lifecycle so each finding produces a tracked, owned, dated, evidence-backed corrective action.

## Per-CA schema (extension of findings register)

```
corrective_actions:
  id | finding_id (FK) | description | owner | target_date
   | type (immediate_containment | root_cause_fix | process_change | training)
   | status (planned | in_progress | completed | verified | failed)
   | evidence_url (link to PR / config diff / training record / control test)
   | verifier | verification_date | post_implementation_review_date
```

## Per-CA classification

Each finding gets **at least** an immediate-containment + root-cause action. Quick fixes that don't address root cause are flagged as incomplete.

- **Immediate containment** (within SLA × 0.25): stop bleeding. E.g., revoke leaked key, disable affected feature.
- **Root-cause fix** (within full SLA): fix the underlying issue so it doesn't recur.
- **Process change** (within SLA × 1.5): update runbook, alert, code-review rubric to catch this class in the future.
- **Training** (within SLA × 2): if human error contributed, training closes the loop.

## Per-CA verification

A corrective action is not "closed" — it is "verified closed." Verification requires:
- Per-CA evidence reviewed by an auditor independent of the owner.
- Per-CA re-test of the original failed control (proves the fix took).
- Per-CA verification recorded in register with auditor identity + date.

## Per-CA post-implementation review (PIR)

90 days after verification, a lightweight PIR confirms:
- Per-CA the fix is still in place (not reverted, not bypassed).
- Per-CA no related issues have appeared (regressions, side-effects).
- Per-CA process change is being followed (sampled).

If PIR fails → re-open finding with new severity.

## Per-CA dashboard

`/admin/grimba/corrective-actions`:
- Open CAs by SLA bucket (on-track / approaching / breached).
- Verified-but-pending-PIR list.
- CA closure rate by quarter.
- CAs that failed PIR (regression count).

## Per-CA escalation

- SLA breach for HIGH/CRITICAL CA → Audit Committee within 5 business days.
- PIR failure → automatic re-audit of related controls in next quarterly plan.

## Cross-references

Master plan: S1886. Sister: `docs/GRIMBANEWS_INTERNAL_AUDIT_FINDINGS_REGISTER.md` (S1885), `docs/GRIMBANEWS_INTERNAL_AUDIT_MANAGEMENT_REVIEW_CADENCE.md` (S1887 next).
