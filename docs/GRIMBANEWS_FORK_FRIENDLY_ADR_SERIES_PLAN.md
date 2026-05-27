# GrimbaNews — Fork-Friendly Architecture Decision Records (ADRs) Plan

**Sprint ID:** S2095
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2081-s2100 — Community — fork-friendly architecture decision records (ADRs)`
**Walk wave:** BBBB

## Gating dependency

A public-facing ADR series needs:

- An `adr/` folder convention (typical `adr/0001-cluster-engine-rule-based.md`)
- A template (context / decision / consequences / status)
- Editorial review of every existing decision worth retroactively documenting
- An ADR index page
- License-clear publication (operator-side counsel pickup)
- An OSS org to publish into (S2043, deferred)

## Surrogate-now infra

- **`docs/` folder** — every Mythos sprint already produces decision-documenting markdown; ~400+ files
- **Git log** — every architectural decision (rule-based clustering, LLM-detector heuristic, cluster-engine confidence-score formulation) is implicit in commit history
- **`/methodologie`** — public methodology page already discloses the high-level decisions to readers

## Honest framing

The decision history exists; it just is not labeled as ADRs. Lifting it into a public ADR series is a documentation refactor + license review. Useful for the fork-friendly-architecture commitment but not gating anything else today.

## Owners

- **Tech Writer:** Michael O'Connor — ADR template + retroactive write-ups
- **CTO:** Elon Musk — decision-history attestation
- **Lead Eng:** Rajesh Kumar + Nina Patel — per-decision authoring
- **Editorial:** Lucy Leai oversight
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2095 row)
- OSS methodology repo: `docs/GRIMBANEWS_OSS_METHODOLOGY_README_PLAN.md`
- Methodology page: `/methodologie` (live)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
