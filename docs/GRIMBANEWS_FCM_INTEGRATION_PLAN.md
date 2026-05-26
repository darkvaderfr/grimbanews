# GrimbaNews — Firebase Cloud Messaging Integration Plan

**Status:** plan v0 (no FCM project; no service account)
**Owner:** Jacob Lee (DevOps) provisions + Rajesh Kumar (Backend) wires server SDK + Sara Chen (CISO) signs service-account scoping
**Walks:** Mythos S1305 (Mobile push FCM) deferred → partial
**Gating dependency:** Firebase project + service account JSON (with messaging scope only) + Android shell shipped (S1162)

## Why this exists

S1305 is the Android-side delivery vendor. Without FCM, no push to Android devices. Apple = APNs (S1306); Web = web-push (S1302).

## Today's surrogate

- **Email weekly digest** — `grimba:weekly-digest-send` cron carries equivalent reader value at email cadence.
- **No real-time push** to Android.

## Provisioning steps

1. Create Firebase project `grimbanews-prod` (Jacob Lee).
2. Add Android app: package `com.grimbanews.app`, download `google-services.json`.
3. Create service account: project settings → service accounts → "Generate new private key" — scope: Firebase Cloud Messaging API only (no Firestore / Realtime DB access).
4. Store key as `FCM_SERVICE_ACCOUNT_JSON` env on VPS.
5. Restrict service account in GCP IAM — no other roles.

## Server SDK install

- `composer require kreait/firebase-php` (Laravel-friendly Firebase Admin SDK).
- Bind in `AppServiceProvider`:

```php
$this->app->singleton('firebase.messaging', function () {
    $factory = (new \Kreait\Firebase\Factory)
        ->withServiceAccount(json_decode(env('FCM_SERVICE_ACCOUNT_JSON'), true));
    return $factory->createMessaging();
});
```

## Delivery code

```php
$messaging = app('firebase.messaging');
$message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $token->token)
    ->withNotification(\Kreait\Firebase\Messaging\Notification::create($title, $body))
    ->withData(['url' => $clickTarget, 'category' => $category]);
$messaging->send($message);
```

## Token registration (client side)

- Capacitor `@capacitor/push-notifications` requests permission.
- On registration: POST token to `/api/push/register` with `platform='android'`.
- Server upserts `push_tokens` row.

## Error handling

| Vendor response | Action |
|---|---|
| 200 OK | mark delivery succeeded |
| 410 Gone (token revoked) | set `push_tokens.is_active = 0` |
| 429 (quota) | exponential backoff via queue |
| 5xx (Firebase outage) | requeue with delay |

## Cost

- Free tier: unlimited delivery.
- Outage risk: Firebase has ~99.95% uptime per Google SLA.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1305)
- Sister docs: `docs/GRIMBANEWS_APNS_INTEGRATION_PLAN.md`, `docs/GRIMBANEWS_MOBILE_PUSH_INFRA_SCOPE.md`, `docs/GRIMBANEWS_PUSH_FREQUENCY_CAPS_DESIGN.md`, `docs/GRIMBANEWS_VENDOR_REGISTER.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
