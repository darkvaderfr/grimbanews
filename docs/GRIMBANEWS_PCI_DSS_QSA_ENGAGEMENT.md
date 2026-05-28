# GrimbaNews — PCI DSS QSA Engagement (If Level 1)

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Vader + counsel
**Walks:** Mythos S1847 (PCI DSS QSA engagement) deferred → partial
**Gating dependency:** Only required if GrimbaNews reaches Level 1 (≥ 6M transactions / year for any one card brand). SAQ-A merchants don't need QSA.

## Threshold review

PCI levels for merchants:
- **Level 1:** ≥ 6M transactions/year per any brand. Mandatory QSA.
- **Level 2:** 1M-6M. SAQ-D OR QSA.
- **Level 3:** 20K-1M e-commerce. SAQ.
- **Level 4:** < 20K. SAQ.

GrimbaNews current state: ~0 paying subscribers (pre-launch). Will be Level 4 at launch + likely Level 3 within 12 months at expected growth.

## Per-Level-1 QSA selection (future)

If/when crossing 6M threshold:
- **Coalfire:** large-enterprise QSA, premier rep.
- **Sysnet:** SaaS-friendly mid-market QSA.
- **NCC Group:** also offers QSA practice.
- **Trustwave:** common SaaS QSA.

Per-QSA engagement: ~$80-200k annually for Level 1 attestation + per-quarter scans + per-year pen test.

## Per-engagement timeline

- Q1: scoping + pre-assessment.
- Q2-Q3: field-work + evidence collection.
- Q4: Report on Compliance (ROC) issued.

## Per-engagement budget gate

Ray Dalio approves per-Level-1 engagement budget.

## Cross-references

Master plan: S1847. Sister: `docs/GRIMBANEWS_PCI_DSS_SCOPE_STATEMENT.md` (Wave LLL), `docs/GRIMBANEWS_PCI_DSS_SAQ_SELECTION.md`.
