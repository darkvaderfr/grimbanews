# GrimbaNews — Public API v2 Design

**Status:** plan v0 (no `/api/v2` routes; RSS + per-stream feeds + `/health` cover read-only programmatic needs today)
**Owner:** Rajesh Kumar (Backend) on API design + Larry Ellison on rate-limit schema + Sara Chen on auth posture + Michael O'Connor on developer docs
**Walks:** Mythos S1181 (Public API v2 design) deferred → partial
**Gating dependency:** Sanctum/Passport install + `api_keys` table schema + partner program (S1185) clarity

## Why this exists

S1181 unblocks the entire B2B API band (S1181-S1190 + S1231-S1260). Today's RSS feeds at `/feed.xml`, `/feed.breaking.xml`, `/feed.latest.xml`, per-category feeds, and per-MG-cluster feed (per Wave HHHHHHHHHHH) handle read-only-fast-poll partner needs but cap at RSS expressivity (paginate, filter, search not natively supported).

## Today's surrogate

- **RSS / Atom feeds** — `/feed.xml`, `/feed.breaking.xml`, `/feed.latest.xml`, `/feed/categorie/{slug}.xml`, `/feed/middle-ground.xml`.
- **`/health` JSON** — health-check JSON.
- **No per-key access** — anonymous access only.

## Surface design — endpoint inventory

| Endpoint | Method | Description | Auth | Quotas |
|---|---|---|---|---|
| `/api/v2/posts` | GET | List posts (filter: locale, category, source, bias, date_from, date_to) | key | per-tier |
| `/api/v2/posts/{id}` | GET | Single post + NobuAI summary | key | per-tier |
| `/api/v2/clusters` | GET | List clusters | key | per-tier |
| `/api/v2/clusters/{id}` | GET | Cluster + bias breakdown + member articles | key | per-tier |
| `/api/v2/sources` | GET | News source registry | key | per-tier |
| `/api/v2/sources/{slug}` | GET | Single source profile + metadata | key | per-tier |
| `/api/v2/search` | GET | Search across posts (q, locale, filters) | key | per-tier |
| `/api/v2/trends` | GET | Top story clusters in last N hours | key | per-tier |
| `/api/v2/bias-distribution/{cluster_id}` | GET | Bias distribution for cluster | key | per-tier |
| `/api/v2/health` | GET | API health (no auth) | none | none |
| `/api/v2/changelog` | GET | API changelog (no auth) | none | none |

## Versioning policy

- `v2` is the first stable JSON contract (no v1 deprecation; v1 = RSS only by convention).
- Breaking changes → `v3`; v2 supported for 12 months past v3 launch.
- Non-breaking additions: new fields appear in v2 without bump.

## Response envelope

```json
{
  "data": [...],
  "meta": {
    "page": 1,
    "per_page": 25,
    "total": 1234,
    "license": "CC-BY-NC-4.0",
    "freshness": "2026-05-26T08:00:00Z"
  },
  "links": {
    "self": "https://grimbanews.com/api/v2/posts?page=1",
    "next": "https://grimbanews.com/api/v2/posts?page=2"
  }
}
```

## Standardized fields per resource

### Post

`id, slug, title, lede, source_id, source_name, source_bias, source_factuality, published_at, language, region, categories[], canonical_url, summary_nobuai`

### Cluster

`id, slug, title, member_count, bias_distribution {left,centre,right,middle_ground}, factuality_avg, published_at, locale, members [post_ids...]`

## Authentication

- Bearer token: `Authorization: Bearer gn_{tier}_{32-char-secret}`.
- Token in URL prohibited.
- Sanctum-backed; per-tier rate limiting via Laravel `RateLimiter`.

## Per-tier quotas

| Tier | Rate (req/hour) | Daily cap | Concurrent |
|---|---|---|---|
| Public (no key) | 60 | 1000 | 2 |
| Academic | 5000 | 50000 | 5 |
| Commercial Starter | 10000 | 100000 | 10 |
| Commercial Pro | per-contract | per-contract | per-contract |

## Why v2 not v1

- Convention: RSS reservation as "v1" (existing).
- JSON contract as v2 makes versioning straightforward — readers/partners switching from RSS to JSON understand "v2 is the JSON one".

## Engineering effort

- Sanctum install + base config: 1 sprint.
- Key model + admin: 2 sprints.
- Each endpoint (10 above): ~1 sprint each = 10 sprints.
- Rate-limit middleware + tier resolver: 1 sprint.
- OpenAPI spec generation: 1 sprint.
- Partner sandbox env: 1 sprint.
- Tests + fixtures: 2 sprints.
- **Total: ~18 sprints once auth decision lands.**

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1181-S1190 band)
- Sister docs: `docs/GRIMBANEWS_API_V2_OAUTH_CLIENT_PLAN.md`, `docs/GRIMBANEWS_API_V2_KEY_REVOCATION_PLAN.md`, `docs/GRIMBANEWS_API_V2_OPENAPI_SPEC_SCOPE.md`, `docs/GRIMBANEWS_API_LAUNCH_PLAYBOOK.md`, `docs/GRIMBANEWS_PARTNER_DOCS_PLAN.md`, `docs/GRIMBANEWS_API_ACADEMIC_TIER_PLAN.md`
- Existing surrogates: `/feed.xml`, `/feed/middle-ground.xml`, `/health`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
