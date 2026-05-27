# GrimbaNews — Transparency Report: Ad Rejections Ledger Plan

**Status:** plan v0
**Owner:** Maya Patel (Compliance) + Olivia Davis (Marketing — ad ops) + Rajesh Kumar (Backend)
**Walks:** Mythos S2007 (annual transparency report — ad rejections + per-category breakdown) deferred → partial
**Gating dependency:** GrimbaAds consent hooks exist (S871) but no rejection log + annual aggregation.

## Why this exists

The annual transparency report (S2001) is incomplete without ad-side data: how many sponsor submissions did we reject, in what categories, why. Reader trust in the publication depends on demonstrating the editorial line is not for sale.

## v1 schema

```sql
CREATE TABLE ad_review_decisions (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  sponsor_slug VARCHAR(64) NOT NULL,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reviewer_user_id BIGINT NULL,
  decision ENUM('accepted', 'rejected', 'revision_requested') NOT NULL,
  decision_at TIMESTAMP NULL,
  reject_category ENUM('off-brand', 'misleading-claims', 'category-excluded', 'consent-non-compliant', 'editorial-conflict', 'other') NULL,
  notes TEXT NULL,
  INDEX idx_decision (decision, decision_at)
);
```

## Annual aggregation

- `grimba:transparency-aggregate-ads` cron writes annual rollup.
- Output: submissions, accepts, rejects, per-category reject counts, top-3 reject reasons.
- Aggregate-only (no per-sponsor public disclosure).

## Cross-references

Master plan: S2007. Sister: S2001 (full transparency report), S871 (consent hooks), S867-S895 (ads pack).
