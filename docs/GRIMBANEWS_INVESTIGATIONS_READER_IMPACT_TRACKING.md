# GrimbaNews — Investigations Reader-Impact Tracking

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Lucy Leai (Strategy)
**Walks:** Mythos S2215 (Long-form investigations reader-impact tracking) deferred → partial
**Gating dependency:** GrimbaVaultEvents extension + outcome-log column.

## Why this exists

Investigative work's value isn't just clicks — it's downstream impact (policy change, regulatory action, public discourse). Tracking these outcomes informs editorial priorities.

## Per-investigation outcome categories

- **Policy change:** law passed, regulation issued, official statement.
- **Investigation triggered:** government/regulator opened inquiry.
- **Resignation/firing:** named-individual outcome.
- **Public-discourse shift:** measurable change in how a topic is covered.
- **Reader behavior change:** subscription spike, share-rate surge.
- **Academic citation:** referenced in scholarly work.

## Schema (gates on Vader migration approval)

```
investigation_outcomes:
  id | post_id | outcome_category | outcome_text | observed_at | reporter_id
   | confidence (1-5) | source_url
```

## Per-outcome cadence

- Per-investigation: per-quarter outcome check by lead investigator.
- Per-year: annual roll-up report.
- Per-investigation: published "impact note" at 6-month + 12-month marks.

## Reader-facing surface

Per-investigation page surfaces:
- "Impact à ce jour" panel.
- Per-outcome timeline.
- Per-outcome source citation.

## Cross-references

Master plan: S2215. Sister: `docs/GRIMBANEWS_LONG_FORM_INVESTIGATIONS_SCOPE.md`, `docs/GRIMBANEWS_PER_USER_READING_TIME_ANALYTICS_PLAN.md`.
