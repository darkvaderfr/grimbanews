# GrimbaNews — Mobile Push Infrastructure Scope

**Status:** plan v0 (no push server; no token table; FCM/APNs accounts not created)
**Owner:** Jacob Lee (DevOps) provisions FCM + APNs + Rajesh Kumar (Backend) builds token registry + Sara Chen (CISO) signs payload-PII policy + Larry Ellison on token-table schema
**Walks:** Mythos S1154 (Push notification infra) deferred → partial
**Gating dependency:** FCM project + APNs auth key (.p8 from Apple Developer) + native app shell (gates on S1152/S1153)

## Why this exists

S1154 is the foundational infra for every push-related row (S1175, S1176, S1305, S1306, S1307, S1308, S1310, S1397). Without a token registry + delivery worker, no per-cluster push can ship.

## Today's surrogate

- **Email-based push surrogate** — saved-search digest + vault digest (weekly) carry the equivalent reader value, but at email cadence not real-time.
- **No web-push** — VAPID keys not generated; `public/grimba-sw.js` does not implement `push` event listener.
- **No FCM/APNs** — zero accounts provisioned.

## Required vendor accounts

| Vendor | Cost | Owner | Cadence |
|---|---|---|---|
| Firebase Cloud Messaging | free tier covers <10M deliveries/mo | Jacob Lee | per-project |
| Apple Push Notification service | included with Apple Developer ($99/yr) | Larry/Ray | annual via Apple Dev |
| Web Push (VAPID) | free; generate keys via `web-push` npm tool | Jacob Lee | one-time generation |

## Schema (operator-side)

```sql
CREATE TABLE push_tokens (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  member_id BIGINT NULL,                    -- nullable: anonymous opt-in supported
  platform ENUM('ios','android','web') NOT NULL,
  token VARCHAR(512) NOT NULL,              -- FCM/APNs registration token
  endpoint VARCHAR(512) NULL,               -- web push endpoint
  p256dh VARCHAR(255) NULL,                 -- web push public key
  auth VARCHAR(255) NULL,                   -- web push auth secret
  locale CHAR(5) DEFAULT 'fr-FR',
  topics_subscribed JSON DEFAULT '[]',      -- ['breaking','tech','climate']
  frequency_cap_per_day TINYINT DEFAULT 3,
  is_active BOOLEAN DEFAULT TRUE,
  last_delivered_at TIMESTAMP NULL,
  last_failed_at TIMESTAMP NULL,
  fail_count TINYINT DEFAULT 0,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  UNIQUE (platform, token(255)),
  INDEX (member_id, is_active),
  INDEX (locale, is_active)
);

CREATE TABLE push_deliveries (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  push_token_id BIGINT NOT NULL,
  message_id VARCHAR(64) NOT NULL,
  category VARCHAR(64) NOT NULL,            -- 'breaking','cluster-update','saved-search'
  title VARCHAR(255) NOT NULL,
  body TEXT NOT NULL,
  payload_json JSON NULL,                   -- click-target URL etc.
  status ENUM('queued','delivered','failed') DEFAULT 'queued',
  error VARCHAR(255) NULL,
  delivered_at TIMESTAMP NULL,
  created_at TIMESTAMP,
  INDEX (push_token_id, created_at),
  INDEX (status, created_at)
);
```

## Delivery worker pattern

- Laravel command `grimba:push-deliver-queue` — runs every 1 min.
- Reads `push_deliveries WHERE status='queued'` (LIMIT 100).
- Splits by platform: FCM SDK for android/web, APNs HTTP/2 for iOS.
- On 410 Gone (token invalidated): `is_active = false`.
- On 429: exponential backoff.

## PII / consent posture (Sara Chen sign-off)

- No content of articles in payload — only title + cluster URL.
- Reader opt-in stored as cookie + (if member) server flag.
- Opt-out always one tap.
- Token revocation on logout.
- No tracking of token across vendors.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1154)
- Sister docs: `docs/GRIMBANEWS_FCM_INTEGRATION_PLAN.md`, `docs/GRIMBANEWS_APNS_INTEGRATION_PLAN.md`, `docs/GRIMBANEWS_PUSH_CATEGORIES_GOVERNANCE.md`, `docs/GRIMBANEWS_PUSH_FREQUENCY_CAPS_DESIGN.md`, `docs/GRIMBANEWS_PUSH_OPTIN_ONBOARDING.md`
- Existing SW: `public/grimba-sw.js`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
