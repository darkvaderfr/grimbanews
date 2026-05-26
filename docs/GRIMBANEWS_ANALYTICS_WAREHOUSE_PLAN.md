# GrimbaNews — Analytics Warehouse Plan

**Status:** plan v0 (no warehouse provisioned; CSV exports + SQLite analytics today)
**Owner:** Larry Ellison (VP DBA) on schema + Jacob Lee (DevOps) on infra + Ray Dalio (CFO) on cost
**Walks:** Mythos S1731 (warehouse — destination pick) + S1737 (warehouse dashboard layer) + S1739 (warehouse cost dashboard) deferred → partial
**Gating dependency:** Destination-vendor decision (BigQuery vs Snowflake vs DuckDB vs ClickHouse) + first analytics use case that requires more than SQLite can deliver. Plan itself is operator-side.

## Why this exists

S1731 was honest-deferred: "no warehouse provisioned." Today GrimbaNews runs SQLite live + CSV exports for vault events (per `docs/GRIMBANEWS_DATASET_CSV_SCHEMA.md`). For modest scale that's enough. As cluster volume + read events grow, plus when A/B harness ships (per `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`) and personalization v2 needs cohort cuts, SQLite becomes painful. This document proposes the destination pick + ingest pipeline.

## Today's analytics surface

| Layer | Tooling | Scope |
|---|---|---|
| Operator real-time | `/admin/grimba/cockpit` Blade dashboard | Last-24h ops |
| Per-job ledger | `grimba_automation_runs` table | Append-only |
| Anon event ledger | `vault_events` + monthly CSV per `app/Console/Commands/GrimbaArchiveVaultEvents.php` | Privacy-safe (ip_hash only) |
| Per-reader exports | `/coffre/export.csv`, `/pour-vous/export.csv` | Member-owned |
| **Aggregate analytics** | SQLite queries via cockpit + ad-hoc | Limited at scale |

**No warehouse / no BI tool / no scheduled reports beyond cron-cron-cron.**

## Destination options

| Option | Cost | Latency | Pros | Cons |
|---|---|---|---|---|
| **DuckDB self-hosted** | $0 (single file) | <100ms on local file | Open-source; runs on same VPS; zero ops; SQL-friendly | Single-node; no multi-user concurrency at scale |
| **ClickHouse self-hosted** | VPS only | <500ms on aggregates | Open-source; columnar; very fast; horizontal scale | More ops surface than DuckDB |
| **BigQuery** | per-query pricing | <2s | Managed; integrated with GCP | Vendor lock-in; per-query cost surprise risk |
| **Snowflake** | per-credit | <2s | Managed; multi-cloud | Most expensive; overkill at our scale |
| **MotherDuck** (managed DuckDB) | $25+/month | <500ms | DuckDB ergonomics + shared | Newer vendor |

**Recommended pick: DuckDB self-hosted on same VPS** (Iboga hosting policy per `feedback_hosting_policy.md`).

Rationale:
- Single file = zero ops surface.
- Cost = $0 above existing VPS.
- Reads parquet + CSV natively — pairs perfectly with existing CSV-export pipeline.
- Aggregate queries are exactly DuckDB's wheelhouse.
- Sister projects (e.g. Incognito) could reuse same pattern.
- Upgrade path to ClickHouse when single-node hits ceiling.

## Schema

DuckDB warehouse file at `storage/warehouse/grimba_warehouse.duckdb`.

### Tables (read-only, append-style)

```sql
-- Mirrored from SQLite live tables (refreshed daily)
posts (id, title, ..., editorial_region, editorial_category, ...)
news_sources (id, name, country, bias_rating, factuality_score, ...)
story_clusters (id, title, created_at, ...)
members (id, created_at, ...)  -- anonymized: no email, no name

-- Ingested from CSV exports
vault_events (event, post_id, ts, ip_hash)
automation_runs (id, job_name, status, started_at, finished_at, duration_ms, ...)

-- Computed (refreshed daily)
daily_post_counts (date, region, category, count)
daily_cluster_counts (date, region, count)
daily_source_activity (date, source_id, post_count)
daily_bias_spread (date, region, left_count, center_count, right_count, unknown_count)

-- A/B harness sink (gates on docs/GRIMBANEWS_AB_HARNESS_DESIGN.md)
experiment_outcomes (experiment_id, variant_slug, metric, metric_value, occurred_at, ...)
```

## Ingest pipeline

