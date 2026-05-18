<?php

return [
    'enabled' => env('GRIMBA_ADS_ENABLED', true),

    /*
     * Production AdSense unit mode. Botble Ads settings still take
     * precedence when configured in admin; these env values keep revenue
     * slots live when DB ad rows are not ready yet.
     */
    'adsense_client_id' => env('GRIMBA_ADSENSE_CLIENT_ID'),
    'load_network_in_non_production' => env('GRIMBA_ADS_LOAD_NETWORK_IN_NON_PRODUCTION', false),

    'direct_fallback_enabled' => env('GRIMBA_ADS_DIRECT_FALLBACK_ENABLED', true),
    'direct_url' => env('GRIMBA_ADS_DIRECT_URL'),
    'sales_email' => env('GRIMBA_ADS_SALES_EMAIL', 'ads@grimbanews.com'),

    /*
     * Optional env-backed ads.txt content for day-one AdSense readiness.
     * If public/ads.txt exists, the web server/plugin file still wins.
     */
    'ads_txt' => env('GRIMBA_ADS_TXT'),

    'slots' => [
        'grimba_home_top' => env('GRIMBA_ADSENSE_SLOT_HOME_TOP'),
        'grimba_home_mid' => env('GRIMBA_ADSENSE_SLOT_HOME_MID'),
        'grimba_home_native' => env('GRIMBA_ADSENSE_SLOT_HOME_NATIVE'),
        'grimba_chrome_top' => env('GRIMBA_ADSENSE_SLOT_CHROME_TOP'),
        'grimba_chrome_bottom' => env('GRIMBA_ADSENSE_SLOT_CHROME_BOTTOM'),
        'grimba_sources_top' => env('GRIMBA_ADSENSE_SLOT_SOURCES_TOP'),
        'grimba_sources_mid' => env('GRIMBA_ADSENSE_SLOT_SOURCES_MID'),
        'grimba_article_top' => env('GRIMBA_ADSENSE_SLOT_ARTICLE_TOP'),
        'grimba_article_mid' => env('GRIMBA_ADSENSE_SLOT_ARTICLE_MID'),
        'grimba_story_after_hero' => env('GRIMBA_ADSENSE_SLOT_STORY_AFTER_HERO'),
        'grimba_story_mid' => env('GRIMBA_ADSENSE_SLOT_STORY_MID'),
        'grimba_story_sidebar' => env('GRIMBA_ADSENSE_SLOT_STORY_SIDEBAR'),
    ],
];
