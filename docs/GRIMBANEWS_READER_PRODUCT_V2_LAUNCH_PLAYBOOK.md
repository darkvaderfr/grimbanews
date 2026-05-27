# GrimbaNews — Reader Product V2 Launch Playbook

**Status:** plan v0
**Owner:** Liam Smith (PM) + Steve Jobs (Design) + Lucy Leai (Strategy)
**Walks:** Mythos S1380 (reader product v2 launch) deferred → partial
**Gating dependency:** S1371-S1379 (annotation set, notebook, per-cluster notes, NobuAI export).

## Why this exists

Reader product v2 is the bundled launch of the annotation + notebook + insight-export trio. Without a coordinated launch, individual features ship as orphans and reader uptake is poor.

## T-minus checklist

| Phase | Step | Owner |
|---|---|---|
| T-14d | Confirm S1371-S1379 all merged + passing tests | Sara Kim |
| T-10d | Design QA pass on all v2 surfaces | Steve / Alex Morgan |
| T-7d | Henry drafts launch newsletter (FR + EN) | Henry Walker |
| T-7d | Maria queues social rollout (3 tweet thread + 1 IG carousel) | Maria Lopez |
| T-5d | Methodology page updated with notebook + annotation docs | Michael O'Connor |
| T-3d | Beta-cohort invite (50 vault-heavy readers) | Liam |
| T-1d | Feature-flag flip + smoke test on staging | Jacob Lee |
| T-0 | Public release toggle | Liam / Steve |
| T+1 | Day-1 review (signups, first-note rate, error logs) | Liam / David Chen |
| T+7 | Day-7 review (retention, notes-per-reader, cluster-overlap heatmap) | Liam / David Chen |
| T+30 | Retrospective + v2.1 backlog grooming | Liam |

## Success metrics

- ≥ 8% of weekly-active readers create at least one notebook within 30 days.
- ≥ 3 notebook entries per active notebook user / 30 days.
- < 0.5% support tickets from v2 surfaces.

## Rollback plan

Feature flag `grimba_reader_v2_enabled` defaults true at launch; flip to false if error rate > 1% or p95 latency on notebook-fetch > 600ms.

## Cross-references

Master plan: S1380. Sister: S1371-S1379. Memory: `feedback_steve_design_language.md`, `feedback_reinvent_not_reskin.md`.
