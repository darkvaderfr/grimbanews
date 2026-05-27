# GrimbaNews — Institutional License Per-Institution Analytics Design

**Sprint ID:** S1985
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1981-s1990 — Institutional license per-institution analytics`
**Walk wave:** BBBB

## Gating dependency

Per-institution analytics dashboard needs:

- Tier shipped (S1981, deferred)
- SSO or IP-allowlist scoping (S1983-S1984, deferred)
- Per-institution `institution_id` tagging on every API call + member action
- Aggregation views (DAU, MAU, top-topics, top-readers)
- Portal surfacing (depends on partner portal S1963 pattern)
- API analytics layer (S1188, deferred)

## Surrogate-now infra

- **`docs/GRIMBANEWS_PER_PARTNER_ANALYTICS_SCOPE.md`** — sibling per-partner analytics scope (same pattern)
- **`docs/GRIMBANEWS_API_PARTNER_ANALYTICS_PLAN.md`** — sibling plan
- **Nginx access logs** — current de-facto per-IP analytics (operator-only)

## Honest framing

Per-institution analytics reuses per-partner-analytics infrastructure. Schema additive on the same `api_calls` ledger.

## Owners

- **Data Eng:** Benjamin Lee — per-institution aggregation
- **Backend:** Rajesh Kumar — scoping middleware
- **Product:** Liam Smith — dashboard KPIs
- **Frontend:** Nina Patel — portal surface
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1985 row)
- Tier design: `docs/GRIMBANEWS_INSTITUTIONAL_LICENSE_TIER_DESIGN.md`
- Per-partner analytics: `docs/GRIMBANEWS_PER_PARTNER_ANALYTICS_SCOPE.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
