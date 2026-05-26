# GrimbaNews — Source License Challenges Ledger Plan

**Status:** plan v0 (no aggregation surface; `news_sources.license_notes` is operator slot per S1030)
**Owner:** Lucy Leai + counsel + Sara Chen on operator hygiene
**Walks:** Mythos S2005 (Source-license challenges + outcomes) deferred → partial
**Gating dependency:** `news_sources.license_notes` column populated for active sources + counsel-defined outcome rubric.

## Why this exists

S2005 transparency-reports "during the year, X sources challenged our use of their content; Y were resolved by removal, Z by license, W by counter-notice." Today no log exists.

## Today's surrogate

- `news_sources.license_notes` free-text column (per S1030).

## Schema (target)

```sql
CREATE TABLE source_license_challenges (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  source_id BIGINT NOT NULL,
  received_at TIMESTAMP NOT NULL,
  challenger_role ENUM('source_legal','rights_aggregator','government') NOT NULL,
  basis ENUM('copyright','db_rights','contract','none_stated') NOT NULL,
  scope ENUM('single_article','date_range','all_use') NOT NULL,
  outcome ENUM('removed','licensed','counter_noticed','pending') DEFAULT 'pending',
  resolved_at TIMESTAMP NULL,
  notes TEXT NULL,
  INDEX (source_id), INDEX (received_at), INDEX (outcome)
);
```

## Operator workflow

- Challenge logged on receipt by operator (manual entry today, eventual `/admin/grimba/source-challenges` UI).
- Counsel-routed for non-trivial cases.
- Resolution outcome + date logged.

## Annual report aggregation

- Total challenges received
- Per-basis distribution
- Per-outcome distribution
- Per-source repeated challenges flag (>=3 in a year → operator decision on continued ingest)

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2005)
- Sister docs: `docs/GRIMBANEWS_ANNUAL_TRANSPARENCY_SCOPE_DECISION.md`, `docs/GRIMBANEWS_DMCA_TAKEDOWN_COUNTS_LEDGER_PLAN.md`
- Existing infra: `news_sources.license_notes` column
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
