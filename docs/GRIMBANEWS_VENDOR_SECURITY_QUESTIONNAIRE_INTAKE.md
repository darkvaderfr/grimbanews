# GrimbaNews — Vendor Security Questionnaire Intake

**Status:** plan v0
**Owner:** Sara Chen (CISO) + per-vendor relationship owner
**Walks:** Mythos S1874 (vendor security-questionnaire intake) deferred → partial
**Gating dependency:** Vendor risk-tier classification (Wave SUB-50 sister).

## Per-tier questionnaire template

### Critical-tier questionnaire (~80 questions)

Per CAIQ Lite (Cloud Security Alliance):
- Per-control coverage assessment.
- Per-data-flow review.
- Per-incident response history.
- Per-SOC 2 attestation copy.
- Per-disaster-recovery + business-continuity plans.
- Per-sub-processor inventory.
- Per-employee background-check policy.

### High-tier questionnaire (~50 questions)

Subset of CAIQ-Lite focused on:
- Access control + encryption.
- Per-incident response.
- Per-DPA + SCC.

### Medium-tier questionnaire (~20 questions)

- Per-encryption-at-rest + in-transit.
- Per-data retention.
- Per-DPA.

### Low-tier questionnaire (~5 questions)

- Per-license.
- Per-known-vulnerability disclosure.
- Per-update cadence.

## Per-onboarding workflow

1. Per-tier classification (per SUB-50 doc).
2. Per-tier questionnaire sent.
3. Per-vendor response review.
4. Per-vendor risk score computed.
5. Per-vendor onboarding approved / rejected.

## Per-vendor renewal

- Critical: annual re-questionnaire.
- High: bi-annual.
- Medium: tri-annual.
- Low: per-incident only.

## Cross-references

Master plan: S1874. Sister: `docs/GRIMBANEWS_VENDOR_RISK_TIER_CLASSIFICATION.md`, `docs/GRIMBANEWS_VENDOR_REGISTER.md` (Wave LLL).
