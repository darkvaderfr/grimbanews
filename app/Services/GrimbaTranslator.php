<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\NobuTranslation\Support\NobuTranslator;
use Throwable;

/*
 * GrimbaTranslator — multi-provider text translation with failover.
 *
 * Providers (first configured wins unless GRIMBA_TRANSLATOR_DRIVER pins
 * a specific one; the chain below is the auto-select order):
 *
 *   1. deepl       — DEEPL_API_KEY         — best-in-class FR/EN,
 *                                            pronoun + idiom accuracy.
 *   2. mistral     — MISTRAL_API_KEY       — FR-native, strong on
 *                                            European news vocabulary.
 *   3. openrouter  — OPENROUTER_API_KEY    — unified gateway to 100+
 *                                            models; one key, routes
 *                                            to the cheapest/fastest
 *                                            model you pick via
 *                                            OPENROUTER_MODEL env
 *                                            (default:
 *                                            mistralai/mistral-small-3-
 *                                            24b-instruct).
 *   4. openai      — OPENAI_API_KEY (or
 *                    Botble setting
 *                    ai_writer_openai_key) — gpt-4o-mini, cheap baseline.
 *   5. anthropic   — ANTHROPIC_API_KEY     — claude-3-5-haiku, good
 *                                            prose, slightly pricier.
 *   6. google      — GOOGLE_API_KEY        — Gemini 2.0 Flash via the
 *                                            generativelanguage API.
 *   7. xai         — XAI_API_KEY           — Grok via OpenAI-compatible
 *                                            chat completions.
 *   8. perplexity  — PERPLEXITY_API_KEY    — Sonar via OpenAI-compatible
 *                                            chat completions.
 *   9. groq        — GROQ_API_KEY          — Llama 3.3 70B, fast and
 *                                            free-tier generous.
 *  10. libre       — LIBRETRANSLATE_URL    — self-hosted fallback,
 *                                            no auth needed.
 *
 * Failover: if the primary driver 5xx's or times out, the next configured
 * provider in the chain is tried. Successful translation returns
 * ['text' => string, 'driver' => provider-name]. Total failure returns
 * null and the batch command logs the post id for retry next tick.
 *
 * Per-call timeout aggressive (10s) so a slow upstream never chokes
 * `grimba:translate-pending` which iterates dozens of posts per run.
 */
class GrimbaTranslator
{
    private const TIMEOUT = 10;

    /** @var array<int, array{driver: string, message: string}> */
    private array $lastFailures = [];

    /** @var array<int, string> Provider preference order when driver=auto.
     *  `googletx` (S158) is the always-on fallback — Google Translate's
     *  unofficial gtx endpoint, no API key required. Quality is "good
     *  enough for a glance"; rate-limited per IP, so it's last in the
     *  chain — paid drivers run first when configured. */
    private const CHAIN = ['deepl', 'mistral', 'openrouter', 'openai', 'anthropic', 'google', 'xai', 'perplexity', 'groq', 'libre', 'googletx'];

    public function enabled(): bool
    {
        return $this->configuredDrivers() !== [];
    }

    /** @return array<int, string> drivers that have a credential configured */
    public function configuredDrivers(): array
    {
        $out = [];
        if (class_exists(NobuTranslator::class)) {
            $out[] = 'nobutranslation';
        }
        foreach (self::CHAIN as $name) {
            if ($this->credentialFor($name) !== null) $out[] = $name;
        }
        return array_values(array_unique($out));
    }

