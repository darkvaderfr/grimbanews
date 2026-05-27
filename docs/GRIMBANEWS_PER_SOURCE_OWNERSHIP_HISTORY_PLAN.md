# GrimbaNews — Per-Source Ownership History Plan

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Lisa Nguyen (data)
**Walks:** Mythos S1621 (per-source ownership-history audit) deferred → partial
**Gating dependency:** Operator-curated ownership-event log.

## Why this exists

When a source changes ownership (Bezos buying WaPo, Bolloré buying CNews), editorial trajectory often shifts. Surfacing ownership history lets readers contextualize bias-rating changes over time and triggers operator re-review.

## Schema (gates on Vader migration approval)

```
source_ownership_events:
  id | source_id | event_date | from_owner | to_owner | ownership_type_before | ownership_type_after
   | event_type (acquisition | divestiture | restructure | ipo) | source_url
   | added_by (operator) | added_at
```

## Per-source surfacing

On `/sources/{slug}` source profile page:
- Per-source ownership timeline (visual)
- Per-event tooltip with details
- Auto-flag for editorial review when ownership_type changes

## Editor cadence

- Operator monitors press for ownership-change announcements.
- Per-event: operator logs + flags for editor review of bias rating.
- Editor reviews bias rating within 30 days of ownership change.
- Per-event published in transparency log (`docs/GRIMBANEWS_TRANSPARENCY_REPORT_SCOPE.md` Wave LLL).

## Cross-references

Master plan: S1621. Sister: `docs/GRIMBANEWS_BIAS_SHIFT_DETECTION_PLAN.md`, `docs/GRIMBANEWS_SOURCE_LEGAL_COVERAGE_AUDIT_PLAN.md`.
