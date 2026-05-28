# GrimbaNews — Vendor Risk-Tier Classification

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Lucy Leai (Strategy)
**Walks:** Mythos S1872 (vendor risk-tier classification — critical / high / medium / low) deferred → partial
**Gating dependency:** Vendor register (Wave LLL).

## Risk-tier methodology

Per-vendor risk tier based on:
- Data exposure: PII access / financial / health.
- System criticality: prod-impacting / non-impacting.
- Substitutability: easy-swap / hard-swap.
- SOC 2 / ISO posture: attested / unattested.

## Per-tier definitions

- **Critical:** PII + prod-impacting + hard-swap. Examples: Stripe, AWS, primary hosting VPS, DB.
- **High:** PII OR prod-impacting + hard-swap. Examples: SES, Mailgun, Sentry (when added).
- **Medium:** Limited PII + non-prod-impacting OR easy-swap. Examples: Anthropic (NobuAI), OpenAI, DeepL, GitHub.
- **Low:** No PII + non-prod-impacting + easy-swap. Examples: NPM packages, Composer packages, dev tools.

## Per-tier review cadence

- Critical: monthly.
- High: quarterly.
- Medium: semi-annually.
- Low: annually.

## Per-tier security-question depth

- Critical: full security questionnaire + SOC 2 attestation + DPA + SCC.
- High: security questionnaire + attestation + DPA.
- Medium: brief questionnaire + DPA.
- Low: per-license review only.

## Cross-references

Master plan: S1872. Sister: `docs/GRIMBANEWS_VENDOR_REGISTER.md` (Wave LLL), `docs/GRIMBANEWS_SOC2_AUDIT_WEEK3_INCIDENT_VENDOR.md`.
