# GrimbaNews — Native Signing & Certificates Plan

**Status:** plan v0 (no certificates provisioned; gates on Apple Developer + Google Play Console)
**Owner:** Jacob Lee (DevOps) owns provisioning + Sara Chen (CISO) on key custody + Larry Ellison on storage strategy
**Walks:** Mythos S1392 (Native signing / certificates) deferred → partial
**Gating dependency:** Apple Developer + Google Play Console + dedicated build-only Apple ID

## Why this exists

S1392 governs the cryptographic identity that links every release to GrimbaNews. Lose the Android upload keystore → cannot ship updates on existing Play listing, ever. Lose Apple key → ship-rotation pain.

## Today's surrogate

- **GitHub deploy keys** — `darkvaderfr` org SSH keys on VPS. Same custodial discipline applies.

## Apple side

### Certificates

- **Apple Distribution certificate** — used to sign App Store builds.
- **Apple Push Notifications certificate** — alternative to .p8 auth key (we use .p8 per S1306).
- **Provisioning profile** — links bundle ID + certificate + capabilities (Push, In-App Purchase, Sign in with Apple).

### Storage

- Use fastlane `match` — encrypted git repo holds certificates + profiles.
- Repo URL: private repo in `darkvaderfr` org (`grimbanews-signing`).
- Encryption passphrase: GitHub Actions secret `MATCH_PASSWORD`.

### Rotation cadence

- Apple Distribution cert: renew before annual expiry.
- App Store Connect API key (for fastlane upload): annual rotation.

## Android side

### Keystore

- Generate once: `keytool -genkey -v -keystore grimbanews-upload.jks -alias upload -keyalg RSA -keysize 2048 -validity 25000`.
- This is the **upload key** — used to sign uploads to Play Console.
- Play Console then **re-signs** with the Play App Signing key (Google-managed).
- Backup `.jks` + passphrase in 1Password (Sara Chen) + redundant offline backup.

### Storage

- Encrypted in fastlane `match` (same repo as iOS, separate folder).
- Passphrase as GHA secret.

### Rotation

- Upload key can be rotated via Play Console (requires uploading new key + 6-week transition).
- App Signing key (Google-managed) effectively never rotates.

## Key custody (Sara Chen)

- Primary custodian: Jacob Lee.
- Backup custodian: Vader (encrypted offline copy).
- No third party (no contractor) holds keys.
- Rotation triggers: suspected compromise + scheduled annual.
- Lost-key procedure: documented in private runbook (not in this public doc).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1392)
- Sister docs: `docs/GRIMBANEWS_NATIVE_RELEASE_PIPELINE_PLAN.md`, `docs/GRIMBANEWS_APNS_INTEGRATION_PLAN.md`, `docs/GRIMBANEWS_IOS_APP_SHELL_SCOPE.md`, `docs/GRIMBANEWS_ANDROID_APP_SHELL_SCOPE.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
