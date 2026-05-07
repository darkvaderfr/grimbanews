<?php

namespace App\Console\Commands;

use App\Support\GrimbaPostPublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/*
 * S150 — auto-publish drafts from trusted classified sources.
 *
 * Most NewsAPI ingests land as drafts so editors can review before
 * the public reader sees them. With 55+ sources now properly bias-
 * classified (S129, S149), the long tail of well-known outlets
 * (Le Monde, Reuters, BBC, AFP, etc.) doesn't actually need per-
 * article editor review — their bias is documented, their
 * credibility is high, the story-page bias bar will read true.
 *
 * Conservative auto-publish rule: publish drafts where ALL of:
 *   1. source has bias_rating in (left, center, right)         — classified
 *   2. source.credibility_score >= grimba_autopub_min_credibility (default 70)
 *   3. post.created_at <= now() - grimba_autopub_age_hours (default 1h)
 *      → editor still has a 1h window to demote / edit / kill
 *   4. post is currently a draft (we never re-publish or revert)
 *
 * Editor-set values (someone moved a post to draft manually) are
 * sacred — we only target drafts that came in from ingest. The
 * status='draft' check + age window is enough; if an editor wanted
 * a recent classified post stopped, they had time to do it.
 *
 * Bypassed entirely when grimba_autopub_active = false.
 */
class GrimbaPublishTrusted extends Command
{
    protected $signature = 'grimba:publish-trusted
        {--threshold= : credibility_score minimum (default: setting / 70)}
        {--age-hours= : minimum draft age in hours (default: setting / 1)}
        {--limit=200 : cap auto-publishes per run}
        {--dry-run : preview without writing}';

    protected $description = 'Auto-publish drafts from trusted classified sources (S150). Skips unknown-bias and low-credibility outlets.';

    public function handle(): int
    {
        $active = (bool) setting('grimba_autopub_active', true);
        if (! $active && ! $this->option('dry-run')) {
            $this->warn('grimba_autopub_active=false — nothing to do.');
            $this->logRun(['active' => false, 'published' => 0, 'duration_s' => 0]);
            return self::SUCCESS;
        }

        $threshold = $this->option('threshold') !== null
            ? (int) $this->option('threshold')
            : (int) setting('grimba_autopub_min_credibility', 70);

        $ageHours = $this->option('age-hours') !== null
            ? (int) $this->option('age-hours')
            : (int) setting('grimba_autopub_age_hours', 1);

        $limit = (int) $this->option('limit');
        $dry   = (bool) $this->option('dry-run');
        $start = microtime(true);

        $cutoff = now()->subHours($ageHours);

        $candidates = DB::table('posts')
            ->join('news_sources', 'news_sources.id', '=', 'posts.source_id')
            ->where('posts.status', 'draft')
            ->whereIn('news_sources.bias_rating', ['left', 'center', 'right'])
            ->where('news_sources.credibility_score', '>=', $threshold)
            ->where('posts.created_at', '<=', $cutoff)
            ->orderByDesc('posts.created_at')
            ->orderByDesc('posts.id')
            ->limit($limit)
            ->select('posts.id', 'posts.name', 'news_sources.name as source_name', 'news_sources.bias_rating', 'news_sources.credibility_score')
            ->get();

        $this->info(sprintf(
            '%d candidate(s) — credibility ≥%d · age ≥%dh · classified bias%s',
            $candidates->count(), $threshold, $ageHours, $dry ? ' [DRY RUN]' : ''
        ));

        if ($candidates->isEmpty()) {
            $this->logRun([
                'active' => true, 'threshold' => $threshold, 'age_hours' => $ageHours,
                'published' => 0, 'dry' => $dry, 'duration_s' => round(microtime(true) - $start, 2),
            ]);
            return self::SUCCESS;
        }

        $byBias = ['left' => 0, 'center' => 0, 'right' => 0];
        $ids = [];
        foreach ($candidates as $c) {
            $byBias[$c->bias_rating] = ($byBias[$c->bias_rating] ?? 0) + 1;
            $ids[] = (int) $c->id;
        }

        if (! $dry) {
            // Bulk update inside a single transaction; keeps the
            // moment of "publication" atomic across all candidates
            // so a bias-distribution reader doesn't see half a
            // promotion mid-query.
            DB::transaction(function () use ($ids): void {
                GrimbaPostPublisher::publishDrafts($ids);
            });
        }

        $this->table(['Bias', 'Published'], [
            ['Gauche',     $byBias['left']],
            ['Centre',     $byBias['center']],
            ['Droite',     $byBias['right']],
            ['Total',      array_sum($byBias)],
        ]);

        if ($dry) {
            $this->warn('DRY RUN — no posts were promoted.');
        } else {
            $this->info(sprintf('Published %d post(s) in %ss.',
                count($ids), round(microtime(true) - $start, 2)));
        }

        $this->logRun([
            'active' => true, 'threshold' => $threshold, 'age_hours' => $ageHours,
            'published' => $dry ? 0 : count($ids),
            'by_bias' => $byBias,
            'dry' => $dry, 'duration_s' => round(microtime(true) - $start, 2),
        ]);

        return self::SUCCESS;
    }

    private function logRun(array $stats): void
    {
        $line = sprintf(
            "[%s] grimba:publish-trusted threshold=%d age=%dh published=%d (L=%d C=%d R=%d) dry=%s duration=%ss\n",
            now()->toIso8601String(),
            (int) ($stats['threshold'] ?? 0),
            (int) ($stats['age_hours'] ?? 0),
            (int) ($stats['published'] ?? 0),
            (int) (($stats['by_bias'] ?? [])['left']   ?? 0),
            (int) (($stats['by_bias'] ?? [])['center'] ?? 0),
            (int) (($stats['by_bias'] ?? [])['right']  ?? 0),
            (! empty($stats['dry'])) ? 'true' : 'false',
            $stats['duration_s'] ?? 0
        );
        @file_put_contents(storage_path('logs/grimba-autopub.log'), $line, FILE_APPEND | LOCK_EX);
        Log::info('[grimba:publish-trusted] run complete', $stats);
    }
}
