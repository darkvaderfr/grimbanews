# GrimbaNews — Bookmark Folders Plan

**Status:** plan v0 (no `vault_folders` table; flat list today)
**Owner:** Rajesh Kumar (Backend) + Alex Morgan (UI/UX) + Liam Smith (PM)
**Walks:** Mythos S1552 (Bookmark — folders) deferred → partial
**Gating dependency:** Existing coffre / vault primitive (`member_bookmarks`) + member auth.

## Why this exists

S1552 organizes saved articles into folders ("À lire plus tard", "Élections 2026", "Recherche climat"). Today coffre is a flat list — UX degrades after ~30 saves.

## Today's surrogate

- Flat list at `/coffre` route. Sort by saved-date desc only.

## Schema (target)

```sql
CREATE TABLE vault_folders (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  member_id BIGINT NOT NULL,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(160) NOT NULL,                -- per-member unique slug for URL
  position INT DEFAULT 0,                    -- manual sort
  icon_key VARCHAR(32) NULL,                 -- emoji or icon-set key
  color_key VARCHAR(16) NULL,                -- accent color
  created_at TIMESTAMP, updated_at TIMESTAMP,
  UNIQUE KEY (member_id, slug),
  INDEX (member_id, position)
);

ALTER TABLE member_bookmarks ADD COLUMN folder_id BIGINT NULL;
ALTER TABLE member_bookmarks ADD INDEX (member_id, folder_id);
```

## Default folder

- On first bookmark: "Tous mes articles" folder auto-created. New bookmarks default here.
- Member can create custom folders + move bookmarks.

## UI surface

- Left rail on `/coffre`: folder list with article counts.
- Drag-and-drop reorder (manual position).
- Per-folder URL: `/coffre/{folder-slug}`.
- Empty state per folder: "Aucun article dans ce dossier. [Parcourir]".

## Limits

- Max 50 folders per member (UX sanity).
- Max 500 articles per folder (UX sanity; pagination beyond).

## Move semantics

- Single article: drag-drop or per-card "Déplacer vers …" menu.
- Multi-select bulk move from list view.
- Article cannot live in 2 folders (single-folder primitive; tags S1553 handles cross-bucketing).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1552)
- Sister docs: `docs/GRIMBANEWS_BOOKMARK_TAGS_PLAN.md`, `docs/GRIMBANEWS_BOOKMARK_SEARCH_WITHIN_SAVED_PLAN.md`
- Existing infra: `/coffre` route + `member_bookmarks` table
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
