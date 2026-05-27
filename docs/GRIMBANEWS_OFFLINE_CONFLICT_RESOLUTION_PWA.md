# GrimbaNews — Offline Conflict Resolution PWA

**Status:** plan v0 (PWA shipped, conflict resolution deferred)
**Owner:** Nina Patel (Lead FE) + Rajesh Kumar (backend)
**Walks:** Mythos S1690 (offline conflict + PWA share-target) deferred → partial
**Gating dependency:** Reader-account sync infrastructure.

## Why this exists

PWA reader saves an article to coffre offline. Comes back online. Server-side coffre already has the article (cross-device). Conflict resolution needed: which wins?

## v1 rules

1. **Idempotent operations:** add-to-coffre is idempotent (no conflict possible).
2. **Last-write-wins on per-article metadata:** if reader edited per-article note offline, last edit (by timestamp) wins on sync.
3. **Per-folder/tag conflicts:** merge sets union.
4. **Per-cluster Q&A submission:** queue offline, submit on reconnect.

## v1 share-target

PWA registers as Web Share Target. When reader shares URL from other apps:
1. Service worker intercepts.
2. Opens GrimbaNews PWA with URL.
3. Looks up if URL is in a tracked cluster.
4. If yes: open cluster page. If no: search by URL.

## Schema

No new schema — existing coffre tables suffice.

## Cross-references

Master plan: S1690. Sister: Wave KKKK PWA + bookmark plans.
