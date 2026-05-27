# GrimbaNews — In-House Editor Seat + Role Surrogate Plan

**Sprint ID:** S1401
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1401-s1410 — In-house editor — seat invite + role`
**Walk wave:** CCCC

## Gating dependency

In-house editor seats need:

- An `editor_invitations` + `editor_seats` table
- A role system that scopes admin actions per-editor (today Botble has one admin role)
- Per-seat scoping (regions, topics, draft assignment)
- An invitation + onboarding flow (email + accept-token + first-login walkthrough)
- A pricing / contract decision (paid seats? equity? FT employee?)

## Surrogate-now infra

- **Botble admin auth** — single-tenant + single-role today; ops uses one shared admin
- **`/admin/grimba/rss-drafts`** — drafts queue that an editor program would mirror per-editor
- **`docs/GRIMBANEWS_ADMIN_BACKEND_CLOSEOUT_INDEX.md`** — admin backend close-out scope

## Honest framing

Foundational for the entire S1401-S1450 editorial-program band. Decision-heavy: editor relationship (W-2 vs 1099 vs contributor) drives the schema + payout choices. Operator-side hiring program.

## Owners

- **CEO:** Lucy Leai — editor program go/no-go
- **Marketing:** Henry Walker — editor recruiting + comms
- **HR:** Sophia Martinez — contract framework
- **Backend:** Rajesh Kumar — multi-role auth + scoping
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1401 row)
- Editor program planning: `docs/GRIMBANEWS_PER_AUTHOR_TRUST_BADGE_PROGRESSION.md`
- Contributor intake: S1451 (deferred)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
