# GrimbaNews — Reader-Language Surfacing + Auto-Translate Rules Plan

> **Codename:** `S-LSAT` (Language Surfacing + Auto-Translate)
> **Owner panel:** Rajesh Kumar (rules engine), Nina Patel (reader serving),
> Alex Morgan (admin form), Larry Ellison (schema), Sara Kim (QA),
> Lisa Nguyen (ingest plumbing), Sara Chen (CISO check — no PII in rule logs),
> Zen / Echo / Mnemo (audit panel), Steve Jobs (UX signoff), Lucy Leai (editorial),
> Vader (directive owner).
>
> **Vader directive 2026-05-18:**
> *"Mythos build a serious and scalable plan to make sure that articles language
> are tagged in english or french so that when a user changes language, all
> articles in french are displayed based on the user language selection and
> similarly for english articles. We want to surface articles based on the user
> language selection. Same for breaking news (we aggregate breaking news in
> french and english) and display articles in french that are in french and
> english that are natively in english. NobuAI translation via NobuTranslate
> will translate all African-related articles and selectively translate
> most-viewed articles or popular articles to the other language (let's say an
> article from Le Monde has 500+ views, NobuAI should automatically translate
> it). We should also have in the admin dashboard article translation
> conditions that can be updated."*
>
> **Status:** draft for execution. 21 sprints, ~28h. Audit / big / polish cadence.
>
> **Foundation reused — NOT re-architected:**
> - `posts.original_language` (varchar 5, indexed) — filled by `GrimbaLanguageDetector` at ingest + nightly `grimba:backfill-language` cron.
> - `posts.editorial_region` (africa / europe / americas / international) — via `GrimbaRegionScope` global Eloquent scope.
> - `posts.views` (integer counter) — already present, already indexed via Botble Blog defaults.
> - `grimba_post_translations` (post_id, locale UNIQUE, translated_name / _description / _content / _summary / _driver / _at) — durable per-locale work map.
> - `posts.translated_*` mirror columns — fast in-row cache.
> - `GrimbaTranslationPresenter::rankForTargetLocale()` — currently **ranks** 0 (native) → 1 (translated) → 2 (labeled wrong locale) → 3 (NULL). Does **not filter**.
> - `GrimbaTranslator` provider chain (NobuTranslate first).
> - `GrimbaTranslatePending` artisan command + scheduled cron (`translate_fr` 10m, `translate_en` 10m, limit 50 each).
> - `/admin/grimba/translation-map` page — read-only.

---

## Why this is its own fleet (not a continuation of S-LANG)

S-LANG-01 → S-LANG-16 built the *tagging* and *translation work-map* foundation:
every post now carries `original_language`, every dossier carries `primary_language`,
every NobuAI summary carries `summary_nobuai_locale`, and the durable
many-to-many key/map (`grimba_post_translations`) is populated and atomic.

What S-LANG did **not** do, and what `S-LSAT` adds:

1. **Hard reader-side filtering** — S-LANG-05 made NULL-language posts *rank
   last*, but EN-only-no-translation content still surfaces to FR readers
   (rank 2). Vader's directive is "display articles in french that are in
   french" — strict filter, not a rank.
2. **Locale-aware breaking news** — `/breaking` and the urgency banner currently
   mix FR + EN providers freely. The keyword list at
   `GrimbaHomeFeed::breakingKeywords()` is bilingual (`'breaking news'` +
   `'dernière minute'`), so the ticker today is intrinsically mixed.
3. **Rule-driven auto-translate** — `GrimbaTranslatePending` translates *any*
   non-target post with `--limit=50`. Vader wants priorities: African region
   **must** be both FR+EN; popular articles ≥ 500 views auto-translate.
4. **Admin UI to tune the rules** — currently you'd edit the cron flag in
   `routes/console.php`. Operator should be able to tune thresholds without
   code changes.

This fleet wires the existing parts together; it does **not** invent new
storage for the data side. Storage adds limited to: one settings group
(thresholds), one optional rules table (provenance + audit trail), and a
denormalized `translation_priority` int on `posts` for query performance.

---

## Architecture — How the 4 Pillars Connect

