# GrimbaNews — Editorial Archives: Year Retrospective

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Steve Jobs (CPO) + per-region editor
**Walks:** Mythos S1603 (editorial archives — year-of-coverage retrospective) deferred → partial
**Gating dependency:** 12 months of cluster history + decay/archive policy (per Wave AAEE).

## Why this exists

Year-end retrospective is editorial-product gold: "what GrimbaNews covered in 2026" / "where consensus formed" / "where blindspots emerged." Drives subscription renewal + new-reader acquisition + media-watchdog visibility.

## v1 design

`/retrospective/{year}` page renders:
- Year-in-review summary (NobuAI-generated draft + editor curation)
- Top-50 clusters by reader-engagement
- Top-25 Middle Ground stories
- Top-25 Blindspot stories
- Per-region rolled summary
- Per-bias-camp consensus + divergence rolled summary
- Editor's-pick: 10 standout stories with commentary

## Auto-generation pipeline

January-15 of year+1:
- `grimba:generate-yearly-retro --year={prev}` cron.
- Pulls aggregate metrics from cluster + post + reading-events tables.
- NobuAI generates draft narrative per Wave AACC research mode.
- Editor reviews + curates 2 weeks.
- Publish February 1 of year+1.

## Reader UX

- Per-year section page (`/retrospective/2026`).
- Per-section deep-link.
- Per-cluster cross-link to /comparatif/{id} (still live per Wave AAEE decay policy).
- Per-author shout-out section.

## Cross-references

Master plan: S1603. Sister: `docs/GRIMBANEWS_PER_CLUSTER_DECAY_ARCHIVE_POLICY.md`, `docs/GRIMBANEWS_NOBUAI_MULTI_STEP_RESEARCH_MODE_PLAN.md`.
