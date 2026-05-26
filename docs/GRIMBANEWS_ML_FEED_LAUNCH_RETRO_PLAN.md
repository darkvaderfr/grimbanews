# GrimbaNews — ML Feed Launch Retrospective Plan

**Status:** plan v0 (gates on S1501-S1509)
**Owner:** David Chen + Liam Smith + Lucy Leai
**Walks:** Mythos S1510 (ML feed launch retrospective) deferred → partial
**Gating dependency:** Design (S1501), collaborative filter (S1502), A/B harness (S1509), full S1503-S1508 band shipped + ≥90 days of live ML feed.

## Why this exists

S1510 closes the ML feed band. Pre-stage template now.

## Retro template

### Section 1 — Adoption
- Opt-in rate (target ≥10%)
- Sustained-use rate (DAU/MAU among opted-in)
- Opt-out rate after experiencing it

### Section 2 — Quality outcomes
- Top-1 CTR vs cookie-only baseline
- Time-on-feed vs baseline
- Return-7d rate vs baseline

### Section 3 — Diversity / fairness
- Diversity guard violation rate (target 0)
- Bias-distribution per reader (must remain spread)
- Echo-chamber complaints (reader survey)

### Section 4 — Privacy
- Number of "forget my feed" requests honored
- Audit log review (no unauthorized access)
- Sara Chen sign-off

### Section 5 — Cost
- Per-recommendation cost vs Ray's budget
- Per-recommendation latency vs target

### Section 6 — Decisions
- Default off / opt-in toggle calibration
- Model upgrade candidates
- Diversity guard tuning

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1510)
- Sister docs: `docs/GRIMBANEWS_ML_FEED_DESIGN_DOC.md`, `docs/GRIMBANEWS_ML_FEED_COLLABORATIVE_FILTER_PLAN.md`, `docs/GRIMBANEWS_ML_FEED_AB_HARNESS_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
