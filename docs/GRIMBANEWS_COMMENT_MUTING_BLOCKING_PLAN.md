# GrimbaNews — Comment Muting / Blocking Surrogate Plan

**Sprint ID:** S1367
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370 — Comment muting / blocking`
**Walk wave:** CCCC

## Gating dependency

Per-reader mute/block needs:

- Comments to exist (S1362)
- Authenticated reader identity surfaced in the comment renderer
- `reader_mutes` table with `(muter_id, muted_id)` unique
- Render-time filter that drops muted authors
- Optional per-thread mute (different from per-author)

## Surrogate-now infra

- **`tests/Feature/SecurityHeadersTest`** — pattern for render-time filter discipline
- **`docs/GRIMBANEWS_PER_CLUSTER_READER_NOTES_DESIGN.md`** — proves per-reader-state UI design pattern

## Honest framing

Standard social-feature pattern; cheap to ship after S1362 (1-2 day build). Decision: do mutes hide the author entirely or only on this thread? Most platforms ship author-level mutes.

## Owners

- **Product:** Liam Smith — mute scope (thread vs author vs site)
- **Backend:** Rajesh Kumar — mutes table + filter
- **Security:** Maya Patel — anti-harassment escalation tie-in
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1367 row)
- Comment threading: `docs/GRIMBANEWS_COMMENT_THREADING_PLAN.md`
- Anti-harassment: `docs/GRIMBANEWS_ANTI_HARASSMENT_COC_ENFORCEMENT_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
