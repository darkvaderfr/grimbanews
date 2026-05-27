# GrimbaNews — Hate-Speech Filter Plan

**Status:** plan v0
**Owner:** Maya Patel (Compliance) + Henry Walker (Editorial) + David Chen (Data)
**Walks:** Mythos S1595 (hate-speech filter) deferred → partial
**Gating dependency:** operator-side editorial review + surrogate-now: `news_sources.factuality_score` + `news_sources.credibility_score` source-level filter on ingest.

## Why this exists

GrimbaNews currently filters source-level via factuality + credibility scores at ingest. That stops most volume but cannot catch hate speech in an otherwise-credible source (e.g. extremist op-ed in a center-right paper).

## v1 layered design

1. **Source-level (today)** — sources below factuality 4 or credibility 3 don't enter the corpus.
2. **Cluster-level (v1)** — once UGC ships (S1361+), report-based escalation flags a cluster's body / comments for editorial review.
3. **Content-classifier (v2)** — open-source hate-speech classifier (Detoxify, Perspective API, or self-hosted) scores incoming articles + comments; threshold-trigger queues for editorial.

## Editorial review flow

- Flagged item → moderation queue at `/admin/grimba/moderation`.
- Editor reviews + decides: keep / annotate-with-warning / hide / remove + per-source flag.
- Repeat-offender source automatic ingest-pause after N flags.

## Schema (additive to S1591+ moderation primitive)

```sql
CREATE TABLE moderation_flags (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  ref_kind ENUM('post', 'comment', 'cluster') NOT NULL,
  ref_id BIGINT NOT NULL,
  flag_kind ENUM('hate', 'incitement', 'harassment', 'spam', 'misinformation') NOT NULL,
  flagged_by ENUM('reader', 'editor', 'classifier') NOT NULL,
  score TINYINT NULL,
  notes TEXT NULL,
  status ENUM('open', 'reviewed', 'actioned', 'dismissed') DEFAULT 'open',
  reviewer_user_id BIGINT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_status (status, created_at)
);
```

## Anti-patterns

- No automated removal without editor review (false positives are real).
- No public per-source hate-speech score (defamation risk).
- No classifier-as-only-judge.

## Cross-references

Master plan: S1595. Sister: S1591+ (moderation set), S1361+ (comments), S2001 (transparency report — flag counts).
