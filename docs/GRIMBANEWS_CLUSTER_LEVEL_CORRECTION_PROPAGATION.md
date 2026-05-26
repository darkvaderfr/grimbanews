# GrimbaNews — Cluster-Level Correction Propagation

**Status:** plan v0 (no per-cluster correction state; per-post correction propagation deferred)
**Owner:** Rajesh Kumar (Backend) implements + Larry Ellison on denormalized counter + Liam Smith (PM) on editorial workflow alignment
**Walks:** Mythos S1435 (Cluster-level correction propagation) deferred → partial
**Gating dependency:** `posts.correction_*` schema (S1433) + `story_clusters` model (shipped)

## Why this exists

S1435 surfaces "a story in this cluster has been corrected" at the cluster level. Reader on dossier page should see this without clicking into individual posts.

## Today's surrogate

- **None** — clusters carry no correction state.

## Schema

```sql
ALTER TABLE story_clusters ADD COLUMN corrections_count INT DEFAULT 0;
ALTER TABLE story_clusters ADD COLUMN last_correction_at TIMESTAMP NULL;
ALTER TABLE story_clusters ADD COLUMN highest_severity ENUM('minor','factual','retraction') NULL;
```

## Trigger / cron logic

Approach A — DB trigger (preferred if MySQL version supports):

```sql
CREATE TRIGGER post_correction_propagate_to_cluster
AFTER UPDATE OF correction_issued_at, correction_severity ON posts
FOR EACH ROW
WHEN NEW.story_cluster_id IS NOT NULL AND NEW.correction_issued_at IS NOT NULL
BEGIN
  UPDATE story_clusters SET
    corrections_count = (SELECT COUNT(*) FROM posts WHERE story_cluster_id = NEW.story_cluster_id AND correction_issued_at IS NOT NULL),
    last_correction_at = (SELECT MAX(correction_issued_at) FROM posts WHERE story_cluster_id = NEW.story_cluster_id),
    highest_severity = (SELECT MAX(correction_severity) FROM posts WHERE story_cluster_id = NEW.story_cluster_id)
  WHERE id = NEW.story_cluster_id;
END;
```

Approach B — cron + Eloquent observer (more portable):

- `PostObserver::updated()` — if correction columns changed and cluster exists, queue cluster rollup.
- `grimba:cluster-correction-rollup` cron runs queued cluster IDs every 1 min.

## Dossier UI propagation (gates on S1433 badge design)

```
+--------------------------------------------------+
| Climate Summit Opens — 12 sources               |
| Bias: 35% L / 30% C / 35% R · Middle Ground     |
|                                                  |
| ⚠ 1 article in this dossier was corrected       |
|   on 2026-05-26                                  |
|                                                  |
| Article list...                                  |
+--------------------------------------------------+
```

## Reader notification path

- Reader who saved post: per-post correction notice push (gates on S1154 + S1175 push categories with `correction-issued` category).
- Reader who saved cluster (follow-cluster gates on S1518 deferred): correction propagation push.

## Editorial integrity guard (Liam Smith)

- Retraction-severity correction on cluster member triggers cluster review by editor.
- If retraction = full retraction: post status → unpublished, but correction record remains.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1435)
- Sister docs: `docs/GRIMBANEWS_CORRECTION_NOTICE_BADGE_DESIGN.md`, `docs/GRIMBANEWS_CORRECTION_POLICY_PUBLIC_PAGE_SCOPE.md`, `docs/GRIMBANEWS_PUSH_CATEGORIES_GOVERNANCE.md`
- Existing: `story_clusters` model
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
