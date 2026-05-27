# GrimbaNews — Investigations Data Pipeline (CSV / Pandas / DuckDB)

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + investigative reporter (when hired)
**Walks:** Mythos S2205 (Long-form investigations data-analysis pipeline) deferred → partial
**Gating dependency:** Investigative reporter hired + per-investigation data sourcing.

## Why this exists

Investigations frequently require analyzing large CSV / Parquet datasets (ownership filings, government records, leaked databases). Standard toolchain shipped.

## v1 toolchain

- **Local analysis:** Python 3.11 + pandas + DuckDB + Jupyter on investigator's Tails laptop.
- **Visualization:** Matplotlib + Plotly for static; Datawrapper for publish-ready charts.
- **Sharing:** Per-investigation Git repo (private, darkvaderfr org).
- **Reproducibility:** Per-analysis Jupyter notebook published alongside investigation.

## Per-investigation data lifecycle

1. Data sourcing (FOIA, leaks, public-data, partner-provided).
2. Cleaning (pandas).
3. Analysis (DuckDB queries + pandas pipelines).
4. Visualization.
5. Source-protection: anonymize PII before publishing notebook.
6. Per-investigation notebook archived in repo + linked from article.

## Tooling repo

`darkvaderfr/grimbanews-investigations` (private). Per-investigation subfolder. CI runs notebooks for reproducibility check.

## Cross-references

Master plan: S2205. Sister: `docs/GRIMBANEWS_INVESTIGATIVE_REPORTER_HIRE_PROFILE.md`, `docs/GRIMBANEWS_FOIA_CADA_TEMPLATE_LIBRARY.md`.
