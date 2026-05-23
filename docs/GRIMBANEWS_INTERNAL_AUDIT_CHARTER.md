# GrimbaNews — Internal Audit Charter (v0 draft)

**Status:** charter v0 (ready for management sign-off)
**Owner:** Sara Chen (CISO) as proposed internal-audit lead
**Walks:** Mythos S1881 (internal-audit charter) deferred → partial
**Gating dependency:** Vader sign-off as top management + Iboga board ratification. Charter draft itself is operator-side and does not depend on external auditor or vendor.

## Why this exists

S1881 was honest-deferred as "operator-side governance doc." Drafting the charter is exactly that operator-side work — it doesn't need an external auditor, just management commitment. This document is the v0 charter that Vader + Iboga board ratifies.

## 1. Purpose

The Internal Audit function exists to provide independent, objective assurance on the design and operating effectiveness of GrimbaNews' security, compliance, operational, and editorial controls. It supports ISO 27001 Clause 9.2 (internal audit) and SOC 2 ongoing-monitoring requirements.

## 2. Authority

Internal Audit reports administratively to the Iboga Ventures founder (Vader) and functionally to a future Audit Committee (TBD per Iboga governance). Internal Audit has:

- Full access to all GrimbaNews systems, logs, code, documentation, and personnel for audit purposes.
- Authority to issue findings + recommendations.
- No authority to remediate (separation of duties — remediation is the relevant control owner's job).

## 3. Independence

To preserve independence:

- Internal Audit lead must not also be the **operational** owner of controls they audit. Sara Chen (CISO) currently owns both compliance design and the proposed audit role — this is a transitional compromise acceptable for a small org but flagged as a residual independence risk in S1888. Mitigation: high-risk audits are co-led by Vader or an external-engaged auditor.
- Engineering-level audit (Zen / Echo / Mnemo panel per `~/.claude/projects/-Users-vb-kaizen/memory/feedback_dream_team_audit.md`) is **separate** from this Internal Audit function — they audit code changes; this charter audits the broader ISMS.

## 4. Scope

In scope:

- Information security controls (per `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md`).
- GDPR processing activities (per `docs/GRIMBANEWS_GDPR_ROPA.md`).
- Vendor risk management (per `docs/GRIMBANEWS_VENDOR_REGISTER.md`).
- Backup + recovery (per `app/Console/Commands/GrimbaVerifyBackups.php` + `docs/GRIMBANEWS_RTO_RPO_DEFINITION.md` when shipped).
- Change management (per CLAUDE.md git policy).
- Incident response (per `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`).
- Operational scheduler health (per `app/Support/GrimbaAutomationMonitor.php`).
- Editorial policy adherence (per `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md` + classifier consistency).

Out of scope:

- Other Iboga product lines (each scopes its own internal audit).
- Financial audit (separate — Ray Dalio CFO + external accounting).

## 5. Annual audit plan structure

The annual plan covers 4 cycles per year, each focused on a control family:

- **Q1 — Security controls** (CC1-CC9 per SOC 2 + ISO 27001 Annex A.5-A.14)
- **Q2 — Privacy + GDPR** (RoPA refresh, consent log integrity, DSAR fulfilment evidence)
- **Q3 — Operational + DR** (scheduler health review, backup-verify cron sample audit, restore-drill exercise per S1945)
- **Q4 — Vendor + change-management** (vendor register refresh, DPA collection progress, deploy-evidence review)

Each cycle output: findings register + corrective-action list + management-review brief.

## 6. Working-paper template

Each audit cycle produces:

1. **Audit memo** (1 page) — scope, audit objective, period covered, auditor sign.
2. **Test results** (per control) — sample size, results, exceptions noted.
3. **Findings register** — issue, severity (informational / low / medium / high / critical), recommendation, owner, due date.
4. **Management response** — owner's planned remediation + target date.
5. **Closure verification** — audit re-test once owner says "fixed."

Working papers stored in `docs/internal-audit/YYYY-Q{1,2,3,4}/` (directory does not yet exist; create on first cycle).

## 7. Findings classification

| Severity | Definition |
|---|---|
| Critical | Material control failure threatening certification or causing breach |
| High | Significant gap requiring remediation within 30 days |
| Medium | Gap requiring remediation within current quarter |
| Low | Improvement opportunity; address within current sprint cycle |
| Informational | Observation; no remediation required |

Aligns with risk-tier ranges in `docs/GRIMBANEWS_RISK_ASSESSMENT_METHODOLOGY.md`.

## 8. Corrective-action tracking

All findings recorded in `docs/internal-audit/corrective-actions.md` (a single rolling ledger across cycles). Closed only after Internal Audit re-tests + signs off. SLA per severity per above table.

## 9. Management review

Quarterly summary brief to Vader + Iboga board after each cycle. Per ISO 27001 Clause 9.3 + S1828 (management review cadence).

## 10. Auditor competencies

Internal Audit lead must:

- Hold or pursue ISO 27001 Lead Auditor or equivalent certification (Sara Chen → CIPM/CIPP path noted on roster).
- Have access to relevant standards (ISO 27001, ISO 27005, SOC 2 TSC, GDPR).
- Be free of operational responsibility in the controls being audited where feasible.

## 11. Charter approval + revision

- Approved by: Vader (top management) at v1.0 ratification.
- Reviewed annually; revisions versioned + dated.
- This v0 is draft awaiting ratification.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1881 row; gates for S1882-S1890)
- ISMS scope: `docs/GRIMBANEWS_ISMS_SCOPE.md` (clause 7 RACI is the parent for who-can-audit-what)
- Sister compliance docs: `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`, `docs/GRIMBANEWS_VENDOR_REGISTER.md`, `docs/GRIMBANEWS_POLICY_LIBRARY_INDEX.md`, `docs/GRIMBANEWS_RISK_ASSESSMENT_METHODOLOGY.md`
- Engineering audit (separate): `~/.claude/projects/-Users-vb-kaizen/memory/feedback_dream_team_audit.md`
- Standards reference: ISO 27001 Clauses 9.2 (internal audit), 9.3 (management review); SOC 2 CC2.1, CC4.1
