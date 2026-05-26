# GrimbaNews — Native Push Permission Flow

**Status:** plan v0 (no native push UI; gates on S1310 onboarding decision)
**Owner:** Nina Patel (Lead FE) on Capacitor plugin wiring + Steve Jobs (CPO) signs flow + Sara Chen on permission posture
**Walks:** Mythos S1397 (Native push-notification permission flow) deferred → partial
**Gating dependency:** Push infra (S1154) + FCM (S1305) + APNs (S1306) + opt-in onboarding (S1310)

## Why this exists

S1397 is the code-level implementation of the user-facing flow in S1310. Different lens — S1310 covers UX, S1397 covers Capacitor plugin calls + token registration sequence.

## Today's surrogate

- **No native code path** — push not registered.
- **Web Notification API** — could be used but service worker push handler not in place.

## Sequence

```
1. Reader taps "Continue" on rationale screen (per S1310)
2. App calls PushNotifications.requestPermissions()
3. iOS/Android shows system prompt
4. If granted:
   a. PushNotifications.register() — gets vendor token
   b. PushNotifications.addListener('registration', token => {
        // POST to /api/push/register
        // body: { token, platform: 'ios'|'android', locale, timezone }
      })
   c. Server upserts push_tokens row, topics_subscribed = ['breaking']
   d. App navigates to /account/notifications to confirm categories
5. If denied:
   a. App shows "OK, web only" toast
   b. Local flag set: push_denied_at = now
   c. Re-prompt in 30 days
```

## Capacitor plugin install

```bash
npm i @capacitor/push-notifications
npx cap sync
```

iOS: Xcode → Signing & Capabilities → Push Notifications + Background Modes (Remote notifications).

Android: AndroidManifest service for FCM (per `docs/GRIMBANEWS_FCM_INTEGRATION_PLAN.md`).

## Server endpoint — POST /api/push/register

```php
public function register(Request $r) {
    $data = $r->validate([
        'token' => 'required|string|max:512',
        'platform' => 'required|in:ios,android,web',
        'locale' => 'nullable|string|max:5',
        'timezone' => 'nullable|string|max:32',
    ]);
    PushToken::updateOrCreate(
        ['platform' => $data['platform'], 'token' => $data['token']],
        [
            'member_id' => Auth::id(),
            'locale' => $data['locale'] ?? 'fr-FR',
            'timezone' => $data['timezone'] ?? 'Europe/Paris',
            'topics_subscribed' => json_encode(['breaking']),
            'is_active' => true,
        ]
    );
    return response()->json(['ok' => true]);
}
```

## Foreground vs background notifications

| State | iOS | Android |
|---|---|---|
| Foreground | App receives via PushNotifications.addListener('pushNotificationReceived') — show inline toast (no system banner by default) | Same — `pushNotificationReceived` handler |
| Background / killed | System shows banner — tap → `pushNotificationActionPerformed` → navigate to URL in payload | Same |

## Token refresh

- iOS: APNs may rotate token — refresh listener required.
- Android: FCM may rotate — `Capacitor.PushNotifications` reissues via `registration` event.
- Server upsert by `(platform, token)` UNIQUE — stale tokens orphaned after 90d cleanup.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1397)
- Sister docs: `docs/GRIMBANEWS_PUSH_OPTIN_ONBOARDING.md`, `docs/GRIMBANEWS_FCM_INTEGRATION_PLAN.md`, `docs/GRIMBANEWS_APNS_INTEGRATION_PLAN.md`, `docs/GRIMBANEWS_MOBILE_PUSH_INFRA_SCOPE.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
