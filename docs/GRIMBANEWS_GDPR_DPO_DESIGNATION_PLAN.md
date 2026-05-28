# GrimbaNews — GDPR DPO Designation Plan

**Status:** plan v0
**Owner:** Sara Chen (CISO) + counsel + Vader
**Walks:** Mythos S1857 (GDPR DPO designation) deferred → partial
**Gating dependency:** Operator-side decision; large-scale-special-category-data threshold not met today (GrimbaNews is news aggregation, not health / finance / biometric).

## DPO mandatory conditions (per GDPR Art. 37)

DPO required if any of:
- Public authority.
- Core activities involve regular and systematic monitoring on a large scale.
- Core activities involve large-scale processing of special-category data.

GrimbaNews:
- Not public authority.
- Reader monitoring: cookie-only profile (not "regular and systematic monitoring" by EDPB guidance).
- No special-category data (race, health, biometric, etc.) processed at scale.

**Per current state: DPO not legally required.** Voluntary DPO recommended for trust signaling.

## Voluntary DPO benefits

- Per-reader trust signal.
- Per-DSAR + per-complaint routing.
- Per-jurisdiction regulator-relationship management.
- Per-launch privacy-by-design oversight.

## DPO role options

1. **In-house DPO:** Sara Chen wears DPO hat (potential conflict: CISO + DPO).
2. **Outsourced DPO:** retained counsel firm provides DPO service (~€10-25k/year).
3. **Fractional DPO:** EU-based DPO consultancy fractional engagement.

## Per-DPO appointment formalities

- Per-DPO published contact email (dpo@grimbanews.com).
- Per-DPO regulator-notification (per-country DPA: CNIL FR, BfDI DE, etc.).
- Per-DPO public-facing role visible in privacy policy.

## Per-year DPO scope review

- Per-year: DPO scope + cost review.
- Per-significant-feature DPIA: DPO consultation.

## Cross-references

Master plan: S1857. Sister: `docs/GRIMBANEWS_GDPR_DPIA_HOMEPAGE_PERSONALIZATION.md`, `docs/GRIMBANEWS_GDPR_ROPA.md` (Wave LLL).
