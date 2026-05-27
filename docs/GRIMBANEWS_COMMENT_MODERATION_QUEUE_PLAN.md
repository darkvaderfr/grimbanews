# GrimbaNews — Comment Moderation Queue Surrogate Plan

**Sprint ID:** S1363
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370 — Comment moderation queue`
**Walk wave:** CCCC

## Gating dependency

A moderation queue needs comments to exist (S1362). Beyond that:

- A `comment_status` enum (pending / approved / spam / hidden / banned)
- Admin UI for triage (`/admin/grimba/comments`)
- Auto-classify pipeline (NobuAI toxicity / spam scoring as pre-filter)
- Per-moderator audit log (gates on operator-side moderator role)

## Surrogate-now infra

- **`/admin/grimba/rss-drafts`** — internal staff triage UI pattern (already in production) — same shape
- **`docs/GRIMBANEWS_ADMIN_BACKEND_CLOSEOUT_INDEX.md`** — admin backend close-out shape
- **`tests/Feature/GrimbaNobuAiBrandPurityTest`** — proves NobuAI can be used as a classifier (for brand-leak); same pattern for toxicity

## Honest framing

Gates on S1362. Once comments exist, moderation queue is a 2-3 day build (admin index + bulk actions + status enum). The harder work is the policy: hateful-speech taxonomy, EU DSA compliance, ombudsman-equivalent appeals process.

## Owners

- **Editorial:** TBD moderator owner — taxonomy + policy
- **Security:** Maya Patel — DSA / Section 230 / LCEN review
- **Product:** Liam Smith — admin UX
- **Backend:** Rajesh Kumar — status enum + admin actions
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1363 row)
- Comment threading: `docs/GRIMBANEWS_COMMENT_THREADING_PLAN.md`
- Ombudsman pattern: `docs/GRIMBANEWS_OMBUDSMAN_CROSS_LOCALE_INTAKE_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
