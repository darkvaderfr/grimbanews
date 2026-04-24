<?php

namespace App\Console\Commands;

use App\Services\GrimbaRssPoller;
use Illuminate\Console\Command;

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

        return self::SUCCESS;
    }
}
