<?php

namespace App\Console\Commands;

use App\Services\GrimbaLiveNewsFetcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GrimbaFetchBreakingNews extends Command
{
    protected $signature = 'grimba:fetch-breaking {--provider=* : Provider(s) to run: gdelt, google-news, webz, mediastack}';

    protected $description = 'Fetch live/breaking articles from free + paid providers and publish them into GrimbaNews.';

    public function handle(GrimbaLiveNewsFetcher $fetcher): int
    {
        $providers = $this->option('provider') ?: null;

        $this->info('Fetching GrimbaNews live providers...');
        $startedAt = microtime(true);

        $summary = $fetcher->fetchAll($providers);

        $rows = [];
        $totalReturned = 0;
        $totalIngested = 0;
        $totalDeduped = 0;
        $totalSkipped = 0;
        $failed = false;
        $hasOkProvider = false;

        foreach ($summary as $row) {
            $rows[] = [
                $row['provider'],
                Str::limit($row['query'], 64),
                $row['status'],
                $row['returned'],
                $row['ingested'],
                $row['deduped'],
                $row['skipped'],
                $row['error'] ? Str::limit($row['error'], 60) : '',
            ];

            $totalReturned += (int) $row['returned'];
            $totalIngested += (int) $row['ingested'];
            $totalDeduped += (int) $row['deduped'];
            $totalSkipped += (int) $row['skipped'];
            $failed = $failed || $row['status'] === 'failed';
            $hasOkProvider = $hasOkProvider || $row['status'] === 'ok';
        }

        $this->table(['Provider', 'Query', 'Status', 'Returned', 'Ingested', 'Deduped', 'Skipped', 'Error'], $rows);

        $duration = round(microtime(true) - $startedAt, 2);
        $this->info(sprintf(
            'Done. %d provider call(s), %d returned, %d ingested, %d deduped, %d skipped in %ss.',
            count($summary),
            $totalReturned,
            $totalIngested,
            $totalDeduped,
            $totalSkipped,
            $duration
        ));

        $line = sprintf(
            "[%s] grimba:fetch-breaking calls=%d returned=%d ingested=%d deduped=%d skipped=%d duration=%ss\n",
            now()->toIso8601String(),
            count($summary),
            $totalReturned,
            $totalIngested,
            $totalDeduped,
            $totalSkipped,
            $duration
        );
        @file_put_contents(storage_path('logs/grimba-breaking.log'), $line, FILE_APPEND | LOCK_EX);

        Log::info('[grimba:fetch-breaking] run complete', [
            'calls' => count($summary),
            'returned' => $totalReturned,
            'ingested' => $totalIngested,
            'deduped' => $totalDeduped,
            'skipped' => $totalSkipped,
            'duration_s' => $duration,
        ]);

        return ($failed && ! $hasOkProvider) ? self::FAILURE : self::SUCCESS;
    }
}
