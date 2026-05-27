# GrimbaNews — API Status & Incident Comms Plan

**Status:** plan v0
**Owner:** Jacob Lee (DevOps) + Hannah Kim (Platform) + Liam Smith (PM)
**Walks:** Mythos S1259 (API status incident comms) deferred → partial
**Gating dependency:** dedicated status page (we currently only ship `/health` JSON).

## Why this exists

Partners and B2B consumers need an authoritative incident channel. `/health` returns binary JSON; a status page communicates severity, scope, ETA, and historical uptime.

## v1 design

- External-hosted status page via Statuspage.io / Instatus / Atlassian — defer build-vs-buy until we have ≥ 3 paying partners.
- Until then, lightweight in-repo surrogate at `/status` Blade view, fed by:
  - last 50 entries of `grimba_automation_runs` (ingest health proxy).
  - `/health` rollup.
  - manually maintained `config/grimba_incidents.php` for past incidents.

## Incident severities

| Sev | Definition | Comms cadence |
|---|---|---|
| Sev1 | Site down or data loss | 15-min updates, public + email to partners |
| Sev2 | Partial outage (one feature) | 30-min updates, public |
| Sev3 | Degraded performance | hourly updates, public |
| Sev4 | Cosmetic / single-source | post-resolution only |

## Communication channels

- Status page (v1 surrogate `/status`).
- Email to subscribed partners.
- `@grimbanews_status` social handle (per Maria Lopez community plan).
- In-app banner for Sev1/Sev2 reader-facing impact only.

## Cross-references

Master plan: S1259. Sister: S1260 (API ops playbook), S1238 (webhook delivery), `/health` (S1017 surrogate). Memory: `feedback_selfcheck_always.md`.
