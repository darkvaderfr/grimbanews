# GrimbaNews — Per-Region Partner Attribution Metrics

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Lisa Nguyen (data)
**Walks:** Mythos S1671 (per-region partner attribution metrics) deferred → partial
**Gating dependency:** Per-partner stream filter live + 30+ days of click data.

## Metrics tracked per partner

1. **Partner-tagged content surfaced** — count of articles attributed in surfacing windows.
2. **Click-through to partner** — readers who clicked partner-attribution badge.
3. **Per-cluster contribution rate** — partner content / cluster total content.
4. **Per-cluster MG/BS contribution** — partner role in editorial-signal clusters.
5. **Reader feedback on partner content** — per-feedback-widget category breakdown.
6. **Per-attribution-prominence** — first-byline vs N-th-source.

## Dashboard

`/admin/grimba/partners/{id}/metrics`:
- Monthly metric trend chart
- Per-cluster top contributions
- Cross-link to per-partner content stream

## Per-partner monthly export

CSV + PDF emailed to partner contact monthly. Partner reviews their own attribution health.

## Cross-references

Master plan: S1671. Sister: `docs/GRIMBANEWS_PER_REGION_PARTNER_CASE_STUDY_TEMPLATE.md`, `docs/GRIMBANEWS_B2B_TRUST_REPORT_MONTHLY_EXPORT_PLAN.md`.
