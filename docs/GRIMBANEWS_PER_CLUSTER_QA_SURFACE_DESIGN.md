# GrimbaNews — Per-Cluster Reader Q&A Surface

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Lucy Leai (Strategy) + per-region editor
**Walks:** Mythos S1145 (per-cluster reader question intake) — sister to S1376 reader notebook
**Gating dependency:** Comment moderation v2 (Wave KKKK partial) + per-cluster editor assignment.

## Why this exists

On a complex dossier (e.g. an unfolding crisis), readers have specific questions. Today they leave site to search elsewhere. A per-cluster Q&A surface would let readers ask + read existing answers without leaving the dossier page.

## v1 design

On `/comparatif/{id}` dossier page, new "Questions de lecteurs" panel:

- Top-3 reader questions on this cluster (most upvoted)
- Editor-answered questions surface to top
- New question form (auth required, mod-queue gates)
- Per-question single-page deep link

## Schema (gates on Vader migration approval)

```
cluster_questions:
  id | cluster_id | author_member_id | question_text | status (pending|published|answered|rejected)
   | created_at | published_at | upvote_count

cluster_question_answers:
  id | cluster_question_id | author_member_id (editor or reader) | answer_text
   | is_editor_answer (bool) | created_at
```

## Moderation cadence

- Per-cluster question goes to mod queue.
- Editor approves / rejects within 48h.
- Approved questions visible to all readers on the dossier page.
- Editor answers within 5 days for "answered" status badge.

## Reader-rights surface

Per-question delete via `/vos-droits` (per Wave KKKK).

## UX touchpoints

- "Avez-vous une question sur ce dossier?" CTA in cluster header.
- Per-question email-notification subscription.
- Per-cluster Q&A RSS feed for editors monitoring.

## Cross-references

Master plan: S1145. Sister: Wave KKKK comment-mod plans, `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`.
