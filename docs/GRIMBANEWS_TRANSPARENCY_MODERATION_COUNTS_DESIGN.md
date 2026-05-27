# GrimbaNews — Annual Transparency: Moderation-Action Counts Design

**Sprint ID:** S2002
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S2001_S2237_TRUST_OPENSOURCE_FINAL_EVIDENCE.md#s2001-s2020 — Annual transparency report — moderation-action counts`
**Walk wave:** BBBB

## Gating dependency

Disclosing per-year moderation-action counts needs:

- A live moderation queue (S1591, deferred — no `moderation_queue` table)
- Per-action ledger (`action`, `subject_type`, `subject_id`, `actor_id`, `created_at`)
- A `/transparence/moderation/{year}` page
- A first annual transparency edition (S2011, deferred)

None ship today. Comments are deferred (S1361 band), so most moderation surface area does not exist as code yet.

## Surrogate-now infra

- **`grimba_automation_runs`** — the closest live action-ledger; logs ingest / classify / cluster / publish actions per source
- **`news_sources` lifecycle columns** — soft-disable / re-enable flags act as a coarse-grained moderation log at the source level
- **Git log** — every blocklist / allowlist change to source rosters is in the public git history (after the darkvaderfr push)

## Honest framing

A real moderation-action count requires comments / user-generated-content to moderate. Until S1361 band ships, the "moderation" we actually do is source-roster curation, which is already publicly auditable via git.

## Owners

- **Editorial / Transparency:** TBD ombudsman + Lucy Leai
- **Backend:** Rajesh Kumar — action-ledger schema if/when comments ship
- **Data Eng:** Benjamin Lee — annual aggregation job
- **Compliance:** Maya Patel — disclosure policy
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2002 row)
- Trust & safety moderation scope: `docs/GRIMBANEWS_TRUST_SAFETY_MODERATION_QUEUE_SCOPE.md`
- Annual transparency scope: `docs/GRIMBANEWS_ANNUAL_TRANSPARENCY_SCOPE_DECISION.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
