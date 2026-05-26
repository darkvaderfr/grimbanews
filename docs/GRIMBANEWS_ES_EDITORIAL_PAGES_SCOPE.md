# GrimbaNews — Spanish (ES) Editorial Pages Scope

**Status:** scope v0 (no ES editorial pages; editorial categories are FR-canonical with EN mirror only)
**Owner:** Lucy Leai (CEO) on editorial taxonomy + Liam Smith (PM) on per-page spec
**Walks:** Mythos S1103 (ES editorial pages) deferred → partial
**Gating dependency:** ES catalog (S1101) + per-category ES editorial reviewer for category descriptions. Source roster (currently FR-dominant) does not yet have an ES source band — covered separately under per-region source roster bands.

## Why this exists

S1103 was honest-deferred as "depends on S1101 catalog; editorial categories are FR-canonical." The catalog dep is real. But the **per-category spec** — which editorial categories ship in ES on day 1, which wait — is operator-side and decidable now.

## Editorial category mapping (FR → ES)

`App\Support\GrimbaEditorialCategories::all()` returns 14 categories today (FR-canonical labels). ES launch mapping:

| FR slug | FR label | ES slug | ES label |
|---|---|---|---|
| politique | Politique | politica | Política |
| economie | Économie | economia | Economía |
| societe | Société | sociedad | Sociedad |
| international | International | internacional | Internacional |
| afrique | Afrique | africa | África |
| technologie | Technologie | tecnologia | Tecnología |
| sante | Santé | salud | Salud |
| sciences | Sciences | ciencias | Ciencias |
| culture | Culture | cultura | Cultura |
| sport | Sport | deportes | Deportes |
| environnement | Environnement | medio-ambiente | Medio Ambiente |
| justice | Justice | justicia | Justicia |
| education | Éducation | educacion | Educación |
| migrations | Migrations | migraciones | Migraciones |

## Per-category page contents

- Page header: category label + brief ES description (1 sentence per category; ~14 short translations)
- Article rail: existing post stream filtered by category + locale=`es` (currently filters by FR + EN translations; needs filter widening)
- Source rail: per-category source breakdown
- Featured dossier: top cluster within category, translated to ES

## Implementation deltas

1. Add `es` to `App\Support\GrimbaEditorialCategories` per-locale label lookup.
2. `GrimbaCategoryPostsQuery` already accepts `locale` parameter; flip ES on per-route resolution.
3. Translation fallback: if ES translation missing for a post, fallback to FR original with `<span class="grimba-translation-fallback">` indicator + ES banner ("Este artículo aún no se ha traducido al español").

## Acceptance gates

1. `/es/categoria/politica` returns 200 with ES chrome + article list.
2. Each of 14 ES category slugs resolves.
3. Per-category JSON-LD emits `inLanguage=es`.
4. Empty-category state: ES copy ("Aún no hay artículos en esta categoría").

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1103 row)
- Sister docs: `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_ES_LANDING_PAGE_SCOPE.md`
- Existing infrastructure: `App\Support\GrimbaEditorialCategories`, `App\Support\GrimbaCategoryPostsQuery`, `App\Support\GrimbaTranslationPresenter`
