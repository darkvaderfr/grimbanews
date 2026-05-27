# GrimbaNews — Contributor → Editor Handoff Design

**Status:** plan v0
**Owner:** Henry Walker (Editorial) + Liam Smith (PM)
**Walks:** Mythos S1455 (contributor editor-handoff) deferred → partial
**Gating dependency:** S1454 contributor submission portal + S1401 multi-editor workflow + per-editor topic assignment.

## Why this exists

When a contributor submits, the routing decision (which editor reviews) should be deterministic and tracked, not ad-hoc Slack-style assignment.

## v1 routing rules

1. Topic-editor match: if `submission.category` has an assigned topic-editor (S1411), route there.
2. Round-robin fallback: among generalist editors, round-robin by submission timestamp.
3. Workload cap: if editor has > 8 open submissions, skip and re-route.
4. Manual override: head editor can re-assign any submission.

## Handoff event

- Editor sees in-app notification + per-day digest email (not per-submission email — too noisy).
- SLA target: editor first-touch within 5 business days.
- Auto-escalation: if no editor activity in 10 business days, head editor (Henry) gets ping.

## Schema additions

```sql
ALTER TABLE contributor_submissions ADD COLUMN editor_assigned_at TIMESTAMP NULL;
ALTER TABLE contributor_submissions ADD COLUMN first_editor_touch_at TIMESTAMP NULL;
```

## Anti-patterns

- No anonymous reviewer assignment (every submission shows assigned editor name to contributor).
- No silent rejection (a rejected submission gets a reason field, mandatory non-template).

## Cross-references

Master plan: S1455. Sister: S1454 (submission portal), S1401 (multi-editor workflow), S1411 (topic editor).
