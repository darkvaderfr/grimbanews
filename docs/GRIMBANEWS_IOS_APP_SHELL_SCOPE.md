# GrimbaNews — iOS App Shell Scope

**Status:** plan v0 (no Xcode project; PWA on iOS Safari is the surrogate today)
**Owner:** Nina Patel (Lead FE) builds shell + Steve Jobs (CPO) on iOS HIG compliance + Alex Morgan (UI/UX) on splash/icon
**Walks:** Mythos S1161 (iOS app shell) deferred → partial
**Gating dependency:** Apple Developer ($99/yr) + macOS build runner (CI mac-mini or GitHub Actions macOS) + Capacitor pick locked (S1152)

## Why this exists

S1161 is the iOS-specific scaffold inside the broader S1153 wrapper plan. iOS has HIG quirks (Safe Area, gestures, native nav patterns) that the WebView shell must honor — otherwise Apple review rejects.

## Today's surrogate

- **PWA install** — Safari → Share → Add to Home Screen. Works but:
  - No splash control (uses static apple-touch-icon).
  - No push (iOS PWAs got Web Push only in 16.4+ and many readers haven't updated).
  - No deep linking from other apps.
  - Loads in-Safari chrome (not standalone) unless `apple-mobile-web-app-capable` set.

## Shell scope

| Layer | Implementation |
|---|---|
| Container | Capacitor iOS shell (WKWebView) |
| Splash | `LaunchScreen.storyboard` with brand mark (Alex Morgan asset) |
| Status bar | `StatusBar` plugin — light content on dark background |
| Safe area | CSS `env(safe-area-inset-*)` honored in app shell |
| Nav | WebView handles its own routes; Capacitor `App` plugin handles `backButton` (rarely used on iOS) |
| Pull-to-refresh | `@capacitor-community/pull-to-refresh` |
| Deep link | Universal Links via `apple-app-site-association` on `https://grimbanews.com/.well-known/` |
| Share | `Share` plugin |
| Haptics | `Haptics` plugin on key interactions (vote, save) |

## Apple review preparation

| Common rejection | Mitigation |
|---|---|
| 4.2 — Minimum functionality (WebView-only) | Add native splash, deep links, haptics, push, IAP, offline cache |
| 5.1.1(v) — Account deletion missing | Link to `/account/delete` from in-app settings; flow shipped |
| 2.5.13 — Use Sign in with Apple | Add SIWA button alongside email (lands with S1163 app login) |
| Privacy nutrition label | Pre-fill: data collected = email, in-app activity, identifiers (member_id only) |

## Effort

- Xcode scaffolding via `npx cap add ios`: 0.5 sprint.
- Native shell adjustments (status bar, safe area, splash): 1 sprint.
- Apple review polish (SIWA, deletion link, nutrition label): 2 sprints.
- First TestFlight: 1 sprint.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1161)
- Sister docs: `docs/GRIMBANEWS_MOBILE_APP_PWA_WRAPPER.md`, `docs/GRIMBANEWS_ANDROID_APP_SHELL_SCOPE.md`, `docs/GRIMBANEWS_MOBILE_APP_LOGIN_SCOPE.md`, `docs/GRIMBANEWS_NATIVE_DEEP_LINK_VERIFICATION.md`, `docs/GRIMBANEWS_NATIVE_SIGNING_CERTIFICATES_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
