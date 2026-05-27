# GrimbaNews — API v2 Article Endpoint Surrogate Plan

**Sprint ID:** S1241
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1241-s1250 — Article endpoint`
**Walk wave:** CCCC

## Gating dependency

A `/api/v2/articles` endpoint needs:

- Stable OpenAPI contract (gates on S1239 v2 spec)
- API key issuance + scoping (gates on S1231-S1232)
- Rate limit policy (gates on S1183 public-API policies)
- Billing meter (gates on S1254)
- Field-selection contract (already specced in `GRIMBANEWS_API_FIELD_SELECTION_CONTRACT.md`)

None of these ship in runtime today; only the data layer is ready.

## Surrogate-now infra

- **`app/Models/Post.php`** — already ships fillable + cast + scope for the public projection
- **`app/Support/GrimbaTranslationPresenter`** — locale-aware presenter that any future controller can reuse
- **`/feed.xml` + `/feed.json`** — public structured exports today (RSS + JSON Feed); a B2B client *can* poll them
- **`/sitemap-grimba.xml`** — full per-locale article index for batch discovery
- **Per-category routes** — `/category/{slug}` server-renders the same projection in HTML

## Honest framing

The Post + presenter layer is one trait + one controller away from `/api/v2/articles`. It sits deferred only because no SDK / billing meter / key issuance exists yet — shipping the endpoint without those would create a unmetered firehose.

## Owners

- **Product:** Liam Smith — endpoint scope + field whitelist
- **Backend:** Rajesh Kumar — controller + presenter wiring
- **Platform:** Hannah Kim — rate limiter middleware
- **Security:** Maya Patel — auth header policy
- **Docs:** Michael O'Connor — OpenAPI spec stub
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1241 row)
- Field selection contract: `docs/GRIMBANEWS_API_FIELD_SELECTION_CONTRACT.md`
- Filter/sort contract: `docs/GRIMBANEWS_API_FILTER_SORT_CONTRACT.md`
- OpenAPI v2 scope: `docs/GRIMBANEWS_API_V2_OPENAPI_SPEC_SCOPE.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