    /**
     * Translate a text chunk. Tries the pinned driver first (if any),
     * then falls through the auto chain on failure. Returns null when
     * nothing succeeded or source == target.
     *
     * @return array{text:string, driver:string}|null
     */
    public function translate(string $text, string $from, string $to = 'fr'): ?array
    {
        $this->lastFailures = [];
        $text = trim($text);
        if ($text === '' || strtolower(substr($from, 0, 2)) === strtolower(substr($to, 0, 2))) {
            return null;
        }

        if (class_exists(NobuTranslator::class) && app()->bound(NobuTranslator::class)) {
            try {
                /** @var NobuTranslator $nobuTranslator */
                $nobuTranslator = app(NobuTranslator::class);
                $translated = $nobuTranslator->translate($text, $to, $from);
                if (trim($translated) !== '' && trim($translated) !== $text) {
                    $health = $nobuTranslator->health();

                    return [
                        'text' => $translated,
                        'driver' => 'nobutranslation:' . (string) ($health['driver'] ?? 'unknown'),
                    ];
                }
            } catch (Throwable $e) {
                $this->lastFailures[] = [
                    'driver' => 'nobutranslation',
                    'message' => $e->getMessage(),
                ];
                Log::warning('[GrimbaTranslator] NobuTranslation module failed, trying legacy chain', [
                    'from' => $from,
                    'to' => $to,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $order = $this->failoverOrder();

        foreach ($order as $driver) {
            try {
                $out = $this->dispatch($driver, $text, $from, $to);
                if ($out !== null && $out !== '') {
                    return ['text' => $out, 'driver' => $driver];
                }
                $this->lastFailures[] = [
                    'driver' => $driver,
                    'message' => 'Empty response or unsupported provider response.',
                ];
            } catch (Throwable $e) {
                $this->lastFailures[] = [
                    'driver' => $driver,
                    'message' => $e->getMessage(),
                ];
                Log::warning('[GrimbaTranslator] driver failed, trying next', [
                    'driver' => $driver, 'from' => $from, 'to' => $to, 'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * @return array<int, array{driver: string, message: string}>
     */
    public function failureDiagnostics(): array
    {
        return $this->lastFailures;
    }

    /** @return array<int, string> */
    private function failoverOrder(): array
    {
        // Setting wins over env so admin UI changes are immediate.
        $pinned = is_callable('setting')
            ? (setting('grimba_translator_driver') ?: null)
            : null;
        if (! $pinned) {
            $pinned = env('GRIMBA_TRANSLATOR_DRIVER');
        }
        if (is_string($pinned) && $pinned !== '' && $pinned !== 'auto') {
            return array_values(array_unique(array_merge([$pinned], $this->configuredDrivers())));
        }
        return $this->configuredDrivers();
    }

    /**
     * Credential resolution order per driver:
     *   1. Botble setting `grimba_translator_<driver>_key` — writable
     *      from /admin/grimba/translation so Vader can rotate without
     *      SSH-editing .env.
     *   2. Driver-specific env var (DEEPL_API_KEY, OPENROUTER_API_KEY,
     *      etc). Keeps 12-factor workflows working.
     *   3. (openai only) Botble's existing ai_writer_openai_key setting
     *      so the AI Writer plugin and our translator share one key.
     */
    private function credentialFor(string $driver): ?string
    {
        $fromSetting = null;
        if (is_callable('setting')) {
            $fromSetting = setting('grimba_translator_' . $driver . '_key') ?: null;
        }
        if ($fromSetting) return $fromSetting;

        return match ($driver) {
            'deepl'      => env('DEEPL_API_KEY') ?: null,
            'mistral'    => env('MISTRAL_API_KEY') ?: null,
            'openrouter' => env('OPENROUTER_API_KEY') ?: null,
            'openai'     => env('OPENAI_API_KEY')
                            ?: (is_callable('setting') ? (setting('ai_writer_openai_key') ?: null) : null),
            'anthropic'  => env('ANTHROPIC_API_KEY') ?: null,
            'google'     => env('GOOGLE_API_KEY') ?: null,
            'xai'        => env('XAI_API_KEY') ?: null,
            'perplexity' => env('PERPLEXITY_API_KEY') ?: null,
            'groq'       => env('GROQ_API_KEY') ?: null,
            'libre'      => env('LIBRETRANSLATE_URL') ?: null,
            'googletx'   => 'free', // sentinel — always available
            default      => null,
        };
    }

    private function dispatch(string $driver, string $text, string $from, string $to): ?string
    {
        return match ($driver) {
            'deepl'      => $this->viaDeepL($text, $from, $to),
            'mistral'    => $this->viaOpenAiCompatible(
                $text, $from, $to,
                'https://api.mistral.ai/v1/chat/completions',
                $this->credentialFor('mistral') ?? '',
                $this->modelFor('mistral', 'mistral-small-latest'),
            ),
            'openrouter' => $this->viaOpenAiCompatible(
                $text, $from, $to,
                'https://openrouter.ai/api/v1/chat/completions',
                $this->credentialFor('openrouter') ?? '',
                $this->modelFor('openrouter', 'mistralai/mistral-small-3-24b-instruct'),
                [
                    // OpenRouter's attribution + analytics headers —
                    // recommended per https://openrouter.ai/docs/api-reference/overview
                    'HTTP-Referer' => (string) (config('app.url') ?: 'https://grimbanews.com'),
                    'X-Title'      => 'GrimbaNews',
                ],
            ),
            'openai'     => $this->viaOpenAiCompatible(
                $text, $from, $to,
                'https://api.openai.com/v1/chat/completions',
                $this->credentialFor('openai') ?? '',
                $this->modelFor('openai', 'gpt-4o-mini'),
            ),
            'anthropic'  => $this->viaAnthropic($text, $from, $to),
            'google'     => $this->viaGoogleGemini($text, $from, $to),
            'xai'        => $this->viaOpenAiCompatible(
                $text, $from, $to,
                'https://api.x.ai/v1/chat/completions',
                $this->credentialFor('xai') ?? '',
                $this->modelFor('xai', 'grok-4.20'),
            ),
            'perplexity' => $this->viaOpenAiCompatible(
                $text, $from, $to,
                'https://api.perplexity.ai/chat/completions',
                $this->credentialFor('perplexity') ?? '',
                $this->modelFor('perplexity', 'sonar-pro'),
            ),
            'groq'       => $this->viaOpenAiCompatible(
                $text, $from, $to,
                'https://api.groq.com/openai/v1/chat/completions',
                $this->credentialFor('groq') ?? '',
                $this->modelFor('groq', 'llama-3.3-70b-versatile'),
            ),
            'libre'      => $this->viaLibreTranslate($text, $from, $to),
            'googletx'   => $this->viaGoogleUnofficial($text, $from, $to),
            default      => null,
        };
    }

    // ---- Drivers ----

    private function viaDeepL(string $text, string $from, string $to): ?string
    {
        $key = $this->credentialFor('deepl');
        if (! $key) return null;

        // DeepL Free endpoints use -free.com; paid use api.deepl.com.
        // Both accept the same request; detect from key suffix (free keys end in ':fx').
        $endpoint = str_ends_with((string) $key, ':fx')
            ? 'https://api-free.deepl.com/v2/translate'
            : 'https://api.deepl.com/v2/translate';

        $response = Http::timeout(self::TIMEOUT)
            ->withHeaders(['Authorization' => 'DeepL-Auth-Key ' . $key])
            ->asForm()
            ->post($endpoint, [
                'text'         => $text,
                'source_lang'  => strtoupper(substr($from, 0, 2)),
                'target_lang'  => strtoupper(substr($to, 0, 2)),
                'preserve_formatting' => '1',
            ]);

        if (! $response->successful()) return null;
        return (string) ($response->json('translations.0.text') ?? '') ?: null;
    }

    private function viaOpenAiCompatible(string $text, string $from, string $to, string $endpoint, string $key, string $model, array $extraHeaders = []): ?string
    {
        if (! $key) return null;

        $http = Http::withToken($key)
            ->timeout(self::TIMEOUT)
            ->acceptJson();

        if (! empty($extraHeaders)) {
            $http = $http->withHeaders($extraHeaders);
        }

        $response = $http->post($endpoint, [
            'model'       => $model,
            'temperature' => 0.2,
            'max_tokens'  => 1200,
            'messages'    => [
                ['role' => 'system', 'content' => $this->translatorSystemPrompt($from, $to)],
                ['role' => 'user',   'content' => $text],
            ],
        ]);

        if (! $response->successful()) return null;
        return trim((string) $response->json('choices.0.message.content', '')) ?: null;
    }

    private function viaAnthropic(string $text, string $from, string $to): ?string
    {
        $key = $this->credentialFor('anthropic');
        if (! $key) return null;

        $response = Http::timeout(self::TIMEOUT)
            ->withHeaders([
                'x-api-key' => $key,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model'      => $this->modelFor('anthropic', 'claude-3-5-haiku-latest'),
                'max_tokens' => 1200,
                'system'     => $this->translatorSystemPrompt($from, $to),
                'messages'   => [['role' => 'user', 'content' => $text]],
            ]);

        if (! $response->successful()) return null;
        // Anthropic returns content as array of blocks
        $blocks = $response->json('content', []);
        $out = '';
        foreach ((array) $blocks as $b) {
            if (($b['type'] ?? '') === 'text') $out .= (string) ($b['text'] ?? '');
        }
        return trim($out) ?: null;
    }

    private function viaGoogleGemini(string $text, string $from, string $to): ?string
    {
        $key = $this->credentialFor('google');
        if (! $key) return null;

        $model = $this->modelFor('google', 'gemini-2.0-flash');
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . urlencode($key);

        $response = Http::timeout(self::TIMEOUT)
            ->acceptJson()
            ->post($endpoint, [
                'contents' => [[
                    'role'  => 'user',
                    'parts' => [['text' => $text]],
                ]],
                'systemInstruction' => [
                    'parts' => [['text' => $this->translatorSystemPrompt($from, $to)]],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 1200,
                ],
            ]);

        if (! $response->successful()) return null;
        $parts = (array) $response->json('candidates.0.content.parts', []);
        $out = '';
        foreach ($parts as $p) { $out .= (string) ($p['text'] ?? ''); }
        return trim($out) ?: null;
    }

    /**
     * Google Translate unofficial "gtx" endpoint — no API key, no
     * billing, rate-limited per IP. Quality is good for headlines +
     * short prose; bumps to slower drivers when blocked.
     *
     * Returns JSON shaped like:
     *   [ [ ["translated", "original", null, null, 1], ... ], null, "en", ... ]
     * Concatenate the first column of each pair to get the full
     * translation back.
     */
    private function viaGoogleUnofficial(string $text, string $from, string $to): ?string
    {
        $sl = mb_substr($from, 0, 2) ?: 'auto';
        $tl = mb_substr($to,   0, 2) ?: 'fr';

        // gtx endpoint caps at ~5000 chars. We chunk longer payloads.
        $chunks = $this->chunkForGoogleTx($text, 4500);
        $out = '';

        foreach ($chunks as $chunk) {
            $res = \Illuminate\Support\Facades\Http::withUserAgent('Mozilla/5.0 (compatible; GrimbaNewsBot/1.0)')
                ->timeout(15)
                ->connectTimeout(8)
                ->get('https://translate.googleapis.com/translate_a/single', [
                    'client' => 'gtx',
                    'sl'     => $sl,
                    'tl'     => $tl,
                    'dt'     => 't',
                    'q'      => $chunk,
                ]);

            if (! $res->successful()) {
                throw new \RuntimeException('googletx HTTP ' . $res->status());
            }

            $body = $res->json();
            if (! is_array($body) || empty($body[0])) {
                throw new \RuntimeException('googletx unexpected payload');
            }

            foreach ($body[0] as $segment) {
                if (is_array($segment) && isset($segment[0]) && is_string($segment[0])) {
                    $out .= $segment[0];
                }
            }
        }

        return trim($out) ?: null;
    }

    /**
     * Split long text on sentence boundaries so each chunk is ≤ $max.
     * @return array<int, string>
     */
    private function chunkForGoogleTx(string $text, int $max): array
    {
        if (mb_strlen($text) <= $max) return [$text];

        $sentences = preg_split('/([.!?…]\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$text];
        $chunks = [];
        $buf = '';
        foreach ($sentences as $piece) {
            if (mb_strlen($buf . $piece) > $max && $buf !== '') {
                $chunks[] = $buf;
                $buf = '';
            }
            $buf .= $piece;
        }
        if ($buf !== '') $chunks[] = $buf;
        return $chunks;
    }

    private function viaLibreTranslate(string $text, string $from, string $to): ?string
    {
        $base = $this->credentialFor('libre');
        if (! $base) return null;

        $response = Http::timeout(self::TIMEOUT)
            ->acceptJson()
            ->post(rtrim($base, '/') . '/translate', [
                'q'      => $text,
                'source' => strtolower(substr($from, 0, 2)),
                'target' => strtolower(substr($to, 0, 2)),
                'format' => 'text',
            ]);

        if (! $response->successful()) return null;
        return trim((string) $response->json('translatedText', '')) ?: null;
    }

    private function translatorSystemPrompt(string $from, string $to): string
    {
        $fromLabel = $this->localeLabel($from);
        $toLabel   = $this->localeLabel($to);
        return "You are a translation engine. Translate the user's text from {$fromLabel} into {$toLabel}. "
             . 'Preserve tone, proper nouns, and any dates or numbers. '
             . 'Do not add commentary, do not quote, do not explain. Output only the translated text.';
    }

    private function modelFor(string $driver, string $default): string
    {
        $settingKey = 'grimba_translator_' . $driver . '_model';
        $fromSetting = is_callable('setting') ? trim((string) setting($settingKey, '')) : '';
        if ($fromSetting !== '') {
            return $fromSetting;
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

    private function localeLabel(string $code): string
    {
        return match (strtolower(substr($code, 0, 2))) {
            'fr' => 'French',
            'en' => 'English',
            'es' => 'Spanish',
            'pt' => 'Portuguese',
            'de' => 'German',
            'it' => 'Italian',
            'ar' => 'Arabic',
            'zh' => 'Chinese',
            'ja' => 'Japanese',
            'ru' => 'Russian',
            default => $code,
        };
    }
}
