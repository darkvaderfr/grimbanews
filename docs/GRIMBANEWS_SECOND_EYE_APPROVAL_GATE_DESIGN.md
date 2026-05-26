# GrimbaNews — Second-Eye Approval Gate Design

**Status:** plan v0 (no two-step approval gate; solo-operator publishes today)
**Owner:** Liam Smith (PM) on workflow + Rajesh Kumar (Backend) on state machine + Steve Jobs (CPO) on UX
**Walks:** Mythos S1422 (Second-eye approval gate) deferred → partial
**Gating dependency:** In-house editor seat program (S1401 deferred) ships first — needs ≥2 editorial seats before a "second eye" exists.

## Why this exists

S1422 requires a draft to receive sign-off from a second editor before publish. Today the operator runs solo, so a "second eye" cannot be enforced — there is no second editor. The state machine + UI surface can be designed now so they're drop-in once S1401 (multi-editor seats) ships.

## Today's surrogate

- **`/admin/grimba/rss-drafts`** queue is operator-reviewed manually before promote.
- **Botble post `status` enum** (draft / pending / published) is the substrate.

## State machine v0

```
draft → submitted_for_review → approved → published
                ↘ rejected → draft (with reviewer note)
```

- `submitted_for_review` blocks publish action; only an editor other than `author_id` can transition to `approved`.
- `posts.reviewed_by` + `posts.reviewed_at` + `posts.review_note` (new columns, deferred with S1401).

## UI surface (deferred)

- Author view: "Submit for review" button replaces "Publish" once seat program lands.
- Reviewer view: `/admin/grimba/review-queue` lists pending; approve / reject inline.
- Conflict guard: same-author cannot self-approve (enforced server-side).

## Solo-operator escape valve

When only one editor seat is filled, allow `solo_publish = true` flag on `users` row to skip second-eye gate. Default off post-S1401.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1422)
- Sister docs: `docs/GRIMBANEWS_DISPUTE_ESCALATION_WORKFLOW.md`, `docs/GRIMBANEWS_INHOUSE_EDITOR_LAUNCH_RETRO_PLAN.md`
- Existing infra: `/admin/grimba/rss-drafts`, Botble `posts.status`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
