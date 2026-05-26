# GrimbaNews — Mobile App Onboarding Scope

**Status:** plan v0 (web onboarding-modal.blade.php is the surrogate today)
**Owner:** Steve Jobs (CPO) signs flow + Alex Morgan (UI/UX) on screens + Liam Smith (PM) on metrics + Emma Brown on activation funnel
**Walks:** Mythos S1170 (App onboarding) deferred → partial
**Gating dependency:** Native shell + push opt-in plan (S1310) + for-you scope (S1166)

## Why this exists

S1170 is the first-launch experience. Web has the modal-based onboarding. Native should be three full screens — readers tolerate it once, and category-seeded feeds boost day-7 retention.

## Today's surrogate

- **`onboarding-modal.blade.php`** — single modal on first web visit (cookie-gated).
- **Category seed via cookie** — `prefs_categories`.
- **No push opt-in onboarding** — gates on S1310.

## Native flow (3 screens + dismiss)

### Screen 1 — Welcome

- Brand cinematic (logo + "Read the world less biased" tagline).
- One CTA: "Continue".
- No login required at this stage.

### Screen 2 — Pick categories

- 12 category chips (Politics, Tech, Climate, Health, Economy, World, France, Sports, Culture, Science, Justice, Local).
- Min 3 selections to proceed.
- Writes to native `Preferences` + `prefs_categories` cookie.

### Screen 3 — Push opt-in

- Friendly rationale (NOT iOS system prompt yet).
- "Breaking news only" pill (default ON, sets `topics: ['breaking']`).
- "Daily highlights" pill (default OFF).
- "Maybe later" dismisses without triggering system prompt.
- If "Continue": THEN trigger native push permission prompt.

### Screen 4 (optional) — Account

- "Create account to save articles" — optional, with "Skip" CTA.
- Email + Sign in with Apple buttons.

## Skip-able by default

- Every screen has skip / dismiss.
- Cookie / preference set to skip future onboarding.
- Can re-run from settings: "Walk me through GrimbaNews again".

## Success metric

- 60% complete Screen 2 (category pick).
- 35% accept push (across categories).
- 15% create account during onboarding.
- Day-7 retention for completers ≥ 2x dismissers.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1170)
- Sister docs: `docs/GRIMBANEWS_PUSH_OPTIN_ONBOARDING.md`, `docs/GRIMBANEWS_MOBILE_APP_FORYOU_SCOPE.md`, `docs/GRIMBANEWS_MOBILE_APP_LOGIN_SCOPE.md`
- Existing: `resources/views/onboarding-modal.blade.php`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
