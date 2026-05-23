# GrimbaNews — Public Status Page Plan

**Status:** plan / pre-engagement (no status-page vendor wired)
**Owner:** Jacob Lee (DevOps) + Lucy Leai (Strategy) on copy
**Walks:** Mythos S1017 (status page) + S1018 (public uptime page) deferred → partial
**Gating dependency:** No status-page provider chosen + no public uptime monitor. This document is the **vendor shortlist + IA + content-source map** that activation will follow.

## Why this exists

The Mythos S1017 + S1018 rows were both honest-deferred for the same reason: no chosen vendor. The internal surrogate already exists — `/health` JSON ships `status/service/time/db/last_post_at` with `Cache-Control: no-store` + `X-Robots-Tag: noindex`, locked by `tests/Feature/GrimbaLaunchReadinessTest::test_health_endpoint_returns_json_with_required_fields`. What's missing is a **public-facing** transparent surface that a reader can bookmark.

This plan picks a vendor shortlist, defines the IA, and maps each status-page block back to existing internal signals so the wiring is a one-time integration when the account ships.

## Vendor shortlist

| Vendor | Pricing | Pros | Cons | Decision factor |
|---|---|---|---|---|
| Better Stack (StatusPage) | $29/mo Pro tier | Bundled with their uptime monitor (S1018); subdomain `status.grimbanews.com`; integrates with our `/health` JSON directly | Newer vendor | **Primary candidate** — one bill for status + uptime, low friction |
| Atlassian Statuspage | $29/mo Starter | Industry standard | Pulled-out brand recognition; pricier at scale | Backup if Better Stack falls through |
| Instatus | $20/mo | Cheaper, fast UI | Fewer integrations | Backup |
| Self-hosted (Cachet) | Free + VPS cost | Full control | Maintenance burden conflicts with VPS-only-no-orchestration policy | Out of scope |

**Recommendation:** Better Stack ($29/mo) — bundles S1017 + S1018 in one contract.

## Status page IA

1. **Top banner** — Overall operational / degraded / outage. Computed from the 4 components below.
2. **Components** (each maps to an internal signal):
   - **Reader site (grimbanews.com)** — pings `/up` every 60s from 3 external regions (NYC, EU-West, SG).
   - **API surfaces (RSS feeds + health endpoint)** — pings `/feed.xml` + `/health` every 60s. Budget: < 3000ms (matches `grimba:release-smoke` budget at `app/Console/Commands/GrimbaReleaseSmoke.php:96-103`).
   - **Ingest pipeline** — webhook from `grimba:health --fail-on-risk` (hourly per `routes/console.php:173-176`). Status = green when last 6 hours of `grimba_automation_runs` rows for `rss_poll_main` are all `success`.
   - **Translation + NobuAI** — webhook from `GrimbaNobuAiHealth` + provider-chain failover. Status = green when at least one provider is healthy.
3. **Incident history** — auto-populated from webhook + manual entries.
4. **Subscribe** — email + Atom feed (Better Stack standard).
5. **Maintenance windows** — operator-posted.

## Content sources (existing internal signals)

| Status block | Internal signal | File / endpoint |
|---|---|---|
| Reader site uptime | `/up` HTTP 200 + < 1500ms | `routes/web.php`, locked by `GrimbaLaunchReadinessTest` |
| Health endpoint | `/health` JSON `status:ok` | `routes/web.php`, locked by `test_health_endpoint_returns_json_with_required_fields` |
| RSS feed responsiveness | `/feed.xml` HTTP 200 + < 3000ms | `platform/themes/echo/routes/web.php:333` |
| Ingest pipeline health | `grimba_automation_runs` failed count, last 24h | `app/Support/GrimbaAutomationMonitor.php` |
| Backup verification | `grimba_automation_runs` row for `backup_verify` status | `app/Console/Commands/GrimbaVerifyBackups.php` |
| Scheduled job health | `grimba_automation_runs` rows for all 22+ jobs | `app/Support/GrimbaAutomationMonitor.php`, scheduler at `routes/console.php` |

## Activation checklist (day-1 when account ships)

1. Provision Better Stack Pro ($29/mo) on `iboga-ventures` org.
2. Add DNS `CNAME status.grimbanews.com → statuspage.betterstack.com`.
3. Configure 4 components above with their probes.
4. Wire webhook from `GrimbaHealth::handle()` (fail-on-risk path) to Better Stack incident-create endpoint.
5. Wire webhook from `GrimbaAutomationMonitor::finish()` for `status='failed'` on P0 jobs.
6. Publish copy in FR + EN (Lucy Leai owns copy; templates in `docs/GRIMBANEWS_INCIDENT_COMMS_TEMPLATES.md`).
7. Add `<a href="https://status.grimbanews.com">État du service</a>` link in footer (per `partials/footer.blade.php`).
8. Update `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md` with status-page URL.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1017, S1018 rows)
- Existing internal surrogate: `/health` JSON + `/up` endpoint locked by `GrimbaLaunchReadinessTest`
- Health probe (signal source): `app/Console/Commands/GrimbaHealth.php`
- Release-smoke budgets (latency reference): `app/Console/Commands/GrimbaReleaseSmoke.php:96-103`
- Automation monitor (job ledger): `app/Support/GrimbaAutomationMonitor.php`
- Sister plan: `docs/GRIMBANEWS_INCIDENT_COMMS_TEMPLATES.md` (S1020) for status-update copy
