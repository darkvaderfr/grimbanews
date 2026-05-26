# GrimbaNews — Author System Launch Retrospective Scope

**Status:** plan v0 (no journalist primitive yet; retro template + acceptance criteria)
**Owner:** Liam Smith (PM) chairs retro + Steve Jobs (CPO) reviews UX wins/losses + Sophia Martinez on HR-side feedback from journalists + Lucy Leai on strategic outcome
**Walks:** Mythos S1420 (Author launch retrospective) deferred → partial
**Gating dependency:** S1411-S1419 actually shipped + at least 90 days of operation

## Why this exists

S1420 is the operator-side retrospective that captures what worked and what didn't after the byline+follow+RSS+analytics suite ships. Without a retro template ready, the lessons aren't structured.

## Today's surrogate

- **Web go-live runbook** (`docs/GRIMBANEWS_GO_LIVE_RUNBOOK.md`) carries the broader retro pattern.

## Retro structure

### Section 1 — context

- What shipped (S1411-S1419) + dates.
- Who shipped (team that built per CLAUDE.md credits).
- Adoption stats: N journalists onboarded, M followers across all journalists.

### Section 2 — what worked

- Per-feature signal: which feature got engagement (follow vs RSS vs profile views).
- Best surprise.
- Journalist quotes (collected via Sophia Martinez survey).
- Reader feedback themes.

### Section 3 — what didn't

- Per-feature signal: which feature got crickets.
- Worst surprise.
- Journalist friction (e.g., bio editing UX issues).
- Reader confusion (e.g., didn't understand "follow" vs "save").

### Section 4 — metrics dive

- Profile page traffic (David Chen).
- Follow conversion rate.
- RSS subscriber count.
- Per-journalist publishing cadence trend.
- Did follower-count predict post engagement (correlation analysis).

### Section 5 — decisions for v2

- Per-feature: keep / kill / iterate.
- New asks from journalists + readers.
- Priority for next quarter.

### Section 6 — anti-pattern lessons

- Document things to NOT repeat.
- Hand off to writing-skills memory.

## Cadence

- T+30: micro-retro (Liam + Steve + 1 journalist) — quick course-correct.
- T+90: full retro (entire team that shipped + 3 journalists + 2 power readers).
- T+180: strategic retro (Lucy + Steve + Liam + Ray) — does this justify continued investment?

## Output

- `docs/GRIMBANEWS_AUTHOR_LAUNCH_RETRO_<date>.md` posted after T+90 retro.
- Decisions logged to `KAIZEN_FEATURE_QUEUE.md` if cross-product implications.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1420)
- Sister docs: `docs/GRIMBANEWS_AUTHOR_TABLE_SCHEMA.md`, `docs/GRIMBANEWS_AUTHOR_PROFILE_PAGE_SCOPE.md`, `docs/GRIMBANEWS_AUTHOR_FOLLOW_DESIGN.md`, `docs/GRIMBANEWS_AUTHOR_RSS_FEED_DESIGN.md`, `docs/GRIMBANEWS_AUTHOR_ANALYTICS_DASHBOARD_SCOPE.md`, `docs/GRIMBANEWS_GO_LIVE_RUNBOOK.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
