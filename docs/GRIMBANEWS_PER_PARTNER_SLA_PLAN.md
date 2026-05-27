# GrimbaNews — Per-Partner SLA Surrogate Plan

**Sprint ID:** S1327
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1321-s1330 — Per-partner SLA`
**Walk wave:** CCCC

## Gating dependency

A per-partner SLA needs:

- A real partner roster (zero contracts today)
- The B2B API ops playbook (S1260)
- Per-partner monitoring + alerting
- Counsel-reviewed SLA template (per-tier: free no-SLA, pro 99.5%, enterprise 99.9%)
- Service-credit policy

## Surrogate-now infra

- **`docs/GRIMBANEWS_API_SLA_DESIGN.md`** — internal SLA scope doc
- **`grimba:health --fail-on-risk`** — internal monitoring pattern that powers SLA reporting
- **`docs/GRIMBANEWS_API_STATUS_PAGE_DESIGN.md`** — status-page that powers SLA visibility
- **`docs/GRIMBANEWS_PER_PARTNER_ANALYTICS_SCOPE.md`** — per-partner analytics scope

## Honest framing

Operator-side contract pickup. The engineering (uptime tracking, error-rate roll-ups, incident attribution) is solvable in 1-2 weeks once we know the contract. Cannot ship before a real partner signs.

## Owners

- **Business Dev:** Victor Garcia — contract template
- **Legal:** TBD counsel — per-jurisdiction template review
- **DevOps:** Jacob Lee — uptime tracking
- **Platform:** Hannah Kim — SLO/SLI per partner
- **Customer success:** Emma Brown — credit-issuance playbook
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1327 row)
- API SLA: `docs/GRIMBANEWS_API_SLA_DESIGN.md`
- Per-partner analytics: `docs/GRIMBANEWS_PER_PARTNER_ANALYTICS_SCOPE.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