```
+-------------------+
| Reader cookie     |  grimba_lang = "fr" | "en"
| Locale switcher   |
+---------+---------+
          |
          v
+-----------------------------+        +-------------------------+
| GrimbaTranslationPresenter  |<------>| filter mode (strict/    |
|   ::targetLocale()          |        | rank) — per-surface,    |
|   ::orderForTargetLocale()  |        | settings-driven         |
|   ::filterForTargetLocale() |        +-------------------------+
|     ^new in S-LSAT-04^      |
+-----+-----------------+-----+
      |                 |
      | Eloquent scope  |  (every list query in GrimbaHomeFeed,
      | applied         |   loop.blade, category.blade, search,
      |                 |   latest, breaking, dossiers, vault, etc.)
      v                 v
+----------+      +-----------+      +------------------+
|  posts   |      |  story_   |      | breaking pages   |
|  table   |      |  clusters |      |  + urgency banner|
+----+-----+      +-----+-----+      +---------+--------+
     |                  |                      |
     |   original_      |  primary_            | both routes call
     |   language       |  language            | GrimbaHomeFeed::
     |   editorial_     |                      | breaking($locale, ...)
     |   region         |                      | (new param in S-LSAT-08)
     |   views          |                      |
     v                  v                      v
+-----------------------+
|  S-LSAT rule engine   |   App\Support\GrimbaTranslationRules
|  - region=africa →    |
|     enqueue BOTH      |
|  - views >= 500 →     |
|     enqueue opposite  |
|  - any other op rule  |
+----------+------------+
           |
           v
+-----------------------+      +-------------------+
| translation_priority  |----->| GrimbaTranslate   |
| int on posts (0/5/10) |      | Pending           |
| denormalized → DESC   |      | --by-rule loop    |
+-----------------------+      +-------------------+
           ^
           |   (settings keys + optional rules table read by both
           |    the rule engine and the admin form)
+----------+------------+
| /admin/grimba/        |
| translation-rules     |  Botble Setting backed; provenance audit
+-----------------------+    on save (Sara Chen / CISO)
```

**Plain-English flow:**

1. A reader on `/` picks FR. The cookie `grimba_lang=fr` is set and the
   request locale is `fr`.
2. Every list query goes through `GrimbaTranslationPresenter::filterForTargetLocale()`
   (new). In **strict** mode (`grimba_lang_strict_surface=1` default ON for
   home/breaking/latest/dossiers) it adds:
   `WHERE original_language = 'fr' OR EXISTS (translation row in fr)`.
   In **rank** mode (current behavior, fallback for tail surfaces like
   search if Vader wants to keep results comprehensive) it just orders.
3. The breaking ticker calls `GrimbaHomeFeed::breaking($locale = …)` —
   keyword set is filtered to the locale's half of `breakingKeywords()`,
   and the SQL `WHERE` adds the strict locale filter.
4. Independently, a cron `grimba:translate-by-rule` runs every 15m. It
   inspects `posts` and:
   - For every post with `editorial_region='africa'` missing one of FR/EN,
     it bumps `translation_priority` to 10 (highest).
   - For every post with `views >= grimba_lang_popularity_threshold` (default
     500) missing the opposite-locale translation, it bumps
     `translation_priority` to 5.
   - It then re-invokes `grimba:translate-pending` with an *order-by-priority*
     selection so the African + popular articles are translated first.
5. The admin form at `/admin/grimba/translation-rules` (new) lets the operator
   tune: popularity threshold, region-must-be-both list, strict-mode toggle
   per surface, per-region popularity threshold override, daily rule-engine
   budget cap (to prevent provider quota burst).

---

## Schema Decisions

### What we add (small)

| Change | Where | Why |
|---|---|---|
| `posts.translation_priority` | new `tinyInteger NOT NULL DEFAULT 0` column on `posts`, indexed | Lets `GrimbaTranslatePending` order by priority DESC without a JOIN. Denormalized; recomputed by the rule engine on each pass. |
| Settings group `grimba_lang_*` | Botble `settings` table (no migration — same pattern as `grimba_nobuai_*`) | Threshold values, surface toggles, region overrides. Consistent with existing `setting('grimba_*')` reads in `GrimbaEditorialCategories`, `GrimbaDossierLanguage`, `GrimbaNobuAi`. |
| `grimba_translation_rules` (**deferred until S-LSAT-19**) | optional table for *rule provenance* — when did the operator change a rule, who, from-value, to-value | Only added if we go past three settings keys. If we stay at ≤ 6 keys, settings + an audit-log row in the existing `grimba_translation_failures`-style pattern is enough. |

