# GrimbaNews — Semantic Search Query Embedding Design

**Status:** plan v0 (no per-query embedding generated; only lexical lookup runs)
**Owner:** Rajesh Kumar (Backend) on serving + David Chen on cache strategy + Hannah Kim on latency monitoring
**Walks:** Mythos S1464 (Semantic search query embedding) deferred → partial
**Gating dependency:** Embedding model picked (S1462) + store provisioned (S1076) + index built (S1463).

## Why this exists

S1464 is the query-side counterpart of S1463. Every search query must be embedded with the same model used for posts before the ANN lookup. This is the per-request hot path — latency matters.

## Today's surrogate

- `searchHandler` runs MATCH() in-process; no external API call per query.

## Query path (target)

```
GET /search?q=climate+change
  ├─► [normalize: lowercase, trim, strip diacritics]
  ├─► [cache lookup: redis key = hash(normalized_query + model_version + locale)]
  │     └─ hit → reuse cached embedding
  │     └─ miss ↓
  ├─► [embed: NobuAI driver → vector]
  ├─► [cache set: TTL = 24h]
  └─► [vector store ANN top-50 → hybrid merge]
```

## Cache strategy

- Per-query embedding cache TTL 24h (queries repeat heavily on news cycles).
- Cache key includes `model_version` so model swaps don't reuse stale embeddings.
- Per-locale cache namespace (avoid cross-locale bleed for queries like "fr"=French vs FR-country abbrev).

## Latency budget

- Cache hit: <5ms
- Cache miss (embedding call): <120ms target (NobuAI driver SLA)
- Total: <150ms for embedding alone

## Fallback when embedding fails

- Timeout > 200ms → drop to lexical-only ranking (FTS5 BM25 alone)
- Surface no degradation to reader — UI is identical
- Log to `grimba_automation_runs` for ops review

## Rate-limit guard

- Per-IP query rate-limit already enforced by Laravel `throttle:60,1`
- Per-API-key (S1241+) bypass with paid-tier check (deferred S1256)

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1464)
- Sister docs: `docs/GRIMBANEWS_SEMANTIC_SEARCH_DESIGN_DOC.md`, `docs/GRIMBANEWS_SEMANTIC_SEARCH_HYBRID_MERGE_DESIGN.md`
- Existing infra: Laravel cache (redis / database), `GrimbaSearchController`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
