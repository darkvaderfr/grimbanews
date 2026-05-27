# GrimbaNews — API v2 Source Endpoint Surrogate Plan

**Sprint ID:** S1242
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1241-s1250 — Source endpoint`
**Walk wave:** CCCC

## Gating dependency

A `/api/v2/sources` JSON endpoint needs:

- The same v2 contract layer as S1241
- A public-projection mapper for `news_sources` (factuality, bias, ownership_type)
- Per-source license + attribution headers respected on every response (gates on S398)
- Per-source rate limiter (gates on S1183)
- Billing meter (gates on S1254)

The data layer is ready; the contract + meter + license discipline are not.

## Surrogate-now infra

- **`app/Models/NewsSource`** — fillable + bias + factuality + ownership_type columns live
- **`app/Support/GrimbaSourceBreakdown`** — already aggregates source mix for cluster pages; the projection method is reusable
- **`/sources` HTML page** — public roster with bias / factuality badges (server-rendered, scrapable as poor-man's API)
- **`news_sources.license_notes`** — operator slot per S1030 captures attribution string per source
- **`/sources.json`-class precedent** — `/feed.json` exists; analogous `/sources.json` is one route registration away

## Honest framing

This is the *cleanest* B2B endpoint to ship next — the source roster is small (~120 rows), bounded, and would unlock partner trust-scoring use cases. Gates only on the v2 wrapper + billing layer.

## Owners

- **Product:** Liam Smith — projection field whitelist
- **Backend:** Rajesh Kumar — `SourcesController@index` + presenter
- **Editorial:** TBD source-roster ops — license attribution review
- **Platform:** Hannah Kim — per-source rate ladder
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1242 row)
- Source roster: `app/Support/GrimbaSourceBreakdown.php`
- Source legal review: S398 (deferred)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
