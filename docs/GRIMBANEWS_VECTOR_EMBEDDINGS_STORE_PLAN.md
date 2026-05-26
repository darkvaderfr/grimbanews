# GrimbaNews — Vector Embeddings Store Plan

**Status:** plan v0 (no vector store; lexical search ships today)
**Owner:** Jacob Lee (DevOps) on infra pick + Larry Ellison (VP DBA) on schema + Ray Dalio (CFO) on cost + Sara Chen (CISO) on privacy
**Walks:** Mythos S1701 (vector store infra pick) + S1702 (vector store schema) deferred → partial
**Gating dependency:** Infra-vendor decision (pgvector vs Qdrant vs Pinecone vs Weaviate) — operator-side per Ray + Jacob review + budget approval. Schema design itself is operator-side.

## Why this exists

S1701 + S1702 share a root: GrimbaNews ships **no vector store** today. Lexical search (FULLTEXT on `posts.name + posts.description`) is the v1 surrogate. Vector store is the precondition for: semantic search (per `docs/GRIMBANEWS_SEARCH_V2_LAUNCH_PLAYBOOK.md`), personalization v2 (per `docs/GRIMBANEWS_PERSONALIZATION_V2_LAUNCH_PLAYBOOK.md`), cross-publisher dedup at the article level, related-dossier improvement. This doc proposes the infra pick + schema so the decision is informed when Ray + Jacob review.

## Infra options

| Option | Cost shape | Hosting | Pros | Cons |
|---|---|---|---|---|
| **pgvector** (Postgres extension) | $0 above existing Postgres | Same VPS as Botble | Single-DB simplicity; no extra ops surface; transactionally consistent with `posts` | Today we're SQLite — would need migration to Postgres OR run pgvector-Postgres alongside SQLite (split-brain risk) |
| **Qdrant** (open-source, self-hosted) | $0 software, $X VPS | Same VPS or sister VPS | Open-source; full control; no vendor lock-in | Adds an ops surface; backup story separate from existing `GrimbaDatabaseBackups` |
| **Qdrant Cloud** | $30+/month managed | Vendor | Managed ops | Vendor lock-in; per-month line item |
| **Pinecone** | $70+/month | Vendor | Mature; battle-tested | Most expensive; least privacy-friendly (US-hosted by default) |
| **Weaviate** | similar Qdrant Cloud | Vendor | Strong query API; rich filtering | Vendor lock-in |
| **Marqo** | self-hosted alternative | Same VPS | Bundles embedding generation | Less mature |

**Recommended pick: Qdrant self-hosted on a sister VPS** (Iboga hosting policy per `feedback_hosting_policy.md`).

Rationale:
- No vendor lock-in.
- No per-vector pricing (cost = VPS + storage).
- Existing SQLite stays the source-of-truth for `posts`; Qdrant is a derived index.
- Backup story can extend `App\Support\GrimbaDatabaseBackups` pattern to include Qdrant snapshots.
- Privacy posture: data stays on Iboga infra (no third-party processing of article content).

## Proposed schema (Qdrant collection)

**Collection: `posts_embeddings`**

