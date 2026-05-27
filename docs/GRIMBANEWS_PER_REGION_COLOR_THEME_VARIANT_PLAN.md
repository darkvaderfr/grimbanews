# GrimbaNews — Per-Region Color Theme Variant Plan

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Alex Morgan (UI/UX) + Nina Patel (Lead FE)
**Walks:** Mythos S1613 (per-region color/theme variant) deferred → partial
**Gating dependency:** Per-region brand-team review + per-locale launch.

## Why this exists

Default FR theme uses warm cream + ink + tan editorial palette. Other regions may benefit from subtle theme variations that respect local design conventions while keeping the GrimbaNews identity coherent.

## Theme spec per region

Always-shared:
- Bias palette (L blue / C green / R red / MG purple / BS blueviolet) — non-negotiable
- Typography (Fraunces serif + Public Sans sans) — Iboga-wide brand
- Glass-panel grain texture — Steve cinematic standard

Variable per region:
- Primary accent
- Secondary accent
- Cream-ink ratio

## Per-region variants

| Region | Primary | Secondary | Cream-ink ratio |
|---|---|---|---|
| FR (default) | tan #DCBC9C | ink #1A1713 | 78/22 |
| DE | grey-blue #4A5A6B | ink #1A1713 | 82/18 |
| BR | warm orange #D97757 | ink #1A1713 | 75/25 |
| JP | indigo #1E3A5F | ink #1A1713 | 85/15 |
| Other | FR default |

## Per-region cinematic motion

- FR: subtle (default)
- DE: very subtle (German clean-design tradition)
- BR: warm (slightly more energetic)
- JP: minimal (Ma — negative space respected)

## Switching

Per-region CSS via `app()->getLocale()` → `body[data-region="{region}"]` attribute → CSS var swaps.

## Cross-references

Master plan: S1613. Sister: `feedback_steve_design_language.md` (Mnemo audit standard), `docs/GRIMBANEWS_DE_LANDING_PAGE_SCOPE.md` (Wave WWW).
