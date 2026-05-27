# GrimbaNews — Per-Publisher Tier Subscription Bundling

**Status:** plan v0
**Owner:** Ray Dalio (CFO) + Lucy Leai (Strategy)
**Walks:** Mythos S1672 (per-publisher-tier subscription bundling) deferred → partial
**Gating dependency:** Stripe subscriptions + partnership program with bundling-capable partners.

## Why this exists

Multi-publisher bundling reduces reader subscription fatigue (one subscription, multiple sources). NYT/WaPo "All Access" pattern adapted for partnered newsrooms.

## v1 design

Bundle tier (€9.99/mo, vs €5/mo GrimbaNews-only):

- All GrimbaNews features
- + Partner-publisher direct subscriptions (e.g. Partner1 + Partner2)
- Stripe revenue share: 70% to partner (split per partner contract), 30% to GrimbaNews

## Per-partner bundle config

```
partner_bundles:
  partner_id | publisher_subscription_price | revenue_share_pct
   | bundle_eligible (bool) | bundle_start_date
```

## Reader UX

- `/abonnement` page surfaces bundle vs GrimbaNews-only.
- Bundle CTA: "Lisez aussi {partner1}, {partner2} avec un seul abonnement."
- Per-partner access info post-subscription.

## Cross-references

Master plan: S1672. Sister: `docs/GRIMBANEWS_PER_LOCALE_SUBSCRIPTION_PRICING_DESIGN.md` (Wave WWW), `docs/GRIMBANEWS_AFFILIATE_REFERRAL_PROGRAM_PLAN.md`.