Per-vector payload (Qdrant's `payload`):

```json
{
  "post_id": 12345,
  "story_cluster_id": 678,
  "title": "...",
  "original_language": "fr",
  "editorial_region": "africa",
  "editorial_category": "politics",
  "news_source_id": 42,
  "bias_rating": "center-left",
  "published_at": "2026-05-26T08:00:00Z",
  "is_active": true,
  "methodology_version": "v1"
}
```

Vector dimension: **384** (sentence-transformers/all-MiniLM-L6-v2 dim) OR **768** (sentence-transformers/all-mpnet-base-v2 dim).

Recommended: 384-dim MiniLM for cost — 50% less storage, ~95% of mpnet quality for our use case.

**Collection: `clusters_embeddings`** (S1334 ship target)

Per-cluster aggregate vector — average of constituent post vectors. Used for cluster-similarity surfaces.

## Embedding generation pipeline (S1703 ship target)

**Model:** sentence-transformers via local Python service OR via NobuAI proxy (per `feedback_nobuai_model_branding.md` — user-facing surfaces never name underlying provider).

**Pipeline:**

1. **Daily backfill** — `php artisan grimba:embeddings:backfill --days=N` walks `posts` not yet embedded; chunks 100 at a time; calls embedding endpoint; upserts to Qdrant.
2. **Incremental** — on `post.created` event, queue `EmbedPostJob` (Laravel queue).
3. **Re-embed on title/dek update** — `posts.updated_at` watcher invalidates embedding.
4. **Cluster vector regeneration** — when cluster gains/loses post, regenerate `clusters_embeddings.{cluster_id}` (S1334 dep).

**Rate-limit:** vendor-aware per chosen embedding endpoint. If using NobuAI proxy, respects existing `GrimbaNobuAi::CHAIN` failover.

## Semantic-search query handler (S1706 ship target)

`app/Services/GrimbaSemanticSearch.php` (new):

```php
public function query(string $q, array $filters = [], int $limit = 20): Collection
{
    $vector = $this->embedder->embed($q);
    $results = $this->qdrant->search('posts_embeddings', [
        'vector' => $vector,
        'limit' => $limit,
        'filter' => $this->buildFilter($filters), // editorial_region, category, date window
        'with_payload' => true,
    ]);
    return Post::whereIn('id', $results->pluck('payload.post_id'))->get();
}
```

## Cost projection (Ray review)

Per Qdrant self-hosted on a 4GB VPS:

| Item | Cost / month |
|---|---|
| VPS (4GB / 80GB SSD, Hetzner) | ~€8 |
| Embedding API calls (NobuAI proxied) | gates on per-call rate — Ray review |
| Storage growth (1M vectors × 384 dim × 4 bytes ≈ 1.5GB) | within VPS |

Versus Pinecone equivalent: ~$70/month flat. Qdrant self-hosted = ~$10/month all-in (10x cost reduction).

## Privacy posture

- **Article content (already public) embedded** — no privacy issue.
- **No reader content** ever enters embedding store.
- **No member content** ever enters embedding store.
- **Search queries** processed in-memory, not persisted (per `docs/GRIMBANEWS_SEARCH_V2_LAUNCH_PLAYBOOK.md`).

## Operational posture

- **Backup:** daily Qdrant snapshot to same backup target as `GrimbaDatabaseBackups`. Restore-drill cadence per `docs/GRIMBANEWS_DR_DRILL_2026_05_23.md` pattern.
- **Monitoring:** `grimba:health` ops check extended to verify Qdrant reachable.
- **Failure mode:** if Qdrant down, semantic search route falls back to lexical (graceful degradation per `docs/GRIMBANEWS_SEARCH_V2_LAUNCH_PLAYBOOK.md` Phase 1 feature flag).

## Cost dashboard (S1709 ship)

Per-day:
- Embedding API calls + cost.
- Qdrant disk usage.
- Query volume + latency p95.

Lives at `/admin/grimba/vector-store` (admin-only).

## Engineering effort estimate

- Infra provision + Qdrant install + backup: 2 sprints (Jacob).
- Embedding pipeline (backfill + incremental): 3 sprints.
- Semantic-search handler: 2 sprints.
- Cluster-embeddings (S1334): 2 sprints.
- Health check + monitoring: 1 sprint.
- Cost dashboard: 1 sprint.
- Tests + restore-drill: 1 sprint.
- **Full ship: ~12 sprints once infra-vendor decision lands.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1701-S1710, S1076 sister)
- Sister docs: `docs/GRIMBANEWS_SEARCH_V2_LAUNCH_PLAYBOOK.md`, `docs/GRIMBANEWS_PERSONALIZATION_V2_LAUNCH_PLAYBOOK.md`, `docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md`, `docs/GRIMBANEWS_DR_DRILL_2026_05_23.md`
- Existing search: `platform/themes/echo/views/search.blade.php`
- Existing NobuAI chain: `app/Services/GrimbaNobuAi.php::CHAIN`
- Existing backup pattern: `app/Support/GrimbaDatabaseBackups.php`
- Iboga hosting policy: `~/.claude/projects/-Users-vb-kaizen/memory/feedback_hosting_policy.md`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
