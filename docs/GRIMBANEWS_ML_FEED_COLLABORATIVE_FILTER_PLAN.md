# GrimbaNews — ML Feed Collaborative Filter Plan

**Status:** plan v0 (no per-member interaction matrix; current implementation is privacy-first cookie-only)
**Owner:** David Chen (Data Scientist) on model + Benjamin Lee (Data Engineer) on matrix build + Sara Chen on opt-in posture
**Walks:** Mythos S1502 (ML feed — collaborative filter model) deferred → partial
**Gating dependency:** Opt-in reader signal log (per S1501 doc) + ≥10k opted-in members + per-member interaction events for ≥30 days.

## Why this exists

S1502 builds a reader-similarity model: "readers who liked these articles also liked …". Foundational personalization primitive. Cannot exist without opt-in reader-signal log (S1501) — privacy posture is non-negotiable.

## Today's surrogate

- No reader signal matrix. `/pour-vous` runs cookie-only heuristic.

## Matrix construction

```
rows = opted-in members (anonymized hashes)
cols = posts
cell = signal weight {0..1}:
  - clicked: 0.3
  - read >30s: 0.6
  - bookmarked: 0.8
  - shared: 1.0
  - skipped explicitly: -0.5
```

## Model candidates

| Model | Pros | Cons | Fit |
|---|---|---|---|
| Item-item k-NN | Simple, explainable, no GPU | Cold-start hard on new posts | Default v1 |
| Matrix factorization (ALS) | Stronger generalization | Heavier to train | Backup v2 |
| Two-tower neural | Best quality | GPU-required + complex | Deferred to v3 |

## Cold-start handling

- New post: bootstrap with content-based filter from S1344 (article embedding similarity).
- New member: bootstrap with editor-picked + region cookie (fallback to today's path).

## Training cadence

- Daily incremental update (last 24h signals only)
- Weekly full re-train

## Diversity post-filter

- Top-50 candidates from CF
- Diversity guard (per S1501 doc) re-ranks to enforce bias-spread + source-spread

## Acceptance gates

- Top-1 click probability ≥2× random baseline
- Diversity guard never violated
- Cold-start members still get reasonable feed

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1502)
- Sister docs: `docs/GRIMBANEWS_ML_FEED_DESIGN_DOC.md`, `docs/GRIMBANEWS_ML_FEED_AB_HARNESS_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
