# GrimbaNews — Correction Notice Badge Design

**Status:** plan v0 (no `posts.correction_*` columns; admin manual edit of `posts.content` is surrogate)
**Owner:** Steve Jobs (CPO) signs visual + Alex Morgan (UI/UX) on badge style + Larry Ellison on schema + Liam Smith (PM) on editorial workflow integration
**Walks:** Mythos S1433 (Correction notice reader-facing badge) deferred → partial
**Gating dependency:** `posts.correction_*` schema + correction policy public page (S1438) + editorial workflow (S1291-S1300 deferred)

## Why this exists

S1433 makes corrections visible. Silent edits destroy trust ("they're rewriting history"). Industry standard: visible badge + dated correction notice.

## Today's surrogate

- **Admin manual edit of `posts.content`** — works mechanically but invisible to reader.
- **No badge** — no UI surface signals a correction exists.

## Schema

```sql
ALTER TABLE posts ADD COLUMN correction_issued_at TIMESTAMP NULL;
ALTER TABLE posts ADD COLUMN correction_notice TEXT NULL;
ALTER TABLE posts ADD COLUMN correction_severity ENUM('minor','factual','retraction') NULL;
ALTER TABLE posts ADD COLUMN corrected_by BIGINT NULL;   -- FK Botble user (editor)
ALTER TABLE posts ADD COLUMN original_content_hash CHAR(64) NULL;  -- audit trail

CREATE TABLE post_correction_log (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  post_id BIGINT NOT NULL,
  before_text TEXT NOT NULL,        -- relevant excerpt before correction
  after_text TEXT NOT NULL,         -- after
  correction_notice TEXT NOT NULL,
  severity ENUM('minor','factual','retraction') NOT NULL,
  corrected_by BIGINT NOT NULL,
  corrected_at TIMESTAMP NOT NULL,
  INDEX (post_id),
  INDEX (corrected_at)
);
```

## Badge variants (Steve Jobs)

| Severity | Badge | Color | Placement |
|---|---|---|---|
| `minor` | "Corrected" small pill | neutral | below byline |
| `factual` | "Correction" badge with icon | amber | above article body + below byline |
| `retraction` | "RETRACTED" full-width banner | red | full top of article + body grayed |

## Reader-facing surface

### Article view

```
+----------------------------------------------+
| Jane Doe · 2026-05-24 · [Corrected]          |
|                                              |
| ⚠ Correction — 2026-05-26                   |
| An earlier version of this article said      |
| 50% instead of the correct 5%. The text      |
| has been updated.                            |
|                                              |
| Article body...                              |
+----------------------------------------------+
```

### Article card (in feeds, search, MG dossier)

- Subtle "Corrected" pill next to date.
- Click → article view with correction at top.

### Per-cluster propagation (per S1435)

- If post in cluster: cluster dossier shows "1 article corrected" at top.

## SEO

- `<meta property="article:modified_time"` reflects correction.
- JSON-LD `dateModified` updated.
- Correction body included in article HTML — search engines index it.

## A11y

- Badge has `aria-label="Correction issued on {date}"`.
- Banner has `role="alert"` for screen readers.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1433)
- Sister docs: `docs/GRIMBANEWS_CLUSTER_LEVEL_CORRECTION_PROPAGATION.md`, `docs/GRIMBANEWS_CORRECTION_POLICY_PUBLIC_PAGE_SCOPE.md`, `docs/GRIMBANEWS_AUTHOR_PROFILE_PAGE_SCOPE.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
