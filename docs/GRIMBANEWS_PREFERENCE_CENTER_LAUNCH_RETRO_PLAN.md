# GrimbaNews — Preference Center Launch Retrospective Plan

**Status:** plan v0 (gates on S1511-S1519 shipping)
**Owner:** Liam Smith (PM) + Alex Morgan (UI/UX) + Sara Chen on privacy posture audit
**Walks:** Mythos S1520 (Preference-center launch retrospective) deferred → partial
**Gating dependency:** Followed topics/sources/authors/clusters (S1513, S1515, S1517, S1518) + weight slider (S1516) all shipped + ≥30 days live.

## Why this exists

S1520 closes the preference-center band — all member-controllable personalization knobs. Pre-stage retro template now.

## Retro template

### Section 1 — Surface adoption
- Members who visited `/account/preferences` (% of total members)
- Members who set at least one preference (target ≥50% of visitors)
- Drop-off funnel: visit → set first → set multiple

### Section 2 — Per-knob usage
- Followed topics per member (P50, P95)
- Followed authors per member
- Followed clusters per member
- Blocked sources per member
- Weight slider adoption (% who moved any slider from default)

### Section 3 — Outcome on engagement
- pour-vous CTR for members with active preferences vs default
- Session length for opted-in personalization vs cookie-only
- Subscription conversion for members with preferences vs without

### Section 4 — Privacy outcomes
- "Forget everything" requests count
- Member-deletion downstream cascade verified (Sara Chen)
- Audit log review (no unauthorized prefs access)

### Section 5 — Operator signals derived
- Top followed authors (validates editorial bet)
- Top blocked sources (editorial signal)
- Topic-follow distribution vs editorial topic distribution (gap)

### Section 6 — Decisions
- Default preferences re-tuning
- New knobs to expose
- Knobs to deprecate (low usage + low signal)

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1520)
- Sister docs: `docs/GRIMBANEWS_FOLLOWED_TOPICS_SERVER_PERSISTENCE_PLAN.md`, `docs/GRIMBANEWS_BLOCKED_SOURCES_UI_DESIGN.md`, `docs/GRIMBANEWS_FOLLOWED_AUTHORS_PLAN.md`, `docs/GRIMBANEWS_FOLLOWED_CLUSTERS_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
