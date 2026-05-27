# GrimbaNews — Ombudsman Correction-Issuance Authority Plan

**Sprint ID:** S2034
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Ombudsman — correction-issuance authority`
**Walk wave:** BBBB

## Gating dependency

Granting ombudsman correction-issuance authority needs:

- Charter clause (S2021, partial)
- Corrections primitive (S2006, deferred — see sibling walk)
- Editorial-workflow surface (S1291, deferred)
- A `/admin/grimba/corrections` endpoint scoped to ombudsman role
- Per-correction notification to original author
- Disclosure to readers (correction note rendered on the corrected article)

## Surrogate-now infra

- **`docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`** — charter scaffold present; needs explicit "may issue corrections without operator sign-off" clause
- **`docs/GRIMBANEWS_TRANSPARENCY_CORRECTIONS_COUNT_DESIGN.md`** — corrections primitive sister walk
- **`posts.updated_at`** — silent correction trail today; not auditable as "correction" specifically

## Honest framing

Authority to correct is a governance commitment; the code is trivial once corrections primitive + ombudsman role exist. Real gate is the operator-side decision to delegate.

## Owners

- **CEO:** Lucy Leai — delegation authority
- **Editorial / Trust:** TBD ombudsman
- **Compliance:** Maya Patel — disclosure policy
- **Backend:** Rajesh Kumar — role-scoped endpoint
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2034 row)
- Charter: `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`
- Corrections primitive: `docs/GRIMBANEWS_TRANSPARENCY_CORRECTIONS_COUNT_DESIGN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
