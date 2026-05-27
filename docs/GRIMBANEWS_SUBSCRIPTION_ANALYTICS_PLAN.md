# GrimbaNews — Subscription Analytics Surrogate Plan

**Sprint ID:** S1270
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Subscription analytics`
**Walk wave:** CCCC

## Gating dependency

Subscription analytics (MRR, churn, ARPU, LTV, cohort retention) need:

- Stripe install (S1261)
- A subscriber ledger (or trust Stripe-as-source-of-truth + Stripe Sigma queries)
- A scheduled job that snapshots Stripe state into the local warehouse
- A dashboard surface

## Surrogate-now infra

- **Stripe Sigma** — out-of-box SQL warehouse for all Stripe data; queries can compute MRR/churn directly
- **`VaultAnalyticsDashboardTest`** — internal staff-analytics shape that the subscription dashboard would mirror
- **`coffre/export.csv`** — daily snapshot job pattern

## Honest framing

Cheap if we use Stripe Sigma; expensive if we self-host. Likely path: ship a `/admin/grimba/subscriptions` dashboard that calls Sigma at request-time + caches 1h. Same gate as the entire billing cluster.

## Owners

- **Strategy:** Ray Dalio — KPI definition (gross/net MRR, NRR, ARPU, LTV)
- **Finance:** Warren Buffett — reporting cadence
- **Data:** David Chen — Sigma queries + cohort builder
- **Frontend:** Nina Patel — dashboard UI
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1270 row)
- Stripe install: `docs/GRIMBANEWS_PAID_TIER_STRIPE_INSTALL_SCOPE.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
