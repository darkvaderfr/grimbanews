<?php

namespace App\Services;

/*
 * S151b — single source of truth for canonical URL hashing.
 * Used by the RSS poller (ingest-time dedup) and the
 * grimba:dedupe-posts command (cleanup), so they always agree on
 * which two URLs are "the same article".
 *
 * Drops:
 *   - URL fragment (#0, #2, etc. — BBC RSS uses these to indicate
 *     re-broadcasts of the same article)
 *   - Tracking query params (utm_*, fbclid, gclid, mc_cid, etc.)
 *
 * Returns null when the URL is unparseable. Always lowercases host
 * so http://Example.com and http://example.com hash to the same.
 */
class GrimbaUrlCanonicalizer
{
    private const TRACKING_PARAMS = [
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
        'fbclid', 'gclid', 'mc_cid', 'mc_eid',
        'ref', 'referrer', 'source', 'srnd', 'taid',
    ];

    public function hash(string $url): ?string
    {
        $clean = $this->canonicalize($url);
        return $clean ? sha1($clean) : null;
    }

    public function canonicalize(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        $parts = parse_url($url);
        if (! $parts || empty($parts['host'])) {
            return null;
        }

        $clean = ($parts['scheme'] ?? 'https') . '://' . mb_strtolower($parts['host']);

        if (! empty($parts['path'])) {
            // Trim trailing slash on non-root paths so /article and
            // /article/ collapse together.
            $path = $parts['path'];
            if ($path !== '/' && str_ends_with($path, '/')) {
                $path = rtrim($path, '/');
            }
            $clean .= $path;
        }

        if (! empty($parts['query'])) {
            parse_str($parts['query'], $q);
            foreach (self::TRACKING_PARAMS as $k) {
                unset($q[$k]);
            }
            if (! empty($q)) {
                ksort($q); // sorted keys → stable hash regardless of param order
                $clean .= '?' . http_build_query($q);
            }
        }

        // Fragment intentionally dropped.

        return $clean;
    }
}
