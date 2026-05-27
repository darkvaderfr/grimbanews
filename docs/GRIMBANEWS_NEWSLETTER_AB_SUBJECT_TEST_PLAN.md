# GrimbaNews — Newsletter A/B Subject Test Surrogate Plan

**Sprint ID:** S1284
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1281-s1290 — Newsletter A/B subject test`
**Walk wave:** CCCC

## Gating dependency

A subject-line A/B test for newsletters needs:

- A/B harness (gates on S1073)
- Email-event tracking (open, click) — currently no SDK installed (S1489)
- Per-variant subject assignment at send time
- Stat-significance evaluator (Bayesian or sequential per existing A/B design docs)

## Surrogate-now infra

- **`grimba:saved-search-digests`** — weekly job pattern that already segments per-subscriber and renders per-recipient subject — natural slot for A/B variant injection
- **`tests/Feature/SavedSearchAlertsTest`** — locks the digest contract
- **`docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`** — overall A/B engine scope
- **`docs/GRIMBANEWS_AB_HARNESS_SEQUENTIAL_TESTING_DESIGN.md`** — sequential test design

## Honest framing

The send-path supports variant injection today (the renderer builds the subject per-recipient). The missing piece is event tracking — we send blind without open/click telemetry, so we can't measure subject performance.

## Owners

- **Marketing:** Henry Walker — variant copy + cadence
- **Data:** David Chen — A/B reporting
- **Backend:** Rajesh Kumar — variant assignment in send job
- **Platform:** Hannah Kim — webhook intake for opens/clicks
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1284 row)
- A/B harness design: `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`
- Email-event tracking gap: S1489
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
