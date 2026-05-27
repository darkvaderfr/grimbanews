# GrimbaNews — Methodology Video Cross-Locale Subtitles Plan (FR + EN)

**Sprint ID:** S1796
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1791-s1800 — Methodology video — cross-locale subtitles (FR + EN)`
**Walk wave:** BBBB

## Gating dependency

Cross-locale subtitles need:

- Video recording (S1792, deferred)
- Transcript (S1795, deferred)
- Translation pipeline — `GrimbaTranslator` (FR↔EN via rules + LibreTranslate / OpenRouter fallback, already shipped) can adapt to subtitle-format JSON output
- WebVTT / SRT export per locale
- Video player with subtitle-track support
- Per-locale chapter markers (a11y bonus)

## Surrogate-now infra

- **`GrimbaTranslator`** — already shipped FR↔EN translation engine; can be invoked for subtitle text
- **`docs/GRIMBANEWS_METHODOLOGY_VIDEO_SCRIPT_PLAN.md`** — if the script is already authored in both locales, half the subtitle work is done

## Honest framing

Subtitles are an a11y commitment. Translation engine exists. ~2 days of work once video + transcript exist.

## Owners

- **Backend:** Rajesh Kumar — subtitle-export adapter on top of GrimbaTranslator
- **Frontend:** Nina Patel — player + subtitle track wiring
- **i18n:** Nina Patel — per-locale review
- **a11y:** Nina Patel — semantic + cue-timing review
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1796 row)
- Script: `docs/GRIMBANEWS_METHODOLOGY_VIDEO_SCRIPT_PLAN.md`
- GrimbaTranslator: `app/Services/GrimbaTranslator.php`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
