# GrimbaNews — Comment Brigading Detection Plan

**Status:** plan v0
**Owner:** Sara Chen (CISO) + David Chen (Data) + Maya Patel (Compliance)
**Walks:** Mythos S1352 (brigading detection — comments/reactions) deferred → partial
**Gating dependency:** UGC primitives (votes, reactions, comments — all currently deferred S1361+).

## Why this exists

When comments + reactions ship (S1361-S1370), coordinated brigading attacks (mass downvote, mass report, mass-flag a story) become a vector for editorial manipulation. Detection has to ship at-or-before the UGC surfaces, not after.

S1593 (general anti-brigading already documented at GRIMBANEWS_BRIGADING_DETECTION_PLAN.md) covers the IP/account-side anomaly model — this plan is the **comment-surface-specific** variant that S1352 calls for.

## v1 signal set (comment-surface)

- Burst detection: > N reactions in T-minute window on a single comment from accounts < 7 days old.
- Cross-thread coordination: same actor set hitting > X threads in same hour.
- Sentiment-cluster anomaly: identical-text or near-duplicate comments from > N distinct accounts within an hour.
- Time-of-day deviation: coordinated reaction outside organic reader pattern.

## Detection pipeline

- Real-time scoring on each comment/reaction action via lightweight extractor → `brigade_comment_signals` table.
- Daily rollup job aggregates signals per cluster + per actor.
- Auto-throttle: actor-side actions queue for review once score > threshold.
- Editorial override via ombudsman (S2021).

## Schema

```sql
CREATE TABLE brigade_comment_signals (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  cluster_id BIGINT NULL,
  comment_id BIGINT NULL,
  actor_member_id BIGINT NULL,
  signal_type ENUM('burst', 'cross_thread', 'duplicate_text', 'temporal_anomaly') NOT NULL,
  score TINYINT NOT NULL,
  detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  meta JSON NULL,
  INDEX idx_cluster (cluster_id, detected_at),
  INDEX idx_actor (actor_member_id, detected_at)
);
```

## Anti-patterns

- No public shaming of suspected brigaders.
- No automatic permaban on score alone (humans review).
- No reader-visible "this article is being brigaded" badge.

## Cross-references

Master plan: S1352. Sister: S1361-S1370 (comments set), S1593 (general brigading at `GRIMBANEWS_BRIGADING_DETECTION_PLAN.md`). Memory: `feedback_dream_team_audit.md`.
