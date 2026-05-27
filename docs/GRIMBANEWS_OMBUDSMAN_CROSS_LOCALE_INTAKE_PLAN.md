# GrimbaNews — Ombudsman Cross-Locale Intake Plan

**Sprint ID:** S2032
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2021-s2040 — Ombudsman — cross-locale intake (FR + EN today, more post-S1101)`
**Walk wave:** BBBB

## Gating dependency

Cross-locale ombudsman intake needs:

- Ombudsman intake page shipped (S2023, deferred)
- Per-locale catalogs (S1101+ band, partial)
- Per-locale form copy + ack templates
- Per-locale routing rules (FR ombudsman vs EN ombudsman vs single ombudsman with translator)

## Surrogate-now infra

- **`docs/GRIMBANEWS_OMBUDSMAN_INTAKE_PAGE_SCOPE.md`** — already shipped scope
- **FR↔EN parity at S301** — already partial; ombudsman intake is one more route to add
- **`/contact` global intake** — locale-agnostic today; could carry "report a story-quality concern" body tag

## Honest framing

Cross-locale intake is a translation deliverable once the FR intake ships. Per-locale routing gates on multi-ombudsman staffing — single ombudsman + translator is the realistic v1.

## Owners

- **Editorial / Trust:** TBD ombudsman + Lucy Leai
- **i18n:** Nina Patel — per-locale catalog entries
- **Translator:** GrimbaTranslator (already shipped FR↔EN)
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2032 row)
- Charter: `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`
- Intake page scope: `docs/GRIMBANEWS_OMBUDSMAN_INTAKE_PAGE_SCOPE.md`
- Annual report: `docs/GRIMBANEWS_OMBUDSMAN_ANNUAL_REPORT_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
