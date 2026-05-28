# GrimbaNews — GDPR DPIA: Homepage Personalization + For-You

**Status:** plan v0
**Owner:** Sara Chen (CISO) + counsel + Lisa Nguyen (data)
**Walks:** Mythos S1852 (GDPR DPIA — homepage personalization) deferred → partial
**Gating dependency:** No formal DPIA; technical surrogate `app/Support/GrimbaForYou` cookie-only profile (no member-row personalization, no profile graph) keeps DPIA risk low.

## DPIA scope

Per GDPR Art. 35: DPIA required when processing likely to result in high risk to data subjects. Personalization typically triggers DPIA threshold.

## Current state (low DPIA risk)

- Personalization: cookie-only (cookie `grimba_read` stores read article IDs).
- No server-side per-member profile.
- No automated decision-making with legal/significant effects.
- No special-category data (race, health, biometric, etc.).

**Per current state: DPIA not legally required.** Operator-side discretionary DPIA still recommended for transparency.

## DPIA structure

For future state with member-row personalization:

1. **Necessity + proportionality:** why personalize? what's the legal basis (legitimate interest)?
2. **Data flow:** what data → where stored → who accesses → who shares.
3. **Risks to data subjects:** profiling discrimination, surveillance feel, opt-out friction.
4. **Mitigations:** anonymization, opt-out friction-free, transparency.
5. **Residual risk:** acceptable per Art. 35.
6. **DPO consultation:** if DPO exists (S1857 below).

## Per-DPIA archive

Per-DPIA archived in ISMS evidence vault. Per-significant-change DPIA re-execution.

## Cross-references

Master plan: S1852. Sister: `docs/GRIMBANEWS_GDPR_ROPA.md` (Wave LLL), `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md` (Wave LLL).
