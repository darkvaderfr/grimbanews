<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/*
 * S167 — IP-based geolocation for the /local news page.
 *
 * Uses ip-api.com (free, no key, ~45 req/min/IP). Result is cached
 * for 24h per IP so we don't probe the upstream on every page load
 * and stay well under the rate limit.
 *
 * Falls back to ipapi.co (HTTPS, free 1k req/day) when ip-api.com
 * 5xx's or returns "fail". When both fail we return null — caller
 * falls back to "International / unknown" defaults.
 *
 * Privacy posture: only fires when the visitor has NO grimba_local_*
 * cookie set, OR when they explicitly hit "use my location". The
 * resolved city/country is then persisted as cookies and we never
 * geolocate again. The raw IP never goes to disk; only the truncated
 * sha1 (12 hex) is logged for cache-key debugging.
 */
class GrimbaGeoLocator
{
    private const CACHE_TTL = 86400; // 24h

    /**
     * @return array{city:string, country:string, country_code:string, region:?string, lat:?float, lon:?float}|null
     */
    public function locate(string $ip): ?array
    {
        $ip = trim($ip);
        if ($ip === '' || $ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.')) {
            return null; // local / private — geolocator returns garbage
        }

        $cacheKey = 'grimba_geo_' . sha1($ip);
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($ip) {
            $r = $this->viaIpApi($ip);
            if ($r) return $r;
            $r = $this->viaIpapiCo($ip);
            return $r;
        });
    }

    private function viaIpApi(string $ip): ?array
    {
        try {
            $res = Http::timeout(5)
                ->connectTimeout(3)
                ->get("http://ip-api.com/json/{$ip}", [
                    'fields' => 'status,country,countryCode,regionName,city,lat,lon,query',
                ]);
            if (! $res->successful()) return null;
            $body = $res->json();
            if (! is_array($body) || ($body['status'] ?? '') !== 'success') return null;

            return [
                'city'         => (string) ($body['city']       ?? ''),
                'country'      => (string) ($body['country']    ?? ''),
                'country_code' => (string) ($body['countryCode']?? ''),
                'region'       => $body['regionName'] ?? null,
                'lat'          => isset($body['lat']) ? (float) $body['lat'] : null,
                'lon'          => isset($body['lon']) ? (float) $body['lon'] : null,
            ];
        } catch (Throwable $e) {
            Log::debug('[GrimbaGeoLocator] ip-api failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function viaIpapiCo(string $ip): ?array
    {
        try {
            $res = Http::timeout(5)
                ->connectTimeout(3)
                ->get("https://ipapi.co/{$ip}/json/");
            if (! $res->successful()) return null;
            $body = $res->json();
            if (! is_array($body) || ! empty($body['error'])) return null;

            return [
                'city'         => (string) ($body['city']         ?? ''),
                'country'      => (string) ($body['country_name'] ?? ''),
                'country_code' => (string) ($body['country_code'] ?? ''),
                'region'       => $body['region'] ?? null,
                'lat'          => isset($body['latitude'])  ? (float) $body['latitude']  : null,
                'lon'          => isset($body['longitude']) ? (float) $body['longitude'] : null,
            ];
        } catch (Throwable $e) {
            Log::debug('[GrimbaGeoLocator] ipapi.co failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
