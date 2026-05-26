# GrimbaNews — Followed Clusters Plan

**Status:** plan v0 (no follow-cluster primitive; surrogate is sharing the dossier URL)
**Owner:** Rajesh Kumar (Backend) + Liam Smith (PM) on UX + Nina Patel (Lead Frontend) on UI
**Walks:** Mythos S1518 (Followed clusters) deferred → partial
**Gating dependency:** Member auth + ongoing-story (cluster) primitive exists today.

## Why this exists

S1518 lets a reader follow a running story (cluster) — "notify me when there's new coverage of the Ukraine war." Today the only way is to keep re-visiting the dossier URL.

## Today's surrogate

- Share the `/dossier/{id}` URL — bookmark it manually.

## Schema (target)

```sql
CREATE TABLE member_followed_clusters (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  member_id BIGINT NOT NULL,
  cluster_id BIGINT NOT NULL,               -- FK story_clusters
  followed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  notify_on_new_post BOOLEAN DEFAULT TRUE,
  notify_on_breaking BOOLEAN DEFAULT TRUE,
  notify_frequency ENUM('realtime','daily','weekly') DEFAULT 'daily',
  UNIQUE KEY (member_id, cluster_id),
  INDEX (member_id), INDEX (cluster_id)
);
```

## Auto-unfollow logic

- Clusters decay (per S1380 cluster-decay deferred). When a cluster hasn't had new posts in 30 days, mark `auto_archived`. After 60 days, auto-unfollow with notification: "Cette histoire est en pause — vous n'êtes plus notifié, mais elle reste accessible."

## UI surface

- "Suivre cette histoire" button on `/dossier/{id}` page header
- `/account/follows/clusters` lists active follows + last-update timestamp
- Per-cluster notification preferences panel (realtime vs daily digest)

## Notification dispatch

- Realtime: web push (S1301+ deferred) or email instant.
- Daily: bundled into per-reader daily report (S1581 v2).
- Weekly: bundled into Saturday digest.

## Coverage signal

- Operator dashboard: per-cluster follower count → editorial signal ("we have 1,200 readers following the Iran story — keep covering").

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1518)
- Sister docs: `docs/GRIMBANEWS_FOLLOWED_AUTHORS_PLAN.md`, `docs/GRIMBANEWS_FOLLOWED_TOPICS_SERVER_PERSISTENCE_PLAN.md`, `docs/GRIMBANEWS_DAILY_REPORT_TIME_OF_DAY_VARIANTS_PLAN.md`
- Existing infra: `story_clusters` table + `/dossier/{id}` route
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
