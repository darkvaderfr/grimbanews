# GrimbaNews — Ad Rejection Ledger Transparency

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Sara Chen (CISO)
**Walks:** Mythos S1688 (ad rejection ledger) deferred → partial
**Gating dependency:** Ad-tech onboarding.

## Why this exists

GrimbaNews rejects ads that conflict with editorial values (political-campaign ads on news, brand-unsafe categories, ads adjacent to bias-classified content). Transparency: publish aggregate rejection counts so readers + advertisers see the editorial integrity work.

## v1 design

`/transparence/publicite` quarterly report:
- Total ads served vs rejected
- Per-category rejection count (campaign | unsafe-adjacent | brand-unsafe | per-publisher-rule)
- Top-5 rejected advertisers (anonymized aggregates only)
- Per-month trend

## Schema

```
ad_rejections:
  ad_id | reason_category | rejected_at | reviewed_by
```

## Per-rejection editor sign-off

For each rejection in 24h window, lead editor confirms vs operator-set rule. Avoids over-rejection.

## Cross-references

Master plan: S1688. Sister: `docs/GRIMBANEWS_PER_REGION_PARTNER_BRAND_SAFETY_SCORING.md`, `docs/GRIMBANEWS_TRANSPARENCY_REPORT_SCOPE.md`.
