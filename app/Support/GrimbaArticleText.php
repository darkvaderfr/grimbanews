<?php

namespace App\Support;

class GrimbaArticleText
{
    public const MIN_READABLE_CHARS = 200;

    public static function stripNewsApiTruncationMarker(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = (string) $value;
        $pattern = '/(?:\s|&nbsp;)*(?:…|&hellip;|&#8230;|&#x2026;|\.{3})?(?:\s|&nbsp;)*\[\+\d+\s+chars?\](?:\s|&nbsp;)*(?=(?:<\/[^>]+>\s*)*$)/iu';

        do {
            $previous = $clean;
            $clean = preg_replace($pattern, '', $clean) ?? $clean;
        } while ($clean !== $previous);

        return trim($clean);
    }

    public static function textLength(?string $html): int
    {
        $text = trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return mb_strlen($text);
    }

    public static function cleanIngestBody(?string $html): ?string
    {
        $clean = self::stripNewsApiTruncationMarker($html);
        if ($clean === null || $clean === '') {
            return null;
        }

        $clean = preg_replace(
            '#^\s*<p>\s*<a\b[^>]*>[^<]*(?:article original|original article|read original|lire l)[^<]*</a>\s*</p>\s*#iu',
            '',
            $clean
        ) ?? $clean;

        return trim($clean) !== '' ? trim($clean) : null;
    }

    public static function firstHttpUrlFromHtml(?string $html): ?string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return null;
        }

        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $loaded = $dom->loadHTML('<?xml encoding="utf-8"><div>' . $html . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            return null;
        }

        foreach ($dom->getElementsByTagName('a') as $anchor) {
            $href = trim((string) $anchor->getAttribute('href'));
            if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
                return html_entity_decode($href, ENT_QUOTES, 'UTF-8');
            }
        }

        return null;
    }

    public static function readableBody(object $post, int $minChars = self::MIN_READABLE_CHARS): ?object
    {
        $full = self::stripNewsApiTruncationMarker($post->full_content ?? null);
        if (self::textLength($full) >= $minChars) {
            return (object) [
                'html' => $full,
                'source' => 'full',
                'is_full' => true,
            ];
        }

        $ingest = self::cleanIngestBody($post->content ?? null);
        if (self::textLength($ingest) >= $minChars) {
            return (object) [
                'html' => $ingest,
                'source' => 'ingest',
                'is_full' => false,
            ];
        }

        $description = self::stripNewsApiTruncationMarker($post->description ?? null);
        if (self::textLength($description) >= $minChars) {
            return (object) [
                'html' => '<p>' . e(strip_tags((string) $description)) . '</p>',
                'source' => 'description',
                'is_full' => false,
            ];
        }

        return null;
    }
}
