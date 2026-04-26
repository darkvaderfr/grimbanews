<?php

namespace App\Console\Commands;

use App\Services\GrimbaTranslator;
use Botble\Blog\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GrimbaTranslatePending extends Command
{
    protected $signature = 'grimba:translate-pending
        {--limit=50 : Max posts to translate per run}
        {--to=fr : Target locale}
        {--force : Retranslate even if already has a translation in the target locale}
        {--dry-run : Report what would be translated without calling providers}';

    protected $description = 'Translate pending un-translated posts via the configured provider chain (OpenAI / OpenRouter / Anthropic / xAI / Perplexity / Mistral / DeepL / Gemini / Groq / LibreTranslate).';

    public function handle(GrimbaTranslator $translator): int
    {
        $to       = (string) $this->option('to') ?: 'fr';
        $limit    = (int) $this->option('limit');
        $force    = (bool) $this->option('force');
        $dry      = (bool) $this->option('dry-run');

        if (! $translator->enabled()) {
            $this->warn('No translation providers configured (set any of DEEPL_API_KEY, MISTRAL_API_KEY, OPENROUTER_API_KEY, OPENAI_API_KEY, ANTHROPIC_API_KEY, GOOGLE_API_KEY, GROQ_API_KEY, LIBRETRANSLATE_URL). Skipping.');
            return self::SUCCESS;
        }

        $this->info(sprintf('Providers available: %s', implode(', ', $translator->configuredDrivers())));

        $query = Post::query()
            ->whereIn('status', ['draft', 'published'])
            ->whereNotNull('original_language')
            ->where('original_language', '!=', $to)
            ->limit($limit)
            ->orderByDesc('id');

        if (! $force) {
            $query->where(function ($q) use ($to) {
                $q->whereNull('translated_to')
                  ->orWhere('translated_to', '!=', $to)
                  ->orWhereNull('translated_name');
            });
        }

        $posts = $query->get(['id', 'name', 'description', 'content', 'original_language', 'translated_to']);

        if ($posts->isEmpty()) {
            $this->info('Nothing to translate.');
            return self::SUCCESS;
        }

        $this->info(sprintf('%d post(s) queued for translation to %s.', $posts->count(), $to));

        $ok = 0; $fail = 0;
        foreach ($posts as $p) {
            $this->line(sprintf('  #%d [%s → %s] %s', $p->id, $p->original_language, $to, \Illuminate\Support\Str::limit($p->name, 60)));

            if ($dry) continue;

            $tName = $translator->translate((string) $p->name, (string) $p->original_language, $to);
            $tDesc = $p->description
                ? $translator->translate((string) $p->description, (string) $p->original_language, $to)
                : null;
            // Content can be much longer; skip translation when it's
            // effectively a duplicate of description (RSS poller case —
            // content is just a link + the same summary) to keep
            // provider tokens down.
            $contentPlain = trim(strip_tags((string) $p->content));
            $descPlain    = trim(strip_tags((string) ($p->description ?? '')));
            $tContent = null;
            if ($contentPlain !== '' && mb_strlen($contentPlain) > mb_strlen($descPlain) + 40) {
                $tContent = $translator->translate((string) $p->content, (string) $p->original_language, $to);
            }

            if ($tName === null) {
                $this->line('    (skipped — all providers failed)');
                $fail++;
                continue;
            }

            DB::table('posts')->where('id', $p->id)->update([
                'translated_name'        => $tName['text'],
                'translated_description' => $tDesc['text'] ?? null,
                'translated_content'     => $tContent['text'] ?? null,
                'translated_to'          => $to,
                'translated_at'          => now(),
                'translation_driver'     => $tName['driver'],
            ]);
            $this->line(sprintf('    → %s via %s', \Illuminate\Support\Str::limit($tName['text'], 60), $tName['driver']));
            $ok++;
        }

        $this->info(sprintf('Done. ok=%d fail=%d%s', $ok, $fail, $dry ? ' (dry-run)' : ''));
        return self::SUCCESS;
    }
}
