# GrimbaNews — Blocked Sources Server Persistence Plan

**Status:** plan v0 (no `member_blocked_sources` table)
**Owner:** Rajesh Kumar (Backend) on schema + Larry Ellison on multi-tenant isolation + Sara Chen on member-PII
**Walks:** Mythos S1515 (Blocked sources — server-persisted) deferred → partial
**Gating dependency:** Member auth + S1514 UI design + opt-in posture.

## Why this exists

S1515 is the backend half of the block-source feature. UI (S1514) is the surface; this is the substrate.

## Schema (target)

```sql
CREATE TABLE member_blocked_sources (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  member_id BIGINT NOT NULL,                -- FK Botble members
  source_id BIGINT NOT NULL,                -- FK news_sources
  blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reason VARCHAR(64) NULL,                  -- optional: 'paywall','low_trust','other'
  UNIQUE KEY (member_id, source_id),
  INDEX (member_id),
  INDEX (source_id)
);
```

## Query application

- Wrap post queries (home, pour-vous, category, search) in a helper:

```php
->whereNotIn('source_id', $blockedSourceIds)
```

- Helper: `MemberBlockedSources::idsFor($memberId): array` — memoized per-request.
- Anonymous readers: cookie `blocked_sources` (comma-joined IDs, max 50 entries).

## Cluster page exception

- `/dossier/{id}` does NOT apply the filter. Rationale: cluster transparency — if reader has blocked a source, they still see in the dossier "this is who's covered the story." Source name surfaces with bias-distribution context.

## Sync semantics

- Login: cookie blocks merge into server rows (union).
- Logout: server preserved; cookie cleared if "remember on this device" off.

## Privacy posture

- Reason field optional + free-text — never used for personalization (Sara Chen non-negotiable).
- Member deletion → cascade-delete blocks.

## Limits

- Max 200 blocked sources per member (UI rate-limit reasonable; otherwise abuse vector).
- Operator dashboard alert if any one source blocked by >5% of opted-in members (editorial signal — needs investigation).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1515)
- Sister docs: `docs/GRIMBANEWS_BLOCKED_SOURCES_UI_DESIGN.md`, `docs/GRIMBANEWS_FOLLOWED_TOPICS_SERVER_PERSISTENCE_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
