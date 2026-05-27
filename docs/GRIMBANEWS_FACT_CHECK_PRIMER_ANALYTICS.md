# GrimbaNews — Fact-Check Primer Analytics (Read-Through Rate)

**Status:** plan v0
**Owner:** Lisa Nguyen (data)
**Walks:** Mythos S1789 (fact-check primer analytics) deferred → partial
**Gating dependency:** S1733 warehouse read-event capture.

## Metrics

- Per-primer reads
- Per-section read-through rate (where readers drop)
- Per-section dwell time
- Per-quiz attempt rate
- Per-quiz pass rate
- Per-cohort comparison (school vs general public)

## Schema (Wave SUB-26 warehouse)

Per-primer events captured:
- `primer_start`
- `primer_section_view` (per-section)
- `primer_complete`
- `primer_quiz_start`
- `primer_quiz_submit`

## Per-primer dashboard

`/admin/grimba/literacy-analytics`:
- Per-module weekly trends
- Per-section drop-off heatmap
- Per-quiz score distribution

## Cross-references

Master plan: S1789. Sister: `docs/GRIMBANEWS_BIAS_BAR_TUTORIAL_COMPLETION_ANALYTICS.md`, `docs/GRIMBANEWS_FACT_CHECK_INTERACTIVE_QUIZ_DESIGN.md`.
