# GrimbaNews — Apple Push Notification Service Integration Plan

**Status:** plan v0 (no APNs auth key; gates on Apple Developer)
**Owner:** Jacob Lee (DevOps) provisions key + Rajesh Kumar (Backend) wires HTTP/2 sender + Sara Chen (CISO) signs key-rotation cadence
**Walks:** Mythos S1306 (Mobile push APNs) deferred → partial
**Gating dependency:** Apple Developer ($99/yr) + iOS shell (S1161) + APNs auth key (.p8) generated

## Why this exists

S1306 is the iOS push delivery layer. Without it, no push to iOS readers. APNs differs from FCM — uses HTTP/2 + JWT auth, no SDK from Apple, every implementer hand-rolls.

## Today's surrogate

- **Email weekly digest** — same email cadence as FCM surrogate.
- **iOS Safari Web Push** — possible in iOS 16.4+ but rollout dependence + service worker push handler not shipped.

## Provisioning steps

1. Login to Apple Developer portal.
2. Certificates, Identifiers & Profiles → Keys → "+".
3. Key Name: "GrimbaNews Push", check "Apple Push Notifications service (APNs)".
4. Download `.p8` key file (one-time download, store securely).
5. Note `Key ID` (10-char) and Team ID (Vader's developer team).
6. Bundle ID: `com.grimbanews.app` (already configured per S1161).

## Env config

| Env | Value |
|---|---|
| `APNS_AUTH_KEY` | contents of `.p8` (base64) |
| `APNS_KEY_ID` | 10-char from Apple |
| `APNS_TEAM_ID` | Vader's team ID |
| `APNS_BUNDLE_ID` | `com.grimbanews.app` |
| `APNS_ENVIRONMENT` | `production` or `sandbox` |

## Server library

- `composer require edamov/pushok` (HTTP/2 APNs client).
- Or: hand-rolled via `guzzle/guzzle` + JWT generation (~100 lines).

## Delivery code (pushok pattern)

```php
$options = new \Pushok\AuthProviderOptions([
    'key_id' => env('APNS_KEY_ID'),
    'team_id' => env('APNS_TEAM_ID'),
    'app_bundle_id' => env('APNS_BUNDLE_ID'),
    'private_key_content' => base64_decode(env('APNS_AUTH_KEY')),
]);
$auth = \Pushok\AuthProvider\Token::create($options);
$client = new \Pushok\Client($auth, env('APNS_ENVIRONMENT') === 'production');

$alert = \Pushok\Payload\Alert::create()
    ->setTitle($title)
    ->setBody($body);
$payload = \Pushok\Payload::create()
    ->setAlert($alert)
    ->setSound('default')
    ->setCustomValue('url', $clickTarget)
    ->setCustomValue('category', $category);

$client->addNotification(new \Pushok\Notification($payload, $token->token));
$responses = $client->push();
```

## Error handling

| APNs status | Action |
|---|---|
| 200 | mark succeeded |
| 410 (unregistered) | `push_tokens.is_active = 0` |
| 429 / 503 | exponential backoff |
| 400 BadDeviceToken | flag for review; might be sandbox vs production token mismatch |
| 403 | check auth — JWT may be expired (regen) |

## Key rotation (Sara Chen)

- APNs auth keys do not expire but should rotate every 12 months.
- Rotation: generate new `.p8`, deploy alongside old, flip env, revoke old after 24h.
- Calendar reminder: Larry adds to ops calendar at provisioning.

## Cost

- Free with Apple Developer membership.
- Subject to ~0.1% delivery failure rate (Apple-side outages).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1306)
- Sister docs: `docs/GRIMBANEWS_FCM_INTEGRATION_PLAN.md`, `docs/GRIMBANEWS_MOBILE_PUSH_INFRA_SCOPE.md`, `docs/GRIMBANEWS_NATIVE_SIGNING_CERTIFICATES_PLAN.md`, `docs/GRIMBANEWS_VENDOR_REGISTER.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
