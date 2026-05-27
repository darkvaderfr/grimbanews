# GrimbaNews — API Status Page Design

**Status:** plan v0
**Owner:** Jacob Lee (DevOps) + Hannah Kim (Platform)
**Walks:** Mythos S1703 (API status page) deferred → partial
**Gating dependency:** /health endpoint live (it is — Wave VVV).

## Why this exists

B2B customers want a public status page (e.g. status.grimbanews.com) showing real-time uptime + recent incidents. Standard for any production API.

## v1 design

`/status` (or status.grimbanews.com subdomain):
- Per-endpoint status: /api/middle-ground.json, /api/middle-ground.atom, /health, /sitemap-grimba.xml, /feed.juste-milieu.xml
- 90-day uptime per endpoint
- Recent incidents (last 5)
- Maintenance windows announced

## Data source

`/health` endpoint already shipped Wave VVV; status page polls every 60s + aggregates.

## Public communication

Per-incident:
- Auto-post to status page within 5 min of detection
- Per-incident update cadence: every 30 min until resolved
- Per-incident post-mortem within 7 days

## Cross-references

Master plan: S1703. Sister: `docs/GRIMBANEWS_PAGING_MATRIX.md`, `docs/GRIMBANEWS_DAY1_INCIDENT_REVIEW_TEMPLATE.md`.
