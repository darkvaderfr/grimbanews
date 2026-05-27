# GrimbaNews — Per-Source Editorial Board Tracking

**Status:** plan v0
**Owner:** Lucy Leai (Strategy)
**Walks:** Mythos S1667 (per-source editorial-board membership tracking) deferred → partial
**Gating dependency:** Operator-curated metadata.

## Why this exists

Source bias correlates with editorial leadership. Tracking masthead changes (editor-in-chief, opinion editor, executive editor turnover) gives leading indicator for bias-shift detection (Wave AAFF).

## Schema

```
source_editorial_board:
  source_id | role (editor_in_chief | opinion_editor | executive_editor | etc.)
   | name | start_date | end_date | source_url (press announcement)
```

## Cadence

- Quarterly: Lucy reviews each source's masthead.
- Per-change: log + trigger bias re-review.

## Per-source profile

Surfaced on `/sources/{slug}/team` (admin first, then public):
- Current masthead
- Per-role-change timeline
- Cross-link to bias-rating history (Wave AAJJ)

## Cross-references

Master plan: S1667. Sister: `docs/GRIMBANEWS_PER_SOURCE_OWNERSHIP_HISTORY_PLAN.md`, `docs/GRIMBANEWS_BIAS_SHIFT_DETECTION_PLAN.md`.
