# GrimbaNews — Commenter Ban List Plan

**Status:** plan v0 (no commenter primitive — comments band entirely deferred)
**Owner:** Sara Chen (CISO) + Maya Patel (Compliance Officer) + Ethan Wilson (Support/CX) for appeals
**Walks:** Mythos S1597 (Author / commenter ban list) deferred → partial
**Gating dependency:** Comments band (S1361-S1370) live + member auth + appeals workflow.

## Why this exists

S1597 is the ban list that backs the moderation queue. Cannot exist before the commenter primitive does.

## Today's surrogate

- No commenter primitive.

## Schema (target)

```sql
CREATE TABLE commenter_bans (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  member_id BIGINT NOT NULL,
  ban_type ENUM('shadow','soft','hard') NOT NULL,
  scope ENUM('comments','reactions','all_ugc') DEFAULT 'comments',
  reason VARCHAR(255) NOT NULL,
  imposed_by BIGINT NOT NULL,
  expires_at TIMESTAMP NULL,                -- NULL = permanent
  appeal_status ENUM('none','pending','denied','granted') DEFAULT 'none',
  created_at TIMESTAMP,
  INDEX (member_id, expires_at)
);
```

## Ban tiers

- Shadow: comments still post for the author, suppressed from others
- Soft: 24h-30d cooldown
- Hard: indefinite; requires appeal

## Appeals

- Member can appeal via `/account/appeals/{ban_id}`.
- Ethan triages tier 1; Maya tier 2; Vader final.
- All appeals logged for audit.

## Transparency

- Public ban-count surfaces in annual transparency report (S2001).
- No public per-account ban disclosure (privacy).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1597)
- Sister docs: `docs/GRIMBANEWS_BRIGADING_DETECTION_PLAN.md`, `docs/GRIMBANEWS_DOWNVOTE_SPAM_GUARD_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
