# GrimbaNews — Embedding Store Wiring Surrogate Plan

**Sprint ID:** S1332
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — Embedding store wiring`
**Walk wave:** CCCC

## Gating dependency

A vector store needs:

- A vector DB decision (pgvector / Qdrant / Pinecone / Weaviate)
- Hosting + cost model (pgvector lives in existing MariaDB-not-PG → switch or sidecar PG; Qdrant is self-host friendly; Pinecone is hosted SaaS)
- An embedding provider (NobuAI driver-side; gates on S1076 vector DB)
- A migration policy (cold-rebuild vs streaming-rebuild on model change)

## Surrogate-now infra

- **`grimba_health` cron** — pattern for periodic-rebuild jobs we'd reuse for embedding refresh
- **`config/grimba_credits.php`** — per-provider cost config pattern
- **`tests/Feature/GrimbaArticleDedupeTest`** — text-similarity tests today via canonical-URL + title fuzzy; semantic dedupe is the v2

## Honest framing

Cornerstone gate for the entire semantic-search + RAG roadmap (S1077, S1335, S1336, S1346). Decision-heavy (vendor + cost model) more than build-heavy. Today's `GrimbaArticleDedupe` proves the *non-semantic* approach is sufficient for canonical-URL dedupe; semantic dedupe is a v2 tier feature.

## Owners

- **CTO:** Elon Musk — vendor selection
- **DBA:** Larry Ellison — schema + sidecar PG decision
- **Data:** David Chen — provider choice + benchmark
- **DevOps:** Jacob Lee — hosting + cost model
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1332 row)
- Per-article embedding: S1333 (deferred)
- Semantic search: S1335 (deferred)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
