# GrimbaNews — Native Release Pipeline Plan

**Status:** plan v0 (no fastlane setup; no CI native-build job)
**Owner:** Jacob Lee (DevOps) builds pipeline + Nina Patel on Capacitor build scripts + Hannah Kim on observability
**Walks:** Mythos S1391 (Native release pipeline CI / fastlane) deferred → partial
**Gating dependency:** Native shell (S1152/S1153) + Apple Developer + Google Play Console + macOS CI runner

## Why this exists

S1391 standardizes how native builds get from `main` → reader devices. Without it, every release is hand-rolled — recipe for missing dSYMs, wrong build numbers, forgotten changelogs.

## Today's surrogate

- **Web GHA → SSH → VPS** — `docs/GRIMBANEWS_GO_LIVE_RUNBOOK.md` for web releases. Same pattern carries to native CI.
- **No native build target.**

## Pipeline stages

### iOS

| Stage | Tool | Output |
|---|---|---|
| Capacitor build | `npx cap sync ios` | iOS project synced with latest web build |
| Xcode archive | fastlane `gym` (macOS runner) | `.ipa` |
| Sign | fastlane `match` (provisioning profiles in private repo) | signed `.ipa` |
| Upload | fastlane `pilot` | TestFlight |
| Symbol upload | fastlane → Sentry CLI | dSYM uploaded to Sentry |
| Notify | fastlane action | Slack `#grimba-mobile` |

### Android

| Stage | Tool | Output |
|---|---|---|
| Capacitor build | `npx cap sync android` | Android project synced |
| Gradle assemble | `./gradlew bundleRelease` | `.aab` |
| Sign | Keystore from CI secrets | signed `.aab` |
| Upload | fastlane `supply` | Play Console Internal Testing |
| Symbol upload | gradle plugin → Sentry CLI | Proguard mapping uploaded |
| Notify | fastlane | Slack |

## Branch model

| Branch | Action |
|---|---|
| `main` | web deploy only (today's behavior) |
| `mobile/main` | iOS + Android internal-testing build per push |
| `mobile/beta/v*` | TestFlight external beta + Play Closed Testing |
| `mobile/release/v*` | production-track rollout (10% → 50% → 100% staged) |

## Versioning

- Marketing version: `1.2.3` synced with `package.json`.
- iOS build number: monotonic integer (`CFBundleVersion`) auto-incremented per CI run.
- Android version code: monotonic integer auto-incremented per CI run.

## Secrets management

- Apple App Store Connect API key (.p8 + key ID + issuer): GitHub Actions secrets.
- Android upload keystore: encrypted in fastlane `match` git repo.
- FCM service account: env (per `docs/GRIMBANEWS_FCM_INTEGRATION_PLAN.md`).
- APNs key: env (per `docs/GRIMBANEWS_APNS_INTEGRATION_PLAN.md`).

## Per-release gates (must pass)

- Lint + typecheck on web bundle.
- Unit tests pass.
- Smoke E2E (web reader paths) green.
- Native bundle size budget: iOS .ipa <50MB, Android .aab <40MB.
- Symbol upload succeeded.
- Changelog entry exists in `CHANGELOG.md`.

## Rollback path

- iOS: cannot rollback a shipped version; mitigate by halting rollout + hotfix.
- Android: phased rollout — halt at any %, push hotfix as new version code.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1391)
- Sister docs: `docs/GRIMBANEWS_NATIVE_SIGNING_CERTIFICATES_PLAN.md`, `docs/GRIMBANEWS_NATIVE_OTA_UPDATES_PLAN.md`, `docs/GRIMBANEWS_NATIVE_CRASH_DASHBOARD_PLAN.md`, `docs/GRIMBANEWS_GO_LIVE_RUNBOOK.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
