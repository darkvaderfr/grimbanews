# GrimbaNews — Swahili (SW) Locale Catalog Plan

**Status:** design v0 (no `lang/sw.json`; SW detector path not shipped)
**Owner:** Lucy Leai (CEO) on editorial relevance + Liam Smith (PM) on string spec + Nina Patel (Lead Frontend) on routing
**Walks:** Mythos S1139 (SW site UI catalog) deferred → partial
**Gating dependency:** SW-native editorial reviewer + East-African source roster expansion.

## Why this exists

S1139 was honest-deferred as "Swahili would be high-value for Afrique; surrogate is FR-only Le Monde Afrique/UNHCR feeds." That deferral is correct — but the **catalog spec + ops checklist** are operator-side and decidable now. Of all the non-FR/EN locales, SW has the strongest editorial fit because GrimbaNews already covers East Africa via Le Monde Afrique + UNHCR feeds; a SW surface would surface that coverage to native speakers.

## Catalog scope

Mirror of ES Tier 1-4 (~1,200 keys). SW specifics:

- **Latin script with Swahili-specific orthography** — no special character requirements beyond standard Latin.
- **Editorial categories**: Siasa, Uchumi, Jamii, Kimataifa, Afrika, Teknolojia, Afya, Sayansi, Utamaduni, Michezo, Mazingira, Sheria, Elimu, Uhamiaji.
- **Audience**: Kenya, Tanzania, Uganda, Rwanda, DRC eastern provinces, Mozambique north, global EA diaspora.
- **Register**: standard Swahili (Kiswahili sanifu) baseline.

## SW editorial relevance

Highest editorial fit of the non-Romance non-FR/EN locales:
- Aligns with GrimbaNews African coverage mission
- Le Monde Afrique + UNHCR + Africa Renewal feeds already cover topics relevant to SW readers
- Pan-African newsroom angle — Iboga editorial vision aligns

## Per-locale ops

Same as ES, plus East-Africa-specific:
- Press list: Daily Nation (KE), Citizen (TZ), Monitor (UG), New Vision (UG), East African
- Mobile-first audience — performance budget should be tighter than other locales
- Per-country sub-routing decision deferred (Kenya vs Tanzania URL prefix split)

## Acceptance gates

Same as ES.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1139 row)
- Sister docs: `docs/GRIMBANEWS_ES_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md`, `docs/GRIMBANEWS_DOM_TOM_SOURCE_ROSTER.md`
- Existing infrastructure: `lang/fr.json`, `App\Support\GrimbaLanguageDetector`
