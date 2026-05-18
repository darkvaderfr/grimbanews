<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\CarbonImmutable;
use Throwable;

class GrimbaTranslationPresenter
{
    /** @var array<string, object|null> */
    protected static array $records = [];

    protected static ?bool $hasTranslationsTable = null;

    public static function flushCache(): void
    {
        self::$records = [];
        self::$hasTranslationsTable = null;
    }

    public static function targetLocale(): string
    {
        $lang = (string) (request()?->cookie('grimba_lang') ?: app()->getLocale() ?: 'fr');

        return in_array($lang, ['fr', 'en'], true) ? $lang : 'fr';
    }

    /**
     * @param iterable<int, object>|object|null $posts
     */
    public static function warm(iterable|object|null $posts, ?string $target = null): void
    {
        $target = strtolower(substr($target ?: self::targetLocale(), 0, 8));
        if (! in_array(substr($target, 0, 2), ['fr', 'en'], true)) {
            return;
        }

        $collection = is_iterable($posts) ? collect($posts) : collect([$posts]);
        $ids = [];

        foreach ($collection as $post) {
            if (! is_object($post)) {
                continue;
            }

            $postId = (int) ($post->id ?? 0);
            if ($postId <= 0) {
                continue;
            }

            $key = $postId . ':' . $target;
            $source = strtolower(substr((string) ($post->original_language ?? ''), 0, 2));
            if ($source !== '' && $source === substr($target, 0, 2)) {
                self::$records[$key] = null;
                continue;
            }

            if (strtolower((string) ($post->translated_to ?? '')) === $target
                && trim((string) ($post->translated_name ?? '')) !== '') {
                self::$records[$key] = (object) [
                    'translated_name' => $post->translated_name,
                    'translated_description' => $post->translated_description ?? null,
                    'translated_content' => $post->translated_content ?? null,
                    'translation_driver' => $post->translation_driver ?? null,
                    'translated_at' => $post->translated_at ?? null,
                ];
                continue;
            }

            if (! array_key_exists($key, self::$records)) {
                $ids[] = $postId;
            }
        }

        $ids = array_values(array_unique($ids));
        if ($ids === []) {
            return;
        }

        if (! self::hasTranslationsTable()) {
            foreach ($ids as $id) {
                self::$records[$id . ':' . $target] = null;
            }

            return;
        }

        try {
            $rows = DB::table('grimba_post_translations')
                ->whereIn('post_id', $ids)
                ->where('locale', $target)
                ->whereNotNull('translated_name')
                ->get([
                    'post_id',
                    'translated_name',
                    'translated_description',
                    'translated_content',
                    'translation_driver',
                    'translated_at',
                ])
                ->keyBy(fn ($row) => (int) $row->post_id);

            foreach ($ids as $id) {
                self::$records[$id . ':' . $target] = $rows->get($id) ?: null;
            }
        } catch (Throwable) {
            foreach ($ids as $id) {
                self::$records[$id . ':' . $target] = null;
            }
        }
    }

    public static function isTranslated(object $post, ?string $target = null): bool
    {
        $target = $target ?: self::targetLocale();
        $source = strtolower(substr((string) ($post->original_language ?? ''), 0, 2));

        if ($source === '' || $source === strtolower(substr($target, 0, 2))) {
            return false;
        }

        return self::translationRecord($post, $target) !== null;
    }

    public static function title(object $post): string
    {
        $record = self::translationRecord($post, self::targetLocale());
        if ($record && trim((string) ($record->translated_name ?? '')) !== '') {
            return trim((string) $record->translated_name);
        }

        return (string) ($post->name ?? '');
    }

    public static function description(object $post): ?string
    {
        $record = self::translationRecord($post, self::targetLocale());
        if ($record && trim((string) ($record->translated_description ?? '')) !== '') {
            return GrimbaArticleText::stripNewsApiTruncationMarker((string) $record->translated_description);
        }

        return GrimbaArticleText::stripNewsApiTruncationMarker($post->description ?? null);
    }

    public static function body(object $post): ?string
    {
        $record = self::translationRecord($post, self::targetLocale());
        if ($record && trim((string) ($record->translated_content ?? '')) !== '') {
            return GrimbaArticleText::stripNewsApiTruncationMarker((string) $record->translated_content);
        }

        return GrimbaArticleText::stripNewsApiTruncationMarker($post->content ?? null);
    }

    public static function hasTranslatedBody(object $post, ?string $target = null): bool
    {
        $record = self::translationRecord($post, $target ?: self::targetLocale());

        return $record !== null && trim((string) ($record->translated_content ?? '')) !== '';
    }

