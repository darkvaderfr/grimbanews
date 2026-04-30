<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

                $this->recordFailure($driver, 'Empty response or upstream HTTP error.');
            } catch (Throwable $e) {
                $this->recordFailure($driver, $e->getMessage());
                Log::warning('[GrimbaNobuAi] driver failed, trying next', [
                    'driver' => $driver,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * @return array<string, array{driver:string, message:string, at:string|null}>
     */
    public function failureDiagnostics(?array $drivers = null): array
    {
        if (! Schema::hasTable('settings')) {
            return [];
        }

        $drivers = $drivers ?: self::CHAIN;
        $keys = collect($drivers)
            ->mapWithKeys(fn (string $driver): array => ['grimba_nobuai_failure_' . $driver => $driver])
            ->all();

        if ($keys === []) {
            return [];
        }

        return DB::table('settings')
            ->whereIn('key', array_keys($keys))
            ->pluck('value', 'key')
            ->mapWithKeys(function (?string $value, string $key) use ($keys): array {
                $payload = json_decode((string) $value, true);
                if (! is_array($payload)) {
                    return [];
                }

                $driver = $keys[$key] ?? (string) ($payload['driver'] ?? '');
                if ($driver === '') {
                    return [];
                }

                return [$driver => [
                    'driver' => $driver,
                    'message' => (string) ($payload['message'] ?? 'Unknown failure.'),
                    'at' => $payload['at'] ?? null,
                ]];
            })
            ->all();
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

    private function recordFailure(string $driver, string $message): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $payload = json_encode([
            'driver' => $driver,
            'message' => $this->sanitizeFailureMessage($message),
            'at' => now()->toDateTimeString(),
        ]);

        DB::table('settings')->updateOrInsert(
            ['key' => 'grimba_nobuai_failure_' . $driver],
            ['value' => $payload, 'created_at' => now(), 'updated_at' => now()]
        );
    }

    private function sanitizeFailureMessage(string $message): string
    {
        $message = preg_replace('/sk-[A-Za-z0-9_\-]{8,}/', 'sk-...[redacted]', $message) ?? $message;
        $message = preg_replace('/Bearer\s+[A-Za-z0-9_\-\.]{8,}/i', 'Bearer ...[redacted]', $message) ?? $message;
        $message = preg_replace('/[A-Za-z0-9_\-]{24,}\.[A-Za-z0-9_\-]{8,}\.[A-Za-z0-9_\-]{8,}/', '[token redacted]', $message) ?? $message;
        $message = trim(strip_tags($message));

        return mb_substr($message !== '' ? $message : 'Unknown upstream failure.', 0, 220);
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
        return $this->editorialSystemPrompt('Produce concise, evidence-bound newsroom assistance.');
    }

    public function editorialSystemPrompt(string $task = ''): string
    {
        $profile = $this->editorialProfile();
        $parts = [
            'You are NobuAI for GrimbaNews.',
            'Mission: ' . $profile['mission'],
            'Editorial soul: ' . $profile['soul'],
            'Capabilities: ' . $profile['capabilities'],
            'Perspective anchors: ' . $profile['anchors'],
            'Guardrails: ' . $profile['guardrails'],
            'Do not mention model providers. Do not invent facts. If evidence is insufficient, say so.',
        ];

        $task = trim($task);
        if ($task !== '') {
            $parts[] = 'Task: ' . $task;
        }

        return implode(' ', $parts);
    }

    /**
     * @return array{mission:string,soul:string,capabilities:string,anchors:string,guardrails:string}
     */
    public function editorialProfile(): array
    {
        $defaults = [
            'mission' => "Servir les publics africains et les intellectuels du continent et de la diaspora avec une lecture rigoureuse des rapports de pouvoir, de souveraineté et d'intérêt public.",
            'soul' => "NobuAI est l'éditeur en chef analytique de GrimbaNews: panafricain, anti-colonial dans sa grille de lecture, pluraliste dans les sources, sobre dans le ton, et strictement lié aux faits disponibles.",
            'capabilities' => "Identifier les conséquences pour l'Afrique, les angles morts des médias dominants, les intérêts institutionnels ou économiques, les continuités historiques, les effets sur les diasporas et les questions de souveraineté.",
            'anchors' => "Traditions panafricaines associées à Kwame Nkrumah, Patrice Lumumba, Nelson Mandela, Nathalie Yamb et d'autres penseurs ou acteurs de souveraineté africaine, sans imiter leur voix ni leur attribuer des propos.",
            'guardrails' => "Ne pas inventer de faits, ne pas appeler à soutenir un parti ou un candidat, distinguer clairement fait/analyse/incertitude, citer les limites des sources, et refuser les conclusions non étayées.",
        ];

        foreach ($defaults as $key => $default) {
            $value = is_callable('setting') ? trim((string) setting('grimba_nobuai_editorial_' . $key, $default)) : '';
            $defaults[$key] = $value !== '' ? $value : $default;
        }

        return $defaults;
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
