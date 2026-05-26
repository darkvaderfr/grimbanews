# GrimbaNews — Mobile App Shell Pick (RN vs Flutter vs Capacitor)

**Status:** plan v0 (no native shell shipped; PWA-on-Safari + Chrome-Android is the surrogate today)
**Owner:** Steve Jobs (CPO) drives the pick + Nina Patel (Lead FE) on web/native bridge + Jacob Lee (DevOps) on CI/CD + Larry Ellison on persisted-cache schema
**Walks:** Mythos S1152 (RN vs Flutter vs Capacitor pick) deferred → partial
**Gating dependency:** Vader sign-off on framework + dev-bandwidth commitment (no Flutter / RN engineer on roster) + Apple Developer ($99/yr) + Google Play Console ($25 one-time)

## Why this exists

S1152 is the framework decision that gates the whole S1151-S1180 mobile band. Until the pick is on paper, S1153 (PWA wrapper), S1161 (iOS shell), S1162 (Android shell) all sit deferred. Today's surrogate is the PWA at `public/grimba-sw.js` + `public/manifest.webmanifest` — installable from Safari / Chrome but no store presence.

## Today's surrogate

- **PWA install** — Add to Home Screen on iOS Safari + Chrome Android.
- **Service worker** at `public/grimba-sw.js` — caches `/` shell + `/blog/*` reads.
- **Manifest** at `public/manifest.webmanifest` — name, icons, theme color.
- **Cover ~70% of mobile reader needs.** Gaps: no push, no app-store discovery, no IAP, no native deep-link.

## Framework comparison

| Criterion | Capacitor | React Native | Flutter |
|---|---|---|---|
| Reuse current Blade+JS code | High (WebView shell) | Medium (rewrite UI in RN components) | Low (rewrite in Dart) |
| Web team learning curve | Low | Medium | High |
| Native module ecosystem | Smaller | Largest | Large + Google-backed |
| Push notification support | Via `@capacitor/push-notifications` | Via `@react-native-firebase/messaging` | Via `firebase_messaging` |
| Bundle size | Larger (WebView + JS) | Medium | Medium |
| Cost to maintain (no native engineer) | LOW — Nina + Lisa can ship | HIGH — needs RN-focused hire | HIGH — needs Dart hire |
| Time to first store submission | 2-3 weeks | 8-12 weeks | 8-12 weeks |

## Recommendation

**Capacitor** — leverages the existing Blade + Tailwind UI as the WebView, lets Nina Patel + Lisa Nguyen ship without hiring. Native features (push, IAP, deep-link) added via Capacitor plugins.

## Gating costs

| Item | Owner | Cost | Cadence |
|---|---|---|---|
| Apple Developer | Larry / Ray | $99 USD | annual |
| Google Play Console | Larry / Ray | $25 USD | one-time |
| App icon + screenshot assets | Alex Morgan | dev-time only | once |
| Capacitor install + scaffold | Nina Patel | 1 sprint | once |
| FCM project + APNs key | Jacob Lee | 1 sprint | once |

## Decision deadline

Vader sign-off required before S1153 (PWA-to-app-store wrapper) ships. Until then, S1152 is **planned** (this doc), not chosen.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1152 row)
- Sister docs: `docs/GRIMBANEWS_MOBILE_APP_PWA_WRAPPER.md`, `docs/GRIMBANEWS_IOS_APP_SHELL_SCOPE.md`, `docs/GRIMBANEWS_ANDROID_APP_SHELL_SCOPE.md`, `docs/GRIMBANEWS_MOBILE_PUSH_INFRA_SCOPE.md`, `docs/GRIMBANEWS_MYTHOS_S1101_S1200_I18N_MOBILE_EVIDENCE.md`
- Existing PWA: `public/grimba-sw.js`, `public/manifest.webmanifest`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
