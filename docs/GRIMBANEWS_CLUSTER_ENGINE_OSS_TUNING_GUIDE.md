# GrimbaNews — Cluster Engine OSS Tuning Guide

**Status:** plan v0
**Owner:** David Chen (Data) + Rajesh Kumar (Backend) + Michael O'Connor (Tech Writer)
**Walks:** Mythos S2073 (cluster engine OSS release — title-similarity threshold tuning guide) deferred → partial
**Gating dependency:** S2071 schema-neutral cluster-engine port + published tuning fixtures.

## Why this exists

The cluster engine's title-similarity threshold is the single biggest knob in the pipeline. Without a tuning guide, OSS adopters set it arbitrarily, then complain when 60% of their clusters are noise.

## v1 guide outline

| Section | Content |
|---|---|
| What the threshold controls | Cosine similarity over normalized token vectors; below = no merge, above = merge candidate |
| Default value | 0.78 (Grimba production; reasoning: F1-optimized on hand-labeled 500-cluster set) |
| Range typical | 0.65 (loose merge, news-aggregator style) to 0.85 (strict merge, archive-grade) |
| Adjust if too-many-clusters | Lower threshold; expect more orphans → look at merge-confirmation rate |
| Adjust if too-few-clusters | Raise threshold; watch for "kitchen sink" clusters mixing unrelated stories |
| Per-locale recommended | FR + EN: 0.78; agglutinative languages may want 0.72; CJK: re-tokenize first |
| Per-topic recommended | Politics: 0.80 (tight); Sports: 0.75 (broader) |

## Tuning fixtures

- 500-input hand-labeled set published in `/fixtures/cluster-tuning/`.
- Per-input expected cluster id.
- F1, precision, recall reported per threshold step (0.65 → 0.90 in 0.01 increments).

## Anti-patterns

- No "one-size threshold."
- No threshold tuning without per-corpus fixture.
- No relying on default-default without measuring.

## Cross-references

Master plan: S2073. Sister: S2071 (algorithm extraction), S2074 (orphan cleanup), S2075 (bias-diversity scoring), S2080 (joint launch retro).
