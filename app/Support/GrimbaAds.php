<?php

namespace App\Support;

class GrimbaAds
{
    /**
     * @var array<string, array<string, string>>
     */
    private const SLOTS = [
        'grimba_home_top' => ['label' => 'Sponsor', 'format' => 'auto', 'placement' => 'home-top'],
        'grimba_home_mid' => ['label' => 'Sponsor', 'format' => 'auto', 'placement' => 'home-mid'],
        'grimba_home_native' => ['label' => 'Sponsor', 'format' => 'auto', 'placement' => 'home-native'],
        'grimba_chrome_top' => ['label' => 'Sponsor', 'format' => 'auto', 'placement' => 'page-top'],
        'grimba_chrome_bottom' => ['label' => 'Sponsor', 'format' => 'auto', 'placement' => 'page-bottom'],
        'grimba_sources_top' => ['label' => 'Sponsor', 'format' => 'auto', 'placement' => 'sources-top'],
        'grimba_sources_mid' => ['label' => 'Sponsor', 'format' => 'auto', 'placement' => 'sources-mid'],
        'grimba_article_top' => ['label' => 'Sponsor', 'format' => 'auto', 'placement' => 'article-top'],
        'grimba_article_mid' => ['label' => 'Sponsor', 'format' => 'auto', 'placement' => 'article-mid'],
        'grimba_story_after_hero' => ['label' => 'Sponsor', 'format' => 'auto', 'placement' => 'story-after-hero'],
        'grimba_story_mid' => ['label' => 'Sponsor', 'format' => 'auto', 'placement' => 'story-mid'],
        'grimba_story_sidebar' => ['label' => 'Sponsor', 'format' => 'auto', 'placement' => 'story-sidebar'],
    ];

    /**
     * @return array<string, mixed>
     */
    public static function resolve(string $location, string $configuredHtml, ?string $label = null): array
    {
        $location = trim($location);
        $configuredHtml = trim($configuredHtml);

        if (! self::enabled() || $location === '' || ! isset(self::SLOTS[$location])) {
            return ['mode' => 'hidden'];
        }

        if ($configuredHtml !== '') {
            return [
                'mode' => 'configured',
                'provider' => 'botble',
                'html' => $configuredHtml,
                'label' => $label ?: __(self::SLOTS[$location]['label']),
            ];
        }

        if (self::hasAutoAds()) {
            return ['mode' => 'hidden'];
        }

        $clientId = self::clientId();
        $slotId = self::slotId($location);
        if ($clientId && $slotId && self::networkAllowed()) {
            return [
                'mode' => 'network',
                'provider' => 'adsense',
                'clientId' => $clientId,
                'slotId' => $slotId,
                'format' => self::SLOTS[$location]['format'],
                'label' => $label ?: __(self::SLOTS[$location]['label']),
            ];
        }

        if (! (bool) config('grimba_ads.direct_fallback_enabled', true)) {
            return ['mode' => 'hidden'];
        }

        return [
            'mode' => 'direct',
            'provider' => app()->environment('production') ? 'direct' : 'dev-preview',
            'label' => $label ?: __(self::SLOTS[$location]['label']),
            'placement' => self::SLOTS[$location]['placement'],
            'directUrl' => self::directUrl($location),
        ];
    }

    public static function shouldLoadAdSenseScript(): bool
    {
        if (! self::enabled() || self::hasAutoAds() || ! self::networkAllowed()) {
            return false;
        }

        if (self::settingValue('ads_google_adsense_unit_client_id') !== '') {
            return false;
        }

        if (self::clientId() === '') {
            return false;
        }

        foreach (array_keys(self::SLOTS) as $location) {
            if (self::slotId($location) !== '') {
                return true;
            }
        }

        return false;
    }

    public static function adsenseClientId(): string
    {
        return self::clientId();
    }

    public static function adsTxt(): string
    {
        return trim((string) config('grimba_ads.ads_txt', ''));
    }

    private static function enabled(): bool
    {
        return (bool) config('grimba_ads.enabled', true);
    }

    private static function hasAutoAds(): bool
    {
        return self::settingValue('ads_google_adsense_auto_ads') !== '';
    }

    private static function networkAllowed(): bool
    {
        return app()->environment('production')
            || (bool) config('grimba_ads.load_network_in_non_production', false);
    }

    private static function clientId(): string
    {
        $clientId = self::settingValue('ads_google_adsense_unit_client_id')
            ?: (string) config('grimba_ads.adsense_client_id', '');

        return preg_match('/^ca-pub-\d{16}$/', $clientId) ? $clientId : '';
    }

    private static function slotId(string $location): string
    {
        // S-ADS-08 (Vader 2026-05-18): settings-first lookup so the
        // admin form at /admin/grimba/ads-config can override per-
        // slot IDs without an .env edit + deploy. Config falls back
        // for env-driven production deploys that pre-date the form.
        $slotId = trim((string) self::settingValue('grimba_ads_slot_' . $location))
            ?: trim((string) config("grimba_ads.slots.$location", ''));

        return preg_match('/^\d{4,}$/', $slotId) ? $slotId : '';
    }

    private static function directUrl(string $location): string
    {
        $placement = self::SLOTS[$location]['placement'] ?? $location;
        // S-ADS-08 — same settings-first pattern for the direct URL.
        $configured = trim((string) self::settingValue('grimba_ads_direct_url'))
            ?: trim((string) config('grimba_ads.direct_url', ''));
        if ($configured !== '') {
            return str_replace('{placement}', rawurlencode($placement), $configured);
        }

        return url('/advertise?slot=' . rawurlencode($placement));
    }

    private static function settingValue(string $key): string
    {
        if (! function_exists('setting')) {
            return '';
        }

        return trim((string) setting($key, ''));
    }
}
