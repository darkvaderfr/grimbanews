# GrimbaNews — Semantic Search Embedding Index Build Plan

**Status:** plan v0 (no index exists; backfill not run)
**Owner:** Rajesh Kumar (Backend) on Artisan command + Hannah Kim (Platform) on infra + David Chen on chunking strategy
**Walks:** Mythos S1463 (Semantic search embedding index build) deferred → partial
**Gating dependency:** Embedding store provisioned (S1076) + model picked (S1462) + cost approved (Ray).

## Why this exists

S1463 is the build pipeline. Once a model is picked, every existing post + every new post needs an embedding written to the store. Backfill of a 100k-post corpus is a one-shot ~$2 + multi-hour run.

## Today's surrogate

- No embeddings — posts only have FTS5 index.

## Build pipeline

```
posts ──► [chunk: title + summary + first 800 chars body]
       ──► [embed: NobuAI driver → vector dim D]
       ──► [write: vector store, key = posts.id, metadata = {locale, published_at, source_id, cluster_id}]
       ──► [verify: dim length, no NaN, count matches posts]
```

## Chunking strategy

- Single chunk per post for v1 (title + meta-description + first 800 chars of body).
- Multi-chunk per post deferred to v2 (per-paragraph embedding for long-form).

## Artisan command (target)

```bash
php artisan grimba:embed-posts \
    --since=2024-01-01 \
    --chunk-strategy=single \
    --rebuild=false \
    --batch=50 \
    --rate-limit=100/min
```

## Resilience

- Idempotent (re-running skips posts with current `embeddings.posts.id` row).
- Retry-on-fail with exponential backoff.
- Per-batch checkpoint to `grimba_automation_runs`.

## Backfill estimate

- 100k posts at 100 embeds/min = ~17 hours
- Cost: ~$1.60 (per S1462 estimate)
- Re-runs: trigger when corpus changes >10% or model swaps

## On-create trigger

- `PostObserver::created()` enqueues `EmbedPostJob` — every new post gets indexed within minutes.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1463)
- Sister docs: `docs/GRIMBANEWS_SEMANTIC_SEARCH_DESIGN_DOC.md`, `docs/GRIMBANEWS_SEMANTIC_SEARCH_EMBEDDING_MODEL_PICK.md`
- Existing infra: Botble `PostObserver`, Laravel job queue
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
