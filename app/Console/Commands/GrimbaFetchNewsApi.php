<?php

namespace App\Console\Commands;

use App\Services\GrimbaNewsApiFetcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GrimbaFetchNewsApi extends Command
{
    protected $signature = 'grimba:fetch-newsapi';

    protected $description = 'Fetch articles from NewsAPI.org top-headlines + everything queries and ingest as draft posts (S128).';

    public function handle(GrimbaNewsApiFetcher $fetcher): int
    {
        if (! $fetcher->isConfigured()) {
            $this->warn('NewsAPI key not set. Configure NEWSAPI_KEY in .env or paste it at /admin/grimba/newsapi.');
            return self::SUCCESS;
        }

        $this->info('Fetching GrimbaNews — NewsAPI…');
        $startedAt = microtime(true);

        $summary = $fetcher->fetchAll();

        $rows = [];
        $totalIngested = 0;
        $totalSeen = 0;
        $totalReturned = 0;
        $totalDeduped = 0;
        $totalSkipped = 0;
        foreach ($summary as $s) {
            $rows[] = [
                $s['kind'],
                $s['query'],
                $s['status'],
                $s['total'],
                $s['returned'],
                $s['ingested'],
                $s['deduped'],
                $s['skipped'],
                $s['error'] ? \Illuminate\Support\Str::limit($s['error'], 60) : '',
            ];
            $totalIngested += (int) $s['ingested'];
            $totalSeen += (int) $s['total'];
            $totalReturned += (int) $s['returned'];
            $totalDeduped += (int) $s['deduped'];
            $totalSkipped += (int) $s['skipped'];
        }

        $this->table(['Endpoint', 'Query', 'Status', 'Seen', 'Returned', 'Ingested', 'Deduped', 'Skipped', 'Error'], $rows);
        $duration = round(microtime(true) - $startedAt, 2);
        $this->info(sprintf(
            'Done. %d call(s), %d seen, %d returned, %d ingested, %d deduped, %d skipped in %ss.',
            count($summary), $totalSeen, $totalReturned, $totalIngested, $totalDeduped, $totalSkipped, $duration
        ));

        // Match S105 logging convention so all ingest cadences land
        // in the same trend log under storage/logs/.
        $line = sprintf(
            "[%s] grimba:fetch-newsapi calls=%d seen=%d returned=%d ingested=%d deduped=%d skipped=%d duration=%ss\n",
            now()->toIso8601String(),
            count($summary), $totalSeen, $totalReturned, $totalIngested, $totalDeduped, $totalSkipped, $duration
        );
        @file_put_contents(storage_path('logs/grimba-newsapi.log'), $line, FILE_APPEND | LOCK_EX);
        Log::info('[grimba:fetch-newsapi] run complete', [
            'calls' => count($summary), 'seen' => $totalSeen,
            'returned' => $totalReturned, 'ingested' => $totalIngested,
            'deduped' => $totalDeduped, 'skipped' => $totalSkipped,
            'duration_s' => $duration,
        ]);

        return self::SUCCESS;
    }
}
