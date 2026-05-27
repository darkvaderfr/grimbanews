# GrimbaNews — A/B Harness Sequential Testing / Stop-Early Stats Design

**Sprint ID:** S1726
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1721-s1730 — A/B harness — sequential testing / stop-early stats`
**Walk wave:** BBBB

## Gating dependency

Sequential testing / stop-early stats need:

- A/B engine shipped (S1721-S1725 base harness, all deferred)
- Statistical framework: Always-Valid Inference (mSPRT) or Bayesian (Beta-Binomial posterior)
- Stop-early decision policy (significance threshold, MEI minimum effect of interest)
- A `experiments` schema with `started_at`, `stopped_at`, `stop_reason`, `posterior_p`
- Admin experiment console (S1727, deferred)
- Per-experiment retrospective template (S1729, deferred)

None of these ship today.

## Surrogate-now infra

- **`docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`** — existing base-harness scope doc (partial)
- **Per-sprint qualitative review** — every Mythos sprint produces an evidence doc that functions as a sprint-level "before/after" comparison
- **Editorial intuition** — Lucy + Steve eyeball changes today (subjective, not statistical)

## Honest framing

Sequential testing is the technically correct way to stop A/B tests early without inflating Type-I error. It is the right v2 — v1 is fixed-horizon t-test, which is also deferred (S1721).

## Owners

- **Data Science:** David Chen — statistical framework selection
- **Backend:** Rajesh Kumar — experiment schema + decision engine
- **Product:** Liam Smith — stop-policy + MEI calibration
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1726 row)
- Base harness scope: `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`
- Admin console: `docs/GRIMBANEWS_AB_HARNESS_ADMIN_CONSOLE_DESIGN.md`
- Retro template: `docs/GRIMBANEWS_AB_HARNESS_RETROSPECTIVE_TEMPLATE.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
