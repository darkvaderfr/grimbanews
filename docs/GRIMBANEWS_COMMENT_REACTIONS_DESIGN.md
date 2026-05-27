# GrimbaNews — Comment Reactions Design

**Status:** plan v0
**Owner:** Alex Morgan (UI/UX) + Steve Jobs (Design) + Liam Smith (PM)
**Walks:** Mythos S1364 (comment reactions — like / thoughtful) deferred → partial
**Gating dependency:** S1361 comment system ships first.

## Why this exists

When comments ship, the reaction surface determines whether the discussion becomes a thoughtful exchange or a popularity contest. Grimba's deliberate reaction set rewards reasoned contribution, not virality.

## v1 reaction set

| Reaction | Slug | Visual | Purpose |
|---|---|---|---|
| Réfléchi | `thoughtful` | small candle icon | Reader found this comment well-reasoned |
| Appris | `learned` | small book icon | This taught me something |
| D'accord | `agree` | thumbs-up | Simple agreement |
| Désaccord respectueux | `respectful-disagree` | thumbs-side | Disagree but constructive |
| Source ? | `cite` | small magnifier | Asking for citation |

**No downvote.** Disagreement is captured by `respectful-disagree` (visible) or no reaction (invisible) — never a punitive vote.

## Schema

```sql
CREATE TABLE comment_reactions (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  comment_id BIGINT NOT NULL,
  member_id BIGINT NOT NULL,
  reaction_slug VARCHAR(32) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_reaction (comment_id, member_id, reaction_slug),
  INDEX idx_comment (comment_id),
  INDEX idx_member (member_id)
);
```

## UX

- Inline icon row under each comment.
- Per-reaction count displayed only if > 0.
- Tap toggles (re-tap removes).
- Self-react allowed (no special-case).

## Anti-patterns

- No top-comment-by-likes algorithmic boost.
- No reaction notification spam.
- No public "user X reacted Y" log.

## Cross-references

Master plan: S1364. Sister: S1361 (comment system), S1365 (quality scoring), S1370 (launch playbook), S1352 (brigading).
