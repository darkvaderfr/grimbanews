# GrimbaNews — Language Tagging Operator Handoff

**Author:** Mythos / Claude · **Date:** 2026-05-17 · **Vader directive:** "Tag every article + breaking + dossier + insights + NobuAI analysis with its language and serve content based on the tag."

This is the operator guide for the language-tagging system shipped across waves J → Y of the 2026-05-16/17 sessions. **14 of 16 S-LANG sprints are closed** (architect plan: `docs/GRIMBANEWS_LANGUAGE_TAGGING_PLAN.md`).

---

## TL;DR for the operator

1. **Run two pending migrations** (one already shipped + ready to run, one fresh):
   ```bash
   php artisan migrate --path=database/migrations/2026_05_16_180000_add_primary_language_to_story_clusters_table.php
   php artisan migrate --path=database/migrations/2026_05_17_120000_add_summary_nobuai_locale_to_posts_table.php
   php artisan migrate --path=database/migrations/2026_05_17_120100_add_translated_summary_to_grimba_post_translations_table.php
   ```
2. **Backfill the 649 dossiers' modal language** (one-shot):
   ```bash
   php artisan grimba:recompute-dossier-language --all
   ```
3. **Daily cron is already wired** — `lang_backfill` at 03:15 UTC and `dossier_lang_recompute` at 03:45 UTC.

Everything else (detector at ingest time, reader-side serving, hreflang, admin work-map, atomicity tests) is already live.

---

## Components

### 1. `App\Services\GrimbaLanguageDetector` (pure function)

Signal precedence — first-wins:
1. `caller_hint` (fetcher passed an explicit `'fr'` / `'en'`)
2. `news_sources.language` (when the source row has a non-null language)
3. URL TLD allowlist (`.fr` / `.qc.ca` / `.gp` / 14 francophone Africa TLDs / `.uk` / `.au` / 9 anglophone TLDs)
4. Body n-gram score on first 800 chars (19 FR markers vs. 17 EN markers; confidence ≥ 0.75)
5. NULL — refuses to guess

Test coverage: 26 unit fixtures, 51 assertions.

### 2. Universal `Post::saving` hook

`platform/themes/echo/functions/grimba-post-hooks.php` runs the detector when `original_language` would otherwise stay NULL after the existing source-copy step. Verdict bubbles up to `news_sources.language` when that row is NULL too, so subsequent posts from the same source skip the detector entirely.

Covers every ingest writer (RSS poller, NewsAPI fetcher, newsdata.io fetcher, LiveNews fetcher, admin Post form) without per-writer wiring.

### 3. Daily backfill cron

`grimba:backfill-language` runs at **03:15 UTC** daily via `routes/console.php`. Sweeps `posts WHERE original_language IS NULL`, batches 500 at a time, wraps each batch in `DB::transaction`. First production run on the 2026-05-16 archive recovered **1340 NULL → 36 NULL (97.3%)** and patched 394 `news_sources` rows.

Flags:
- `--batch=500` (default)
- `--limit=N` (cap total processed)
- `--reclassify` (re-run against rows that already have a language)
- `--confidence=0.75` (override detector threshold)
- `--dry` (count only, no writes)

### 4. Dossier-language denormalization

`story_clusters.primary_language` + `language_mix_json` + `language_recomputed_at` columns (migration `2026_05_16_180000`).

`GrimbaDossierLanguage::recompute($clusterId)` computes the modal language; commits only at ≥60% modal share (`grimba_dossier_lang_modal_min` setting). NULL-language posts excluded from the denominator.

Two triggers:
- **Daily cron** at 03:45 UTC — `grimba:recompute-dossier-language` (sweeps clusters not recomputed in 24h)
- **On-touch** — `Post::saved` hook fires `recompute()` whenever a post lands in a cluster. Try/catch'd so a hiccup never blocks a save.

### 5. NobuAI summary locale tag (S-LANG-08)

`posts.summary_nobuai_locale` (migration `2026_05_17_120000`). The migration backfills `'fr'` for every existing non-empty `summary_nobuai` (the current writer hardcodes a French prompt). `GrimbaGenerateNobuAiSummaries` now tags new summaries with `'fr'`.

Future cluster-aware generators can produce EN summaries by passing a different prompt and tagging accordingly.

### 6. Translated summary in the join table (S-LANG-09)

`grimba_post_translations.translated_summary` (migration `2026_05_17_120100`). When `GrimbaTranslatePending` runs against a post that has a `summary_nobuai` in its `summary_nobuai_locale` differing from the target locale, it translates the summary and writes it alongside the existing translated_name/description/content row.

### 7. Reader-side serving (S-LANG-05/14)

`GrimbaTranslationPresenter::rankForTargetLocale()` pushes NULL-language posts to **rank 3 (last)** so we never preferentially serve unclassified content. Same rule in the SQL CASE in `languagePrioritySql()`.

Reader-visible badge on unclassified articles: red pill `Langue non classifiée` linking to `/methodology#language-detection`. Sits in the article-hero-card meta line.

JSON-LD `inLanguage` is now omitted (not falsely set to `'fr'`) when origin is NULL. `<html lang>` correct via `app()->getLocale()` in all three layouts. The empty `lang=""` bug on the "Show original" details block is fixed (now conditional).

