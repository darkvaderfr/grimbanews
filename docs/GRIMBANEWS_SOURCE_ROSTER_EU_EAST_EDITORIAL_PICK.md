# GrimbaNews — EU East Source Roster Editorial Pick

**Status:** plan v0 (no sources seeded for EU east today)
**Owner:** Lucy Leai (Strategy) + Liam Smith (PM) on intake cadence + Michael O'Connor (Technical Writer) on per-source brief
**Walks:** Mythos S1021 (source roster expansion EU east) deferred → partial
**Gating dependency:** In-house editor cleared for Polish / Czech / Hungarian / Romanian editorial review.

## Tier-1 sources per country

- **Poland:** Gazeta Wyborcza (left/liberal), Rzeczpospolita (right), TVP Info (state), Onet.pl (center)
- **Czech Republic:** Hospodářské noviny (center), Deník N (center-left), iROZHLAS (public)
- **Hungary:** Telex (independent), 444.hu (center-left), HVG (center), Magyar Nemzet (right/pro-gov)
- **Romania:** Adevărul (center-right), Hotnews (center), Libertatea (center-left), Digi24 (center)
- **Bulgaria + Slovakia + Slovenia + Baltic states:** TBD per editor.

## Per-source seeder schema

`RssFeedsSeeder.php` row: name, website, feed_url, country (ISO-2), language (ISO-2), bias_rating, credibility_score, factuality_score, ownership_type, editorial_category, license_notes.

## Onboarding cadence

1. Native-speaker editor signs off bias rating.
2. RSS endpoint validated over 7-day window.
3. `php artisan grimba:classify-sources --source-id={id}`.
4. `grimba:poll-feeds --source-id={id}` daily 14 days before go-live.
5. Monitor `grimba_automation_runs` 30 days.

## Cross-references

Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1021).
Sister doc: `docs/GRIMBANEWS_DOM_TOM_SOURCE_ROSTER.md`.
Source seeder: `database/seeders/RssFeedsSeeder.php`.
