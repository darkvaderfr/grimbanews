# GrimbaNews — Editorial Assignment System Design

**Status:** design v0 (no assignments table; ad-hoc assignment lives operator-side)
**Owner:** Lucy Leai (Strategy) + Liam Smith (PM) + Steve Jobs (CPO) on UX
**Walks:** Mythos S1318 (editorial assignment system) deferred → partial
**Gating dependency:** Editorial roster (per S1315 deferred) + in-house composing (S1311 per `docs/GRIMBANEWS_INHOUSE_SOURCE_EDITOR_SCOPE.md`) + editorial calendar (S1316 per `docs/GRIMBANEWS_EDITORIAL_CALENDAR_PLAN.md`). Schema design itself is operator-side.

## Why this exists

S1318 was honest-deferred: "no assignments table." That's true at the schema level but the **shape** of the assignment system is operator-side scope work that gates on it. This doc proposes the schema, lifecycle, and notification rules so the moment editorial roster ships, the assignment system is a straight implementation task.

## Today's state

- **No `editorial_assignments` table.**
- **Ad-hoc assignment** lives operator-side via direct messaging (Iboga ops channel).
- **Botble admin roles** exist generically (`Botble\ACL` package) — no per-editorial-section role.

## Proposed schema

```sql
CREATE TABLE editorial_assignments (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  calendar_item_id BIGINT NULL,         -- FK editorial_calendar_items
  post_id BIGINT NULL,                  -- FK posts (set on publish)
  assigned_to BIGINT NOT NULL,          -- FK members.id (editor doing the work)
  assigned_by BIGINT NOT NULL,          -- FK members.id (assigner)
  role ENUM('writer','reviewer','copyeditor','factchecker','translator') DEFAULT 'writer',
  due_at TIMESTAMP NULL,
  accepted_at TIMESTAMP NULL,
  declined_at TIMESTAMP NULL,
  decline_reason TEXT NULL,
  completed_at TIMESTAMP NULL,
  notes TEXT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX (assigned_to, due_at),
  INDEX (calendar_item_id),
  INDEX (post_id)
);
```

## Lifecycle

```
[assigned] → [accepted] → [in_progress] → [completed]
                ↓                              ↑
            [declined]                     (post.status = 'published')
                ↓
            [reassigned by lead editor]
```

- **assigned** — assignment created; assignee notified.
- **accepted** — assignee acks (target: within 24h for short-deadline assignments, 72h otherwise).
- **declined** — with optional reason; lead editor reassigns.
- **in_progress** — assignee actively drafting (linked to compose surface per `docs/GRIMBANEWS_INHOUSE_SOURCE_EDITOR_SCOPE.md`).
- **completed** — on `posts.status = 'published'` for writer role, or on review-pass for reviewer role.

## Notification cadence

- **On assignment** — email + in-platform notification.
- **24h before due_at** — reminder email if `completed_at IS NULL`.
- **On overdue** — email to assignee + lead editor; surfaced on cockpit board.
- Reuses `App\Mail` + `App\Support\GrimbaAutomationMonitor` ledger patterns.

## Role definitions

| Role | Responsibility | Typical SLA |
|---|---|---|
| writer | Drafts the piece | 24-72h depending on type |
| reviewer | First-read review (per S1317 workflow) | 24h |
| copyeditor | Line-edit + style-guide adherence | 24h |
| factchecker | Source-citation verification | 48h |
| translator | FR↔EN second-language version (if multilingual desk active) | 24h |

A single piece can carry multiple assignments (writer + reviewer + factchecker) — each row tracks its own lifecycle.

## Authority + access

- **Lead editor (editor-in-chief role per S1315)** — creates / reassigns / closes assignments.
- **Assignee** — accepts / declines / marks complete on own row.
- **Read all** — full editorial roster (per S1315).
- **Read own** — non-editorial admin staff cannot see editorial assignments.

## Reassignment rules

- **Decline** — auto-reassigned to next on-deck per round-robin within role.
- **Overdue 48h** — escalate to lead editor + suggest reassignment.
- **PTO declared (members.away_until)** — block new assignments to that member; alert lead editor on attempt.

## Reporting

- **Per-editor workload dashboard** — open assignments + due-date heatmap.
- **Per-section throughput** — assignments completed / week per `editorial_category`.
- **SLA hit rate** — % completed by due_at; lead-editor review.
- Lives at `/admin/grimba/editorial/assignments` (new admin surface).

## Integration with calendar (S1316)

- Each `editorial_calendar_items` row can carry **multiple** assignments (writer, reviewer, copyeditor).
- Calendar grid shows assignment status icons per item.
- "Schedule on calendar" in compose flow auto-creates the writer assignment for the composing editor.

## Integration with compose flow (S1311)

- `/admin/grimba/editorial/compose` reads "your open assignments" from `editorial_assignments WHERE assigned_to = current_user AND completed_at IS NULL` → operator picks one before composing.

## Audit + accountability

Per `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md` section 6 step 3, ombudsman has authority to review editorial process. Assignment audit trail (who assigned, who accepted, who completed) supports that review. `editorial_assignments` is **append-only after creation** (no UPDATE on assignment metadata; only state-transition columns mutate).

## Engineering effort estimate

- Schema + migration: 0.5 sprint.
- Notification cadence: 1 sprint.
- Lifecycle state machine: 1 sprint.
- Assignment dashboard: 2 sprints.
- Compose-flow integration: 1 sprint.
- Calendar-grid status icons: 1 sprint.
- Tests + audit-trail lock: 1 sprint.
- **Full ship: ~7-8 sprints, gates on editorial roster + S1311 + S1316.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1318; gates on S1311, S1315, S1316)
- Sister docs: `docs/GRIMBANEWS_INHOUSE_SOURCE_EDITOR_SCOPE.md`, `docs/GRIMBANEWS_EDITORIAL_CALENDAR_PLAN.md`, `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`
- Mail pattern: `app/Mail/GrimbaVaultDigestMail.php`
- Automation monitor: `app/Support/GrimbaAutomationMonitor.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
