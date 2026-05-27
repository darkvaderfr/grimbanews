# GrimbaNews — Cancellation Flow Surrogate Plan

**Sprint ID:** S1268
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Cancellation flow`
**Walk wave:** CCCC

## Gating dependency

A cancellation flow needs:

- Stripe install (S1261)
- A cancellation-reason intake (selectable + free-text)
- A save-offer step (pause / downgrade / discount)
- End-of-period vs immediate-cancel decision (Stripe `cancel_at_period_end: true` is the default)
- Final confirmation + post-cancel comms

## Surrogate-now infra

- **Stripe Customer Portal** — out-of-box cancel button (no save-offer step)
- **`docs/GRIMBANEWS_PER_READER_ANTI_CHURN_RESCUE_OFFER.md`** — proposed rescue-offer doc already specs the save-offer rail
- **`/account` placeholder** — slot for custom cancel surface
- **`newsletter_subscriptions.unsubscribe_token`** — pattern for token-based off-app cancel links (CAN-SPAM analog)

## Honest framing

The portal button works today (post-S1261); the *retention-aware* cancel flow (with save-offer, reason capture, and pause-instead nudge) is a 1-2 week custom build that materially impacts churn.

## Owners

- **Product:** Liam Smith — flow design (steps, copy, save-offer ladder)
- **Customer success:** Emma Brown — save-offer playbook + reason taxonomy
- **Backend:** Rajesh Kumar — webhook handling + reason persistence
- **Data:** David Chen — reason-code analytics
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1268 row)
- Rescue offer: `docs/GRIMBANEWS_PER_READER_ANTI_CHURN_RESCUE_OFFER.md`
- Pause/resume: `docs/GRIMBANEWS_SUBSCRIPTION_PAUSE_RESUME_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
