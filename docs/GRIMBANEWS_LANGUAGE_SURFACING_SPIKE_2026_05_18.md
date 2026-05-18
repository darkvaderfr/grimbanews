# Language-Surfacing Empirical Spike — 2026-05-18

**Author:** Claude / Mythos prep · **Trigger:** Vader directive about strict language-based surfacing + auto-translate rules

Empirical baseline captured before Mythos's plan lands. This documents what the system actually does today, so the architect's plan can connect to ground truth rather than assumed behavior.

---

## 1. The reader-visible gap

Live test (`?lang=fr` vs. `?lang=en` on `/breaking`):

```
=== /breaking ?lang=fr — top 5 ===
Moyen-Orient en direct : les puissances du G7 discutent…
EN DIRECT - Guerre au Moyen-Orient : «Pour l'Iran, le temps presse…»
EN DIRECT - Festival de Cannes, jour 7 : les remous de «Zapper Bolloré»…
Nigeria: Death Toll Hits 191 As Lassa Fever Cases Rise
EN DIRECT, guerre en Ukraine : Volodymyr Zelensky estime que…

=== /breaking ?lang=en — top 5 ===
Moyen-Orient en direct : les puissances du G7 discutent…   ← same FR headline
EN DIRECT - Guerre au Moyen-Orient…                         ← same FR headline
EN DIRECT - Festival de Cannes, jour 7…                     ← same FR headline
Nigeria: Death Toll Hits 191 As Lassa Fever Cases Rise      ← only EN-origin
EN DIRECT, guerre en Ukraine…                               ← same FR headline
```

**The EN reader sees 4 untranslated FR titles in the top 5.** Vader's directive exactly identifies this gap.

