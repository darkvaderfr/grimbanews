# GrimbaNews — Per-Author Trust Badge Progression

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Henry Walker (Editorial) + Maya Patel (Compliance)
**Walks:** Mythos S1313 (byline system — in-house author trust badge) deferred → partial
**Gating dependency:** S1411 author/byline system + `authors.trust_tier` enum + editorial review SOP.

## Why this exists

Once GrimbaNews ships in-house bylines (S1411), readers need a way to gauge author credibility at a glance. The badge progression rewards demonstrated rigor (sourcing depth, corrections rate, peer review) — not output volume.

## v1 progression

| Tier | Badge | Criteria |
|---|---|---|
| 1 | Contributeur | First published article, signed editorial code-of-conduct |
| 2 | Vérifié | 5+ published articles, 0 unresolved corrections, peer-review on 3+ pieces |
| 3 | Référence | 25+ articles, < 2% correction rate, cited externally by IFCN-signatory outlet OR cross-published by ≥1 partner outlet |
| 4 | Doyen | 100+ articles, 5+ years tenure, ombudsman recommendation |

## Schema

```sql
ALTER TABLE authors ADD COLUMN trust_tier TINYINT DEFAULT 1;
ALTER TABLE authors ADD COLUMN trust_tier_at TIMESTAMP NULL;
ALTER TABLE authors ADD COLUMN trust_review_log JSON NULL;
```

## Promotion / demotion process

- Promotion: editorial board (Lucy + Henry + at-least-one-peer Doyen) signs off.
- Demotion: corrections rate spike or unresolved factual dispute → editor places author back one tier; appealable to ombudsman (gates on S2021).
- All transitions written to `trust_review_log` with reviewer IDs + ISO date.

## UX

- Author profile page (`/auteur/{slug}`) shows current badge + tenure date.
- Article bylines show micro-badge inline.
- Methodology page links to badge criteria.

## Anti-patterns

- No automatic AI-driven promotions.
- No reader-vote influence on tier.
- No badge purchase / advertiser influence.

## Cross-references

Master plan: S1313. Sister: S1411 (author system), S1418 (author analytics), S2021 (ombudsman charter), S1591+ (corrections workflow).
