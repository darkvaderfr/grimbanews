# GrimbaNews — Per-Member Referral Dashboard Design

**Sprint ID:** S1957
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1951-s1960 — Referral dashboard (per-member)`
**Walk wave:** BBBB

## Gating dependency

A per-member referral dashboard needs:

- Code generation shipped (S1952, deferred)
- Attribution tracking live (S1953, deferred)
- A `/compte/parrainages` (FR) / `/account/referrals` (EN) route
- Per-row data: code, link, click count, signup count, conversion count, earned reward
- Reward issuance live (S1954, deferred)
- i18n catalog entries for the dashboard

## Surrogate-now infra

- **`/compte` member home placeholder** — Botble member-account UI already exists at base; a referral tab would slot in
- **`/contact` ack pattern** — current "share with a friend" call lives in the methodology page footer

## Honest framing

Dashboard is the readable surface for the data captured by S1952 + S1953. ~3-day frontend lift once data exists.

## Owners

- **Frontend:** Nina Patel — dashboard partial + i18n catalog
- **Backend:** Rajesh Kumar — per-member rollup query
- **Product:** Liam Smith — KPI surfacing rules
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1957 row)
- Program design: `docs/GRIMBANEWS_REFERRAL_PROGRAM_TIER_DESIGN.md`
- Leaderboard sister: `docs/GRIMBANEWS_REFERRAL_LEADERBOARD_DESIGN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
