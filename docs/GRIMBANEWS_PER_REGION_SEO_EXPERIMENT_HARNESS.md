# GrimbaNews — Per-Region SEO Experiment Harness

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Lisa Nguyen (data) + per-region editor
**Walks:** Mythos S1720 (per-region SEO experiment harness) deferred → partial
**Gating dependency:** Per-region cluster volume + 90-day-of-search-Console data.

## Why this exists

Per-region SEO behavior differs (Google Discover surfaces differently in DE vs FR vs BR). A/B testing per-region surface tweaks needs structured harness.

## Experiments to run

1. **Per-region homepage hero layout:** card-style vs list-style.
2. **Per-region cluster card density:** 3-col vs 2-col.
3. **Per-region OG image variant:** colored bar vs minimalist.
4. **Per-region meta description style:** declarative vs question.

## Per-region cohort assignment

Cookie-based, similar to Wave AAFF ML feed A/B harness:
- 50% control (default layout)
- 50% variant (new layout)
- Per-region cohort isolated (don't cross-pollinate FR experiment with DE)

## Metrics

- Per-region click-through rate from search
- Per-region session depth
- Per-region 30-day-return rate
- Per-region newsletter conversion

## Cross-references

Master plan: S1720. Sister: `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md` (Wave LLL), `docs/GRIMBANEWS_AB_PERSONALIZATION_FLEET_DESIGN.md`, `docs/GRIMBANEWS_PER_REGION_HOMEPAGE_HERO_LOCALIZATION.md`.
