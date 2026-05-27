# GrimbaNews — Gift Subscription (Pay-It-Forward) Design

**Sprint ID:** S1265
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Gift subscription`
**Walk wave:** BBBB

## Gating dependency

A pay-it-forward gift subscription needs:

- Stripe install (S1261, deferred — no `composer require stripe/`)
- Subscriber tier scaffolding (S1262 monthly + S1263 annual, both deferred)
- A `gifts` table (`from_member_id`, `to_email`, `redeemed_at`, `tier`, `code`)
- Email template for gift-code delivery
- A `/redeem/{code}` flow
- Tax / VAT handling per recipient region (S1269, deferred)

None ship. There is no billing infrastructure at all today.

## Surrogate-now infra

- **Existing "donate / support" framing on `/methodologie`** — sets the reader expectation that voluntary support is a path; today the path lands at `/contact`
- **`members` table** — Botble's member infra exists, ready to be extended with subscription columns
- **Referral landing concept** — sister plan in `GRIMBANEWS_AFFILIATE_REFERRAL_PROGRAM_PLAN.md` (different sprint, but covers the same "spread access" UX surface)

## Honest framing

Gift subscription is a downstream product of S1261-S1264 billing rail. Until Stripe is installed, "gift subscriptions" means manually invoiced ops favors.

## Owners

- **Product:** Liam Smith — gift-flow UX + redeem token format
- **Backend:** Rajesh Kumar — `gifts` table + Stripe Connect / Checkout link
- **Finance:** Warren Buffett (CFO) + Ray Dalio (OM CFO) — unit economics + VAT handling
- **Frontend:** Nina Patel — `/redeem/{code}` partial
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1265 row)
- Subscriber tier monthly: `docs/GRIMBANEWS_SUBSCRIBER_TIER_MONTHLY_DESIGN.md`
- Subscriber tier annual: `docs/GRIMBANEWS_SUBSCRIBER_TIER_ANNUAL_DESIGN.md`
- Existing pay-it-forward sister: `docs/GRIMBANEWS_GIFT_SUBSCRIPTION_PLAN.md` (different scope, partner gifting)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
