# GrimbaNews — Bias Shift Detection Plan

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Lucy Leai (Strategy)
**Walks:** Mythos S1061 + S1062 (bias-shift + factuality-shift detection) deferred → partial
**Gating dependency:** Time-series of `news_sources.bias_rating` + `news_sources.factuality_score` historical snapshots.

## Why this exists

A news source's bias rating + factuality score should be reviewed at least quarterly. If a source meaningfully shifts (e.g. ownership change, editorial leadership swap), readers need to know and clusters need re-weighting.

## v1 design

1. Daily snapshot of `news_sources` table → `news_sources_history` (new table).
2. Quarterly compute per-source rolling-90-day average bias + factuality.
3. Flag sources with shift > 1 step (e.g. center → center-right, or factuality_score Δ > 15).
4. Editor reviews flagged sources, updates rating, logs rationale in `news_sources.review_log`.

## Schema (new table, gates on migration approval)

```
news_sources_history:
  id | source_id | bias_rating | factuality_score | credibility_score | ownership_type | snapshot_at
```

## UX

Admin-only `/admin/grimba/news-sources/shifts` page:

- Table: source, current rating, 90-day-avg rating, shift Δ, last review date.
- Color-coded: green (stable), yellow (drifting), red (shifted).
- Click → source detail page with shift history chart.

## Editor review cadence

- Quarterly: editor reviews all sources flagged red/yellow.
- On-event: ownership change, leadership swap, major fact-check incident triggers immediate review.

## Surrogate today

`news_sources.review_log` JSON column exists (per Wave EEEEEEEEEEE Wave SOK). Operator can manually log shifts. Auto-detection deferred to this plan.

## Cross-references

Master plan: S1061, S1062. Sister: `docs/GRIMBANEWS_SOURCE_LEGAL_COVERAGE_AUDIT_PLAN.md`, `docs/GRIMBANEWS_PER_SOURCE_SLA_DASHBOARD_PLAN.md` (Wave DDDD).
