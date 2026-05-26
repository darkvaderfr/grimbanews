# GrimbaNews — Mobile Crash Reporting Scope

**Status:** plan v0 (no Crashlytics/Sentry account for native; web Sentry per `GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md` is the partial-shipped sister)
**Owner:** Jacob Lee (DevOps) on integration + Hannah Kim (Platform) on alerting + Sara Chen (CISO) on PII redaction policy
**Walks:** Mythos S1158 (App crash reporting) deferred → partial
**Gating dependency:** Native shell shipped (S1152/S1153) + Sentry org seat OR Firebase Crashlytics enabled

## Why this exists

S1158 closes the production-visibility gap on native. Web has the surrogate at `GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md` (planned, not yet shipped). Mobile crash data lives nowhere without an SDK.

## Today's surrogate

- **Web-side Sentry plan** — `docs/GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md` (not yet integrated).
- **Laravel logs** — `storage/logs/laravel.log` cover server-side errors.
- **Browser console** — only visible to the user with the crash; never sent.

## Vendor option matrix

| Vendor | Native iOS | Native Android | Web | Source maps | Cost (early stage) |
|---|---|---|---|---|---|
| Sentry | yes | yes | yes (existing plan) | yes | free <5k events/mo, then $26/mo |
| Firebase Crashlytics | yes | yes | no | partial | free unlimited |
| Bugsnag | yes | yes | yes | yes | $59/mo+ |

**Recommendation:** Sentry — single pane for web + mobile, source-map upload, existing web plan ready to extend.

## Integration scope

1. Add `@sentry/capacitor` (assuming Capacitor pick) to mobile shell.
2. Set DSN per env (`SENTRY_DSN_IOS`, `SENTRY_DSN_ANDROID`, `SENTRY_DSN_WEB`).
3. Upload symbol files (iOS dSYM, Android Proguard maps) on each release via CI.
4. Per-release tagging — release version pulled from `package.json` + native version code.

## PII redaction policy (Sara Chen)

- Replace breadcrumbs containing email regex with `[REDACTED_EMAIL]`.
- Replace `Authorization: Bearer ...` headers with `[REDACTED_AUTH]`.
- Drop event if breadcrumb URL matches `/api/auth/*`.
- No raw user input captured (e.g., comment field text not sent on crash).

## Alerting rules (Hannah Kim)

| Trigger | Threshold | Channel |
|---|---|---|
| New crash signature (release X) | first occurrence | Slack #grimba-platform |
| Crash rate > 0.5% sessions (24h) | sustained 1h | Slack #grimba-on-call |
| Specific symbol regression | baseline +50% | Slack #grimba-on-call |
| Symbol upload missing (release) | per release | CI job fails build |

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1158)
- Sister docs: `docs/GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md`, `docs/GRIMBANEWS_MOBILE_APP_ANALYTICS_SCOPE.md`, `docs/GRIMBANEWS_NATIVE_CRASH_DASHBOARD_PLAN.md`, `docs/GRIMBANEWS_VENDOR_REGISTER.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
