# GrimbaNews — Annual Transparency Report A/B-Test Outcomes Disclosure Scope

**Sprint ID:** S2009
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — A/B-test outcomes transparency`
**Walk wave:** BBBB

## Gating dependency

Publishing A/B-test outcomes in the annual transparency report needs:

- A live A/B engine that has actually run experiments (S1073, deferred; S1721-S1730 harness, deferred)
- A retrospective doc template per experiment (S1729, deferred)
- An editorial-decision policy on which experiments are disclosed (winners-and-losers vs winners-only)
- A `/transparence/experiments/{year}` page or section
- A first annual transparency edition (S2011, deferred) to publish into

None of the gating dependencies have shipped. The annual transparency report itself is deferred — there is no first edition to disclose A/B outcomes within.

## Surrogate-now infra

- **Methodology page disclosure** — `/methodologie` declares the editorial principles that govern experimentation publicly
- **OSS methodology repo plan** — `docs/GRIMBANEWS_OSS_METHODOLOGY_README_PLAN.md` already commits to publishing methodology rule changes
- **Per-sprint git log** — every methodology change since S1 is in the public git history (after the darkvaderfr push); this is the de-facto pre-A/B-engine "experiment log"

## Honest framing

Until an A/B engine ships, "transparency about A/B outcomes" means "transparency about the absence of A/B" — which is already disclosed in `/methodologie`. The placeholder is intentional.

## Owners

- **Editorial / Transparency:** TBD ombudsman + Lucy Leai oversight
- **Data Science:** David Chen — experiment readout format
- **Product:** Liam Smith — disclosure scope policy
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2009 row)
- Annual transparency scope: `docs/GRIMBANEWS_ANNUAL_TRANSPARENCY_SCOPE_DECISION.md`
- A/B harness design: `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`
- OSS methodology: `docs/GRIMBANEWS_OSS_METHODOLOGY_README_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
