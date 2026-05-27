# GrimbaNews — Climate v2 Deep Source Roster

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + per-region editor + climate-specialist editor TBD
**Walks:** Mythos S2182 (Climate v2 deep source-roster) deferred → partial
**Gating dependency:** Climate-specialist editor onboarded; surrogate today is `grimba:seed-thin-category-sources` (S1024 partial).

## Why this exists

Climate coverage in mainstream press is structurally thin: bias-mix skewed by political framing, science-vs-policy collapsed into single bucket, IPCC reports under-reported relative to political-climate noise. A deep climate-specific roster delivers depth + diversity.

## Tier-1 sources to seed

### Specialist climate-only outlets
- **Carbon Brief** (UK, EN) — science-policy explainer-focused, factuality > 90.
- **Reporterre** (FR) — environment + climate, left-leaning, factuality > 80.
- **Mongabay** (English, global) — biodiversity + tropical, science-grounded.
- **InsideClimate News** (US) — investigative climate journalism.
- **Climate Home News** (UK) — international climate policy.
- **DeSmog** (UK + US) — fossil-fuel investigative.

### Science-press
- **Nature Climate Change** (peer-reviewed, gated by Wave S2186 preprint-integration).
- **Science** policy briefs.
- **PNAS Climate** specials.

### National-press climate desks
- **Le Monde Planète** (FR section).
- **The Guardian Environment** (UK section).
- **Spiegel Klima** (DE section).

### Operator-curated thin-category seeder

`grimba:seed-thin-category-sources --category=climate` adds the above as a single batch with consistent metadata (bias, factuality, license, editorial_category="climat").

## Per-source onboarding cadence

Mirror Wave UUUU regional roster pattern:
1. Editorial sign-off on bias rating per source.
2. RSS endpoint validated.
3. 14-day poll test.
4. 30-day monitor in `grimba_automation_runs`.

## Cross-references

Master plan: S2182. Sister: `docs/GRIMBANEWS_SOURCE_ROSTER_EU_EAST_EDITORIAL_PICK.md` (Wave UUUU regional roster pattern), `docs/GRIMBANEWS_PER_REGION_TRUST_DASHBOARD_PLAN.md` (per-region trust monitoring).
Code: `database/seeders/RssFeedsSeeder.php`, `app/Console/Commands/GrimbaSeedThinCategorySources.php` (planned per S1024).
