# GrimbaNews — Contributor Analytics Dashboard Design

**Sprint ID:** S1458
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1451-s1460 — Contributor analytics dashboard`
**Walk wave:** BBBB

## Gating dependency

Per-contributor analytics dashboard needs:

- Per-author analytics layer (S1418, deferred — see `docs/GRIMBANEWS_AUTHOR_ANALYTICS_DASHBOARD_SCOPE.md`)
- Author byline shipped (already partial — Wave DDDD)
- Per-author article inventory query
- Per-author engagement aggregation (pageviews, reads, reading-time)
- Per-author correction-rate tracking (sister deferred surface, see S1457 walk)
- Per-contributor compensation ledger (S1456, deferred)
- A `/contributeurs/{slug}/tableau-de-bord` route

## Surrogate-now infra

- **`docs/GRIMBANEWS_AUTHOR_ANALYTICS_DASHBOARD_SCOPE.md`** — existing scope for per-author analytics
- **`<x-grimba-byline>`** — per-article attribution already shipped
- **Botble admin per-author article list** — operator-visible today

## Honest framing

This is largely a UI surface on top of an analytics layer that itself is deferred. Code lift modest once author analytics exist.

## Owners

- **Data Eng:** Benjamin Lee — per-author aggregation
- **Backend:** Rajesh Kumar — dashboard endpoint
- **Frontend:** Nina Patel — dashboard layout
- **Product:** Liam Smith — KPI surfacing
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1458 row)
- Author analytics scope: `docs/GRIMBANEWS_AUTHOR_ANALYTICS_DASHBOARD_SCOPE.md`
- Profile + verification: `docs/GRIMBANEWS_CONTRIBUTOR_PROFILE_VERIFICATION_DESIGN.md`
- Rate card: `docs/GRIMBANEWS_CONTRIBUTOR_RATE_CARD_DESIGN.md`
- 1099 / tax: `docs/GRIMBANEWS_CONTRIBUTOR_1099_TAX_REPORTING_DESIGN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
