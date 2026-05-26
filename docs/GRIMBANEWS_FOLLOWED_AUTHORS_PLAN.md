# GrimbaNews — Followed Authors Plan

**Status:** plan v0 (no follow-author primitive; depends on author system S1411)
**Owner:** Rajesh Kumar (Backend) + Nina Patel (UI) + Liam Smith (PM)
**Walks:** Mythos S1517 (Followed authors) deferred → partial
**Gating dependency:** Journalist table (S1411 — author schema doc) live + at least one verified journalist with active byline.

## Why this exists

S1517 lets a reader follow a specific journalist — like substack subscribe but free. Cross-references the existing AUTHOR_FOLLOW_DESIGN doc for UX; this doc focuses on the personalization-feed integration angle.

## Today's surrogate

- No follow primitive. Reader can only bookmark per-article.

## Schema (target)

```sql
CREATE TABLE member_followed_journalists (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  member_id BIGINT NOT NULL,
  journalist_id BIGINT NOT NULL,
  followed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  notify_new BOOLEAN DEFAULT TRUE,
  UNIQUE KEY (member_id, journalist_id),
  INDEX (member_id), INDEX (journalist_id)
);
```

## Feed integration

- Followed-authors articles get bonus weight in ML feed (S1501) ranking.
- New article from followed author → optional notification (web push S1301+ when shipped; email otherwise).
- `/account/follows` lists all follows with unfollow option.

## Notification preferences

- Per-follow toggle for new-article notification.
- Frequency cap: max 1 notification per author per day (avoid spam).
- Aggregation: "3 new articles from your follows" digest if >1 author published.

## Author-side benefit

- Author profile page (per S1412 sibling doc) shows follower count (transparency: low public threshold ≥10 to display).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1517)
- Sister docs: `docs/GRIMBANEWS_AUTHOR_FOLLOW_DESIGN.md`, `docs/GRIMBANEWS_AUTHOR_TABLE_SCHEMA.md`, `docs/GRIMBANEWS_ML_FEED_DESIGN_DOC.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