Bundle composition reported by `GrimbaHomeFeed::breaking(18)`:
- 9 total breaking posts
- 5 FR-origin, 4 EN-origin
- None of these specific posts have `posts.translated_*` rows yet (translate-pending hasn't run regularly on the breaking lane)

---

## 2. Where the system already does the right thing

- **Cache key includes locale** — `grimba_breaking_v1:fr:international:18` vs `:en:` — so FR and EN renders are physically separated in cache.
- **Post::saving hook tags `original_language`** — 99% of posts (3,461 of 3,497) carry a confident FR/EN tag from `GrimbaLanguageDetector`.
- **`GrimbaTranslationPresenter::orderForTargetLocale`** — the SQL CASE pushes native-locale posts first, then translated, then labeled wrong-locale, then NULL.
- **`GnTr::title(...)` / `description(...)` / `body(...)` / `summary(...)`** — readers GET the translated version when one exists in `grimba_post_translations` or the in-row cache.
- **`grimba_post_translations` table** — durable per-locale map keyed on `(post_id, locale)`.

---

## 3. Where the system passes original text through (the gap)

- **`/breaking`, `/latest`, `/dossiers`, `/blog/*`, `/article/*` views** — when the active locale's translation row is missing, the presenter falls back to the original text. This is correct as a degradation strategy but **visible** to the reader.
- **`GrimbaHomeFeed::breaking()`** ranks by locale via `orderForTargetLocale` — does NOT filter wrong-locale posts out. EN-origin without a FR translation lands inside a FR-locale bundle when no native-FR breaking exists for that 18-hour window.
- **No "strict locale" mode** anywhere. The presenter has no equivalent of `->filterToLocale('fr')` that drops rank-2/rank-3 rows.

---

## 4. Translate-pending coverage today

`/admin/grimba/translation-map` shows the per-direction work map. Specific numbers (as of this spike):
- FR → EN translation work-map: substantial backlog (most En-origin posts don't have FR translations)
- EN → FR work-map: same shape
- `grimba:translate-pending` cron is scheduled but on a quota-aware throttle; it doesn't burst-translate when an article gains traction.

Vader's two new rules are exactly the levers that would close the gap:
1. **African-region articles → always translate both directions** (the editorial-priority rule)
2. **Popular articles → auto-translate when crossing a view threshold** (the audience-signal rule)

---

## 5. Specific signals Mythos's plan should connect to

- `posts.original_language` → known
- `posts.editorial_region` → known (`africa` is the trigger for rule #1)
- `posts.views` → known (≥500 is the example trigger for rule #2)
- `grimba_post_translations` (post_id, locale UNIQUE) → write target
- `GrimbaTranslator` chain → NobuTranslate primary, fallbacks below
- `GrimbaTranslatePending` → existing selection criteria + retry queue
- Admin form: settings keys with admin form like `/admin/grimba/newsdataio`

---

## 6. Suggested decision points for the plan

1. **Filter vs. rank** — let the presenter expose a strict-mode method `filterToLocaleOrTranslated($query, $target)` for views that opt in. Lists like `/breaking` should be strict; `/dossiers` (multi-perspective by design) should remain rank-only.
2. **Rules engine location** — either as a new `App\Support\GrimbaTranslationRules::shouldAutoTranslate(Post $post, string $target): bool` consulted at `GrimbaTranslatePending` selection time, OR a dedicated `grimba_translation_rules` table for operator-editable conditions. The architect should decide based on whether Vader wants new rules per source/category, not just thresholds.
3. **Admin UI shape** — extend the existing `/admin/grimba/translation-map` page with a "Rules" subsection, or split into a new `/admin/grimba/translation-rules` form. Extension preserves the work-map mental model.
4. **Rule firing cadence** — `Post::saved` hook can dispatch a synchronous rule check (low overhead since rules are just SQL columns), or a separate `grimba:apply-translation-rules` artisan that scans the corpus periodically (more controllable for quota).

---

## 6b. Rule-engine cost ceiling (live data)

Snapshot at 2026-05-18 — 4,578 published posts in corpus.

**Rule #1: African articles must have both FR + EN translations.**
- Africa-region posts: **302** (150 FR-origin + 152 EN-origin)
- Currently missing the cross-locale translation: **150 FR-Africa missing EN**, **152 EN-Africa missing FR**
- One-shot backfill cost: **302 NobuTranslate calls** (≈ a few hours of throttled cron)
- Ongoing per-day cost: ≈ 5-10 calls/day (estimated based on Africa ingest cadence × 50% missing-other-locale rate)

**Rule #2: Popular articles auto-translate when crossing the view threshold.**
- Posts with views ≥ 500: **30 posts** today
- Posts with views ≥ 1000: **22 posts** today
- Posts with views ≥ 100: **35 posts**
- One-shot backfill at the 500-view threshold: **≈30 calls** (half already have a translation per their region; net new ≈15)
- Ongoing cost: highly skewed — viral spikes burst, otherwise dormant. The architect must budget for the worst case (e.g., a Le Monde Africa scoop trending in both langs).

**Net cost ceiling for both rules combined at launch:** ≈300-400 NobuTranslate calls one-shot, then 10-20/day ongoing. **Well within NobuAI's budget envelope** — these rules are SAFE to roll out as cron-driven without quota risk.

---

## 7. Acceptance gates for Vader

When the plan is done, `/breaking?lang=en` should show:
- Native-EN posts (Nigeria Lassa Fever, etc.) at the top
- FR-origin posts ONLY IF a translated-to-EN row exists in `grimba_post_translations`
- Zero raw-French headlines unless the reader explicitly clicks a "Lecture en français" expander

Same for `?lang=fr` mirrored.

For African articles: regardless of view count, both FR and EN translations exist within 1 cron tick of ingest.

For popular articles: when `views >= grimba_translation_popularity_threshold` (default 500), the missing-direction translation lands within 1 cron tick.

Admin can edit `grimba_translation_popularity_threshold`, the regions that get always-translated, and any other condition via `/admin/grimba/translation-rules` without code changes.
