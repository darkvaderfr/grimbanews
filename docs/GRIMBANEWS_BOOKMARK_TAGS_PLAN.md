# GrimbaNews — Bookmark Tags Plan

**Status:** plan v0 (no tag schema for bookmarks)
**Owner:** Rajesh Kumar (Backend) + Liam Smith (PM) + Alex Morgan (UI/UX)
**Walks:** Mythos S1553 (Bookmark — tags) deferred → partial
**Gating dependency:** Member auth + existing bookmark primitive.

## Why this exists

S1553 complements folders (S1552) by letting a single bookmark live in multiple buckets ("Climat" + "Politique" + "Long-form"). Folders = single hierarchy; tags = cross-cutting labels. Industry standard for save-for-later UX.

## Today's surrogate

- No tags. Saved articles are tag-less.

## Schema (target)

```sql
CREATE TABLE vault_tags (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  member_id BIGINT NOT NULL,
  name VARCHAR(80) NOT NULL,                -- 'climat', 'long-form'
  slug VARCHAR(120) NOT NULL,
  color_key VARCHAR(16) NULL,
  created_at TIMESTAMP,
  UNIQUE KEY (member_id, slug),
  INDEX (member_id)
);

CREATE TABLE vault_bookmark_tags (
  bookmark_id BIGINT NOT NULL,              -- FK member_bookmarks
  tag_id BIGINT NOT NULL,                   -- FK vault_tags
  PRIMARY KEY (bookmark_id, tag_id),
  INDEX (tag_id)
);
```

## UI surface

- Add tag from bookmark card: pill-input with autocomplete from existing member tags.
- Per-tag URL: `/coffre/tag/{tag-slug}`.
- Tag cloud / tag rail on `/coffre` (sorted by frequency).

## Auto-suggestions (v2)

- On bookmark, suggest tags from article's categories (server-side).
- Suggest from member's existing tag-affinity ("Vous avez souvent tagué 'climat' sur articles similaires").

## Tag normalization

- Slug-based dedupe on creation (case + accent insensitive).
- Max 100 tags per member.
- Max 20 tags per bookmark.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1553)
- Sister docs: `docs/GRIMBANEWS_BOOKMARK_FOLDERS_PLAN.md`, `docs/GRIMBANEWS_BOOKMARK_SEARCH_WITHIN_SAVED_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
