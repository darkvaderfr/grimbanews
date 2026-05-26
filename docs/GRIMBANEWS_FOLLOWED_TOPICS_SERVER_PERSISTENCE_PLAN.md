# GrimbaNews — Followed Topics Server Persistence Plan

**Status:** plan v0 (followed topics stored in cookie only; no `member_followed_categories` table)
**Owner:** Rajesh Kumar (Backend) on schema + Nina Patel on UI + Sara Chen on member-PII posture
**Walks:** Mythos S1513 (Followed topics — server-persisted) deferred → partial
**Gating dependency:** Member auth + opt-in to server-side persistence (per S1501 privacy posture).

## Why this exists

S1513 promotes the existing cookie-based saved-categories from per-device to per-account. Today the pour-vous cookie holds saved topics — but it's lost if reader clears cookies / changes browsers / opens private mode.

## Today's surrogate

- **`pour_vous` cookie** holds saved categories (per-device, per-browser).

## Schema (target)

```sql
CREATE TABLE member_followed_categories (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  member_id BIGINT NOT NULL,                -- FK Botble members
  category_id BIGINT NOT NULL,              -- FK categories
  followed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY (member_id, category_id),
  INDEX (member_id),
  INDEX (category_id)
);
```

## Sync semantics

- On login: server-side rows merge into cookie (server wins on conflict — explicit opt-in beats incidental cookie).
- On follow/unfollow: server row added/removed + cookie updated for parity within session.
- On logout: cookie cleared (deferred — operator preference TBD).

## UI surface

- "Suivre" / "Follow" pill on `/categorie/{slug}` page header.
- "Mes sujets suivis" / "My followed topics" on `/account` page — lists + lets remove.
- Cross-device parity guarantee surfaced in copy: "Synced across all your devices."

## Privacy posture (Sara Chen)

- Per S1501 — opt-in only. Anonymous cookie path preserved as fallback for non-members.
- Member can clear all follows in one click via account preferences.
- 30-day deletion on member deletion (GDPR).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1513)
- Sister docs: `docs/GRIMBANEWS_BLOCKED_SOURCES_SERVER_PERSISTENCE_PLAN.md`, `docs/GRIMBANEWS_FOLLOWED_AUTHORS_PLAN.md`, `docs/GRIMBANEWS_ML_FEED_DESIGN_DOC.md`
- Existing infra: pour_vous cookie + `/account` page
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
