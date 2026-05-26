# GrimbaNews — Correction Flow Launch Retrospective Plan

**Status:** plan v0 (no corrections live — gates on S1431-S1439)
**Owner:** Lucy Leai (CEO) + Liam Smith (PM) + Michael O'Connor (Technical Writer) for public correction policy clarity
**Walks:** Mythos S1440 (Correction-flow launch retrospective) deferred → partial
**Gating dependency:** Corrections primitive + badge (S2006, S1431-S1439) shipped + ≥90 days of live correction issuance.

## Why this exists

S1440 closes the corrections band. Like S1430, it's a retro on a system that doesn't exist. Pre-staging the retro template now means the next session can fill numbers.

## Today's surrogate

- No corrections issued — all post-publish edits go via direct overwrite without log.

## Retro template

### Section 1 — Volume
- Corrections issued (total / per-locale / per-author / per-source)
- Article-scope vs translation-scope ratio
- Time-from-issue-to-correction (P50 / P95)

### Section 2 — Origin distribution
- Reader-reported vs internal-discovered ratio
- Source right-of-reply requests resolved as corrections
- Cluster-level propagation rate (S1431 dependency)

### Section 3 — Reader experience
- Badge visibility CTR
- Public correction policy page (S1438 dependency) views
- Subscriber notifications on correction (S1437 dependency) engagement

### Section 4 — Editorial process
- Reviewer load
- Backlog (uncorrected acknowledged complaints)
- False-positive rate (corrections that were reverted)

### Section 5 — Decisions
- Policy tightening / loosening
- Tooling changes
- Owner + due date per change

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1440)
- Sister docs: `docs/GRIMBANEWS_CORRECTION_NOTICE_BADGE_DESIGN.md`, `docs/GRIMBANEWS_CORRECTION_POLICY_PUBLIC_PAGE_SCOPE.md`, `docs/GRIMBANEWS_TRANSLATION_LEVEL_CORRECTION_DESIGN.md`, `docs/GRIMBANEWS_CLUSTER_LEVEL_CORRECTION_PROPAGATION.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
