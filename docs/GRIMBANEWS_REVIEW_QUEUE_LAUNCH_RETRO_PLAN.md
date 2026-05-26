# GrimbaNews — Review Queue Launch Retrospective Plan

**Status:** plan v0 (no review queue live yet — gates on S1421-S1429)
**Owner:** Liam Smith (PM) on retro structure + Lucy Leai on outcomes + Steve Jobs on UX learnings
**Walks:** Mythos S1430 (Review queue launch retrospective) deferred → partial
**Gating dependency:** Second-eye gate (S1422), dispute escalation (S1423), cross-locale routing (S1429) — all shipped — plus ≥30 days of live review queue activity.

## Why this exists

S1430 closes the S1421-S1429 band. It cannot run until the queue exists. This doc pre-stages the retro template so the next session can fill numbers without re-designing the format.

## Today's surrogate

- `grimba_automation_runs` table captures ingestion-side QA outcomes but not editorial-review timing.

## Retro template

### Section 1 — Volume metrics
- Drafts submitted for review (total / per-locale / per-author)
- Approval rate, rejection rate, revision-cycle count
- Time-to-first-review (P50 / P95)
- Time-to-publish from submit (P50 / P95)

### Section 2 — Quality metrics
- Post-publish corrections issued (S2006 dependency)
- Reader complaints per published article
- Source right-of-reply requests received

### Section 3 — Process pain points
- Editor surveys: friction in submit / review / approve flow
- Bottleneck identification (Sankey: submit → review → publish)
- Time-of-day load distribution

### Section 4 — Decisions
- Keep / change / kill per rule
- Owner + due date per change

## Retro cadence

- Quarterly after first 90 days; annual thereafter.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1430)
- Sister docs: `docs/GRIMBANEWS_SECOND_EYE_APPROVAL_GATE_DESIGN.md`, `docs/GRIMBANEWS_INHOUSE_EDITOR_LAUNCH_RETRO_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
