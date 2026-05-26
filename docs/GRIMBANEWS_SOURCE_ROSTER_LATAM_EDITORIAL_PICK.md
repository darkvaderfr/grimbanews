# GrimbaNews — LATAM Source Roster Editorial Pick

**Status:** plan v0 (no LATAM sources seeded today)
**Owner:** Lucy Leai (Strategy) + Liam Smith (PM) + native ES + PT-BR editor TBD
**Walks:** Mythos S1022 (source roster LATAM) deferred → partial
**Gating dependency:** Native ES + PT-BR editor cleared for editorial review.

## Tier-1 sources per country

- **Mexico:** El Universal (center), Reforma (center-right), La Jornada (left), Animal Político (independent center).
- **Brazil:** Folha de S.Paulo (center-right), O Globo (center-right), Estadão (center-right), El País Brasil (center-left).
- **Argentina:** Clarín (center-right), La Nación (right), Página/12 (left), Infobae (center-right).
- **Colombia:** El Tiempo (center), El Espectador (center-left), Semana (center-right).
- **Chile:** El Mercurio (right), La Tercera (center-right), BioBioChile (center).
- **Peru / Venezuela / Cuba / Uruguay:** TBD per editor.

## Per-source seeder schema

Mirror EU east doc: `RssFeedsSeeder.php` row with name, website, feed_url, country, language, bias_rating, credibility_score, factuality_score, ownership_type, editorial_category, license_notes.

## Onboarding cadence

1. Native-speaker editor signs off.
2. RSS endpoint 7-day validation.
3. `grimba:classify-sources --source-id={id}`.
4. `grimba:poll-feeds --source-id={id}` 14 days.
5. 30-day monitor in `grimba_automation_runs`.

## Cross-references

Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1022).
Sister: `docs/GRIMBANEWS_SOURCE_ROSTER_EU_EAST_EDITORIAL_PICK.md`, `docs/GRIMBANEWS_DOM_TOM_SOURCE_ROSTER.md`.
