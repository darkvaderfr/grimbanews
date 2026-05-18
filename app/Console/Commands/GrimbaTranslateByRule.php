<?php

namespace App\Console\Commands;

use App\Services\GrimbaTranslator;
use App\Support\GrimbaArticleText;
use App\Support\GrimbaLanguageSettings;
use App\Support\GrimbaTranslationRules;
use Botble\Blog\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * S-LSAT-10 (Vader 2026-05-18) — rule-driven auto-translation.
 *
 * Vader's directive: "NobuAI translation via NobuTranslate will
 * translate all African-related articles and selectively translate
 * most-viewed articles or popular articles to the other language
 * (let's say an articles from Le Monde has 500+ views, nobuai should
 * automatically translate it)."
 *
 * Decomposition:
 *   1. Query un-translated posts (status published, has language,
 *      no translation in opposite locale yet).
 *   2. Feed them to GrimbaTranslationRules (the pure-function engine
 *      from S-LSAT-09) along with `callsToday()` so the daily cap is
 *      honored.
 *   3. For each picked post: bump `translation_priority`, then call
 *      the translator chain inline. Same payload shape as
 *      GrimbaTranslatePending so the resulting row blends cleanly
 *      with manual translation runs.
 *
 * The scheduler (routes/console.php) runs this every 15 minutes.
 *
 * @see App\Support\GrimbaTranslationRules
 * @see App\Support\GrimbaLanguageSettings
 */
class GrimbaTranslateByRule extends Command
{
    protected $signature = 'grimba:translate-by-rule
        {--limit=200 : Max posts to evaluate this run (rule engine still caps via daily budget)}
        {--dry-run : Report which posts would be translated without calling providers}';

    protected $description = 'Auto-translate posts that match the popularity / region rules from GrimbaTranslationSettings.';

