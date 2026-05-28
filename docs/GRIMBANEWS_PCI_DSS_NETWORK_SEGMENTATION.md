# GrimbaNews — PCI DSS Network Segmentation Diagram

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Jacob Lee (DevOps) + Stripe partnership lead
**Walks:** Mythos S1842 (PCI DSS network segmentation) deferred → partial
**Gating dependency:** No CDE (Cardholder Data Environment) exists today; Stripe-hosted checkout means we're SAQ-A scope. Diagram + segmentation gates on payment-flow shape.

## Current state (no CDE)

GrimbaNews uses Stripe Checkout (hosted page). Cardholder data never touches GrimbaNews servers:
- Reader → Stripe Checkout iframe → Stripe processor.
- Stripe webhook → GrimbaNews `subscription.created` event.
- GrimbaNews stores: customer Stripe ID + subscription metadata.
- GrimbaNews never stores: PAN, CVV, expiration.

**Per current state: PCI scope is SAQ-A (lowest tier; ~22 questions vs SAQ-D ~329 questions).**

## Future-state segmentation (if direct payment processing enters scope)

If GrimbaNews ever processes payments directly (not planned):

```
[Internet] → [WAF] → [DMZ] → [CDE Network]
                              └─ Payment app (PAN/CVV transit)
                              └─ Payment DB (tokenized only)
                              └─ Logging (out-of-band)

[CDE Network] ↮ no direct connection to ↮ [Non-CDE Network]
                                          └─ Editorial app
                                          └─ Reader DB
                                          └─ Admin DB
```

Per-zone firewall rules + per-zone audit logging + per-zone access control.

## Per-zone access matrix

If CDE ever exists:
- CDE access: limited to payment-domain engineers only (smallest possible group).
- Non-CDE staff: zero CDE access; physical separation.
- Per-CDE log retention: 1 year minimum per PCI DSS 10.5.

## Cross-references

Master plan: S1842. Sister: `docs/GRIMBANEWS_PCI_DSS_SCOPE_STATEMENT.md` (Wave LLL).