### What we deliberately **do not** add

- **No new translations table.** `grimba_post_translations` already handles
  per-locale work; doubling up would split the truth source.
- **No `posts.is_african` boolean.** `editorial_region='africa'` is already the
  source of truth.
- **No per-post `manual_translate_request` column.** The admin can flip
  `translation_priority` to 10 on demand from the existing post edit screen
  with a simple form field add (S-LSAT-17).

### Settings keys (the contract)

| Key | Type | Default | Where read |
|---|---|---|---|
| `grimba_lang_strict_surface` | bool | `true` | Presenter `filterForTargetLocale()` master switch |
| `grimba_lang_strict_home` | bool | `true` | per-surface override for `/` |
| `grimba_lang_strict_breaking` | bool | `true` | per-surface override for `/breaking` and urgency banner |
| `grimba_lang_strict_latest` | bool | `true` | per-surface override for `/latest` |
| `grimba_lang_strict_dossiers` | bool | `true` | per-surface override for `/dossiers` and dossier detail |
| `grimba_lang_strict_category` | bool | `false` | per-surface override for `/category/*` — left soft by default to avoid empty pages on thin categories |
| `grimba_lang_strict_search` | bool | `false` | per-surface override for `/search` — search stays comprehensive |
| `grimba_lang_popularity_threshold` | int | `500` | Rule engine — views ≥ N triggers cross-translation |
| `grimba_lang_popularity_threshold_africa` | int | `100` | Lower threshold for African region (popular African content gets priority sooner) |
| `grimba_lang_region_force_both` | string (CSV) | `africa` | Comma list of `editorial_region` values that MUST have both locales |
| `grimba_lang_rule_engine_daily_cap` | int | `500` | Max number of posts the rule engine will enqueue in 24h (prevents provider quota burst) |
| `grimba_lang_rule_engine_enabled` | bool | `true` | Master kill switch for the rule engine cron |
| `grimba_lang_tail_expander_enabled` | bool | `true` | Reader UI — show a "Lecture en anglais (12 articles)" expander at the bottom of strict-filtered lists |

---

## Sprint Sequence — `S-LSAT-01` → `S-LSAT-21`

**Cadence:** audit → big → polish (per `feedback_sprint_cadence_audit_big_polish.md`).
Each `*-big-*` sprint touches the rule engine or schema and gets a full Zen/Echo/Mnemo
audit run; `*-polish-*` sprints get a Zen-only review.

