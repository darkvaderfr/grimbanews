# GrimbaNews — Language Tagging System Plan

> **Owner:** Larry Ellison (schema), Rajesh Kumar (backend), Lisa Nguyen (ingest hooks),
> Nina Patel (reader-serving), Alex Morgan (admin map UI), Sara Kim (QA),
> Zen / Echo / Mnemo (audit panel), Steve Jobs (signoff).
>
> **Vader directive 2026-05-16:**
> *"Tag each article, breaking news, dossier, insights, and NobuAI analysis with
> its original language (FR or EN). Serve content based on that tag. English
> content should be translated to French and French content to English, but the
> sourced/initial content should be tagged at creation AND retroactively. We
> need a key/map of what needs to be translated."*
>
> **Scope:** This plan is **narrow** — origin-language tagging + the translation
> work-map that flows from it. The broader bilingual UX overhaul (switcher
> polish, hreflang, sitemap split, NobuTranslate driver, CI string-coverage
> guard) lives in `GRIMBANEWS_LANGUAGE_SPRINT_PLAN.md` (245 sprints, Phases A–H).
> This document is its **foundation** — Phase A-26 ("source language detection
> accuracy") expands into the 16-sprint plan below.

---

## Current State (grounded — read before changing)

What already exists in the repo as of 2026-05-16:

| Surface | Status |
|---|---|
| `posts.original_language` column | **Present** — `varchar(5) NULL`, indexed, since migration `2026_04_24_000000_add_original_language_to_posts.php`. |
| Source-driven backfill | The same migration copied `news_sources.language` onto every `post` joined via `source_id`. Posts with no source — and posts whose source has `language = NULL` — were left NULL. |
| Ingest writer (RSS / admin) | `platform/themes/echo/functions/grimba-post-hooks.php` line 59 copies `news_sources.language` onto `original_language` on every `Post::saving` event **when the source field is empty / `unknown`**. So RSS already tags. |
| Ingest writer (NewsAPI / LiveNews / Newsdata.io) | These services **auto-create `news_sources` rows with `language = null`** when an upstream source isn't yet in the table (`GrimbaNewsApiFetcher.php:533`, `GrimbaLiveNewsFetcher.php:782`). So new auto-created sources produce NULL-language posts until an editor classifies them. |
| `posts.translated_name / _description / _content / _to / _at / _driver` | Per-post in-row translation cache (one target locale at a time). Used as fast path. |
| `grimba_post_translations` table | **Present** — unique on `(post_id, locale)`, holds N translations per post. Already serves as the durable many-to-many key/map. |
| `summary_nobuai` column | Single text column — **no locale tag** (already flagged as `A-16` in the 245-sprint plan). |
| `story_clusters` (dossiers) | `id, topic, description, timestamps, review_action, reviewed_at` — **no language column**. Topic is currently FR-only. |
| `GrimbaTranslationPresenter` (alias `GnTr`) | Reader-side renderer. NULL handling: `rankForTargetLocale()` already puts NULL-language posts at rank 2 (below same-locale, below translated) — i.e. it serves the raw `name` / `description` and accepts the locale mismatch. **This is the wrong default** going forward; we'll fix it in S-LANG-10. |
| `GrimbaTranslator` service | Multi-provider chain (DeepL → Mistral → OpenRouter → OpenAI → Anthropic → Google → xAI → Perplexity → Groq → Libre → Google-gtx). Speaks FR↔EN. Brand-scrubbed to "NobuAI" in user-visible chips. |
| `GrimbaTranslatePending` command | Scheduled job that loops `posts WHERE original_language IS NOT NULL AND != target` and writes `translated_name/description/content` per post. Direction-controlled by `--to=fr` or `--to=en`. |
| `GrimbaIngestGuardrails` | Already flags `"traduction manquante"` when `original_language != 'fr' && empty(translated_name)`. So absence-of-translation is already a publication blocker. |

**Net:** Half the system already exists. This plan is mostly **closing gaps**, not greenfield.

---

## Architectural Decisions Locked

### 1. The translation key/map is **`grimba_post_translations`** — do NOT add a `grimba_translation_keys` table

The table already exists with `(post_id, locale)` unique, `translated_at` timestamps, `translation_driver`, and indexes. It is the durable work map. To answer *"what needs translating for locale L?"* we run:

```sql
SELECT p.id, p.original_language, p.published_at
FROM   posts p
LEFT JOIN grimba_post_translations gpt
       ON gpt.post_id = p.id AND gpt.locale = 'fr'
WHERE  p.original_language IS NOT NULL
   AND p.original_language NOT LIKE 'fr%'
   AND p.status = 'published'
   AND gpt.id IS NULL
ORDER BY p.published_at DESC;
```

Adding a parallel `grimba_translation_keys` table would be a denormalized cache of that query and would drift. **One source of truth.** We will add a memoized count helper and an admin map UI on top of this same query — described in S-LANG-12 / 13 / 14.

### 2. NULL `original_language` posts get **deferred translation**, not silent fallback

Reader-side change: when `original_language IS NULL`, the presenter must **not** treat the raw text as serving any locale. Behavior:

- **Lists:** push to rank 3 (last) — lower than translated content; not hidden, but never preferred.
- **Article page:** show as-is with a small disclosure ("Origin language not yet classified"). No misleading `lang=` attribute (currently we emit `lang="fr"` for these — wrong).
- **Cron:** the detector runs nightly on the NULL backlog (S-LANG-04) so this state is transient.

This is the conservative answer: we never tell the reader "this is French" when we don't know.

### 3. Dossier ("story cluster") primary language = **modal language of its published posts**

We do **not** add a static `language` column to `story_clusters`. Instead we add a denormalized `primary_language` + `language_mix_json` pair, recomputed by a daily command and on cluster touch. Rationale: the modal can change as new articles land in a cluster, and a single static column would lie. Computation: see S-LANG-11.

### 4. NobuAI summaries are **per-post + per-locale**, stored alongside translations

Today `summary_nobuai` is one text field on `posts`. Going forward:
- The **original-language summary** stays on `posts.summary_nobuai` (it was generated in the post's origin language). Add `summary_nobuai_locale` column to record what locale that is — defaults to `original_language` for back-compat.
- The **translated summary** lives in `grimba_post_translations` as a new `translated_summary` column. Same key, same map.

This avoids a separate table for what is structurally the same join.

### 5. Source-of-truth precedence for ingest-time detection

When a new post lands, `GrimbaLanguageDetector::detect()` consults signals in this order, first-wins:

```
1. Caller hint                       — fetcher passed an explicit `language` in the article payload
2. `news_sources.language` row       — when source_id is set AND the source has a non-null language
3. Domain TLD heuristic              — .fr / .ca-fr / .be-fr / .ch-fr → fr; .com / .co.uk / .au / .us → en (only when TLD is in a confident allowlist)
4. Body character distribution       — fast n-gram score on first 800 chars of name + description
5. NULL                              — refuse to guess. Mark for deferred classification.
```

Signal #3 is intentionally conservative — a French Africa source on `.com` would otherwise be mis-tagged. Signal #4 must clear a confidence threshold (default 0.75); below that we return NULL.

We do **not** add an external language-detection API call on the ingest path. Latency would compound on a 100-article tick. The heuristic runs in-process; a future enhancement (S-LANG-NN in the 245-sprint plan, not here) can plug in NobuTranslate's detection endpoint as an offline batch.

---

## Sprint Sequence (16 sprints, ~22 hours)

Cadence follows `feedback_sprint_cadence_audit_big_polish`: audit → big → polish, alternating. Each sprint capped at 90 min unless flagged.

### Foundation (S-LANG-01 → S-LANG-04) — detector + retroactive backfill

| Sprint | Title | Est. | Acceptance |
|---|---|---|---|
| **S-LANG-01** | **Audit** — full inventory of where `original_language` is read/written | 45m | Markdown ledger of every read site (presenter, guardrails, admin cockpit queries, blade hreflang emit, JSON-LD `inLanguage`, post.blade `lang=` attrs, ingest hooks, fetcher inserts, translate-pending filter). One paragraph per site noting current behavior vs. desired. Land at `docs/GRIMBANEWS_LANGUAGE_TAGGING_INVENTORY.md`. |
| **S-LANG-02** | **Big** — `GrimbaLanguageDetector` service | 90m | New `app/Services/GrimbaLanguageDetector.php`. Public `detect(array $signals): ?string` returning `'fr'`, `'en'`, or `null`. Signals struct: `{caller_hint, source_language, source_url, text_sample}`. Internal scorers: TLD allowlist (private const), body n-gram (FR markers: ç, é à è, "le ", " de ", " et ", "qu'"; EN markers: " the ", " and ", " of ", " to ", " is ", no diacritics). Confidence threshold env-configurable (`GRIMBA_LANG_DETECT_CONFIDENCE` default `0.75`). Unit tests: 30 fixtures, 15 FR / 15 EN, mixed signal strengths. Pure function — no DB, no HTTP. |
| **S-LANG-03** | **Polish** — wire detector into every ingest writer | 60m | Hook `GrimbaLanguageDetector::detect()` into: (a) `grimba-post-hooks.php::saving` — runs **only** when `original_language` would otherwise be NULL after source-copy step; (b) `GrimbaRssPoller::insertPost`; (c) `GrimbaNewsApiFetcher::storeArticle`; (d) `GrimbaNewsdataIoFetcher::storeArticle`; (e) `GrimbaLiveNewsFetcher::insertPost`. All five writers also bubble up the detected language to **also** populate `news_sources.language` when that row was auto-created with NULL. New posts must land with `original_language` set or with a Sentry-logged warning explaining which signal step refused. |
| **S-LANG-04** | **Big** — `grimba:backfill-language` artisan command | 90m | Pattern from `GrimbaBackfillEditorialRegions.php`. Signature: `grimba:backfill-language {--batch=500} {--limit=} {--dry} {--reclassify} {--require-confidence=0.75}`. Selects `posts WHERE original_language IS NULL` (or all when `--reclassify`), runs the detector against each row's name + description + source URL + source language, batches DB writes by language bucket, prints `... 500 touched (lastId=X) — fr=… en=… unknown=…`. Exit code 0. Reports unclassified percentage at end. Schedule daily at 03:15 via `routes/console.php`. |

### Reader serving + dossiers + insights (S-LANG-05 → S-LANG-11)

| Sprint | Title | Est. | Acceptance |
|---|---|---|---|
| **S-LANG-05** | **Audit** — current `GnTr` rank-2 behavior verification | 30m | Reproduce: load `/` with `grimba_lang=en` and identify what serves for posts where `original_language IS NULL`. Document in inventory doc. Confirm hypothesis: raw FR text is being shown to EN readers because rank-2 still wins over rank-3. Screenshots for evidence ledger. |
| **S-LANG-06** | **Big** — NULL-language reader policy (deferred translation) | 75m | Change `GrimbaTranslationPresenter::languagePrioritySql()`: NULL `original_language` becomes rank 3, **after** translated-not-same-locale. Add `GrimbaTranslationPresenter::originUnknown(object $post): bool` helper. Update `post.blade.php` JSON-LD `inLanguage` to **not** fall back to `'fr'` when origin is NULL — instead omit the field entirely (per schema.org spec, missing is better than wrong). Update the article `<html lang="…">` attr in the same file. Unit + feature tests. |
| **S-LANG-07** | **Polish** — `OriginLanguageBadge` partial | 30m | `platform/themes/echo/partials/home/language-badge.blade.php` already exists (line 9 reads `$post->original_language`). Audit it: confirm it renders nothing when origin is NULL, renders `FR` pill when fr, `EN` pill when en. Polish CSS to match Steve's glass-pill style. |
| **S-LANG-08** | **Audit** — `GrimbaTranslatePending` coverage gap audit | 45m | Run command in dry-mode for each direction. Count remaining backlog. Confirm it **skips** NULL-origin posts (current behavior — `whereNotNull('original_language')` at line 41). Confirm it covers all post statuses, not just published. Document gap if any. |
| **S-LANG-09** | **Big** — Per-locale NobuAI summary | 90m | Migration: add `summary_nobuai_locale` to `posts` (varchar(5) nullable, indexed alongside `summary_nobuai`); add `translated_summary` longtext column to `grimba_post_translations`. Default backfill: `summary_nobuai_locale = original_language` where summary exists. Update `GrimbaGenerateNobuAiSummaries` to write the locale tag. Add a parallel command `grimba:translate-summaries --to=fr` / `--to=en` that fills `translated_summary` in the translations table (re-uses `GrimbaTranslator`). Reader: `GnTr::summary($post)` helper that returns the locale-correct text. |
| **S-LANG-10** | **Polish** — drop misleading `lang="fr"` on NULL-origin pages | 30m | Sweep theme blades for `lang="fr"` hardcodes that should be conditional. Confirm `post.blade.php:81` (`inLanguage`) is corrected per S-LANG-06. Confirm `post.blade.php:1094-1095` ("Afficher le texte original") suppresses the disclosure block when origin is NULL. |
| **S-LANG-11** | **Big** — Dossier (story cluster) `primary_language` + mix | 90m | Migration: add `primary_language` (varchar(5) nullable) + `language_mix_json` (json nullable) + `language_recomputed_at` (timestamp nullable) to `story_clusters`. New `app/Support/GrimbaDossierLanguage.php` with `recomputeFor(int $clusterId): array`. Algorithm: count `published` posts in cluster grouped by `original_language` (excluding NULL); modal wins; ties broken by recency. Cron command `grimba:recompute-dossier-languages` runs nightly at 03:30 (right after the backfill). Recompute also triggered when a post enters/leaves a cluster (hook into `GrimbaRecluster`). Reader: dossier page uses `primary_language` for hreflang + JSON-LD, falls back gracefully when NULL. |

### Translation work-map + admin (S-LANG-12 → S-LANG-14)

| Sprint | Title | Est. | Acceptance |
|---|---|---|---|
| **S-LANG-12** | **Big** — `GrimbaTranslationMap` support class | 75m | New `app/Support/GrimbaTranslationMap.php`. Three methods: `pendingFor(string $targetLocale): Builder` (the LEFT-JOIN query above, returns a query — no `->get()` so admin pages can paginate); `summaryStats(): array` (cached for 5 min — total posts, NULL-origin count, per-locale-origin count, per-target translation coverage %, average translated_at lag); `cliReport(): string` for headless ops. All reads are off the existing `grimba_post_translations` table; no new schema. |
| **S-LANG-13** | **Big** — Admin "Translation Map" page | 90m | New admin route `/admin/grimba/translation-map`. Three panels: (1) headline counts from `GrimbaTranslationMap::summaryStats()`; (2) "Pending translation" list — paginated, filterable by `target_locale`, `original_language`, `published_after`; (3) "Unclassified" list — paginated, every post with NULL origin, with inline "Re-detect" + "Set FR" / "Set EN" buttons. Authorized to existing admin role only. Add menu entry under existing Grimba admin nav. |
| **S-LANG-14** | **Polish** — Inline lang controls on admin post-edit | 45m | The admin Post edit screen already has the `grimba_*` form bridge in `grimba-post-hooks.php`. Add a `grimba_original_language` form field (FR / EN / Auto / Unclassified radio group) so editors can override the detector when wrong. "Auto" re-runs detection on save. |

### Tests + close (S-LANG-15 → S-LANG-16)

| Sprint | Title | Est. | Acceptance |
|---|---|---|---|
| **S-LANG-15** | **Audit** — Test coverage for tagging pipeline | 60m | Unit tests: `GrimbaLanguageDetector` fixture battery (30 cases). `GrimbaTranslationMap::summaryStats()` shape. `GrimbaDossierLanguage::recomputeFor()` modal logic. Feature tests: NULL-origin post on home with EN cookie → not preferred. Reader on EN, FR-origin post with translation → translation served. Reader on EN, FR-origin post without translation → translation absent → post still appears but rank 3. Admin: translation map renders. Inline lang controls round-trip. |
| **S-LANG-16** | **Polish** — Plan close: dream-team audit panel + memory update | 60m | Zen / Echo / Mnemo parallel audit per `feedback_dream_team_audit`. Update `project_grimbanews_next_prompt.md` memory file with S-LANG status. Mark sprint band complete in master plan. Commit + push to `darkvaderfr/grimbanews:main`. |

---

## Schema Additions Summary

| Table | Column | Type | Notes |
|---|---|---|---|
| `posts` | `summary_nobuai_locale` | `varchar(5) NULL` | Locale the `summary_nobuai` text is in. Backfilled from `original_language`. |
| `story_clusters` | `primary_language` | `varchar(5) NULL` | Modal language of constituent published posts. Recomputed nightly. |
| `story_clusters` | `language_mix_json` | `json NULL` | E.g. `{"fr": 8, "en": 3, "null": 1}`. For admin diagnostics. |
| `story_clusters` | `language_recomputed_at` | `timestamp NULL` | Stale-data marker. |
| `grimba_post_translations` | `translated_summary` | `longtext NULL` | Per-locale NobuAI summary translation. |

**No** new `grimba_translation_keys` table. No new column on `posts` for translation-status — it's derivable from the join.

---

## Settings Keys Added

All under Botble's `setting()` system, writable from `/admin/grimba/translation` once S-LANG-13 lands. ENV fallbacks in parens.

| Key | Default | Purpose |
|---|---|---|
| `grimba_lang_detect_confidence` | `0.75` | n-gram threshold below which the detector returns NULL. ENV: `GRIMBA_LANG_DETECT_CONFIDENCE`. |
| `grimba_lang_detect_enable_tld` | `true` | Toggle the TLD heuristic; can be disabled if a corpus shift makes it noisy. |
| `grimba_lang_detect_log_misses` | `false` | When true, every NULL-return is logged via `GrimbaAutomationMonitor` for tuning. |
| `grimba_dossier_lang_modal_min` | `0.6` | Modal must clear this fraction of cluster posts to be promoted to `primary_language` (else NULL). |
| `grimba_translation_map_cache_ttl` | `300` | Seconds. Cache for `GrimbaTranslationMap::summaryStats()`. |

---

## Cron Additions

Append to `routes/console.php` after S-LANG-04 ships:

```
$schedule->command('grimba:backfill-language --batch=1000 --require-confidence=0.75')
    ->dailyAt('03:15')->withoutOverlapping()->onOneServer()
    ->description('Classify origin language of any post still tagged NULL');

$schedule->command('grimba:recompute-dossier-languages')
    ->dailyAt('03:30')->withoutOverlapping()->onOneServer()
    ->description('Recompute story_clusters.primary_language from member posts');

$schedule->command('grimba:translate-summaries --to=fr')
    ->hourlyAt(22)->withoutOverlapping()->onOneServer();

$schedule->command('grimba:translate-summaries --to=en')
    ->hourlyAt(52)->withoutOverlapping()->onOneServer();
```

---

## Risks & Open Questions

1. **`news_sources.language` is itself unreliable** — many auto-created NewsAPI / LiveNews sources have `language = NULL`. The original 2026-04-24 backfill migration was a no-op for those posts. The new detector closes that gap by re-running on every NULL post, but if the editor team has manually marked any of those sources with the **wrong** language, S-LANG-04's first run will inherit the bad tag for the source-language signal. Mitigation: the detector treats source-language as one signal of five, and the body n-gram will overrule a wrong source tag at confidence ≥ 0.85.
2. **Posts that were tagged `original_language` by the 2026-04-24 migration but are actually a different language** are invisible to S-LANG-04 (which only touches NULLs). The `--reclassify` flag handles this but operator must run it explicitly. Recommendation: ship S-LANG-04 in detect-only mode first, then run with `--reclassify --dry` on prod to see drift count before flipping.
3. **`grimba_post_translations.translated_at` is the work-map's freshness proxy** — but `GrimbaTranslatePending` writes the in-row `posts.translated_*` fields too, and the two can drift. S-LANG-15 must add a test that asserts both writes happen atomically, else the map count will lie.
4. **Dossier modal language is unstable for small clusters.** A 3-post cluster (2 FR + 1 EN) flips to NULL the moment an EN post lands. We mitigate with `grimba_dossier_lang_modal_min = 0.6` — but this means most 2-post clusters resolve to NULL. Acceptable for v1; a per-locale dossier view (`/dossier/{id}?lang=en`) is the proper long-term answer and lives in the 245-sprint plan, not here.
5. **`summary_nobuai_locale` backfill assumption** — defaulting to `original_language` is correct **only** when the summary was generated by `GrimbaGenerateNobuAiSummaries` (which runs against the source text). If any summary was generated against `translated_content`, the assumption breaks. Audit needed in S-LANG-08.

---

## Append to Master Plan

The following section heading should be appended to
`docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` after the existing
"Glass-button + light-mode shadow polish + category backfill" section:

```
## Language Tagging System (S-LANG-01 → S-LANG-16)
```

Then a single status table with all 16 sprints, defaulting to `open`. Full
spec stays in this document; the master plan carries the registry row only.
