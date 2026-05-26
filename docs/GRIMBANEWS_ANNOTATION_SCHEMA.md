# GrimbaNews — Annotation Schema

**Status:** plan v0 (no `post_annotations` / `post_highlights` table)
**Owner:** Larry Ellison (VP DBA) on schema + Rajesh Kumar (Backend) on integration + Sara Chen on member-PII
**Walks:** Mythos S1541 (Annotation schema — highlights table) deferred → partial
**Gating dependency:** Member auth + text-selection UI (S1542 sibling doc).

## Why this exists

S1541 is the foundation for reader highlights, notes, and annotations. Today readers can only bookmark whole articles — no in-article snippet save. This blocks S1542-S1549 (highlight UI, notes, sync, export, analytics, moderation).

## Today's surrogate

- `member_bookmarks` table — article-level only.

## Schema (target)

```sql
CREATE TABLE post_highlights (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  member_id BIGINT NOT NULL,
  post_id BIGINT NOT NULL,

  -- Anchor (W3C Web Annotation Data Model-inspired)
  selected_text TEXT NOT NULL,              -- the highlighted text
  text_position_start INT NOT NULL,         -- char offset in cleaned body
  text_position_end INT NOT NULL,
  text_quote_prefix VARCHAR(64) NULL,       -- 32-char prefix for robust re-anchoring
  text_quote_suffix VARCHAR(64) NULL,

  -- Optional note attached
  note TEXT NULL,

  -- Visibility
  visibility ENUM('private','shared_with_followers','public') DEFAULT 'private',

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (member_id, created_at),
  INDEX (post_id, visibility)
);

CREATE TABLE post_highlight_reactions (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  highlight_id BIGINT NOT NULL,
  member_id BIGINT NOT NULL,
  reaction ENUM('agree','disagree','important','clarification') NOT NULL,
  created_at TIMESTAMP,
  UNIQUE KEY (highlight_id, member_id)
);
```

## Anchor robustness

- W3C-inspired three-tuple: `(start_offset, end_offset, quote_prefix, quote_suffix)`.
- On article edit, anchors that drift detected via prefix/suffix mismatch → mark `orphan = true` (degrade gracefully).
- Cron: `grimba:reconcile-highlights` runs nightly to re-anchor or orphan.

## Privacy posture (Sara Chen)

- Private highlight visible to member only.
- Shared/public highlights: still owned by member, can be retracted anytime.
- Member deletion → cascade delete all highlights + reactions.
- 30-day deletion guarantee.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1541)
- Sister docs: `docs/GRIMBANEWS_HIGHLIGHT_UI_DESIGN.md`, `docs/GRIMBANEWS_PRIVATE_ANNOTATIONS_SYNC_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
