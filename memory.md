# GrimbaNews Memory

**Updated:** 2026-04-29

GrimbaNews is in all-hands pre-production execution. Every contributor and agent should keep work moving in tandem against the sprint registry in `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`.

## Standing Directive

- Always pick the next useful atomic sprint outcome when there is no newer user instruction.
- Work in parallel where ownership is clear: frontend, backend, data, QA, security, editorial, revenue, ops, docs, and support can advance at the same time.
- Coordinate through evidence, not assumptions. Every sprint closeout needs changed files, commit SHA when committed, verification, risks, and next dependency.
- Do not block the whole team on one lane unless a release gate requires it.
- Do not deploy to production until the release gates in the 1000-sprint master plan are green.
- Keep reader-facing AI copy branded as `NobuAI`; never expose provider names on public surfaces.
- Protect unrelated work in the tree. Read `git status` before editing and do not revert changes you did not make.
- Prefer small, reviewable commits tied to sprint IDs.

## Tandem Work Rule

When a contributor finishes one sprint, they should immediately do one of the following:

1. Pick the next unblocked sprint in the same lane.
2. Help verify another lane's completed work.
3. Document evidence, risks, or runbooks needed for a release gate.
4. Surface a blocker with the exact file, route, command, or external dependency involved.

The default posture is forward motion until the pre-production plan is complete.

## Canonical Planning Files

- `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` is the current master sprint registry.
- `docs/GRIMBANEWS_TANDEM_WORK_PROTOCOL.md` defines how all contributors work side by side.
- `docs/GRIMBANEWS_SPRINT_PLAN.md` and `docs/MYTHOS_SPRINT_FLEET.md` remain historical/living reference ledgers.
