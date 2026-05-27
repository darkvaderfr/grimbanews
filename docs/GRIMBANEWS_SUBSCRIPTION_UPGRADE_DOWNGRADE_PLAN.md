# GrimbaNews — Subscription Upgrade / Downgrade Surrogate Plan

**Sprint ID:** S1266
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Subscription upgrade / downgrade`
**Walk wave:** CCCC

## Gating dependency

Plan change UI needs Stripe installed (S1261). Stripe handles proration and the underlying call (`POST /subscriptions/{id}` with new price_id); the UI is one button + one confirmation modal.

## Surrogate-now infra

- **`/account` placeholder** — ready slot for plan picker
- **Stripe Customer Portal** — out-of-the-box upgrade/downgrade UI; one redirect is the entire feature
- **`docs/GRIMBANEWS_API_QUOTA_TIERS_PLAN.md`** — defines the price ladder

## Honest framing

Effectively a one-line redirect if we use Stripe's hosted Customer Portal (Stripe handles confirmation + proration + tax). Custom in-app UX is a 2-3 day build on top of that.

## Owners

- **Product:** Liam Smith — UX decision (hosted portal vs custom)
- **Backend:** Rajesh Kumar — portal session endpoint
- **Customer success:** Emma Brown — change-rate analytics
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1266 row)
- Stripe install: `docs/GRIMBANEWS_PAID_TIER_STRIPE_INSTALL_SCOPE.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
