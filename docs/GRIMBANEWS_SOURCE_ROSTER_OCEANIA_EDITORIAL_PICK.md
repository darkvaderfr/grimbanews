# GrimbaNews — Oceania Source Roster Editorial Pick

**Status:** plan v0 (no Oceania sources seeded today)
**Owner:** Lucy Leai (Strategy) + native EN-AU + EN-NZ editor TBD
**Walks:** Mythos S1026 (source roster Oceania) deferred → partial
**Gating dependency:** Native editor cleared.

## Tier-1 sources per country

- **Australia:** Sydney Morning Herald (Fairfax/center), The Age (Fairfax/center-left), The Australian (Murdoch/right), ABC News (public/center), The Guardian Australia (left), Crikey (independent/center-left), Daily Telegraph (Murdoch/right).
- **New Zealand:** NZ Herald (center), Stuff (center), RNZ (public/center), The Spinoff (independent/center-left), Newshub (private/center), Otago Daily Times (private/center).
- **Pacific islands:** RNZ Pacific (NZ public), ABC Pacific Beat (AU public), Islands Business (private/center).

## Ownership-concentration callouts

- News Corp (Murdoch) controls ~60% of Australian metropolitan circulation per ACCC 2024 report. Tag `ownership_type='private'` with `parent_org='News Corp'` so reader sees concentration in source-page badge.
- Nine Entertainment (Fairfax) controls SMH + The Age. Same pattern.
- ABC + RNZ are public broadcasters; tag `ownership_type='public'`.

## Per-source seeder schema

Same as prior packs. AU/NZ English variants reuse `lang/en.json` catalog; no per-locale catalog needed.

## Onboarding cadence

Mirror prior packs.

## Cross-references

Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1026).
Sister: `docs/GRIMBANEWS_SOURCE_ROSTER_EU_EAST_EDITORIAL_PICK.md`.
