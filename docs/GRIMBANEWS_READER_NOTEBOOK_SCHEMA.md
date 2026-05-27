# GrimbaNews — Reader Notebook Schema

**Status:** plan v0
**Owner:** Liam Smith (PM) + Rajesh Kumar (Backend) + Alex Morgan (UI/UX) + Larry Ellison (DBA)
**Walks:** Mythos S1376 (reader notebook) deferred → partial
**Gating dependency:** members table, vault save primitive already shipped; notebook adds free-text + structured note layer.

## Why this exists

The notebook is the home for: NobuAI insight exports (S1098), per-cluster reader notes (S1377), per-article annotations (S1371-S1379), and cross-cluster narrative tracking. It replaces three half-built surrogates (a vault note, a saved-search comment, a clipped insight) with one canonical surface.

## v1 schema

```sql
CREATE TABLE reader_notebooks (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  member_id BIGINT NOT NULL,
  title VARCHAR(160) NOT NULL DEFAULT 'Carnet sans titre',
  slug VARCHAR(80) NOT NULL,
  visibility ENUM('private', 'shared-link', 'public') DEFAULT 'private',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_slug (member_id, slug),
  INDEX idx_member (member_id, updated_at)
);

CREATE TABLE reader_notebook_entries (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  notebook_id BIGINT NOT NULL,
  entry_type ENUM('article-clip', 'cluster-note', 'annotation-quote', 'nobuai-insight', 'free-text') NOT NULL,
  ref_kind VARCHAR(32) NULL,             -- 'post' | 'cluster' | 'search'
  ref_id BIGINT NULL,
  body TEXT NULL,
  body_md TEXT NULL,
  meta JSON NULL,
  position INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_notebook_position (notebook_id, position),
  INDEX idx_ref (ref_kind, ref_id)
);
```

## v1 surfaces

- `/coffre/carnets` — index of notebooks.
- `/coffre/carnets/{slug}` — single notebook view (reorderable entries).
- `/coffre/carnets/{slug}/add?ref=post:1234` — entry-add endpoint from any article page.

## Privacy defaults

- `private` is default.
- `shared-link` issues a signed URL (no listing).
- `public` requires confirm modal + indexable opt-in.

## Surrogate today

- Bookmarks / vault — single-bucket save without notes.
- Saved searches — query persistence without per-result note.

## Cross-references

Master plan: S1376. Sister: S1377 (per-cluster notes), S1098 (NobuAI export), S1371-S1379 (annotation set), S1547 (annotation export).