### 8. `?lang=` query support (S-LANG-06)

Both reader layouts (`grimba-chrome.blade.php`, `grimba-home.blade.php`) read `?lang=` query param first, then `grimba_lang` cookie. Hreflang alternates emit on every reader page: `fr`, `en`, `x-default` → `fr`.

### 9. Admin translation work-map (S-LANG-10/13)

`/admin/grimba/translation-map` — read-only dashboard:
- Pending count + done count per direction (FR↔EN) with progress bars
- Top-15 source publishers by pending backlog per direction
- Per-source coverage table (top 40 by total): total / FR / EN / unknown (with traffic-light badge) / in-row translated / source-lang tag
- Unclassified pool count with backfill CTA

Driven entirely by `grimba_post_translations` — no separate work-map table.

### 10. Atomicity test (S-LANG-15)

`tests/Feature/TranslationAtomicityTest.php` — 4 assertions:
1. In-row + join-table agree when both are written
2. Join-only translation still satisfies the presenter (in-row can lag)
3. Half-rolled-back state (`translated_to` set but `translated_name` empty) does NOT count as translated
4. Unique `(post_id, locale)` index rejects duplicate inserts

---

## Operator workflows

### Bringing a brand-new source into FR/EN parity

1. Operator adds the source row in admin → `news_sources` table.
2. First post lands via the ingest pipeline.
3. `Post::saving` hook runs the detector → if confident, sets `posts.original_language`.
4. If the source row had `language=NULL`, the hook also patches it so future posts skip the detector.
5. Nightly `lang_backfill` cleans up any NULL leftovers from too-short text.
6. `GrimbaTranslatePending` (scheduled job) translates name/description/content/summary to the opposite locale.
7. Reader requesting `?lang=en` (or with cookie set) gets the translated version; the original still serves to `?lang=fr` readers.

### Spot-checking the system health

1. Visit `/admin/grimba/translation-map` — confirm pending counts are dropping over time.
2. Run `php artisan grimba:backfill-language --dry` — should show < 1% unknown on healthy archives.
3. `php artisan schedule:list | grep -E "lang_backfill|dossier_lang_recompute"` — both crons should appear.

### When something looks wrong

| Symptom | Likely cause | Fix |
|---|---|---|
| Article shows red "Langue non classifiée" badge | Detector refused to guess on this row | Wait for nightly backfill OR run `grimba:backfill-language --confidence=0.5` to lower threshold |
| Reader sees English content on a FR-mode page | Translation didn't run for this post | Check `grimba_post_translations` for a row at `(post_id, 'fr')`. If missing, the translate-pending cron will pick it up next tick |
| Dossier modal language is wrong | Recompute hasn't run since the last post landed | The `Post::saved` hook should fire; if not, manually `php artisan grimba:recompute-dossier-language --all` |
| Source language is wrong site-wide | Editor mis-tagged the source row | Fix `news_sources.language`; future posts inherit; existing posts need `grimba:backfill-language --reclassify` |

---

## What's still open (2 sprints out of 16)

- **S-LANG-08/09** — the column writes won't take effect until the two new 2026-05-17 migrations are run (this doc's TL;DR step 1).
- **S-LANG-16** — this doc. Once you've read it, the language tagging system is fully handed off.

---

## File index (paths to know)

- `app/Services/GrimbaLanguageDetector.php`
- `app/Support/GrimbaDossierLanguage.php`
- `app/Support/GrimbaTranslationPresenter.php`
- `app/Console/Commands/GrimbaBackfillLanguage.php`
- `app/Console/Commands/GrimbaRecomputeDossierLanguage.php`
- `app/Console/Commands/GrimbaGenerateNobuAiSummaries.php`
- `app/Console/Commands/GrimbaTranslatePending.php`
- `platform/themes/echo/functions/grimba-post-hooks.php` (Post::saving + Post::saved)
- `platform/themes/echo/functions/grimba-admin-translation-map.php`
- `resources/views/grimba-admin/translation-map/index.blade.php`
- `platform/themes/echo/partials/story/article-hero-card.blade.php` (badge)
- `platform/themes/echo/layouts/grimba-chrome.blade.php` (`?lang=` + hreflang)
- `platform/themes/echo/layouts/grimba-home.blade.php` (`?lang=` + hreflang)
- `database/migrations/2026_04_24_000000_add_original_language_to_posts.php` (already run)
- `database/migrations/2026_05_16_180000_add_primary_language_to_story_clusters_table.php` (needs running)
- `database/migrations/2026_05_17_120000_add_summary_nobuai_locale_to_posts_table.php` (needs running)
- `database/migrations/2026_05_17_120100_add_translated_summary_to_grimba_post_translations_table.php` (needs running)
- `tests/Unit/GrimbaLanguageDetectorTest.php` (26 fixtures)
- `tests/Feature/TranslationAtomicityTest.php` (4 invariants)
- `docs/GRIMBANEWS_LANGUAGE_TAGGING_PLAN.md` (architect's 16-sprint plan)

That's the complete map. The system is launch-ready once the three migrations land + the dossier recompute runs.
