# GrimbaNews — Editorial Calendar Plan

**Status:** plan v0 (operator-side calendar lives outside platform today)
**Owner:** Lucy Leai (Strategy) + Steve Jobs (CPO) on UX + Liam Smith (PM) on workflow integration
**Walks:** Mythos S1316 (editorial calendar) deferred → partial
**Gating dependency:** In-house editorial workflow (S1311 per `docs/GRIMBANEWS_INHOUSE_SOURCE_EDITOR_SCOPE.md`). Until in-house posts ship, the calendar tracks operator-side outside the platform. This doc defines the **in-platform calendar shape** that activates the day in-house composing ships.

## Why this exists

S1316 was honest-deferred: the editorial calendar lives in **operator Notion / shared doc** today because there is no in-house editorial pipeline (S1311). The deferral note pointed at "no in-platform calendar." This document defines the in-platform calendar surface so the moment in-house composing ships the calendar is a straight engineering task.

## Today's surrogate

- **Operator-side Notion / shared doc** (Lucy Leai-maintained, not in repo).
- **News-source weekly cadence** lives in source classifier metadata (`news_sources.publishing_cadence` slot is implicit — operator knows which RFI vs Le Monde cycles).
- **Per-region editorial pivots** documented in `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md`.

No in-platform calendar exists.

## Proposed in-platform calendar

**Route:** `/admin/grimba/editorial/calendar` (new admin surface).

**View modes:**

1. **Month** — default. Grid view, one card per planned editorial item.
2. **Week** — rolling 7-day view with day-of-week columns.
3. **Per-section** — filter by editorial section (Climate, Politics, Tech, Culture, etc. per `App\Services\GrimbaCategoryClassifier::CATEGORIES`).
4. **Per-editor** — filter by author (gates on S1315 editorial roles).
5. **Per-region** — filter by `editorial_region` ∈ {africa, international, dom-tom}.

**Item types:**

| Type | Source | Status flow |
|---|---|---|
| Planned in-house piece | `editorial_calendar_items` (new) | planned → assigned → drafting → in-review → published |
| Recurring digest | `newsletter_schedule` (new) | scheduled (cron-driven) |
| Aggregated event-coverage plan | `editorial_calendar_items` flagged `coverage_plan=true` | planned → live → wrapped |
| External event marker | `editorial_calendar_items` flagged `external_event=true` | (informational only — e.g., "G20 summit", "COP30") |

**Coverage-plan example:**

> Event: "EU Parliament vote on AI Act amendment — 2026-06-15"  
> Coverage plan:
> - Pre-vote analysis (assigned: Editor A, draft due 2026-06-12)
> - Live coverage rail (auto-curated via cluster engine; check daily)
> - Post-vote explainer (assigned: Editor B, draft due 2026-06-16)
> - Newsletter angle (in next Friday's vault-digest)

This shape gives editors a single timeline view + lets us tag aggregated articles back to a coverage plan.

## Schema (new)

```sql
CREATE TABLE editorial_calendar_items (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  title VARCHAR(255) NOT NULL,
  planned_date DATE NOT NULL,
  status ENUM('planned','assigned','drafting','in_review','published','dropped') DEFAULT 'planned',
  type ENUM('inhouse','digest','coverage_plan','external_event') DEFAULT 'inhouse',
  editorial_region VARCHAR(32) NULL,
  editorial_category VARCHAR(64) NULL,
  assigned_to BIGINT NULL,           -- members.id
  post_id BIGINT NULL,               -- posts.id once published
  parent_coverage_plan_id BIGINT NULL,
  notes TEXT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX (planned_date),
  INDEX (status),
  INDEX (editorial_region, editorial_category)
);
```

## Integrations

- **Compose flow** — "Schedule on calendar" button in `/admin/grimba/editorial/compose` (per `docs/GRIMBANEWS_INHOUSE_SOURCE_EDITOR_SCOPE.md`) creates / links to a calendar item.
- **Cluster engine** — when a cluster's primary post matches a coverage-plan event keyword, surface a "Link to coverage plan?" suggestion in `/admin/grimba/story-clusters`.
- **Newsletter pipeline** — `App\Console\Commands\GrimbaSavedSearchDigests` reads upcoming digest items from `editorial_calendar_items` where `type='digest'`.
- **Assignment workflow (S1318 dependency)** — calendar item `assigned_to` populates assignment notifications.

## Authority + access

- **Read:** all editorial roles (per S1315 deferred).
- **Edit:** lead editor (editor-in-chief role per S1315).
- **Create item:** any editorial role.
- **Public surface:** none today. Calendar is operator-only. (Future: "Coming up" reader teaser per S1297 re-engagement dependency.)

## Reminder cadence

- **Daily 09:00 UTC** — calendar email to all editorial roles: today's deadlines + tomorrow's drafts.
- **Weekly Monday 08:00 UTC** — week-ahead summary.
- Reuses existing mail infrastructure (`App\Mail\GrimbaVaultDigestMail` pattern).

## Engineering effort estimate

- Schema + migration: 0.5 sprint.
- Calendar grid UI (month / week): 3 sprints.
- Per-filter views: 1 sprint.
- Compose-flow link: 0.5 sprint.
- Cluster-engine "link to plan" suggestion: 1 sprint.
- Reminder emails: 1 sprint.
- Tests + a11y pass: 1 sprint.
- **Full ship: ~8 sprints, gates on in-house editor (S1311).**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1316; gates on S1311+, links to S1318 + S1320)
- Sister doc: `docs/GRIMBANEWS_INHOUSE_SOURCE_EDITOR_SCOPE.md`
- Sister doc: `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md`
- Existing news ledger: `posts` table + `App\Console\Commands\GrimbaClusterStories.php`
- Mail pattern: `app/Mail/GrimbaVaultDigestMail.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
