# GrimbaNews — A/B Rank Harness Surrogate Plan

**Sprint ID:** S1346
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1341-s1350 — A/B rank harness`
**Walk wave:** CCCC

## Gating dependency

A/B rank harness needs:

- General A/B engine (S1073 deferred)
- Per-request variant assignment with sticky-by-visitor
- Two ranker implementations (e.g., rules vs ML)
- Engagement instrumentation (click, dwell, return) — also missing
- Bayesian / sequential evaluator (per existing A/B design)

## Surrogate-now infra

- **`docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`** — overall A/B design
- **`docs/GRIMBANEWS_AB_HARNESS_SEQUENTIAL_TESTING_DESIGN.md`** — sequential test design
- **`docs/GRIMBANEWS_AB_HARNESS_RETROSPECTIVE_TEMPLATE.md`** — retrospective template
- **`GrimbaVaultEvents`** — minimal event sink

## Honest framing

Sibling to S1345 (ML rank model). Useless to ship one without the other. Both gate on A/B harness (S1073) + engagement instrumentation.

## Owners

- **Data:** David Chen — variant assignment + evaluator
- **Backend:** Rajesh Kumar — request-time ranker dispatch
- **Product:** Liam Smith — guardrail metrics (bounce, NPS)
- **Platform:** Hannah Kim — variant assignment caching
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1346 row)
- ML rank model: `docs/GRIMBANEWS_ML_RANK_MODEL_PLAN.md`
- A/B harness: `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
