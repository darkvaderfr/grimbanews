# GrimbaNews — ML Feed Model A/B Harness Plan

**Status:** plan v0 (no A/B engine site-wide; per S1073 honest deferral)
**Owner:** David Chen (Data Scientist) on experiment design + Hannah Kim on infra + Liam Smith on guardrail review
**Walks:** Mythos S1509 (ML feed — model A/B harness) deferred → partial
**Gating dependency:** General A/B engine (S1073) shipped + ML feed live (S1501-S1508).

## Why this exists

S1509 lets a new ranker variant ship to 5% of opted-in readers before global rollout. Without an A/B engine, ranker updates are big-bang releases — risky for editorial trust if the variant tilts diversity guard.

## Today's surrogate

- No A/B. Single global behavior.

## Experiment spec

```
experiment_name: "ml_feed_ranker_v2"
hypothesis: "ALS factorization outperforms item-item kNN on top-1 CTR by ≥5%"
variants:
  control: item-item kNN (current default)
  treatment_a: ALS factorization
allocation:
  control: 90%
  treatment_a: 10%
duration: 14 days OR statistical significance reached
guardrails (hard-stop if breached):
  - diversity_guard_violation_rate increases >2%
  - reader-survey "feels like an echo chamber" >baseline +5%
  - subscription cancellations spike (vs trailing 14d)
metric_primary: top-1 CTR
metric_secondary: time-on-feed, return-7d rate
```

## Allocation key

- Per-member hash of `(member_id_hash + experiment_name)` → stable bucket
- Anonymous members: cookie-set per-visit (variant logs to cookie for analytics)

## Stop conditions

- Auto-stop on guardrail breach (alert + revert)
- Time-stop at end of allocation window
- Stat-stop on Bayesian "treatment >control with >95% prob OR <70% prob"

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1509)
- Sister docs: `docs/GRIMBANEWS_ML_FEED_DESIGN_DOC.md`, `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
