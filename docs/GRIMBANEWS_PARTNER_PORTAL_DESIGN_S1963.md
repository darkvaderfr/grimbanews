# GrimbaNews — Partner Portal Design

**Sprint ID:** S1963
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1961-s1970 — Partner portal`
**Walk wave:** BBBB

## Gating dependency

A partner portal needs:

- Program tier + onboarding live (S1961-S1962)
- Per-partner login (extends Botble member auth with partner role)
- Portal surfaces: API key management, usage dashboard, billing, contract status, support tickets, changelog feed, sandbox
- Existing sub-plans for many surfaces are already partial (sandbox plan, changelog plan, etc.)

## Surrogate-now infra

- **`docs/GRIMBANEWS_B2B_API_PARTNER_SANDBOX_PLAN.md`** — sandbox surface scoped
- **`docs/GRIMBANEWS_B2B_API_PARTNER_CHANGELOG_PLAN.md`** — changelog surface scoped
- **`docs/GRIMBANEWS_PARTNER_DOCS_PLAN.md`** — doc surface scoped
- **Botble admin** — existing single-role admin could be extended with `partner` role guard

## Honest framing

Portal is the assembly point for many already-scoped per-surface docs. Code lift is the auth role-split (Larry-DBA-scoped) + the per-partner data scoping middleware.

## Owners

- **Backend:** Rajesh Kumar — portal scaffolding + role guard
- **DBA:** Larry Ellison — role-split migration
- **Frontend:** Nina Patel — portal layout
- **Product:** Liam Smith — portal IA
- **CISO:** Sara Chen — partner-role security model
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1963 row)
- Tier design: `docs/GRIMBANEWS_PARTNER_PROGRAM_TIER_DESIGN_S1961.md`
- Onboarding: `docs/GRIMBANEWS_PARTNER_ONBOARDING_FLOW_DESIGN_S1962.md`
- Analytics: `docs/GRIMBANEWS_PARTNER_ANALYTICS_DASHBOARD_DESIGN_S1967.md`
- Sandbox: `docs/GRIMBANEWS_B2B_API_PARTNER_SANDBOX_PLAN.md`
- Changelog: `docs/GRIMBANEWS_B2B_API_PARTNER_CHANGELOG_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
