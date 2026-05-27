# GrimbaNews — Per-Cluster Embedding Generation Surrogate Plan

**Sprint ID:** S1334
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1331-s1340 — Per-cluster embedding generation`
**Walk wave:** CCCC

## Gating dependency

Per-cluster embedding (story-cluster-level vector for "find related stories") needs:

- Per-article embeddings (S1333) — the cluster embedding is usually the centroid of member-article embeddings
- A `story_clusters.embedding_vec` column or sidecar
- A rebuild trigger on cluster-member change

## Surrogate-now infra

- **`StoryCluster` model** — model + member relations already in place
- **`GrimbaDossierVoices`** — already computes per-cluster aggregations; would naturally extend to centroid embedding
- **`grimba:nobuai-summaries`** — cron pattern that updates `posts.summary_nobuai` analogous to cluster embedding updates

## Honest framing

Trivial after S1333 — `embedding_centroid = mean(member_embeddings)`. Same gating chain (vector DB).

## Owners

- **Data:** David Chen — centroid policy (mean vs medoid)
- **Backend:** Rajesh Kumar — cluster-rebuild trigger
- **DBA:** Larry Ellison — index on cluster vector
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1334 row)
- Per-article embedding: `docs/GRIMBANEWS_PER_ARTICLE_EMBEDDING_GENERATION_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
