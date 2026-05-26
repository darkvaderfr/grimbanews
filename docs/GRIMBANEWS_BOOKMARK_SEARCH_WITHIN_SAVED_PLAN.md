# GrimbaNews — Search Within Saved Bookmarks Plan

**Status:** plan v0 (no FTS index over vault subset; full-corpus search only)
**Owner:** Rajesh Kumar (Backend) + Nina Patel (Lead Frontend) + David Chen on FTS strategy
**Walks:** Mythos S1556 (Bookmark — search within saved) deferred → partial
**Gating dependency:** Member auth + existing bookmark primitive + FTS5 index over posts already in place.

## Why this exists

S1556 lets a reader search only their saved articles ("did I save the article about X?"). Today the global search returns everything; member must scroll their coffre or remember the title.

## Today's surrogate

- Global search `/search?q=...` returns whole-corpus results.
- `/coffre` list view — paginate + scroll.

## Implementation

**No new index needed.** FTS5 over posts already exists. Add an `IN (subquery)` filter:

```sql
SELECT p.* FROM posts p
WHERE p.id IN (SELECT rowid FROM posts_fts WHERE posts_fts MATCH :q)
  AND p.id IN (SELECT post_id FROM member_bookmarks WHERE member_id = :member_id)
ORDER BY rank;
```

For folder/tag scoping (after S1552 / S1553):

```sql
  AND (
    p.id IN (SELECT post_id FROM member_bookmarks
             WHERE member_id = :member_id AND folder_id = :folder_id)
    OR p.id IN (SELECT bookmark_id FROM vault_bookmark_tags
                WHERE tag_id IN (:tag_ids))
  )
```

## UI surface

- Search bar at top of `/coffre` page (separate from global search bar).
- Placeholder copy: "Rechercher dans mes articles sauvegardés…".
- Within-folder / within-tag context preserved if reader is in `/coffre/dossier/{slug}` view.

## Performance

- FTS5 MATCH() against in-process index — sub-50ms.
- IN-subquery on member_bookmarks → fast (member_id is indexed).
- Total search latency target: <100ms P95.

## Semantic-search uplift (when S1463 ships)

- Optional toggle: "Recherche sémantique dans mes saves"
- Embedding-based ANN on member's bookmark subset

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1556)
- Sister docs: `docs/GRIMBANEWS_BOOKMARK_FOLDERS_PLAN.md`, `docs/GRIMBANEWS_BOOKMARK_TAGS_PLAN.md`, `docs/GRIMBANEWS_SEMANTIC_SEARCH_DESIGN_DOC.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
