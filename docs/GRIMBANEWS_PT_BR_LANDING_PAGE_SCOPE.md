# GrimbaNews — Brazilian Portuguese (PT-BR) Landing Page Scope

**Status:** scope v0 (no `/pt-BR/` landing; depends on PT-BR catalog)
**Owner:** Steve Jobs (CPO) on hero + Liam Smith (PM) on copy spec + Alex Morgan (UI/UX) on visual sign-off
**Walks:** Mythos S1112 (PT-BR landing) deferred → partial
**Gating dependency:** PT-BR catalog (S1111). PT-BR-native reviewer required for above-the-fold copy.

## Why this exists

S1112 mirrors S1102 (ES). Honest-deferred on catalog dependency. **Scope spec** is operator-side.

## Hero layout

Mirrors FR canonical landing; copy swapped to PT-BR via `__()` calls:

- "Leia as fontes" (Read the sources)
- "Múltiplas perspectivas, uma só história" (Multiple perspectives, one story)
- "Veja como classificamos a parcialidade" (See how we classify bias)

## PT-BR sub-routes

- `/pt-BR` landing
- `/pt-BR/categoria/{slug}` editorial categories
- `/pt-BR/dossie/{id}` (note: `dossie` reads more natural than `dossier` in PT-BR)
- `/pt-BR/blog/{slug}`

## Acceptance gates

Same as ES landing scope; substitute `es` for `pt-BR` throughout.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1112 row)
- Sister docs: `docs/GRIMBANEWS_PT_BR_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_PT_BR_EDITORIAL_PAGES_SCOPE.md`
- Existing infrastructure: `resources/views/marketing/landing.blade.php`