| Sprint | Type | Title | Est. | Depends on |
|---|---|---|---|---|
| S-LSAT-01 | audit | Inventory every list query that calls `GrimbaTranslationPresenter::orderForTargetLocale` + every place that touches `breaking()` | 45m | — |
| S-LSAT-02 | big | Add `filterForTargetLocale()` to presenter — strict mode that ANDs `original_language = target OR EXISTS (translation)` | 90m | 01 |
| S-LSAT-03 | big | Add settings-key reader (`GrimbaLanguageSettings` helper) — single class to read/cast all 13 keys with sane defaults | 60m | — |
| S-LSAT-04 | big | Wire `filterForTargetLocale()` into the 4 priority surfaces (home, latest, dossiers, breaking) — gated by per-surface settings | 90m | 02, 03 |
| S-LSAT-05 | polish | Tail expander partial — `partials/lang/tail-expander.blade.php` shows "Articles disponibles en anglais (N)" with disclosure | 60m | 04 |
| S-LSAT-06 | big | Breaking-news locale filter — `GrimbaHomeFeed::breaking($locale, $windowHours)` overload + keyword set per-locale | 90m | 03, 04 |
| S-LSAT-07 | polish | Urgency banner partial + `/breaking` route signature accept reader locale | 45m | 06 |
| S-LSAT-08 | big | `posts.translation_priority` migration + index | 30m | — |
| S-LSAT-09 | big | `App\Support\GrimbaTranslationRules` rule engine — pure-function evaluators, no I/O | 90m | 03, 08 |
| S-LSAT-10 | big | `grimba:translate-by-rule` artisan command — runs rules, bumps priority, then chains `grimba:translate-pending --order-by-priority` | 75m | 09 |
| S-LSAT-11 | big | Scheduler entry — `grimba_schedule_command('translate_by_rule', 'grimba:translate-by-rule', '*/15 * * * *')` + daily-cap guard | 45m | 10 |
| S-LSAT-12 | big | Extend `GrimbaTranslatePending` with `--order-by-priority` + `--respect-rule-cap` flags | 60m | 10 |
| S-LSAT-13 | polish | Admin route `/admin/grimba/translation-rules` — form scaffold + Botble menu item | 60m | 03 |
| S-LSAT-14 | big | Admin form view — `grimba-admin.translation-rules.index` with all 13 settings fields, validation, NobuAI brand purity | 90m | 13 |
| S-LSAT-15 | big | Admin save handler — POST endpoint, validates, writes settings, fires `Cache::forget('grimba_lang_settings')`, logs change to existing settings audit | 60m | 14 |
| S-LSAT-16 | polish | Live "preview rule outcome" — admin form shows projected enqueue count *before* save (read-only count query) | 60m | 14 |
| S-LSAT-17 | polish | Per-post override on the existing post-edit screen — `translation_priority` numeric input + radio "Force translate to FR/EN" | 60m | 08 |
| S-LSAT-18 | big | Tests — `tests/Feature/GrimbaLanguageSurfacingTest.php` (filter SQL correctness × 4 surfaces × 2 locales), `tests/Unit/GrimbaTranslationRulesTest.php` (rule predicates × 6 cases) | 90m | 04, 06, 09 |
| S-LSAT-19 | big | Tests — `tests/Feature/GrimbaTranslateByRuleCommandTest.php` (idempotency, daily-cap, priority ordering, dry-run) | 75m | 10, 11 |
| S-LSAT-20 | polish | Docs — append fleet outcomes to `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` + operator handoff at `docs/GRIMBANEWS_LANG_SURFACING_OPERATOR_HANDOFF.md` | 45m | 18, 19 |
| S-LSAT-21 | polish | Release smoke — 8-URL × 2-locale sweep proving strict filter works on home / breaking / latest / dossiers; admin form roundtrip | 60m | 20 |

**Total:** 21 sprints, ~24h 30m of focused engineering. With audit-panel
runs after each big sprint and the standard test-then-commit-then-push
overhead, plan ~27–30h end-to-end.

---

## Acceptance Criteria

### S-LSAT-01 — Audit
- A markdown table in this doc listing every file/line that calls
  `orderForTargetLocale` (11 known) + every reader of `breaking()` (3 known)
  + every list query that bypasses the presenter (target = 0).
- Confirms no list query writes its own `CASE WHEN original_language` instead
  of calling the presenter.

### S-LSAT-02 — `filterForTargetLocale()`
- New static method on `GrimbaTranslationPresenter` with same signature shape
  as `orderForTargetLocale($query, $target = null)`.
- Adds a `whereExists` for the join-table OR a `WHERE original_language = ?`
  branch — never both at the SQL level (avoids OR explosion).
- Unit test: `filterForTargetLocale($q, 'fr')` produces a query whose result
  set against a fixture with 4 FR-original, 2 EN-original-FR-translated, 3
  EN-only-no-translation, 1 NULL post returns **6 rows** (not 10).

### S-LSAT-03 — `GrimbaLanguageSettings`
- Class lives at `app/Support/GrimbaLanguageSettings.php`.
- Single `Cache::remember('grimba_lang_settings', 300, …)` so the rule engine
  doesn't hit the settings table on every post.
- Sane defaults if any setting is absent.
- Unit test: every key has the documented default.

### S-LSAT-04 — Wire into 4 priority surfaces
- `GrimbaHomeFeed::hero / breaking / latest / topNews / sections / regionalMix`
  all call `filterForTargetLocale()` after `orderForTargetLocale()` **iff**
  the matching per-surface setting is on.
- The `/dossiers` list does the same against `story_clusters.primary_language`.
- The /breaking route — and `partials/home/urgency-banner.blade.php` — pass
  through the new filter.
- Smoke: FR reader on `/` sees zero EN-only-no-translation cards. EN reader
  on `/` sees zero FR-only-no-translation cards.

### S-LSAT-05 — Tail expander
- Partial at `platform/themes/echo/partials/lang/tail-expander.blade.php`.
- Appears below filtered lists when `grimba_lang_tail_expander_enabled=true`
  and the count of opposite-locale-only-no-translation posts in the same
  recency window > 0.
