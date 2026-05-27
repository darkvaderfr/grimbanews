# GrimbaNews — Per-Region Partner Termination Workflow

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + counsel + per-region editor
**Walks:** Mythos S1664 (per-region partner termination workflow) deferred → partial
**Gating dependency:** First active regional partner per partnership program (Wave LLL).

## Why this exists

Partnerships sometimes end (financial reorganization, editorial divergence, mutual decision). Need a clean wind-down workflow that respects:
1. Reader continuity (no link rot)
2. Partner intellectual property (depublish vs preserve)
3. Per-source legal obligations (license terms)

## Workflow

1. Termination initiated by either party (30-day notice per syndication agreement).
2. Operator triggers termination via `/admin/grimba/partners/{id}/terminate`:
   - Termination date
   - Per-cluster handling: depublish | preserve-with-attribution
   - Per-attribution surface cleanup: badge removal vs preservation
3. Editor reviews:
   - Per-partner content review (still-relevant clusters)
   - Per-attribution badge update on existing posts
4. Wind-down execution:
   - Partnership-program landing card removed (per Wave LLL plan)
   - Per-partner-stream filter URL keeps working but with "Archived" badge
   - Last 30 days of partner content stays accessible
   - Older partner content per per-cluster decay policy (Wave AAEE)
5. Per-partner data export per partnership agreement
6. Per-partner contract closure log

## Cross-references

Master plan: S1664. Sister: `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md` (Wave LLL), `docs/GRIMBANEWS_PER_SOURCE_BAN_REINSTATE_WORKFLOW.md`.
