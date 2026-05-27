# GrimbaNews — Content-Based Filter (Article-Similarity) Surrogate Design

**Sprint ID:** S1344
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1341-s1350 — Content-based filter (article-similarity)`
**Walk wave:** BBBB

## Gating dependency

A content-based article-similarity ranker needs:

- Per-article embeddings (S1333, deferred — gates on embedding store S1332)
- Vector DB (pgvector / qdrant / pinecone — S1076, deferred)
- ANN query layer (FAISS / cosine over indexed vectors)
- Ranker integration ("readers also viewed" + "more on this story")
- Per-article-similarity score cache

None of those ship today. There is no embedding column on `posts`, no vector index, no ANN service.

## Surrogate-now infra

What approximates article similarity today:

- **`StoryCluster`** — the cluster engine groups articles by lexical / source / time signals (rule-based, S1053)
- **`GrimbaDossierVoices`** — per-cluster: surfaces the other articles in the same cluster (a hard-coded "more on this story" rail)
- **Tag overlap** — Botble's native tag system gives a lightweight Jaccard-on-tags fallback
- **Per-category landing** — `/categorie/{slug}` lists same-bucket articles

The cluster engine is the de-facto content-similarity engine today. It just does not use embeddings — it uses title-similarity + source-coverage + time-window heuristics.

## Honest framing

The current cluster engine already does what most readers need from "content similarity" (within-cluster). A true content-based filter using embeddings unlocks **cross-cluster** similarity ("related but not in the same story") — which is the actual deferred capability.

## Owners

- **Data Science:** David Chen — embedding model selection + similarity scoring
- **Data Eng:** Benjamin Lee — vector DB provisioning + ANN index
- **Backend:** Rajesh Kumar — ranker + cache layer
- **DevOps:** Jacob Lee — pgvector / qdrant operational ownership
- **DBA:** Larry Ellison — schema + index sizing
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1344 row)
- Embedding-store gate: `docs/GRIMBANEWS_EMBEDDING_STORE_VECTOR_DB_DESIGN.md` (already partial)
- Cluster engine: `app/Support/GrimbaClusterEngine.php`
- Dossier-voices partial: `resources/views/partials/grimba/dossier-voices.blade.php`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
