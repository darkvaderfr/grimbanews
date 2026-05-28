# GrimbaNews — Internal Audit Findings Register

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Audit Committee
**Walks:** Mythos S1885 (internal-audit findings register) deferred → partial
**Gating dependency:** Working-paper template (Wave SUB-53, S1884) feeding findings into the register.

## Why this exists

A findings register is the single source of truth for "what did internal audit catch and where does each fix stand?" Without one, findings rot in individual working papers and management response promises evaporate. SOC 2 auditors specifically test for "tracking of identified issues through to resolution."

## Register schema (admin surface at `/admin/grimba/audit-findings`, pending Vader migration approval)

```
audit_findings:
  id | audit_id (FK working paper) | finding_ref (e.g., 2026-Q1-F03)
   | control_ref (SOC2/ISO mapping) | severity (LOW|MEDIUM|HIGH|CRITICAL)
   | description | root_cause | recommendation
   | remediation_owner | target_date | actual_close_date
   | status (OPEN|IN_PROGRESS|VERIFIED_CLOSED|RISK_ACCEPTED)
   | compensating_control_text | last_review_date | reviewer
```

## Per-severity SLA

| Severity | Target remediation | Escalation trigger |
|---|---|---|
| CRITICAL | 30 days | Day-1 to Audit Committee + CEO; daily standup until closed. |
| HIGH | 60 days | Day-7 to Audit Committee Chair; weekly status. |
| MEDIUM | 90 days | Monthly status in committee meeting. |
| LOW | 180 days | Reviewed at quarterly committee meeting. |

## Per-finding lifecycle

1. Per-finding logged from working paper closure.
2. Per-finding assigned remediation owner + target date.
3. Per-finding status updates required at SLA-defined cadence.
4. Per-finding closure requires evidence (re-test result, control re-implementation artifact).
5. Per-finding closure verified by an auditor independent of the remediation owner.
6. Per-finding closure-verification documented in register (date + verifier).

## Risk-acceptance path

Where remediation is infeasible (cost, technical debt, business decision):
- Per-finding risk-acceptance memo signed by CEO + CISO + Audit Committee Chair.
- Per-finding compensating control documented (what mitigates the residual risk).
- Per-finding annual re-evaluation in next audit cycle.

## Per-register dashboard (admin)

`/admin/grimba/audit-findings`:
- Open findings by severity (count + age).
- SLA-breached findings (highlighted red).
- Closure rate by quarter.
- Top 5 oldest open findings.
- Audit Committee export (PDF/CSV for quarterly meetings).

## Per-register integrity

- Per-entry append-only; status changes logged with timestamp + actor.
- Per-entry retention: indefinite while system operates (auditors look back across years).
- Per-entry access: Audit Committee + CISO read; only auditors write new entries.

## Cross-references

Master plan: S1885. Sister: `docs/GRIMBANEWS_INTERNAL_AUDIT_WORKING_PAPER_TEMPLATE.md` (S1884), `docs/GRIMBANEWS_INTERNAL_AUDIT_PLAN_ANNUAL.md` (S1883), `docs/GRIMBANEWS_INTERNAL_AUDIT_TEAM_COMPOSITION.md` (S1882).
