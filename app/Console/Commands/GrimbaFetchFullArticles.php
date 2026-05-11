<?php

namespace App\Console\Commands;

use App\Services\GrimbaArticleExtractor;
use App\Support\GrimbaPostRecency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/*
 * S163 — backfill posts.full_content from upstream article URLs.
 *
 * Walks all published posts that have a usable upstream link
 * (rss_feed_items.link or newsapi_items.article_url) and no
 * full_content yet (or a stale extraction), runs them through
 * GrimbaArticleExtractor, persists the cleaned body.
 *
 * Cron-friendly: idempotent, --limit caps batches, --force re-runs
 * even on already-extracted rows. Errors are stored on the post so
 * editors can audit failure modes.
 */
class GrimbaFetchFullArticles extends Command
{
    protected $signature = 'grimba:fetch-full-articles
        {--limit=50              : max posts processed per run}
        {--force                 : re-extract even when full_content is already set}
        {--post=                 : only process this post id}
        {--retry-after-hours=24  : retry failed/missing extractions after this many hours}';

    protected $description = 'Fetch + extract full article body for paid-tier reading (S163).';

    public function handle(GrimbaArticleExtractor $extractor): int
    {
        $limit = (int) $this->option('limit');
        $force = (bool) $this->option('force');
        $only  = $this->option('post');
        $retryAfterHours = max(0, (int) $this->option('retry-after-hours'));

        $start = microtime(true);

        // Build candidate set: published posts with an upstream URL,
        // missing full_content (unless --force).
        $rss = DB::table('posts')
            ->join('rss_feed_items', 'rss_feed_items.post_id', '=', 'posts.id')
            ->where('posts.status', 'published')
            ->whereNotNull('rss_feed_items.link')
            ->select('posts.id', 'rss_feed_items.link as url');

        $api = DB::table('posts')
            ->join('newsapi_items', 'newsapi_items.post_id', '=', 'posts.id')
            ->where('posts.status', 'published')
            ->whereNotNull('newsapi_items.article_url')
            ->select('posts.id', 'newsapi_items.article_url as url');

        $query = $rss->union($api);

        $candidates = DB::query()
            ->fromSub($query, 'pool')
            ->join('posts', 'posts.id', '=', 'pool.id')
            ->when(! $force, function ($q) use ($only, $retryAfterHours): void {
                $q->whereRaw("TRIM(COALESCE(posts.full_content, '')) = ''");

                if ($only === null && $retryAfterHours > 0) {
                    $q->where(function ($retry) use ($retryAfterHours): void {
                        $retry->whereNull('posts.full_fetched_at')
                            ->orWhere('posts.full_fetched_at', '<=', now()->subHours($retryAfterHours));
                    });
                }
            })
            ->when($only !== null, fn ($q) => $q->where('posts.id', (int) $only))
            ->select('pool.id', 'pool.url');

        if (! $force) {
            $candidates->orderByRaw('posts.full_fetched_at IS NULL DESC');
        }

        GrimbaPostRecency::orderByPublished($candidates, 'posts');

        $candidates = $candidates
            ->limit($limit)
            ->get();

        if ($candidates->isEmpty()) {
            $retryNote = $force
                ? ''
                : sprintf(' Recent failures wait %dh before retry; use --force or --post=ID to override.', $retryAfterHours);
            $this->info('Nothing to extract.' . $retryNote);
            return self::SUCCESS;
        }

        $this->info(sprintf('Extracting %d post(s)…', $candidates->count()));

        $ok = 0;
        $fail = 0;

        $bar = $this->output->createProgressBar($candidates->count());
        $bar->start();

        foreach ($candidates as $row) {
            $result = $extractor->extractFromUrl((string) $row->url);

            if ($result['ok']) {
                DB::table('posts')->where('id', $row->id)->update([
                    'full_content'        => $result['html'],
                    'full_fetched_at'     => now(),
                    'full_extract_error'  => null,
                    'updated_at'          => now(),
                ]);
                $ok++;
            } else {
                DB::table('posts')->where('id', $row->id)->update([
                    'full_fetched_at'    => now(),
                    'full_extract_error' => Str::limit((string) $result['error'], 180, ''),
                    'updated_at'         => now(),
                ]);
                $fail++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $duration = round(microtime(true) - $start, 2);
        $this->info(sprintf('Done. %d ok · %d failed · %ss.', $ok, $fail, $duration));

        Log::info('[grimba:fetch-full-articles] run complete', [
            'ok' => $ok,
            'failed' => $fail,
            'duration_s' => $duration,
            'forced' => $force,
            'retry_after_hours' => $retryAfterHours,
        ]);

        return self::SUCCESS;
    }
}
