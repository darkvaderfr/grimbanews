# GrimbaNews — Arabic (AR) Locale Catalog Plan

**Status:** design v0 (no `lang/ar.json`; AR detector path not shipped; RTL chrome S1142 separate)
**Owner:** Liam Smith (PM) + Nina Patel (Lead Frontend, RTL audit) + Michael O'Connor (Technical Writer)
**Walks:** Mythos S1132 (AR site UI catalog) deferred → partial
**Gating dependency:** AR-native editorial reviewer + RTL chrome shipped (S1142) before catalog can ship.

## Why this exists

S1132 honest-deferred on three blockers: catalog file, detector AR path, and RTL chrome (S1142). Catalog spec + per-locale ops are operator-side.

## Catalog scope

Mirror of ES Tier 1-4 (~1,200 keys). AR specifics:

- **MSA (Modern Standard Arabic)** baseline — pan-Arab readership.
- **No regional dialects on launch** — Egyptian / Maghrebi / Gulf variants would fragment audience; MSA mirrors how Al Jazeera / BBC Arabic publish.
- **Bidirectional text** in mixed contexts (English brand name "GrimbaNews" embedded in RTL paragraph) — handled by Unicode bidi algorithm but needs CSS test.
- **Numerals**: Eastern Arabic numerals (٠-٩) vs Western (0-9) — catalog ships Western (matches Al Jazeera Arabic + most pan-Arab press).
- **Date formatting**: Hijri + Gregorian dual display under Vader call — defer to second-pass.

## Per-locale ops

Same shape as ES, plus:

- RTL Playwright smoke tests (cards mirror, drop-shadows mirror, scroll-indicators mirror)
- Right-aligned headers + left-aligned numbers
- Locale-specific font fallback chain (Cairo, Tajawal, Noto Naskh Arabic) — already in font-loader

## AR editorial relevance

Strong fit for GrimbaNews FR-Africa source coverage when surfaced in AR (Maghreb diaspora; pan-African Arabic-reading audience). Also opens MENA editorial doors.

## Acceptance gates

Same as ES + RTL acceptance gates (`docs/GRIMBANEWS_RTL_SUPPORT_PLAN.md`).

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1132 row)
- Sister docs: `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_RTL_SUPPORT_PLAN.md`, `docs/GRIMBANEWS_HE_LOCALE_CATALOG_PLAN.md`
- Existing infrastructure: `lang/fr.json`, `App\Support\GrimbaLanguageDetector`
