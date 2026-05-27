# GrimbaNews — API Usage Dashboard Surrogate Plan

**Sprint ID:** S1253
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API usage dashboard`
**Walk wave:** CCCC

## Gating dependency

A per-key usage dashboard needs:

- API key issuance (gates on S1231)
- Per-request ledger (gates on S1254 billing meter)
- Aggregator (per-day / per-week / per-endpoint roll-up)
- Customer-facing UI (gates on the same multi-tenant auth as S1192-S1200)

None of these ship today.

## Surrogate-now infra

- **`grimba_automation_runs`** — internal job ledger pattern we'd mirror for API requests
- **`/admin/grimba`** cockpit — pattern for staff dashboards that the customer-facing dashboard would adapt
- **`GrimbaProviderCredits`** — daily counter pattern (NobuAI driver credit ledger) is the closest design analog
- **`tests/Feature/VaultAnalyticsDashboardTest`** — locks the contract for staff-side analytics; same shape for B2B

## Honest framing

Cannot ship before S1231 (key issuance) + S1254 (meter). The dashboard *design* is well-precedented internally; the missing pieces are billing infra, not data viz.

## Owners

- **Product:** Liam Smith — dashboard scope
- **Backend:** Rajesh Kumar — aggregator
- **Frontend:** Nina Patel — customer-portal UI
- **Data:** David Chen — aggregation policy
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1253 row)
- Billing meter scope: S1254 (deferred)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
