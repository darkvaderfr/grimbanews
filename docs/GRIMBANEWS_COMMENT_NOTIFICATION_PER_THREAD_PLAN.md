# GrimbaNews — Comment Notification (Per-Thread) Surrogate Plan

**Sprint ID:** S1366
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370 — Comment notification (per-thread)`
**Walk wave:** CCCC

## Gating dependency

Per-thread comment notifications need:

- Comments + threading (S1362)
- A `comment_subscriptions` table (per-reader, per-thread, opt-in)
- A reply-detection job that fans out emails
- Unsub tokens per CAN-SPAM
- Rate-limit (no more than N per day per reader)

## Surrogate-now infra

- **`newsletter_subscriptions.unsubscribe_token`** — unsub-token pattern ready to mirror
- **`grimba:saved-search-digests`** — per-recipient send pattern with rate limit
- **`tests/Feature/SavedSearchAlertsTest`** — locks the fan-out contract pattern

## Honest framing

Trivial after S1362 ships — same digest job framework. Decision-heavy: opt-in vs opt-out, batch vs realtime, in-app vs email.

## Owners

- **Product:** Liam Smith — opt model + frequency
- **Backend:** Rajesh Kumar — subscription table + fan-out job
- **Marketing:** Henry Walker — template copy
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1366 row)
- Comment threading: `docs/GRIMBANEWS_COMMENT_THREADING_PLAN.md`
- Saved search digests: `app/Console/Commands/GrimbaSavedSearchDigests.php`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
