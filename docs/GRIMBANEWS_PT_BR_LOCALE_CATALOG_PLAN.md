# GrimbaNews — Brazilian Portuguese (PT-BR) Locale Catalog Plan

**Status:** design v0 (no `lang/pt_BR.json` catalog shipped; detector covers PT-BR)
**Owner:** Liam Smith (PM) on string spec + Nina Patel (Lead Frontend) on routing wiring + Michael O'Connor (Technical Writer) on draft string review
**Walks:** Mythos S1111 (PT-BR site UI catalog) deferred → partial
**Gating dependency:** PT-BR-native editorial reviewer (none on current Iboga roster cleared for editorial sign-off). Catalog + UX wiring itself is operator-side.

## Why this exists

S1111 was honest-deferred as "lang/pt_BR.json does not exist; detector covers PT-BR." The detector is shipped (per `GrimbaLanguageDetectorTest`). Catalog wiring follows the ES pattern (see `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`). This doc captures PT-BR-specific scope.

## Current state

- Detector returns `pt_BR` when browser advertises `pt-BR` (per detector test).
- Locale enforcement: `GrimbaLocaleEnforce::PRIMARY_LOCALES = ['fr', 'en']` today; PT-BR is the planned fourth locale (after ES).
- Translation infrastructure same as ES — `post_translations` table is locale-agnostic.

## Catalog scope

Mirror of ES Tier 1-4 (~1,200 keys total). PT-BR specifics:

- **Brazilian Portuguese vs European Portuguese**: catalog ships as `pt_BR` (Brazilian) baseline. EU Portuguese (pt-PT) deferred — Brazilian audience is the larger immediate target.
- **Lusofone editorial categories**: same 14 categories as ES, with PT-BR labels: Política, Economia, Sociedade, Internacional, África (high-relevance per GrimbaNews FR-Africa editorial focus), Tecnologia, Saúde, Ciências, Cultura, Esporte, Meio Ambiente, Justiça, Educação, Migrações.
- **Sentence-length expansion**: PT-BR runs ~15% longer than FR — chrome button widths need audit.
- **PWA install copy**: PT-BR uses different idiom for "Add to Home Screen" — manifest copy needs PT-BR-specific override.

## Per-locale ops checklist

Mirror of `docs/GRIMBANEWS_ES_LAUNCH_READINESS_CHECKLIST.md` — same T-14 / T-7 / T-1 / T-0 / T+7 cadence.

## Africa-Lusophone editorial relevance

GrimbaNews has strong FR-Africa source coverage. PT-BR launch unlocks Angola + Mozambique + Cabo Verde + Guiné-Bissau + São Tomé editorial framing. Source-roster expansion (currently FR-dominant for Africa) gates separately, but the **catalog enables surfacing existing PT-translated content** to the Lusophone-Africa diaspora and Brazilian readers.

## Acceptance gates

Same shape as ES (per `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md` acceptance gates section).

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1111 row)
- Sister docs: `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_PT_BR_LANDING_PAGE_SCOPE.md`, `docs/GRIMBANEWS_PT_BR_EDITORIAL_PAGES_SCOPE.md`, `docs/GRIMBANEWS_PT_BR_HREFLANG_WIRING.md`, `docs/GRIMBANEWS_PT_BR_LAUNCH_READINESS_CHECKLIST.md`
- Existing infrastructure: `lang/fr.json`, `App\Support\GrimbaLanguageDetector`, `App\Http\Middleware\GrimbaLocaleEnforce`
