# GrimbaNews — API Filter & Sort Contract

**Status:** plan v0 (no advanced filter/sort layer; query params with implicit defaults today)
**Owner:** Rajesh Kumar (Backend) on parsing + Larry Ellison on per-filter index strategy + Michael O'Connor on docs
**Walks:** Mythos S1248 (Filter / sort contract) deferred → partial
**Gating dependency:** API v2 endpoints shipped + index audit for performant filter paths

## Why this exists

S1248 standardizes how partners pass complex queries. Without contract, every endpoint invents its own filter syntax — partner DX breaks.

## Today's surrogate

- **Per-endpoint ad-hoc query params** — `/api/v2/posts?category=tech` would work for single filter; complex AND/OR not native.

## Filter syntax — JSON:API style

Per JSON:API section 7.8.

```
GET /api/v2/posts?filter[category]=tech&filter[locale]=fr&filter[published_at][gte]=2026-05-01
```

## Operators

| Operator | Use | Example |
|---|---|---|
| (none) | equals | `filter[category]=tech` |
| `gte` | greater-than-or-equal | `filter[published_at][gte]=2026-05-01` |
| `lte` | less-than-or-equal | `filter[published_at][lte]=2026-05-26` |
| `in` | one of (comma-separated) | `filter[source_id][in]=42,87,123` |
| `not` | not equal | `filter[locale][not]=es` |
| `like` | string contains (case-insensitive) | `filter[title][like]=climate` |
| `has` | array contains | `filter[categories][has]=tech` |

## Combinator

- Default: AND across all filters.
- OR only via explicit endpoint feature (e.g., `/search` endpoint uses query DSL).

## Sort syntax

```
GET /api/v2/posts?sort=-published_at,title
```

- Leading `-` = descending.
- Comma-separated list = primary, secondary sort.
- Allowed sort fields per resource:

| Resource | Sortable fields |
|---|---|
| Post | published_at, source_id, factuality, bias_score |
| Cluster | published_at, member_count, factuality_avg |
| Source | name, factuality, bias |

## Pagination

- `?page=N&per_page=N`.
- Default page=1, per_page=25.
- Max per_page=100.
- Response carries `links.next`, `links.prev`, `meta.total`.

## Cursor pagination (high-volume)

For bulk-export endpoints:
- `?cursor=eyJpZCI6MTIzNDV9` (opaque base64 token).
- Avoids OFFSET problem at deep pagination.

## Index strategy (Larry Ellison)

| Filter field | Index |
|---|---|
| `posts.published_at` | already indexed |
| `posts.source_id` | already indexed |
| `posts.categories` (JSON) | generated column + index when partner volume justifies |
| `posts.title` (LIKE) | falls back to FTS5 search endpoint; pure LIKE rejected (perf cliff) |
| `clusters.published_at` | already indexed |

## Error handling

- Unknown filter field → 400 `unknown_filter`.
- Unknown operator → 400 `unknown_operator`.
- Bad value (non-date for date field) → 400 `invalid_value`.
- Too many filters (>20) → 400 `too_many_filters`.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1248)
- Sister docs: `docs/GRIMBANEWS_PUBLIC_API_V2_DESIGN.md`, `docs/GRIMBANEWS_API_FIELD_SELECTION_CONTRACT.md`, `docs/GRIMBANEWS_API_V2_OPENAPI_SPEC_SCOPE.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