    /**
     * S-LANG-08/09 (Vader 2026-05-17) — locale-aware NobuAI summary.
     * Returns the FR-tagged summary verbatim to FR readers, the
     * translated EN summary to EN readers, or NULL when no usable
     * version exists. Zen audit fix 2026-05-17: prevents the 145
     * FR-tagged summaries from leaking to /en readers.
     */
    public static function summary(object $post): ?string
    {
        $target = self::targetLocale();
        $summaryRaw = trim((string) ($post->summary_nobuai ?? ''));
        $summaryLocale = strtolower(substr((string) ($post->summary_nobuai_locale ?? ''), 0, 2));

        // Same-locale: serve verbatim.
        if ($summaryRaw !== '' && $summaryLocale !== '' && $summaryLocale === $target) {
            return $summaryRaw;
        }

        // Different locale: look for a translated version on the join table.
        $record = self::translationRecord($post, $target);
        if ($record !== null && trim((string) ($record->translated_summary ?? '')) !== '') {
            return (string) $record->translated_summary;
        }

        // No same-locale and no translation. We could fall back to the
        // raw summary in the wrong locale, but that would re-introduce
        // the 145-row leak Zen flagged. Return null — the caller decides
        // whether to hide the panel or show the raw text with a
        // disclosure.
        return null;
    }

    /**
     * Whether to surface the NobuAI summary panel at all. True when a
     * locale-appropriate summary exists; the article view can hide the
     * panel cleanly when this returns false.
     */
    public static function hasUsableSummary(object $post): bool
    {
        return self::summary($post) !== null;
    }

    public static function excerpt(object $post, int $limit = 160): string
    {
        return Str::limit(strip_tags((string) self::description($post)), $limit);
    }

    public static function publishedAt(object $post): ?CarbonImmutable
    {
        return GrimbaPostRecency::value($post);
    }

    public static function orderForTargetLocale(mixed $query, ?string $target = null, bool $withRecency = true): mixed
    {
        $target = strtolower(substr($target ?: self::targetLocale(), 0, 2));
        if (! in_array($target, ['fr', 'en'], true)) {
            $target = 'fr';
        }

        [$sql, $bindings] = self::languagePrioritySql($target);

        $query->orderByRaw($sql, $bindings);

        return $withRecency ? GrimbaPostRecency::orderByPublished($query) : $query;
    }

    /**
     * S-LSAT-02 (Vader 2026-05-18) — strict reader-language surfacing.
     *
     * Adds a WHERE clause to the query so ONLY posts that are either
     * already in the target locale OR have a translation in that locale
     * are returned. Wrong-locale posts with no translation, and
     * unclassified NULL-language posts, are excluded.
     *
     * Use this on hard-filter surfaces (`/breaking`, `/latest`, home
     * rails). Lists that should remain inclusive (`/search`, multi-side
     * dossiers) keep using the soft `orderForTargetLocale()` ranker.
     *
     * Non-breaking: callers must opt in. The ranker `orderForTargetLocale`
     * stays untouched.
     *
     * Internal-only: when `$target` is invalid (anything other than
     * `fr`/`en`), the method is a no-op and the query passes through
     * unchanged — the caller's downstream order remains.
     *
     * @param mixed   $query       Eloquent or Query Builder instance.
     * @param ?string $target      Override the locale; defaults to active reader locale.
     * @param bool    $applyOrder  When true, ALSO applies the soft ranker so within
     *                             the filtered set, native-locale posts still come
     *                             before translated ones. Default false (Zen audit
     *                             2026-05-18) so we don't double-order when callers
     *                             already tap `orderForTargetLocale` themselves —
     *                             callers must opt in explicitly when they want it.
     */
    public static function filterForTargetLocale(mixed $query, ?string $target = null, bool $applyOrder = false): mixed
    {
        $target = strtolower(substr($target ?: self::targetLocale(), 0, 2));
        if (! in_array($target, ['fr', 'en'], true)) {
            return $query;
        }

        $query->where(function ($q) use ($target): void {
            // Branch A — post is natively in the target locale.
            $q->whereRaw("lower(substr(coalesce(posts.original_language, ''), 1, 2)) = ?", [$target]);

            // Branch B — post has an in-row translation to the target.
            $q->orWhere(function ($inner) use ($target): void {
                $inner->whereRaw("lower(substr(coalesce(posts.translated_to, ''), 1, 2)) = ?", [$target])
                      ->whereNotNull('posts.translated_name')
                      ->whereRaw("trim(posts.translated_name) != ''");
            });

            // Branch C — post has a join-table translation row.
            if (self::hasTranslationsTable()) {
                $q->orWhereExists(function ($exists) use ($target): void {
                    $exists->select(DB::raw(1))
                        ->from('grimba_post_translations')
                        ->whereColumn('grimba_post_translations.post_id', 'posts.id')
                        ->whereRaw('lower(grimba_post_translations.locale) = ?', [$target])
                        ->whereNotNull('grimba_post_translations.translated_name')
                        ->whereRaw("trim(grimba_post_translations.translated_name) != ''");
                });
            }
        });

        if ($applyOrder) {
            self::orderForTargetLocale($query, $target);
        }

        return $query;
    }

