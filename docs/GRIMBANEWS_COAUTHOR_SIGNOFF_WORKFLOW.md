# GrimbaNews — Co-Author Signoff Workflow

**Status:** plan v0 (single-author posts only; no co-author primitive)
**Owner:** Liam Smith (PM) on workflow + Rajesh Kumar (Backend) on schema + Steve Jobs (CPO) on UX
**Walks:** Mythos S1409 (In-house editor — collaboration / co-author signoff) deferred → partial
**Gating dependency:** Journalist table (S1411 author schema doc shipped) + multi-editor seats (S1401).

## Why this exists

S1409 supports multi-author bylines (e.g., investigative pieces, region-X + region-Y co-coverage). Today every post has a single `author_id`. The `post_journalists` join table from the author schema doc is the substrate but no signoff workflow exists.

## Today's surrogate

- **Single `posts.author_id`** + Botble polymorphic author.
- All multi-author content credits the lead author in copy ("with reporting by …") manually.

## Workflow (target)

```
draft created by author A
  → author A invites co-author B (via co-author picker UI)
  → B accepts → post_journalists row {post_id, journalist_id: B, role: 'co-author'}
  → B can edit draft (RBAC: co-author scope)
  → either author can mark "ready for review"
  → both must mark "signed-off" before publish
  → byline renders A, B (order = byline_order from post_journalists)
```

## Conflict resolution

- If A and B disagree on a clause:
  - Inline diff view (CodeMirror compare) shows both versions
  - Editor-in-chief breaks tie
  - Dispute log appended to `editorial_disputes` (S1423 dependency)

## RBAC notes (Sara Chen sign-off)

- Co-author can edit body + add `corrections.original_text` proposals
- Co-author CANNOT change `posts.author_id` (lead), `posts.published_at`, or remove other co-authors
- Editor-in-chief can override any of the above

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1409)
- Sister docs: `docs/GRIMBANEWS_AUTHOR_TABLE_SCHEMA.md`, `docs/GRIMBANEWS_SECOND_EYE_APPROVAL_GATE_DESIGN.md`
- Existing infra: Botble `posts.author_id` polymorphic
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
