# GrimbaNews — Author Table Schema

**Status:** plan v0 (no authors / post_authors table; Botble's author_id FK exists at posts but no journalist-profile metadata)
**Owner:** Larry Ellison (VP DBA) on schema + Rajesh Kumar (Backend) on migration + Liam Smith (PM) on field set + Sara Chen on PII posture
**Walks:** Mythos S1411 (Author table schema) deferred → partial
**Gating dependency:** In-house editor seat program (S1401 deferred) + counsel review of journalist-PII storage + at least one byline going live

## Why this exists

S1411 separates "GrimbaNews staff journalist" from generic Botble admin user. Today every post is authored by an admin record — no journalist-level metadata (specialty, locale, bio, social, photo). Without a dedicated table the byline + author follow + author RSS features (S1412-S1419) cannot land.

## Today's surrogate

- **`posts.author_id`** + Botble polymorphic `author_type` — used for admin attribution; not journalist-grade.
- **`/methodology`** page lists editorial principles (operator-side) but no per-author bio.

## Schema

```sql
CREATE TABLE journalists (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  user_id BIGINT NULL,                  -- FK Botble user (optional — external contributors not Botble users)
  slug VARCHAR(160) NOT NULL UNIQUE,    -- 'jane-doe'
  display_name VARCHAR(255) NOT NULL,   -- 'Jane Doe'
  pen_name VARCHAR(255) NULL,           -- if differs from display
  email VARCHAR(255) NULL,
  bio_short VARCHAR(280) NULL,
  bio_long TEXT NULL,
  photo_path VARCHAR(255) NULL,
  twitter VARCHAR(64) NULL,
  bluesky VARCHAR(64) NULL,
  mastodon VARCHAR(128) NULL,
  linkedin VARCHAR(160) NULL,
  website VARCHAR(255) NULL,
  specialties JSON DEFAULT '[]',        -- ['climate','politics','tech']
  locales_written JSON DEFAULT '[]',    -- ['fr','en']
  verified BOOLEAN DEFAULT FALSE,
  verified_at TIMESTAMP NULL,
  verified_by BIGINT NULL,              -- FK Botble user (admin who verified)
  active BOOLEAN DEFAULT TRUE,
  meta JSON NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX (slug),
  INDEX (active, verified)
);

CREATE TABLE post_journalists (
  post_id BIGINT NOT NULL,
  journalist_id BIGINT NOT NULL,
  role ENUM('author','co-author','editor','translator') DEFAULT 'author',
  byline_order TINYINT DEFAULT 0,        -- multi-author order
  PRIMARY KEY (post_id, journalist_id, role),
  INDEX (journalist_id, post_id)
);
```

## PII posture (Sara Chen)

- `email` — staff-only visibility (never API-leaked).
- `bio_short`, `bio_long`, `photo_path`, social handles — public.
- `verified_by` — staff-only.
- Right-to-be-forgotten: `journalists.active = false` + redact display name from old posts after consent.

## Verification workflow

- Default `verified = false` on creation.
- Verification by editorial admin → sets `verified = true`, `verified_at`, `verified_by`.
- Verified badge in byline UI.
- Annual re-verification (cron flag) for active journalists.

## Migration plan

- New table — no data migration.
- Backfill: every existing post stays attributed to Botble admin — `post_journalists` row only added when a journalist is explicitly associated.
- Default-fallback byline: "GrimbaNews Editorial Staff" if no `post_journalists` row.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1411)
- Sister docs: `docs/GRIMBANEWS_AUTHOR_PROFILE_PAGE_SCOPE.md`, `docs/GRIMBANEWS_AUTHOR_CONTRIBUTION_LOG_SCHEMA.md`, `docs/GRIMBANEWS_AUTHOR_RSS_FEED_DESIGN.md`, `docs/GRIMBANEWS_AUTHOR_ANALYTICS_DASHBOARD_SCOPE.md`
- Existing: `posts.author_id` + Botble polymorphic author
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
