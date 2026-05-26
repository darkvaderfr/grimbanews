# GrimbaNews — API Launch Playbook

**Status:** plan v0 (no API to launch; gates on S1181-S1189 shipping)
**Owner:** Liam Smith (PM) chairs + Victor Garcia on partner pipeline + Henry Walker / Olivia Davis on press + Michael O'Connor on docs site freshness
**Walks:** Mythos S1190 (API launch playbook) deferred → partial
**Gating dependency:** All of S1181-S1189 (design, OAuth, revocation, sandbox, docs, ops playbook, analytics, SLA)

## Why this exists

S1190 is the GTM moment for the API. Without a coordinated launch, the API sits documented + invisible — same as the RSS feeds today (technically available but not marketed).

## Today's surrogate

- **Web go-live runbook** — `docs/GRIMBANEWS_GO_LIVE_RUNBOOK.md` pattern carries.
- **Partnership program launch playbook** — `docs/GRIMBANEWS_PARTNERSHIP_PROGRAM_LAUNCH_PLAYBOOK.md` (Wave LLL).

## Phase 0 — pre-launch (T-6 weeks)

| # | Gate | Owner |
|---|---|---|
| 0.1 | All API v2 endpoints stable in staging | Rajesh Kumar |
| 0.2 | OpenAPI spec generated + reviewed | Michael O'Connor |
| 0.3 | Sandbox env stable + seeded | Jacob Lee |
| 0.4 | Docs site published | Michael O'Connor |
| 0.5 | First 3 friendly-design-partner integrations green | Victor Garcia |
| 0.6 | SLA reviewed by counsel | Lucy Leai |
| 0.7 | Status page wired | Hannah Kim |

## Phase 1 — soft launch (T-2 weeks → T+0)

- Open API access to waiting list (~50 academic + 10 commercial leads).
- Per-day metric review: usage, errors, latency.
- First commercial contract close in this window.

## Phase 2 — public launch (T+0)

- Press release (Gary Vaynerchuk + Henry Walker).
- Blog post: "Introducing the GrimbaNews API" — Henry Walker writes.
- Hacker News + Reddit /r/dataisbeautiful + /r/journalism share.
- Twitter/X thread (Lucy Leai).
- Mailing list announcement.
- Docs site rotates "What's new" banner.

## Phase 3 — first month observe (T+0 → T+30)

- Daily standup: usage, errors, latency, partner support tickets.
- Weekly partner pipeline review.
- Per-incident SLA credit calculation.

## Phase 4 — first quarter retrospective (T+90)

- Per-partner case study (Victor Garcia).
- Aggregate usage report (David Chen).
- Decision: v2.1 feature roadmap.

## Rollback / mitigation

- If error rate >5%: pause new key issuance, root-cause, fix forward.
- If SLA breached: credit-back automatic per `GRIMBANEWS_API_SLA_DESIGN.md`.
- If single partner causes outage (abuse): rate-limit aggressively + Slack #grimba-api-incidents.

## Success metrics

- 30+ API keys issued in first 30 days.
- 3+ commercial contracts signed in first 90 days.
- p95 latency budget held.
- 99.5%+ uptime.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1190)
- Sister docs: `docs/GRIMBANEWS_PUBLIC_API_V2_DESIGN.md`, `docs/GRIMBANEWS_GO_LIVE_RUNBOOK.md`, `docs/GRIMBANEWS_PARTNERSHIP_PROGRAM_LAUNCH_PLAYBOOK.md`, `docs/GRIMBANEWS_PARTNER_OPS_PLAYBOOK.md`, `docs/GRIMBANEWS_API_SLA_DESIGN.md`, `docs/GRIMBANEWS_API_PARTNER_ANALYTICS_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
