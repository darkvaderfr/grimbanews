# GrimbaNews — API Academic-Tier Docs

**Status:** plan v0
**Owner:** Michael O'Connor (Technical Writer) + Lisa Nguyen (data)
**Walks:** Mythos S1696 (academic-tier API docs) deferred → partial
**Gating dependency:** S1691 academic tier launched.

## Why this exists

Academic users need rigorous API docs: per-field definitions, citation requirements, reproducibility examples, common research-use patterns.

## Doc surfaces

`/docs/api/academic/`:
- **Getting Started:** auth, first request, rate limits.
- **Endpoint Reference:** per-endpoint full schema (extends Wave QQQQ MG API reference).
- **Dataset Versioning:** per-version pinning for reproducibility (per Wave SUB-24).
- **Citation Guide:** how to cite GrimbaNews dataset in academic papers.
- **Common Research Patterns:** bias-mix analysis, MG cluster tracking, source-roster evolution, per-region time-series.
- **Code Examples:** Python (pandas + requests), R (httr + tidyjson), Stata, MATLAB.
- **Reproducibility Checklist:** version pinning + per-call timestamp + per-grant tagging.

## Per-example Jupyter notebook

`docs/academic-examples/{notebook-slug}.ipynb`:
- Per-research-question notebook
- Per-notebook reproducible cell-by-cell
- Per-notebook citation cell with DOI

## Cross-references

Master plan: S1696. Sister: `docs/GRIMBANEWS_MIDDLE_GROUND_API_REFERENCE.md`, `docs/GRIMBANEWS_API_ACADEMIC_TIER_PLAN.md`.
