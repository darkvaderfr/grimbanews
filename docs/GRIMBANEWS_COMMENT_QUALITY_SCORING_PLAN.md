# GrimbaNews — Comment Quality Scoring Surrogate Plan

**Sprint ID:** S1365
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370 — Comment quality scoring`
**Walk wave:** CCCC

## Gating dependency

Comment quality scoring (per Slate / Coral / Perspective API) needs:

- Comments to exist (S1362)
- A scoring pipeline (likely NobuAI-driven; rubric: thoughtfulness, on-topic, sourced, civil)
- A surfacing UI (top-rated rail, sort-by-quality option)
- Reader-feedback loop for score calibration

## Surrogate-now infra

- **NobuAI summary pipeline (`grimba:nobuai-summaries`)** — proves the per-row scoring cadence works at 30min intervals
- **`tests/Feature/GrimbaNobuAiBrandPurityTest`** — proves NobuAI can be wrapped as classifier
- **`GrimbaClusterBias`** — analogous rule-based + LLM-augmented scoring helper

## Honest framing

A v2 feature (post-comments-launch) that's premature without comments at scale. The infra to score *would* leverage existing NobuAI pipeline; the policy work (what does "quality" mean per locale?) is the harder unsolved problem.

## Owners

- **Editorial:** TBD — quality rubric per locale
- **Data:** David Chen — scoring pipeline
- **Product:** Liam Smith — surfacing UX
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1365 row)
- Comment threading: `docs/GRIMBANEWS_COMMENT_THREADING_PLAN.md`
- Comment moderation: `docs/GRIMBANEWS_COMMENT_MODERATION_QUEUE_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
