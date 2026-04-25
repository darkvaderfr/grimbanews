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
        foreach ($summary as $s) {
            $rows[] = [
                $s['kind'],
                $s['query'],
                $s['status'],
                $s['total'],
                $s['ingested'],
                $s['error'] ? \Illuminate\Support\Str::limit($s['error'], 60) : '',
            ];
            $totalIngested += (int) $s['ingested'];
            $totalSeen += (int) $s['total'];
        }

        $this->table(['Endpoint', 'Query', 'Status', 'Seen', 'Ingested', 'Error'], $rows);
        $duration = round(microtime(true) - $startedAt, 2);
        $this->info(sprintf(
            'Done. %d call(s), %d articles seen, %d new draft post(s) in %ss.',
            count($summary), $totalSeen, $totalIngested, $duration
        ));

        // Match S105 logging convention so all ingest cadences land
        // in the same trend log under storage/logs/.
        $line = sprintf(
            "[%s] grimba:fetch-newsapi calls=%d seen=%d ingested=%d duration=%ss\n",
            now()->toIso8601String(),
            count($summary), $totalSeen, $totalIngested, $duration
        );
        @file_put_contents(storage_path('logs/grimba-newsapi.log'), $line, FILE_APPEND | LOCK_EX);
        Log::info('[grimba:fetch-newsapi] run complete', [
            'calls' => count($summary), 'seen' => $totalSeen,
            'ingested' => $totalIngested, 'duration_s' => $duration,
        ]);

        return self::SUCCESS;
    }
}
