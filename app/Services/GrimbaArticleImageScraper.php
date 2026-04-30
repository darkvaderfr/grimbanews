<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/*
 * Lifts a hero image URL out of a published article page. Shared
 * between the live poller (ingest-time fallback, S93) and the
 * grimba:enrich-drafts one-shot backfill (S84).
 *
 * Strategy:
 *   1. <meta property="og:image" content="…">     — OG spec
 *   2. <meta property="og:image:url" …>           — older variant
 *   3. <meta name="twitter:image" …>              — Twitter card
 *   4. schema.org / image_src hints               — common CMS fallbacks
 *   5. JSON-LD image fields                       — structured metadata
 *   6. <img src/srcset="…jpg|png|webp|avif">      — first sane hit
 *
 * Relative / protocol-relative / origin-relative URLs are resolved
 * against the article URL before we return. We refuse to emit a
 * non-http(s) URL so we never persist a data-URI tracker.
 */
class GrimbaArticleImageScraper
{
    private const USER_AGENT = 'GrimbaNewsBot/1.0 (+https://grimbanews.com/bot)';
    private const FETCH_TIMEOUT = 15;

    /** @return array{0: ?string, 1: ?string} [url, method] */
    public function extractFromUrl(string $url): array
    {
        if ($url === '' || ! $this->looksHttp($url)) {
            return [null, null];
        }

        try {
            $res = Http::withUserAgent(self::USER_AGENT)
                ->withHeaders([
                    'Accept'          => 'text/html,application/xhtml+xml',
                    'Accept-Language' => 'fr,en;q=0.6',
                ])
                ->timeout(self::FETCH_TIMEOUT)
                ->connectTimeout(10)
                ->withOptions(['allow_redirects' => ['max' => 5]])
                ->get($url);

            if (! $res->successful()) {
                return [null, null];
            }

            $html = (string) $res->body();
            if ($html === '') {
                return [null, null];
            }

            // og:image (property-then-content OR content-then-property)
            if (preg_match('/<meta[^>]+property=["\']og:image(?::url)?["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $m)
                || preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]*property=["\']og:image(?::url)?["\']/i', $html, $m)) {
                $img = $this->resolveUrl(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), $url);
                if ($img !== null && $this->looksHttp($img)) {
                    return [$img, 'og'];
                }
            }

            // twitter:image
            if (preg_match('/<meta[^>]+name=["\']twitter:image(?::src)?["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $m)
                || preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]*name=["\']twitter:image(?::src)?["\']/i', $html, $m)) {
                $img = $this->resolveUrl(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), $url);
                if ($img !== null && $this->looksHttp($img)) {
                    return [$img, 'twitter'];
                }
            }

