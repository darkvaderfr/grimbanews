# GrimbaNews — Per-Cluster Decay + Archive Policy

**Status:** plan v0
**Owner:** Larry Ellison (DBA) + Lucy Leai (Strategy) + Steve Jobs (CPO)
**Walks:** Mythos S1473 (per-cluster decay/archive policy) deferred → partial
**Gating dependency:** Vader sign-off on archive UX + scheduler implementation.

## Why this exists

Active corpus contains ~10K clusters. Over time, this grows unbounded — degrading search speed + cluttering /dossiers. A decay policy archives stale clusters while preserving the dossier permanently at its URL (for back-link integrity).

## Tier system

| Tier | Definition | Behavior |
|---|---|---|
| Hot | Active (article added < 7 days ago) | Listed everywhere; full embedding + cluster-merge consideration |
| Warm | Recently active (7-30 days) | Listed in /dossiers default; eligible for ranking, not re-merge |
| Cool | Older (30-180 days) | Listed in /dossiers archive view only; not in ranking |
| Cold | Stale (180-365 days) | Listed via search only; embedding evicted from hot index |
| Archived | Older than 365 days | Static page; full search; not in any feed |

## Per-cluster behavior on transition

- Hot → Warm at day-7: no change in URL; drops out of /dossiers default
- Warm → Cool at day-30: drops from ranking weight
- Cool → Cold at day-180: embedding moved to cold storage
- Cold → Archived at day-365: added to `cluster_archived_at` column; static-serve path

## Reader UX

- /comparatif/{id} URL works for all tiers (permanent, no 404 ever)
- Archived cluster page carries an "Archivé · YYYY-MM-DD" badge
- /dossiers/archive route lists all archived clusters with filter by year/month
- Search returns archived clusters with archive tag

## Schema

```
ALTER TABLE story_clusters ADD COLUMN tier ENUM('hot','warm','cool','cold','archived') DEFAULT 'hot';
ALTER TABLE story_clusters ADD COLUMN archived_at TIMESTAMP NULL;
```

## Daily cron

`grimba:age-clusters` daily 04:00 UTC: walks all clusters, transitions tiers based on `last_post_at` column. Idempotent.

## Cross-references

Master plan: S1473. Sister: `docs/GRIMBANEWS_PER_CLUSTER_NARRATIVE_TIMELINE.md`, `docs/GRIMBANEWS_RTO_RPO_DEFINITION.md` (Wave LLL).
