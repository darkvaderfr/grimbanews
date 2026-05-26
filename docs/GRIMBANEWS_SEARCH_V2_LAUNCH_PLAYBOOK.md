# GrimbaNews — Search v2 Launch Playbook

**Status:** playbook v0 (no vector store; lexical search ships today as v1)
**Owner:** Steve Jobs (CPO) on UX + Jacob Lee (DevOps) on infra + Lucy Leai (Strategy) on rollout
**Walks:** Mythos S1340 (search v2 launch) deferred → partial
**Gating dependency:** Vector store provisioning (S1701 per `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md`) + embedding-generation pipeline (S1333) + semantic-search query handler (S1335) + per-cluster embeddings (S1334). Playbook itself is operator-side.

## Why this exists

S1340 was honest-deferred as gating on S1332-S1339 — all of which are vector-store dependencies. The launch playbook is operator-side scope: how do we ship semantic search without burning reader trust on a v2 that misbehaves? This document sequences the launch so the moment vector infra lands, the rollout is a straight task list.

## Today's v1 surface

- `/search` page + `views/search.blade.php` ship lexical search.
- Facets: `editorial_category`, `editorial_region`, source name, date range. Per `SearchFacetsTest` (S279 coverage).
- Snippet: `posts.description` truncate (per S1339 partial).
- Search ranking: SQL `MATCH AGAINST` (FULLTEXT index on `posts.name + posts.description`).

## v2 scope

| v1 | v2 |
|---|---|
| Lexical FULLTEXT only | Lexical FULLTEXT + semantic vector search (parallel + merged) |
| Snippet = description truncate | NobuAI-enriched snippet (S1339 ship) |
| No spell-correction | Spell correction via vector-neighbor or BK-tree (S1337 ship) |
| No autocomplete | Search typeahead from popular queries (S1338 ship) |
| No query expansion | NobuAI query expansion (synonyms, related terms) (S1336 ship) |
| Static facets only | Dynamic facet ordering by relevance |

## Launch phases

### Phase 0 — Prep (operator + engineering, no v2 surface live)

1. **Vector infra picked + provisioned** (S1701).
2. **Embedding pipeline shipped** (S1703 + S1704 daily backfill + S1705 incremental).
3. **Semantic query handler** lands at `app/Services/GrimbaSemanticSearch.php` (new).
4. **Ranking blend** designed: lexical 40% + semantic 60% (tunable). A/B harness gates on S1721.
5. **Cost dashboard** wired (gates on `docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md`).
6. **Per-shape tests** — semantic-similarity fixture posts; expected top-3 results frozen as regression baseline.

### Phase 1 — Internal-only beta

1. **Feature flag** `search_v2_enabled` on `/search` route, gated to admin users via `Auth::user()?->is_admin`.
2. **Operator side-by-side** — admin testing both v1 + v2 rankings on same queries.
3. **2-week qual-review** — Lucy + Steve compare result sets across 20 representative queries.
4. **Tuning pass** — blend weights, snippet length, facet ordering.

### Phase 2 — 10% canary

1. **Cookie-pinned cohort** — 10% of /search visitors get v2 (cookie `grimba_search_variant=v2`).
2. **Per-variant analytics** — click-through rate, "no results" rate, refinement rate.
3. **2-week observe window.**
4. **Rollback gate** — if v2 CTR < v1 CTR − 5%, rollback to 0%. If v2 "no results" rate > v1 + 10%, rollback.

### Phase 3 — 50% canary

1. **Scale cohort to 50%** if Phase 2 passes.
2. **2-week observe window.**
3. **Same rollback gate.**

### Phase 4 — Full launch

1. **100% on v2.**
2. **Feature-flag retained** for emergency rollback (90-day window).
3. **Public announcement** — vault-digest mail entry + `/blog` post (per S2103+ blog deferred — operator-side until shipped).
4. **v1 code path removed** after 90 days of stable v2.

## UX additions in v2

- **Search-result snippet** — NobuAI-enriched (S1339 ship) summarizes how the article matches the query.
- **Query suggestions** — "Did you mean…?" + "Related queries…".
- **Autocomplete dropdown** below search input (S1338 ship).
- **Faceted refinement** — dynamic facet ordering by per-query relevance.

## Privacy posture

- Search queries are **not** persisted server-side today (lexical query lives in URL, no logging beyond standard nginx).
- v2 retains same posture by default. **Opt-in only** for per-user search history (deferred to `docs/GRIMBANEWS_GDPR_ROPA.md` future revision).
- Embeddings are over `posts` (already public content) — no reader-side PII enters the vector store.

## Cost posture (Ray review required)

| Cost line | v1 | v2 |
|---|---|---|
| Search-query latency | ~50ms | ~200-400ms (acceptable per UX) |
| Vector-store cost | $0 | gates on `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md` per-provider pricing |
| Embedding API cost | $0 | per-post one-time + incremental on new posts |
| NobuAI snippet enrichment | $0 | per-query NobuAI call (cached 5min) |

Ray review the blended unit-cost per search query at Phase 2 cutover.

## Rollback plan

- **Feature flag flip** — `search_v2_enabled = false` returns route to v1.
- **Vector store retention** — keep for 90 days post-rollback in case of revert.
- **Embedding pipeline pause** — `php artisan grimba:embeddings:pause`.

## Observability

- **Per-variant click-through dashboard** at `/admin/grimba/search-analytics` (new).
- **Per-variant "no results" rate** — flag if > 10%.
- **Per-variant time-to-first-result** — flag if p95 > 800ms.
- Pages to ops-on-call per `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md` P2 tier.

## Engineering effort estimate

- Vector infra + embedding pipeline (S1701-S1705): ~8 sprints.
- Semantic-query handler (S1335): ~3 sprints.
- NobuAI snippet enrichment (S1339): ~2 sprints.
- Autocomplete + query suggest (S1336-S1338): ~4 sprints.
- A/B + observability: ~2 sprints.
- **Full ship to Phase 4: ~20 sprints from Phase 0 start.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1340; gates on S1331-S1339)
- Sister docs: `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md`, `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`, `docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md`, `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md`
- Existing search surface: `platform/themes/echo/views/search.blade.php`
- Search facets coverage: `tests/Feature/SearchFacetsTest.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
