# GrimbaNews — Annotation Analytics Plan

**Status:** plan v0
**Owner:** David Chen (Data) + Liam Smith (PM)
**Walks:** Mythos S1548 (annotation analytics) deferred → partial
**Gating dependency:** S1371 annotation primitive + aggregation job.

## Why this exists

Annotation analytics let editorial team understand which passages of which articles resonate — informing both ranker signal and editorial pull-quote selection.

## v1 metrics (aggregate-only)

| Metric | Source |
|---|---|
| Annotations per article | count |
| Top-quoted passages | most-anchored quote text per article |
| Note-attached rate | % annotations with non-empty note |
| Public-share rate | % annotations with visibility != 'private' |
| Per-locale annotation rate | breakdown |
| Per-topic annotation rate | breakdown |

## v1 dashboard

- `/admin/grimba/annotations` — last-30-day rollup.
- Per-article drill: heat-strip of which paragraphs are most-annotated.
- Editorial use: surface top-quoted passage as pull-quote in newsletter.

## Privacy

- All metrics aggregate-only (no per-reader log).
- Per-passage heatmap is per-article, never per-reader.
- Public-annotation author handle only visible to editorial reviewers (not on public dashboard).

## Cross-references

Master plan: S1548. Sister: S1371 (schema), S1376 (notebook), S1545 (public), S1549 (moderation).
