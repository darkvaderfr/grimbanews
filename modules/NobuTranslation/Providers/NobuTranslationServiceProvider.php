<?php

namespace Modules\NobuTranslation\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\NobuTranslation\Console\Commands\TestTranslationCommand;
use Modules\NobuTranslation\Contracts\TranslationProvider;
use Modules\NobuTranslation\Support\NobuTranslator;

class NobuTranslationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'modules.nobutranslation');

        $this->app->singleton(NobuTranslator::class, function ($app): NobuTranslator {
            $config = (array) $app['config']->get('modules.nobutranslation', []);

            $providers = [];
            foreach ((array) ($config['providers'] ?? []) as $key => $providerConfig) {
                $provider = $this->buildProvider($app, (string) $key, (array) $providerConfig);
                if ($provider !== null) {
                    $providers[(string) $key] = $provider;
                }
            }

            return new NobuTranslator(
                providers: $providers,
                defaultProviderKey: (string) ($config['default'] ?? 'nobuai'),
                fallbackProviderKey: ($config['fallback'] ?? null) ? (string) $config['fallback'] : null,
                cacheTtlMinutes: (int) ($config['cache_ttl_minutes'] ?? 60 * 24 * 30),
                cachePrefix: (string) ($config['cache_prefix'] ?? 'nobu-translation'),
            );
        });

        $this->app->bind(TranslationProvider::class, function ($app): TranslationProvider {
            $translator = $app->make(NobuTranslator::class);
            $health = $translator->health();
            $driver = (string) ($health['driver'] ?? 'null');
            $config = (array) $app['config']->get("modules.nobutranslation.providers.{$driver}", []);

            return $this->buildProvider($app, $driver, $config) ?? new NullTranslationProvider;
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([TestTranslationCommand::class]);
        }
    }

    protected function buildProvider($app, string $key, array $config): ?TranslationProvider
    {
        $driver = (string) ($config['driver'] ?? $key);

        return match ($driver) {
            'nobuai' => new NobuAiTranslationProvider($app->make(\App\Services\GrimbaNobuAi::class)),
            'libretranslate' => new LibreTranslateProvider(
                url: (string) ($config['url'] ?? 'https://libretranslate.com'),
                apiKey: $config['api_key'] ?? null,
                timeout: (int) ($config['timeout'] ?? 20),
                verifyTls: (bool) ($config['verify_tls'] ?? true),
            ),
            'null' => new NullTranslationProvider,
            default => null,
        };
    }
}
