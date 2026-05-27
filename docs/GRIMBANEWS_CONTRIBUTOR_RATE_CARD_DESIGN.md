# GrimbaNews — Contributor Rate Card Design

**Sprint ID:** S1453
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1451-s1460 — Contributor rate card`
**Walk wave:** BBBB

## Gating dependency

Contributor rate card needs:

- Editorial decision on payment model (per-word / per-article / per-page-view / hybrid)
- Per-tier rate matrix (newsroom-tier / freelance-tier / community-tier)
- Per-region adjustment (Antilles vs Paris vs NYC rates differ)
- A published rate card on `/contributeurs/tarifs` (FR) / `/contributors/rates` (EN)
- Per-locale catalog
- Tied to monetization activation (S1211, deferred)

## Surrogate-now infra

- **Operator-side spreadsheet** — Ray-CFO maintains rate prospectus today (out-of-repo)
- **`docs/GRIMBANEWS_PARTNER_PROGRAM_TIER_DESIGN_S1961.md`** — sibling tier-pricing pattern

## Honest framing

Rate card is an editorial-policy publication. Code lift is minimal. Real gate: payment infrastructure (S1456 payout, deferred) cannot deliver against a published rate without billing rails.

## Owners

- **CEO:** Lucy Leai — editorial-payment philosophy
- **CFO:** Ray Dalio + Warren Buffett — rate economics
- **Editorial:** TBD ombudsman + Lucy
- **Marketing:** Henry Walker — rate card publication
- **Frontend:** Nina Patel — `/contributeurs/tarifs` route
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1453 row)
- Profile + verification: `docs/GRIMBANEWS_CONTRIBUTOR_PROFILE_VERIFICATION_DESIGN.md`
- 1099 / tax: `docs/GRIMBANEWS_CONTRIBUTOR_1099_TAX_REPORTING_DESIGN.md`
- Analytics: `docs/GRIMBANEWS_CONTRIBUTOR_ANALYTICS_DASHBOARD_DESIGN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
