<?php

namespace App\Console\Commands;

use App\Services\GrimbaTranslator;
use App\Support\GrimbaArticleText;
use Botble\Blog\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GrimbaTranslatePending extends Command
{
    protected $signature = 'grimba:translate-pending
        {--limit=50 : Max posts to translate per run}
        {--to=fr : Target locale}
        {--force : Retranslate even if already has a translation in the target locale}
        {--failed-only : Retry only posts currently recorded in the translation failure queue}
        {--dry-run : Report what would be translated without calling providers}';

    protected $description = 'Translate pending un-translated posts via NobuAI (or fallback providers when NobuAI is offline).';

    public function handle(GrimbaTranslator $translator): int
    {
        $to       = (string) $this->option('to') ?: 'fr';
        $limit    = (int) $this->option('limit');
        $force    = (bool) $this->option('force');
        $failedOnly = (bool) $this->option('failed-only');
        $dry      = (bool) $this->option('dry-run');
        $hasFullContent = Schema::hasColumn('posts', 'full_content');

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

        if ($failedOnly) {
            if (! Schema::hasTable('grimba_translation_failures')) {
                $this->warn('Translation failure queue table is not installed yet.');
                return self::SUCCESS;
            }

            $query->whereExists(function ($sub) use ($to) {
                $sub->selectRaw('1')
                    ->from('grimba_translation_failures')
                    ->whereColumn('grimba_translation_failures.post_id', 'posts.id')
                    ->where('grimba_translation_failures.locale', $to);
            });
        }

        if (! $force) {
            $sourceBodySql = $hasFullContent
                ? "length(trim(coalesce(nullif(posts.full_content, ''), posts.content, '')))"
                : "length(trim(coalesce(posts.content, '')))";
            $translatedBodySql = "length(trim(coalesce(posts.translated_content, '')))";

            $query->where(function ($q) use ($to, $sourceBodySql, $translatedBodySql) {
                $q->where(function ($legacy) use ($to) {
                    $legacy->whereNull('translated_to')
                        ->orWhere('translated_to', '!=', $to)
                        ->orWhereNull('translated_name');
                });

                if (Schema::hasTable('grimba_post_translations')) {
                    $q->whereNotExists(function ($sub) use ($to) {
                        $sub->selectRaw('1')
                            ->from('grimba_post_translations')
                            ->whereColumn('grimba_post_translations.post_id', 'posts.id')
                            ->where('grimba_post_translations.locale', $to)
                            ->whereNotNull('grimba_post_translations.translated_name');
                    });
                }

                $q->orWhere(function ($body) use ($to, $sourceBodySql, $translatedBodySql) {
                    $body->whereRaw($sourceBodySql . ' >= ?', [GrimbaArticleText::MIN_READABLE_CHARS])
                        ->where(function ($needsBody) use ($translatedBodySql, $sourceBodySql, $to) {
                            $needsBody
                                ->whereNull('posts.translated_content')
                                ->orWhereRaw("trim(coalesce(posts.translated_content, '')) = ''")
                                ->orWhereRaw($translatedBodySql . ' < (' . $sourceBodySql . ' * 0.35)');

                            if (Schema::hasTable('grimba_post_translations')) {
                                $needsBody->orWhereNotExists(function ($sub) use ($to) {
                                    $sub->selectRaw('1')
                                        ->from('grimba_post_translations')
                                        ->whereColumn('grimba_post_translations.post_id', 'posts.id')
                                        ->where('grimba_post_translations.locale', $to)
                                        ->whereNotNull('grimba_post_translations.translated_content')
                                        ->whereRaw("trim(coalesce(grimba_post_translations.translated_content, '')) != ''");
                                });
                            }
                        });
                });
            });
        }

        $columns = ['id', 'name', 'description', 'content', 'original_language', 'translated_to'];
        if ($hasFullContent) {
            $columns[] = 'full_content';
        }
        // S-LANG-09 — pull the summary + its locale so the loop below
        // can decide whether the NobuAI synthesis needs translating.
        // Zen audit 2026-05-17: hoist schema reflection outside the
        // per-post loop. These two booleans drive both the SELECT list
        // here AND the per-row decision below — single source of truth.
        $hasSummaryCol = Schema::hasColumn('posts', 'summary_nobuai');
        $hasSummaryLocaleCol = Schema::hasColumn('posts', 'summary_nobuai_locale');
        $hasTranslatedSummaryCol = Schema::hasTable('grimba_post_translations')
            && Schema::hasColumn('grimba_post_translations', 'translated_summary');
        if ($hasSummaryCol) {
            $columns[] = 'summary_nobuai';
        }
        if ($hasSummaryLocaleCol) {
            $columns[] = 'summary_nobuai_locale';
        }

        $posts = $query->get($columns);

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
            $contentHtml = GrimbaArticleText::cleanIngestBody($p->full_content ?? null)
                ?: GrimbaArticleText::cleanIngestBody($p->content ?? null)
                ?: (string) ($p->content ?? '');
            $contentPlain = trim(strip_tags((string) $contentHtml));
            $descPlain    = trim(strip_tags((string) ($p->description ?? '')));
            $tContent = null;
            if ($contentPlain !== '' && mb_strlen($contentPlain) > mb_strlen($descPlain) + 40) {
                $tContent = $translator->translate((string) $contentHtml, (string) $p->original_language, $to);
            }

            if ($tName === null) {
                $this->line('    (skipped — all providers failed)');
                $this->recordFailure($p, $to, $translator);
                $fail++;
                continue;
            }

            // S-LANG-09 — translate the NobuAI summary when the column
            // is present and the post has one in its native locale that
            // differs from $to. (Zen audit 2026-05-17: schema reflection
            // is hoisted outside the loop — see below.)
            $tSummary = null;
            if ($hasSummaryCol && $hasTranslatedSummaryCol) {
                $summaryRaw = trim((string) ($p->summary_nobuai ?? ''));
                $summaryLocale = strtolower(substr((string) ($p->summary_nobuai_locale ?? ''), 0, 2)) ?: strtolower(substr((string) ($p->original_language ?? ''), 0, 2));
                if ($summaryRaw !== '' && $summaryLocale !== '' && $summaryLocale !== strtolower($to)) {
                    $tSummary = $translator->translate($summaryRaw, $summaryLocale, $to);
                }
            }

            $payload = [
                'translated_name'        => $tName['text'],
                'translated_description' => $tDesc['text'] ?? null,
                'translated_content'     => $tContent['text'] ?? null,
                'translated_to'          => $to,
                'translated_at'          => now(),
                'translation_driver'     => $tName['driver'],
            ];

            DB::table('posts')->where('id', $p->id)->update($payload);

            if (Schema::hasTable('grimba_post_translations')) {
                $now = now();
                $joinPayload = [
                    'translated_name' => $payload['translated_name'],
                    'translated_description' => $payload['translated_description'],
                    'translated_content' => $payload['translated_content'],
                    'translation_driver' => $payload['translation_driver'],
                    'translated_at' => $payload['translated_at'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ];
                if ($hasTranslatedSummaryCol && $tSummary !== null) {
                    $joinPayload['translated_summary'] = $tSummary['text'] ?? null;
                }
                DB::table('grimba_post_translations')->updateOrInsert(
                    ['post_id' => $p->id, 'locale' => $to],
                    $joinPayload
                );
            }

            $this->clearFailure((int) $p->id, $to);

            $this->line(sprintf('    → %s via %s', \Illuminate\Support\Str::limit($tName['text'], 60), $tName['driver']));
            $ok++;
        }

        $this->info(sprintf('Done. ok=%d fail=%d%s', $ok, $fail, $dry ? ' (dry-run)' : ''));
        return self::SUCCESS;
    }

    private function recordFailure(object $post, string $to, GrimbaTranslator $translator): void
    {
        if (! Schema::hasTable('grimba_translation_failures')) {
            return;
        }

        $diagnostics = $translator->failureDiagnostics();
        $message = collect($diagnostics)
            ->map(fn (array $item) => $item['driver'] . ': ' . $item['message'])
            ->filter()
            ->implode(' | ');

        if ($message === '') {
            $message = 'All configured translation providers failed.';
        }

        $existing = DB::table('grimba_translation_failures')
            ->where('post_id', $post->id)
            ->where('locale', $to)
            ->first(['attempts']);

        $payload = [
            'source_language' => strtolower(substr((string) ($post->original_language ?? ''), 0, 8)) ?: null,
            'driver_chain' => implode(' → ', $translator->configuredDrivers()),
            'error_message' => \Illuminate\Support\Str::limit($message, 1000, ''),
            'attempts' => ((int) ($existing->attempts ?? 0)) + 1,
            'failed_at' => now(),
            'updated_at' => now(),
        ];

        if (! $existing) {
            $payload['created_at'] = now();
        }

        DB::table('grimba_translation_failures')->updateOrInsert(
            ['post_id' => $post->id, 'locale' => $to],
            $payload
        );
    }

    private function clearFailure(int $postId, string $to): void
    {
        if (! Schema::hasTable('grimba_translation_failures')) {
            return;
        }

        DB::table('grimba_translation_failures')
            ->where('post_id', $postId)
            ->where('locale', $to)
            ->delete();
    }
}
