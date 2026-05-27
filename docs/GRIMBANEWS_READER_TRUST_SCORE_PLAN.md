# GrimbaNews — Reader Trust Score Plan

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Sara Chen (CISO) + Steve Jobs (CPO)
**Walks:** Mythos S1680 (reader trust score) deferred → partial
**Gating dependency:** Reader-contribution surfaces live (citizen fact submission per Wave AAKK, reader feedback widget per Wave AAMM, comment moderation per Wave KKKK).

## Why this exists

Some readers contribute high-quality factual corrections + thoughtful comments + valid bug reports. Trust score surfaces this pattern, lets editors prioritize their submissions in mod queue.

## v1 design

Per-reader trust score 0-100 computed from:

- Accepted-fact-submission rate (% accepted of submitted)
- Comment-mod approval rate
- Reader-feedback validity rate
- Account age + engagement-tenure
- Anti-abuse signals (negative)

## Trust tiers

- **Anonyme:** no account = no score = standard mod queue.
- **Inscrit:** 0-30 score = standard mod queue.
- **Contributeur:** 30-60 = priority mod queue (24h vs 72h response).
- **Contributeur reconnu:** 60-80 = expedited approval pathway.
- **Membre de confiance:** 80+ = auto-approved on certain low-risk surfaces.

## Anti-pattern guardrails

- Trust score never publicly exposed (don't create reader leaderboards).
- Per-reader can opt-out + reset score.
- Operator manual override always available.

## Cross-references

Master plan: S1680. Sister: `docs/GRIMBANEWS_CITIZEN_CONTRIBUTION_FACT_SUBMISSION_PLAN.md`, `docs/GRIMBANEWS_READER_FEEDBACK_WIDGET_PLAN.md`.
