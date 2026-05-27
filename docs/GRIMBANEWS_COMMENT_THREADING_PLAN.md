# GrimbaNews — Comment Threading Surrogate Plan

**Sprint ID:** S1362
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1361-s1370 — Comment threading`
**Walk wave:** CCCC

## Gating dependency

Threaded comments need:

- A `comments` table with `parent_id` self-reference + `path` materialized for ordering
- Logged-in reader identity (Botble already supports it; not surfaced to readers)
- Anti-spam (rate limit + reCAPTCHA or hashcash)
- Moderation queue (S1363)
- Notification fan-out (S1366)

None of these ship today; comments are entirely deferred.

## Surrogate-now infra

- **`/contact`** — free-form intake; readers can engage but not publicly
- **`docs/GRIMBANEWS_PER_CLUSTER_READER_NOTES_DESIGN.md`** — per-cluster note pattern (private notes today)
- **`docs/GRIMBANEWS_PER_CLUSTER_QA_SURFACE_DESIGN.md`** — per-cluster Q&A pattern that pre-figures the public discussion surface
- **`tests/Feature/SecurityHeadersTest`** — CSP discipline that would protect any future comment renderer from XSS

## Honest framing

Comments are a deliberate non-MVP scope decision. Editorial bandwidth + moderation cost + harassment exposure outweigh perceived engagement lift for a small editorial team. Surrogate: reader engagement currently lives in newsletter replies + saved searches + private notes.

## Owners

- **Product:** Liam Smith — public-discussion go/no-go
- **Editorial:** TBD — moderation bandwidth budget
- **Security:** Maya Patel — anti-abuse policy
- **Backend:** Rajesh Kumar — schema + threading logic
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1362 row)
- Reader notes design: `docs/GRIMBANEWS_PER_CLUSTER_READER_NOTES_DESIGN.md`
- Comment moderation queue: S1363 (deferred)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