- A11y: `<details>` element, summary keyboard-focusable, contents
  `lang="en"` (or `fr`) so screen readers switch voice.

### S-LSAT-06 — Breaking news locale
- `GrimbaHomeFeed::breaking()` accepts an optional `$locale` arg.
- Keyword set is split into `breakingKeywordsFr()` / `breakingKeywordsEn()`
  and unioned only in "international without strict" mode.
- Strict mode (default) uses only the locale's half + filters posts by
  `original_language = $locale OR EXISTS (translation in $locale)`.
- Cache key now includes `$locale`.

### S-LSAT-07 — Urgency banner locale
- `partials/home/urgency-banner.blade.php` passes `app()->getLocale()` to
  `GrimbaHomeFeed::breaking()`.
- `/breaking` route does the same in `routes/web.php:199` and `:258`.

### S-LSAT-08 — `translation_priority` column
- Migration `2026_05_18_NNNNNN_add_translation_priority_to_posts.php` adds
  `tinyInteger NOT NULL DEFAULT 0` + index.
- Existing posts default to 0; backfill happens on first `translate-by-rule`
  run, not in the migration (avoids long-running migration).

### S-LSAT-09 — `GrimbaTranslationRules`
- Class at `app/Support/GrimbaTranslationRules.php`, pure functions only:
  - `static needsTranslation(Post $post, string $locale, GrimbaLanguageSettings $settings): bool`
  - `static computePriority(Post $post, GrimbaLanguageSettings $settings): int`
  - `static targetLocales(Post $post, GrimbaLanguageSettings $settings): array` → returns subset of `['fr','en']` post must have
