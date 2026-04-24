<?php

namespace App\Console\Commands;

use App\Services\GrimbaRssPoller;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GrimbaPollFeeds extends Command
{
    protected $signature = 'grimba:poll-feeds {--feed= : Only poll a specific rss_feeds.id}';

    protected $description = 'Poll active RSS/Atom feeds, dedup items, and create draft posts for editor review.';

    public function handle(GrimbaRssPoller $poller): int
    {
        $this->info('Polling GrimbaNews RSS feeds…');

        if ($feedId = $this->option('feed')) {
            $feed = \DB::table('rss_feeds')
                ->join('news_sources', 'news_sources.id', '=', 'rss_feeds.source_id')
                ->where('rss_feeds.id', (int) $feedId)
                ->first([
                    'rss_feeds.*',
                    'news_sources.name as source_name',
                    'news_sources.bias_rating as source_bias',
                ]);
            if (! $feed) {
                $this->error("No feed with id {$feedId}.");
                return self::FAILURE;
            }
            $summary = [$poller->pollOne($feed)];
        } else {
            $summary = $poller->pollAll();
        }

        $rows = [];
        $totalIngested = 0;
        foreach ($summary as $s) {
            $rows[] = [
                $s['feed_id'],
                $s['source_name'],
                $s['status'],
                $s['ingested'],
                $s['error'] ? \Illuminate\Support\Str::limit($s['error'], 60) : '',
            ];
            $totalIngested += $s['ingested'];
        }

        $this->table(['Feed', 'Source', 'Status', 'New drafts', 'Error'], $rows);
        $this->info(sprintf('Done. %d feed(s), %d new draft post(s).', count($summary), $totalIngested));

        $this->flagUnhealthyFeeds();

        return self::SUCCESS;
    }

    /**
     * Log + echo a warning for feeds that have missed 5+ polls in a row.
     * A scheduled run still returns success so a single upstream flap
     * doesn't mark the whole schedule:run as failed; an editor can react
     * via the admin UI shipped in S72.
     */
    private function flagUnhealthyFeeds(int $threshold = 5): void
    {
        $sick = DB::table('rss_feeds')
            ->join('news_sources', 'news_sources.id', '=', 'rss_feeds.source_id')
            ->where('rss_feeds.is_active', true)
            ->where('rss_feeds.consecutive_failures', '>=', $threshold)
            ->orderByDesc('rss_feeds.consecutive_failures')
            ->get([
                'rss_feeds.id',
                'rss_feeds.url',
                'rss_feeds.consecutive_failures',
                'rss_feeds.last_error',
                'news_sources.name as source_name',
            ]);

        if ($sick->isEmpty()) {
            return;
        }

        $this->warn(sprintf(
            '⚠ %d feed(s) have failed %d+ times in a row — editor action needed:',
            $sick->count(),
            $threshold
        ));

        foreach ($sick as $f) {
            $msg = sprintf(
                '  feed #%d (%s) — %d consecutive failures — %s',
                $f->id,
                $f->source_name,
                $f->consecutive_failures,
                \Illuminate\Support\Str::limit((string) $f->last_error, 80)
            );
            $this->line($msg);
            Log::warning('[grimba:poll-feeds] unhealthy feed', [
                'feed_id'              => $f->id,
                'source'               => $f->source_name,
                'url'                  => $f->url,
                'consecutive_failures' => $f->consecutive_failures,
                'last_error'           => $f->last_error,
            ]);
        }
    }
}
