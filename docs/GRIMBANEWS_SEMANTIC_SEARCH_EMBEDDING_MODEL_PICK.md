# GrimbaNews — Semantic Search Embedding Model Pick

**Status:** plan v0 (no embeddings generated; no model contracted)
**Owner:** David Chen (Data Scientist) on benchmark + Elon Musk (CTO) on procurement + Ray Dalio (CFO) on cost
**Walks:** Mythos S1462 (Semantic search embedding model pick) deferred → partial
**Gating dependency:** Embedding store (S1076) + benchmark corpus assembled.

## Why this exists

S1462 picks the embedding model. Decision drives store sizing, latency, cost, and multilingual quality. Per global NobuAI branding policy, user-facing surface remains "NobuAI" regardless of model provenance.

## Candidates

| Family | Dim | Cost per 1M tokens | Multilingual | Notes |
|---|---|---|---|---|
| NobuAI (internal future) | TBD | TBD | TBD | Long-term target; not shipped |
| OpenAI text-embedding-3-small | 1536 (configurable) | $0.02 | strong | Cheap, mature |
| OpenAI text-embedding-3-large | 3072 | $0.13 | strongest | 6x cost; reserve for re-rank |
| Cohere embed-multilingual-v3 | 1024 | $0.10 | strong on FR, ES, ZH, AR | Multilingual native |
| BAAI/bge-m3 (self-hosted) | 1024 | $0 + GPU | strong | Needs GPU box (S1072 deferred) |
| Voyage AI voyage-2 | 1024 | $0.10 | strong | Optimized for retrieval |

## Decision matrix (David Chen rubric)

| Criterion | Weight | Notes |
|---|---|---|
| P@5 on news-retrieval benchmark | 0.30 | Build from `posts.title` + hand-labels |
| FR / EN parity (must be ≤5% gap) | 0.20 | Core editorial requirement |
| Per-query cost <$0.001 | 0.20 | At 10k QPS daily ceiling |
| Latency P95 <100ms | 0.15 | Network roundtrip incl. |
| Provider stability / SLA | 0.10 | 1-year horizon |
| Multilingual reach (≥6 locales) | 0.05 | S1101+ roadmap |

## Preliminary recommendation

**OpenAI text-embedding-3-small @ 1536-dim** as baseline. Re-rank top-50 with `text-embedding-3-large` if quality gap requires. Migrate to NobuAI-internal when ready.

## Cost projection

- 100k posts × 1536 dim × 4 bytes ≈ 615 MB index
- Generation: 100k × 800 tokens avg × $0.02/M = $1.60 one-shot
- Daily re-index of ~5k new posts: $0.08/day = $30/year

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1462)
- Sister docs: `docs/GRIMBANEWS_SEMANTIC_SEARCH_DESIGN_DOC.md`, `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
