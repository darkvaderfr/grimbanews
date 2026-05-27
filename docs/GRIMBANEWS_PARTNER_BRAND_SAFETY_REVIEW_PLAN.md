# GrimbaNews — Partner Brand-Safety Review Plan

**Status:** plan v0
**Owner:** Maya Patel (Compliance) + Victor Garcia (BD) + Henry Walker (Editorial)
**Walks:** Mythos S1448 (partner brand-safety review) deferred → partial
**Gating dependency:** operator-side legal pickup + partnership contract template.

## Why this exists

Before each partnership ships, both sides need a brand-safety baseline: what content categories are out of scope, what redactions/exclusions apply, what review cadence catches drift.

## v1 review checklist

| Area | Question |
|---|---|
| Content categories | Which IAB categories are off-limits for this partner? |
| Bias mix | Does partner require balanced-bias-only? |
| Geographic scope | Any regions where partner cannot republish? |
| Topic exclusions | NSFW, violence, partisan-leaning op-eds? |
| Tone | Wire-style only, or includes opinion content? |
| Source allowlist | Some partners only accept sources at factuality ≥ N |
| Drift cadence | Quarterly review of exclusion list |

## Workflow

- Pre-partnership: contract attachment specifies the brand-safety profile.
- At ingest: per-partner content filter rejects out-of-profile items before delivery.
- Quarterly: Maya + Victor + Henry review filter performance + propose adjustments.

## Schema (partner-side filter declaration)

```sql
CREATE TABLE partner_brand_safety (
  partner_id BIGINT PRIMARY KEY,
  excluded_categories JSON,
  required_factuality_min TINYINT DEFAULT 0,
  excluded_source_ids JSON,
  excluded_locales JSON,
  reviewed_at TIMESTAMP NULL,
  reviewer_user_id BIGINT NULL
);
```

## Cross-references

Master plan: S1448. Sister: S1442 (content-share), S1446 (takedown), S1450 (launch retro).
