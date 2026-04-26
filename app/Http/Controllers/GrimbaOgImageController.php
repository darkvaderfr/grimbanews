<?php

namespace App\Http\Controllers;

use Botble\Blog\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class GrimbaOgImageController
{
    // 1200 × 630 is the OG 1.91:1 standard.
    private const W = 1200;
    private const H = 630;

    private const PAPER      = [246, 241, 232];
    private const INK        = [26, 23, 19];
    private const INK_SOFT   = [107, 100, 89];
    private const RULE       = [26, 23, 19, 102]; // 0.4 alpha
    private const LEFT       = [59, 130, 246];
    private const CENTER     = [168, 168, 168];
    private const RIGHT      = [232, 76, 61];

    public function show(Request $request, int $postId): Response
    {
        $post = Post::query()
            ->where('id', $postId)
            ->where('status', 'published')
            ->first();

        abort_if(! $post, 404);

        $cacheKey = 'og/post-' . $postId . '-' . substr(md5($post->updated_at ?? $post->name), 0, 8) . '.png';
        $cachePath = storage_path('app/public/' . $cacheKey);

        if (! File::exists($cachePath)) {
            File::ensureDirectoryExists(dirname($cachePath));
            $this->render($post, $cachePath);
        }

        return response(File::get($cachePath), 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Default / homepage OG image: GrimbaNews wordmark + tagline on paper.
     * Cached forever unless the file is deleted; re-rendered on miss.
     */
    public function home(): Response
    {
        $cachePath = storage_path('app/public/og/home.png');

        if (! File::exists($cachePath)) {
            File::ensureDirectoryExists(dirname($cachePath));
            $this->renderHome($cachePath);
        }

        return response(File::get($cachePath), 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    public function surface(string $surface): Response
    {
        abort_unless(in_array($surface, ['local', 'coffre'], true), 404);

        $cachePath = storage_path('app/public/og/' . $surface . '.png');

        if (! File::exists($cachePath)) {
            File::ensureDirectoryExists(dirname($cachePath));
            $this->renderSurface($surface, $cachePath);
        }

        return response(File::get($cachePath), 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    public function story(Request $request, int $clusterId): Response
    {
        $posts = Post::query()
            ->where('story_cluster_id', $clusterId)
            ->where('status', 'published')
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'translated_name', 'description', 'translated_description', 'source_name', 'bias_rating', 'updated_at']);

        abort_if($posts->count() < 2, 404);

        $latest = optional($posts->max('updated_at'))->timestamp ?? time();
        $cacheKey = 'og/story-' . $clusterId . '-' . $posts->count() . '-' . $latest . '.png';
        $cachePath = storage_path('app/public/' . $cacheKey);

        if (! File::exists($cachePath)) {
            File::ensureDirectoryExists(dirname($cachePath));
            $this->renderStory($posts, $cachePath);
        }

        return response(File::get($cachePath), 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function renderHome(string $outPath): void
    {
        $img = imagecreatetruecolor(self::W, self::H);

        $paper   = imagecolorallocate($img, self::PAPER[0], self::PAPER[1], self::PAPER[2]);
        $ink     = imagecolorallocate($img, self::INK[0], self::INK[1], self::INK[2]);
        $inkSoft = imagecolorallocate($img, self::INK_SOFT[0], self::INK_SOFT[1], self::INK_SOFT[2]);
        $tan     = imagecolorallocate($img, 220, 200, 160);
        $left    = imagecolorallocate($img, self::LEFT[0], self::LEFT[1], self::LEFT[2]);
        $center  = imagecolorallocate($img, self::CENTER[0], self::CENTER[1], self::CENTER[2]);
        $right   = imagecolorallocate($img, self::RIGHT[0], self::RIGHT[1], self::RIGHT[2]);

        imagefill($img, 0, 0, $paper);
        imagefilledrectangle($img, 0, 0, self::W, 16, $tan);

        $serifFont = $this->findFont(['Georgia Bold.ttf', 'Georgia.ttf', 'Times New Roman Bold.ttf']);
        $sansFont  = $this->findFont(['Arial Bold.ttf', 'Arial.ttf', 'Helvetica.ttc']);

        // Big centered wordmark — measure so "News" never collides with "GRIMBA"
        $markFont = $sansFont;
        $tagFont  = $serifFont ?? $sansFont;
        $markText = 'GRIMBA';
        $markX = 80;
        $markY = 170;
        $this->ttfText($img, $markFont, 68, $markX, $markY, $ink, $markText);
        $markBbox  = imagettfbbox(68, 0, $markFont, $markText);
        $markWidth = $markBbox[2] - $markBbox[0];
        $this->ttfText($img, $tagFont, 56, $markX + $markWidth + 24, $markY, $inkSoft, 'News');

        // Tagline
        $this->ttfText($img, $serifFont ?? $sansFont, 48, 80, 310, $ink, 'Voyez chaque angle');
        $this->ttfText($img, $serifFont ?? $sansFont, 48, 80, 374, $ink, 'de chaque histoire.');

        // Subtext
        $this->ttfText($img, $sansFont, 22, 80, 440, $inkSoft, 'Biais classés. Angles morts repérés. Sources comparées.');
        $this->ttfText($img, $sansFont, 22, 80, 476, $inkSoft, 'En français — et désormais en anglais.');

        // L/C/R bar strip at bottom
        $barY = self::H - 90;
        imagefilledrectangle($img, 80, $barY, 480, $barY + 10, $left);
        imagefilledrectangle($img, 480, $barY, 800, $barY + 10, $center);
        imagefilledrectangle($img, 800, $barY, self::W - 80, $barY + 10, $right);

        $this->ttfText($img, $sansFont, 18, 80, $barY + 40, $inkSoft, 'GAUCHE   ·   CENTRE   ·   DROITE');

        // Corner mark
        $this->ttfText($img, $sansFont, 18, self::W - 220, self::H - 50, $inkSoft, 'grimbanews.com');

        imagepng($img, $outPath, 6);
        imagedestroy($img);
    }

    private function renderSurface(string $surface, string $outPath): void
    {
        $copy = [
            'local' => [
                'kicker' => 'LOCAL',
                'title' => 'Votre actualité locale',
                'lines' => ['Sources proches. Angles comparés.', 'Votre ville replacée dans le contexte national.'],
                'accent' => [34, 197, 94],
            ],
            'coffre' => [
                'kicker' => 'COFFRE',
                'title' => "Votre veille, prête à reprendre",
                'lines' => ['Articles sauvegardés. Biais filtrables.', 'Export CSV pour garder vos lectures utiles.'],
                'accent' => [59, 130, 246],
            ],
        ][$surface];

        $img = imagecreatetruecolor(self::W, self::H);

        $paper   = imagecolorallocate($img, self::PAPER[0], self::PAPER[1], self::PAPER[2]);
        $ink     = imagecolorallocate($img, self::INK[0], self::INK[1], self::INK[2]);
        $inkSoft = imagecolorallocate($img, self::INK_SOFT[0], self::INK_SOFT[1], self::INK_SOFT[2]);
        $tan     = imagecolorallocate($img, 220, 200, 160);
        $accent  = imagecolorallocate($img, $copy['accent'][0], $copy['accent'][1], $copy['accent'][2]);
        $left    = imagecolorallocate($img, self::LEFT[0], self::LEFT[1], self::LEFT[2]);
        $center  = imagecolorallocate($img, self::CENTER[0], self::CENTER[1], self::CENTER[2]);
        $right   = imagecolorallocate($img, self::RIGHT[0], self::RIGHT[1], self::RIGHT[2]);

        imagefill($img, 0, 0, $paper);
        imagefilledrectangle($img, 0, 0, self::W, 16, $tan);
        imagefilledrectangle($img, 72, 514, self::W - 72, 532, imagecolorallocatealpha($img, 26, 23, 19, 112));
        imagefilledrectangle($img, 72, 514, 460, 532, $left);
        imagefilledrectangle($img, 460, 514, 770, 532, $center);
        imagefilledrectangle($img, 770, 514, self::W - 72, 532, $right);

        $serifFont = $this->findFont(['Georgia Bold.ttf', 'Georgia.ttf', 'Times New Roman Bold.ttf']);
        $sansFont  = $this->findFont(['Arial Bold.ttf', 'Arial.ttf', 'Helvetica.ttc']);
        $monoFont  = $this->findFont(['Menlo.ttc', 'Courier New Bold.ttf', 'Courier.ttc']) ?? $sansFont;

        $this->ttfText($img, $monoFont ?? $sansFont, 30, 72, 90, $ink, 'GRIMBA');
        $this->ttfText($img, $serifFont ?? $sansFont, 28, 218, 90, $inkSoft, 'News');
        $this->ttfText($img, $monoFont ?? $sansFont, 18, 72, 146, $accent, $copy['kicker']);

        $y = 250;
        foreach (array_slice($this->wrapText($serifFont ?? $sansFont, 60, $copy['title'], self::W - 144), 0, 3) as $line) {
            $this->ttfText($img, $serifFont ?? $sansFont, 60, 72, $y, $ink, $line);
            $y += 78;
        }

        $y += 12;
        foreach ($copy['lines'] as $line) {
            $this->ttfText($img, $sansFont, 26, 72, $y, $inkSoft, $line);
            $y += 38;
        }

        $this->ttfText($img, $sansFont, 18, 72, self::H - 42, $inkSoft, 'GAUCHE · CENTRE · DROITE');
        $this->ttfText($img, $sansFont, 18, self::W - 250, self::H - 42, $inkSoft, 'grimbanews.com');

        imagepng($img, $outPath, 6);
        imagedestroy($img);
    }

    private function render(Post $post, string $outPath): void
    {
        $img = imagecreatetruecolor(self::W, self::H);

        // Palette (verbose form — PHP 8.2 forbids positional args after unpacked variadic)
        $paper    = imagecolorallocate($img, self::PAPER[0], self::PAPER[1], self::PAPER[2]);
        $ink      = imagecolorallocate($img, self::INK[0], self::INK[1], self::INK[2]);
        $inkSoft  = imagecolorallocate($img, self::INK_SOFT[0], self::INK_SOFT[1], self::INK_SOFT[2]);
        $rule     = imagecolorallocatealpha($img, 26, 23, 19, 95);
        $left     = imagecolorallocate($img, self::LEFT[0], self::LEFT[1], self::LEFT[2]);
        $center   = imagecolorallocate($img, self::CENTER[0], self::CENTER[1], self::CENTER[2]);
        $right    = imagecolorallocate($img, self::RIGHT[0], self::RIGHT[1], self::RIGHT[2]);

        // Background
        imagefill($img, 0, 0, $paper);

        // Top tan strip (GrimbaNews brand color)
        $tan = imagecolorallocate($img, 220, 200, 160);
        imagefilledrectangle($img, 0, 0, self::W, 12, $tan);

        // Fonts (macOS — Linux bundled TTFs would be under public/themes/echo/fonts/)
        $serifFont = $this->findFont(['Georgia Bold.ttf', 'Georgia.ttf', 'Times New Roman Bold.ttf']);
        $sansFont  = $this->findFont(['Arial Bold.ttf', 'Arial.ttf', 'Helvetica.ttc']);
        $monoFont  = $this->findFont(['Menlo.ttc', 'Courier New Bold.ttf', 'Courier.ttc']) ?? $sansFont;

        // Wordmark — top left
        $this->ttfText($img, $monoFont ?? $sansFont, 32, 60, 92, $ink, 'GRIMBA');
        $this->ttfText($img, $serifFont ?? $sansFont, 28, 215, 92, $inkSoft, 'News');

        // Hairline separator
        imagefilledrectangle($img, 60, 120, self::W - 60, 121, $rule);

        // Title — wrapped
        $title = (string) $post->name;
        $titleLines = $this->wrapText($serifFont ?? $sansFont, 58, $title, self::W - 120);
        $y = 200;
        foreach (array_slice($titleLines, 0, 4) as $line) {
            $this->ttfText($img, $serifFont ?? $sansFont, 58, 60, $y, $ink, $line);
            $y += 76;
        }

        // Description — 2 lines
        if ($y < 440 && $post->description) {
            $desc = strip_tags($post->description);
            $descLines = $this->wrapText($sansFont, 26, $desc, self::W - 120);
            $y += 12;
            foreach (array_slice($descLines, 0, 2) as $line) {
                $this->ttfText($img, $sansFont, 26, 60, $y, $inkSoft, $line);
                $y += 36;
            }
        }

        // Bottom row — source + bias + coverage bar
        $footerY = self::H - 110;

        // Source name
        if ($post->source_name) {
            $this->ttfText($img, $sansFont, 22, 60, $footerY, $inkSoft, 'SOURCE');
            $this->ttfText($img, $serifFont ?? $sansFont, 34, 60, $footerY + 42, $ink, $post->source_name);
        }

        // Bias chip on the right
        $biasLabel = match ($post->bias_rating) {
            'left'   => ['Gauche', self::LEFT],
            'center' => ['Centre', self::CENTER],
            'right'  => ['Droite', self::RIGHT],
            default  => null,
        };
        if ($biasLabel) {
            [$label, $rgb] = $biasLabel;
            $biasColor = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
            $bgColor   = imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], 90);
            $pillX     = self::W - 260;
            $pillY     = $footerY + 10;
            $this->roundedRect($img, $pillX, $pillY, 200, 44, $bgColor, 22);
            $this->ttfText($img, $sansFont, 20, $pillX + 22, $pillY + 28, $biasColor, strtoupper($label));
        }

        // Coverage bar at very bottom if cluster has ≥2 biases
        if ($post->story_cluster_id) {
            $cluster = Post::query()
                ->where('story_cluster_id', $post->story_cluster_id)
                ->where('status', 'published')
                ->get(['bias_rating']);
            $counts = ['left' => 0, 'center' => 0, 'right' => 0];
            foreach ($cluster as $c) {
                if (isset($counts[$c->bias_rating])) $counts[$c->bias_rating]++;
            }
            $sides = array_filter($counts, fn ($n) => $n > 0);
            if (count($sides) >= 2) {
                $total = array_sum($counts);
                $barY  = self::H - 18;
                $barH  = 10;
                $x     = 60;
                $w     = self::W - 120;
                $colors = ['left' => $left, 'center' => $center, 'right' => $right];
                foreach (['left', 'center', 'right'] as $side) {
                    $segW = (int) round($counts[$side] * $w / $total);
                    if ($segW > 0) {
                        imagefilledrectangle($img, $x, $barY, $x + $segW, $barY + $barH, $colors[$side]);
                    }
                    $x += $segW;
                }
            }
        }

        imagepng($img, $outPath, 6);
        imagedestroy($img);
    }

    private function renderStory($posts, string $outPath): void
    {
        $img = imagecreatetruecolor(self::W, self::H);

        $paper   = imagecolorallocate($img, self::PAPER[0], self::PAPER[1], self::PAPER[2]);
        $ink     = imagecolorallocate($img, self::INK[0], self::INK[1], self::INK[2]);
        $inkSoft = imagecolorallocate($img, self::INK_SOFT[0], self::INK_SOFT[1], self::INK_SOFT[2]);
        $rule    = imagecolorallocatealpha($img, 26, 23, 19, 95);
        $tan     = imagecolorallocate($img, 220, 200, 160);
        $left    = imagecolorallocate($img, self::LEFT[0], self::LEFT[1], self::LEFT[2]);
        $center  = imagecolorallocate($img, self::CENTER[0], self::CENTER[1], self::CENTER[2]);
        $right   = imagecolorallocate($img, self::RIGHT[0], self::RIGHT[1], self::RIGHT[2]);

        imagefill($img, 0, 0, $paper);
        imagefilledrectangle($img, 0, 0, self::W, 16, $tan);

        $serifFont = $this->findFont(['Georgia Bold.ttf', 'Georgia.ttf', 'Times New Roman Bold.ttf']);
        $sansFont  = $this->findFont(['Arial Bold.ttf', 'Arial.ttf', 'Helvetica.ttc']);
        $monoFont  = $this->findFont(['Menlo.ttc', 'Courier New Bold.ttf', 'Courier.ttc']) ?? $sansFont;

        $head = $posts->first();
        $title = trim((string) ($head->translated_name ?: $head->name));
        $sources = $posts->pluck('source_name')->filter()->unique()->values();
        $sourceCount = $sources->count();
        $counts = ['left' => 0, 'center' => 0, 'right' => 0, 'unknown' => 0];
        foreach ($posts as $post) {
            $bias = $post->bias_rating ?? 'unknown';
            $counts[$bias] = ($counts[$bias] ?? 0) + 1;
        }
        $total = max(1, $counts['left'] + $counts['center'] + $counts['right']);

        $this->ttfText($img, $monoFont ?? $sansFont, 30, 60, 88, $ink, 'GRIMBA');
        $this->ttfText($img, $serifFont ?? $sansFont, 28, 206, 88, $inkSoft, 'News');
        $this->ttfText($img, $monoFont ?? $sansFont, 18, 60, 132, $inkSoft, 'DOSSIER MULTI-SOURCES');
        imagefilledrectangle($img, 60, 152, self::W - 60, 153, $rule);

        $y = 230;
        foreach (array_slice($this->wrapText($serifFont ?? $sansFont, 54, $title, self::W - 120), 0, 4) as $line) {
            $this->ttfText($img, $serifFont ?? $sansFont, 54, 60, $y, $ink, $line);
            $y += 70;
        }

        $summaryY = min($y + 20, 500);
        $this->ttfText($img, $sansFont, 24, 60, $summaryY, $inkSoft, $posts->count() . ' articles · ' . $sourceCount . ' sources · couverture comparée');

        $barX = 60;
        $barY = self::H - 92;
        $barW = self::W - 120;
        $barH = 18;
        imagefilledrectangle($img, $barX, $barY, $barX + $barW, $barY + $barH, imagecolorallocatealpha($img, 26, 23, 19, 110));

        $colors = ['left' => $left, 'center' => $center, 'right' => $right];
        $x = $barX;
        foreach (['left', 'center', 'right'] as $side) {
            $segW = (int) round($counts[$side] * $barW / $total);
            if ($segW > 0) {
                imagefilledrectangle($img, $x, $barY, $x + $segW, $barY + $barH, $colors[$side]);
            }
            $x += $segW;
        }

        $this->ttfText($img, $sansFont, 19, 60, self::H - 42, $left, 'GAUCHE ' . $counts['left']);
        $this->ttfText($img, $sansFont, 19, 240, self::H - 42, $center, 'CENTRE ' . $counts['center']);
        $this->ttfText($img, $sansFont, 19, 430, self::H - 42, $right, 'DROITE ' . $counts['right']);
        $this->ttfText($img, $sansFont, 18, self::W - 250, self::H - 42, $inkSoft, 'grimbanews.com');

        imagepng($img, $outPath, 6);
        imagedestroy($img);
    }

    private function findFont(array $candidates): ?string
    {
        // Bundled fonts win — same render on macOS dev and Linux prod.
        $bundled = public_path('themes/echo/fonts');
        $bundledMap = [
            // serif candidates → Fraunces-Bold
            'Fraunces-Bold.ttf'       => 'Fraunces-Bold.ttf',
            'Georgia Bold.ttf'        => 'Fraunces-Bold.ttf',
            'Georgia.ttf'             => 'Fraunces-Bold.ttf',
            'Times New Roman Bold.ttf' => 'Fraunces-Bold.ttf',
            // sans candidates → PublicSans-Bold
            'PublicSans-Bold.ttf'     => 'PublicSans-Bold.ttf',
            'Arial Bold.ttf'          => 'PublicSans-Bold.ttf',
            'Arial.ttf'               => 'PublicSans-Bold.ttf',
            'Helvetica.ttc'           => 'PublicSans-Bold.ttf',
        ];
        foreach ($candidates as $name) {
            if (isset($bundledMap[$name])) {
                $p = $bundled . '/' . $bundledMap[$name];
                if (File::exists($p)) return $p;
            }
        }

        // Fall back to system fonts (dev convenience on macOS).
        $roots = [
            '/System/Library/Fonts/Supplemental',
            '/System/Library/Fonts',
            '/Library/Fonts',
            '/usr/share/fonts/truetype/dejavu',
            '/usr/share/fonts/truetype/liberation',
            $bundled,
        ];
        foreach ($roots as $root) {
            foreach ($candidates as $name) {
                $p = $root . '/' . $name;
                if (File::exists($p)) return $p;
            }
        }
        $fallback = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        return File::exists($fallback) ? $fallback : null;
    }

    private function ttfText($img, ?string $font, int $size, int $x, int $y, int $color, string $text): void
    {
        if ($font && function_exists('imagettftext')) {
            imagettftext($img, $size, 0, $x, $y, $color, $font, $text);
        } else {
            imagestring($img, 5, $x, $y - 16, $text, $color);
        }
    }

    private function wrapText(?string $font, int $size, string $text, int $maxWidth): array
    {
        if (! $font || ! function_exists('imagettfbbox')) {
            return [wordwrap($text, 38, "\n", true)];
        }
        $words = preg_split('/\s+/', trim($text));
        $lines = [];
        $current = '';
        foreach ($words as $w) {
            $test = $current === '' ? $w : ($current . ' ' . $w);
            $bbox = imagettfbbox($size, 0, $font, $test);
            $width = $bbox[2] - $bbox[0];
            if ($width > $maxWidth && $current !== '') {
                $lines[] = $current;
                $current = $w;
            } else {
                $current = $test;
            }
        }
        if ($current !== '') $lines[] = $current;
        return $lines;
    }

    private function roundedRect($img, int $x, int $y, int $w, int $h, int $color, int $r): void
    {
        imagefilledrectangle($img, $x + $r, $y, $x + $w - $r, $y + $h, $color);
        imagefilledrectangle($img, $x, $y + $r, $x + $w, $y + $h - $r, $color);
        imagefilledellipse($img, $x + $r, $y + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($img, $x + $w - $r, $y + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($img, $x + $r, $y + $h - $r, $r * 2, $r * 2, $color);
        imagefilledellipse($img, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $color);
    }
}
