# GrimbaNews — PCI DSS Card Data Flow Diagram

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Stripe partnership lead
**Walks:** Mythos S1843 (PCI DSS card-data-flow diagram) deferred → partial
**Gating dependency:** Same as S1842; SAQ-A scope keeps CDE out of GrimbaNews.

## Current card-data flow (SAQ-A scope)

```
[Reader browser]
    ↓ subscription click → grimbanews.com/abonnement
[GrimbaNews /abonnement page]
    ↓ embeds Stripe Checkout iframe (Stripe.js hosted)
[Stripe.js iframe in browser]
    ↓ reader inputs PAN/CVV in Stripe-hosted iframe
[Stripe servers (CDE — outside GrimbaNews scope)]
    ↓ tokenize + charge
[Stripe webhook]
    ↓ POST subscription.created → grimbanews.com/api/stripe/webhook
[GrimbaNews server]
    - Stores: stripe_customer_id, stripe_subscription_id, plan_id, amount.
    - Never stores: PAN, CVV, expiration, billing address (Stripe holds).
```

## Per-data-element matrix

| Data | Where stored | Encrypted? | Retention |
|---|---|---|---|
| PAN | Stripe (not GrimbaNews) | Stripe-managed | Stripe policy |
| CVV | Stripe iframe transient | Never stored | Per transaction |
| Stripe customer ID | GrimbaNews `members.stripe_customer_id` | TLS-in-transit | Account life + 30d |
| Subscription metadata | GrimbaNews `member_subscriptions` | TLS-in-transit | Account life + 30d |
| Charge amount | GrimbaNews `member_subscription_history` | TLS-in-transit | 7 years (tax compliance) |

## Per-quarter flow review

Per-quarter: Sara reviews data flow + confirms no scope drift.

## Cross-references

Master plan: S1843. Sister: `docs/GRIMBANEWS_PCI_DSS_NETWORK_SEGMENTATION.md`.
