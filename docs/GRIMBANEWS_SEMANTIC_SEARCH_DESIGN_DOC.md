# GrimbaNews — Semantic Search Design Doc

**Status:** plan v0 (FTS5 is the substrate today; no semantic layer)
**Owner:** Elon Musk (CTO) on architecture + Rajesh Kumar (Backend) on integration + David Chen (Data Scientist) on retrieval quality
**Walks:** Mythos S1461 (Semantic search design doc) deferred → partial
**Gating dependency:** Embedding store provision (S1076) + model pick (S1462) + at least 6 months of post corpus to validate against.

## Why this exists

S1461 is the design doc for a semantic search layer that complements (not replaces) the FTS5 lexical search. Today `searchHandler` runs MATCH() on FTS5 — strong for keyword exact match, weak for paraphrase ("global warming" finding "climate change" articles).

## Today's surrogate

- **FTS5 virtual table over `posts.title + body`** — see `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md` for the substrate.
- BM25 ranking via `FTS5 rank`.

## Architecture (target)

```
query  ──► [lexical channel: FTS5 BM25 top-K]
       └─► [semantic channel: query embedding → vector store ANN top-K]
                    │
                    ▼
           [hybrid merge: Reciprocal Rank Fusion]
                    │
                    ▼
              ranked result set
```

## Component breakdown

| Component | Sprint | Status |
|---|---|---|
| Embedding store (pgvector / qdrant / pinecone) | S1076 | deferred |
| Embedding model pick | S1462 | this pack |
| Per-post embedding index build | S1463 | this pack |
| Query embedding | S1464 | this pack |
| Hybrid merge (lexical + semantic) | S1465 | this pack |
| Query expansion (NobuAI) | S1466 | this pack |

## Latency budget

- Lexical channel: <50ms (FTS5 in-process)
- Semantic channel: <150ms (vector ANN)
- Total search response: <300ms P95

## Acceptance gates

- Side-by-side eval: 100 hand-labeled queries, semantic must improve P@5 by ≥15% over lexical-only.
- No regression on exact-match queries.
- Cost <$0.005 per query at expected QPS.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1461)
- Sister docs: `docs/GRIMBANEWS_SEMANTIC_SEARCH_EMBEDDING_MODEL_PICK.md`, `docs/GRIMBANEWS_SEMANTIC_SEARCH_INDEX_BUILD_PLAN.md`, `docs/GRIMBANEWS_SEMANTIC_SEARCH_QUERY_EMBEDDING_DESIGN.md`, `docs/GRIMBANEWS_SEMANTIC_SEARCH_HYBRID_MERGE_DESIGN.md`, `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md`
- Existing infra: FTS5 over posts in `app/Http/Controllers/GrimbaSearchController.php`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
