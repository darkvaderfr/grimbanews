# GrimbaNews — Per-Article Embedding Generation Surrogate Plan

**Sprint ID:** S1333
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — Per-article embedding generation`
**Walk wave:** CCCC

## Gating dependency

Per-article embedding needs:

- Vector store (S1332)
- A `posts.embedding_vec` (or sidecar embeddings table) column
- A background job that embeds new + updated articles
- Per-model migration policy (when embedding model changes, full rebuild)
- Cost budget per article (~$0.0001 OpenAI ada-002, free NobuAI fast model)

## Surrogate-now infra

- **`grimba:nobuai-summaries`** — per-article enrichment cron pattern (runs every 30min over stale rows) — same shape for embeddings
- **`posts.summary_nobuai` column** — proof-of-concept for per-post NobuAI-generated field
- **`GrimbaArticleText::normalize()`** — text-normalization helper that would feed embedding input

## Honest framing

Once S1332 lands, this is a 2-day build (clone summary pipeline, swap completion for embedding endpoint, write vector instead of text). Cost is the only ongoing concern.

## Owners

- **Data:** David Chen — embedding pipeline
- **Backend:** Rajesh Kumar — column + job
- **DBA:** Larry Ellison — index strategy (IVFFlat / HNSW)
- **Finance:** Warren Buffett — cost approval
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1333 row)
- Embedding store: `docs/GRIMBANEWS_EMBEDDING_STORE_WIRING_PLAN.md`
- Per-cluster embedding: S1334 (deferred)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
