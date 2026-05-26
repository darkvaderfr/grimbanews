# GrimbaNews — German (DE) Landing Page Scope

**Status:** scope v0 (no `/de/` landing)
**Owner:** Steve Jobs (CPO) on hero + Liam Smith (PM) on copy spec
**Walks:** Mythos S1122 (DE landing) deferred → partial
**Gating dependency:** DE catalog (S1121) + DE-native reviewer for above-the-fold copy.

## Hero layout

- "Lesen Sie die Quellen" (Read the sources, formal Sie)
- "Mehrere Perspektiven, eine Geschichte"
- "Sehen Sie, wie wir Bias klassifizieren"

## DE sub-routes

- `/de` landing
- `/de/kategorie/{slug}` editorial categories
- `/de/dossier/{id}` (cognate; keep `dossier`)
- `/de/blog/{slug}`

## Width-audit notes

- "Lesezeichen hinzufügen" (Add to bookmarks): 23 chars — verify primary CTA button doesn't overflow `min-width: 200px` constraint.
- Footer "Datenschutzerklärung" (Privacy policy): 22 chars — vertical-stack footer rows tolerate.
- "Einstellungen für Cookies" (Cookie settings): 25 chars — consent banner needs DE-specific layout pass.

## Acceptance gates

Same as ES; substitute `de`.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1122 row)
- Sister docs: `docs/GRIMBANEWS_DE_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_ES_LANDING_PAGE_SCOPE.md`
- Existing infrastructure: `resources/views/marketing/landing.blade.php`
