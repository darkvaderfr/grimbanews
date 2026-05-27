# GrimbaNews — Analytics Warehouse Launch Playbook

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Jacob Lee (DevOps) + Larry Ellison (DBA)
**Walks:** Mythos S1740 (warehouse launch playbook) deferred → partial
**Gating dependency:** S1731-S1739 warehouse band (event ingest + dwell-time + per-event schema + per-event admin).

## Phase 0 — pre-launch

1. ETL pipeline configured from main DB → warehouse (BigQuery/Snowflake/ClickHouse — pick TBD per Ray cost review).
2. Per-event schema defined (event_id, member_hash, event_type, payload, timestamp).
3. Per-event capture in `app/Support/GrimbaVaultEvents.php` extended.
4. Per-event privacy review (anonymization + retention).
5. Per-day backfill script.

## Phase 1 — soft launch (30 days)

1. Per-event live capture (no historical backfill yet).
2. Per-event dashboard skeleton in /admin/grimba/warehouse.
3. Per-event Lisa monitors for capture-rate vs expected.

## Phase 2 — backfill + dashboard polish

1. 90-day historical backfill.
2. Per-event admin queries + visualizations.
3. Per-event reader-analytics features (Wave AAHH dependencies).

## Phase 3 — full production

1. Per-event 1-year retention live.
2. Per-event DSAR + delete workflow.
3. Per-event quarterly cost review (Ray).

## Cost estimate

- BigQuery: ~$5/TB stored + $5/TB queried.
- At GrimbaNews scale (10K events/day × 1KB = 10MB/day = 3.6GB/yr): negligible.

## Cross-references

Master plan: S1740. Sister: Wave LLL analytics-warehouse plan, `docs/GRIMBANEWS_PER_USER_READING_TIME_ANALYTICS_PLAN.md`.