```
0500 UTC  -> existing cron suite: fetch + cluster + classify
0530 UTC  -> grimba:warehouse:refresh-mirror  [new]
          -> snapshots posts, news_sources, story_clusters, members (anonymized) to parquet
          -> imports CSVs (vault_events, automation_runs) via COPY
0600 UTC  -> grimba:warehouse:compute-rollups  [new]
          -> daily_post_counts, daily_cluster_counts, daily_source_activity, daily_bias_spread
0700 UTC  -> grimba:warehouse:vacuum  [new]
          -> DuckDB pragma optimize
```

All commands new at `app/Console/Commands/GrimbaWarehouse*.php`.

## Dashboard layer (S1737)

**Recommended: Metabase self-hosted** OR **Superset self-hosted**.

| Option | Cost | Pros | Cons |
|---|---|---|---|
| **Metabase** | $0 OSS | Fastest setup; non-engineer-friendly | Heavier (Java) |
| **Superset** | $0 OSS | Strong charts; Python | More complex setup |
| **Hex** | $$ | Best UX; cloud | Vendor; per-seat |
| **Custom Blade dashboards** | $0 (just code) | Full control; matches existing admin | More dev time |

**Recommended: Metabase self-hosted** (Iboga hosting policy). Wire DuckDB JDBC driver.

Per-Iboga ops convention: behind admin auth + IP allowlist. Per `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md` CC6 access control.

## Cost projection (Ray review)

| Item | Cost / month |
|---|---|
| DuckDB | $0 |
| Metabase VPS (2GB / 40GB) | ~€5 |
| Storage growth (warehouse file < 5GB at year-1 volume) | within VPS |
| Cron compute (~30min/day extra) | within existing VPS |

Total: ~€5/month vs ~$200+/month if we went BigQuery + Looker. **Two-order-of-magnitude cost saving** with self-hosted stack.

## Quarterly retention policy (S1738 ship)

- **Raw warehouse:** retain 24 months rolling.
- **Daily rollups:** retain indefinitely (small footprint).
- **vault_events monthly CSV originals:** retain 24 months (already archive-pattern per S1735 complete).
- **GDPR erasure:** member-id deletion propagates to anonymized members mirror within 30 days.
- **Quarterly review:** Sara Chen + Larry Ellison.

## Privacy posture

- **No reader PII** ever enters warehouse — anonymized members mirror drops email, name, IP.
- **vault_events** carries ip_hash only (already privacy-preserving per `app/Console/Commands/GrimbaArchiveVaultEvents.php`).
- **No raw search queries** persisted (per `docs/GRIMBANEWS_SEARCH_V2_LAUNCH_PLAYBOOK.md`).
- **GDPR right-to-erasure** flows through to warehouse via per-member-id delete cascade.
- **Vendor register update** per `docs/GRIMBANEWS_VENDOR_REGISTER.md` — DuckDB self-hosted = no new vendor; Metabase self-hosted = no new vendor.

## Cost dashboard (S1739 ship)

Per-day:
- DuckDB file size.
- Warehouse refresh duration.
- Query volume + slow-query log (queries > 5s).
- Per-table row counts.

Lives at `/admin/grimba/warehouse` (admin-only).

## Engineering effort estimate

- DuckDB install + warehouse file scaffold: 1 sprint.
- Mirror-refresh command (snapshots SQLite tables → parquet): 2 sprints.
- Rollup-compute command: 2 sprints.
- Vault-events + automation-runs CSV ingest: 1 sprint.
- Metabase install + DuckDB JDBC wire: 2 sprints (Jacob).
- Per-dashboard buildout (operator-board, editor-board, exec-board): 4 sprints.
- Cost dashboard + slow-query log: 1 sprint.
- GDPR erasure cascade: 1 sprint.
- Quarterly retention cron: 0.5 sprint.
- Tests + restore-drill: 1 sprint.
- **Full ship: ~15 sprints.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1731-S1740)
- Sister docs: `docs/GRIMBANEWS_DATASET_CSV_SCHEMA.md`, `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`, `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`, `docs/GRIMBANEWS_VENDOR_REGISTER.md`, `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md`
- Existing exports: `app/Console/Commands/GrimbaArchiveVaultEvents.php`
- Existing cockpit: `resources/views/grimba-admin/cockpit.blade.php`
- Automation ledger: `app/Support/GrimbaAutomationMonitor.php`
- Iboga hosting policy: `~/.claude/projects/-Users-vb-kaizen/memory/feedback_hosting_policy.md`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
