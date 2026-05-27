# GrimbaNews — Partner Analytics Dashboard Design

**Sprint ID:** S1967
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1961-s1970 — Partner analytics dashboard`
**Walk wave:** BBBB

## Gating dependency

Partner analytics dashboard needs:

- OAuth + API issuance (S1182 + S1965, both deferred)
- Per-partner API analytics layer (S1188, deferred)
- A per-partner ledger of API calls (`api_calls`: `partner_id`, `endpoint`, `status`, `ms`, `created_at`)
- Aggregation views (daily / monthly, per-endpoint, per-status)
- Portal surfacing (S1963, deferred)
- Pre-existing per-partner analytics scope at `docs/GRIMBANEWS_PER_PARTNER_ANALYTICS_SCOPE.md`

## Surrogate-now infra

- **`docs/GRIMBANEWS_PER_PARTNER_ANALYTICS_SCOPE.md`** — already shipped surrogate
- **`docs/GRIMBANEWS_API_PARTNER_ANALYTICS_PLAN.md`** — already shipped sibling plan
- **Nginx access logs** — current de-facto per-IP API call log (operator-only)

## Honest framing

This row is partially covered by two earlier walks. This doc is the per-S1967 anchor that ties them into the new partner-program walk band.

## Owners

- **Data Eng:** Benjamin Lee — aggregation pipeline
- **Backend:** Rajesh Kumar — per-partner ledger + scoping
- **Product:** Liam Smith — dashboard KPIs
- **Frontend:** Nina Patel — portal surface
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1967 row)
- Existing scope: `docs/GRIMBANEWS_PER_PARTNER_ANALYTICS_SCOPE.md`
- Existing plan: `docs/GRIMBANEWS_API_PARTNER_ANALYTICS_PLAN.md`
- Portal: `docs/GRIMBANEWS_PARTNER_PORTAL_DESIGN_S1963.md`
- Tier design: `docs/GRIMBANEWS_PARTNER_PROGRAM_TIER_DESIGN_S1961.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
