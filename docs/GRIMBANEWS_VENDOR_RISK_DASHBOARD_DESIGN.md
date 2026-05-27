# GrimbaNews — Vendor Risk Dashboard Design (Per-Source Editorial-Board Sibling)

**Sprint ID:** S1879
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1871-s1880 — Vendor risk dashboard`
**Walk wave:** BBBB

## Gating dependency

Vendor risk dashboard needs:

- Full vendor inventory (S1871, deferred)
- Risk tier per vendor (S1872, deferred)
- DPA collection (S1873, deferred — sibling walk)
- Security-questionnaire intake (S1874, deferred)
- SOC 2 / ISO 27001 report collection (S1875, deferred)
- Quarterly review cadence (S1878, deferred)
- Surfacing route `/admin/grimba/vendor-risk` (super-admin only)

## Surrogate-now infra

- **Operator-maintained spreadsheet (out-of-repo)** — current de-facto risk dashboard
- **`feedback_hosting_policy.md`** — anchors the major vendor choices (Stellar hosting, VPS) and the operator-side risk reasoning
- **Per-source editorial-board sibling** — `news_sources` curated metadata already captures editorial-board tier as a proxy for source-trust (different domain but same pattern)

## Honest framing

The dashboard is the UI for vendor-risk data that does not exist as structured data yet. Until S1871 ships, the dashboard would render an empty state.

## Owners

- **CISO:** Sara Chen — dashboard scope + risk-tier policy
- **Compliance:** Maya Patel — questionnaire + report-collection process
- **Backend:** Rajesh Kumar — dashboard endpoint
- **Frontend:** Nina Patel — dashboard layout
- **DBA:** Larry Ellison — vendor + risk schema
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1879 row)
- DPA collection: `docs/GRIMBANEWS_VENDOR_DPA_COLLECTION_PLAN.md`
- Vendor band anchor: `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1871-s1880`
- Hosting policy: `~/.claude/projects/-Users-vb-kaizen/memory/feedback_hosting_policy.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
