# newsdata.io — Operator Handoff (S-NDI-20)

**Authored:** 2026-05-22 (Wave YYYYYYYY).
**Source plan:** `docs/GRIMBANEWS_NEWSDATAIO_INTEGRATION_PLAN.md`
**Code:** `app/Services/GrimbaNewsdataIoFetcher.php` + `platform/themes/echo/functions/grimba-admin-newsdataio.php` + `resources/views/grimba-admin/newsdataio/`

## What newsdata.io gives us

Third programmatic breaking-news provider alongside GDELT, Google News, Webz, Mediastack. Free plan = **200 credits/day, 10 articles per credit call**. We stay on free until ad revenue covers a paid sub.

Adds breaking-news coverage for ~50 countries that GDELT under-represents (West Africa, MENA, Southeast Asia).

## Day-one setup

1. Register at https://newsdata.io and copy the API key.
2. Go to **/admin/grimba/newsdata-io** (admin login required).
3. Paste the key into "API key".
4. Toggle "Active" ON.
5. Set **Queries** (comma-separated). Start with these for FR + EN coverage:
   ```
   breaking news, urgent, alerte, dernière minute, en direct
   ```
6. Set **Languages** to `fr,en`.
7. Set **Countries** to the codes you care about (default: `fr,us,gb`; extend with `sn,ci,bf,ml` for West Africa, `eg,ma,tn` for MENA).
8. Set **Categories** to `top` (newsdata.io's catch-all for breaking).
9. Set **Max calls per run** to `1` initially (1 call = 10 articles + 1 credit; 1 run/5min cron × 1 call = 288 credits/day, well above the 200 budget — see #budgeting below).
10. Set **Daily credit budget** to `190` (10-credit safety margin under the 200 free-plan ceiling).
11. Click "Save". The page reloads with the green "Active" badge.

## Budgeting

- Free plan: **200 credits/day**, resets at midnight UTC.
- Each successful call burns 1 credit and returns up to 10 articles.
- The fetcher hard-stops when today's burn ≥ `grimba_newsdata_io_daily_credit_budget` (default 190).
- Live status: admin progress bar shows used/budget, color-coded green<70%, amber 70-90%, red ≥90%.
- The progress bar reads from `App\Support\GrimbaProviderCredits::usedToday('newsdata-io')` — same surface that powers the scheduler hard-stop.

## Verifying

1. Click **"Test connection"** (admin button). Expects 200 status + a single test article in the response panel. Burns 1 credit.
2. Click **"Run now"** to trigger an immediate poll. Burns 1 credit + ingests up to 10 articles into the live-news queue.
3. Check the cockpit (`/admin/grimba/cockpit`) for new "newsdata-io" provider runs in the "Ingest history" table.
4. Posts ingested via newsdata.io land on the home rail + `/breaking` within 5 minutes (next scheduler tick).

## Scheduler

newsdata.io plugs into the **shared `breaking_live` cron** at `*/5 * * * *` (every 5 min, defined in `routes/console.php`). It runs ONLY when:

- `grimba_newsdata_io_active` = `1`
- `grimba_newsdata_io_key` is non-empty
- Today's burn < `grimba_newsdata_io_daily_credit_budget`

There's no separate `*/8` cron (the originally planned dedicated cadence). The shared cron handles all live-news providers in rotation; newsdata.io takes its turn when its credit window is open.

## Dedupe + provider_item_id

Every article ingested via newsdata.io gets a `provider_item_id` of the form `newsdata-io:{article_id}` (Wave XXXXXXXX). The other 4 providers carry their own prefixes (`google-news:`, `gdelt:`, `webz:`, `mediastack:`). The load-bearing dedupe primary key is `article_url_hash` (UNIQUE constraint on `grimba_live_news_items`); the prefixed `provider_item_id` is for cross-provider attribution debugging.

## Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| "Skipped: not active" in logs | `grimba_newsdata_io_active` = `0` | Toggle ON in admin |
| "Skipped: no API key" | `grimba_newsdata_io_key` empty | Paste key in admin |
| "Skipped: daily budget reached" | Today's burn ≥ budget | Either raise budget, wait for UTC midnight reset, or accept the cap |
| HTTP 401 from newsdata.io | Key invalid or expired | Re-paste from newsdata.io dashboard |
| HTTP 429 from newsdata.io | Rate limit (rare on free plan) | Reduce `max_calls_per_run` or queries |
| `status:error` payload | newsdata.io returned an error response despite 200 OK | Read the `message` field in admin's "Last error" panel |
| Articles ingested but not visible on home/`/breaking` | Locale strict filter is dropping them | Confirm articles' `original_language` matches reader locale, or wait for the daily backfill cron to repair NULL languages |

## Lock-tested contracts

- `tests/Feature/GrimbaNewsdataIoFetcherTest.php` — 9 tests, 29 assertions, all `Http::fake()`. Covers the full state machine: skipped paths (3), error paths (2), success paths (3), normalisation (1).
- `tests/Feature/LiveNewsProviderTest::test_provider_item_id_carries_provider_prefix` — locks the `newsdata-io:` prefix contract (plus the 4 others).

## What's still open in the S-NDI band

| Sprint | Title | Status |
|---|---|---|
| S-NDI-09 | Shared `breaking_live` cron path validated | Blocked on real API key in env |
| S-NDI-10 | Dedicated `*/8` cron (gated, off by default) | Plan deferred — shared cron is sufficient pre-launch |
| S-NDI-17 | Cross-provider title-similarity guard | Deferred — `article_url_hash` already catches the dominant duplicate class |

Everything else (S-NDI-01..08, S-NDI-11..16, S-NDI-18, S-NDI-19) is closed and lock-tested.

## When to revisit

- Once we have a real API key, run S-NDI-09 manually: hit `php artisan schedule:run` and watch logs for a `newsdata-io` line. Confirm articles land in the cockpit's "Ingest history".
- If FR + EN reader traffic exceeds 200 credits/day of breaking-news demand (i.e. the budget gate hits regularly), upgrade to newsdata.io's paid plan or shift to a dedicated `*/8` cron with stricter budget (S-NDI-10 plan).
- If we discover a same-day cross-provider title-similarity issue post-launch (e.g. AFP wire → Reuters wire → AP wire all returning the same Soudan headline), revive S-NDI-17.

---

*Authored by Mythos under Vader 2026-05-22 directive. Reviewed in spirit by Sara Chen (CISO) — keys live behind admin auth, never user-visible — and Ray Dalio (CFO) — free plan budget is the launch posture; revisit when ad revenue clears the upgrade cost.*
