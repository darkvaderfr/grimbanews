# GrimbaNews — Incident Escalation Tiers

**Status:** policy v0 (named tiers, no paging vendor wired)
**Owner:** Sara Chen (CISO) + Jacob Lee (DevOps)
**Walks:** Mythos S1016 (escalation tiers) deferred → partial
**Gating dependency:** PagerDuty / Better Stack account not yet provisioned. This document defines the **severity ladder + ack windows + named tiers** that the vendor will be configured against.

## Why this exists

S1016 was honest-deferred because escalation needs vendor wiring. But the **ladder itself** (severity definitions, response windows, named human escalation tiers) is policy, not vendor config. This doc captures the policy v0 so the moment the vendor ships the configuration is import-not-debate.

## Severity ladder

| Severity | Definition | Ack target | Resolution target | Pager tier |
|---|---|---|---|---|
| **P0** | Site down for >5 min, data loss, confirmed breach, full ingest stall, scheduler entirely offline | 5 min | 1 hour | Primary + Secondary + Tertiary (CISO) simultaneously |
| **P1** | Single critical surface down (e.g. /health 500, RSS feed broken, NobuAI provider chain exhausted), partial ingest stall (>50% sources failing), translation pipeline stuck | 15 min | 4 hours | Primary, Secondary if no ack in 15 min |
| **P2** | Single non-critical feature broken (For-You ranking off, sitemap stale, admin-only surface broken), single scheduled job failing | 2 hours (business hours) | 24 hours | Primary, business hours only |
| **P3** | Cosmetic, accessibility regression on non-critical surface, copy typo, slow query needing tuning | Next business day | Sprint queue | Issue tracker, no page |

## Triggers (existing internal signals)

| Trigger | Maps to | Severity |
|---|---|---|
| `/health` JSON returns `status:err` | S1011 surrogate | P0 |
| `/up` returns non-200 for >2 min | reader site down | P0 |
| `grimba:health --fail-on-risk` exit code ≠ 0, hourly (`routes/console.php:173-176`) | freshness / coverage SLO breach | P1 |
| `grimba_automation_runs` row `status='failed'` for `backup_verify` | backup failed | P0 |
| `grimba_automation_runs` row `status='failed'` for `rss_poll_main` | ingest down | P1 |
| `grimba_automation_runs` row `status='stale'` for `nobuai_summaries` >2h | NobuAI summaries stale | P2 |
| Sentry (when wired per S1013) error rate > baseline + 3σ over 15 min | unhandled exceptions surge | P1 |

## Named escalation tiers (real Iboga roster)

**Tier 1 — Primary on-call:** Jacob Lee (DevOps) → Hannah Kim (Platform) alternating weekly per `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md`.

**Tier 2 — Secondary on-call:** Alternate of Tier 1 same week. Auto-paged 15 min after Tier 1 if no ack.

**Tier 3 — Discipline owners (P0 only):**
- DB-class P0 → Larry Ellison (VP DBA)
- Security-class P0 (suspected breach, auth failure, CSRF storm) → Sara Chen (CISO)
- Content-class P0 (publish surface broken, misinformation event) → Lucy Leai (Strategy) + Steve Jobs (CPO)

**Tier 4 — Founder escalation (P0 unresolved >1h):** Zenkai (founder-ops signoff).

(Names from `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`.)

## Per-severity comms

| Severity | Internal channel | Public channel | Stakeholder email |
|---|---|---|---|
| P0 | Iboga ops + Tier 3 + Tier 4 | Status page incident (per `docs/GRIMBANEWS_STATUS_PAGE_PLAN.md`) + X post if >30 min | Iboga exec roster |
| P1 | Iboga ops + Tier 3 | Status page degraded if >15 min | Internal only |
| P2 | Iboga ops | None | Internal only |
| P3 | Issue tracker | None | None |

Template copy lives at `docs/GRIMBANEWS_INCIDENT_COMMS_TEMPLATES.md`.

## Activation checklist (day-1 when vendor ships)

1. Import the ladder into the paging-vendor severity model.
2. Map each trigger above to a vendor service/component.
3. Wire the on-call roster (`docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md`) as schedule layers.
4. Test each severity with a synthetic event.
5. Add `Ladder reference → docs/GRIMBANEWS_ESCALATION_TIERS.md` to the incident response runbook (S1805 / `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`).

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1016 row)
- Sister docs: `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md`, `docs/GRIMBANEWS_INCIDENT_COMMS_TEMPLATES.md`, `docs/GRIMBANEWS_STATUS_PAGE_PLAN.md`, `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
- Internal signals: `app/Support/GrimbaAutomationMonitor.php`, `app/Console/Commands/GrimbaHealth.php`, `routes/console.php:173-176`
