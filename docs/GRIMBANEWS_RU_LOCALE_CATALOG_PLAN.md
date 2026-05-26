# GrimbaNews — Russian (RU) Locale Catalog Plan

**Status:** design v0 (no `lang/ru.json`; RU detector path not shipped)
**Owner:** Liam Smith (PM) + Nina Patel (Lead Frontend) + Michael O'Connor (Technical Writer)
**Walks:** Mythos S1136 (RU site UI catalog) deferred → partial
**Gating dependency:** RU-native editorial reviewer; geopolitical content policy decision.

## Catalog scope

Mirror of ES Tier 1-4 (~1,200 keys). RU specifics:

- **Cyrillic script** — Noto Sans baseline supports.
- **Sentence length** ~20% longer than FR — chrome width audit needed.
- **Editorial categories**: Политика, Экономика, Общество, Международное, Африка, Технологии, Здоровье, Наука, Культура, Спорт, Окружающая среда, Юстиция, Образование, Миграция.

## Geopolitical considerations

RU launch raises editorial questions:
- Russian-speaking audience is split across Russian Federation, Belarus, Kazakhstan, Ukraine, Baltic diaspora, global emigrants.
- Coverage of Russia/Ukraine war must use neutral framing per bias-bar methodology; source roster includes both sides.
- Potential Roskomnadzor (Russian internet regulator) interest — operator should be prepared for ru-domain blocking. Site may primarily serve Russian-speaking diaspora outside Russian Federation.

Operator-side call required (Lucy + Vader).

## Acceptance gates

Same as ES + Cyrillic font check.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1136 row)
- Sister docs: `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_DE_LOCALE_CATALOG_PLAN.md`
- Existing infrastructure: `lang/fr.json`, `App\Support\GrimbaLanguageDetector`
