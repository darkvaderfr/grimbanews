# GrimbaNews — External Audit Remediation

**Status:** plan v0
**Owner:** Sara Chen (CISO) + remediation owners (per finding)
**Walks:** Mythos S1896 (external-audit remediation) deferred → partial
**Gating dependency:** External-audit findings response (Wave SUB-56, S1895) acknowledged + remediation owners assigned.

## Why this exists

Acknowledged findings in the management response (S1895) are commitments. Failing to land them on time leaves us with two bad outcomes: (a) the auditor cites uncorrected exceptions in the bridge letter or Year-2 report, eroding customer trust, and (b) we drift into the same finding being repeated next cycle. This doc operationalizes the work between "response submitted" and "finding closed."

## Per-remediation tracking

Each acknowledged remediation gets:
- Per-remediation entry in the corrective-action register (per SUB-54, S1886).
- Per-remediation owner with explicit ownership transfer if the original owner changes role.
- Per-remediation milestones (typically: design → implement → test → verify).
- Per-remediation weekly status update during the SLA window.
- Per-remediation evidence pack assembled in parallel (so verification is fast).

## Per-SLA matrix

| Severity | Target close | Escalation if missed |
|---|---|---|
| CRITICAL | 30 days from report | CEO + Audit Committee + auditor notified. |
| HIGH | 60 days | Audit Committee Chair notified. |
| MEDIUM | 90 days | Tracked in quarterly committee review. |
| LOW | 180 days | Tracked annually. |

## Per-evidence pack composition

For each remediation, the verification pack contains:
- The original finding text + management response.
- The remediation plan (what we said we'd do).
- Evidence of execution: PR / config diff / runbook update / training record.
- Re-test of the original failing control showing PASS.
- Sign-off from a verifier independent of the remediation owner.

## Per-bridge-letter handling

If a remediation is still open when an interim attestation period closes, the bridge letter explicitly notes:
- Per-finding the remediation status as of the bridge date.
- Per-finding the expected close date.
- Per-finding compensating controls in effect during remediation.

This is healthier than letting the bridge be silent — auditors and customers value transparency over a polished but stale snapshot.

## Per-Year-2 carry-forward

Any finding still open at the start of Year-2 audit:
- Per-finding flagged as repeat-risk in Year-2 scope.
- Per-finding subject to detailed retest in Year-2 fieldwork.
- Per-finding escalated to Audit Committee for risk-acceptance review if closure is infeasible.

## Cross-references

Master plan: S1896. Sister: `docs/GRIMBANEWS_EXTERNAL_AUDIT_FINDINGS_RESPONSE.md` (SUB-56, S1895), `docs/GRIMBANEWS_EXTERNAL_AUDIT_REPORT_RECEIPT.md` (S1897 next), `docs/GRIMBANEWS_INTERNAL_AUDIT_CORRECTIVE_ACTION_TRACKING.md` (SUB-54, S1886).
