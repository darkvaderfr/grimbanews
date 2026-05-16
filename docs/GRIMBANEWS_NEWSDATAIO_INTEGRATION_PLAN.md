# GrimbaNews — newsdata.io Integration Plan

**Author:** Architect / Principal Reviewer
**Date:** 2026-05-16
**Status:** Sprint plan ready to pick up
**Owner directive:** Vader, 2026-05-16 — "Wire newsdata.io as another breaking-news provider. Free plan = 10 articles / query, 200 credits / day. Time queries throughout the day. UI/UX config for future manual updates. Stay on free plan until revenue covers a subscription."

This plan adds **newsdata.io** as a third programmatic breaking-news provider next to the existing GDELT / Google News RSS / Webz.io / Mediastack pipeline, while preserving the contract that `GrimbaLiveNewsFetcher` already defines (one fetcher class per upstream, summary row per query, telemetry into `grimba_live_news_provider_runs`, idempotent dedupe through `GrimbaArticleDedupe`).

The integration is **admin-visible** (newsdata.io can be named) and **reader-invisible** (the brand never appears on public surfaces — articles flow through the same `news_sources` + `posts.source_id` plumbing as every other provider).

---

## 1. Service-class structure

### 1.1 New class — `App\Services\GrimbaNewsdataIoFetcher`

Mirror the public contract used by `GrimbaLiveNewsFetcher::fetchWebz()` (closest analog because Webz already has daily/monthly budget guards). The new class is a **standalone service** (not a method inside `GrimbaLiveNewsFetcher`) so it stays under the SRP threshold of the existing fetchers and can be unit-tested in isolation.

**Public contract:**

```
public function fetch(?array $queries = null): array
    // returns summary[] with the canonical shape:
    // { provider:'newsdata-io', query:string, status:string,
    //   returned:int, ingested:int, deduped:int, skipped:int, error:?string }

public function isConfigured(): bool         // true iff API key set
public function dailyCreditBudget(): int     // 200 default, clamped 1..200
public function maxCallsPerRun(): int        // default 2, clamped 1..6
public function creditsUsedToday(): int      // cache-backed daily counter
public function creditsRemainingToday(): int // budget - usedToday
public function plannedCallCount(): int      // queries × cadence, for UI preview
```

**Reuse, don't fork:**

- All shared helpers — `ingestMany()`, `ingestArticle()`, `createPost()`, `resolveSourceId()`, `applyImageProvenance()`, `classifyPost()`, dedupe lookup via `GrimbaArticleDedupe::hasSeen()`, telemetry rows in `grimba_live_news_items` and `grimba_live_news_provider_runs` — should be **extracted** from `GrimbaLiveNewsFetcher` into a new trait `App\Services\Concerns\IngestsLiveArticles` (preferred) **or** moved into a service object `App\Services\GrimbaLiveIngestPipeline`. Either way, `GrimbaNewsdataIoFetcher` does **not** copy 200+ lines of post-creation logic.
- If extraction is too risky in one sprint, fall back to a pragmatic option: make `GrimbaLiveNewsFetcher::ingestMany()` and `::ingestArticle()` `public` (still inside that class) and have the new fetcher call into them. Mark that as a NEXT-SESSION refactor.

**Hookup to the existing dispatcher:**

`GrimbaLiveNewsFetcher::fetchAll()` already takes a `$providers` array and routes on `match`. Add a new arm:

```
'newsdata', 'newsdata-io' => app(GrimbaNewsdataIoFetcher::class)->fetch(),
```

And register `newsdata-io` in the default `grimba_breaking_providers` setting (see §7) so the existing `grimba:fetch-breaking` command picks it up automatically.

### 1.2 newsdata.io endpoint shape

