# GrimbaNews — API Billing Meter Surrogate Plan

**Sprint ID:** S1254
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API billing meter`
**Walk wave:** CCCC

## Gating dependency

A billing meter needs:

- API key issuance (S1231)
- A per-request ledger row (request_id, customer_id, endpoint, units, ts)
- A Stripe subscription per customer (gates on S1261 Stripe install)
- A reconciler that aggregates ledger → Stripe usage records (Stripe metered billing)
- Overage logic (gates on S1257)

## Surrogate-now infra

- **`GrimbaProviderCredits`** — internal cost ledger for NobuAI driver calls is the architectural template
- **`config/grimba_credits.php`** — per-driver pricing pattern translates to per-endpoint pricing
- **Stripe metered billing reference**: well-documented public API
- **`grimba_automation_runs`** — pattern for request-id idempotency we'd mirror

## Honest framing

This is the bottleneck for the entire B2B API tier. Cannot meter without S1231 keys; cannot bill without S1261 Stripe; cannot show usage without S1253. All four sit in the same dependency cluster — they ship as one fleet or none.

## Owners

- **Product:** Liam Smith — pricing model
- **Backend:** Rajesh Kumar — ledger schema + reconciler
- **Finance:** Warren Buffett — pricing approval + revenue rec policy
- **Strategy:** Ray Dalio — unit-economics review (LTV vs API call cost)
- **DBA:** Larry Ellison — ledger partitioning (high write rate)
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1254 row)
- Paid tier infra: S1261 (deferred)
- API quota tiers: S1256 (deferred)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
