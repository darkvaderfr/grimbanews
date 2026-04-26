<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/*
 * NobuAI wrapper — provider names stay behind this server-side layer.
 *
 * Reader-facing surfaces should only say "NobuAI". This service is the
 * operational wrapper that can route to whichever LLM provider key is
 * configured in env or Botble settings.
 */
class GrimbaNobuAi
{
    private const TIMEOUT = 12;

    private const CHAIN = ['mistral', 'openrouter', 'openai', 'anthropic', 'google', 'xai', 'perplexity', 'groq'];

    public function enabled(): bool
    {
        return $this->configuredDrivers() !== [];
    }

    /** @return array<int, string> */
    public function configuredDrivers(): array
    {
        $drivers = [];

        foreach (self::CHAIN as $driver) {
            if ($this->credentialFor($driver) !== null) {
                $drivers[] = $driver;
            }
        }

        return $drivers;
    }

    /**
     * @return array{text:string, driver:string}|null
     */
    public function complete(string $prompt, ?string $system = null): ?array
    {
        $prompt = trim($prompt);
        if ($prompt === '') {
            return null;
        }

        foreach ($this->failoverOrder() as $driver) {
            try {
                $text = $this->dispatch($driver, $prompt, $system ?: $this->defaultSystemPrompt());
                if ($text !== null && $text !== '') {
                    return ['text' => $text, 'driver' => $driver];
                }
            } catch (Throwable $e) {
                Log::warning('[GrimbaNobuAi] driver failed, trying next', [
                    'driver' => $driver,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function failoverOrder(): array
    {
        $pinned = is_callable('setting')
            ? (setting('grimba_nobuai_driver') ?: setting('grimba_translator_driver') ?: null)
            : null;
        if (! $pinned) {
            $pinned = env('GRIMBA_NOBUAI_DRIVER');
        }

        if (is_string($pinned) && $pinned !== '' && $pinned !== 'auto') {
            return array_values(array_unique(array_merge([$pinned], $this->configuredDrivers())));
        }

        return $this->configuredDrivers();
    }

    private function credentialFor(string $driver): ?string
    {
        $fromSetting = is_callable('setting')
            ? (setting('grimba_translator_' . $driver . '_key') ?: setting('grimba_nobuai_' . $driver . '_key') ?: null)
            : null;

        if ($fromSetting) {
            return $fromSetting;
        }

        return match ($driver) {
            'mistral' => env('MISTRAL_API_KEY') ?: null,
            'openrouter' => env('OPENROUTER_API_KEY') ?: null,
            'openai' => env('OPENAI_API_KEY') ?: (is_callable('setting') ? (setting('ai_writer_openai_key') ?: null) : null),
            'anthropic' => env('ANTHROPIC_API_KEY') ?: null,
            'google' => env('GOOGLE_API_KEY') ?: null,
            'xai' => env('XAI_API_KEY') ?: null,
            'perplexity' => env('PERPLEXITY_API_KEY') ?: null,
            'groq' => env('GROQ_API_KEY') ?: null,
            default => null,
        };
    }

    private function dispatch(string $driver, string $prompt, string $system): ?string
    {
        return match ($driver) {
            'mistral' => $this->viaOpenAiCompatible(
                'https://api.mistral.ai/v1/chat/completions',
                $this->credentialFor('mistral') ?? '',
                $this->modelFor('mistral', 'mistral-small-latest'),
                $prompt,
                $system,
            ),
            'openrouter' => $this->viaOpenAiCompatible(
                'https://openrouter.ai/api/v1/chat/completions',
                $this->credentialFor('openrouter') ?? '',
                $this->modelFor('openrouter', 'mistralai/mistral-small-3-24b-instruct'),
                $prompt,
                $system,
                [
                    'HTTP-Referer' => (string) (config('app.url') ?: 'https://grimbanews.com'),
                    'X-Title' => 'GrimbaNews',
                ],
            ),
            'openai' => $this->viaOpenAiCompatible(
                'https://api.openai.com/v1/chat/completions',
                $this->credentialFor('openai') ?? '',
                $this->modelFor('openai', 'gpt-4o-mini'),
                $prompt,
                $system,
            ),
            'anthropic' => $this->viaAnthropic($prompt, $system),
            'google' => $this->viaGoogleGemini($prompt, $system),
            'xai' => $this->viaOpenAiCompatible(
                'https://api.x.ai/v1/chat/completions',
                $this->credentialFor('xai') ?? '',
                $this->modelFor('xai', 'grok-4.20'),
                $prompt,
                $system,
            ),
            'perplexity' => $this->viaOpenAiCompatible(
                'https://api.perplexity.ai/chat/completions',
                $this->credentialFor('perplexity') ?? '',
                $this->modelFor('perplexity', 'sonar-pro'),
                $prompt,
                $system,
            ),
            'groq' => $this->viaOpenAiCompatible(
                'https://api.groq.com/openai/v1/chat/completions',
                $this->credentialFor('groq') ?? '',
                $this->modelFor('groq', 'llama-3.3-70b-versatile'),
                $prompt,
                $system,
            ),
            default => null,
        };
    }

    private function viaOpenAiCompatible(string $endpoint, string $key, string $model, string $prompt, string $system, array $extraHeaders = []): ?string
    {
        if (! $key) {
            return null;
        }

        $http = Http::withToken($key)->timeout(self::TIMEOUT)->acceptJson();
        if ($extraHeaders !== []) {
            $http = $http->withHeaders($extraHeaders);
        }

        $response = $http->post($endpoint, [
            'model' => $model,
            'temperature' => 0.2,
            'max_tokens' => 900,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if (! $response->successful()) {
            return null;
        }

        return trim((string) $response->json('choices.0.message.content', '')) ?: null;
    }

    private function viaAnthropic(string $prompt, string $system): ?string
    {
        $key = $this->credentialFor('anthropic');
        if (! $key) {
            return null;
        }

        $response = Http::timeout(self::TIMEOUT)
            ->withHeaders([
                'x-api-key' => $key,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->modelFor('anthropic', 'claude-3-5-haiku-latest'),
                'max_tokens' => 900,
                'system' => $system,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]);

        if (! $response->successful()) {
            return null;
        }

        $out = '';
        foreach ((array) $response->json('content', []) as $block) {
            if (($block['type'] ?? '') === 'text') {
                $out .= (string) ($block['text'] ?? '');
            }
        }

        return trim($out) ?: null;
    }

    private function viaGoogleGemini(string $prompt, string $system): ?string
    {
        $key = $this->credentialFor('google');
        if (! $key) {
            return null;
        }

        $model = $this->modelFor('google', 'gemini-2.0-flash');
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . urlencode($key);
        $response = Http::timeout(self::TIMEOUT)
            ->acceptJson()
            ->post($endpoint, [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [['text' => $prompt]],
                ]],
                'systemInstruction' => [
                    'parts' => [['text' => $system]],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 900,
                ],
            ]);

        if (! $response->successful()) {
            return null;
        }

        $out = '';
        foreach ((array) $response->json('candidates.0.content.parts', []) as $part) {
            $out .= (string) ($part['text'] ?? '');
        }

        return trim($out) ?: null;
    }

    private function defaultSystemPrompt(): string
    {
        return 'You are NobuAI for GrimbaNews. Produce concise, neutral newsroom assistance. '
            . 'Do not mention model providers. Do not invent facts. If evidence is insufficient, say so.';
    }

    private function modelFor(string $driver, string $default): string
    {
        $fromNobuSetting = is_callable('setting') ? trim((string) setting('grimba_nobuai_' . $driver . '_model', '')) : '';
        if ($fromNobuSetting !== '') {
            return $fromNobuSetting;
        }

        $fromTranslatorSetting = is_callable('setting') ? trim((string) setting('grimba_translator_' . $driver . '_model', '')) : '';
        if ($fromTranslatorSetting !== '') {
            return $fromTranslatorSetting;
        }

        return match ($driver) {
            'openrouter' => env('OPENROUTER_MODEL', $default),
            'openai' => env('OPENAI_MODEL', $default),
            'anthropic' => env('ANTHROPIC_MODEL', $default),
            'google' => env('GOOGLE_MODEL', $default),
            'mistral' => env('MISTRAL_MODEL', $default),
            'groq' => env('GROQ_MODEL', $default),
            'xai' => env('XAI_MODEL', $default),
            'perplexity' => env('PERPLEXITY_MODEL', $default),
            default => $default,
        };
    }
}
