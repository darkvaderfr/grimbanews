# GrimbaNews — Risk Treatment Decisions (Avoid / Mitigate / Transfer / Accept)

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Vader + counsel
**Walks:** Mythos S1836 (risk-treatment decisions) deferred → partial
**Gating dependency:** Inherent + Residual risk scoring (Wave SUB-41 + SUB-42).

## Per-treatment decision tree

For each risk:

1. **Can the activity be avoided entirely?** (don't enter market, don't onboard customer-class, etc.) → **Avoid**.
2. **Can the risk be reduced via controls?** → **Mitigate** + per-control roadmap.
3. **Can the risk be transferred to a vendor/insurance?** → **Transfer** + per-vendor / per-policy.
4. **Is residual risk acceptable to Vader?** → **Accept** + per-Vader sign-off.

## Per-decision template

```
| Risk ID | Decision | Owner | Rationale | Per-control / per-vendor / per-policy ref | Sign-off date |
|---|---|---|---|---|---|
| R-001 | Mitigate | Larry | High inherent + cost-effective controls | A.8.13 backup encryption | 2026-MM-DD |
| R-007 | Transfer | Sara | Cyber-insurance covers gap cost-effectively | Aviva policy AB-12345 | 2026-MM-DD |
| R-019 | Accept | Vader | Residual Low + insufficient ROI on additional controls | n/a | 2026-MM-DD |
| R-023 | Avoid | Vader | High residual + ethical concerns | n/a | 2026-MM-DD |
| ... | ... | ... | ... | ... | ... |
```

## Per-decision audit trail

Per-treatment-decision logged in risk register with timestamp + decision authority + rationale.

## Per-quarter re-review

Per-quarter: Sara reviews per-treatment decisions for currency.

## Cross-references

Master plan: S1836. Sister: `docs/GRIMBANEWS_RESIDUAL_RISK_SCORING.md`, `docs/GRIMBANEWS_ISO27001_RISK_TREATMENT_PLAN.md`.
