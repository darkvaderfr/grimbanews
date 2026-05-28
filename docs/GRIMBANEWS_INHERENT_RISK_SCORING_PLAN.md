# GrimbaNews — Inherent Risk Scoring Plan

**Status:** plan v0
**Owner:** Sara Chen (CISO)
**Walks:** Mythos S1833 (inherent-risk scoring) deferred → partial
**Gating dependency:** Asset/threat/vuln/impact mapping (Wave SUB-41 sister).

## Scoring methodology

Per-risk-row in register:
- **Likelihood (1-5):** 1=rare, 5=near-certain.
- **Impact (1-5):** 1=negligible, 5=catastrophic.
- **Inherent risk score = Likelihood × Impact** (range 1-25).
- **Risk tier:** Low (1-6), Medium (7-12), High (13-19), Critical (20-25).

## Per-tier treatment defaults

- **Critical (20-25):** mandatory mitigation; per-month review.
- **High (13-19):** preferred mitigation; per-quarter review.
- **Medium (7-12):** mitigation if cost-effective; per-quarter review.
- **Low (1-6):** accept; per-year review.

## Per-risk re-scoring cadence

- Per-incident: per-affected-risk re-score.
- Per-control-change: per-affected-risk re-score.
- Per-quarter: full register review.

## Residual risk

After treatment:
- Residual likelihood × Residual impact.
- Per-risk Vader sign-off if Residual remains High or Critical.

## Cross-references

Master plan: S1833. Sister: `docs/GRIMBANEWS_ASSET_THREAT_VULN_IMPACT_MAPPING.md`, `docs/GRIMBANEWS_ISO27001_RISK_TREATMENT_PLAN.md`.
