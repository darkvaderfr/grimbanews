# GrimbaNews — Push Frequency Cap Runtime Enforcement

**Status:** plan v0 (no push infra; this is the runtime/code-path companion to S1176 design)
**Owner:** Rajesh Kumar (Backend) implements + Hannah Kim on cap-hit telemetry + Sara Chen on hard limit non-negotiability
**Walks:** Mythos S1308 (Push frequency caps) deferred → partial
**Gating dependency:** Push infra (S1154) + delivery worker schema (S1154) + category schema (S1175)

## Why this exists

S1308 is the per-device runtime cap enforcement. Different from S1176 (design) — S1308 is the operational layer (where in code, what telemetry, what alerts).

## Today's surrogate

- **`grimba:weekly-digest-send` cron** — implicitly capped at 1/week per design.
- **`saved_searches.active` boolean** — implicit cap of "only if subscribed".

## Worker code path

```
grimba:push-deliver-queue (cron 1 min)
  ├── Pull batch from push_deliveries WHERE status='queued' LIMIT 500
  ├── For each delivery:
  │   ├── Check token.is_active → drop if false
  │   ├── Check token.timezone quiet hours → re-queue if quiet
  │   ├── Check per-category cap (last 24h count) → drop if exceeded
  │   ├── Check global cap (last 24h count vs token.frequency_cap_per_day) → drop if exceeded
  │   ├── Check per-cluster dedupe → drop if cluster already pushed to this token
  │   ├── Send via vendor (FCM/APNs/WebPush)
  │   ├── On 200: status='delivered', token.last_delivered_at = now
  │   └── On 410: token.is_active = false, status='failed'
  └── Commit batch
```

## Telemetry (Hannah Kim)

| Metric | Source | Alert threshold |
|---|---|---|
| Pushes queued (per minute) | push_deliveries inserts | spike >2x baseline = Slack |
| Pushes delivered | push_deliveries WHERE status=delivered | drop >50% baseline = Slack |
| Cap-blocked pushes | push_deliveries WHERE error LIKE 'cap_%' | >10% of attempts = Slack |
| Token attrition | push_tokens.is_active flips per day | >5% daily = Slack |

## Performance budget

- Worker p95 batch (500 deliveries) < 30s.
- Per-vendor API p99 < 2s.
- Worker scales horizontally — multiple workers ok via `withoutOverlapping(N)` per-vendor lock.

## Non-negotiables (Sara Chen)

- Global cap MUST be checked even if category is uncapped.
- Quiet hours MUST be respected (never override "important enough").
- Token revocation on 410 MUST be immediate, no retry.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1308)
- Sister docs: `docs/GRIMBANEWS_PUSH_FREQUENCY_CAPS_DESIGN.md`, `docs/GRIMBANEWS_MOBILE_PUSH_INFRA_SCOPE.md`, `docs/GRIMBANEWS_PUSH_CATEGORY_PREFERENCES_DESIGN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
