# GrimbaNews — Africa Newsletter Cadence (Separate from Main)

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Africa curator + Liam Smith (PM)
**Walks:** Mythos S1758 (Africa newsletter cadence separate from main) deferred → partial
**Gating dependency:** `newsletter_subscriptions` table extension for per-edition subscription bucket.

## Why this exists

Per-Africa newsletter subscriber wants Africa-relevant cadence + content distinct from Hexagone-default. Today single newsletter_subscriptions entry.

## Per-edition subscription schema (extension)

```
ALTER TABLE newsletter_subscriptions
  ADD COLUMN edition VARCHAR(32) DEFAULT 'fr-fr';
-- 'fr-fr', 'africa-fr', 'africa-en', 'us-en', 'br-pt', etc.
```

## Per-Africa-edition cadence

- Daily 06:00 UTC (West Africa AM cycle).
- Weekly Saturday 09:00 UTC (deep editorial picks).
- Per-event: per-election, per-summit, per-major-event special.

## Per-Africa-edition template

Distinct from Hexagone default:
- Per-region hero (West / Central / East / Maghreb).
- Per-region cluster picks.
- Per-Africa Middle Ground signal.
- Per-Africa Blindspots.
- Per-Africa investigative spotlight.
- African-curator's pick.

## Per-edition unsubscribe granularity

Reader can per-edition opt-out without losing other-edition subscriptions.

## Cross-references

Master plan: S1758. Sister: `docs/GRIMBANEWS_AFRICA_FRANCOPHONE_EDITORIAL_CADENCE.md`, `docs/GRIMBANEWS_DAILY_REPORT_EMAIL_TEMPLATE_DESIGN.md`.
