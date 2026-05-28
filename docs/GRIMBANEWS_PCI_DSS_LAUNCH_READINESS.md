# GrimbaNews — PCI DSS Launch Readiness Checklist

**Status:** checklist v0
**Owner:** Sara Chen (CISO) + Vader
**Walks:** Mythos S1849 (PCI DSS launch readiness) deferred → partial
**Gating dependency:** Payment processor integration live (Stripe checkout) + S1841-S1848 (PCI infrastructure docs).

## SAQ-A launch readiness checklist

- [ ] Stripe Checkout integration live + tested.
- [ ] Card-data flow confirmed: no PAN/CVV touches GrimbaNews.
- [ ] Card-data-flow diagram approved (Wave SUB-44).
- [ ] Network segmentation reviewed (Wave SUB-44).
- [ ] SAQ-A selection rationale documented (Wave SUB-44).
- [ ] Quarterly ASV scan vendor engaged (Wave SUB-45).
- [ ] Annual pen-test vendor selected (Wave SUB-45).
- [ ] AoC procedure documented (Wave SUB-46).
- [ ] AoC signing authority assigned (Vader).
- [ ] PCI evidence vault provisioned (per Wave SUB-35 SOC 2 vault pattern).
- [ ] Per-quarter ASV scan first-run completed.
- [ ] Vader sign-off on launch readiness.

## Per-launch decision

Sara + Vader review checklist. All items checked = ready for paid-tier go-live with PCI compliance.

## Cross-references

Master plan: S1849. Sister: `docs/GRIMBANEWS_PCI_DSS_ATTESTATION_OF_COMPLIANCE.md`, `docs/GRIMBANEWS_PCI_DSS_SAQ_SELECTION.md`.
