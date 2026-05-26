# GrimbaNews — Brazilian Portuguese (PT-BR) Editorial Pages Scope

**Status:** scope v0 (no PT-BR editorial pages)
**Owner:** Lucy Leai (CEO) on editorial taxonomy + Liam Smith (PM) on per-page spec
**Walks:** Mythos S1113 (PT-BR editorial pages) deferred → partial
**Gating dependency:** PT-BR catalog (S1111) + PT-BR editorial reviewer for category descriptions.

## Why this exists

S1113 mirrors S1103 (ES). **Per-category mapping** is operator-side.

## Editorial category mapping (FR → PT-BR)

| FR slug | FR label | PT-BR slug | PT-BR label |
|---|---|---|---|
| politique | Politique | politica | Política |
| economie | Économie | economia | Economia |
| societe | Société | sociedade | Sociedade |
| international | International | internacional | Internacional |
| afrique | Afrique | africa | África |
| technologie | Technologie | tecnologia | Tecnologia |
| sante | Santé | saude | Saúde |
| sciences | Sciences | ciencias | Ciências |
| culture | Culture | cultura | Cultura |
| sport | Sport | esporte | Esporte |
| environnement | Environnement | meio-ambiente | Meio Ambiente |
| justice | Justice | justica | Justiça |
| education | Éducation | educacao | Educação |
| migrations | Migrations | migracoes | Migrações |

## Implementation deltas

Mirror of ES editorial pages scope. Same `GrimbaEditorialCategories` + `GrimbaCategoryPostsQuery` extension pattern. Translation fallback banner: "Este artigo ainda não foi traduzido para o português."

## Acceptance gates

Same as ES; substitute `pt-BR` for `es`.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1113 row)
- Sister docs: `docs/GRIMBANEWS_PT_BR_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_PT_BR_LANDING_PAGE_SCOPE.md`, `docs/GRIMBANEWS_ES_EDITORIAL_PAGES_SCOPE.md`
- Existing infrastructure: `App\Support\GrimbaEditorialCategories`, `App\Support\GrimbaCategoryPostsQuery`, `App\Support\GrimbaTranslationPresenter`
