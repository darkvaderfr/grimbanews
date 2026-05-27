# GrimbaNews — Per-Region Weekly Bias-Mix Retrospective

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + per-region editor
**Walks:** Mythos S1602 (per-region weekly bias-mix retrospective) deferred → partial
**Gating dependency:** Per-region trust dashboard (Wave AABB partial).

## Why this exists

Weekly editorial review needs structured visibility into per-region bias drift, source-mix changes, and per-cluster signal trends. A weekly retro doc captures this and drives editor action items.

## Template

```
# GrimbaNews — Region: {FR | BR | DE | ...} Weekly Bias-Mix Retro · Week {N}

## Window
Monday 00:00 UTC → Sunday 23:59 UTC.

## Bias-mix
- L/C/R article distribution: {actual}% / {actual}% / {actual}%
- vs national-press baseline: drift {±Δ%}
- vs prior week: drift {±Δ%}

## Middle Ground signal
- New MG clusters this week: {count}
- Top MG sources (anchor sources): {top-5}
- Per-region MG cluster rate / 1000 articles: {rate}

## Blindspot signal
- New BS clusters this week: {count}
- Per-camp split: L-blindspot {count}, R-blindspot {count}

## Source health
- Top-3 high-uptime sources
- Top-3 high-failure-rate sources (needs ops follow-up)

## Reader engagement
- Top-5 most-read clusters
- Top-5 most-shared clusters
- Reading-time avg: {min}

## Action items
- [ ] Editor follow-up: ...
- [ ] Ops follow-up: ...
- [ ] Brief revision: ...

## Sign-off
- Per-region editor:
- Lucy Leai (Strategy):
```

## Cadence

- Monday 09:00 UTC: data export.
- Monday 10:00-12:00 UTC: editor drafts retro.
- Monday 14:00 UTC: Lucy reviews + signs off.
- Action items tracked in shared backlog.

## Cross-references

Master plan: S1602. Sister: `docs/GRIMBANEWS_PER_REGION_TRUST_DASHBOARD_PLAN.md`, `docs/GRIMBANEWS_DAY7_INCIDENT_REVIEW_TEMPLATE.md`.
