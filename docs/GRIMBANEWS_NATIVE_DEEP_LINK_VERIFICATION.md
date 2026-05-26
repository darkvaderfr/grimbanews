# GrimbaNews — Native Deep Link Verification Plan

**Status:** plan v0 (no Universal Links / App Links registered)
**Owner:** Nina Patel (Lead FE) implements + Jacob Lee deploys `.well-known/` files + Sara Chen on association posture
**Walks:** Mythos S1396 (Native deep-link verification) deferred → partial
**Gating dependency:** Native shell shipped + control over `https://grimbanews.com/.well-known/` paths + Apple App Site Association + Android App Links assetlinks

## Why this exists

S1396 makes `https://grimbanews.com/dossier/123` open in the native app when installed, instead of in Safari/Chrome. This is the "feels native" affordance — and the foundation for push payload click-targets.

## Today's surrogate

- **Web URLs** — all share URLs are web URLs; without deep-link, they open browser.
- **PWA** — installed PWA may intercept on some platforms inconsistently.

## iOS — Universal Links

### apple-app-site-association

Served at: `https://grimbanews.com/.well-known/apple-app-site-association`

Content-Type: `application/json`, no `.json` extension on URL.

```json
{
  "applinks": {
    "apps": [],
    "details": [
      {
        "appID": "TEAMID.com.grimbanews.app",
        "paths": [
          "/dossier/*",
          "/blog/*",
          "/local/*",
          "/account/*",
          "/methodology",
          "/sources",
          "NOT /admin/*",
          "NOT /api/*"
        ]
      }
    ]
  }
}
```

### Native side

- Xcode → Capabilities → Associated Domains → add `applinks:grimbanews.com`.
- Capacitor `App.addListener('appUrlOpen', ...)` handles route → `window.location.href = url.path`.

## Android — App Links

### assetlinks.json

Served at: `https://grimbanews.com/.well-known/assetlinks.json`

```json
[{
  "relation": ["delegate_permission/common.handle_all_urls"],
  "target": {
    "namespace": "android_app",
    "package_name": "com.grimbanews.app",
    "sha256_cert_fingerprints": ["<sha256 of Play App Signing cert>"]
  }
}]
```

### Native side

- `AndroidManifest.xml` intent-filter with `android:autoVerify="true"`.
- Same Capacitor App listener routes deep link to WebView.

## Verification

| Tool | Use |
|---|---|
| Apple Branch.io validator | https://branch.io/resources/aasa-validator/ |
| Google Asset Link tool | https://developers.google.com/digital-asset-links/tools/generator |
| Manual: `xcrun simctl openurl booted https://grimbanews.com/dossier/1` | iOS sim test |
| Manual: `adb shell am start -a android.intent.action.VIEW -d "https://grimbanews.com/dossier/1"` | Android emulator test |

## Security posture (Sara Chen)

- `apple-app-site-association` MUST be HTTPS, served at `.well-known/`.
- No redirects allowed on .well-known path.
- Bundle ID + Team ID in AASA == built app — mismatch breaks association silently.
- Path whitelist excludes `/admin/*` and `/api/*` (browser-only).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1396)
- Sister docs: `docs/GRIMBANEWS_IOS_APP_SHELL_SCOPE.md`, `docs/GRIMBANEWS_ANDROID_APP_SHELL_SCOPE.md`, `docs/GRIMBANEWS_MOBILE_APP_PWA_WRAPPER.md`, `docs/GRIMBANEWS_NATIVE_PUSH_PERMISSION_FLOW.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