- 12 unit tests covering: African post needs both → priority 10; popular EN
  post → priority 5; popular African → priority 10; thin views below threshold
  → priority 0; NULL-language post → priority 0 (we don't translate NULL);
  manual `translation_priority>=10` override is preserved.

### S-LSAT-10 — `grimba:translate-by-rule` command
- Signature: `{--limit=100} {--dry-run} {--region=}` (`--region=` lets the
  operator force a single region for ad-hoc backfills).
- Algorithm: select up to `--limit` posts whose `(views >= threshold OR region in force-both)` AND missing a target translation; call `GrimbaTranslationRules::computePriority()`; write `translation_priority`; commit; then optionally chain into `grimba:translate-pending` via `Artisan::call` *unless* `--dry-run`.
- Daily-cap check: counts `translation_priority` writes in the last 24h
  against `grimba_lang_rule_engine_daily_cap`; returns SUCCESS with a
  "cap reached" message if exceeded.

### S-LSAT-11 — Scheduler
- New entry in `routes/console.php` using the existing `grimba_schedule_command`
  helper, every 15m, `withoutOverlapping()`.
- Gated by `setting('grimba_lang_rule_engine_enabled', true)`.

### S-LSAT-12 — `GrimbaTranslatePending` extension
- `--order-by-priority` flag adds `orderByDesc('translation_priority')` to
  the selection query, before the existing `orderByDesc('id')`.
- `--respect-rule-cap` flag has the command check the daily cap and exit
  early if it would breach.

### S-LSAT-13 — Admin route shell
- Route at `platform/themes/echo/functions/grimba-admin-translation-rules.php`
  (parallels existing `grimba-admin-translation-map.php`).
- Botble dashboard menu item under the same `grimba-root` parent, priority 65
  (right after translation-map at 64).

### S-LSAT-14 — Admin form view
- Blade at `platform/themes/echo/views/grimba-admin/translation-rules/index.blade.php`.
- All 13 settings keys, grouped (Strict surfaces / Auto-translate rules /
  Operational caps).
- Help-text on each field. **No mention of any external LLM provider** —
  drivers are referred to as "NobuAI providers" per
  `feedback_nobuai_model_branding.md`.

### S-LSAT-15 — Admin save handler
- POST to `grimba.translation-rules.save`, CSRF middleware, validates ints
  positive, booleans coerced.
- After save: `Cache::forget('grimba_lang_settings')` so the new values
  take effect within seconds.
- Logs to `storage/logs/grimba-settings-audit.log` (already used by
  GrimbaNobuAi pattern): `actor_id, key, from, to, at`.

### S-LSAT-16 — Live preview
- On the admin form GET, runs a read-only count: "with current settings, the
  next rule-engine pass would enqueue **X** posts (Y African + Z popular)."
- After save, redirect with success flash showing the same projection for
  the **new** values.

### S-LSAT-17 — Per-post override
- Existing Botble Post edit screen gets a "Translation priority" numeric
  field (range 0-10) and a "Force translate to" radio (none / FR / EN).
- "Force translate to" persists by writing `translation_priority=10` and
  setting a `translated_to_pending` hint column? — actually we already have
  the durable map; simpler to just bump priority and let the cron pick it
  up at the next 15m pass.
- Tooltip explains: 0 = default rules, 5 = popular tier, 10 = must-translate.

### S-LSAT-18 — Filter + rule tests
- `tests/Feature/GrimbaLanguageSurfacingTest.php`:
  - FR reader on home with 4 FR / 2 EN-with-FR-translation / 3 EN-only-no-translation / 1 NULL fixture: lists return 6 items.
  - EN reader on `/breaking`: ticker excludes FR-only posts.
  - Strict toggle OFF: same fixture returns 10 items (current behavior).
- `tests/Unit/GrimbaTranslationRulesTest.php`: 12 cases above.

### S-LSAT-19 — Command tests
- `tests/Feature/GrimbaTranslateByRuleCommandTest.php`:
  - Fixture of 200 posts: 50 African no-translation, 80 popular EN, 70 normal.
  - Dry-run produces zero DB writes.
  - Full run respects `--limit=30` and picks the 30 highest-priority.
  - Re-running within 24h respects the daily cap.

### S-LSAT-20 — Docs
- New rows in the master plan's S-LSAT band table (template stub below).
- Operator handoff at `docs/GRIMBANEWS_LANG_SURFACING_OPERATOR_HANDOFF.md`
  with: how to flip strict mode per surface, how to tune the popularity
  threshold, how to interpret the live preview, how to reset
  `translation_priority` on a stuck post.

### S-LSAT-21 — Release smoke
- 8-URL × 2-locale sweep: `/`, `/breaking`, `/latest`, `/dossiers`,
  `/categories/politique`, `/search?q=climate`, `/admin/grimba/translation-rules`,
  `/admin/grimba/translation-map`.
- Each URL on each locale screenshot + DOM-assert "no FR titles when locale=en
  on strict surfaces".

---

## Risks Specific to This Fleet

| # | Risk | Mitigation |
|---|---|---|
| R1 | **Provider quota burst** — a viral African article crosses 500 views during peak traffic; rule engine enqueues a flood at the same 15-min boundary. | `grimba_lang_rule_engine_daily_cap` (default 500) + `--respect-rule-cap` on `GrimbaTranslatePending`. Sara Chen sign-off on the cap value. |
| R2 | **Presenter SQL CASE complexity** — `filterForTargetLocale` ANDed onto already-complex `languagePrioritySql` may break some Eloquent compositions (especially in `regionalMix` which already filters by region). | S-LSAT-02 unit test runs the composition on a real PG/sqlite fixture; explain query plan reviewed by Larry Ellison. |
| R3 | **Rule-engine race conditions** — two cron passes (`translate_fr` and `translate_by_rule`) hitting the same `translation_priority` row at once. | All `translation_priority` writes use `DB::transaction()` + `lockForUpdate()`. Scheduler entries use `withoutOverlapping()`. |
| R4 | **Empty pages on thin categories** — strict mode on a category with only 4 EN posts will return 0 to FR readers. | Per-surface override: `grimba_lang_strict_category=false` by default. Tail expander offers the disclosure. |
| R5 | **NULL-language posts vanish** — strict mode would also drop NULL-language posts from FR/EN surfaces. Today these are ~3% of posts. | The strict filter checks `original_language = target OR EXISTS (translation)` — NULL posts are dropped intentionally (Vader directive: "display articles in french that ARE in french"). Detector backfill cron + the rare-NULL amber badge keep this pool ≤ 1% in steady state. |
| R6 | **Translation-rule oscillation** — a post crosses 500 views, gets translated, then loses popularity; the engine might re-evaluate and waste cycles. | Rule engine is *idempotent* on priority: once `translation_priority=5` AND target translation exists, no further write occurs. |
| R7 | **Locale switcher cache poisoning** — the existing `grimba_breaking_v1:$locale:…` cache key already includes locale; we must add it to **every** new cache touched in this fleet (S-LSAT-03 settings cache is locale-agnostic so safe; tail expander partials must not be page-cached without locale). | Code review checklist for every new cache key: must include the reader locale. |
| R8 | **Admin form abuse** — operator sets `popularity_threshold=1` by accident, triggering a full-corpus translation pass. | Validation: threshold must be ≥ 10. Live preview shows projected enqueue count before save. Daily cap as final safety net. |
| R9 | **NobuAI brand purity in admin form** — natural temptation to label fields with provider names ("DeepL daily limit"). Vader directive: **never** in any UI. | S-LSAT-14 acceptance criterion explicitly forbids provider names. Mnemo audit checks the diff for `DeepL\|OpenAI\|Anthropic\|Mistral` strings. |
| R10 | **Recompute of `editorial_region` after rule engine has run** — a post reclassified from `europe` to `africa` retroactively should jump priority. | `GrimbaRegionScope::onSave` hook fires `GrimbaTranslationRules::recompute($post)` whenever `editorial_region` changes. Add to S-LSAT-09 scope. |

---

## Wired Into Existing Code — What Gets Touched

| File | What changes |
|---|---|
| `app/Support/GrimbaTranslationPresenter.php` | + `filterForTargetLocale()` method; reuse `hasTranslationsTable()` + cache; same warm() path. **No breaking change** to `orderForTargetLocale`. |
| `app/Support/GrimbaHomeFeed.php` | The 11 call sites of `orderForTargetLocale` each get an additional `->tap(fn ($q) => GrimbaTranslationPresenter::filterForTargetLocale($q))` gated by surface setting. `breaking()` signature gets `$locale` parameter. Cache key includes locale (already does). |
| `app/Support/GrimbaTranslationRules.php` | **NEW** — pure-function rule engine. |
| `app/Support/GrimbaLanguageSettings.php` | **NEW** — cached settings reader. |
| `app/Console/Commands/GrimbaTranslateByRule.php` | **NEW** — the orchestrator command. |
| `app/Console/Commands/GrimbaTranslatePending.php` | + `--order-by-priority` and `--respect-rule-cap` flags; `orderByDesc('translation_priority')` added before existing `orderByDesc('id')`. |
| `routes/console.php` | + `translate_by_rule` cron entry every 15m. |
| `database/migrations/2026_05_18_…_add_translation_priority_to_posts.php` | **NEW** — single column + index. |
| `platform/themes/echo/functions/grimba-admin-translation-rules.php` | **NEW** — admin route + dashboard menu. |
| `platform/themes/echo/views/grimba-admin/translation-rules/index.blade.php` | **NEW** — admin form. |
| `platform/themes/echo/partials/lang/tail-expander.blade.php` | **NEW** — reader UI expander. |
| `platform/themes/echo/views/breaking.blade.php` | passes locale to `GrimbaHomeFeed::breaking()`; tail-expander include below the list. |
| `platform/themes/echo/views/latest.blade.php` | tail-expander include. |
| `platform/themes/echo/views/loop.blade.php` | tail-expander include for category/tag/author paginators. |
| `platform/themes/echo/views/category.blade.php` | tail-expander include (only when `grimba_lang_strict_category` is true; default false). |
| `platform/themes/echo/partials/home/urgency-banner.blade.php` | passes locale to `breaking()`. |
| `platform/themes/echo/partials/home/hero-grid.blade.php` | inherits the filter via the upstream query in `GrimbaHomeFeed::hero()`. |
| `platform/themes/echo/routes/web.php:199` + `:258` | `/breaking` route passes locale into `GrimbaHomeFeed::breaking()`. |
| `app/Scopes/GrimbaRegionScope.php` | + `onSaved` hook firing `GrimbaTranslationRules::recompute()` when `editorial_region` changes. |
| `app/Listeners/GrimbaPostHooks.php` (or wherever `Post::saved` is wired) | + listener that calls `GrimbaTranslationRules::computePriority` on each save. |
| `tests/Feature/GrimbaLanguageSurfacingTest.php` | **NEW** |
| `tests/Unit/GrimbaTranslationRulesTest.php` | **NEW** |
| `tests/Feature/GrimbaTranslateByRuleCommandTest.php` | **NEW** |
| `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` | append S-LSAT band table (stub below). |
| `docs/GRIMBANEWS_LANG_SURFACING_OPERATOR_HANDOFF.md` | **NEW** — operator runbook. |

---

## Master Plan Addition — Append This To `GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`

```markdown
---

## Reader-Language Surfacing + Auto-Translate (S-LSAT-01 → S-LSAT-21)

Vader directive 2026-05-18 — strict per-locale article surfacing across home /
breaking / latest / dossiers; rule-driven auto-translate (African content
must be both FR+EN; popular articles ≥ 500 views auto-translate to the
opposite locale); admin-tunable thresholds. Architect plan at
`docs/GRIMBANEWS_LANGUAGE_SURFACING_AND_AUTO_TRANSLATE_PLAN.md` (21 sprints,
~28h, audit/big/polish cadence). Foundation reuses `posts.original_language`,
`posts.editorial_region`, `posts.views`, and `grimba_post_translations` —
**zero new tables**, one new column (`posts.translation_priority`), one
settings group.

| Sprint | Title | Est. | Status |
|---|---|---|---|
| S-LSAT-01 | Inventory of presenter call sites + breaking() readers | 45m | open |
| S-LSAT-02 | `GrimbaTranslationPresenter::filterForTargetLocale()` | 90m | open |
| S-LSAT-03 | `GrimbaLanguageSettings` cached reader | 60m | open |
| S-LSAT-04 | Wire filter into 4 priority surfaces (home/latest/dossiers/breaking) | 90m | open |
| S-LSAT-05 | Tail-expander partial — "Articles disponibles en anglais (N)" | 60m | open |
| S-LSAT-06 | Breaking-news locale filter — `breaking($locale, …)` | 90m | open |
| S-LSAT-07 | Urgency banner + `/breaking` route pass locale | 45m | open |
| S-LSAT-08 | `posts.translation_priority` migration | 30m | open |
| S-LSAT-09 | `GrimbaTranslationRules` pure-function engine | 90m | open |
| S-LSAT-10 | `grimba:translate-by-rule` artisan command | 75m | open |
| S-LSAT-11 | Scheduler entry — every 15m, daily-cap gated | 45m | open |
| S-LSAT-12 | `GrimbaTranslatePending` `--order-by-priority` + `--respect-rule-cap` | 60m | open |
| S-LSAT-13 | Admin route shell `/admin/grimba/translation-rules` | 60m | open |
| S-LSAT-14 | Admin form view — 13 settings fields, NobuAI brand purity | 90m | open |
| S-LSAT-15 | Admin save handler + cache flush + settings audit log | 60m | open |
| S-LSAT-16 | Live "projected enqueue count" preview on the admin form | 60m | open |
| S-LSAT-17 | Per-post override on Botble post edit screen | 60m | open |
| S-LSAT-18 | Filter + rules unit/feature tests | 90m | open |
| S-LSAT-19 | `translate-by-rule` command tests (idempotency, cap, dry-run) | 75m | open |
| S-LSAT-20 | Docs + operator handoff | 45m | open |
| S-LSAT-21 | Release smoke — 8-URL × 2-locale sweep + admin roundtrip | 60m | open |

Sprint nomenclature: S-LSAT-N = Language Surfacing + Auto-Translate fleet.
```

---

## Open Decisions for Vader (defaulted, but flag-able before S-LSAT-04)

1. **Strict-mode defaults** — this plan ships strict ON for home / breaking
   / latest / dossiers, OFF for category / search. Vader can override per-surface
   in the admin form post-launch. If you want strict EVERYWHERE on day one,
   flip the defaults in `GrimbaLanguageSettings::defaults()` before S-LSAT-04
   ships.
2. **Popularity threshold of 500** — Vader's example. The plan codifies it as
   a setting so the operator can lower to 200 once we have traffic data.
   African region threshold defaults to **100** because we want African
   content cross-translated more aggressively.
3. **Tail expander vs hard hide** — strict mode hides EN-only-no-translation
   posts from FR readers. The expander gives readers an "open me to see what
   else is out there in EN" disclosure. Vader can disable the expander
   entirely in the admin form (`grimba_lang_tail_expander_enabled=false`).
4. **Manual `translation_priority=10` per-post** — currently editors set this
   via the post edit screen (S-LSAT-17). Should we add a bulk-action button
   on the admin posts index ("Translate selected to EN now")? Plan defers this
   to a follow-up S-LSAT-22 if Vader greenlights it.
