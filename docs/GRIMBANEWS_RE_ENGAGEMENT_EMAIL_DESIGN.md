# GrimbaNews — Re-engagement Email Design

**Status:** design v0 (no dormant-member detection; new-reader onboarding modal exists for fresh visitors)
**Owner:** Lucy Leai (Strategy) on copy + Steve Jobs (CPO) on UX + Liam Smith (PM) on cadence
**Walks:** Mythos S1297 (re-engagement email — dormant member) + S1298 (in-product re-engagement nudge — dormant reader) deferred → partial
**Gating dependency:** Dormant-member detection (members.last_active_at column + nightly job) + email template + suppression rules. Design itself is operator-side.

## Why this exists

S1297 + S1298 share a root: GrimbaNews has the **new-reader nudge** today (`partials/home/onboarding-modal.blade.php`) but **no dormant-reader path**. Once a member subscribes to the newsletter and then stops opening it, we have no system to re-engage gracefully. This document defines the design so the moment dormant-member detection ships, the re-engagement flow is straight to build.

## Today's surrogate

- `partials/home/onboarding-modal.blade.php` — first-visit nudge for new readers (per S1298 partial).
- `App\Mail\GrimbaVaultDigestMail` — weekly per-member digest (fires regardless of dormancy).
- `App\Mail\GrimbaSavedSearchDigestMail` — weekly per-segment digest.
- **No `members.last_active_at` column.**
- **No dormancy detection.**
- **No re-engagement email template.**

## Dormancy detection

Add `members.last_active_at TIMESTAMP NULL` column (Botble member extension via migration).

Updated on:
- Login.
- Vault save / unsave (per `GrimbaVaultEvents`).
- Email open (gates on tracking pixel S1286 deferred — without pixel, last activity = last login or last save).
- API key use (if `docs/GRIMBANEWS_API_ACADEMIC_TIER_PLAN.md` ships).

Nightly cron `grimba:detect-dormancy` (new) sweeps:
- `members WHERE last_active_at < NOW() - INTERVAL 30 DAY AND re_engagement_sent_at IS NULL`.
- Adds them to re-engagement queue.

## Re-engagement cadence

Three-step cadence over 30 days, then suppress:

| Step | Timing | Trigger condition | Message |
|---|---|---|---|
| Re-engage 1 | day 30 dormant | last_active_at < 30d ago | "We miss you. Here's what you've missed." (top 5 stories matching saved interests) |
| Re-engage 2 | day 60 dormant | no activity since step 1 | "Here's the bias-bar story of the month — the cluster with broadest source spread." |
| Re-engage 3 | day 90 dormant | no activity since step 2 | "Are these still useful?" with subscription-preferences link + easy unsubscribe |

Post-step-3 with no activity: auto-suppress all subsequent re-engagement until member re-activates.

## Template structure

Per-step template at `resources/views/emails/re-engagement/{step}.blade.php`:

**Step 1 — "We miss you" (light):**
- Header: GrimbaNews wordmark + "Welcome back" hook.
- 5 articles matching member's saved categories.
- Bias-bar spotlight on one cluster.
- CTA: "See what's happening now" → `/`.
- Footer: unsubscribe + subscription preferences.

**Step 2 — "Bias-bar story of the month":**
- Header: GrimbaNews wordmark + "A reminder of what GrimbaNews is for."
- Single best-bias-spread cluster of the month (from `App\Support\GrimbaSourceBreakdown` aggregation).
- Brief: why this story matters + how broad-source-spread coverage works.
- CTA: "Read the full dossier" → `/dossier/{id}`.
- Footer: unsubscribe + preferences.

**Step 3 — "Are these still useful?" (honest):**
- Header: "Final question — should we keep sending these?"
- Brief: "We've sent 2 prior emails with no response. We don't want to clutter your inbox."
- 3 buttons: "Keep sending" (resets dormancy), "Send less" (link to preferences), "Unsubscribe" (one-click).
- Footer: explanation of how we handle inactive subscribers (per GDPR data-minimization stance).

## Suppression rules

Don't re-engage if:
- Member already unsubscribed (`unsubscribed_at IS NOT NULL`).
- Member is in re-engagement freeze (`re_engagement_freeze_until > NOW()`).
- Bounce / complaint history (per LeafRelay feedback loop integration).
- GDPR erasure pending.
- Member has < 7 days since signup (let new readers settle in).

## Per-locale

Per `lang/{fr,en}/grimba.php` re-engagement keys. Pattern matches `docs/GRIMBANEWS_PER_REGION_DAILY_DIGEST_CADENCE.md` locale enforcement (FR templates use FR throughout).

## In-product re-engagement nudge (S1298 ship)

Companion to email cadence. When a logged-in dormant member returns and visits any page:

- Lightweight banner (top of page): "Welcome back. Here's what you missed since last visit."
- Click → modal with 5-article re-engagement set (same content as Step 1 email).
- Dismissable (cookie `grimba_dormancy_banner_dismissed`).
- Suppress in reading mode (per `docs/GRIMBANEWS_READING_MODE_DESIGN.md`).

## Privacy posture

- **No tracking pixel for re-engagement** (until S1286 newsletter tracking pixel ships).
- **Suppression list** stored on `members.re_engagement_freeze_until` — gives member explicit control.
- **3-step max cadence** — explicitly capped so we don't become spam.
- **Same unsubscribe semantics** as regular newsletter — one click, persistent.
- **GDPR data-export** includes re-engagement send history per request.

## Measurement (gates on tracking)

Without tracking pixel:
- **Activity-resumption rate** within 7d of each step = success proxy.
- **Unsubscribe rate per step** = anti-pattern detector.

With tracking pixel (S1286 ship):
- Open rate per step.
- CTR per step.
- Activity-resumption rate post-open.

## Engineering effort estimate

- `members.last_active_at` column + backfill: 1 sprint.
- `grimba:detect-dormancy` cron + queue: 1 sprint.
- Per-step email templates (3 templates + base): 3 sprints.
- Suppression rules + bounce-list integration: 2 sprints.
- In-product banner + modal: 2 sprints.
- Per-locale strings + translations: 1 sprint.
- Member preferences page (granular control): 2 sprints.
- Tests + mailpit E2E: 2 sprints.
- **Full ship: ~12 sprints.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1297, S1298; sister S1291-S1300)
- Sister docs: `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`, `docs/GRIMBANEWS_PER_REGION_DAILY_DIGEST_CADENCE.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`, `docs/GRIMBANEWS_READING_MODE_DESIGN.md`, `docs/GRIMBANEWS_API_ACADEMIC_TIER_PLAN.md`
- Existing onboarding: `platform/themes/echo/partials/home/onboarding-modal.blade.php`
- Existing mail: `app/Mail/GrimbaVaultDigestMail.php`, `app/Mail/GrimbaSavedSearchDigestMail.php`
- Vault events: `app/Support/GrimbaVault.php`
- Source-breakdown: `app/Support/GrimbaSourceBreakdown.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
