# GrimbaNews — Mobile App Login Scope

**Status:** plan v0 (web auth shipped via Botble member; native flow not yet wrapped)
**Owner:** Nina Patel (Lead FE) wires WebView auth + Sara Chen (CISO) on token-storage posture + Rajesh Kumar (Backend) on native-friendly session endpoints
**Walks:** Mythos S1163 (App login) deferred → partial
**Gating dependency:** Native shell (S1161/S1162) + SIWA enabled for iOS App Store compliance (per S1161 review prep)

## Why this exists

S1163 is the auth path inside the native shell. Naive WebView reuse works for read-only browsing but breaks once login involves third-party OAuth (redirects out of WebView lose session cookies) or Sign in with Apple (requires native SDK).

## Today's surrogate

- **`/account` web auth** — Botble's member auth (session cookies + CSRF).
- **No SIWA** — would fail Apple review if app shipped without it.
- **No Google OAuth** — same WebView redirect-loss risk.

## Native auth approach

| Flow | Native implementation | WebView session handoff |
|---|---|---|
| Email + password | Native form posts to `/api/auth/login`, receives session cookie | `CookieManager` injects cookie into WebView |
| Sign in with Apple | `@capacitor-community/apple-sign-in` plugin → identity token | POST to `/api/auth/siwa` exchanges for session |
| Google OAuth | `@codetrix-studio/capacitor-google-auth` plugin → ID token | POST to `/api/auth/google` exchanges for session |

## Required new server endpoints (Rajesh Kumar)

- `POST /api/auth/login` — JSON email + password → session cookie + member JSON.
- `POST /api/auth/siwa` — identity token → upsert member by Apple sub claim → session.
- `POST /api/auth/google` — ID token → upsert member by Google sub claim → session.
- `POST /api/auth/logout` — invalidate session.
- `POST /api/auth/me` — return current member or 401.

## Token / session posture (Sara Chen)

- Session cookies HttpOnly + Secure + SameSite=Strict.
- For native: store session-cookie value in iOS Keychain / Android Keystore (NOT WebView default cookie store on logout-cleanup).
- Refresh: cookie auto-extended on each `/api/auth/me`.
- SIWA / Google IDs stored as `members.apple_sub`, `members.google_sub` — UNIQUE indexed.

## Defer-list (downstream gates)

- Sign in with Email magic link (no SMTP code yet at the application API).
- Passkeys / WebAuthn — gates on browser+native WebAuthn parity.
- 2FA / TOTP — operator-side, gates on member profile.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1163)
- Sister docs: `docs/GRIMBANEWS_IOS_APP_SHELL_SCOPE.md`, `docs/GRIMBANEWS_ANDROID_APP_SHELL_SCOPE.md`, `docs/GRIMBANEWS_MOBILE_APP_READER_SCOPE.md`, `docs/GRIMBANEWS_MOBILE_APP_ONBOARDING_SCOPE.md`
- Existing web auth: Botble member auth + `/account` views
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
