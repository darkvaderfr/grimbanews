# GrimbaNews — Internal Audit Team Composition

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Lucy Leai (CEO sponsor)
**Walks:** Mythos S1882 (internal-audit team composition) deferred → partial
**Gating dependency:** Exec roster + Audit Committee approval.

## Why this exists

SOC 2 Trust Services Criterion CC4.1 requires the entity to "select, develop, and perform ongoing and/or separate evaluations to ascertain whether the components of internal control are present and functioning." ISO 27001 clause 9.2 requires internal audits at planned intervals. We need a named team with independence from the activities they audit.

## Per-role composition

| Role | Iboga Roster Member | Notes |
|---|---|---|
| Lead Internal Auditor | Sara Chen (CISO) | Independent of day-to-day engineering execution. Reports to CEO + Audit Committee, not engineering management. |
| Audit Committee Chair | Lucy Leai (CEO) | Receives findings; approves remediation plans. |
| Technical Auditor (rotating) | Lisa Nguyen / Rajesh Kumar | Backend engineering rep, rotated each audit so no one audits their own work. |
| Process Auditor | Liam Smith (PM) | Audits change management, deployment, intake processes. |
| Independent Observer (optional, year 2+) | External fractional advisor | Brought in for SOC 2 Type II year-over-year refresh. |

## Per-audit independence rule

**An auditor cannot audit a control they personally implemented or operate.** Rotation matrix maintained in `/admin/grimba/audit-rotation`:
- Per-control owner identified.
- Per-audit auditor assigned from non-owner pool.
- Per-quarter rotation enforced for technical auditor seat.

## Per-meeting cadence

- Audit Committee: quarterly + on-demand for material findings.
- Internal audit team: monthly working sessions during active audit windows; quiet between.

## Per-finding escalation path

1. Finding logged by auditor → CISO triage.
2. CISO + control owner agree on severity + remediation owner + due date.
3. Material findings (HIGH or CRITICAL) escalated to Audit Committee within 5 business days.
4. Quarterly committee meeting reviews open findings register + closure rate.

## Per-charter document (separate Wave)

A signed Internal Audit Charter approved by Lucy Leai will formalize:
- Authority (access to all systems + records).
- Independence (reporting line + budget protection).
- Scope (all ISO 27001 + SOC 2 in-scope controls).
- Standards (IIA International Standards for the Professional Practice of Internal Auditing as guidance).

## Cross-references

Master plan: S1882. Sister: `docs/GRIMBANEWS_INTERNAL_AUDIT_PLAN_ANNUAL.md` (S1883 next), `docs/GRIMBANEWS_INTERNAL_AUDIT_FINDINGS_REGISTER.md` (S1885).
