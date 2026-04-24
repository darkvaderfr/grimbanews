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
 *   4. <img src="…jpg|png|webp|avif">             — first sane hit
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
}
