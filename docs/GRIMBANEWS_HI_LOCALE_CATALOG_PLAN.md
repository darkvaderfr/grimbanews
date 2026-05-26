# GrimbaNews — Hindi (HI) Locale Catalog Plan

**Status:** design v0 (no `lang/hi.json`; HI detector path not shipped)
**Owner:** Liam Smith (PM) + Nina Patel (Lead Frontend) + Michael O'Connor (Technical Writer)
**Walks:** Mythos S1138 (HI site UI catalog) deferred → partial
**Gating dependency:** HI-native editorial reviewer; Devanagari font fallback verification.

## Catalog scope

Mirror of ES Tier 1-4 (~1,200 keys). HI specifics:

- **Devanagari script** — Noto Sans Devanagari fallback required.
- **Hindi-Urdu register decision**: ship Hindi (Devanagari) first; Urdu (Nastaliq, RTL) is separate locale decision.
- **Editorial categories**: राजनीति, अर्थव्यवस्था, समाज, अंतर्राष्ट्रीय, अफ्रीका, प्रौद्योगिकी, स्वास्थ्य, विज्ञान, संस्कृति, खेल, पर्यावरण, न्याय, शिक्षा, प्रवास.
- **English code-switching**: Hindi web content frequently mixes English terms; chrome stays pure Hindi but article-body retains code-switching as-translated.

## HI editorial relevance

Large India audience + global Indian diaspora. Africa coverage (per GrimbaNews FR strength) has IN-IN editorial relevance via India-Africa diplomatic ties.

## Acceptance gates

Same as ES + Devanagari font check + line-height tuning (Devanagari needs more line-height than Latin).

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1138 row)
- Sister docs: `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`
- Existing infrastructure: `lang/fr.json`, `App\Support\GrimbaLanguageDetector`
