# GrimbaNews — Day-7 Incident Review Template

**Status:** template v0
**Owner:** Sara Chen (CISO) + Hannah Kim (Platform)
**Walks:** Mythos S1003 (day-7 incident review) deferred → partial
**Gating dependency:** Real day-7 traffic + 7 days of `grimba_automation_runs` rows.

## Template

```
# GrimbaNews — Day-7 Incident Review · YYYY-MM-DD

## Window
Start: ISO-8601 (prod cutover + 24h)
End: ISO-8601 (prod cutover + 7 days)

## Incidents (P0/P1 only this review)
- ...

## Trend: vs Day-1 baseline
- Daily-active-readers Δ
- /health uptime Δ (target: ≥ 99.5%)
- RSS poll failure rate Δ
- NobuAI per-driver error rate Δ
- Slack webhook fire count Δ (`slack_webhook` job key)

## Editorial signal velocity
- Middle Ground clusters tagged in window (per `/health` `middle_ground_clusters_24h`)
- Blindspot clusters published
- Per-region cluster mix change

## Reader signal
- Top-10 stories by share count
- Bounce rate trend
- Time-to-first-article (median)

## Action items vs Day-1
- [ ] Resolved from Day-1 review
- [ ] New action items

## Sign-off
- Sara Chen (CISO):
- Hannah Kim (Platform):
- Zenkai (final):
```

## Surrogate today

`GrimbaAutomationMonitor::status()` already gives 7-day history if `grimba_automation_runs` rows accumulate. /admin/grimba/cockpit panel already shows the trend.

## Cross-references

Master plan: S1003. Sister: `docs/GRIMBANEWS_DAY1_INCIDENT_REVIEW_TEMPLATE.md`.
