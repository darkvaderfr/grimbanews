# GrimbaNews — Per-Region Under-Covered Story Tracker Plan

**Sprint ID:** S2178
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Per-region under-covered-story tracker`
**Walk wave:** BBBB

## Gating dependency

A per-region under-covered tracker needs:

- Per-region story-volume counters (`region`, `category`, `count_24h`, `count_7d`, `count_30d`)
- Baseline coverage-density expectations per region (operator-side editorial calibration — S2173, deferred)
- A delta job that flags regions where current cadence is N std-dev below baseline
- An editorial-workflow surface (S1291, deferred)
- Per-region editor seats (S2174, deferred)

None of this ships today.

## Surrogate-now infra

- **Per-region landing pages** — `/locale` family already exposes regional cadence visually; editors can eyeball under-coverage
- **`grimba_automation_runs`** — captures per-source ingest cadence; an under-covered region == multiple regional sources with stale `last_polled_at`
- **`news_sources.region` filter on admin** — operator can sort sources by region + last-ingest-time to find quiet regions

## Honest framing

The editorial signal is observable today via existing admin tooling. Automated alerting on under-coverage gates on per-region baselines (S2173, operator-side calibration).

## Owners

- **Editorial:** TBD per-region editors + Lucy Leai
- **Data Eng:** Benjamin Lee — per-region counters + delta job
- **Backend:** Rajesh Kumar — flag-emit endpoint
- **Product:** Liam Smith — flag-routing UX
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2178 row)
- Per-region annual coverage-density: `docs/GRIMBANEWS_PER_REGION_ANNUAL_COVERAGE_DENSITY_PLAN.md`
- Per-region newsroom partnership: `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`
- Editorial workflow gate: `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1281-s1290`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
