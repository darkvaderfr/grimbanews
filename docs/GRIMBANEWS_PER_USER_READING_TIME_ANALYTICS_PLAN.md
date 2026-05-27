# GrimbaNews — Per-User Reading-Time Analytics

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Steve Jobs (CPO) + Sara Chen (CISO) on privacy posture
**Walks:** Mythos S1601 (per-user reading-time analytics) deferred → partial
**Gating dependency:** Reader-tier subscription (Wave AACC) + privacy review.

## Why this exists

Reading-time is the single highest-signal engagement metric. Per-reader history surfaces: which topics resonate, which article lengths read through, which times of day they read.

## v1 design

- Client-side: pageshow + scroll-pause tracker measures actual time-on-page.
- Beacon API submits aggregate metrics (no real-time tracking — batched every 30s).
- Stored per-reader if logged in; per-cookie if anonymous (cookie-only data per Wave LLL privacy posture).

## Analytics surface

`/account/insights` (premium-tier per Wave AACC):
- Top-5 topics by reading-time this month
- Reading streak (per Wave AADD)
- Avg time per article + median
- Most-read sources
- Reading-pattern heatmap (day-of-week × hour-of-day)
- Topics read across bias spectrum

## Privacy guardrails

- Per-reader data: only for logged-in members + opt-in.
- Anonymous readers: per-cookie aggregate, no cross-device tracking.
- Data deletable via `/account/preferences`.
- DSAR export per Wave KKKK.
- No external analytics (no Google Analytics) — server-side only.

## Schema (gates on Vader migration)

```
member_reading_events:
  member_id | post_id | time_on_page_sec | scroll_depth_pct | recorded_at
  -- 90-day retention rolling window
```

## Aggregate-only public metric

Per-cluster avg reading time exposed on `/comparatif/{id}` ("Lecture moyenne: X min"). Reader-facing transparency about engagement.

## Cross-references

Master plan: S1601. Sister: `docs/GRIMBANEWS_AB_PERSONALIZATION_FLEET_DESIGN.md`, `docs/GRIMBANEWS_READING_STREAK_GAMIFICATION_DESIGN.md`, `docs/GRIMBANEWS_PII_FIELD_INVENTORY.md`.
