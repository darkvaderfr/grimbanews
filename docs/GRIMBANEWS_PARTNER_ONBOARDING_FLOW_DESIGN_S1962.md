# GrimbaNews — Partner Onboarding Flow Design

**Sprint ID:** S1962
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1961-s1970 — Partner onboarding flow`
**Walk wave:** BBBB

## Gating dependency

Partner onboarding flow needs:

- Program tier design (S1961, deferred)
- Intake form (company / use-case / tier choice / contact)
- Contract acceptance step (counsel-defined)
- API key issuance (S1965, deferred)
- Welcome-email automation
- Per-partner onboarding-status ledger
- Tier-appropriate doc handoff (existing `docs/GRIMBANEWS_PARTNER_DOCS_PLAN.md`)

## Surrogate-now infra

- **`/contact` form + manual ops** — interested partners email today; ops onboards them manually with `/feed.xml`
- **`docs/GRIMBANEWS_PARTNER_DOCS_PLAN.md`** — existing doc package
- **`docs/GRIMBANEWS_PARTNER_OPS_PLAYBOOK.md`** — existing operator-side runbook

## Honest framing

Onboarding flow is a wrapper around the program design. Manual ops handles single-digit partners today. Self-serve onboarding gates on tier design + portal + OAuth.

## Owners

- **Biz Dev:** Victor Garcia + Chris Johnson — pipeline + manual onboard
- **Product:** Liam Smith — self-serve flow scope
- **Backend:** Rajesh Kumar — intake schema + key issuance
- **CSM:** Emma Brown — welcome cadence
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1962 row)
- Tier design: `docs/GRIMBANEWS_PARTNER_PROGRAM_TIER_DESIGN_S1961.md`
- Portal: `docs/GRIMBANEWS_PARTNER_PORTAL_DESIGN_S1963.md`
- Existing partner docs: `docs/GRIMBANEWS_PARTNER_DOCS_PLAN.md`
- Existing ops playbook: `docs/GRIMBANEWS_PARTNER_OPS_PLAYBOOK.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
