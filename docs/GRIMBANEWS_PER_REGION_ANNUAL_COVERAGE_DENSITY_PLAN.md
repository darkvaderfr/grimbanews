# GrimbaNews — Per-Region Annual Coverage-Density Report Plan

**Sprint ID:** S2179
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Per-region annual coverage-density report`
**Walk wave:** BBBB

## Gating dependency

A per-region annual coverage-density report needs:

- Per-region counters (S2178 sister, deferred)
- A first annual transparency report (S2011, deferred)
- A `/transparence/couverture-regionale/{year}` route
- Per-locale ack copy
- Operator-side comparison vs prior years (gates on ≥2 years operational data)

## Surrogate-now infra

- **`grimba_automation_runs`** — daily ingest cadence per source; can be rolled annually after ≥1 year
- **`news_sources.region` + `last_polled_at`** — sufficient for hand-built per-region rollups today
- **`docs/GRIMBANEWS_ANNUAL_TRANSPARENCY_SCOPE_DECISION.md`** — existing transparency-program anchor

## Honest framing

Density report is a deliverable, not a feature. Gates on transparency program (S2001-S2020 band) shipping at all + ≥1 year of clean per-region data.

## Owners

- **Editorial:** TBD ombudsman + Lucy Leai
- **Data Eng:** Benjamin Lee — annual rollup
- **Product:** Liam Smith — report format
- **Compliance:** Maya Patel — disclosure policy
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2179 row)
- Under-covered tracker: `docs/GRIMBANEWS_PER_REGION_UNDER_COVERED_STORY_TRACKER_PLAN.md`
- Annual transparency: `docs/GRIMBANEWS_ANNUAL_TRANSPARENCY_SCOPE_DECISION.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
