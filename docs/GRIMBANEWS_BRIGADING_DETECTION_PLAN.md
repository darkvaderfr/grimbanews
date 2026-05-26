# GrimbaNews — Brigading Detection Plan

**Status:** plan v0 (no per-user behavior tracking; no comment surface yet)
**Owner:** Sara Chen (CISO) on threat model + David Chen on anomaly model + Maya Patel on review queue
**Walks:** Mythos S1593 (Brigading detection — anomalous traffic) deferred → partial
**Gating dependency:** Comments / reactions surface (S1361+) live + per-IP / per-account telemetry.

## Why this exists

S1593 catches coordinated bad-faith activity — bot rings, coordinated downvote campaigns, mass-reporting abuse. None of the surfaces it would protect exist yet (no comments, no votes, no flags).

## Today's surrogate

- No user-generated content surface. Brigading attack surface is zero.

## Detection signals (when surfaces exist)

- Spike: comments / votes from new accounts >5σ above baseline
- Geo: cluster of unrelated activity from same /24 subnet
- Account-age: bursts from <7-day-old accounts
- Bias-uniformity: voting pattern aligned >95% with one position
- Cross-account coordination: same wording fingerprints across multiple accounts

## Response

- Tier 1: shadow-flag (item still visible to author, suppressed from public counts)
- Tier 2: temp-hold for review (out of public count, queued to Maya)
- Tier 3: ban + retroactive removal of associated activity

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1593)
- Sister docs: `docs/GRIMBANEWS_DOWNVOTE_SPAM_GUARD_PLAN.md`, `docs/GRIMBANEWS_COMMENTER_BAN_LIST_PLAN.md`, `docs/GRIMBANEWS_TRUST_SAFETY_LAUNCH_RETRO_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
