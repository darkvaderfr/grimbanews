<?php

namespace Modules\NobuTranslation\Providers;

use Modules\NobuTranslation\Contracts\TranslationProvider;

class NullTranslationProvider implements TranslationProvider
{
    public function translate(string $source, string $targetLocale, ?string $sourceLocale = null): string
    {
        return $source;
    }

    public function translateMany(array $sources, string $targetLocale, ?string $sourceLocale = null): array
    {
        return $sources;
    }

    public function supports(): bool
    {
        return true;
    }

    public function name(): string
    {
        return 'Null translation provider';
    }
}
