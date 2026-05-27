# GrimbaNews — Affiliate Referral Program

**Status:** plan v0
**Owner:** Ray Dalio (CFO) + Lucy Leai (Strategy) + Liam Smith (PM)
**Walks:** Mythos S1630 (affiliate referral program) deferred → partial
**Gating dependency:** Stripe subscriptions (S1211) + Stripe Connect for referral payouts.

## Why this exists

Per-reader unique referral code for sharing GrimbaNews. Referrer gets month-free reward per converted-and-retained subscriber. Lowest-cost acquisition channel for editorial products (NYT, FT, Substack all use).

## Schema

```
member_referrals:
  member_id PK | referral_code | total_referrals | converted_referrals | rewards_earned_months
referral_conversions:
  referrer_id | referred_member_id | converted_at | retention_check_30d (bool) | reward_applied (bool)
```

## Reward tiers

- 1 conversion + 30-day retention → 1 free month for referrer
- 5 conversions + 30-day retention each → 6 months free
- 10 conversions → 12 months free
- 25+ → contact Lucy for partner-tier

## Anti-fraud

- Referred member must be different IP + different device.
- 30-day retention check before reward applied.
- Operator review on bulk-referral patterns.

## Cross-references

Master plan: S1630. Sister: `docs/GRIMBANEWS_NOBUAI_PREMIUM_TIER_FEATURE_GATE_PLAN.md`, `docs/GRIMBANEWS_PER_LOCALE_SUBSCRIPTION_PRICING_DESIGN.md`.
