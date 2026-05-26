# GrimbaNews — Italian (IT) Locale Catalog Plan

**Status:** design v0 (no `lang/it.json` catalog shipped; detector covers IT)
**Owner:** Liam Smith (PM) + Nina Patel (Lead Frontend) + Michael O'Connor (Technical Writer)
**Walks:** Mythos S1131 (IT site UI catalog) deferred → partial
**Gating dependency:** IT-native editorial reviewer.

## Why this exists

S1131 honest-deferred as "lang/it.json does not exist; detector covers IT." Catalog spec is operator-side.

## Catalog scope

Mirror of ES Tier 1-4 (~1,200 keys). IT specifics:

- Romance-language family with FR/ES — sentence length similar to FR.
- Editorial categories: Politica, Economia, Società, Internazionale, Africa, Tecnologia, Salute, Scienze, Cultura, Sport, Ambiente, Giustizia, Istruzione, Migrazioni.
- Italian press freedom register: maintains formal register baseline (Lei, not Tu).

## Per-locale ops

Mirror of `docs/GRIMBANEWS_ES_LAUNCH_READINESS_CHECKLIST.md`.

## IT editorial relevance

Italy hosts large Maghreb/Africa diaspora — strong fit for GrimbaNews FR-Africa source coverage when surfaced in IT.

## Acceptance gates

Same as ES.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1131 row)
- Sister docs: `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`
- Existing infrastructure: `lang/fr.json`, `App\Support\GrimbaLanguageDetector`
