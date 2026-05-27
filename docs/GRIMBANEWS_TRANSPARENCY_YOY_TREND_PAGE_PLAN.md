# GrimbaNews — Transparency Report: Year-Over-Year Trend Page Plan

**Status:** plan v0
**Owner:** David Chen (Data) + Michael O'Connor (Tech Writer) + Henry Walker (Editorial)
**Walks:** Mythos S2018 (annual transparency report — year-over-year trend page) deferred → partial
**Gating dependency:** ≥ 2 editions of annual transparency report exist (gates on S2011 first edition + 1 year later).

## Why this exists

A standalone annual report is a snapshot. Trends across years tell the story of editorial maturation, source-roster evolution, and bias-distribution shifts.

## v1 design

- New page: `/transparency/tendances`.
- Per-metric YoY chart for headline metrics:
  - Articles published per year
  - Source roster size + diversity (Gini)
  - Bias-distribution shift (radar overlay multi-year)
  - Corrections issued (rate + absolute)
  - Ad decisions (accept/reject totals)
  - Reader complaints / ombudsman cases
- Narrative section: editorial-team commentary on what shifted and why.
- Downloadable as PDF.

## Implementation

- Pulls from per-year aggregate JSON (S2013 open-data bundles).
- Updates automatically when new year ships.
- Multi-year overlays default to last 5 years; user can extend.

## Cross-references

Master plan: S2018. Sister: S2001 (annual report), S2011 (publish cadence), S2013 (open-data bundle), S2019 (archive accessibility).
