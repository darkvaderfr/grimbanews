# GrimbaNews — Per-Cluster Auto-Translation Pipeline

**Status:** plan v0 (per-article translation ships per `GrimbaTranslator`; per-cluster orchestration deferred)
**Owner:** Lisa Nguyen (data) + Rajesh Kumar (backend)
**Walks:** Mythos S1724 (per-cluster auto-translation pipeline) deferred → partial
**Gating dependency:** Per-cluster cross-locale matching (Wave AAJJ).

## Why this exists

When a cluster spans multiple source-locales (FR primary + EN coverage of same event), readers want each article in their preferred locale. Per-cluster orchestrated translation ensures consistency + caches results.

## v1 design

For each multi-locale cluster:

1. Identify primary-locale (most articles).
2. For each non-primary article, queue translation to primary-locale via `GrimbaTranslator`.
3. Per-translated-article stored as `posts.translated_summary_<locale>`.
4. Per-cluster, reader picks locale; system serves matching summary.

## Cache + cost

- Per-article translation: ~$0.005 (NobuAI fallback chain).
- Per-cluster orchestration: ~10 articles avg × 2 locales = 20 calls per multi-locale cluster.
- Cache permanently (translations don't change).

## Editor review

- Per-cluster translations sampled weekly.
- Per-locale editor flags low-quality translations.
- Re-translate via different driver for flagged.

## Cross-references

Master plan: S1724. Sister: `docs/GRIMBANEWS_CROSS_LOCALE_STORY_MATCHING_PLAN.md`, `app/Services/GrimbaTranslator.php`.
