# GrimbaNews — Peer-Reviewed Journal Coverage Plan

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + science editor TBD + Lisa Nguyen (data)
**Walks:** Mythos S2187 (Science v2 peer-reviewed-journal coverage) deferred → partial
**Gating dependency:** Science editor + per-journal RSS/Atom feeds.

## Why this exists

Mainstream science press cherry-picks Nature / Science / Lancet papers; specialist journals (PNAS, Cell, NEJM) get less coverage. GrimbaNews can fill the gap with structured per-journal coverage.

## Tier-1 journals to cover

- **Nature** (multi-disciplinary, top-tier).
- **Science** (multi-disciplinary, top-tier).
- **Lancet** (medical).
- **NEJM** (medical).
- **PNAS** (multi-disciplinary).
- **Cell** (biology).
- **Nature Climate Change** (climate-specific).
- **Nature Medicine** (medicine).

## Per-journal feed integration

Each journal publishes RSS for new articles. `RssFeedsSeeder.php` row per journal:

```
[
    'name' => 'Nature',
    'website' => 'https://www.nature.com',
    'feed_url' => 'https://www.nature.com/nature.rss',
    'editorial_category' => 'sciences',
    'credibility_score' => 95,
    'factuality_score' => 95,
    'ownership_type' => 'private',
    'license_notes' => 'press-release content under embargo policy',
]
```

## Embargo handling

Journals embargo papers until publication time. Some allow pre-embargo briefings to press. GrimbaNews:
- Respects embargoes (no early publication).
- Sets up per-journal press-relations contact.
- Per-article carries publication-time stamp matching embargo lift.

## Cluster-merge

Per-paper article often gets covered by multiple downstream sources (Reuters, AP, then publishers). Cluster merge groups journal-original + downstream coverage.

## Cross-references

Master plan: S2187. Sister: `docs/GRIMBANEWS_PREPRINT_SERVER_INGEST_PLAN.md`, `docs/GRIMBANEWS_IPCC_REPORT_COVERAGE_PLAYBOOK.md`.
