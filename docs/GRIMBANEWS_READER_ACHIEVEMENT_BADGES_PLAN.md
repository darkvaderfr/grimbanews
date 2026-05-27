# GrimbaNews — Reader Achievement Badges Plan

**Status:** plan v0
**Owner:** Alex Morgan (UI/UX) + Liam Smith (PM) + Sara Kim (QA — eligibility test)
**Walks:** Mythos S1296 (reader achievement badges) deferred → partial
**Gating dependency:** S1291 streak counter + `reader_badges` table + cinematic chip component.

## Why this exists

Badges (sparingly) anchor a reader's identity on the site without devolving into Khan-Academy-style point spam. Per Steve, a badge is earned, never given; it must mean something across the GrimbaNews trust philosophy (bias-balance literacy, cross-source reading, returning consistency).

## v1 badge taxonomy

| Badge | Earned when |
|---|---|
| Curieux | First cluster read (>= 3 sources opened from same cluster) |
| Régulier | 7-day streak |
| Marathonien | 30-day streak |
| Polyglotte | Read in 2+ locales in 7 days |
| Équilibré | Read 5 clusters where bias mix spans left + center + right in 30 days |
| Ouvert | Opened a /angles-morts (blindspot) article |
| Archiviste | 50 articles saved to vault |

Final taxonomy gated on Steve approval; this v1 list is starter.

## Schema

```sql
CREATE TABLE reader_badges (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  member_id BIGINT NOT NULL,
  badge_slug VARCHAR(48) NOT NULL,
  earned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  meta JSON NULL,
  UNIQUE KEY uq_badge (member_id, badge_slug),
  INDEX idx_member (member_id)
);
```

## UX

- Compact pill row on `/coffre` profile header.
- Tap → `/coffre/insignes` page with description per badge + earned date.
- No public badge wall (privacy default; opt-in public profile is a separate sprint).

## Anti-patterns

- No notification spam on earn (single in-app toast, no email).
- No "complete the set" gamification language.
- Badges are visual recognition, not a leaderboard.

## Cross-references

Master plan: S1296. Sister: S1291/S1292 (streak), S1394 (reader product v2). Memory: `feedback_steve_design_language.md`, `feedback_reinvent_not_reskin.md`.
