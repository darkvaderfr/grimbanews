# GrimbaNews — Per-Reader Yearly Wrap

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Liam Smith (PM) + Lisa Nguyen (data)
**Walks:** Mythos S1635 (per-reader yearly reading-pattern wrap) deferred → partial
**Gating dependency:** Per-user reading-time analytics (Wave AAHH partial).

## Why this exists

Spotify Wrapped-style yearly recap. Per-reader celebration of their year of GrimbaNews. Drives retention + share-vouching.

## v1 design

Mid-December cron generates per-reader wrap:
- Total articles read
- Total minutes spent
- Top-5 topics by reading time
- Top-5 sources read
- Per-bias-camp breakdown ("Vous avez lu 32% gauche, 28% centre, 30% droite, 10% non classé")
- Reading streak achievements (per Wave AADD)
- 1 standout cluster the reader engaged most with
- "Mois le plus actif"

## UX

- `/account/wrap-{year}` reader-only page (premium-tier surface).
- Shareable per-reader OG card (gates on Wave NNN OG controller extension).
- Per-section deep-link.

## Privacy guardrails

- Opt-out toggle in `/account/preferences`.
- Aggregate metrics only; no per-article reading-time exposed publicly.
- DSAR export includes wrap data.

## Cross-references

Master plan: S1635. Sister: `docs/GRIMBANEWS_PER_USER_READING_TIME_ANALYTICS_PLAN.md`, `docs/GRIMBANEWS_READING_STREAK_GAMIFICATION_DESIGN.md`, `docs/GRIMBANEWS_EDITORIAL_ARCHIVES_YEAR_RETROSPECTIVE.md`.
