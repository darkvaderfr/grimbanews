# GrimbaNews — Internal Audit Management-Review Cadence

**Status:** plan v0
**Owner:** Lucy Leai (CEO) + Sara Chen (CISO)
**Walks:** Mythos S1887 (internal-audit management-review cadence) deferred → partial
**Gating dependency:** ISO 27001 management review (S1828) framework; findings register (SUB-53, S1885); corrective-action tracking (SUB-54, S1886).

## Why this exists

ISO 27001 clause 9.3 requires top management to review the ISMS at planned intervals. SOC 2 CC4.2 requires management to evaluate and communicate internal control deficiencies. Both standards expect this as a **standing meeting**, not an ad-hoc reaction.

## Per-cadence schedule

| Meeting | Frequency | Chair | Attendees |
|---|---|---|---|
| Management Review (ISMS) | Quarterly + annual deep-dive | Lucy Leai (CEO) | CISO, CTO-equivalent, Legal, Audit Committee Chair |
| Audit Committee | Quarterly | Audit Committee Chair (Lucy Leai initially) | CEO, CISO, Lead Internal Auditor, external observer (year 2+) |
| CISO standup with audit team | Monthly during active audits | Sara Chen | Internal audit team |

## Per-management-review agenda (ISO 27001 clause 9.3 inputs)

Required inputs for the meeting:
1. Status of actions from previous management reviews.
2. Changes in external/internal issues relevant to the ISMS (regulations, threat landscape, business strategy).
3. Information security performance, including:
   - Nonconformities + corrective actions.
   - Monitoring + measurement results.
   - Audit results (internal + external).
   - Fulfillment of information security objectives.
4. Feedback from interested parties (customers, regulators, vendors).
5. Risk assessment + risk treatment plan status.
6. Opportunities for continual improvement.

## Per-management-review agenda (outputs)

Required outputs documented in the meeting minutes:
1. Decisions related to continual improvement opportunities.
2. Any needs for changes to the ISMS.
3. Resource needs.

## Per-meeting artifacts

For each meeting:
- Per-meeting agenda circulated 5 business days in advance.
- Per-meeting input pack (findings register snapshot, CA dashboard, metric trends) circulated 3 days in advance.
- Per-meeting minutes drafted within 5 business days after.
- Per-meeting minutes approved at the next meeting.
- Per-meeting minutes archived in `/admin/grimba/management-reviews/YYYY-Qn/`.

## Per-decision tracking

Decisions become register entries (similar to findings):
- Per-decision owner + due date.
- Per-decision evidence of execution.
- Per-decision reviewed at next management review.

## Per-annual review

Q4 management review is a deeper session including:
- Annual audit plan approval for following year.
- ISMS scope review + update.
- Risk register full re-evaluation.
- Strategic objectives setting for following year.

## Cross-references

Master plan: S1887. Sister: `docs/GRIMBANEWS_INTERNAL_AUDIT_CORRECTIVE_ACTION_TRACKING.md` (S1886), `docs/GRIMBANEWS_INTERNAL_AUDIT_LAUNCH_READINESS.md` (S1889 next), `docs/GRIMBANEWS_ISO_27001_MANAGEMENT_REVIEW.md` (S1828, planned).