    public function handle(GrimbaTranslator $translator): int
    {
        if (! GrimbaLanguageSettings::ruleEngineEnabled()) {
            $this->info('[rule-engine] disabled via grimba_lang_rule_engine_enabled — no work.');
            return self::SUCCESS;
        }

        if (! $translator->enabled()) {
            $this->warn('[rule-engine] no translation providers configured. Skipping.');
            return self::SUCCESS;
        }

        $limit = max(1, (int) $this->option('limit'));
        $dry = (bool) $this->option('dry-run');

        $cap = GrimbaLanguageSettings::ruleEngineDailyCap();
        $burned = self::callsToday();
        $remaining = max(0, $cap - $burned);

        if ($remaining === 0 && ! $dry) {
            $this->info(sprintf('[rule-engine] daily cap reached: %d/%d. Next run tomorrow.', $burned, $cap));
            return self::SUCCESS;
        }

        $columns = ['id', 'name', 'description', 'content', 'original_language', 'translated_to', 'editorial_region', 'views', 'translation_priority'];
        if (Schema::hasColumn('posts', 'full_content')) {
            $columns[] = 'full_content';
        }
        if (Schema::hasColumn('posts', 'summary_nobuai')) {
            $columns[] = 'summary_nobuai';
        }
        if (Schema::hasColumn('posts', 'summary_nobuai_locale')) {
            $columns[] = 'summary_nobuai_locale';
        }

        // We pull candidates ordered by translation_priority DESC
        // then views DESC so editorial pins (priority=2 via the
        // post-edit form) reach the front of the queue before
        // popularity-threshold matches. Within priority bands, the
        // highest-signal posts still hit the cap first; without
        // this an africa-region backlog could starve the global
        // popularity rule when the cap is tight.
        $candidates = Post::query()
            ->where('status', 'published')
            ->whereNotNull('original_language')
            ->whereIn('original_language', ['fr', 'en'])
            ->orderByDesc('translation_priority')
            ->orderByDesc('views')
            ->orderByDesc('id')
            ->limit($limit)
            ->get($columns);

        if ($candidates->isEmpty()) {
            $this->info('[rule-engine] no candidates with original_language set.');
            return self::SUCCESS;
        }

        $picked = GrimbaTranslationRules::selectTranslatable($candidates, $burned);

        if (empty($picked)) {
            $this->info(sprintf(
                '[rule-engine] %d candidates evaluated, 0 matched the rule (burned=%d/%d).',
                $candidates->count(),
                $burned,
                $cap
            ));
            return self::SUCCESS;
        }

        $this->info(sprintf(
            '[rule-engine] %d candidates, %d match — cap %d/%d, %d slots free.',
            $candidates->count(),
            count($picked),
            $burned,
            $cap,
            $remaining
        ));

        $hasFullContent = Schema::hasColumn('posts', 'full_content');
        $hasSummaryCol = Schema::hasColumn('posts', 'summary_nobuai');
        $hasSummaryLocaleCol = Schema::hasColumn('posts', 'summary_nobuai_locale');
        $hasTranslatedSummaryCol = Schema::hasTable('grimba_post_translations')
            && Schema::hasColumn('grimba_post_translations', 'translated_summary');

        $ok = 0;
        $fail = 0;
        $hadPriorityCol = Schema::hasColumn('posts', 'translation_priority');

        foreach ($picked as $pair) {
            $p = $pair['post'];
            $d = $pair['decision'];
            $to = $d->targetLocale;
            if ($to === null) {
                continue;
            }

            $this->line(sprintf(
                '  #%d [%s → %s, region=%s, views=%d] %s',
                $p->id,
                $p->original_language,
                $to,
                $p->editorial_region ?? '-',
                (int) ($p->views ?? 0),
                \Illuminate\Support\Str::limit($p->name, 60),
            ));
            $this->line(sprintf('    reason: %s', $d->reason));

            if ($dry) {
                // S-LSAT-19 — dashboard still surfaces dry-run
                // decisions so operators can see what WOULD fire.
                self::recordDecision([
                    'ts'      => now()->toIso8601String(),
                    'post_id' => (int) $p->id,
                    'title'   => \Illuminate\Support\Str::limit((string) ($p->name ?? ''), 120),
                    'from'    => (string) ($p->original_language ?? ''),
                    'to'      => $to,
                    'region'  => (string) ($p->editorial_region ?? ''),
                    'views'   => (int) ($p->views ?? 0),
                    'reason'  => $d->reason,
                    'outcome' => 'dry',
                ]);
                continue;
            }

            // Bump priority FIRST so even if translation fails the
            // ordering signal survives for the manual translator run.
            if ($hadPriorityCol && (int) ($p->translation_priority ?? 0) < $d->priority) {
                DB::table('posts')
                    ->where('id', $p->id)
                    ->update(['translation_priority' => $d->priority]);
            }

            $tName = $translator->translate((string) $p->name, (string) $p->original_language, $to);
            if ($tName === null) {
                $this->line('    (skipped — all providers failed)');
                self::recordDecision([
                    'ts'      => now()->toIso8601String(),
                    'post_id' => (int) $p->id,
                    'title'   => \Illuminate\Support\Str::limit((string) ($p->name ?? ''), 120),
                    'from'    => (string) ($p->original_language ?? ''),
                    'to'      => $to,
                    'region'  => (string) ($p->editorial_region ?? ''),
                    'views'   => (int) ($p->views ?? 0),
                    'reason'  => $d->reason,
                    'outcome' => 'fail',
                ]);
                $fail++;
                continue;
            }

            $tDesc = $p->description
                ? $translator->translate((string) $p->description, (string) $p->original_language, $to)
                : null;

            $contentHtml = GrimbaArticleText::cleanIngestBody($p->full_content ?? null)
                ?: GrimbaArticleText::cleanIngestBody($p->content ?? null)
                ?: (string) ($p->content ?? '');
            $contentPlain = trim(strip_tags((string) $contentHtml));
            $descPlain = trim(strip_tags((string) ($p->description ?? '')));
            $tContent = null;
            if ($contentPlain !== '' && mb_strlen($contentPlain) > mb_strlen($descPlain) + 40) {
                $tContent = $translator->translate((string) $contentHtml, (string) $p->original_language, $to);
            }

            $tSummary = null;
            if ($hasSummaryCol && $hasTranslatedSummaryCol) {
                $summaryRaw = trim((string) ($p->summary_nobuai ?? ''));
                $summaryLocale = strtolower(substr((string) ($p->summary_nobuai_locale ?? ''), 0, 2))
                    ?: strtolower(substr((string) ($p->original_language ?? ''), 0, 2));
                if ($summaryRaw !== '' && $summaryLocale !== '' && $summaryLocale !== strtolower($to)) {
                    $tSummary = $translator->translate($summaryRaw, $summaryLocale, $to);
                }
            }

            $payload = [
                'translated_name' => $tName['text'],
                'translated_description' => $tDesc['text'] ?? null,
                'translated_content' => $tContent['text'] ?? null,
                'translated_to' => $to,
                'translated_at' => now(),
                'translation_driver' => $tName['driver'],
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

            self::recordCall();
            self::recordDecision([
                'ts'      => now()->toIso8601String(),
                'post_id' => (int) $p->id,
                'title'   => \Illuminate\Support\Str::limit((string) ($p->name ?? ''), 120),
                'from'    => (string) ($p->original_language ?? ''),
                'to'      => $to,
                'region'  => (string) ($p->editorial_region ?? ''),
                'views'   => (int) ($p->views ?? 0),
                'reason'  => $d->reason,
                'outcome' => 'ok',
                'driver'  => (string) ($tName['driver'] ?? ''),
            ]);
            $this->line(sprintf('    → %s via %s', \Illuminate\Support\Str::limit($tName['text'], 60), $tName['driver']));
            $ok++;
        }

        $this->info(sprintf(
            '[rule-engine] done. ok=%d fail=%d burned=%d/%d%s',
            $ok,
            $fail,
            self::callsToday(),
            $cap,
            $dry ? ' (dry-run)' : ''
        ));
        return self::SUCCESS;
    }

    /**
     * How many translation calls the rule engine has issued today.
     * Stored in a daily cache key so it resets at midnight without
     * needing a dedicated DB column.
     */
    public static function callsToday(): int
    {
        return (int) Cache::get(self::callsCacheKey(), 0);
    }

    public static function recordCall(int $count = 1): void
    {
        $key = self::callsCacheKey();
        $current = (int) Cache::get($key, 0);
        // 36h TTL: comfortable margin past midnight rollover.
        Cache::put($key, $current + $count, now()->addHours(36));
    }

    private static function callsCacheKey(): string
    {
        return 'grimba_rule_engine_calls:' . now()->format('Y-m-d');
    }

    /**
     * S-LSAT-19 (Vader 2026-05-18) — rolling decisions log for the
     * admin observability dashboard. Each entry carries timestamp +
     * post id + post title (truncated) + reason + target locale +
     * outcome (ok / fail / dry / pinned). Capped at 100 entries on a
     * 36h TTL so a noisy day doesn't fill the cache.
     */
    public static function recordDecision(array $entry): void
    {
        $key = self::decisionsCacheKey();
        $log = (array) Cache::get($key, []);
        // Prepend so the most recent decision is index 0 — the
        // dashboard reads from index 0 down.
        array_unshift($log, $entry);
        if (count($log) > 100) {
            $log = array_slice($log, 0, 100);
        }
        Cache::put($key, $log, now()->addHours(36));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function recentDecisions(int $limit = 50): array
    {
        $log = (array) Cache::get(self::decisionsCacheKey(), []);
        return array_slice($log, 0, max(1, min(100, $limit)));
    }

    public static function clearDecisions(): void
    {
        Cache::forget(self::decisionsCacheKey());
    }

    private static function decisionsCacheKey(): string
    {
        return 'grimba_rule_engine_decisions:' . now()->format('Y-m-d');
    }
}
