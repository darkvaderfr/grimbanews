# GrimbaNews — Metrics Pipeline Plan

**Status:** plan / pre-engagement (no metrics-platform account)
**Owner:** Jacob Lee (DevOps)
**Walks:** Mythos S1931 (metrics pipeline) deferred → partial
**Gating dependency:** Vendor selection + budget; integration is straightforward once chosen.

## Why this exists

S1931 was honest-deferred with the note "Jacob-Lee-DevOps pickup; vendor selection blocker." The selection itself is the work — the integration is a half-day after the account ships. This doc shortlists, recommends, and identifies the integration point.

## Today's metrics surface

What we already emit:

- `grimba_automation_runs` table — per-job duration_ms, status, exit_code, error_message. Aggregable to mean/p50/p95/p99 with a query.
- `grimba_live_news_provider_runs` table — per-provider call counts.
- `GrimbaProviderCredits` daily counters — per-provider per-UTC-day call count + cache hits + fast-vs-slow split.
- `/health` JSON — point-in-time state.
- Laravel access log + error log in `storage/logs/`.
- `grimba:health` command output — text-format SLO check.

What we don't emit today: time-series metrics on a queryable substrate. There's no Prometheus scrape endpoint, no StatsD client, no OpenTelemetry collector wired.

## Vendor shortlist

| Vendor | Pricing | Pros | Cons | Decision |
|---|---|---|---|---|
| **Datadog** | $15-$23/host/mo | Single tool for metrics + logs + traces; deep Laravel integration; integrates with the same on-call vendor stack (PagerDuty/Better Stack) | Pricey at scale | Primary candidate — bundles S1931 + S1932 + S1933 in one bill |
| Grafana Cloud Free | Free tier (10k series) | Open standards (Prometheus + Loki + Tempo); cheap | More wiring effort | Backup candidate |
| New Relic | Free 100GB/mo | Generous free tier; easy install | Vendor lock for custom dashboards | Backup |
| Self-hosted Prometheus + Grafana | Free + VPS cost | Full control | Maintenance burden vs VPS-only policy | Out of scope per `feedback_hosting_policy.md` |

**Recommendation:** Datadog (or Grafana Cloud Free for a budget-constrained start). Both have the same integration shape — install agent, scrape `/health` + send custom Laravel metrics.

## Integration point

File: `app/Support/GrimbaMetrics.php` (does not exist; would be created).

Shape:

```php
namespace App\Support;

use DataDog\StatsD\StatsdClient;  // or league/statsd

class GrimbaMetrics
{
    protected StatsdClient $client;

    public function timing(string $metric, float $ms, array $tags = []): void
    {
        $this->client->timing($this->prefix($metric), $ms, 1.0, $this->flatten($tags));
    }

    public function increment(string $metric, array $tags = []): void
    {
        $this->client->increment($this->prefix($metric), 1, $this->flatten($tags));
    }

    public function gauge(string $metric, float $value, array $tags = []): void
    {
        $this->client->gauge($this->prefix($metric), $value, $this->flatten($tags));
    }

    protected function prefix(string $m): string { return "grimba.{$m}"; }
    protected function flatten(array $tags): array { /* tag-format adapter */ }
}
```

Wiring points (5 places):

1. **`GrimbaAutomationMonitor::finish()`** — emit `grimba.scheduler.duration_ms` + `grimba.scheduler.status` tagged by `job_key`.
2. **`GrimbaReleaseSmoke::handle()`** — emit `grimba.release_smoke.budget_check` per route.
3. **`GrimbaNobuAi::dispatch()`** — emit `grimba.nobuai.call` + `grimba.nobuai.failover` tagged by `provider`.
4. **`GrimbaProviderCredits::bump()`** — emit `grimba.provider_credits.consumed` tagged by `provider`.
5. **`GrimbaHealth::handle()` finish** — emit `grimba.health.risk_count` gauge.

## SLO definitions (S1934 partial → ready to ship)

Map existing freshness SLO knobs to per-endpoint metrics:

- `grimba.endpoint.health.p99_ms < 1500` (matches release-smoke budget)
- `grimba.endpoint.up.p99_ms < 1500`
- `grimba.endpoint.feed_xml.p99_ms < 3000`
- `grimba.endpoint.home.p99_ms < 3000`
- `grimba.ingest.full_content_coverage_pct >= 70` (already on `grimba:health --min-full-content-coverage=70`)
- `grimba.ingest.category_published_24h >= 3` (already on `--min-category-published-24h=3`)

## Activation checklist (day-1 when vendor ships)

1. Provision Datadog or Grafana Cloud Free account.
2. Install agent on VPS via vendor install script.
3. Add Datadog DogStatsD or Prometheus pushgateway URL to `.env`.
4. Create `app/Support/GrimbaMetrics.php` per the shape above.
5. Wire 5 emission points.
6. Define SLOs in vendor UI per list above.
7. Create dashboards: Scheduler health (per-job latency + failure rate), Ingest velocity (per-provider call rate), NobuAI cost (per-provider call/credit/cache).
8. Update `docs/GRIMBANEWS_VENDOR_REGISTER.md` — Datadog row.
9. Update `docs/GRIMBANEWS_GDPR_ROPA.md` — verify Datadog DPA + EU residency.

## Estimated effort

- Initial setup: 4 hours.
- Wiring 5 emission points + tests: 6 hours.
- Dashboard + SLO config: 4 hours.
- Total: ~2 days of Jacob Lee time.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1931 row; gates for S1934-S1936 SLO dashboards + error-budget alerts)
- Existing time-series-like substrate: `app/Support/GrimbaAutomationMonitor.php`, `app/Support/GrimbaProviderCredits.php`
- Existing SLO surface: `app/Console/Commands/GrimbaHealth.php` (`--fail-on-risk --min-full-content-coverage --min-category-published-24h`)
- Existing budget surface: `app/Console/Commands/GrimbaReleaseSmoke.php` (per-route ms budgets at lines 96-103)
- Sister docs: `docs/GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md` (errors), `docs/GRIMBANEWS_STATUS_PAGE_PLAN.md` (uptime), `docs/GRIMBANEWS_VENDOR_REGISTER.md`
