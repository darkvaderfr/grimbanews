# GrimbaNews — Ad-Tech-Controlled Source Flag Plan

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Sara Chen (CISO)
**Walks:** Mythos S1065 (ad-tech-controlled-source flag) deferred → partial
**Gating dependency:** Operator metadata column + initial ownership audit.

## Why this exists

Some sources are owned/controlled by ad-tech / programmatic-advertising firms (e.g. publisher acquisitions by Taboola, Outbrain, RevContent-affiliated networks). These often skew toward clickbait and degraded editorial standards. Readers should see this in the source-page badge taxonomy.

## v1 design

New column on `news_sources`:

```
ad_tech_controlled BOOLEAN DEFAULT FALSE
ad_tech_parent_org VARCHAR(128) NULL
ad_tech_review_at TIMESTAMP NULL
```

Operator-curated. No auto-detection in v1 (research surface too noisy).

## UX

On source-page badge stack:

- If `ad_tech_controlled = true`, render a "Réseau publicitaire: <parent_org>" badge between ownership-type and bias badges.
- Cross-link to methodology page explaining what this means for readers.

## Editorial criteria

Operator includes a source in this list when:
1. The publisher is materially owned (≥ 30%) by an ad-tech firm whose primary business is programmatic advertising delivery (not content production).
2. The publisher has measurably degraded editorial standards post-acquisition (e.g. fact-check rate drop, listicle dominance).
3. Independent press-watchdogs (Press Gazette, NiemanLab) have flagged the relationship.

## Onboarding

For each source, operator:
1. Reviews ownership filings.
2. If ad-tech-controlled, flips flag + populates parent_org.
3. Logs rationale in `news_sources.review_log`.
4. Re-review at 6 months.

## Cross-references

Master plan: S1065. Sister: state-owned-media flag (S1066, already partial via `ownership_type='state'`).
