# GrimbaNews — API v2 OAuth Client Plan

**Status:** plan v0 (no Sanctum/Passport install)
**Owner:** Rajesh Kumar (Backend) implements + Sara Chen (CISO) signs scope model + Larry Ellison on tokens schema
**Walks:** Mythos S1182 (OAuth client) deferred → partial
**Gating dependency:** Sanctum vs Passport pick + first commercial partner ask (drives 3-legged-vs-2-legged decision)

## Why this exists

S1182 enables third-party apps to act on behalf of a GrimbaNews member (vs simple API keys which act as the operator). Required if any partner builds a product where members log in via GrimbaNews credentials.

## Today's surrogate

- **No OAuth** today.
- **API keys** plan (in S1234) covers the 2-legged case (operator-to-operator).
- **Sign in with Apple / Google** at member level (per `GRIMBANEWS_MOBILE_APP_LOGIN_SCOPE.md`) — these are inbound, not outbound OAuth.

## Sanctum vs Passport pick

| Criterion | Sanctum | Passport |
|---|---|---|
| Token type | opaque bearer | OAuth2 (PKCE, refresh, scopes) |
| Use case | first-party SPA / API | true 3rd-party app auth |
| Complexity | low | high |
| Refresh tokens | no | yes |
| Scopes | yes (simple) | yes (per-OAuth-grant) |

**Recommendation:** Sanctum for API keys (S1234). Passport ONLY when first 3-party-app partner asks (today: none).

## When Passport ships, scope model

| Scope | Description |
|---|---|
| `posts:read` | Read public + member posts |
| `clusters:read` | Read clusters + bias distribution |
| `sources:read` | Read source registry |
| `search:read` | Use /search endpoint |
| `vault:read` | Read member's coffre (member-scoped) |
| `vault:write` | Save to member's coffre |
| `preferences:read` | Read member preferences |
| `preferences:write` | Write member preferences (categories, push, locale) |
| `account:delete` | Delete member account (high-risk; explicit consent screen) |

## PKCE-only (no implicit grant)

- All flows MUST use PKCE per OAuth2 BCP.
- No client secrets for public clients (mobile apps, SPAs).
- Confidential clients (server-side) get secret + can use authorization code grant.

## Token lifetimes

| Token | TTL |
|---|---|
| Authorization code | 10 minutes |
| Access token | 1 hour |
| Refresh token | 30 days (sliding window) |
| Personal Access Token | configurable, default 365d |

## Consent screen

- Mandatory before issuing first token.
- Lists exact scopes + duration.
- Skin matches GrimbaNews dark theme.
- "Revoke at any time at /account/connected-apps".

## Revocation

- Member-side: `/account/connected-apps` shows all OAuth grants + revoke buttons.
- Operator-side: admin can mass-revoke per client.
- Token revocation propagates immediately (no cache).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1182)
- Sister docs: `docs/GRIMBANEWS_PUBLIC_API_V2_DESIGN.md`, `docs/GRIMBANEWS_B2B_API_V1_AUTH_PLAN.md`, `docs/GRIMBANEWS_API_V2_KEY_REVOCATION_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