            // schema.org itemprop=image and older <link rel=image_src>.
            if (preg_match('/<meta[^>]+itemprop=["\']image["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $m)
                || preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]*itemprop=["\']image["\']/i', $html, $m)) {
                $img = $this->resolveUrl(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), $url);
                if ($img !== null && $this->looksHttp($img)) {
                    return [$img, 'schema'];
                }
            }

            if (preg_match('/<link[^>]+rel=["\'][^"\']*image_src[^"\']*["\'][^>]*href=["\']([^"\']+)["\']/i', $html, $m)
                || preg_match('/<link[^>]+href=["\']([^"\']+)["\'][^>]*rel=["\'][^"\']*image_src[^"\']*["\']/i', $html, $m)) {
                $img = $this->resolveUrl(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), $url);
                if ($img !== null && $this->looksHttp($img)) {
                    return [$img, 'image_src'];
                }
            }

            $jsonLdImage = $this->extractJsonLdImage($html);
            if ($jsonLdImage !== null) {
                $img = $this->resolveUrl($jsonLdImage, $url);
                if ($img !== null && $this->looksHttp($img)) {
                    return [$img, 'jsonld'];
                }
            }

            if (preg_match('/<img[^>]+srcset=["\']([^"\']+)["\']/i', $html, $m)) {
                $candidate = $this->bestSrcsetCandidate(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $img = $candidate !== null ? $this->resolveUrl($candidate, $url) : null;
                if ($img !== null && $this->looksHttp($img)) {
                    return [$img, 'srcset'];
                }
            }

            // First `<img>` with a real image extension — skip tracking
            // pixels and sprite sheets. Size hints help here too.
            if (preg_match('/<img[^>]+src=["\']([^"\']+\.(?:jpe?g|png|webp|avif)(?:\?[^"\']*)?)["\']/i', $html, $m)) {
                $img = $this->resolveUrl(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), $url);
                if ($img !== null && $this->looksHttp($img)) {
                    return [$img, 'img'];
                }
            }
        } catch (Throwable $e) {
            Log::debug('[GrimbaArticleImageScraper] fetch failed', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
        }

        return [null, null];
    }

    private function extractJsonLdImage(string $html): ?string
    {
        if (! preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
            return null;
        }

        foreach ($matches[1] as $rawJson) {
            $decoded = json_decode(html_entity_decode(trim($rawJson), ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
            $image = $this->imageFromJsonLd($decoded);
            if ($image !== null) {
                return $image;
            }
        }

        return null;
    }

    private function imageFromJsonLd(mixed $node): ?string
    {
        if (is_string($node)) {
            return $this->looksImageUrl($node) ? $node : null;
        }

        if (! is_array($node)) {
            return null;
        }

        if (isset($node['image'])) {
            $image = $this->imageFromJsonLd($node['image']);
            if ($image !== null) {
                return $image;
            }
        }

        if (isset($node['url']) && is_string($node['url']) && $this->looksImageUrl($node['url'])) {
            return $node['url'];
        }

        foreach (['@graph', 'thumbnail', 'thumbnailUrl', 'primaryImageOfPage'] as $key) {
            if (isset($node[$key])) {
                $image = $this->imageFromJsonLd($node[$key]);
                if ($image !== null) {
                    return $image;
                }
            }
        }

        if (array_is_list($node)) {
            foreach ($node as $item) {
                $image = $this->imageFromJsonLd($item);
                if ($image !== null) {
                    return $image;
                }
            }
        }

        return null;
    }

    private function bestSrcsetCandidate(string $srcset): ?string
    {
        $bestUrl = null;
        $bestWidth = -1;

        foreach (explode(',', $srcset) as $candidate) {
            $parts = preg_split('/\s+/', trim($candidate));
            $candidateUrl = $parts[0] ?? '';
            if (! $this->looksImageUrl($candidateUrl)) {
                continue;
            }

            $width = 0;
            foreach ($parts as $part) {
                if (preg_match('/^(\d+)w$/', $part, $m)) {
                    $width = (int) $m[1];
                    break;
                }
            }

            if ($bestUrl === null || $width > $bestWidth) {
                $bestUrl = $candidateUrl;
                $bestWidth = $width;
            }
        }

        return $bestUrl;
    }

    /**
     * Upgrade a relative / protocol-relative / origin-relative URL to
     * absolute using the article URL as the base.
     */
    private function resolveUrl(string $candidate, string $base): ?string
    {
        if ($candidate === '') {
            return null;
        }
        if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://')) {
            return $candidate;
        }
        $parts = parse_url($base);
        if (! $parts || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }
        $origin = $parts['scheme'] . '://' . $parts['host'];

        if (str_starts_with($candidate, '//')) {
            return $parts['scheme'] . ':' . $candidate;
        }
        if (str_starts_with($candidate, '/')) {
            return $origin . $candidate;
        }
        $dir = isset($parts['path']) ? rtrim(dirname($parts['path']), '/') : '';
        return $origin . $dir . '/' . $candidate;
    }

    private function looksHttp(string $url): bool
    {
        return (bool) preg_match('#^https?://[^\s<>"\']+$#i', $url);
    }

    private function looksImageUrl(string $url): bool
    {
        return (bool) preg_match('/\.(jpe?g|png|gif|webp|avif)(?:[?#].*)?$/i', $url);
    }
}
