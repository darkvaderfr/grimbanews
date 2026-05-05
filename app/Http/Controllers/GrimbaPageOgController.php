<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * S350 — per-page OG image generator for static editorial pages
 * (explainer, FAQ, about, methodology). Mirrors GrimbaPlaceholderController's
 * SVG approach — cheap, no GD, no file cache, response is HTTP-cacheable.
 *
 * Route signature: /og/page?title=<urlenc>&kicker=<urlenc>
 *
 * Output: 1200×630 SVG card with the editorial paper palette, the
 * GrimbaNews wordmark, the kicker uppercased, the title in Fraunces.
 */
class GrimbaPageOgController
{
    private const W = 1200;
    private const H = 630;

    public function show(Request $request): Response
    {
        $titleRaw  = (string) $request->query('title', 'GrimbaNews');
        $kickerRaw = (string) $request->query('kicker', 'GrimbaNews');

        $title = mb_substr(trim($titleRaw), 0, 120) ?: 'GrimbaNews';
        $kicker = mb_strtoupper(mb_substr(trim($kickerRaw), 0, 40)) ?: 'GRIMBANEWS';

        $svg = $this->render($title, $kicker);

        return response($svg, 200, [
            'Content-Type'  => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
            'X-GN-Source'   => 'page-og',
        ]);
    }

    private function render(string $title, string $kicker): string
    {
        $w = self::W;
        $h = self::H;

        $paper = '#f6f1e8';
        $ink   = '#1a1713';
        $accent = '#c0392b';

        $kickerEsc = htmlspecialchars($kicker, ENT_QUOTES | ENT_XML1, 'UTF-8');

        // Wrap title to 3 lines max, ~28 chars each (Fraunces 60px @1200w).
        $lines = $this->wrapTitle($title, 28, 3);
        $titleY = 312;
        $lineH = 76;
        $titleSvg = '';
        foreach ($lines as $i => $line) {
            $y = $titleY + ($i * $lineH);
            $esc = htmlspecialchars($line, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $titleSvg .= "\n    <text x=\"112\" y=\"{$y}\" fill=\"{$ink}\" font-size=\"60\" font-family=\"'Fraunces','Playfair Display',Georgia,serif\" font-weight=\"600\" letter-spacing=\"-0.6\">{$esc}</text>";
        }

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$w} {$h}" width="{$w}" height="{$h}" role="img" aria-label="GrimbaNews · {$kickerEsc}">
    <defs>
        <pattern id="grain" x="0" y="0" width="4" height="4" patternUnits="userSpaceOnUse">
            <rect width="4" height="4" fill="{$paper}"/>
            <circle cx="1" cy="1" r="0.3" fill="{$ink}" opacity="0.05"/>
        </pattern>
    </defs>
    <rect width="{$w}" height="{$h}" fill="url(#grain)"/>
    <rect x="0" y="0" width="32" height="{$h}" fill="{$accent}"/>
    <text x="112" y="138" fill="{$ink}" opacity="0.55" font-size="22" font-family="'Public Sans',system-ui,sans-serif" font-weight="600" letter-spacing="3">{$kickerEsc}</text>
    <line x1="112" y1="172" x2="1080" y2="172" stroke="{$ink}" stroke-opacity="0.2" stroke-width="1"/>{$titleSvg}
    <text x="112" y="{$h}" dy="-46" fill="{$ink}" opacity="0.55" font-size="22" font-family="'Public Sans',system-ui,sans-serif">grimbanews.com</text>
    <text x="112" y="{$h}" dy="-22" fill="{$accent}" font-size="22" font-family="'Fraunces','Playfair Display',Georgia,serif" font-weight="700">Grimba<tspan fill="{$ink}" opacity="0.85">News</tspan></text>
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
