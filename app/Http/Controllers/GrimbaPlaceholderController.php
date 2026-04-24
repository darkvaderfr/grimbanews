<?php

namespace App\Http\Controllers;

use Botble\Blog\Models\Post;
use Illuminate\Http\Response;

/*
 * S96 — editorial SVG placeholder for posts that have no image after
 * the RSS extraction (S79) + article-page scrape (S93) + weekly
 * enrich-drafts sweep (S84). Served as a cheap inline SVG so no GD
 * round-trip and no file-system cache needed — the response itself
 * is HTTP-cacheable for a day.
 *
 * Layout:
 *   — paper background
 *   — bias color stripe on the left edge (left=blue, center=grey,
 *     right=red, unknown=soft ink)
 *   — source wordmark stacked over the (possibly translated) title,
 *     sentence case, serif headline
 *
 * Never names "NobuAI" — this is editorial chrome, not AI output.
 */
class GrimbaPlaceholderController
{
    private const W = 1200;
    private const H = 630;

    public function show(int $postId): Response
    {
        $post = Post::query()->where('id', $postId)->first([
            'id', 'name', 'translated_name', 'translated_to',
            'source_name', 'bias_rating',
        ]);

        $source = $post->source_name ?? 'GrimbaNews';
        $title  = trim((string) ($post->translated_name ?: $post->name ?? 'Article'));
        $bias   = $post->bias_rating ?? 'unknown';

        $svg = $this->render($source, $title, $bias);

        return response($svg, 200, [
            'Content-Type'  => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
            'X-GN-Source'   => 'placeholder',
        ]);
    }

    private function render(string $source, string $title, string $bias): string
    {
        $w = self::W;
        $h = self::H;

        $stripeColor = match ($bias) {
            'left'   => '#3b82f6',
            'right'  => '#e84c3d',
            'center' => '#a8a8a8',
            default  => '#6b6459',
        };

        // Soft editorial palette (matches --gn-paper / --gn-ink tokens).
        $paper = '#f6f1e8';
        $ink   = '#1a1713';
        $rule  = '#1a171366';

        $sourceEsc = htmlspecialchars(mb_strtoupper($source), ENT_QUOTES | ENT_XML1, 'UTF-8');

        // Title: wrap to 3 lines of ~34 chars. Truncate with em-dash if
        // it overflows. Serif headline rendered via generic font-family
        // so the client picks up whatever "Fraunces"-alike it has.
        $lines = $this->wrapTitle($title, 34, 3);
        $titleY = 310;
        $titleLineHeight = 72;

        $titleSvg = '';
        foreach ($lines as $i => $line) {
            $y = $titleY + ($i * $titleLineHeight);
            $esc = htmlspecialchars($line, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $titleSvg .= "\n    <text x=\"120\" y=\"{$y}\" fill=\"{$ink}\" font-size=\"60\" font-family=\"'Fraunces','Playfair Display',Georgia,serif\" font-weight=\"600\" letter-spacing=\"-0.5\">{$esc}</text>";
        }

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$w} {$h}" width="{$w}" height="{$h}" role="img" aria-label="GrimbaNews">
    <defs>
        <pattern id="grain" x="0" y="0" width="4" height="4" patternUnits="userSpaceOnUse">
            <rect width="4" height="4" fill="{$paper}"/>
            <circle cx="1" cy="1" r="0.3" fill="{$ink}" opacity="0.05"/>
        </pattern>
    </defs>
    <rect width="{$w}" height="{$h}" fill="url(#grain)"/>
    <rect x="0" y="0" width="32" height="{$h}" fill="{$stripeColor}"/>
    <line x1="120" y1="160" x2="1080" y2="160" stroke="{$rule}" stroke-width="1"/>
    <text x="120" y="128" fill="{$ink}" font-size="28" font-family="'Public Sans',system-ui,sans-serif" font-weight="700" letter-spacing="3">{$sourceEsc}</text>
    <text x="120" y="200" fill="{$ink}" opacity="0.55" font-size="20" font-family="'Public Sans',system-ui,sans-serif" letter-spacing="1">ARTICLE · GRIMBANEWS</text>{$titleSvg}
    <text x="120" y="{$h}" dy="-48" fill="{$ink}" opacity="0.5" font-size="22" font-family="'Public Sans',system-ui,sans-serif">grimbanews.com</text>
</svg>
SVG;
    }

    /** @return array<int, string> */
    private function wrapTitle(string $title, int $perLine, int $maxLines): array
    {
        $words = preg_split('/\s+/u', trim($title)) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $w) {
            $candidate = $current === '' ? $w : $current . ' ' . $w;
            if (mb_strlen($candidate) > $perLine && $current !== '') {
                $lines[] = $current;
                $current = $w;
                if (count($lines) === $maxLines) break;
            } else {
                $current = $candidate;
            }
        }

        if (count($lines) < $maxLines && $current !== '') {
            $lines[] = $current;
        }

        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $lines[$maxLines - 1] = rtrim($lines[$maxLines - 1], ' .,;:') . ' —';
        }

        return $lines;
    }
}
