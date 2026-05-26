# GrimbaNews — Native OTA Updates Plan

**Status:** plan v0 (no OTA channel; native ships only via store)
**Owner:** Nina Patel (Lead FE) on web-bundle delivery + Jacob Lee (DevOps) on CDN + Sara Chen (CISO) on signed-bundle posture
**Walks:** Mythos S1393 (Native code-push / OTA updates) deferred → partial
**Gating dependency:** Native shell shipped + remote-loaded web bundle decision (Capacitor allows both bundled and remote)

## Why this exists

S1393 lets us push web-side fixes to native readers without re-submitting to App Store / Play Store (5-day review delay on iOS, hours on Android). Capacitor architecture makes this natural — the web bundle CAN be loaded from `https://grimbanews.com` instead of bundled in the .ipa.

## Today's surrogate

- **Pure web** — every web deploy reaches readers within a CDN flush (~5 min). Native readers would inherit the same speed if remote-loaded.

## Two architecture options

### Option A — Bundled (Capacitor default)

- Web assets shipped inside `.ipa` / `.aab`.
- Native release required for any web change.
- **Pro:** offline-first, single signature chain.
- **Con:** every fix = store re-submit.

### Option B — Remote-loaded WebView

- Native shell loads `https://grimbanews.com` in WebView.
- Web changes go live instantly (same CDN flush as web readers).
- **Pro:** instant updates, native shell rarely re-submitted.
- **Con:** offline experience degraded (only PWA cache), reviewers may flag as "WebView wrapper" (Apple 4.2 rejection risk).

### Hybrid (recommended)

- Native app shell + critical reader routes (home, dossier, account) bundled.
- Heavy / changing routes (admin, methodology, blog) load from remote.
- Capacitor `Live Updates` (Ionic-managed paid service) OR self-hosted update channel.

## Self-hosted update channel

- Store version-pinned web bundles at `https://grimbanews.com/native-updates/<version>/`.
- Native shell checks `https://grimbanews.com/native-updates/manifest.json` on each launch.
- If new version available: download, verify signature, swap on next launch.
- Signature: Ed25519 keypair generated once; public key bundled, private key on VPS.

## Security posture (Sara Chen)

- All updates served over HTTPS.
- Ed25519 signature verified before swap.
- Update channel CANNOT pull from third-party domain — hard-coded grimbanews.com.
- Update bundles include `meta.json` with version, sha256, signature.
- Failed verification → fall back to bundled version.

## Apple compliance

- Apple Guideline 4.7: code can be downloaded as long as it doesn't change "primary purpose" or use "JavaScriptCore" engines.
- WebView with downloaded web bundle is allowed; native code changes via downloaded binary is NOT.
- Stay within rendering / business-logic changes only.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1393)
- Sister docs: `docs/GRIMBANEWS_NATIVE_RELEASE_PIPELINE_PLAN.md`, `docs/GRIMBANEWS_MOBILE_APP_PWA_WRAPPER.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
