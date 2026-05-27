# GrimbaNews — Classroom Teacher Discount Tier

**Status:** plan v0
**Owner:** Ray Dalio (CFO) + Lucy Leai (Strategy) + Liam Smith (PM)
**Walks:** Mythos S1679 (classroom teacher discount tier) deferred → partial
**Gating dependency:** Stripe paid tier (S1211) + verified-teacher eligibility check.

## Why this exists

Teachers using GrimbaNews for media-literacy classes need:
- Free or discounted classroom access (don't burden teachers personally)
- Bulk seats (per-student access)
- Per-school administrator portal

## Tier design

- **Free teacher tier:** verified educator gets free premium access + 30 student seats.
- **Paid school tier (per-seat):** €0.50/student/month for 31+ seats.
- **Institutional tier:** flat rate per school (€500-2000/yr depending on size).
- **University tier:** flat rate per institution (negotiated).

## Verification

- Teacher uploads verification doc (school-issued ID, .edu email, etc.).
- Lucy + Liam review weekly batches.
- Verification valid 12 months; re-verification at year-end.

## Per-school admin portal

- Per-school admin manages teacher accounts + student rosters.
- Per-school billing consolidated.
- Per-school analytics aggregated.

## Cross-references

Master plan: S1679. Sister: `docs/GRIMBANEWS_SCHOOLS_PROGRAM_EDU_SCOPE.md`, `docs/GRIMBANEWS_PER_LOCALE_SUBSCRIPTION_PRICING_DESIGN.md`.
