<?php

namespace Modules\NobuTranslation\Providers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Modules\NobuTranslation\Contracts\TranslationProvider;
use RuntimeException;
use Throwable;

class LibreTranslateProvider implements TranslationProvider
{
    public function __construct(
        protected string $url,
        protected ?string $apiKey,
        protected int $timeout,
        protected bool $verifyTls,
    ) {}

    public function translate(string $source, string $targetLocale, ?string $sourceLocale = null): string
    {
        $response = $this->post($this->buildPayload($source, $targetLocale, $sourceLocale));
        $value = $response->json('translatedText');

        if (! is_string($value) || trim($value) === '') {
            throw new RuntimeException('LibreTranslate response missing translatedText.');
        }

        return $value;
    }

    public function translateMany(array $sources, string $targetLocale, ?string $sourceLocale = null): array
    {
        if ($sources === []) {
            return [];
        }

        $keys = array_keys($sources);
        $values = array_values($sources);
        $response = $this->post($this->buildPayload($values, $targetLocale, $sourceLocale));
        $translated = $response->json('translatedText');

        if (is_string($translated) && count($values) === 1) {
            $translated = [$translated];
        }

        if (! is_array($translated)) {
            throw new RuntimeException('LibreTranslate batch response missing translatedText array.');
        }

        $out = [];
        foreach ($keys as $index => $key) {
            $out[$key] = (string) ($translated[$index] ?? $values[$index]);
        }

        return $out;
    }

    public function supports(): bool
    {
        return trim($this->url) !== '';
    }

    public function name(): string
    {
        $host = parse_url($this->url, PHP_URL_HOST) ?: $this->url;

        return "LibreTranslate ({$host})";
    }

    protected function post(array $payload): Response
    {
        try {
            $response = $this->client()->post(rtrim($this->url, '/') . '/translate', $payload);
        } catch (Throwable $e) {
            throw new RuntimeException("LibreTranslate transport error: {$e->getMessage()}", 0, $e);
        }

        if (! $response->ok()) {
            throw new RuntimeException(sprintf('LibreTranslate HTTP %d: %s', $response->status(), mb_substr($response->body(), 0, 200)));
        }

        return $response;
    }

    protected function client(): PendingRequest
    {
        $client = Http::acceptJson()->asJson()->timeout($this->timeout);

        if (! $this->verifyTls) {
            $client = $client->withOptions(['verify' => false]);
        }

        return $client;
    }

    /**
     * @param array<int, string>|string $q
     * @return array<string, mixed>
     */
    protected function buildPayload(string|array $q, string $targetLocale, ?string $sourceLocale): array
    {
        $payload = [
            'q' => $q,
            'source' => $sourceLocale ?: 'auto',
            'target' => $targetLocale,
            'format' => 'text',
        ];

        if (filled($this->apiKey)) {
            $payload['api_key'] = $this->apiKey;
        }

        return $payload;
    }
}
