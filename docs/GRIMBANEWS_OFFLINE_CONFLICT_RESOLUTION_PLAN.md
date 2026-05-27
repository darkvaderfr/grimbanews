# GrimbaNews — Offline Mode Conflict Resolution Plan

**Status:** plan v0
**Owner:** Nina Patel (Lead FE) + Rajesh Kumar (Backend)
**Walks:** Mythos S1565 (offline mode — conflict resolution) deferred → partial
**Gating dependency:** S1554 cross-device sync + S1370 offline mode primitives + per-record version vectors.

## Why this exists

Once a reader's notebook + bookmarks + annotations sync across devices (S1554), offline conflicts become inevitable: edit a note on phone (offline), edit same note on laptop (online), reconnect phone. Without a defined policy, data is silently lost.

## v1 conflict-resolution strategy (per record type)

| Record type | Strategy |
|---|---|
| Bookmarks (add/remove) | Last-write-wins (idempotent set) |
| Annotations (note text) | Three-way merge with manual prompt if both sides diverged after common-ancestor |
| Notebook entries | Append-only timeline (entries always merge cleanly; reorder = last-write-wins) |
| Reader preferences | Per-key last-write-wins |
| Reading-history (opt-in) | Union (additive only) |

## Schema additions

```sql
ALTER TABLE annotations ADD COLUMN version_vector JSON NULL; -- {device_id: lamport_clock}
ALTER TABLE annotations ADD COLUMN updated_by_device VARCHAR(64) NULL;
ALTER TABLE reader_notebook_entries ADD COLUMN version_vector JSON NULL;
```

## Conflict UX

- On reconnect, if any annotation diverged, render a small inline diff card: "Cette annotation a 2 versions. Garder la version du téléphone / portable / fusionner ?".
- Default action 7 days later if no user choice: keep both as separate annotations (no data loss).
- Resolution decisions logged for support.

## Anti-patterns

- No silent overwrites.
- No server-side decides-without-asking-reader.
- No cap on conflict-card retention; conflicts persist until resolved.

## Cross-references

Master plan: S1565. Sister: S1554 (cross-device sync), S1568 (share-target), S1569 (offline analytics), S1370 (offline launch playbook).
