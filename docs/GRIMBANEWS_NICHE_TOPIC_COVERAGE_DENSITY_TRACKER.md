# GrimbaNews — Niche-Topic Coverage Density Tracker

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Lucy Leai (Strategy) + per-topic editor
**Walks:** Mythos S2199 (Niche-topic v2 coverage density) deferred → partial
**Gating dependency:** v2 taxonomy + S2179 per-region tracker pattern.

## Why this exists

Once GrimbaNews has 40+ sub-buckets (Wave UUUU), risk of uneven coverage: some buckets thriving, others starving. Density tracker surfaces under-served niches for operator intervention.

## v1 design

Per-week aggregate per sub-bucket:
- Article count
- Cluster count
- Source diversity (distinct sources contributing)
- Reader-engagement signal (clicks, reads, shares)
- Bias-mix L/C/R distribution
- MG/BS cluster contribution

Sub-buckets ranked by:
- Density score: weighted combination of above
- Trend: 4-week rolling Δ

## Operator surface

`/admin/grimba/niche-density`:
- Heatmap: sub-bucket × week
- Top-5 thriving
- Top-5 starving
- Per-starving: source-roster gap analysis

## Editorial action

- Starving 4+ weeks → editor reviews source roster for the sub-bucket
- Starving 8+ weeks → consider sub-bucket consolidation

## Cross-references

Master plan: S2199. Sister: `docs/GRIMBANEWS_PER_REGION_TRUST_DASHBOARD_PLAN.md` (similar pattern), `docs/GRIMBANEWS_TOPIC_TAXONOMY_V2_DESIGN.md`.
