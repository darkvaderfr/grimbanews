# GrimbaNews — Mobile App "For You" Scope

**Status:** plan v0 (web `/pour-vous` + `/for-you` is the WebView surrogate today)
**Owner:** Liam Smith (PM) defines feature scope + Steve Jobs (CPO) on shape + Nina Patel (Lead FE) on native pull-to-refresh + David Chen on signal logging
**Walks:** Mythos S1166 (App for-you) deferred → partial
**Gating dependency:** Native shell + personalization v2 (S1501 deferred) for ML rerank; current pour-vous uses cookie-only signals

## Why this exists

S1166 ports the web for-you experience into the native shell. The web view ships today (cookie-based no PII); native wraps it + adds quality-of-life (pull-to-refresh, infinite scroll smoothness, swipe-to-save haptic).

## Today's surrogate

- **`/pour-vous` (FR)** + **`/for-you` (EN)** — cookie-driven category boost on top of `/` rail logic.
- **No server-side per-member feature vector** — privacy-first per `feedback_nobuai_model_branding.md` design posture.
- **Categories saved in cookie** — `prefs_categories` array.

## Native enhancements

| Enhancement | Implementation |
|---|---|
| Pull-to-refresh | `@capacitor-community/pull-to-refresh` — calls `/pour-vous?fresh=1` |
| Infinite scroll | Existing IntersectionObserver pattern carries into WebView |
| Swipe-to-save | `swiped-events` library + Capacitor Haptics on success |
| "Tell us what you like" sheet | Native modal over WebView on first-launch (3-tap category seed) |
| Skeleton loaders | Existing skeleton CSS works in WebView |

## Cookie → native preferences bridge

App preference UI writes to two places:
1. Existing `prefs_categories` cookie (drives WebView).
2. Native `Preferences` API (drives splash + offline-cache prefetch decisions).

Sync on app foreground: `Preferences.get()` → POST to `/api/preferences/sync` → ensures cookie matches.

## What this is NOT (defer-list)

- **Not ML feed.** ML feed gates on S1501 (no per-member behavior log server-side).
- **Not push trigger.** Push for new for-you items gates on S1175 push categories.
- **Not paid-tier upgrade prompt.** Gates on S1261 paid tier.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1166)
- Sister docs: `docs/GRIMBANEWS_MOBILE_APP_READER_SCOPE.md`, `docs/GRIMBANEWS_PERSONALIZATION_V2_LAUNCH_PLAYBOOK.md`, `docs/GRIMBANEWS_PUSH_CATEGORIES_GOVERNANCE.md`
- Existing routes: `/pour-vous`, `/for-you`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
