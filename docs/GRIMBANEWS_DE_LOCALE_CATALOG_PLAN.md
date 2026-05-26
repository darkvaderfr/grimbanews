# GrimbaNews — German (DE) Locale Catalog Plan

**Status:** design v0 (no `lang/de.json` catalog shipped)
**Owner:** Liam Smith (PM) on string spec + Nina Patel (Lead Frontend) on routing wiring + Michael O'Connor (Technical Writer) on draft string review
**Walks:** Mythos S1121 (DE site UI catalog) deferred → partial
**Gating dependency:** DE-native editorial reviewer (none on current Iboga roster cleared for editorial sign-off). Catalog + wiring operator-side.

## Why this exists

S1121 was honest-deferred as "lang/de.json does not exist." Detector covers DE per `GrimbaLanguageDetectorTest`. Wiring follows ES pattern.

## Catalog scope

Mirror of ES Tier 1-4 (~1,200 keys). DE specifics:

- **German compound words** balloon button widths — chrome audit needed (e.g. "Speichern" 8 chars vs "Lesezeichen hinzufügen" 23 chars; button widths use `min-content` per design system but containers cap may overflow).
- **Capitalization rules**: every noun capitalized in DE — affects sentence-case button copy ("Quelle ansehen" not "quelle ansehen").
- **Du vs Sie**: catalog ships **formal Sie** baseline (matches Le Monde / Le Figaro / NYT register; informal Du reserved for marketing surfaces if Vader approves).
- **German privacy expectation**: DE audience is the highest-bar GDPR-aware market — consent banner copy needs DE-native review more critical than other locales.

## Per-locale ops checklist

Mirror of `docs/GRIMBANEWS_ES_LAUNCH_READINESS_CHECKLIST.md`.

## DACH editorial relevance

German launch unlocks DACH coverage (Germany + Austria + Switzerland). Source roster currently lacks German-language sources — separate expansion band.

## Acceptance gates

Same shape as ES.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1121 row)
- Sister docs: `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_DE_LANDING_PAGE_SCOPE.md`, `docs/GRIMBANEWS_DE_EDITORIAL_PAGES_SCOPE.md`, `docs/GRIMBANEWS_DE_HREFLANG_WIRING.md`, `docs/GRIMBANEWS_DE_LAUNCH_READINESS_CHECKLIST.md`
- Existing infrastructure: `lang/fr.json`, `App\Support\GrimbaLanguageDetector`, `App\Http\Middleware\GrimbaLocaleEnforce`
