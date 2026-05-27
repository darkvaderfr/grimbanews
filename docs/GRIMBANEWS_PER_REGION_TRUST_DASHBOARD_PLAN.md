# GrimbaNews — Per-Region Trust Dashboard Plan

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Sara Chen (CISO) + per-region editor
**Walks:** Mythos S1144 (per-region trust dashboard) deferred → partial
**Gating dependency:** Per-region source roster shipped (Wave UUUU EU east + LATAM + MENA + APAC + Oceania docs) + 30 days of cluster data per region.

## Why this exists

A reader in France should be able to see how the GrimbaNews coverage of French news compares to French national-press benchmarks: bias mix, factuality, sourcing diversity. Same for every active region. Surfaces editorial accountability per region.

## v1 design

`/admin/grimba/trust-dashboard/{region}` admin-only page renders:

- **Bias mix:** L/C/R article distribution over 30 days, vs national-press baseline.
- **Source diversity:** number of distinct sources contributing per topic.
- **Factuality average:** weighted avg of `news_sources.factuality_score` across active sources.
- **Per-topic coverage gaps:** topics where one bias camp is < 10%.
- **MG cluster rate per region:** Middle Ground clusters per 1000 articles.
- **Blindspot rate per region:** Blindspot clusters per 1000 articles.
- **Per-cluster bias-shift over time:** are clusters becoming more polarized?

## v2 — public-facing

Polished version of the dashboard at `/transparence/{region}` for readers. Same data, reader-friendly visualizations.

## Per-region benchmarks

- France: against national-press surveys (Reporters sans frontières press freedom index, etc.).
- Brazil: against Abraji + Abert benchmarks.
- DE: against Reuters Institute Digital News Report DE.
- Etc.

## Cadence

- Weekly auto-refresh.
- Monthly editor review.
- Quarterly published external (`/transparence/<region>` public page).

## Cross-references

Master plan: S1144. Sister: Wave UUUU source roster docs, `docs/GRIMBANEWS_TRANSPARENCY_REPORT_SCOPE.md` (Wave LLL), `docs/GRIMBANEWS_PER_SOURCE_SLA_DASHBOARD_PLAN.md`.
