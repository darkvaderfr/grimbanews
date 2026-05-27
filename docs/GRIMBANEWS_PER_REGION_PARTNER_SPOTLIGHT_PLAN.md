# GrimbaNews — Per-Region Partner Spotlight Plan

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Liam Smith (PM) + per-region editor
**Walks:** Mythos S1583 (per-region partner spotlight) deferred → partial
**Gating dependency:** First signed regional partner (Wave LLL partnership program).

## Why this exists

Once GrimbaNews signs a partner publisher per region, surfacing the partner's content with attribution builds trust + helps partner discovery.

## v1 design

On `/region/{slug}` page, new "Partenaire de la semaine" rail:
- Featured partner logo + per-partner-spotlight blurb
- 3 latest articles from the partner
- Click → partner-feed page (`/partenaire/{partner-slug}`)
- Per-partner badge on every partner-sourced article

## Rotation cadence

- Weekly rotation per region.
- Editor chooses next-week partner from active-partner pool.
- Per-partner appears at most once per quarter (avoid favoritism).

## Schema (gates on partnership program code shipping)

```
partner_spotlights:
  region | partner_id | week_starting | curator_note (TEXT) | created_by | created_at
```

## Editorial guardrails

- Per-spotlight curator note: 1-2 sentences explaining why this partner this week.
- Sponsored-content exclusion: spotlight is editorial-curation, NOT paid placement.
- Per-partner attribution always shown.

## Cross-references

Master plan: S1583. Sister: `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md` (Wave LLL), `docs/GRIMBANEWS_PER_REGION_HOMEPAGE_HERO_LOCALIZATION.md`.
