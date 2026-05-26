# GrimbaNews — Zero-Result Search Tracking Plan

**Status:** plan v0 (no zero-result alert; queries that return nothing vanish silently)
**Owner:** Liam Smith (PM) on alerting rules + Benjamin Lee on aggregation + Lucy Leai on editorial response
**Walks:** Mythos S1493 (Zero-result-search tracking) deferred → partial
**Gating dependency:** Search event logging (S1491) shipped.

## Why this exists

S1493 surfaces queries returning zero results — these are pure editorial signal. Either the topic isn't covered (coverage gap) or the query is mistyped / non-news (intent mismatch). The first case is actionable.

## Today's surrogate

- None. Zero-result queries are invisible to the operator.

## Detection (target)

- `search_events.zero_result` is a generated column (per S1491 schema).
- Nightly Artisan: `grimba:zero-result-digest` rolls up zero-result queries from previous 24h.

## Daily digest email

**To:** editorial inbox
**Subject:** "Zero-result queries — {date} — {count} queries / {searches} searches"

**Body example:**
```
Top 20 zero-result queries (last 24h):

  Query                          Searches    Locale
  algorithme tiktok                  47        fr
  haiti electricity                  31        en
  jeux olympiques 2032               28        fr
  malta gambling                     14        en
  ...

Action: assign editorial pickup or note as out-of-scope.
```

## Editorial response loop

- Each digest row links to "Create draft from this query" — pre-fills draft title.
- Operator can mark `out_of_scope` (e.g., "tiktok algorithm" is product news, off-mission) → query is whitelisted from future digests for 90 days.

## Alert thresholds

- Spike alert: zero-result query count for a single query > 3× the 7-day average → flag in cockpit board (`GrimbaAutomationMonitor`).
- Catastrophic alert: total zero-result rate > 20% of all searches → pager (operator-side, deferred).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1493)
- Sister docs: `docs/GRIMBANEWS_SEARCH_EVENT_LOGGING_SCHEMA.md`, `docs/GRIMBANEWS_TOP_SEARCHES_DASHBOARD_SCOPE.md`
- Existing infra: `app/Services/GrimbaAutomationMonitor.php`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
