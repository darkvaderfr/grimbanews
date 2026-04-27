<?php

namespace Modules\NobuTranslation\Contracts;

interface TranslationProvider
{
    public function translate(string $source, string $targetLocale, ?string $sourceLocale = null): string;

    /**
     * @param array<int|string, string> $sources
     * @return array<int|string, string>
     */
    public function translateMany(array $sources, string $targetLocale, ?string $sourceLocale = null): array;

    public function supports(): bool;

    public function name(): string;
}
