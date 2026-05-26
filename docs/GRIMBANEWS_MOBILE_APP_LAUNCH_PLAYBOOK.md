# GrimbaNews — Mobile App Launch Playbook

**Status:** plan v0 (no app to launch yet; this is the operator-side checklist)
**Owner:** Steve Jobs (CPO) chairs launch + Liam Smith (PM) coordinates + Gary Vaynerchuk (CMO) on press + Lucy Leai (CEO) on PR sign-off + Ethan Wilson (Support) on day-0 desk
**Walks:** Mythos S1160 (App launch playbook) deferred → partial
**Gating dependency:** S1151-S1159 shipped (shell + push + analytics + crash + review channel)

## Why this exists

S1160 is the consolidated runbook for the first store submission and rollout. Without it, "going live" creates blind spots — analytics not verified, crash dashboard empty, support team unprepared, press unsynchronized.

## Today's surrogate

- **Web go-live runbook** — `docs/GRIMBANEWS_GO_LIVE_RUNBOOK.md` (cinematic launch checklist for web).
- Adapt the pattern; mobile-specific gates added below.

## Phase 0 — pre-flight (T-4 weeks)

| # | Gate | Owner | Evidence |
|---|---|---|---|
| 0.1 | Shell + native deep-link verified | Nina Patel | TestFlight + Internal Testing build live |
| 0.2 | Push delivery end-to-end | Jacob Lee | one push delivered to TestFlight device |
| 0.3 | Crash reporting catches induced crash | Hannah Kim | Sentry events visible |
| 0.4 | Analytics events arrive | David Chen | Amplitude shows app_open from TestFlight |
| 0.5 | Store listing copy + screenshots approved | Alex Morgan + Steve Jobs | PR approval in Slack #grimba-launch |

## Phase 1 — store submission (T-2 weeks)

- iOS: submit for review via App Store Connect — typical 24-72h.
- Android: submit Internal → Open → Production track.
- Set release date for synchronized launch.
- Press embargo set with Gary's PR list.

## Phase 2 — soft launch (T+0)

- Available in **France only** for first 7 days.
- Monitor: crash rate <0.5%, push opt-in >40%, app-open / session ≥1.3.
- Daily standup: Steve + Liam + Hannah on metrics.

## Phase 3 — broad rollout (T+7)

- Expand to EU + LATAM + ANZ.
- Press release goes live (Gary + Henry Walker write the copy).
- In-app banner on web prompts mobile install.

## Phase 4 — retrospective (T+30)

- Per-event funnel review.
- Crash signature dossier.
- Reader feedback themes.
- Decision: marquee feature for v1.1.

## Rollback plan

- If crash rate >2% in first 24h: pull production listing (Apple), halt rollout (Google).
- Hotfix CI path documented in `docs/GRIMBANEWS_NATIVE_RELEASE_PIPELINE_PLAN.md`.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1160)
- Sister docs: `docs/GRIMBANEWS_GO_LIVE_RUNBOOK.md`, `docs/GRIMBANEWS_MOBILE_APP_SHELL_PICK.md`, `docs/GRIMBANEWS_MOBILE_PUSH_INFRA_SCOPE.md`, `docs/GRIMBANEWS_MOBILE_APP_STORE_REVIEW_CHANNEL.md`, `docs/GRIMBANEWS_APP_STORE_OPTIMIZATION_PLAN.md`, `docs/GRIMBANEWS_NATIVE_RELEASE_PIPELINE_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
