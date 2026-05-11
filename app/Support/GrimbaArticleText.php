<?php

namespace App\Support;

class GrimbaArticleText
{
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
}