    public static function rankForTargetLocale(object $post, ?string $target = null): int
    {
        $target = strtolower(substr($target ?: self::targetLocale(), 0, 2));
        if (! in_array($target, ['fr', 'en'], true)) {
            $target = 'fr';
        }

        $source = strtolower(substr((string) ($post->original_language ?? ''), 0, 2));

        // S-LANG-05 (Vader 2026-05-16): NULL-language posts are pushed
        // to LAST (rank 3) — we don't preferentially serve content we
        // can't confidently label. Wrong-locale-with-no-translation
        // still ranks above NULL because at least it's labeled.
        if ($source === $target) {
            return 0;
        }

        if ($source !== '' && self::isTranslated($post, $target)) {
            return 1;
        }

        if ($source !== '') {
            return 2; // labeled wrong-locale (no translation yet)
        }

        return 3; // unclassified — push to last
    }

    /**
     * @return array{0: string, 1: array<int, string>}
     */
    protected static function languagePrioritySql(string $target): array
    {
        $existsSql = '';
        if (self::hasTranslationsTable()) {
            $existsSql = " OR EXISTS (
                SELECT 1
                FROM grimba_post_translations gpt
                WHERE gpt.post_id = posts.id
                  AND lower(gpt.locale) = ?
                  AND gpt.translated_name IS NOT NULL
                  AND trim(gpt.translated_name) != ''
            )";
        }

        $bindings = [$target, $target];
        if ($existsSql !== '') {
            $bindings[] = $target;
        }

        // S-LANG-05 (Vader 2026-05-16) — NULL now ranks LAST so we
        // don't preferentially serve content we can't confidently label.
        // Order: same-locale (0) → translated (1) → labeled wrong-locale
        // (2) → unclassified NULL (3).
        return [
            "CASE
                WHEN lower(substr(coalesce(posts.original_language, ''), 1, 2)) = ? THEN 0
                WHEN (
                    lower(substr(coalesce(posts.translated_to, ''), 1, 2)) = ?
                    AND posts.translated_name IS NOT NULL
                    AND trim(posts.translated_name) != ''
                ){$existsSql} THEN 1
                WHEN coalesce(posts.original_language, '') != '' THEN 2
                ELSE 3
            END",
            $bindings,
        ];
    }

    protected static function translationRecord(object $post, string $target): ?object
    {
        $target = strtolower(substr($target, 0, 8));
        $postId = (int) ($post->id ?? 0);
        if ($postId <= 0 || ! in_array(substr($target, 0, 2), ['fr', 'en'], true)) {
            return null;
        }

        $source = strtolower(substr((string) ($post->original_language ?? ''), 0, 2));
        if ($source !== '' && $source === substr($target, 0, 2)) {
            return null;
        }

        if (strtolower((string) ($post->translated_to ?? '')) === $target
            && trim((string) ($post->translated_name ?? '')) !== '') {
            return (object) [
                'translated_name' => $post->translated_name,
                'translated_description' => $post->translated_description ?? null,
                'translated_content' => $post->translated_content ?? null,
                'translation_driver' => $post->translation_driver ?? null,
                'translated_at' => $post->translated_at ?? null,
            ];
        }

        $key = $postId . ':' . $target;
        if (array_key_exists($key, self::$records)) {
            return self::$records[$key];
        }

        try {
            if (! self::hasTranslationsTable()) {
                return self::$records[$key] = null;
            }

            $record = DB::table('grimba_post_translations')
                ->where('post_id', $postId)
                ->where('locale', $target)
                ->whereNotNull('translated_name')
                ->first([
                    'translated_name',
                    'translated_description',
                    'translated_content',
                    'translation_driver',
                    'translated_at',
                ]);

            return self::$records[$key] = $record ?: null;
        } catch (Throwable) {
            return self::$records[$key] = null;
        }
    }

    protected static function hasTranslationsTable(): bool
    {
        if (self::$hasTranslationsTable !== null) {
            return self::$hasTranslationsTable;
        }

        try {
            return self::$hasTranslationsTable = Schema::hasTable('grimba_post_translations');
        } catch (Throwable) {
            return self::$hasTranslationsTable = false;
        }
    }
}
