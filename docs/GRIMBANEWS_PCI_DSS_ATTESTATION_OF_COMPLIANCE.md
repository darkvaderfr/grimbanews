# GrimbaNews — PCI DSS Attestation of Compliance (AoC)

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Vader (signing)
**Walks:** Mythos S1848 (PCI DSS Attestation of Compliance) deferred → partial
**Gating dependency:** SAQ-A self-attestation OR QSA-issued ROC (Level 1).

## SAQ-A AoC procedure

For SAQ-A scope (current GrimbaNews state):

1. Annual self-assessment using SAQ-A v4 questionnaire.
2. Per-question response + per-question evidence reference.
3. Per-question executive sign-off (Vader).
4. AoC form completed + signed (Vader + Sara).
5. Submitted to acquirer (Stripe relays to card brands).

## SAQ-A AoC contents

- Per-merchant identification.
- Per-card-brand summary (Visa, MC, Amex, Discover).
- Per-PCI-validation method (SAQ-A self).
- Per-control attestation table.
- Per-quarterly ASV scan attestation.
- Per-annual pen-test attestation.
- Sara + Vader signature + date.

## Per-year AoC submission

- Q4 each year: SAQ-A completed.
- Q4 each year: AoC submitted to Stripe acquirer relationship.

## AoC archive

Per-year AoC archived in PCI evidence vault (per Wave SUB-35 SOC 2 vault pattern).

## Cross-references

Master plan: S1848. Sister: `docs/GRIMBANEWS_PCI_DSS_SAQ_SELECTION.md`, `docs/GRIMBANEWS_PCI_DSS_ASV_SCAN_PLAN.md`.
