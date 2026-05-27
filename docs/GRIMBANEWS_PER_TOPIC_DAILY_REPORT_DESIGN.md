# GrimbaNews — Per-Topic Daily Report Design

**Status:** plan v0
**Owner:** Henry Walker (Editorial) + Liam Smith (PM)
**Walks:** Mythos S1384 (per-topic daily report) deferred → partial
**Gating dependency:** S1382 baseline daily report + S1411 topic-editor program + `reader_topic_preferences` table.

## Why this exists

Beyond the global daily report, power readers want a per-topic edition (e.g. "Climat daily", "Tech daily"). v1 ships ≤ 6 topics aligned to S1411 editorial categories to avoid fragmentation.

## v1 topic list

- Politique
- International
- Climat & Environnement
- Économie & Travail
- Sciences & Santé
- Culture & Société

(Final list gates on S1411 topic-editor sign-off.)

## Schema

```sql
CREATE TABLE reader_topic_preferences (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  member_id BIGINT NOT NULL,
  topic_slug VARCHAR(64) NOT NULL,
  daily_optin BOOLEAN DEFAULT FALSE,
  weekly_optin BOOLEAN DEFAULT FALSE,
  UNIQUE KEY uq_pref (member_id, topic_slug)
);
```

## Send shape

- One email per opted-in topic per day, batched per reader (single envelope, sectioned by topic) to avoid inbox-overload.
- Same 06:30 reader-local window as S1382.
- Each topic block: hero cluster + 2 secondary + per-topic methodology link.

## Anti-patterns

- No more than 6 topics in v1 (more = picking-fatigue, lower opens).
- No auto-opt-in based on reading history (opt-in is explicit).
- No paid-tier gating in v1 (gates with S1388 when monetization ships).

## Cross-references

Master plan: S1384. Sister: S1382 (baseline), S1411 (topic editor), S1388 (subscriber-only tier), S1387 (A/B).
