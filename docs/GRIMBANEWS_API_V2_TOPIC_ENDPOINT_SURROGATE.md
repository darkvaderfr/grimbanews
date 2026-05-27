# GrimbaNews — API v2 Topic Endpoint Surrogate Plan

**Sprint ID:** S1245
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1241-s1250 — Topic endpoint`
**Walk wave:** CCCC

## Gating dependency

A `/api/v2/topics` endpoint needs:

- v2 contract wrapper (S1239)
- Topic projection that includes slug, label per locale, recent-cluster count, source diversity, freshness
- Topic-recency cache (so a `?fresh=24h` query doesn't trigger a full scan)
- Billing meter (gates on S1254)

## Surrogate-now infra

- **`app/Support/GrimbaEditorialCategories::all()`** — returns the operator-curated topic taxonomy
- **`/category/{slug}` HTML** — server-renders the per-topic story river that a B2B client would consume
- **`grimba_editorial_categories` setting** — admin-editable taxonomy with per-locale labels
- **Sitemap** — `/sitemap-grimba.xml` already shipping per-topic URL groupings

## Honest framing

Smallest of the five v2 endpoints (bounded list, ~20 topics today). Trivial once S1241/S1243 ship — same presenter pattern.

## Owners

- **Product:** Liam Smith — projection field set
- **Backend:** Rajesh Kumar — `TopicsController@index` + presenter
- **i18n:** Nina Patel — per-locale label contract
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1245 row)
- Editorial categories: `app/Support/GrimbaEditorialCategories.php`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
