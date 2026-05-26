# GrimbaNews — Paging Matrix

**Status:** matrix v0 (operator-side; external pager wiring deferred to launch week)
**Owner:** Sara Chen (CISO) + Hannah Kim (Platform) + Jacob Lee (DevOps)
**Walks:** Mythos S1019 (paging matrix) deferred → partial
**Gating dependency:** PagerDuty (or equivalent) account onboarded.

## Trigger → Severity → Pager

| Trigger | Severity | Channel | Detection mechanism |
|---|---|---|---|
| /health returns 5xx for > 3 min | P0 | PagerDuty primary | external probe (Uptime Robot etc.) |
| `grimba:health --fail-on-risk` returns non-zero | P1 | Slack #ops + PagerDuty secondary | hourly cron, sends via `slack_webhook` job key (Wave FFFFFFFFFFF) |
| DB backup fails 2 consecutive days | P1 | Slack #ops + PagerDuty | `grimba:verify-backups` cron |
| RSS ingest stalled > 4h (no successful poll) | P1 | Slack #ops | `grimba_automation_runs.rss_ingest` stale check |
| NobuAI all drivers failing | P1 | Slack #ops + email lead | per-driver error rate > 80% in 15-min window |
| Disk usage on VPS > 85% | P2 | Slack #ops | `df -h /` cron |
| Per-source ingest failure rate > 50% | P2 | Slack #ops | `news_sources.last_error` recent count |
| Middle Ground cluster count drops below floor | P3 | Slack #ops | `grimba:health --min-middle-ground-clusters=N` |
| Reader bounce rate > 80% on hero page | P3 | Slack #editorial | analytics cron (gates on analytics provider) |

## P0 = wake operator. P1 = next-business-hour. P2 = daily standup. P3 = weekly review.

## Existing surrogate

`grimba:health --fail-on-risk` hourly cron + `GrimbaAutomationMonitor::status()` cockpit board cover P1 detection today. External pager wiring is the open dep.

## Cross-references

Master plan: S1019. Sister: `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md`, `docs/GRIMBANEWS_ESCALATION_TIERS.md`, `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`.
Job-registry signal: `slack_webhook` key in `app/Support/GrimbaAutomationMonitor.php`.
