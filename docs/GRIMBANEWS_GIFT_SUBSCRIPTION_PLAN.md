# GrimbaNews — Gift Subscription Plan

**Status:** plan v0
**Owner:** Ray Dalio (CFO) + Liam Smith (PM)
**Walks:** Mythos S1631 (pay-it-forward gift subscription) deferred → partial
**Gating dependency:** Stripe subscriptions (S1211).

## Why this exists

Gifting a subscription removes friction for paid-tier conversion of price-sensitive readers. Common in editorial products.

## v1 design

`/cadeau` page allows logged-in subscriber to:
- Buy 1, 3, 6, or 12 months for recipient email
- Optional personal message
- Redemption code emailed to recipient
- Recipient creates account or logs in to redeem

## Schema

```
gift_subscriptions:
  id | gifter_member_id | recipient_email | months | redemption_code (unique)
   | paid_at | redeemed_at | redeemed_by_member_id | personal_message
```

## UX

- Gift card design (Steve cinematic standard)
- Per-month tier: €5 = 1 month, €25 = 6 months, €40 = 12 months
- Auto-renew off (gift expires after period)
- Recipient receives ceremonial email

## Cross-references

Master plan: S1631. Sister: `docs/GRIMBANEWS_AFFILIATE_REFERRAL_PROGRAM_PLAN.md`, `docs/GRIMBANEWS_NOBUAI_PREMIUM_TIER_FEATURE_GATE_PLAN.md`.
