<?php

namespace Modules\NobuTranslation\Providers;

use App\Services\GrimbaNobuAi;
use Modules\NobuTranslation\Contracts\TranslationProvider;
use RuntimeException;

class NobuAiTranslationProvider implements TranslationProvider
{
    public function __construct(protected GrimbaNobuAi $nobuAi) {}

    public function translate(string $source, string $targetLocale, ?string $sourceLocale = null): string
    {
        $source = trim($source);
        if ($source === '') {
            return '';
        }

        $result = $this->nobuAi->complete($source, $this->systemPrompt($targetLocale, $sourceLocale));

        if (! $result || trim($result['text']) === '') {
            throw new RuntimeException('NobuAI translation returned no text.');
        }

        return trim($result['text']);
    }

    public function translateMany(array $sources, string $targetLocale, ?string $sourceLocale = null): array
    {
        $out = [];
        foreach ($sources as $key => $source) {
            $out[$key] = $this->translate($source, $targetLocale, $sourceLocale);
        }

        return $out;
    }

    public function supports(): bool
    {
        return $this->nobuAi->enabled();
    }

    public function name(): string
    {
        return 'NobuAI LLM translation';
    }

    protected function systemPrompt(string $targetLocale, ?string $sourceLocale): string
    {
        $from = $sourceLocale ? $this->localeName($sourceLocale) : 'the detected source language';
        $to = $this->localeName($targetLocale);

        return "You are NobuAI Translation. Translate from {$from} into {$to}. "
            . 'Preserve meaning, names, numbers, dates, links, and paragraph breaks. '
            . 'Do not add commentary, labels, quotation marks, markdown, or explanations. Output only the translation.';
    }

    protected function localeName(string $locale): string
    {
        return match (strtolower(substr($locale, 0, 2))) {
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
            default => $locale,
        };
    }
}
