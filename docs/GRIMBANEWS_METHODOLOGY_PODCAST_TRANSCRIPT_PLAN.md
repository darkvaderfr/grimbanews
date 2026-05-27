# GrimbaNews — Methodology Podcast Transcript Plan

**Sprint ID:** S1799
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1791-s1800 — Methodology podcast — transcript`
**Walk wave:** BBBB

## Gating dependency

Per-episode transcript needs:

- Recordings (S1797, deferred)
- ASR pipeline (Whisper / Deepgram / AssemblyAI) — branded as NobuAI for any user-facing surface
- Human editing pass (ASR is 90% accurate, brand-critical text needs review)
- Render under each episode page (HTML semantic + searchable)
- Cross-locale subtitles if FR show with EN translation (or vice versa)
- a11y commitment (transcripts are an accessibility requirement, not a nice-to-have)

## Surrogate-now infra

- **None — no podcast recordings exist**
- **Existing `/methodologie` written longform** — already accessible without ASR

## Honest framing

Transcripts gate on recordings. ASR is solvable; the editing pass is the real cost. Commit to "transcript-first podcast" so transcripts ship with recordings, not after.

## Owners

- **DevOps:** Jacob Lee — ASR pipeline
- **Data Science:** David Chen — ASR provider eval
- **Editorial:** Michael O'Connor (Tech Writer) — editing pass
- **a11y:** Nina Patel — semantic rendering
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1799 row)
- Recording: `docs/GRIMBANEWS_METHODOLOGY_PODCAST_RECORDING_PLAN.md`
- Hosting: `docs/GRIMBANEWS_METHODOLOGY_PODCAST_HOSTING_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
