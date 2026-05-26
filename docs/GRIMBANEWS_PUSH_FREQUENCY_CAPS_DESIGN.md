# GrimbaNews — Push Frequency Caps Design

**Status:** plan v0 (no push infra; cap logic is operator-side rule layer)
**Owner:** Sara Chen (CISO) signs rule rigor + Liam Smith (PM) on cap defaults + Rajesh Kumar on delivery-worker enforcement + David Chen on per-cap analytics
**Walks:** Mythos S1176 (App push frequency caps) deferred → partial
**Gating dependency:** Push infra (S1154) + category schema (S1175)

## Why this exists

S1176 is the runtime enforcement layer that prevents push fatigue. Push tolerance drops 40% after 3+ pushes / day in industry benchmarks. Cap logic at delivery time, never at send time, because send-time race conditions ship duplicates.

## Today's surrogate

- **Email weekly digest** — `weekly` cadence by definition self-caps (1/week).
- **Saved-search digest** — cap is at cadence level, not enforcement.

## Cap layers (defense in depth)

| Layer | Where | Logic |
|---|---|---|
| Per-category daily cap | delivery worker | `push_deliveries WHERE token=X AND category=Y AND delivered_at > now-24h` |
| Global daily cap | delivery worker | per-token total ≤ 8/day across all categories |
| Quiet hours | delivery worker | reject if local time in 23:00-07:00 (per token's locale) |
| Per-cluster dedupe | delivery worker | one push per cluster per token (forever — not per-day) |
| Per-correction unique | delivery worker | one correction-notice push per post per token |

## Per-token cap config

`push_tokens.frequency_cap_per_day TINYINT DEFAULT 3` — token-level override (reader can lower it from 8 to 3, e.g.).

## Enforcement code (Rajesh Kumar)

```php
// In grimba:push-deliver-queue worker
foreach ($queued as $delivery) {
    $token = PushToken::find($delivery->push_token_id);
    if (!$token->is_active) { $delivery->status = 'failed'; $delivery->error = 'token_inactive'; continue; }

    // Quiet hours
    $localHour = now()->setTimezone($token->timezone ?? 'Europe/Paris')->hour;
    if ($localHour >= 23 || $localHour < 7) {
        if (in_array($delivery->category, ['breaking','cluster-update','saved-search','local'])) {
            $delivery->status = 'queued'; // delay until 07:00
            continue;
        }
    }

    // Per-category daily cap (config-driven per-category default)
    $catCap = config("push_caps.{$delivery->category}", 3);
    $catSent = PushDelivery::where('push_token_id', $token->id)
        ->where('category', $delivery->category)
        ->where('delivered_at', '>=', now()->subDay())
        ->count();
    if ($catSent >= $catCap) { $delivery->status = 'failed'; $delivery->error = "cap_category_{$catCap}"; continue; }

    // Global daily cap
    $globalSent = PushDelivery::where('push_token_id', $token->id)
        ->where('delivered_at', '>=', now()->subDay())->count();
    if ($globalSent >= $token->frequency_cap_per_day) {
        $delivery->status = 'failed'; $delivery->error = 'cap_global'; continue;
    }

    // Send via vendor SDK
    $this->sendViaVendor($token, $delivery);
}
```

## Reader-visible cap

`/account/notifications`:
- "Max notifications per day: [3] [5] [8]".
- Default 3 (low + safe).

## Quality monitor (David Chen)

| Metric | Target | Source |
|---|---|---|
| Per-token open rate | >35% | `notification_tap` event ratio |
| Per-category opt-out within 30d | <5% | toggle-off events |
| Cap-hit rate | <10% pushes blocked by cap | delivery-worker logs |

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1176)
- Sister docs: `docs/GRIMBANEWS_PUSH_CATEGORIES_GOVERNANCE.md`, `docs/GRIMBANEWS_MOBILE_PUSH_INFRA_SCOPE.md`, `docs/GRIMBANEWS_PUSH_FREQUENCY_CAP_RUNTIME.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
