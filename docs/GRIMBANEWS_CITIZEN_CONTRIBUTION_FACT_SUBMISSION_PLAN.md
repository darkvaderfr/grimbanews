# GrimbaNews — Citizen Contribution: Fact Submission

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Sara Chen (CISO) + per-region editor
**Walks:** Mythos S1632 (citizen contribution: per-cluster fact submission with review) deferred → partial
**Gating dependency:** Comment moderation v2 + per-cluster editor assignment.

## Why this exists

Readers often have first-hand knowledge of a story (eyewitness, expert, professional). Capturing this enriches dossiers AND grows reader engagement. But requires moderation to keep quality + avoid disinformation.

## Schema

```
cluster_fact_submissions:
  id | cluster_id | submitter_member_id | fact_text | evidence_link
   | status (pending|approved|rejected) | reviewed_by | reviewed_at
```

## Review cadence

- Submissions in `pending` go to per-region editor mod queue.
- Editor approves only if:
  - Evidence link supports claim
  - Submitter is verified (Trust Project indicator or named expert)
  - Fact materially enriches the cluster
- Approved facts surface in cluster's "Contributions des lecteurs" panel with submitter credit.

## Anti-abuse

- Rate-limit: max 5 submissions per user per day.
- Per-IP throttle.
- Anonymous submissions accepted but lower priority in mod queue.

## Cross-references

Master plan: S1632. Sister: `docs/GRIMBANEWS_PER_CLUSTER_QA_SURFACE_DESIGN.md`, Wave KKKK comment moderation pack.
