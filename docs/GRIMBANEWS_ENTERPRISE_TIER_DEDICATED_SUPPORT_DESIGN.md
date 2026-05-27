# GrimbaNews — Enterprise Tier Dedicated Support Design (CSM, Response-Time Tiers)

**Sprint ID:** S1993
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1991-s2000 — Enterprise tier dedicated-support tier (CSM, response-time tiers)`
**Walk wave:** BBBB

## Gating dependency

Dedicated support tier needs:

- Tier feature design shipped (S1991, deferred)
- Per-tier response-time SLA matrix (P0 / P1 / P2 / P3 with hour targets)
- A ticketing system (Zendesk / Freshdesk / built-in) — none today
- Per-customer CSM assignment ledger
- Escalation runbooks
- Sufficient staffing (depends on enterprise pipeline)

## Surrogate-now infra

- **`/contact` + Ethan Wilson (Support)** — all tickets today funnel through this single channel
- **`docs/GRIMBANEWS_PARTNER_OPS_PLAYBOOK.md`** — current ops playbook
- **Emma Brown (CSM lead)** — exists in roster, can absorb single-digit enterprise customers today

## Honest framing

Dedicated support is a staffing decision dressed up as a feature. Code lift is minimal once a ticketing system is picked. The real gate is "do we have enterprise customers to support."

## Owners

- **CSM:** Emma Brown — motion ownership
- **Support:** Ethan Wilson — escalation runbooks
- **Product:** Liam Smith — SLA matrix
- **CFO:** Ray Dalio — cost-of-support economics
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1993 row)
- Tier feature design: `docs/GRIMBANEWS_ENTERPRISE_TIER_FEATURE_DESIGN.md`
- CSM motion: `docs/GRIMBANEWS_ENTERPRISE_TIER_CSM_MOTION_DESIGN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
