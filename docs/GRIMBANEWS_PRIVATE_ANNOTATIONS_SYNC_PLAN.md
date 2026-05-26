# GrimbaNews — Private Annotations Cross-Device Sync Plan

**Status:** plan v0 (single-device cookie behavior on bookmarks; no sync infra for annotations)
**Owner:** Rajesh Kumar (Backend) on sync API + Hannah Kim (Platform) on resolution strategy + Sara Chen on encryption-at-rest
**Walks:** Mythos S1546 (Private annotations sync across devices) deferred — partial
**Gating dependency:** Annotation schema (S1541) + member auth + offline mode (S1564 partial) bidirectional sync layer.

## Why this exists

S1546 is the layer that keeps the same highlight visible on a reader's phone and laptop. Without sync, highlight on laptop is invisible from phone — UX feels broken.

## Today's surrogate

- Member-attached highlights are server-side from creation (per S1541 schema) — so technically sync is free for member-mode.
- Anonymous-mode highlights deferred (cookie storage limit + privacy hazard).

## Sync semantics

- **Member mode:** server is source of truth. Each device fetches `/api/highlights?since={cursor}` on load.
- **Conflict resolution:** last-write-wins per highlight by `updated_at`. (Highlights are append-mostly; conflicts rare.)
- **Offline-then-online:** local queue of pending mutations flushes on reconnect with retry-on-fail.

## API contract (target)

```
GET  /api/highlights?since=2026-05-26T00:00:00Z    → list of new/updated
POST /api/highlights                               → create
PATCH /api/highlights/{id}                         → update (note, visibility)
DELETE /api/highlights/{id}                        → soft-delete (orphan flag)
```

## Real-time channel (optional v2)

- Server-sent events on `/api/highlights/stream` for instant cross-device updates.
- Defer until v1 polling proves insufficient.

## Encryption at rest

- Selected text + note stored unencrypted (server queries needed for FTS within saved highlights — S1556).
- Counsel review: do member-authored notes warrant member-side encryption? Adds key-management complexity. Defer to v2.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1546)
- Sister docs: `docs/GRIMBANEWS_ANNOTATION_SCHEMA.md`, `docs/GRIMBANEWS_HIGHLIGHT_UI_DESIGN.md`, `docs/GRIMBANEWS_BOOKMARK_SEARCH_WITHIN_SAVED_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
