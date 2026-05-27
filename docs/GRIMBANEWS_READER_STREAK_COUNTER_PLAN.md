# GrimbaNews — Reader Streak Counter Plan

**Status:** plan v0
**Owner:** Alex Morgan (UI/UX) + Liam Smith (PM) + Rajesh Kumar (Backend)
**Walks:** Mythos S1291 (daily streak counter) deferred → partial
**Gating dependency:** `members.streak_days` column + per-day visit ledger (`reader_daily_visits` table) + cookie-vs-account dual-mode policy.

## Why this exists

Per Steve's cinematic design language and the gamification pattern shipped on Incognito (daily streak), reader retention lifts ~12-18% when a visible streak is on the home rail. GrimbaNews v1 ships with zero retention loop beyond bookmarks; a streak counter is the smallest unit that re-engages a returning reader without nagging.

## v1 schema

```sql
ALTER TABLE members ADD COLUMN streak_days INT DEFAULT 0;
ALTER TABLE members ADD COLUMN streak_last_visit_at DATE NULL;
ALTER TABLE members ADD COLUMN streak_longest INT DEFAULT 0;

CREATE TABLE reader_daily_visits (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  member_id BIGINT NULL,                 -- nullable for anon cookie-mode
  visit_cookie_id VARCHAR(64) NULL,
  visit_date DATE NOT NULL,
  visit_count INT DEFAULT 1,
  UNIQUE KEY uq_visit (member_id, visit_cookie_id, visit_date),
  INDEX idx_member_date (member_id, visit_date)
);
```

## Visit-counting policy

- One row per (member or cookie) per UTC day.
- Increment `streak_days` if previous row exists for `today - 1`.
- Reset to 1 if previous row is older than `today - 1`.
- Cookie-mode upgrade: on account creation, merge anon `visit_cookie_id` rows into `member_id` rows.

## Surrogate today

- Cookie-based "returning reader" pill on home rail (no streak math, just bool).
- Bookmarks count (already shipped) signals returning intent.

## UX (per Steve cinematic SOK)

- Compact gold-amber chip on header right of `/coffre`.
- Tap → `/coffre/streak` micro-page with calendar heatmap.
- Quiet by default (no toast, no nag).

## Anti-patterns to avoid

- No nudge emails before S1292 ships.
- No "you lost your streak" red copy. Always say "Nouvelle série démarrée aujourd'hui".
- Streak is one of many retention signals, never the primary.

## Cross-references

Master plan: S1291. Sister: S1292 (streak reminder), S1296 (badges), S1394 (reader product v2). Memory: `feedback_steve_design_language.md`. Pattern source: Incognito daily streak (`project_incognito_session5.md`).
