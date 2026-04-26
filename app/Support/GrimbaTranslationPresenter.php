<?php

namespace App\Support;

use Illuminate\Support\Str;

class GrimbaTranslationPresenter
{
    public static function targetLocale(): string
    {
        $lang = (string) (request()?->cookie('grimba_lang') ?: app()->getLocale() ?: 'fr');

        return in_array($lang, ['fr', 'en'], true) ? $lang : 'fr';
    }

    public static function isTranslated(object $post, ?string $target = null): bool
    {
        $target = $target ?: self::targetLocale();
        $source = strtolower(substr((string) ($post->original_language ?? ''), 0, 2));

        if ($target !== 'fr' || $source === '' || $source === 'fr') {
            return false;
        }

        return strtolower((string) ($post->translated_to ?? '')) === 'fr'
            && trim((string) ($post->translated_name ?? '')) !== '';
    }

    public static function title(object $post): string
    {
        if (self::isTranslated($post)) {
            return trim((string) $post->translated_name);
        }

        return (string) ($post->name ?? '');
    }

    public static function description(object $post): ?string
    {
        if (self::isTranslated($post) && trim((string) ($post->translated_description ?? '')) !== '') {
            return (string) $post->translated_description;
        }

        return $post->description ?? null;
    }

    public static function body(object $post): ?string
    {
        if (self::isTranslated($post) && trim((string) ($post->translated_content ?? '')) !== '') {
            return (string) $post->translated_content;
        }

        return $post->content ?? null;
    }

    public static function excerpt(object $post, int $limit = 160): string
    {
        return Str::limit(strip_tags((string) self::description($post)), $limit);
    }
}
