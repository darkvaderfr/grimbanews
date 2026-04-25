<?php
/**
 * S118 — favicon generator. Renders square GrimbaNews mark via GD
 * (paper bg, serif "G", red dot accent) at the standard favicon
 * sizes plus a 180×180 apple-touch-icon and a multi-size .ico.
 *
 * Run: php scripts/build-favicons.php
 *
 * Outputs to public/:
 *   favicon-16x16.png
 *   favicon-32x32.png
 *   favicon-48x48.png
 *   apple-touch-icon.png  (180×180)
 *   favicon.ico           (multi-size 16+32+48 PNG-payload ICO)
 *
 * favicon.svg is hand-authored separately (vector, no script needed).
 */

declare(strict_types=1);

$projectRoot = realpath(__DIR__ . '/..');
$publicDir   = $projectRoot . '/public';
$fontPath    = $projectRoot . '/public/themes/echo/fonts/Fraunces-Bold.ttf';

if (! file_exists($fontPath)) {
    fwrite(STDERR, "Missing Fraunces TTF at $fontPath\n");
    exit(1);
}

/**
 * Render the mark at $size×$size as a GD image resource.
 * Paper bg, serif G, red dot in upper-right corner.
 */
function render_mark(int $size, string $fontPath)
{
    $im = imagecreatetruecolor($size, $size);
    imagealphablending($im, true);
    imagesavealpha($im, true);

    // Editorial palette — paper bg, dark "G" mirroring "Grimba" in
    // the wordmark, red accent dot mirroring "News".
    $paper = imagecolorallocate($im, 246, 241, 232);
    $ink   = imagecolorallocate($im, 26, 23, 19);
    $red   = imagecolorallocate($im, 192, 57, 43);

    imagefilledrectangle($im, 0, 0, $size - 1, $size - 1, $paper);

    // Serif "G" left-anchored so the red dot has room to read as
    // the "News" accent on its baseline (matches the wordmark's
    // two-tone rhythm). At 16px the dot would clip — render the G
    // centered + skip the dot at that size.
    $tinyMode = $size < 24;
    $fontSize = $tinyMode ? (int) round($size * 0.72) : (int) round($size * 0.7);

    $bbox = imagettfbbox($fontSize, 0, $fontPath, 'G');
    $textW = abs($bbox[2] - $bbox[0]);
    $textH = abs($bbox[7] - $bbox[1]);

    if ($tinyMode) {
        $x = (int) (($size - $textW) / 2 - $bbox[0]);
    } else {
        // Left-anchored: G occupies left ~60%, leaves ~40% for the dot.
        $x = (int) ($size * 0.20) - $bbox[0];
    }
    $y = (int) (($size + $textH) / 2 - 2);

    imagettftext($im, $fontSize, 0, $x, $y, $ink, $fontPath, 'G');

    // Red accent dot ON BASELINE next to the G, sized to feel like
    // a punctuation mark in a wordmark — bigger than the previous
    // top-right speck. Only on ≥24px (clipping at 16px).
    if (! $tinyMode) {
        $r = max(3, (int) round($size * 0.10));
        $cx = (int) ($size * 0.78);
        // Baseline-aligned vertical: matches the bottom of the G.
        $cy = (int) ($size * 0.72);
        imagefilledellipse($im, $cx, $cy, $r * 2, $r * 2, $red);
    }

    return $im;
}

function save_png($im, string $path): void
{
    imagepng($im, $path, 9);
}

/**
 * Write a multi-size .ico file with PNG-encoded payloads. Valid ICO
 * format since Vista; renders correctly in every modern browser.
 *
 * @param array<int, string> $pngPaths size→PNG file path
 */
function write_ico(array $pngPaths, string $outPath): void
{
    $count = count($pngPaths);
    $headers = [];
    $payloads = [];
    $offset = 6 + (16 * $count);

    foreach ($pngPaths as $size => $path) {
        $data = file_get_contents($path);
        $len  = strlen($data);

        $headers[] = pack(
            'CCCCvvVV',
            $size >= 256 ? 0 : $size,  // width
            $size >= 256 ? 0 : $size,  // height
            0,                          // color count
            0,                          // reserved
            1,                          // color planes
            32,                         // bits per pixel
            $len,                       // image size
            $offset                     // image data offset
        );
        $payloads[] = $data;
        $offset += $len;
    }

    $ico = pack('vvv', 0, 1, $count);
    $ico .= implode('', $headers);
    $ico .= implode('', $payloads);

    file_put_contents($outPath, $ico);
}

$sizes = [16, 32, 48, 180];
$paths = [];

foreach ($sizes as $size) {
    $im = render_mark($size, $fontPath);
    if ($size === 180) {
        $path = $publicDir . '/apple-touch-icon.png';
    } else {
        $path = $publicDir . "/favicon-{$size}x{$size}.png";
    }
    save_png($im, $path);
    imagedestroy($im);
    $paths[$size] = $path;
    fwrite(STDOUT, "wrote $path\n");
}

write_ico(
    [
        16 => $paths[16],
        32 => $paths[32],
        48 => $paths[48],
    ],
    $publicDir . '/favicon.ico'
);
fwrite(STDOUT, "wrote {$publicDir}/favicon.ico\n");

fwrite(STDOUT, "done.\n");
