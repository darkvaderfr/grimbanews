# GrimbaNews — Residual Risk Scoring

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Vader
**Walks:** Mythos S1835 (residual-risk scoring) deferred → partial
**Gating dependency:** Inherent risk scoring (Wave SUB-41) + control effectiveness scoring (Wave SUB-42).

## Methodology

Per-risk residual score:

**Residual Risk = Inherent Risk × (1 - Σ(Control Effectiveness × Control Coverage) / Max)**

Where:
- Inherent risk = Likelihood × Impact (per SUB-41).
- Control effectiveness = per-control 1-5 score (per SUB-42).
- Control coverage = % of inherent-risk vectors this control addresses.

Simplified: if Inherent=20 (Critical) + Control set fully effective + comprehensive coverage → Residual ≤ 5 (Low).

## Per-residual-risk decisions

- **Residual Low (1-6):** accept; per-year review.
- **Residual Medium (7-12):** monitor; per-quarter review; consider additional controls.
- **Residual High (13-19):** Vader sign-off required; mandatory additional treatment.
- **Residual Critical (20-25):** ban operation OR avoid scope OR transfer.

## Per-risk Vader sign-off

Per Critical or High residual: per-risk Vader written sign-off acknowledging acceptance OR mandating additional treatment.

## Cross-references

Master plan: S1835. Sister: `docs/GRIMBANEWS_INHERENT_RISK_SCORING_PLAN.md`, `docs/GRIMBANEWS_CONTROL_EFFECTIVENESS_SCORING.md`.
