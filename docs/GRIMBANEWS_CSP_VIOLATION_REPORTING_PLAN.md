# GrimbaNews — CSP Violation Reporting Plan

**Status:** plan v0 (CSP shipped, report-uri not wired)
**Owner:** Sara Chen (CISO) + Jacob Lee (DevOps)
**Walks:** Mythos S1080 (CSP violation reporting) deferred → partial
**Gating dependency:** report-uri.com (or self-hosted endpoint) for CSP report collection + Slack channel for alerts.

## v1 — wire `report-uri` directive

Per `app/Http/Middleware/GrimbaSecurityHeaders.php`, current CSP includes only:

```
default-src 'self'
script-src 'self' 'unsafe-inline' ...
style-src 'self' 'unsafe-inline' https:
img-src 'self' data: blob: https: http:
```

Add:
```
report-uri https://grimbanews.report-uri.com/r/d/csp/enforce
report-to csp-endpoint
```

Plus `Report-To` header pointing at the same endpoint.

## v2 — self-hosted report endpoint

New route `POST /csp-report` accepts CSP violation JSON payloads. Stores in `csp_violations` table (gates on migration). Aggregates daily for review.

```
csp_violations:
  id | document_uri | blocked_uri | violated_directive | source_file | line_no | reported_at
```

## UX

Admin-only `/admin/grimba/csp-violations` page:
- Top-10 blocked URIs over last 7 days
- Per-directive violation count
- Per-source-file aggregation (which Blade template introduced what violation)
- Filter: ignore known-noise (browser extensions, etc.)

## Alerting

Per Wave FFFFFFFFFFF pattern: if violation count spikes > 100/hour, alert via `slack_webhook` job key.

## Cross-references

Master plan: S1080. Sister: `docs/GRIMBANEWS_VULNERABILITY_SCAN_RUNBOOK.md`.
Code: `app/Http/Middleware/GrimbaSecurityHeaders.php`.
