# GrimbaNews — Vendor DPA Collection Plan (Per-Source Ownership Audit Sibling)

**Sprint ID:** S1873
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1871-s1880 — Vendor DPA collection`
**Walk wave:** BBBB

## Gating dependency

Vendor Data-Processing Agreement (DPA) collection needs:

- Vendor inventory (S1871, deferred)
- Per-vendor risk tier (S1872, deferred)
- DPA template requesting EU-Model-Clauses / SCC + Schrems-II addendum where applicable
- Per-vendor signed-DPA storage (encrypted at rest, indexed)
- Per-vendor renewal cadence
- DPO designation (S1857, deferred — likely operator decision under GDPR threshold)

## Surrogate-now infra

- **Manual vendor list maintained out-of-repo** — Sara-Chen-CISO maintains operator-side
- **`/.env` declaration** — current de-facto vendor list (anything with a key is a vendor)
- **`docs/GRIMBANEWS_LEGAL_CONTRACT_LIBRARY_PLAN.md`** — sibling legal-ops doc (if shipped)
- **Per-source ownership-history audit** — covered by the upstream `news_sources.ownership_type` enum + curated metadata (S2046, deferred but with code-level seed in place)

## Honest framing

DPA collection is legal-ops infra. Counsel-side pickup. Until DPO is named, this is operator-shadow work.

## Owners

- **CISO:** Sara Chen — vendor inventory + DPA chase
- **Compliance:** Maya Patel — DPA template + storage
- **Counsel (operator-side):** template review
- **DBA:** Larry Ellison — encrypted DPA storage
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1873 row)
- Vendor risk dashboard: `docs/GRIMBANEWS_VENDOR_RISK_DASHBOARD_DESIGN.md`
- GDPR DPIA / DPO band: `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1851-s1860`
- Per-source ownership audit anchor: `news_sources.ownership_type` enum + S2046 row
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
