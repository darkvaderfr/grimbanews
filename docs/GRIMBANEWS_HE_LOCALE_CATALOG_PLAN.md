# GrimbaNews — Hebrew (HE) Locale Catalog Plan

**Status:** design v0 (no `lang/he.json`; HE detector path not shipped; RTL chrome S1142 separate)
**Owner:** Liam Smith (PM) + Nina Patel (Lead Frontend, RTL audit) + Michael O'Connor (Technical Writer)
**Walks:** Mythos S1137 (HE site UI catalog) deferred → partial
**Gating dependency:** HE-native editorial reviewer + RTL chrome shipped (S1142).

## Catalog scope

Mirror of ES Tier 1-4 (~1,200 keys). HE specifics:

- **RTL** — shares mirror requirements with AR (S1132/S1142).
- **Niqqud (vowel marks)** off by default in news contexts (matches Haaretz, Times of Israel HE register).
- **Editorial categories**: פוליטיקה, כלכלה, חברה, בינלאומי, אפריקה, טכנולוגיה, בריאות, מדע, תרבות, ספורט, סביבה, משפט, חינוך, הגירה.
- **Numerals**: Western Arabic numerals (0-9) — standard in HE press.

## HE editorial relevance

Smaller absolute audience but high engagement on Middle East / Israel-Palestine coverage where neutral framing + bias bar are explicit value-adds.

## Acceptance gates

Same as ES + RTL acceptance gates + HE font fallback check.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1137 row)
- Sister docs: `docs/GRIMBANEWS_AR_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_RTL_SUPPORT_PLAN.md`, `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`
- Existing infrastructure: `lang/fr.json`, `App\Support\GrimbaLanguageDetector`
