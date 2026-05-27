# GrimbaNews — Per-Article Annotation Surface Design

**Status:** plan v0
**Owner:** Nina Patel (Lead FE) + Liam Smith (PM) + Larry Ellison (DBA)
**Walks:** Mythos S1371 (per-article annotation surface) deferred → partial
**Gating dependency:** `annotations` table + selection-to-anchor mapping + reader-side highlight UI (S1372).

## Why this exists

Annotations are the foundational primitive for the reader product v2 (S1380). Without them, reader notebook entries can only reference whole articles, never specific passages.

## v1 schema

```sql
CREATE TABLE annotations (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  member_id BIGINT NOT NULL,
  post_id BIGINT NOT NULL,
  quote_text TEXT NOT NULL,
  anchor_xpath VARCHAR(512) NULL,
  anchor_start INT NULL,
  anchor_end INT NULL,
  note TEXT NULL,
  visibility ENUM('private', 'shared-link', 'public') DEFAULT 'private',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_member_post (member_id, post_id),
  INDEX idx_post_visibility (post_id, visibility)
);
```

## Anchor strategy

- Primary: xpath + character offset (robust to most reflows).
- Fallback: quote-text fuzzy match if xpath drifts (article re-render).
- Surface anchor-orphan warning ("Cette annotation ne retrouve plus son contexte") if both fail.

## UX

- Reader selects text → floating popover with "Surligner" / "Annoter" / "Citer".
- Highlights render as background-color overlays on re-visit.
- Private by default.

## Anti-patterns

- No public-annotation surface in v1 (waits for S1545).
- No annotation count badge on article (avoids gamification).
- No third-party annotation provider (Hypothesis, Genius) — own the data.

## Cross-references

Master plan: S1371. Sister: S1372 (reader highlight save), S1376 (notebook), S1545 (public annotations), S1547 (export), S1549 (moderation).
