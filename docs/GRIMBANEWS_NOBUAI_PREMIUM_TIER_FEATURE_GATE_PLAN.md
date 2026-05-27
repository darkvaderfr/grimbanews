# GrimbaNews — NobuAI Premium Tier Feature Gate Plan

**Status:** plan v0
**Owner:** Ray Dalio (CFO) on unit economics + Liam Smith (PM) + Lucy Leai (Strategy)
**Walks:** Mythos S1096 (NobuAI premium-tier feature gate) deferred → partial
**Gating dependency:** Stripe subscriptions (S1211) + paid tier launched.

## Free vs Premium feature matrix

| Feature | Free | Premium ($5/mo) |
|---|---|---|
| Browse articles | ✓ | ✓ |
| Cluster pages | ✓ | ✓ |
| NobuAI summaries | 1st 5/day | unlimited |
| NobuAI insights on clusters | 3/month | unlimited |
| Multi-step research mode (Wave AABB) | 3/month | unlimited |
| Counterargument mode (Wave AABB) | 1/article/month | unlimited |
| Vault / coffre | up to 50 saves | unlimited |
| Saved-search alerts | 1 search | up to 10 |
| Newsletter digests | weekly | daily + per-topic |
| API access | n/a | n/a (separate B2B) |
| Ad-free reading | no | yes |

## Implementation

`GrimbaSubscriptionGate::canUse('feature', $member)`:
1. Returns true if member has active premium subscription.
2. Returns true if member has not hit the free-tier quota.
3. Otherwise returns false; renders a subscription nudge.

## Subscription nudge UX

When feature gated:
- Inline modal: "Vous avez utilisé vos N réponses gratuites ce mois. Continuez avec Premium pour 5€/mois — annulable à tout moment."
- Per-feature contextual messaging.
- Stripe checkout deep-link.

## Schema (gates on Stripe + Vader migration approval)

```
member_subscriptions:
  member_id | tier (free|premium) | active | started_at | renewed_at | canceled_at
  | stripe_customer_id | stripe_subscription_id
member_feature_usage:
  member_id | feature | usage_count_this_month | reset_at
```

## Pricing review

Ray Dalio reviews per-tier pricing quarterly against:
- Conversion rate per nudge
- Churn rate
- NobuAI per-driver cost trends
- Competitive benchmarks (Ground News $99/year, Reuters $0)

## Cross-references

Master plan: S1095, S1096. Sister: `docs/GRIMBANEWS_PER_LOCALE_SUBSCRIPTION_PRICING_DESIGN.md` (Wave WWW), `docs/GRIMBANEWS_AB_PERSONALIZATION_FLEET_DESIGN.md`.
