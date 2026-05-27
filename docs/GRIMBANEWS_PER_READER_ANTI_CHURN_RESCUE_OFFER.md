# GrimbaNews — Per-Reader Anti-Churn Rescue Offer

**Status:** plan v0
**Owner:** Ray Dalio (CFO) + Liam Smith (PM) + Lisa Nguyen (data)
**Walks:** Mythos S1723 (per-reader anti-churn rescue offer) deferred → partial
**Gating dependency:** Stripe subscription tier live (S1211).

## Why this exists

Per editorial-product industry: 30% of subscribers churn within 12 months. Targeted retention offers materially reduce this.

## Trigger conditions

1. Subscriber's engagement drops below 50% of their 90-day baseline.
2. Subscriber missed renewal payment.
3. Subscriber clicked cancel button (intercept).

## Rescue offer tiers

- **Tier 1 (engagement drop):** "We noticed you've been less active. Try our personalized digest for free for 30 days."
- **Tier 2 (payment-failed):** Update card prompt + 14-day grace period.
- **Tier 3 (clicked-cancel):** "Stay 3 months at 50% off" + brief survey of cancel reason.

## Cadence

- Per-trigger: one offer attempt per 90 days max.
- Per-tier: tracked in `subscriber_rescue_log` for measurement.

## Measurement

- Rescue success rate per-tier
- Lifetime-value impact of rescued subscribers
- Per-tier offer ROI

## Cross-references

Master plan: S1723. Sister: `docs/GRIMBANEWS_PER_LOCALE_SUBSCRIPTION_PRICING_DESIGN.md`, `docs/GRIMBANEWS_AB_PERSONALIZATION_FLEET_DESIGN.md`.
