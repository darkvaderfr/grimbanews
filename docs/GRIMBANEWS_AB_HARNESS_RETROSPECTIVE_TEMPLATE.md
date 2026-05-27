# GrimbaNews — A/B Harness Experiment Retrospective Doc Template

**Sprint ID:** S1729
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1721-s1730 — A/B harness — experiment retrospective doc template`
**Walk wave:** BBBB

## Gating dependency

A per-experiment retrospective template needs:

- Live A/B engine (S1721-S1725, all deferred)
- Decision on disclosure scope (winners-only / winners-and-losers / full)
- Template sections: hypothesis / setup / observed effect / stop reason / decision / next-steps
- Per-experiment artifact lifecycle (live → archived → published)
- Tie-in to annual transparency disclosure (S2009, deferred)

## Surrogate-now infra

- **Per-Mythos-sprint evidence docs** — every shipped sprint produces a doc that functions as an implicit retrospective; ~400+ examples in `docs/`
- **Git log + commit messages** — every decision documented in commit history
- **`docs/GRIMBANEWS_MYTHOS_S*_EVIDENCE.md`** family — these are the pattern future A/B retros would follow

## Honest framing

The template format is straightforward and could ship now as a `.md` skeleton. Deferred because skeletons without filled examples are non-load-bearing.

## Owners

- **Data Science:** David Chen — template structure
- **Product:** Liam Smith — disclosure-scope policy
- **Tech Writer:** Michael O'Connor — template authoring
- **Editorial:** Lucy Leai — disclosure governance
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1729 row)
- Base harness: `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`
- Admin console: `docs/GRIMBANEWS_AB_HARNESS_ADMIN_CONSOLE_DESIGN.md`
- Sequential testing: `docs/GRIMBANEWS_AB_HARNESS_SEQUENTIAL_TESTING_DESIGN.md`
- Transparency A/B disclosure: `docs/GRIMBANEWS_TRANSPARENCY_AB_OUTCOMES_DISCLOSURE_SCOPE.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
