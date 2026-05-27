# GrimbaNews — Institutional License Renewal Cadence Design

**Sprint ID:** S1988
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1981-s1990 — Institutional license renewal cadence`
**Walk wave:** BBBB

## Gating dependency

Renewal cadence needs:

- ≥1 signed institutional customer (S1981, deferred)
- Renewal-reminder email automation (N-90d, N-30d, N-7d)
- Renegotiation workflow (price changes, seat changes, scope changes)
- Termination workflow
- Per-institution renewal status ledger
- CSM motion (Emma Brown owns)

## Surrogate-now infra

- **Manual ops calendaring** — single-digit customers tracked via ops calendar
- **`docs/GRIMBANEWS_PARTNER_OPS_PLAYBOOK.md`** — existing playbook pattern for ops cadence

## Honest framing

Renewal cadence is a CSM motion. Code lift is reminder automation; the policy is operator-side.

## Owners

- **CSM:** Emma Brown — renewal motion ownership
- **CFO:** Ray Dalio + Warren Buffett — price-change discipline
- **Backend:** Rajesh Kumar — reminder cron
- **Product:** Liam Smith — renewal UX
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1988 row)
- Tier design: `docs/GRIMBANEWS_INSTITUTIONAL_LICENSE_TIER_DESIGN.md`
- Existing partner ops playbook: `docs/GRIMBANEWS_PARTNER_OPS_PLAYBOOK.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
