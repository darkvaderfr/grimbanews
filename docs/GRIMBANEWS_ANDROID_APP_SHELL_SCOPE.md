# GrimbaNews — Android App Shell Scope

**Status:** plan v0 (no Android Studio project; PWA on Chrome Android is the surrogate today)
**Owner:** Nina Patel (Lead FE) builds shell + Steve Jobs (CPO) on Material You compliance + Alex Morgan on splash/icon
**Walks:** Mythos S1162 (Android app shell) deferred → partial
**Gating dependency:** Google Play Console ($25 one-time) + Capacitor pick locked (S1152) + Android SDK toolchain on CI runner

## Why this exists

S1162 is the Android-specific scaffold. Android's quirks differ from iOS — back gesture, intent system, foreground service for push, edge-to-edge — and Material Design compliance affects Play Store editorial visibility.

## Today's surrogate

- **TWA-via-Chrome** — Trusted Web Activity is a path some PWAs take to ship to Play Store; we've not done it.
- **Plain PWA on Chrome Android** — Add to Home Screen banner appears on engaged sessions.
- **Web Push** — works in Chrome Android once VAPID + service worker `push` handler shipped (gates on S1302/S1303).

## Shell scope

| Layer | Implementation |
|---|---|
| Container | Capacitor Android shell (WebView - Chrome Custom Tabs fallback for external URLs) |
| Splash | `res/drawable/splash.xml` + adaptive icon |
| Edge-to-edge | `WindowCompat.setDecorFitsSystemWindows(false)` + CSS safe area |
| Back gesture | Capacitor `App` plugin `backButton` handler → WebView history.back() |
| Nav bar | Material You-friendly bottom bar (defer to WebView's own nav for v1) |
| Pull-to-refresh | `@capacitor-community/pull-to-refresh` |
| Deep link | App Links via `assetlinks.json` on `https://grimbanews.com/.well-known/` |
| Foreground service for push | Firebase Messaging service in `AndroidManifest.xml` |

## Play Store review preparation

| Common rejection | Mitigation |
|---|---|
| Data safety form mismatch | Pre-declare: email + in-app activity + ID (member_id) |
| Background location not declared | Confirm: zero background location collected |
| Sensitive permissions (READ_MEDIA, CONTACTS) | None declared |
| Account deletion not in-app | Link to `/account/delete` from settings |
| Target API level outdated | Build against latest Android API (currently 35) |

## Build matrix

| Track | Audience | Cadence |
|---|---|---|
| Internal | 5 testers (Vader + Iboga eng) | per commit on `mobile` branch |
| Closed beta | 100 readers from waitlist | weekly |
| Open beta | unlimited opt-in | monthly |
| Production | 100% rollout w/ staged 10/50/100 | per release |

## Effort

- `npx cap add android`: 0.5 sprint.
- Manifest + splash + adaptive icon: 1 sprint.
- Play Console listing setup + data safety form: 1 sprint.
- First Internal Testing release: 1 sprint.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1162)
- Sister docs: `docs/GRIMBANEWS_MOBILE_APP_PWA_WRAPPER.md`, `docs/GRIMBANEWS_IOS_APP_SHELL_SCOPE.md`, `docs/GRIMBANEWS_FCM_INTEGRATION_PLAN.md`, `docs/GRIMBANEWS_NATIVE_DEEP_LINK_VERIFICATION.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
