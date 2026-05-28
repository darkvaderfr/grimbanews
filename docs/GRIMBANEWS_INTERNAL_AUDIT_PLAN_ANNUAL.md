# GrimbaNews — Internal Audit Plan (Annual)

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Audit Committee
**Walks:** Mythos S1883 (internal-audit annual plan) deferred → partial
**Gating dependency:** Internal audit team composition (Wave SUB-52, S1882) + risk register.

## Why this exists

ISO 27001 clause 9.2.2 requires the organization to "plan, establish, implement and maintain an audit programme(s), including the frequency, methods, responsibilities, planning requirements and reporting." SOC 2 CC4.1 requires ongoing evaluations. The annual plan operationalizes both.

## Risk-based audit selection methodology

Audits selected based on a heat-map score:
- Per-control criticality (HIGH / MEDIUM / LOW).
- Per-control time-since-last-audit (>12 months = higher priority).
- Per-control known issues (open findings, incidents in the year).
- Per-control change density (heavily-modified controls audited more often).

Score = (criticality × 3) + (months_since_last × 1) + (open_findings × 5).
Top N by score until audit-day budget exhausted form the year's plan.

## Sample annual plan structure

| Quarter | Audit topic | Lead | Scope | Estimated days |
|---|---|---|---|---|
| Q1 | Access management (RBAC, MFA, joiner/mover/leaver) | Sara Chen | All production systems | 5 |
| Q1 | Change management (deployment, code review) | Liam Smith | Last 3 months of releases | 3 |
| Q2 | Incident response readiness (playbook + tabletop) | Sara Chen | IR plan + last 3 incidents | 4 |
| Q2 | Vendor risk management | Sara Chen | Critical-tier vendors | 3 |
| Q3 | Backup + DR (restore drill) | Lisa Nguyen | Critical data stores | 4 |
| Q3 | Encryption (at-rest, in-transit, key rotation) | Rajesh Kumar | All in-scope systems | 3 |
| Q4 | Privacy program (GDPR, DPIAs, DSAR fulfillment) | Sara Chen | All EU-data flows | 5 |
| Q4 | Year-end controls walkthrough (SOC 2 readiness) | Sara Chen | All in-scope controls | 5 |

## Per-audit deliverables

For each audit:
- Per-audit scope memo (signed by CISO + Audit Committee Chair).
- Per-audit working papers (filed per S1884 template).
- Per-audit findings register entries (per S1885).
- Per-audit closing memo with management response.

## Per-plan governance

- Annual plan approved by Audit Committee in December for following year.
- Mid-year adjustments require Audit Committee vote.
- Unplanned audits (incident-driven) added without revising the base plan.

## Per-year budget

- ~36 audit-days/year for Lead Internal Auditor.
- ~12 audit-days/year for rotating Technical Auditor.
- ~8 audit-days/year for Process Auditor.

## Cross-references

Master plan: S1883. Sister: `docs/GRIMBANEWS_INTERNAL_AUDIT_TEAM_COMPOSITION.md` (S1882), `docs/GRIMBANEWS_INTERNAL_AUDIT_WORKING_PAPER_TEMPLATE.md` (S1884 next), `docs/GRIMBANEWS_INTERNAL_AUDIT_FINDINGS_REGISTER.md` (S1885).
