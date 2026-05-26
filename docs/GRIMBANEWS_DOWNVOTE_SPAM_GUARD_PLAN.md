# GrimbaNews — Downvote Spam Guard Plan

**Status:** plan v0 (no vote primitive on reader surface)
**Owner:** Sara Chen (CISO) + David Chen on anomaly + Liam Smith (PM) on UX
**Walks:** Mythos S1594 (Downvote-spam guard) deferred → partial
**Gating dependency:** Vote / reaction surface (S1364 comment reactions) live.

## Why this exists

S1594 protects vote-based ranking from coordinated downvote attacks once any vote primitive exists.

## Today's surrogate

- No vote primitive — attack surface zero.

## Guard rules (when surface exists)

- Per-account rate limit: max 30 downvotes / hour, 200 / day
- Per-target burst limit: max 10 unique downvoters from <7-day accounts in 1 hour → freeze net score, queue for review
- IP / device fingerprint dedup: one effective vote per device per item per 24h
- Sybil: votes from accounts created same week with no other activity → 25% weight
- Disclosure: reader can see vote breakdown after a delay (anti-mob)

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1594)
- Sister docs: `docs/GRIMBANEWS_BRIGADING_DETECTION_PLAN.md`, `docs/GRIMBANEWS_COMMENTER_BAN_LIST_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
