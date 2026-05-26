# GrimbaNews — Day-30 Quality Review Template

**Status:** template v0
**Owner:** Steve Jobs (CPO) + Lucy Leai (Strategy) + Sara Chen (CISO) + Larry Ellison (DBA)
**Walks:** Mythos S1004 (day-30 quality review) deferred → partial
**Gating dependency:** 30 days of `grimba_automation_runs` + 30 days of release evidence (`GrimbaPruneReleaseEvidence` retains 30-day window).

## Template

```
# GrimbaNews — Day-30 Quality Review · YYYY-MM-DD

## Window
prod cutover + 7 days → prod cutover + 30 days.

## Editorial-quality KPIs
- Articles published per day (target ≥ 12 per `grimba:ensure-daily-publish`)
- Per-category freshness (target ≥ 3 per category per 24h)
- Bias-mix L/C/R per region (acceptable drift ±5% vs editorial target)
- Middle Ground cluster count growth
- Blindspot cluster count growth
- Per-source uptime (lost-poll rate < 5%)

## Operational-quality KPIs
- /health uptime ≥ 99.5%
- p95 page-load latency < 800ms
- DB backup success rate 100% (per `grimba:verify-backups`)
- NobuAI driver fallback rate (when primary fails)
- DR drill report (if any in window)

## Reader-quality KPIs
- DAU growth %
- Session depth (median pages per session)
- Top-10 stories by read-time
- Per-cluster average sources viewed (target ≥ 2 per visit)

## Release evidence
[paste from `storage/app/release-evidence/` files in window]

## Action items roll-up
- From Day-1
- From Day-7
- New from Day-30

## Sign-off
- Steve Jobs (CPO):
- Lucy Leai (Strategy):
- Larry Ellison (DBA):
- Sara Chen (CISO):
- Zenkai (final):
```

## Cross-references

Master plan: S1004. Sister: `docs/GRIMBANEWS_DAY1_INCIDENT_REVIEW_TEMPLATE.md`, `docs/GRIMBANEWS_DAY7_INCIDENT_REVIEW_TEMPLATE.md`.
Code: `app/Console/Commands/GrimbaPruneReleaseEvidence.php`.
