<?php

namespace Modules\NobuTranslation\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\NobuTranslation\Contracts\TranslationProvider;
use RuntimeException;
use Throwable;

class NobuTranslator
{
    /**
     * @param array<string, TranslationProvider> $providers
     */
    public function __construct(
        protected array $providers,
        protected string $defaultProviderKey,
        protected ?string $fallbackProviderKey,
        protected int $cacheTtlMinutes,
        protected string $cachePrefix,
    ) {}

    public function translate(string $source, string $to, ?string $from = null, ?string $providerKey = null): string
    {
        $source = trim($source);
        if ($source === '') {
            return '';
        }

        if ($from !== null && strtolower(substr($from, 0, 2)) === strtolower(substr($to, 0, 2))) {
            return $source;
        }

        $cacheKey = $this->cacheKey($source, $to, $from);
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $value = $this->callWithFallback(
            fn (TranslationProvider $provider): string => $provider->translate($source, $to, $from),
            $providerKey
        );

        if ($value !== '') {
            Cache::put($cacheKey, $value, now()->addMinutes($this->cacheTtlMinutes));
        }

        return $value;
    }

    /**
     * @param array<int|string, string> $sources
     * @return array<int|string, string>
     */
    public function translateMany(array $sources, string $to, ?string $from = null, ?string $providerKey = null): array
    {
        if ($sources === []) {
            return [];
        }

        if ($from !== null && strtolower(substr($from, 0, 2)) === strtolower(substr($to, 0, 2))) {
            return $sources;
        }

        $cached = [];
        $misses = [];
        foreach ($sources as $key => $value) {
            $value = trim($value);
            if ($value === '') {
                $cached[$key] = '';
                continue;
            }

            $hit = Cache::get($this->cacheKey($value, $to, $from));
            if (is_string($hit) && $hit !== '') {
                $cached[$key] = $hit;
            } else {
                $misses[$key] = $value;
            }
        }

        if ($misses === []) {
            return $cached;
        }

        $translated = $this->callWithFallback(
            fn (TranslationProvider $provider): array => $provider->translateMany($misses, $to, $from),
            $providerKey
        );

        foreach ($translated as $key => $value) {
            if (is_string($value) && $value !== '' && isset($misses[$key])) {
                Cache::put($this->cacheKey((string) $misses[$key], $to, $from), $value, now()->addMinutes($this->cacheTtlMinutes));
            }
        }

        return $cached + $translated;
    }

    /**
     * @return array{driver: string, name: string, supports: bool, fallback: ?string}
     */
    public function health(): array
    {
        $primary = $this->resolveProvider($this->defaultProviderKey);

        return [
            'driver' => $this->defaultProviderKey,
            'name' => $primary?->name() ?? '(unresolved)',
            'supports' => $primary?->supports() ?? false,
            'fallback' => $this->fallbackProviderKey,
        ];
    }

    public function flushCache(): void
    {
        try {
            Cache::tags($this->cachePrefix)->flush();
        } catch (Throwable) {
            // File/array stores may not support tags.
        }
    }

    /**
     * @param callable(TranslationProvider): mixed $callback
     */
    protected function callWithFallback(callable $callback, ?string $providerKey): mixed
    {
        $primaryKey = $providerKey ?: $this->defaultProviderKey;
        $primary = $this->resolveProvider($primaryKey);

        if ($primary !== null && $primary->supports()) {
            try {
                return $callback($primary);
            } catch (RuntimeException $e) {
                Log::warning('NobuTranslator primary provider failed, attempting fallback', [
                    'primary' => $primaryKey,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($this->fallbackProviderKey === null || $this->fallbackProviderKey === $primaryKey) {
            throw new RuntimeException(sprintf('NobuTranslator: provider "%s" failed and no fallback configured.', $primaryKey));
        }

        $fallback = $this->resolveProvider($this->fallbackProviderKey);
        if ($fallback === null || ! $fallback->supports()) {
            throw new RuntimeException(sprintf(
                'NobuTranslator: provider "%s" failed and fallback "%s" unsupported.',
                $primaryKey,
                $this->fallbackProviderKey
            ));
        }

        return $callback($fallback);
    }

    protected function resolveProvider(string $key): ?TranslationProvider
    {
        return $this->providers[$key] ?? null;
    }

    protected function cacheKey(string $source, string $to, ?string $from): string
    {
        return sprintf(
            '%s:%s:%s:%s',
            $this->cachePrefix,
            $from ? strtolower(substr($from, 0, 8)) : 'auto',
            strtolower(substr($to, 0, 8)),
            sha1($source)
        );
    }
}
