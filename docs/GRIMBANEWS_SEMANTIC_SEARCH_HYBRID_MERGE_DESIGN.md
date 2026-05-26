# GrimbaNews — Hybrid Lexical + Semantic Merge Design

**Status:** plan v0 (lexical-only today; no merge step exists)
**Owner:** David Chen (Data Scientist) on RRF tuning + Rajesh Kumar (Backend) on integration
**Walks:** Mythos S1465 (Semantic search hybrid merge) deferred → partial
**Gating dependency:** Query embedding (S1464) + index build (S1463) shipped.

## Why this exists

S1465 merges results from the lexical channel (FTS5 BM25) with results from the semantic channel (vector ANN). Pure semantic search loses exact-keyword match queries; pure lexical loses paraphrase. Hybrid is industry standard.

## Today's surrogate

- FTS5 BM25 alone — exact-keyword strong, paraphrase weak.

## Algorithm: Reciprocal Rank Fusion (RRF)

```
for doc in (lexical_topK ∪ semantic_topK):
    score(doc) = sum over channels c:
                   1 / (k + rank_c(doc))
                 where k=60 (industry default)
sort by score desc; return top N
```

## Why RRF over weighted sum

- No need to normalize disparate score scales (BM25 vs cosine).
- Robust to one channel being silent (doc missing → contributes 0).
- Easy to tune (`k` is the only knob).
- Industry-validated (Elastic, Vespa, Weaviate all ship RRF defaults).

## Channel weighting (optional v2)

If RRF baseline underperforms in eval:
```
score(doc) = w_l * (1 / (k + rank_lexical(doc)))
           + w_s * (1 / (k + rank_semantic(doc)))
```
Default w_l = w_s = 1.0. Per-query weight learned via per-locale eval (deferred).

## Boost rules (post-merge)

- Freshness boost: ×1.2 if `published_at` within 24h
- Bias-diversity boost: per `GrimbaSourceBreakdown` — penalize over-represented bias bucket
- Quality boost: ×1.1 if `news_sources.factuality_score >= 0.8`

## Acceptance gates

- P@5 ≥ 15% above lexical-only on hand-labeled 100-query benchmark
- No regression on exact-match top-1 queries
- Latency P95 within budget (S1461 doc)

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1465)
- Sister docs: `docs/GRIMBANEWS_SEMANTIC_SEARCH_DESIGN_DOC.md`, `docs/GRIMBANEWS_SEMANTIC_SEARCH_QUERY_EMBEDDING_DESIGN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
