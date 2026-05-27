# GrimbaNews — API Ops Playbook Surrogate Plan

**Sprint ID:** S1260
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API ops playbook`
**Walk wave:** CCCC

## Gating dependency

A B2B API operations playbook (runbook + escalation + comms) needs S1231-S1259 to ship — the playbook documents the live system, and the live system does not exist.

## Surrogate-now infra

- **`docs/GRIMBANEWS_API_STATUS_INCIDENT_COMMS_PLAN.md`** — status-page incident comms shape is already specced
- **`docs/GRIMBANEWS_API_LAUNCH_PLAYBOOK.md`** — launch-day runbook stub
- **`docs/GRIMBANEWS_API_STATUS_PAGE_DESIGN.md`** — status-page shape
- **`grimba:health --fail-on-risk`** — internal cron that already lands ops-style failures into the cockpit board (precedent for ops monitoring)
- **`docs/GRIMBANEWS_API_SLA_DESIGN.md`** — SLA model

## Honest framing

The *frame* of the playbook exists (status page + incident comms + SLA + launch); the *content* depends on what the API actually does and how it actually fails. Cannot write a real runbook for a non-existent system.

## Owners

- **DevOps:** Jacob Lee — on-call rotation + paging
- **Platform:** Hannah Kim — SLO/SLI definitions
- **Customer success:** Emma Brown — customer comms playbook
- **Docs:** Michael O'Connor — runbook authorship
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1260 row)
- API status incident comms: `docs/GRIMBANEWS_API_STATUS_INCIDENT_COMMS_PLAN.md`
- API SLA: `docs/GRIMBANEWS_API_SLA_DESIGN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
