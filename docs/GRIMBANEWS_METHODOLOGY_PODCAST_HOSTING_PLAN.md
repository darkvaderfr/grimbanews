# GrimbaNews — Methodology Podcast Hosting + RSS Plan (Apple / Spotify)

**Sprint ID:** S1798
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1791-s1800 — Methodology podcast — hosting + RSS (Apple Podcasts / Spotify)`
**Walk wave:** BBBB

## Gating dependency

Podcast hosting + RSS needs:

- Recordings to host (S1797, deferred)
- Hosting decision (self-host on S3 / Cloudflare R2 vs Transistor / Buzzsprout / Acast)
- iTunes-compatible RSS 2.0 feed at `/podcast/methodologie/feed.xml`
- Cover art (3000x3000 JPG/PNG)
- Per-episode show notes (could render from existing `posts` table with `category=podcast`)
- Apple Podcasts Connect + Spotify for Podcasters submission
- Cross-locale ID3 metadata (FR show + EN show separate, or bilingual single show)

## Surrogate-now infra

- **`docs/GRIMBANEWS_PODCAST_PUBLISHING_PIPELINE.md`** — existing publishing-pipeline plan
- **`/feed.xml` infrastructure** — existing RSS-emit infrastructure can be cloned for podcast RSS
- **Botble posts table** — could carry podcast episodes as a category

## Honest framing

Hosting + RSS is a 1-week build once recordings exist. Submission to Apple / Spotify is ops paperwork.

## Owners

- **DevOps:** Jacob Lee — hosting infra
- **Backend:** Rajesh Kumar — RSS adapter
- **Marketing:** Henry Walker — submission paperwork
- **Frontend:** Nina Patel — `/podcast/methodologie` landing
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1798 row)
- Recording: `docs/GRIMBANEWS_METHODOLOGY_PODCAST_RECORDING_PLAN.md`
- Transcript: `docs/GRIMBANEWS_METHODOLOGY_PODCAST_TRANSCRIPT_PLAN.md`
- Existing podcast publishing pipeline: `docs/GRIMBANEWS_PODCAST_PUBLISHING_PIPELINE.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
