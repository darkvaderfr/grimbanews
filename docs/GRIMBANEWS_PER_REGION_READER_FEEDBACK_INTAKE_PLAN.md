# GrimbaNews — Per-Region Reader Feedback Intake Surrogate Plan

**Sprint ID:** S2176
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2161-s2180 — Per-region reader-feedback intake`
**Walk wave:** BBBB

## Gating dependency

A per-region reader-feedback intake needs:

- A `region` selector at intake time (auto-detect from `grimba_region` cookie + manual override)
- A `feedback_intake` table with `region` column
- Routing rules per region (Antilles / Pacific / Sub-Saharan / DOM-TOM / Europe / NA)
- Per-region acknowledgement copy in the locale-appropriate catalog
- Aggregator that rolls per-region feedback into the editorial-decisions feed (S2178 under-covered tracker)

None of these ship today. There is one global `/contact` form with no region tagging.

## Surrogate-now infra

- **`/contact`** — global free-form mailbox; reader can mention their region in the body
- **`grimba_region` cookie** — already present, would auto-tag if hooked up
- **Per-region landing pages** — `/locale` family already groups by region; the feedback surface could live as a footer block per landing
- **Editorial review** — ops manually reads `/contact` mail and routes by hand

## Honest framing

Per-region intake is a 1-week build (form field + table + routing config). It sits deferred because per-region routing is meaningless without per-region editors (S2174 deferred) — the ack would land in the same single inbox.

## Owners

- **Product:** Liam Smith — intake form scope
- **Backend:** Rajesh Kumar — `feedback_intake` table + endpoint
- **Editorial:** TBD per-region editor seats (operator-side hire)
- **i18n:** Nina Patel — per-locale catalog ack copy
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2176 row)
- Per-region under-covered tracker: `docs/GRIMBANEWS_PER_REGION_UNDER_COVERED_STORY_TRACKER_PLAN.md`
- Per-region newsroom partnership template: `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
