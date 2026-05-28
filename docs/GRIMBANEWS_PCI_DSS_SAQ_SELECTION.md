# GrimbaNews — PCI DSS SAQ Selection (A / A-EP / D)

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Vader
**Walks:** Mythos S1844 (PCI DSS SAQ selection) deferred → partial
**Gating dependency:** Card data flow diagram (Wave SUB-44 sister).

## SAQ-A selection rationale

GrimbaNews qualifies for SAQ-A based on:
- All card processing fully outsourced to Stripe (PCI-DSS Level 1 service provider).
- Cardholder data never touches GrimbaNews servers.
- GrimbaNews uses Stripe Checkout (hosted by Stripe; not Stripe Elements which would be SAQ-A-EP).
- GrimbaNews does not redirect through any GrimbaNews-hosted payment page.

## SAQ-A requirements (22 controls)

Per Stripe SAQ-A guide:
1. PCI-DSS Level 1 service provider used (Stripe).
2. Annual SAQ-A self-attestation submitted to acquirer.
3. Per-quarter network vulnerability scan (Stripe-provided + GrimbaNews-side via Nessus / Qualys).
4. Per-quarter attestation of compliance signed.

## If SAQ-A-EP becomes applicable

If we ever ship Stripe Elements (embedded form fields, not Stripe Checkout iframe):
- Scope expands to SAQ-A-EP (139 controls).
- Per-rev-control evidence needed.
- Per-firewall network-segmentation may be needed.

## Per-decision Vader sign-off

If payment-flow architecture changes: per-change Vader + Sara sign-off + per-SAQ-tier re-evaluation.

## Cross-references

Master plan: S1844. Sister: `docs/GRIMBANEWS_PCI_DSS_CARD_DATA_FLOW.md`, `docs/GRIMBANEWS_PCI_DSS_SCOPE_STATEMENT.md` (Wave LLL).
