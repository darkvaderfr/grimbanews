# GrimbaNews — Push Opt-In Onboarding Flow

**Status:** plan v0 (no push UI; pre-prompt rationale not built)
**Owner:** Steve Jobs (CPO) signs flow + Liam Smith (PM) on opt-in target + Alex Morgan (UI/UX) on rationale screen + David Chen on opt-in funnel
**Walks:** Mythos S1310 (Push opt-in onboarding) deferred → partial
**Gating dependency:** Push infra (S1154) + native shell (S1152/S1153) + onboarding flow (S1170)

## Why this exists

S1310 is the make-or-break moment — once a reader taps "Don't Allow" on the iOS system prompt, the only path back is Settings → app → Notifications → enable. Industry default: 80% of "ask immediately on launch" apps lose the push channel forever.

## Today's surrogate

- **Web push not enabled** — service worker push handler not present.
- **Email opt-in only** — newsletter signup at `/newsletter`.

## Two-stage opt-in

### Stage 1 — pre-prompt (in-app, customizable)

Trigger: 3rd session OR clicking "Notify me when this is updated" on a dossier.

Screen:
```
Stay in the loop                                 ┓
                                                 ┃
Be the first to know when a story you care      ┃
about gets a new perspective.                   ┃
                                                 ┃
We respect your time:                            ┃
  · Maximum 3 notifications per day              ┃
  · Quiet between 11 PM and 7 AM                 ┃
  · You pick the categories                      ┃
                                                 ┃
[ Maybe later ]              [ Continue ]        ┛
```

If "Maybe later": no system prompt, ask again after 14 days.

### Stage 2 — system prompt (iOS / Android native)

Triggered ONLY if Stage 1 → "Continue".

OS-level system dialog appears: "GrimbaNews would like to send you notifications".

If granted: token registered via FCM/APNs → `push_tokens` row → `topics_subscribed = ['breaking']` (default-low).

If denied: graceful — show "OK, we'll just be on the web" toast. Pre-prompt re-shows after 30 days.

## Subsequent category opt-ins

After Stage 2 acceptance, drop reader at `/account/notifications` with chip-based category picker (per `docs/GRIMBANEWS_PUSH_CATEGORY_PREFERENCES_DESIGN.md`).

## Funnel metrics (David Chen)

| Step | Target | Drop-off ok |
|---|---|---|
| Pre-prompt shown | 100% of trigger | NA |
| Pre-prompt → Continue | >55% | 30% bail |
| System prompt → Allow | >75% | of "Continue" cohort |
| Net token capture | >40% of total app users | comparison vs ask-immediately benchmark of 15% |

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1310)
- Sister docs: `docs/GRIMBANEWS_MOBILE_APP_ONBOARDING_SCOPE.md`, `docs/GRIMBANEWS_PUSH_CATEGORIES_GOVERNANCE.md`, `docs/GRIMBANEWS_PUSH_CATEGORY_PREFERENCES_DESIGN.md`, `docs/GRIMBANEWS_MOBILE_PUSH_INFRA_SCOPE.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
