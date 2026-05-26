# GrimbaNews — Author Follow Design

**Status:** plan v0 (no follow-author primitive; follow-source surrogate exists via saved searches)
**Owner:** Liam Smith (PM) on UX + Nina Patel (Lead FE) implements + Larry Ellison on schema + David Chen on activity metric definitions
**Walks:** Mythos S1416 (Author follow) deferred → partial
**Gating dependency:** `journalists` table (S1411) + `members` table (Botble) + author profile page (S1412)

## Why this exists

S1416 lets readers follow specific journalists, get their new bylines in for-you feed + optional email digest. Today's surrogate is follow-source via saved-search — coarser than follow-byline.

## Today's surrogate

- **Saved searches** can filter by source — `?source=42&category=climate` saved → weekly digest.
- **No per-byline follow.**

## Schema

```sql
CREATE TABLE member_followed_journalists (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  member_id BIGINT NOT NULL,
  journalist_id BIGINT NOT NULL,
  notification_pref ENUM('off','weekly','realtime') DEFAULT 'weekly',
  created_at TIMESTAMP,
  UNIQUE (member_id, journalist_id),
  INDEX (member_id),
  INDEX (journalist_id)
);
```

## UI

### Follow button (on author profile page S1412)

```
+----------------------------------+
| 📌 Follow Jane Doe               |
|    Get notified about her bylines|
+----------------------------------+
```

Once followed, button becomes:

```
+----------------------------------+
| ✓ Following Jane Doe   [▼]      |
|     ┌─────────────────────────┐  |
|     │ Notifications:          │  |
|     │ ( ) Off                 │  |
|     │ (•) Weekly digest       │  |
|     │ ( ) Real-time push      │  |
|     │                         │  |
|     │ [ Unfollow ]            │  |
|     └─────────────────────────┘  |
+----------------------------------+
```

### Account page

`/account/follows` — list of followed journalists with edit / unfollow.

## For-you boost

- For-you ranking algorithm (`/pour-vous` route) checks followed-journalist list.
- Posts authored by followed journalists boosted +20% rank in feed.
- Indicator chip on post card: "Following Jane Doe".

## Digest cadence

- **Weekly**: bundled with existing `/account/saved-searches` digest cadence.
- **Real-time**: pushes via push infra (gates on S1154 + S1175 push categories).
- **Off**: no email or push; only appears in for-you feed.

## Telemetry (David Chen)

- Per-journalist follow count (private to operator).
- Per-member followed count.
- Conversion: profile-page-views → follow.
- Anti-spam: hard cap 500 follows per member.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1416)
- Sister docs: `docs/GRIMBANEWS_AUTHOR_TABLE_SCHEMA.md`, `docs/GRIMBANEWS_AUTHOR_PROFILE_PAGE_SCOPE.md`, `docs/GRIMBANEWS_AUTHOR_RSS_FEED_DESIGN.md`, `docs/GRIMBANEWS_MOBILE_APP_FORYOU_SCOPE.md`, `docs/GRIMBANEWS_PUSH_CATEGORIES_GOVERNANCE.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
