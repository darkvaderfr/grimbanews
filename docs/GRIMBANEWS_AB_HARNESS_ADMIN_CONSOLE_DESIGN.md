# GrimbaNews — A/B Harness Admin Experiment Console Design

**Sprint ID:** S1727
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1721-s1730 — A/B harness — admin experiment console`
**Walk wave:** BBBB

## Gating dependency

Admin experiment console needs:

- A/B engine shipped (S1721-S1725, all deferred)
- A `/admin/grimba/experiments` route
- Per-experiment status surfaces: cohorts, conversions, p-value or posterior, stop button
- Cohort-rollout slider (1% → 10% → 50% → 100%)
- Per-experiment notes log
- Permission gate (super-admin only)

## Surrogate-now infra

- **Botble admin scaffolding** — existing admin patterns can host the route
- **`docs/GRIMBANEWS_ADMIN_BACKEND_CLOSEOUT_INDEX.md`** — admin-backend overview already documented
- **No experiments to display** — empty state would be a literal accurate representation today

## Honest framing

Console is the human surface for the harness. Built last; meaningless without experiments.

## Owners

- **Backend:** Rajesh Kumar — admin route + per-experiment queries
- **Frontend:** Nina Patel — console layout + slider UX
- **Product:** Liam Smith — surfacing rules
- **CISO:** Sara Chen — admin-only gate
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1727 row)
- Base harness: `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`
- Sequential testing: `docs/GRIMBANEWS_AB_HARNESS_SEQUENTIAL_TESTING_DESIGN.md`
- Retro template: `docs/GRIMBANEWS_AB_HARNESS_RETROSPECTIVE_TEMPLATE.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
