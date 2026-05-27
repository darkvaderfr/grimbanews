# GrimbaNews — Syndication Agreement Template Surrogate Plan

**Sprint ID:** S1441
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1401_S1600_EDITORIAL_SEARCH_PERSONALIZATION_EVIDENCE.md#s1441-s1450 — Syndication agreement template`
**Walk wave:** CCCC

## Gating dependency

A syndication agreement template needs:

- Counsel-reviewed boilerplate (per-jurisdiction: FR / EU / US / CA)
- Per-partner negotiable clauses (territory, exclusivity, term, royalty, takedown, indemnity)
- Tax/VAT clauses (gates on S1269)
- A signed-on-file storage workflow
- A signed-version reference in the partner-row of any future `partners` table

## Surrogate-now infra

- **`/legal/dpa`** — DPA template pattern already shipped
- **`/legal/terms`** — terms template pattern
- **`docs/GRIMBANEWS_PER_REGION_PARTNER_EXCLUSIVITY_TERMS.md`** — adjacent partner exclusivity scope
- **`docs/GRIMBANEWS_PER_REGION_PARTNER_TAKEDOWN_REQUEST_WORKFLOW.md`** — adjacent takedown workflow

## Honest framing

Operator-side legal pickup. The engineering work (template storage + signed-version tracking) is trivial; the *content* (per-jurisdiction enforceable language) requires counsel.

## Owners

- **Legal:** TBD counsel — per-jurisdiction template authoring
- **Business Dev:** Victor Garcia — negotiable-clauses framework
- **Strategy:** Lucy Leai — territory + exclusivity defaults
- **Backend:** Rajesh Kumar — template + signed-version storage
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1441 row)
- Partner exclusivity: `docs/GRIMBANEWS_PER_REGION_PARTNER_EXCLUSIVITY_TERMS.md`
- Partner takedown: `docs/GRIMBANEWS_PER_REGION_PARTNER_TAKEDOWN_REQUEST_WORKFLOW.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
