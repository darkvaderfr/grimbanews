# GrimbaNews — German (DE) Editorial Pages Scope

**Status:** scope v0 (no DE editorial pages)
**Owner:** Lucy Leai (CEO) on taxonomy + Liam Smith (PM) on per-page spec
**Walks:** Mythos S1123 (DE editorial pages) deferred → partial
**Gating dependency:** DE catalog (S1121) + DE editorial reviewer for category descriptions.

## Editorial category mapping (FR → DE)

| FR slug | FR label | DE slug | DE label |
|---|---|---|---|
| politique | Politique | politik | Politik |
| economie | Économie | wirtschaft | Wirtschaft |
| societe | Société | gesellschaft | Gesellschaft |
| international | International | international | International |
| afrique | Afrique | afrika | Afrika |
| technologie | Technologie | technologie | Technologie |
| sante | Santé | gesundheit | Gesundheit |
| sciences | Sciences | wissenschaft | Wissenschaft |
| culture | Culture | kultur | Kultur |
| sport | Sport | sport | Sport |
| environnement | Environnement | umwelt | Umwelt |
| justice | Justice | justiz | Justiz |
| education | Éducation | bildung | Bildung |
| migrations | Migrations | migration | Migration |

## Implementation deltas

Mirror of ES editorial pages. Translation fallback: "Dieser Artikel wurde noch nicht ins Deutsche übersetzt."

## Acceptance gates

Same as ES; substitute `de`.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1123 row)
- Sister docs: `docs/GRIMBANEWS_DE_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_DE_LANDING_PAGE_SCOPE.md`
- Existing infrastructure: `App\Support\GrimbaEditorialCategories`
