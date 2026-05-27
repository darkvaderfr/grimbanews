# GrimbaNews — Canada / Quebec FR-CA Dialect Routing

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Lucy Leai (Strategy)
**Walks:** Mythos S1649 (Canada pilot — Quebec FR-CA dialect handling) deferred → partial
**Gating dependency:** FR-CA dialect detection (Wave SUB-19 S1624).

## Why this exists

GrimbaNews reader in Quebec wants Quebec-specific framing. Today FR-CA reader sees Hexagone-default content. Per-dialect routing changes:
- Default region: /region/quebec
- Default newsletter cadence: Quebec-time-zone
- Default editorial register: per-FR-CA tone

## v1 routing

1. On first visit, detect locale via Accept-Language header + IP geolocation.
2. If FR + Canada → set `edition=quebec` cookie.
3. /region/quebec page becomes home default.
4. Per-FR-CA content surfaced first.
5. User can opt back to Hexagone via UI toggle.

## Per-FR-CA editorial cadence

- Daily morning cycle aligned with EDT/EST.
- Per-Quebec-event coverage: Festival d'été, Just for Laughs, election cycles.
- Per-FR-CA source roster: Le Devoir, La Presse, Radio-Canada, TVA, Le Soleil.

## Schema (no new tables; uses existing region detection)

`members.preferred_edition VARCHAR(32) DEFAULT 'fr-fr'` covers per-reader override.

## Cross-references

Master plan: S1649. Sister: `docs/GRIMBANEWS_FR_CA_DIALECT_DETECTION_PLAN.md`, `docs/GRIMBANEWS_PER_REGION_HOMEPAGE_HERO_LOCALIZATION.md`.
