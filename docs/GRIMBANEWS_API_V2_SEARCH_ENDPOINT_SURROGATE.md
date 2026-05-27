# GrimbaNews — API v2 Search Endpoint Surrogate Plan

**Sprint ID:** S1244
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1241-s1250 — Search endpoint`
**Walk wave:** CCCC

## Gating dependency

A `/api/v2/search` endpoint needs:

- v2 contract wrapper (S1239)
- Query-string parser shared with the saved-search engine
- Stable facet schema (category, language, source, date-range, bias)
- Result-count limit + cursor pagination
- Billing meter (gates on S1254)

## Surrogate-now infra

- **`app/Support/GrimbaSavedSearches::matchingPosts()`** — already produces a paginated `Builder` matching the public query DSL
- **`tests/Feature/SearchFacetsTest`** — locks the facet contract today (S278 surrogate)
- **`/search?q=&category=&lang=&from=&to=`** — public HTML search page hits the same backend; JSON response is one `Accept: application/json` branch away
- **`grimba:saved-search-digests`** — weekly Monday 04:55 job is proof the query engine runs unattended at scale

## Honest framing

The search query layer is the most production-tested helper in the codebase (powers RSS digests, saved-search alerts, and the public `/search` page). The JSON contract is the missing piece, not the engine.

## Owners

- **Product:** Liam Smith — facet contract + cursor decision
- **Backend:** Rajesh Kumar — `SearchController@index` json branch + facet presenter
- **Data:** David Chen — facet count caching policy
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1244 row)
- Saved searches: `app/Support/GrimbaSavedSearches.php`
- Search insight (S279): deferred (NobuAI enrichment)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
