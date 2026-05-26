# GrimbaNews — Day-1 Incident Review Template

**Status:** template v0 (review happens after prod cutover)
**Owner:** Sara Chen (CISO) + Hannah Kim (Platform) + Jacob Lee (DevOps)
**Walks:** Mythos S1002 (day-1 incident review) deferred → partial
**Gating dependency:** Real day-1 traffic. Surrogate is `GrimbaAutomationMonitor::status()` board on `/admin/grimba/cockpit`.

## When this fires

Within 24 hours of prod cutover (DNS pointed at VPS + HTTPS provisioned + first reader traffic). Or after any P0/P1 incident in the first 7 days.

## Review template

```
# GrimbaNews — Day-1 Incident Review · YYYY-MM-DD

## Window
Start: ISO-8601
End: ISO-8601

## Incidents
- ID | Severity | Surface | Detection | First user impact | Resolution time | Root cause | Action item
- ...

## Automation-monitor failures (last 24h)
[paste from `php artisan grimba:health --fail-on-risk` output]

## RSS ingest health
- Polls attempted vs. completed
- Per-source failure rate
- Lost-poll reasons

## NobuAI driver health
- Per-driver call count + error rate
- Slack webhook fire count (per `slack_webhook` job key, Wave FFFFFFFFFFF)

## Reader impact
- Unique visitors
- /health uptime
- Top-3 page-load errors (per Sentry — gates on Sentry onboarding)

## Action items
- [ ] ...
- [ ] ...

## Sign-off
- Sara Chen (CISO):
- Hannah Kim (Platform):
- Jacob Lee (DevOps):
- Zenkai (final signoff):
```

## Cadence

- Day-1: full template above.
- Day-7: lighter template (just incident roll-up + automation health) per S1003.
- Day-30: quality review per S1004 (focuses on `GrimbaPruneReleaseEvidence` 30-day window of release-evidence files).

## Surrogate today

`GrimbaAutomationMonitor::status()` running on `/admin/grimba/cockpit` already records:
- `grimba_automation_runs` per-job-key status (`running`, `success`, `failed`, `stale`)
- `started_at`, `finished_at`, `duration_ms`, `exit_code`, `error_message`
- Wave PPPPPPPPPPP signal-éditorial tile shows MG/BS counts
- Wave HHHH top-5 sources tile shows MG anchor sources
- Wave FFFFFFFFFFF `slack_webhook` health-paging self-monitor

When prod cutover happens, day-1 review pulls from these tables + paste into the template above.

## Cross-references

Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1002, S1003, S1004).
Sister: `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`, `docs/GRIMBANEWS_INCIDENT_COMMS_TEMPLATES.md`.
Cockpit: `resources/views/grimba-admin/cockpit.blade.php`, `platform/themes/echo/functions/grimba-admin-cockpit.php`.
Job registry: `app/Support/GrimbaAutomationMonitor.php`.
