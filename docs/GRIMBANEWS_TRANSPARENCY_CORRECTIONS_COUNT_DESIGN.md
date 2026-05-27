# GrimbaNews — Annual Transparency: Corrections + Per-Source Count Design

**Sprint ID:** S2006
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — corrections issued + per-source count`
**Walk wave:** BBBB

## Gating dependency

Publishing corrections + per-source counts annually needs:

- A `corrections` table (`post_id`, `source_id`, `issued_at`, `nature`, `public_log_url`)
- An editorial-workflow surface to issue + log corrections (S1291, deferred)
- A per-source aggregator rolling corrections into `news_sources.corrections_count`
- A `/transparence/corrections/{year}` page
- A first annual transparency report (S2011, deferred)

None ship today. There is no `corrections` table; corrections are issued informally via article edits without ledger entry.

## Surrogate-now infra

- **`posts.updated_at`** — every edit is timestamped, including silent corrections
- **Git diff on `/methodologie`** — methodology rule changes are visible in history
- **`/contact` form** — correction requests come in here and are handled out-of-band
- **Per-source factuality_score** — `news_sources.factuality_score` is the upstream prior; sources with high correction-rates should already score lower

## Honest framing

A corrections primitive is a 1-week build but creates a public commitment to a SLA we cannot honor without an ombudsman (S2022, deferred) and an editorial-workflow surface (S1291, deferred). The right path is: ombudsman appointment → editorial workflow → corrections primitive → transparency disclosure.

## Owners

- **Editorial:** TBD ombudsman + Lucy Leai
- **Backend:** Rajesh Kumar — `corrections` table + endpoint
- **Frontend:** Nina Patel — per-article correction note partial
- **Compliance:** Maya Patel — disclosure policy
- **DBA:** Larry Ellison — schema + per-source rollup
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2006 row)
- Annual transparency scope: `docs/GRIMBANEWS_ANNUAL_TRANSPARENCY_SCOPE_DECISION.md`
- Ombudsman charter: `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