- Endpoint: `https://newsdata.io/api/1/latest` (alias of `/news` on free plan; `/latest` is the documented current name).
- Query params we will use:
  - `apikey` — required (header alt: `X-ACCESS-KEY`; use header).
  - `q` — keyword string. Free plan supports up to ~512 char query, no `qInTitle`. **Important free-tier limit:** the `q` operator supports OR/AND/NOT but **only one language at a time** for some accounts — we will run separate FR and EN queries instead of mixed.
  - `language` — comma-separated ISO codes. Free plan accepts up to 5 languages per call.
  - `country` — comma-separated ISO 3166-1 alpha-2 codes. **Free plan: up to 5 countries per call.** Plan exceeded → 422.
  - `category` — comma-separated from newsdata.io taxonomy: `business, crime, domestic, education, entertainment, environment, food, health, lifestyle, other, politics, science, sports, technology, top, tourism, world`.
  - `size` — page size. Free plan max = **10**. Default our fetcher to `10` always (one call = one credit, so always max-out the page).
  - `page` — pagination cursor returned in `nextPage` of prior response. **We will not paginate on free** (each page = another credit; not worth it under 200/day).
- Response shape: `{ status: 'success', totalResults: int, results: Article[], nextPage: string|null }`.
- Article shape (fields we'll consume — `null`-safe):
  - `article_id` (provider-stable id)
  - `title`
  - `link`
  - `description`, `content`
  - `pubDate`
  - `image_url`
  - `source_id`, `source_name`, `source_url`, `source_icon`, `source_priority`
  - `country` (array, ISO codes lowercase)
  - `category` (array)
  - `language`
  - `creator` (array)

### 1.3 Defensive guards

- 200/page-size > 10 silently returns 10 — do not trust input. Clamp before request.
- `q` with parens that don't balance returns 422. Validate at the admin-save layer (see §4).
- `status: 'error'` payloads return HTTP 200 with `code: 'RateLimitExceeded'` / `'UnauthorizedKey'`. Always check `$response->json('status')` even on HTTP 2xx.
- newsdata.io rate-limit policy: **30 requests / 15 minutes** on free plan in addition to the 200/day cap. Our 7-minute cadence (§3) stays under both ceilings.
- Empty `results` array is valid and is **not** a failure — treat as `status: 'ok', returned: 0`.

---

## 2. Credit accounting

### 2.1 Storage choice — daily Cache counter, **not** a new table

We already have `grimba_live_news_provider_runs` to record every call. We do **not** need a second table to compute credits used today — it's just `count(provider='newsdata-io' AND status != 'skipped' AND started_at >= startOfDay)`. That's the **authoritative** counter (survives cache flush, redis restart, etc.).

However, **hot-path callers** (the fetcher itself, the admin page, the manual "Run now" button) need a cheap pre-flight check. Use a Laravel `Cache::remember`/`Cache::increment` daily counter as a **fast guard**, falling back to the DB count as the **canonical** number.

```
Cache::increment('grimba_newsdata_credits:'.now()->utc()->toDateString());
```

Key resets implicitly at UTC midnight (per-day key) and has a TTL of 36h to survive timezone slop. The fetcher writes the cache key **after** every successful request (success or rate-limited HTTP — anything that consumed a credit). The admin page **reads from DB** for display (cache is best-effort, DB is truth).

### 2.2 Hard-stop logic

Before each call:

```
$canonical = DB::table('grimba_live_news_provider_runs')
    ->where('provider', 'newsdata-io')
    ->where('status', '!=', 'skipped')
    ->where('started_at', '>=', now()->utc()->startOfDay())
    ->count();

$cached = (int) Cache::get('grimba_newsdata_credits:'.now()->utc()->toDateString(), 0);

$creditsUsed = max($canonical, $cached);

if ($creditsUsed >= $this->dailyCreditBudget()) {
    return [$this->skipped('newsdata-io', $query, 'newsdata.io daily credit budget reached.')];
}
```

Headroom: hard-default the budget setting to **190** even though the upstream cap is 200. Leaves a 10-credit operator buffer for the manual "Run now" button and for retries on transient 5xx.

### 2.3 Why not a new `grimba_provider_credits` table?

- Adds a migration and a write path that duplicates information already on `grimba_live_news_provider_runs`.
- Every other provider gets free credit-accounting from that table too — building a provider-agnostic helper (`GrimbaProviderCredits::used($provider, $window)`) is the **leverage** play. The newsdata.io ticket is the right place to ship that helper as a side benefit.

**Decision: ship a small `App\Support\GrimbaProviderCredits` helper, no new table.**

---

## 3. Schedule strategy

### 3.1 Budget math

- 200 credits/day ÷ 24 hours = ~8.3 credits/hr.
- Reserve **10-15 credits** for manual "Run now" + retries → effective auto budget = **185-190 credits/day**.
- Spread evenly: 1440 min/day ÷ 185 calls ≈ **7.8 minutes** between calls.
- Run **2 queries per cron tick** (one FR-leaning, one EN-leaning) every **8 minutes** = 180 credits/day. Sweet spot under the limit and gives the cadence enough volume.

Alternative: 1 query every 4 minutes (single-language alternating) = 360 calls/day — **rejected** (exceeds budget). Stay at 2-per-8-min.

### 3.2 Cron expression

```
*/8 * * * *
```

But — and this matters — the existing `breaking_live` cron at `*/15 * * * *` already invokes `grimba:fetch-breaking` (which itself iterates providers including newsdata.io once we add it to `grimba_breaking_providers`). Two paths possible:

**Option A — share the breaking_live cron (recommended for sprint 1).**
Keep `*/15 * * * *`. Inside `GrimbaNewsdataIoFetcher::fetch()`, run **2 queries per tick** = 96 calls/day. Well under budget. Simpler — no new cron.

**Option B — dedicated newsdata cron at `*/8` for full 180-credit utilization.**
Adds a second `grimba_schedule_command('breaking_newsdata', 'grimba:fetch-breaking --provider=newsdata-io')` line in `routes/console.php`. Use this when ground-truth shows we want more newsdata volume (e.g. when other providers are paused or budget-stressed).

**Ship Option A. Add the Option B cron only if a later sprint shows under-utilization.** This is documented as a follow-up sprint at the bottom.

### 3.3 routes/console.php addition

After the existing `breaking_live` block (line ~75), add (Option B variant — wired but `when()`-guarded so it stays off by default):

```php
grimba_schedule_command('breaking_newsdata', 'grimba:fetch-breaking --provider=newsdata-io')
    ->cron('*/8 * * * *')
    ->onOneServer()
    ->withoutOverlapping(8)
    ->runInBackground()
    ->when(fn () => (bool) setting('grimba_newsdata_io_dedicated_cron', false)
                  && (bool) setting('grimba_breaking_active', true)
                  && (bool) setting('grimba_newsdata_io_active', false));
```

The dedicated cron is **off by default** (`grimba_newsdata_io_dedicated_cron=false`). Admin toggle in §4. The shared `breaking_live` cron is the day-1 path.

### 3.4 Register in `GrimbaAutomationMonitor::jobs()`

Add entry so the admin cockpit shows newsdata.io alongside the others:

```php
'breaking_newsdata' => [
    'label' => 'newsdata.io live lane',
    'command' => 'grimba:fetch-breaking --provider=newsdata-io',
    'expected_minutes' => 15,
],
```

---

## 4. Admin UI — `resources/views/grimba-admin/newsdataio/index.blade.php`

### 4.1 Structure (mirror `grimba-admin/newsapi/index.blade.php`)

Cinematic, light/dark via existing `grimba-admin-*` classes. Same hero + card layout. Section order:

1. **Hero** — `"newsdata.io"` brand (admin-only — exempt from NobuAI rule per §5 below), tagline, status chip ("ready" / "paused" / "key missing").
2. **Stat grid (4 columns):**
   - **Credits today** — `{used}/{budget}` with progress bar. Color: green <70%, amber 70-90%, red 90%+.
   - **Calls today (DB authoritative)** — `count(provider='newsdata-io')` since `startOfDay()`.
   - **24h ingested / deduped** — sum from `grimba_live_news_provider_runs`.
   - **24h failures** — count where `status='failed'`.
3. **Form (POST /admin/grimba/newsdataio):**
   - **API key** — text input, masked, paste-friendly. Save → `grimba_newsdata_io_key`.
   - **Active toggle** — on/off. Save → `grimba_newsdata_io_active`.
   - **Daily credit budget** — number, 1..200, default 190.
   - **Max calls per run** — number, 1..6, default 2.
   - **Cadence** — readonly label ("Runs every 15 minutes via `breaking_live`"). If `dedicated_cron`, show `"every 8 min"`.
   - **Dedicated cron toggle** — checkbox, default off. Save → `grimba_newsdata_io_dedicated_cron`.
   - **Queries** — textarea, newline-separated. Default value seeded with 6 queries (FR x 3, EN x 3) covering Africa, breaking, politics. Validate balanced parens on save.
   - **Countries** — comma-separated alpha-2 codes, default `fr,sn,ci,ml,cm`. Validate max 5.
   - **Languages** — comma-separated, default `fr,en`. Validate max 5.
   - **Categories** — multi-select from newsdata.io taxonomy. Default `top,politics,world`.
   - **Auto-publish** — already exists at `grimba_ingest_auto_publish` global. Show as readonly indicator only.
4. **Test button (POST /admin/grimba/newsdataio/test)** — fires one call against `/latest` with current settings, returns first 5 titles + total + remaining credits. **Costs 1 credit.** Warn in copy.
5. **Run now button (POST /admin/grimba/newsdataio/run)** — `Artisan::call('grimba:fetch-breaking', ['--provider' => 'newsdata-io'])`. Show summary.
6. **Recent runs table** — last 12 rows of `grimba_live_news_provider_runs WHERE provider='newsdata-io'` — provider, status, returned, ingested, deduped, started_at.
7. **Footer copy** — "newsdata.io free plan: 200 credits/day. Each request consumes 1 credit and returns up to 10 articles. We stay on free until revenue covers a subscription (Vader directive 2026-05-16)."

### 4.2 Route bindings — new file `platform/themes/echo/functions/grimba-admin-newsdataio.php`

Mirror `grimba-admin-newsapi.php`. Routes:

- `GET  /admin/grimba/newsdataio`         — form
- `POST /admin/grimba/newsdataio`         — save settings
- `POST /admin/grimba/newsdataio/test`    — 1-call probe
- `POST /admin/grimba/newsdataio/run`     — manual fetch

Register dashboard menu item under "GrimbaNews" parent with badge if credits >= 90% used.

### 4.3 NobuAI branding compliance

**Admin pages are exempt** from the NobuAI provider-name hiding rule per global CLAUDE.md (`feedback_nobuai_model_branding.md`). Saying "newsdata.io" on `/admin/grimba/newsdataio` is correct and expected.

**Public surfaces:** the brand "newsdata.io" must never appear. Articles ingested via this provider surface only the publisher's name (Reuters, AFP, RFI, …) — exactly the same pipeline as Webz/GDELT. The `news_sources` row is keyed on the **publisher**, not on newsdata.io itself. No reader-facing template needs a change.

---

## 5. Public surface — none

Confirmed by directive. Articles enter via:

- `posts.source_id` → `news_sources` row of the actual publisher (Reuters, AFP, etc.)
- `posts.source_name` → publisher name
- `posts.editorial_region` → set by existing `GrimbaCategoryClassifier` / country inference
- `posts.story_cluster_id` → set by existing `GrimbaRssPoller::findOrFormCluster()` so dedupe and ground-news-style multi-source clustering work identically to other providers

No new public view, no new public route, no NobuAI translation override needed. **Done.**

---

## 6. Dedupe vs other providers

Reuters, AFP and other wire services surface from newsdata.io, Webz, GDELT, and Google News alike. The existing `GrimbaArticleDedupe::hasSeen()` already handles:

- **URL-hash dedupe** — `sha1($url)` matched against `newsapi_items.article_url_hash` and `grimba_live_news_items.article_url_hash`.
- **Canonical-URL hash dedupe** — `GrimbaUrlCanonicalizer` strips trackers and matches across providers.
- **Title × source name+domain dedupe** — `hasMatchingPostTitle()`.

That stack is sufficient for newsdata.io if we feed it the same fields. Two extras to add:

### 6.1 Provider-prefixed external ID column

newsdata.io's `article_id` is provider-stable. Store it on `grimba_live_news_items.provider_item_id` (column already exists — see `GrimbaLiveNewsFetcher` line 564). Prefix with `newsdata-io:` to disambiguate from Webz UUIDs and GDELT hashes:

```
'provider_item_id' => 'newsdata-io:' . ($article['article_id'] ?? sha1($url)),
```

Add a uniqueness index on `(provider, provider_item_id)` in a new migration so the same newsdata.io article fetched twice on the same tick can't double-ingest even if the URL canonicalizer trips.

### 6.2 Reject if URL host matches a publisher we already ingested same-day

Light-touch: add an extra check inside `ingestArticle()` — if `news_sources.id` for this publisher has any post in the last 4 hours with a normalised-title Jaccard ≥ 0.7, skip and count as `deduped`. **Defer to a follow-up sprint** — the existing dedupe is already broad enough that wire-service double-coverage is unlikely to hurt v1.

---

## 7. Migrations + settings keys + env vars

### 7.1 No new tables required

Both writes (`grimba_live_news_items` and `grimba_live_news_provider_runs`) already exist and accept the schema we need.

### 7.2 One small migration (additive index)

`2026_05_17_120000_add_provider_item_id_unique_index_to_grimba_live_news_items.php`:

```php
Schema::table('grimba_live_news_items', function (Blueprint $table): void {
    $table->index(['provider', 'provider_item_id'], 'grimba_live_items_provider_item_idx');
});
```

(Skip if the index already exists — wrap in `Schema::hasIndex` if your Schema version supports it; otherwise `try/catch` the duplicate-index error or use a `DB::statement` raw `CREATE INDEX IF NOT EXISTS` on MySQL 8 / MariaDB 10.5+.)

### 7.3 Settings keys (Botble `setting()` store)

| Key | Type | Default | Purpose |
|---|---|---|---|
| `grimba_newsdata_io_key` | string | `''` | API key (fallback to env) |
| `grimba_newsdata_io_active` | bool | `false` | Master on/off |
| `grimba_newsdata_io_daily_credit_budget` | int | `190` | Hard cap, 1..200 |
| `grimba_newsdata_io_max_calls_per_run` | int | `2` | Per-tick concurrency, 1..6 |
| `grimba_newsdata_io_queries` | text | (6 default) | Newline-separated |
| `grimba_newsdata_io_languages` | string | `'fr,en'` | Max 5 |
| `grimba_newsdata_io_countries` | string | `'fr,sn,ci,ml,cm'` | Max 5 |
| `grimba_newsdata_io_categories` | string | `'top,politics,world'` | newsdata.io taxonomy |
| `grimba_newsdata_io_timeout` | int | `12` | HTTP timeout (s) |
| `grimba_newsdata_io_connect_timeout` | int | `5` | HTTP connect timeout (s) |
| `grimba_newsdata_io_dedicated_cron` | bool | `false` | Opt into `*/8 * * * *` cron |
| `grimba_newsdata_io_page_size` | int | `10` | Clamped 1..10 (free plan) |

Plus extend the global provider list default:

```
grimba_breaking_providers = 'google-news,gdelt,webz,mediastack,newsdata-io'
```

### 7.4 .env vars

| Var | Default | Notes |
|---|---|---|
| `NEWSDATA_IO_KEY` | `` | Primary, read by setting fallback |
| `NEWSDATA_IO_BASE_URL` | `https://newsdata.io/api/1` | Override-able for sandbox |

(`.env.example` gets both, with comments.)

---

## 8. Sprint sequence (12-20 sprints, ≤ 90 min each)

Order: **foundation → credit accounting → ingester → schedule → admin UI → dedupe → tests → docs.**

### Foundation

**S-NDI-01 — Inventory + provider taxonomy update** (45m)
- Add `'newsdata-io'` to the default `grimba_breaking_providers` setting.
- Extend `GrimbaLiveNewsFetcher::fetchAll()` `match` arm to dispatch into `GrimbaNewsdataIoFetcher`.
- Update `GrimbaAutomationMonitor::jobs()` with a `breaking_newsdata` entry.
- No new files; just registrations and dispatch.
- Smoke: `grimba:fetch-breaking --provider=newsdata-io` returns a `skipped` row ("not implemented yet" sentinel).

**S-NDI-02 — Provider-item index migration** (30m)
- New migration `2026_05_17_120000_add_provider_item_id_unique_index_to_grimba_live_news_items.php`.
- Idempotent (skip if index exists).
- Run, verify, write a migration test that calls up+down.

**S-NDI-03 — Settings keys defaults baked in** (45m)
- Add the 12 setting keys from §7.3 with default values pre-registered in the existing `grimba` settings seeder (if one exists) or in a one-shot data-migration command.
- Add `NEWSDATA_IO_KEY` + `NEWSDATA_IO_BASE_URL` to `.env.example` with comments.
- Sanity: `setting('grimba_newsdata_io_daily_credit_budget')` returns 190 in a fresh install.

### Credit accounting

**S-NDI-04 — `GrimbaProviderCredits` helper** (60m)
- New class `App\Support\GrimbaProviderCredits`.
- `used(string $provider, Carbon $since): int` — DB-backed authoritative.
- `bump(string $provider): void` — increments `Cache::increment('grimba_provider_credits:'.$provider.':'.date('Y-m-d'))` (UTC), TTL 36h.
- `fast(string $provider): int` — `max(DB count, cache value)`. Used for hot-path skip decisions.
- Unit tests: counting, midnight reset, cache fallback, DB authority.

### Ingester

**S-NDI-05 — Skeleton `GrimbaNewsdataIoFetcher` (no network)** (75m)
- Class created with constructor injecting `GrimbaLiveNewsFetcher` (for reuse of `ingestMany` via a `public` opener — see 1.1) and `GrimbaProviderCredits`.
- All public methods listed in §1.1 stubbed; `fetch()` returns a `skipped` row.
- Unit tests for `dailyCreditBudget()`, `maxCallsPerRun()`, `creditsRemainingToday()`.

**S-NDI-06 — newsdata.io HTTP call + normaliser** (90m)
- Implement `fetchQuery(string $query): array` — single `Http::get` against `/latest` with all params.
- Implement `normaliseNewsdataArticle(array $article): array` returning the canonical normalized shape (matches `normaliseWebzArticle()` etc.).
- Treat `status='error'` payloads as failed even on HTTP 200.
- Handle 429 / rate-limit-exceeded as a `failed` row with a clear error message.
- Recorded telemetry: `startLiveRun()` / `finishLiveRun()` (reuse existing).
- Smoke test (mocked Http::fake) returning 3 articles → 3 ingested.

**S-NDI-07 — Credit accounting wired into fetcher** (45m)
- Pre-flight check: `if (GrimbaProviderCredits::fast('newsdata-io') >= $this->dailyCreditBudget()) return $skipped`.
- After every non-skipped call: `GrimbaProviderCredits::bump('newsdata-io')`.
- Unit test: 191st call in a day → skipped with reason "daily credit budget reached".

**S-NDI-08 — Per-tick query rotation** (45m)
- `fetch()` iterates over `queries()` round-robin, capped at `maxCallsPerRun()`.
- Pick starting index based on `creditsUsedToday() % count(queries)` so the rotation surfaces all configured queries across a day even if cron ticks lose calls to budget.
- Unit test: 6 queries × 2 calls/run → over 12 ticks, all 6 queries get hit at least 4 times.

### Schedule

**S-NDI-09 — Shared cron path validated** (30m)
- Verify `grimba_schedule_command('breaking_live', ...)` fires `GrimbaNewsdataIoFetcher` once newsdata-io is in `grimba_breaking_providers`.
- Production cron-tail-style smoke: trigger one run, observe a `grimba_live_news_provider_runs` row with `provider='newsdata-io'`.

**S-NDI-10 — Dedicated `*/8` cron (gated, off by default)** (30m)
- Add the new `breaking_newsdata` scheduler block to `routes/console.php` per §3.3.
- Gated on `grimba_newsdata_io_dedicated_cron` (default `false`).
- Sanity: `php artisan schedule:list` shows it as `inactive` until the setting is flipped.

### Admin UI

**S-NDI-11 — Theme function file + route shell** (75m)
- New file `platform/themes/echo/functions/grimba-admin-newsdataio.php`.
- Routes: GET (form), POST (save), POST /test, POST /run.
- Dashboard menu item under GrimbaNews parent.
- View references `grimba-admin/newsdataio/index` (next sprint).
- Smoke: visit `/admin/grimba/newsdataio` → 200 with placeholder.

**S-NDI-12 — Blade form + stat grid** (90m)
- New file `resources/views/grimba-admin/newsdataio/index.blade.php` per §4.1.
- Render: hero, 4-stat grid, settings form, recent runs table.
- Reuse existing `grimba-admin-*` classes (no new CSS).

**S-NDI-13 — Save handler + validation** (75m)
- POST handler validates: alpha-2 country code list ≤ 5; language list ≤ 5; categories from a fixed allow-list; queries non-empty + balanced parens; numbers clamped.
- Save via `SettingStore`.
- Success flash, validation errors echoed inline.

**S-NDI-14 — Test + Run-Now buttons** (75m)
- `/test` route: 1 call against `/latest` with first configured query; return first 5 titles + remaining credits.
- `/run` route: `Artisan::call('grimba:fetch-breaking', ['--provider' => 'newsdata-io'])`.
- Surface artisan output (truncated) on redirect-back flash.

**S-NDI-15 — Credit progress bar + warning** (45m)
- Progress bar component already exists in NewsAPI page — clone styling.
- Color rule: green <70%, amber 70-90%, red 90%+.
- If `creditsRemainingToday() < 10` show inline warning "Approaching daily limit — manual runs may be rejected."

### Dedupe

**S-NDI-16 — Provider-prefixed `provider_item_id`** (45m)
- Implement §6.1 — prefix with `newsdata-io:`.
- Backfill not needed (no prior rows).
- Unit test: ingesting the same newsdata.io payload twice in a row produces 1 row + 1 `duplicate` summary.

**S-NDI-17 — (Optional, deferable) cross-provider same-day title-similarity guard** (60m)
- Implement §6.2 only if there's time. Otherwise close this sprint as `deferred` with a 1-line note in next-session prompt.

### Tests

**S-NDI-18 — Integration test fixture** (75m)
- `tests/Feature/GrimbaNewsdataIoIngestTest.php`.
- Mocks newsdata.io with `Http::fake` returning 10 articles, 1 of which is a known duplicate-URL of an existing newsapi-item.
- Asserts: 9 ingested, 1 deduped, 1 `grimba_live_news_provider_runs` row, 1 credit consumed via the helper.

**S-NDI-19 — Credit-budget E2E** (60m)
- Test: with budget=2 and `Http::fake` returning a successful payload every call, the 3rd call in the same day is `skipped` with reason "daily credit budget reached".
- Test: cache flushed → DB still authoritative → still skipped at 3rd call.

### Docs

**S-NDI-20 — Docs + handoff** (45m)
- Append a `## newsdata.io` section to `docs/GRIMBANEWS_NEWSAPI_CONFIG_GUARD_2026_05_11.md` (or a new sibling doc) covering admin route, settings keys, env vars, expected daily ceiling, troubleshooting (rate-limit, key-revoked, country-cap-exceeded).
- Add a one-liner to `CLAUDE.md` listing the new provider.
- Add row to `project_grimbanews_next_prompt.md` resume memory.

---

## 9. Master sprint queue addition

Append to `KAIZEN_FEATURE_QUEUE_V02.md` (or the GrimbaNews-specific master queue if that's the active doc) under a new section:

```
### GrimbaNews — newsdata.io integration (S-NDI-01 → S-NDI-20)

Ready to pick up. Driven by Vader directive 2026-05-16. Plan doc:
docs/GRIMBANEWS_NEWSDATAIO_INTEGRATION_PLAN.md

- [ ] S-NDI-01  Provider taxonomy update + dispatcher arm                  (45m)
- [ ] S-NDI-02  Provider-item unique index migration                       (30m)
- [ ] S-NDI-03  Settings keys + .env.example defaults                      (45m)
- [ ] S-NDI-04  GrimbaProviderCredits helper (DB + cache)                  (60m)
- [ ] S-NDI-05  GrimbaNewsdataIoFetcher skeleton (no network)              (75m)
- [ ] S-NDI-06  newsdata.io HTTP call + article normaliser                 (90m)
- [ ] S-NDI-07  Credit-accounting wired into fetcher                       (45m)
- [ ] S-NDI-08  Per-tick query rotation                                    (45m)
- [ ] S-NDI-09  Shared breaking_live cron path validated                   (30m)
- [ ] S-NDI-10  Dedicated */8 cron (gated, off by default)                 (30m)
- [ ] S-NDI-11  Admin route shell + dashboard menu item                    (75m)
- [ ] S-NDI-12  Blade form + stat grid                                     (90m)
- [ ] S-NDI-13  Save handler + validation                                  (75m)
- [ ] S-NDI-14  Test + Run-Now admin buttons                               (75m)
- [ ] S-NDI-15  Credit progress bar + warning copy                         (45m)
- [ ] S-NDI-16  Provider-prefixed provider_item_id dedupe                  (45m)
- [ ] S-NDI-17  (Optional) Same-day cross-provider title-similarity guard  (60m)
- [ ] S-NDI-18  Integration test (Http::fake fixture)                      (75m)
- [ ] S-NDI-19  Credit-budget E2E test                                     (60m)
- [ ] S-NDI-20  Docs + resume-memory handoff                               (45m)

Total: ~21h of focused work. Ship at one fleet's normal cadence.
```

---

## 10. Risks specific to newsdata.io's free tier

| Risk | Mitigation |
|---|---|
| `q` operator may be restricted on some free accounts to single-language scope. | Run separate FR and EN queries; do not OR languages inside one `q`. |
| `country` param capped at 5 per call. | Validate at save layer; default list = 5 codes. |
| `size` capped at 10 — paginating costs another credit per page. | Hard-disable pagination on free plan. |
| 30 req / 15 min rate ceiling **in addition** to 200/day. | `*/8` cadence stays at ~30 req / 4 hrs — well under both. Shared `*/15` cadence even safer. |
| Latency: newsdata.io free can return 2-5s under load. | `timeout=12`, `connect_timeout=5`; if timeout, telemetry row gets `failed` and skips next-tick won't accumulate. |
| Article body is **short** on free plan (~200 chars). | Same pipeline as other providers — `grimba:fetch-full-articles` scrapes the publisher URL afterwards. No special handling. |
| Provider returns Reuters/AFP duplicates already in our DB via NewsAPI/Webz. | Three-layer dedupe (URL hash, canonical-URL hash, title+source) already handles this; provider-prefixed `provider_item_id` adds a 4th layer. |
| Image URLs occasionally hot-link to publisher CDNs that 403 our user agent. | Existing image-provenance flow already records extract errors; not a blocker. |
| Free key revocation if T&Cs violated (e.g. caching results > 30 days publicly). | We display fresh; no archived API responses. Compliant by construction. |
| API surface changes (newsdata.io has bumped versions before). | Pin to `/api/1/latest`; one place to update via `NEWSDATA_IO_BASE_URL` env. |

---

## End of plan
