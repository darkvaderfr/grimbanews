<?php

namespace App\Http\Controllers;

use Botble\Blog\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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

    public function show(int $postId, Request $request): Response
    {
        $post = Post::query()->where('id', $postId)->first([
            'id', 'name', 'translated_name', 'translated_to',
            'source_name', 'source_id', 'bias_rating',
        ]);

        $source = $post->source_name ?? 'GrimbaNews';
        $title  = trim((string) ($post->translated_name ?: $post->name ?? 'Article'));
        $bias   = $post->bias_rating ?? 'unknown';

        // S102 — pull country tag for the kicker line if the post is
        // backed by a registered news_source row.
        $country = null;
        if ($post && $post->source_id) {
            $country = DB::table('news_sources')
                ->where('id', $post->source_id)
                ->value('country');
        }

        // S327 — theme-aware placeholder. Reader's theme cookie or
        // explicit ?theme=dark query forces the dark palette so the
        // editorial fallback doesn't pop as a bright cream rectangle
        // on dark-mode pages.
        $themePref = (string) $request->query('theme', $request->cookie('grimba_theme', 'auto'));
        $dark = $themePref === 'dark';

        $svg = $this->render($source, $title, $bias, $country, $dark);

        return response($svg, 200, [
            'Content-Type'  => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
            'Vary'          => 'Cookie, ' . ($request->headers->get('Vary') ?? ''),
            'X-GN-Source'   => 'placeholder' . ($dark ? '-dark' : ''),
        ]);
    }

    private function render(string $source, string $title, string $bias, ?string $country = null, bool $dark = false): string
    {
        $w = self::W;
        $h = self::H;

        $stripeColor = match ($bias) {
            'left'   => '#3b82f6',
            'right'  => '#e84c3d',
            'center' => '#a8a8a8',
            default  => '#6b6459',
        };

        $biasLabel = match ($bias) {
            'left'   => 'GAUCHE',
            'right'  => 'DROITE',
            'center' => 'CENTRE',
            default  => '—',
        };

        // S102 — deterministic source-derived hue accent so each
        // outlet's placeholder is visually distinct. S327 — flip
        // saturation/lightness to keep the wash subtle in dark mode too.
        $hue = $this->sourceHue($source);
        $sourceWash = $dark ? "hsl({$hue}, 28%, 14%)" : "hsl({$hue}, 25%, 90%)";

        // Editorial palette (matches --gn-paper / --gn-ink tokens, with
        // dark counterparts from grimba-home.css's [data-bs-theme="dark"]).
        $paper = $dark ? '#121007' : '#f6f1e8';
        $ink   = $dark ? '#f6f1e8' : '#1a1713';
        $rule  = $dark ? '#f6f1e866' : '#1a171366';

        $sourceEsc = htmlspecialchars(mb_strtoupper($source), ENT_QUOTES | ENT_XML1, 'UTF-8');
        $kickerCountry = $country
            ? htmlspecialchars(mb_strtoupper($country), ENT_QUOTES | ENT_XML1, 'UTF-8')
            : 'GRIMBANEWS';

        // Title: wrap to 3 lines of ~32 chars. Truncate with em-dash if
        // it overflows. Serif headline rendered via generic font-family
        // so the client picks up whatever "Fraunces"-alike it has.
        $lines = $this->wrapTitle($title, 32, 3);
        $titleY = 320;
        $titleLineHeight = 74;

        $titleSvg = '';
        foreach ($lines as $i => $line) {
            $y = $titleY + ($i * $titleLineHeight);
            $esc = htmlspecialchars($line, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $titleSvg .= "\n    <text x=\"120\" y=\"{$y}\" fill=\"{$ink}\" font-size=\"62\" font-family=\"'Fraunces','Playfair Display',Georgia,serif\" font-weight=\"600\" letter-spacing=\"-0.5\">{$esc}</text>";
        }

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$w} {$h}" width="{$w}" height="{$h}" role="img" aria-label="GrimbaNews · {$sourceEsc}">
    <defs>
        <pattern id="grain" x="0" y="0" width="4" height="4" patternUnits="userSpaceOnUse">
            <rect width="4" height="4" fill="{$paper}"/>
            <circle cx="1" cy="1" r="0.3" fill="{$ink}" opacity="0.05"/>
        </pattern>
        <linearGradient id="sourceWash" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="{$sourceWash}" stop-opacity="0.85"/>
            <stop offset="60%" stop-color="{$paper}" stop-opacity="1"/>
        </linearGradient>
    </defs>
    <rect width="{$w}" height="{$h}" fill="url(#grain)"/>
    <rect width="{$w}" height="240" fill="url(#sourceWash)"/>
    <rect x="0" y="0" width="32" height="{$h}" fill="{$stripeColor}"/>
    <line x1="120" y1="160" x2="1080" y2="160" stroke="{$rule}" stroke-width="1"/>
    <text x="120" y="128" fill="{$ink}" font-size="32" font-family="'Public Sans',system-ui,sans-serif" font-weight="700" letter-spacing="3">{$sourceEsc}</text>
    <text x="120" y="200" fill="{$ink}" opacity="0.55" font-size="18" font-family="'Public Sans',system-ui,sans-serif" letter-spacing="1.5">{$kickerCountry} · BIAIS {$biasLabel}</text>{$titleSvg}
    <line x1="120" y1="{$h}" y2="{$h}" x2="280" stroke="{$stripeColor}" stroke-width="3" transform="translate(0,-72)"/>
    <text x="120" y="{$h}" dy="-30" fill="{$ink}" opacity="0.5" font-size="22" font-family="'Public Sans',system-ui,sans-serif">grimbanews.com</text>
</svg>
SVG;
    }

    /**
     * Map a source name to a stable hue (0-360). Pure hash; no LUT
     * to maintain. Different sources land in different parts of the
     * spectrum so the page doesn't feel monochrome.
     */
    private function sourceHue(string $source): int
    {
        $hash = crc32($source);
        return (int) ($hash % 360);
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
