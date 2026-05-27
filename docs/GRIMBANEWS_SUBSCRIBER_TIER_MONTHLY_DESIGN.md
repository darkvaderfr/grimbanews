# GrimbaNews — Subscriber Tier (Monthly) Design

**Sprint ID:** S1262
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Subscriber tier (monthly)`
**Walk wave:** BBBB

## Gating dependency

A monthly subscriber tier needs:

- Stripe install (S1261, deferred)
- `subscriptions` table with `status`, `current_period_end`, `cancel_at`
- Stripe webhook listener (`invoice.payment_succeeded`, `customer.subscription.deleted`)
- Member entitlement check at request time (`hasActiveSubscription()`)
- Ad-suppression for subscribers (S1869 / S1882, both deferred awaiting tier)
- Member-only content gate

None ship today.

## Surrogate-now infra

- **All content free** — every article is free to all readers today; the surrogate is "implicit goodwill subscription" with no paywall
- **`members.subscribed_at`** — Botble's existing newsletter-sub flag; can be repurposed but is not billing-backed
- **Operator-side patron list** — early backers can be tracked in `docs/PATRON_LEDGER.md` or similar; out of repo today

## Honest framing

Monthly tier is the foundation that unlocks every other deferred billing sprint (gift / family / annual / etc.). It is a 2-week build (Stripe + webhook + entitlement). The reason it sits deferred is that the editorial product is not yet at the maturity Ray-CFO + Lucy-CEO have set as the gate for monetization activation.

## Owners

- **CEO:** Lucy Leai — go/no-go on monetization activation
- **CFO:** Warren Buffett + Ray Dalio — pricing + LTV / CAC model
- **Product:** Liam Smith — tier scope + paywall policy
- **Backend:** Rajesh Kumar — Stripe + webhook + entitlement
- **DBA:** Larry Ellison — schema + indexes for billing tables
- **CISO:** Sara Chen — PCI scope minimization
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1262 row)
- Annual sister tier: `docs/GRIMBANEWS_SUBSCRIBER_TIER_ANNUAL_DESIGN.md`
- Gift sub: `docs/GRIMBANEWS_GIFT_SUBSCRIPTION_DESIGN_S1265.md`
- Family plan: `docs/GRIMBANEWS_FAMILY_PLAN_MULTI_SEAT_DESIGN.md`
- PCI DSS gating: `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1841-s1850`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
