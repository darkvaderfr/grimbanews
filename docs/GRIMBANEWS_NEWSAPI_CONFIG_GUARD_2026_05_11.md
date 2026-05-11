# GrimbaNews NewsAPI Configuration Guard - 2026-05-11

**Scope:** make missing NewsAPI credentials visible instead of allowing a scheduled no-op to look healthy.

## Production Finding

Manual production probe:

```sh
php artisan grimba:fetch-newsapi
```

Result:

```text
NewsAPI key not set. Configure NEWSAPI_KEY in .env or paste it at /admin/grimba/newsapi.
```

This explained the `NewsAPI fetch : 0 items` line in production health. RSS was carrying freshness, but NewsAPI was not actually configured.

## Code Change

- `grimba:fetch-newsapi` now exits with failure when no NewsAPI key is configured.
- `grimba:health` reports NewsAPI state as active/inactive plus configured/missing key.
- `grimba:health --fail-on-risk` flags active-without-key as an operating risk.
- Admin and scheduler defaults now treat NewsAPI as active by default only when a key exists.
- If an operator explicitly enables NewsAPI without a key, the command and health guard fail loudly.

## Verification

Local tests:

- `php artisan test --filter=NewsApiCategorySweepTest`
- `php artisan test --filter=AutomationScheduleTest`
- `php artisan test --filter=AdminSettingsTest`
- `php artisan test`

Full result:

- 152 tests passed.
- 2229 assertions.

Production after deploy:

- Home route: passed.
- Feed route: passed.
- `grimba:health --fail-on-risk`: passed.
- Health reports `NewsAPI state : inactive / missing key`.
- Manual `grimba:fetch-newsapi` reports the missing key and exits with code 1.

## Residual Risk

- Production still needs a real NewsAPI key before NewsAPI can contribute fresh articles.
- Until then, RSS is the active freshness path.
- The next operator action is to configure `NEWSAPI_KEY` or paste the key in `/admin/grimba/newsapi`, then run a live fetch and verify `newsapi_items.fetched_at` within 24h.
