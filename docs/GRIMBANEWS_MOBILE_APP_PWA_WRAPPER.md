# GrimbaNews — PWA-to-App-Store Wrapper Plan

**Status:** plan v0 (no native shell; gates on S1152 framework decision)
**Owner:** Nina Patel (Lead FE) builds shell + Jacob Lee (DevOps) wires CI + Steve Jobs (CPO) signs design
**Walks:** Mythos S1153 (PWA-to-app-store wrapper) deferred → partial
**Gating dependency:** Apple Developer account + Google Play Console account (per `GRIMBANEWS_MOBILE_APP_SHELL_PICK.md`) + framework pick locked

## Why this exists

S1153 is the bridge between today's installable PWA and a real app-store presence. Without it, readers can't find GrimbaNews via App Store / Play Store search, and we forfeit the discovery channel + ratings social proof.

## Today's surrogate

- **PWA install banner** — Chrome auto-prompts "Add GrimbaNews to home screen" after 2 visits if engagement criteria met.
- **iOS Safari** — Share → Add to Home Screen (manual, no auto-prompt).
- **manifest.webmanifest** — display: standalone, theme_color matches dark UI.
- **No app-store listing** — search "GrimbaNews" returns zero stores.

## Wrapper approach (assumes Capacitor pick per S1152)

1. `npm i @capacitor/core @capacitor/ios @capacitor/android` in project root.
2. `npx cap init "GrimbaNews" com.grimbanews.app --web-dir=public`.
3. Point `webDir` at production build OR remote URL (`https://grimbanews.com`).
4. `npx cap add ios` + `npx cap add android`.
5. Custom shell wraps the WebView with app-chrome navigation + native splash.

## Native config per platform

| Item | iOS | Android |
|---|---|---|
| Bundle ID | `com.grimbanews.app` | `com.grimbanews.app` |
| Min SDK | iOS 14 | Android 7 (API 24) |
| Splash | `Assets.xcassets/Splash` | `res/drawable/splash.xml` |
| App icon | `Assets.xcassets/AppIcon` | `res/mipmap-*/ic_launcher.png` |
| Deep-link scheme | `grimbanews://` + Universal Links | `grimbanews://` + App Links |

## Shell features

- **Pull-to-refresh** on top of WebView.
- **Native nav bar** with brand color + back/forward.
- **Offline fallback** — leverages existing service worker, plus native cache for app shell.
- **Deep-link routing** — `grimbanews://dossier/123` → opens `/dossier/123`.
- **Native share sheet** — Capacitor's `Share` plugin.

## Build + CI pipeline

- Builds via fastlane on GitHub Actions (CI runner with macOS for iOS).
- Per-PR builds → TestFlight (iOS) / Internal Testing (Android).
- Tagged releases → production rollout.

## Effort estimate

- Capacitor scaffold + first build: 1 sprint.
- Shell UI (nav, splash, deep-link): 2 sprints.
- CI + fastlane: 2 sprints.
- First TestFlight + Internal Testing release: 1 sprint.
- **Total to internal-test: 6 sprints once S1152 locked.**

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1153)
- Sister docs: `docs/GRIMBANEWS_MOBILE_APP_SHELL_PICK.md`, `docs/GRIMBANEWS_IOS_APP_SHELL_SCOPE.md`, `docs/GRIMBANEWS_ANDROID_APP_SHELL_SCOPE.md`, `docs/GRIMBANEWS_MOBILE_APP_LAUNCH_PLAYBOOK.md`, `docs/GRIMBANEWS_NATIVE_RELEASE_PIPELINE_PLAN.md`
- Existing PWA: `public/grimba-sw.js`, `public/manifest.webmanifest`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
