# GrimbaNews — Control Effectiveness Scoring

**Status:** plan v0
**Owner:** Sara Chen (CISO) + per-control owner
**Walks:** Mythos S1834 (control-effectiveness scoring) deferred → partial
**Gating dependency:** Statement of Applicability + control inventory (Wave SUB-39).

## Scoring methodology

Per-control quarterly assessment:

- **5: Highly effective** — control fully implemented, evidence comprehensive, no incidents.
- **4: Effective** — implemented, minor gaps, no incidents.
- **3: Partially effective** — implemented, evidence gaps OR minor incidents.
- **2: Marginally effective** — partial implementation OR multiple incidents.
- **1: Ineffective** — not implemented OR major incident with control failure.

## Per-control scoring inputs

- Per-control evidence completeness.
- Per-control incident occurrence over period.
- Per-control internal-audit findings.
- Per-control external-audit findings.
- Per-control owner self-assessment.

## Per-quarter dashboard

`/admin/grimba/control-effectiveness`:
- Per-control current score + trend (4-quarter rolling).
- Per-tier score distribution.
- Per-control "needs attention" flag if score drops 2+ in one quarter.

## Per-control improvement plans

Per Score ≤ 2: mandatory per-quarter improvement plan with Vader review.

## Cross-references

Master plan: S1834. Sister: `docs/GRIMBANEWS_INHERENT_RISK_SCORING_PLAN.md`, `docs/GRIMBANEWS_ISO27001_INTERNAL_AUDIT_PLAN.md`.
