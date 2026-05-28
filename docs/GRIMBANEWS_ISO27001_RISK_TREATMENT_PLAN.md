# GrimbaNews — ISO 27001 Risk Treatment Plan

**Status:** plan v0
**Owner:** Sara Chen (CISO) + counsel
**Walks:** Mythos S1823 (ISO 27001 risk-treatment plan) deferred → partial
**Gating dependency:** Risk-assessment methodology (Wave LLL `docs/GRIMBANEWS_RISK_ASSESSMENT_METHODOLOGY.md`) + Statement of Applicability (Wave SUB-39).

## What this is

Per-risk identified in risk assessment:
- Treatment decision: Accept / Mitigate / Transfer / Avoid.
- If Mitigate: per-control map + per-control owner + per-control timeline.
- If Transfer: per-vendor / per-insurance reference.
- Per-risk re-assessment cadence.

## Per-risk template

```
| Risk ID | Description | Inherent risk | Treatment | Per-control IDs | Owner | Residual risk |
|---|---|---|---|---|---|---|
| R-001 | DB backup loss | High | Mitigate | A.8.13, A.8.14, Wave LLL backup encryption | Larry Ellison | Low |
| R-002 | Vendor sub-processor breach | Medium | Mitigate + Transfer | Wave LLL vendor register; cyber insurance | Sara Chen | Low |
| ... | ... | ... | ... | ... | ... | ... |
```

## Per-quarter risk review

- Per-quarter: Sara reviews risk register + treatment status.
- Per-incident: emergency risk re-assessment.

## Per-risk-tier cadence

- High inherent risk: monthly review.
- Medium: quarterly.
- Low: annually.

## Cross-references

Master plan: S1823. Sister: `docs/GRIMBANEWS_RISK_ASSESSMENT_METHODOLOGY.md` (Wave LLL), `docs/GRIMBANEWS_ISO27001_STATEMENT_OF_APPLICABILITY.md`.
