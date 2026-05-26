# GrimbaNews — Complaint Public Findings Publication Plan

**Status:** plan v0 (no public findings published; gates on ombudsman intake S2023 + investigation log S2027)
**Owner:** Lucy Leai (CEO) + Ombudsman + Michael O'Connor (Technical Writer) for public copy
**Walks:** Mythos S2029 (Complaint workflow — public findings publication) deferred → partial
**Gating dependency:** Ombudsman intake (S2023) live + investigation log (S2027) populated + editorial-policy on public-vs-private findings.

## Why this exists

S2029 makes outcomes visible. Without public findings, the ombudsman is invisible — no demonstration of accountability.

## Today's surrogate

- None. Operator's editorial decisions are internal.

## Publication route

**Route:** `/ombudsman/decisions` (FR) + `/ombudsman/decisions` (EN sibling)

**Page structure:**
- Reverse-chronological list of public-excerpt entries (per S2027 `public_excerpt`).
- Per-entry: date, severity tier, decision, remedy, public excerpt of complaint + decision rationale.
- No complainant identity unless they explicitly waived anonymity.

## Decision criteria (editorial)

- P0 + P1 default to publication (high-stakes accountability).
- P2 publication at ombudsman discretion (some are too granular to be meaningful).
- P3 default no publication (operational noise).

## Anonymization

- Complainant identity redacted unless waiver (per S2023 form checkbox).
- Specific source / author names redacted unless decision specifically concerned them.
- Specific article titles redacted unless decision pertained to the article being published.

## Cadence

- Decisions published within 7 days of close.
- Annual roll-up in transparency report (S2001).

## Counsel review

- All P0 publication drafts → counsel sign-off before publish (defamation / privacy / contract liability).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2029)
- Sister docs: `docs/GRIMBANEWS_OMBUDSMAN_INTAKE_PAGE_SCOPE.md`, `docs/GRIMBANEWS_INVESTIGATION_LOG_SCHEMA.md`, `docs/GRIMBANEWS_COMPLAINT_TRIAGE_RUBRIC_PLAN.md`, `docs/GRIMBANEWS_VOS_DROITS_READER_RIGHTS_PAGE_SCOPE.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
