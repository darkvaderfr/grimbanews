# GrimbaNews — Institutional License Tier Design (Per-Seat vs Site-Wide)

**Sprint ID:** S1981
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1981-s1990 — Institutional license tier design (per-seat vs site-wide)`
**Walk wave:** BBBB

## Gating dependency

Institutional license tier needs:

- Tier definition: per-seat vs site-wide vs IP-range (Lucy + Ray decision)
- SSO integration (S1983, deferred — no SAML / Shibboleth / OIDC today)
- IP-allowlist provisioning (S1984, deferred)
- Per-institution analytics (S1985, deferred)
- Invoicing infra (S1987, deferred — gates on monetization)
- Renewal cadence (S1988, deferred)
- Contract template (S1982, deferred — operator-side counsel)

## Surrogate-now infra

- **`/contact` form + manual invoice** — universities or NGOs can request access today via ops; ops invoices manually
- **Botble member-auth** — basic single-tenant auth could carry institutional members today; SSO is the upgrade

## Honest framing

Institutional license is a B2B sales motion more than a code motion. Code lift (SSO + IP-allowlist) is meaningful but unlocked late in the monetization arc.

## Owners

- **CEO:** Lucy Leai — tier scope
- **CFO:** Ray Dalio + Warren Buffett — pricing
- **CISO:** Sara Chen — SSO security model
- **Backend:** Rajesh Kumar — auth integration
- **Biz Dev:** Victor Garcia + James Williams — pipeline
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1981 row)
- Per-institution analytics: `docs/GRIMBANEWS_INSTITUTIONAL_LICENSE_ANALYTICS_DESIGN.md`
- Renewal cadence: `docs/GRIMBANEWS_INSTITUTIONAL_LICENSE_RENEWAL_DESIGN.md`
- Enterprise tier sibling: `docs/GRIMBANEWS_ENTERPRISE_TIER_FEATURE_DESIGN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
