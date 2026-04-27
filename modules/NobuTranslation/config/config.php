<?php

return [
    'default' => env('NOBU_TRANSLATION_DRIVER', 'nobuai'),
    'fallback' => env('NOBU_TRANSLATION_FALLBACK', 'libretranslate'),
    'cache_ttl_minutes' => (int) env('NOBU_TRANSLATION_CACHE_TTL', 60 * 24 * 30),
    'cache_prefix' => env('NOBU_TRANSLATION_CACHE_PREFIX', 'nobu-translation'),

    'providers' => [
        'nobuai' => [
            'driver' => 'nobuai',
        ],

        'libretranslate' => [
            'driver' => 'libretranslate',
            'url' => env('NOBU_TRANSLATION_LIBRETRANSLATE_URL', env('LIBRETRANSLATE_URL', 'https://libretranslate.com')),
            'api_key' => env('NOBU_TRANSLATION_LIBRETRANSLATE_KEY'),
            'timeout' => (int) env('NOBU_TRANSLATION_LIBRETRANSLATE_TIMEOUT', 20),
            'verify_tls' => filter_var(env('NOBU_TRANSLATION_LIBRETRANSLATE_VERIFY_TLS', true), FILTER_VALIDATE_BOOL),
        ],

        'null' => [
            'driver' => 'null',
        ],
    ],
];
