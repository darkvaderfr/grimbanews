# GrimbaNews — Push Category Preferences Design

**Status:** plan v0 (no preference UI; saved_searches.active is the parallel pattern)
**Owner:** Steve Jobs (CPO) signs UX + Nina Patel (Lead FE) builds preference center + Alex Morgan on chip design + Sara Chen on consent log
**Walks:** Mythos S1307 (Push category preferences) deferred → partial
**Gating dependency:** Push infra (S1154) + category list locked (S1175)

## Why this exists

S1307 is the reader-facing surface — where the per-category toggles live. Without it, S1175's category model is a backend abstraction without user control.

## Today's surrogate

- **`saved_searches.active`** — per-saved-search delivery toggle. Same opt-in primitive at the database level.
- **`/account` page** carries placeholder section "Notifications (coming soon)".

## UI surface (`/account/notifications`)

```
+-----------------------------------------------+
| Notifications                                 |
|                                               |
| Email                                         |
|  [✓] Saved-search alerts (weekly digest)     |
|  [ ] Vault digest (weekly)                    |
|                                               |
| Push                          (per device)    |
|  [✓] Breaking news (max 5/day)               |
|  [ ] Saved-story updates                     |
|  [ ] Daily highlights (8am local)            |
|  [✓] Saved-search push                       |
|  [ ] Local edition push                      |
|  [✓] Correction notice for saved articles   |
|                                               |
| Frequency cap (per day, all push)            |
|  [3] [5] [8]   ← reader-set                  |
|                                               |
| Quiet hours: 23:00 — 07:00 (your timezone)   |
|                                               |
| Manage devices: [iPhone 15] [Pixel 8]        |
+-----------------------------------------------+
```

## Schema additions

```sql
ALTER TABLE push_tokens ADD COLUMN topics_subscribed JSON DEFAULT '[]';
-- topics_subscribed = ['breaking','saved-search','correction-issued']
```

```sql
ALTER TABLE members ADD COLUMN email_notif_prefs JSON DEFAULT '{"saved_search":true,"vault_digest":true}';
```

## Consent log (Sara Chen)

Every toggle change writes to `consent_log` table (per `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md`):

| Field | Value |
|---|---|
| member_id | reader |
| event | `push_category_toggle` |
| context | `{"category":"breaking","old":false,"new":true,"platform":"ios"}` |
| timestamp | now() |

## Reader-driven device list

- Each push_token row shows in "Manage devices" with friendly label (e.g., "iPhone 15 (Paris, May 26)").
- "Remove" → `is_active = false`, token revoked at vendor.
- Auto-revoke devices inactive >90 days (cron sweep).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1307)
- Sister docs: `docs/GRIMBANEWS_PUSH_CATEGORIES_GOVERNANCE.md`, `docs/GRIMBANEWS_PUSH_FREQUENCY_CAPS_DESIGN.md`, `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md`
- Existing parallel: `saved_searches.active` boolean
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
