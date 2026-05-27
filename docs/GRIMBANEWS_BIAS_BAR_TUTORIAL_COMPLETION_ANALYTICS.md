# GrimbaNews — Bias-Bar Tutorial Completion Analytics

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Liam Smith (PM)
**Walks:** Mythos S1779 (bias-bar tutorial analytics — completion rate) deferred → partial
**Gating dependency:** S1772 tutorial overlay live + S1733 warehouse read-event capture.

## Why this exists

Tutorial-completion rate is a key onboarding KPI. Per-tutorial drop-off pinpoints UX friction.

## Metrics tracked

- Tutorial starts (per session).
- Tutorial completion (all steps).
- Per-step drop-off rate.
- Per-step dwell time.
- Post-tutorial bias-classifier-comprehension quiz score (gates on quiz module).

## Schema (gates on Wave SUB-26 warehouse + Vader migration)

```
tutorial_events:
  id | session_hash | tutorial_slug | step_index | event_type (start|step|complete|abandon)
   | timestamp
```

## Per-tutorial dashboard

`/admin/grimba/tutorial-analytics`:
- Per-tutorial weekly completion-rate trend.
- Per-step drop-off heatmap.
- Per-tutorial cohort comparison (school vs general public).

## Cross-references

Master plan: S1779. Sister: `docs/GRIMBANEWS_BIAS_BAR_TUTORIAL_SCHOOL_DISTRIBUTION.md`, `docs/GRIMBANEWS_WAREHOUSE_LAUNCH_PLAYBOOK.md`.
