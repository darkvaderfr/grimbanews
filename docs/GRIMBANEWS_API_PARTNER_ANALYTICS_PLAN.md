# GrimbaNews — API Partner Analytics Plan

**Status:** plan v0 (no per-key analytics dashboard; web-server access logs cover sampling today)
**Owner:** David Chen (Data Scientist) defines schema + Hannah Kim (Platform) builds dashboard + Liam Smith on partner-facing self-service view
**Walks:** Mythos S1188 (API analytics) deferred → partial
**Gating dependency:** API v2 shipped + `api_keys` schema + log retention policy

## Why this exists

S1188 is the visibility layer over API usage. Two audiences:
1. **Operator** — capacity planning, churn risk, anomaly detection.
2. **Partner** — usage tracking against quota, billing reconciliation.

## Today's surrogate

- **Web server access logs** at `/var/log/nginx/access.log` — bare-minimum per-IP sampling.
- **No structured per-key analytics.**

## Schema

```sql
CREATE TABLE api_v2_usage_events (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  api_key_id BIGINT NOT NULL,
  endpoint VARCHAR(255) NOT NULL,
  http_method VARCHAR(8) NOT NULL,
  status_code SMALLINT NOT NULL,
  duration_ms INT NOT NULL,
  request_bytes INT NULL,
  response_bytes INT NULL,
  user_agent VARCHAR(255) NULL,
  ip_hash CHAR(64) NULL,           -- sha256 of IP, not raw IP (Sara Chen)
  timestamp TIMESTAMP NOT NULL,
  rate_limit_remaining INT NULL,
  INDEX (api_key_id, timestamp),
  INDEX (endpoint, timestamp),
  INDEX (status_code, timestamp)
);

-- Rollup table (hourly) for fast dashboards
CREATE TABLE api_v2_usage_hourly (
  api_key_id BIGINT NOT NULL,
  endpoint VARCHAR(255) NOT NULL,
  hour TIMESTAMP NOT NULL,
  request_count INT NOT NULL,
  error_count INT NOT NULL,
  p50_ms INT NOT NULL,
  p95_ms INT NOT NULL,
  p99_ms INT NOT NULL,
  total_bytes_out BIGINT NOT NULL,
  PRIMARY KEY (api_key_id, endpoint, hour)
);
```

Cron `grimba:api-usage-rollup` runs at :05 each hour, aggregates last hour's events.

## Retention

- Raw events: 30 days.
- Hourly rollups: 365 days.
- Daily rollups (further aggregation): forever.

## Operator dashboard `/admin/grimba/api-keys/{id}/usage`

- Last 7 days: stacked bar chart per-endpoint.
- Last 30 days: line chart total + error rate.
- Top 10 endpoints by volume.
- p50/p95/p99 latency over time.
- Rate-limit hit count.
- "Anomaly" flag if traffic >2σ from rolling 14-day baseline.

## Partner self-service `/api/v2/usage`

```
GET /api/v2/usage?from=2026-05-19&to=2026-05-26

{
  "data": {
    "key_prefix": "gn_acad_a1b2",
    "tier": "academic",
    "rate_limit_per_hour": 5000,
    "daily_cap": 50000,
    "current_hour_used": 1234,
    "current_day_used": 21345,
    "events_in_range": [
      {"day": "2026-05-19", "requests": 4321, "errors": 12, "p95_ms": 145},
      ...
    ]
  }
}
```

## Privacy posture (Sara Chen)

- IP hashed (sha256), never raw.
- No partner-side analytics expose other partners' data.
- Admin dashboards require admin role.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1188)
- Sister docs: `docs/GRIMBANEWS_PUBLIC_API_V2_DESIGN.md`, `docs/GRIMBANEWS_API_SLA_DESIGN.md`, `docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
