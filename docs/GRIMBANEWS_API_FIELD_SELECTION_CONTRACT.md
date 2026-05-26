# GrimbaNews — API Field Selection Contract

**Status:** plan v0 (no sparse-fieldsets / GraphQL layer)
**Owner:** Rajesh Kumar (Backend) on parsing + Michael O'Connor on docs + Liam Smith (PM) on partner ask
**Walks:** Mythos S1247 (Field-selection contract) deferred → partial
**Gating dependency:** API v2 endpoints shipped + partner perf request (today: hypothetical)

## Why this exists

S1247 lets partners reduce payload size + parse time by requesting only fields they need. Big endpoints (Posts with NobuAI summary) can be 5x larger than minimal — costly at high volume.

## Today's surrogate

- **Full-payload responses** — every field always returned.
- **Pagination** trims volume but not per-record fatness.

## Approach — JSON:API sparse fieldsets

Per JSON:API spec section 7.7.

Query parameter: `fields[posts]=id,title,published_at`.

Server returns only listed fields + always-included primary key.

```
GET /api/v2/posts?fields[posts]=id,title,source_name&per_page=25
```

Response:
```json
{
  "data": [
    {"id": 123, "title": "...", "source_name": "Le Monde"},
    ...
  ]
}
```

## Default field sets (when `fields[]` not specified)

| Resource | Default fields returned |
|---|---|
| Post | id, slug, title, lede, source_id, source_name, published_at, categories[], summary_nobuai |
| Cluster | id, slug, title, member_count, bias_distribution, published_at |
| Source | id, slug, name, bias, factuality, region |

## Field group aliases

For convenience:

| Alias | Expands to |
|---|---|
| `minimal` | id only |
| `default` | (per above) |
| `full` | every available field |
| `compact` | id, title, published_at (for list views) |

`fields[posts]=compact` shorthand for the common list-view case.

## Per-tier field gating

Some fields restricted to higher tiers:
- `summary_nobuai` — academic + commercial only
- `bias_distribution_history` — commercial only
- `source_factuality_history` — enterprise only

Requesting a non-allowed field → 403 with error: `field_not_allowed_for_tier`.

## OpenAPI implications

- Each field documented with availability (always / by-tier).
- Generated SDKs (when shipped per S1240) honor field selection.

## Performance benefit (estimated)

- `compact` reduces typical posts response by ~70%.
- Per high-volume partner: ~30% reduction in monthly bytes-out → real cost savings.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1247)
- Sister docs: `docs/GRIMBANEWS_PUBLIC_API_V2_DESIGN.md`, `docs/GRIMBANEWS_API_FILTER_SORT_CONTRACT.md`, `docs/GRIMBANEWS_API_V2_OPENAPI_SPEC_SCOPE.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
