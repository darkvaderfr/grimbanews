<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class GrimbaTranslationPresenter
{
    /** @var array<string, object|null> */
    protected static array $records = [];

    public static function targetLocale(): string
    {
        $lang = (string) (request()?->cookie('grimba_lang') ?: app()->getLocale() ?: 'fr');

        return in_array($lang, ['fr', 'en'], true) ? $lang : 'fr';
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
            return (string) $record->translated_description;
        }

        return $post->description ?? null;
    }

    public static function body(object $post): ?string
    {
        $record = self::translationRecord($post, self::targetLocale());
        if ($record && trim((string) ($record->translated_content ?? '')) !== '') {
            return (string) $record->translated_content;
        }

        return $post->content ?? null;
    }

    public static function hasTranslatedBody(object $post, ?string $target = null): bool
    {
        $record = self::translationRecord($post, $target ?: self::targetLocale());

        return $record !== null && trim((string) ($record->translated_content ?? '')) !== '';
    }

    public static function excerpt(object $post, int $limit = 160): string
    {
        return Str::limit(strip_tags((string) self::description($post)), $limit);
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
            if (! Schema::hasTable('grimba_post_translations')) {
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
}
