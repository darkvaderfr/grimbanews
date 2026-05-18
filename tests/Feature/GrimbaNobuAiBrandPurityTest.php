<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Static brand-purity scanner. Vader's standing directive:
 * user-facing surfaces show ONLY "NobuAI" — never the external
 * provider names that the translator / summarizer chain runs on
 * under the hood (Anthropic, OpenAI, Claude, GPT, Mistral, Llama,
 * Cohere, Gemini, Groq, DeepL, LibreTranslate, OpenRouter).
 *
 * Companion to GrimbaLaunchReadinessTest which scans RENDERED
 * HTML for runtime leaks. This test scans the SOURCE blade files
 * for leaks at the source-of-truth layer, so a regression
 * lands as a test failure during pre-commit rather than as a
 * production visit.
 *
 * Admin-side blade files (anything under
 * `resources/views/grimba-admin/` or themes/echo/functions/
 * `grimba-admin-*.php`) are EXEMPT per the global rule —
 * operators legitimately need provider names when wiring API
 * keys.
 */
class GrimbaNobuAiBrandPurityTest extends TestCase
{
    /**
     * @return array<int, string>
     */
    private function bannedNames(): array
    {
        return [
            'Anthropic',
            'OpenAI',
            'ChatGPT',
            'GPT-4',
            'GPT-3',
            'Mistral',
            'Llama',
            'Cohere',
            'Gemini',
            'Groq',
            'DeepL',
            'LibreTranslate',
            'OpenRouter',
            'Perplexity',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function readerBladePaths(): array
    {
        $base = dirname(__DIR__, 2) . '/platform/themes/echo';
        $paths = [];
        foreach (['views', 'partials', 'layouts'] as $dir) {
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base . '/' . $dir));
            foreach ($it as $file) {
                if (! $file->isFile()) {
                    continue;
                }
                $name = $file->getFilename();
                if (! str_ends_with($name, '.blade.php')) {
                    continue;
                }
                $paths[] = $file->getPathname();
            }
        }
        return $paths;
    }

    public function test_no_reader_blade_mentions_an_external_provider_name(): void
    {
        $banned = $this->bannedNames();
        $blades = $this->readerBladePaths();
        $this->assertGreaterThan(20, count($blades), 'Sanity: should be at least 20 reader blade files.');

        $leaks = [];
        foreach ($blades as $path) {
            $content = file_get_contents($path);
            if ($content === false) continue;

            // Skip the legitimate provider-stripping regex literal
            // in post.blade.php — it lists every banned name inside
            // a /\b(?:Name|Name|…)\b/ pattern as the WHOLE POINT of
            // the scrubber. Drop everything between the regex
            // delimiters before scanning.
            $cleaned = preg_replace(
                "#/[\\\\][b].+?[\\\\][b]/iu#s",
                '/* PROVIDER STRIP-REGEX */',
                $content,
            );

            // Skip blade {{-- comments --}} so we can describe the
            // rule in dev docs without tripping the scanner.
            $cleaned = preg_replace(
                '/\{\{--.*?--\}\}/s',
                '/* BLADE-COMMENT */',
                (string) $cleaned,
            );

            // Skip /* PHP block comments */ for the same reason.
            $cleaned = preg_replace(
                '#/\*.*?\*/#s',
                '/* PHP-COMMENT */',
                (string) $cleaned,
            );

            foreach ($banned as $needle) {
                // Use word-boundary matching so "Gemini" doesn't
                // accidentally match an unrelated "Gemini" inside
                // another word.
                if (preg_match('/\b' . preg_quote($needle, '/') . '\b/u', (string) $cleaned)) {
                    $leaks[] = $path . ' contains "' . $needle . '"';
                }
            }
        }

        $this->assertSame([], $leaks, "Brand-purity leaks found:\n  " . implode("\n  ", $leaks));
    }

    public function test_translation_admin_blade_legitimately_lists_providers(): void
    {
        // Confirm the OPS-side translation admin file DOES list
        // provider names (because that's where operators paste
        // their API keys). If this asserts non-empty, the global
        // rule's "admin pages exempt" carve-out is doing its job.
        $adminPath = dirname(__DIR__, 2) . '/resources/views/grimba-admin/translation/index.blade.php';
        $this->assertFileExists($adminPath);
        $content = (string) file_get_contents($adminPath);
        $found = 0;
        foreach (['Anthropic', 'OpenAI', 'Mistral', 'DeepL'] as $needle) {
            if (str_contains($content, $needle)) {
                $found++;
            }
        }
        $this->assertGreaterThan(
            2,
            $found,
            'Admin translation page must surface provider names (operator-only context).',
        );
    }
}
