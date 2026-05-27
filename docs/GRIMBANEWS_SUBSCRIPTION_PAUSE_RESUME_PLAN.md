# GrimbaNews — Subscription Pause / Resume Surrogate Plan

**Sprint ID:** S1267
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Subscription pause / resume`
**Walk wave:** CCCC

## Gating dependency

Pause/resume needs:

- Stripe install (S1261)
- Entitlement layer that respects `pause_collection: { behavior: 'mark_uncollectible' }` and gates feature access during pause
- Optional auto-resume date (Stripe-native)
- Customer-portal toggle or in-app modal

## Surrogate-now infra

- **Stripe Customer Portal** — exposes pause toggle if enabled in portal config
- **Stripe API** — `subscription.pause_collection` field handles the contract; webhook drives entitlement state
- **`docs/GRIMBANEWS_SUBSCRIPTION_UPGRADE_DOWNGRADE_PLAN.md`** — same UX surface

## Honest framing

Common churn-rescue lever — pausing instead of cancelling cuts ~15-30% of cancellations per industry benchmarks. Cheap to ship once S1261 lands; useful retention tool. Pairs with cancellation flow S1268.

## Owners

- **Product:** Liam Smith — pause UX (max duration policy)
- **Customer success:** Emma Brown — pause-rescue offer playbook
- **Backend:** Rajesh Kumar — webhook handling + entitlement flag
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1267 row)
- Cancellation flow: S1268 (deferred)
- Stripe install: `docs/GRIMBANEWS_PAID_TIER_STRIPE_INSTALL_SCOPE.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
