<?php

namespace App\Console\Commands;

use App\Services\GrimbaNewsApiFetcher;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GrimbaNewsApiReadiness extends Command
{
    protected $signature = 'grimba:newsapi-readiness
        {--recent-hours=0 : require a successful NewsAPI run within this many hours; 0 disables the recency gate}
        {--allow-inactive : report inactive/missing-key state without failing}';

    protected $description = 'Report whether NewsAPI is configured, active, budgeted, and recently successful.';

    public function handle(GrimbaNewsApiFetcher $fetcher): int
    {
        $configured = $fetcher->isConfigured();
        $active = (bool) setting('grimba_newsapi_active', $configured);
        $countries = $fetcher->countries();
        $categories = $fetcher->categories();
        $queries = $fetcher->everythingQueries();
        $planned = $fetcher->plannedCallCount();
        $dailyBudget = $fetcher->dailyRequestBudget();
        $callsToday = $fetcher->callsToday();
        $remaining = max(0, $dailyBudget - $callsToday);
        $maxCalls = $fetcher->maxCallsPerRun();
        $recentHours = max(0, (int) $this->option('recent-hours'));
        $allowInactive = (bool) $this->option('allow-inactive');
        $risks = [];

        $this->line('NewsAPI readiness');
        $this->line(sprintf('  State          : %s', $active ? 'active' : 'inactive'));
        $this->line(sprintf('  Key            : %s', $configured ? 'configured' : 'missing'));
        $this->line(sprintf(
            '  Planned sweep  : %d call(s) (%d countr%s × %d categor%s + %d everything quer%s)',
            $planned,
            count($countries),
            count($countries) === 1 ? 'y' : 'ies',
            count($categories),
            count($categories) === 1 ? 'y' : 'ies',
            count($queries),
            count($queries) === 1 ? 'y' : 'ies'
        ));
        $this->line(sprintf('  Countries      : %s', implode(', ', $countries)));
        $this->line(sprintf('  Categories     : %s', implode(', ', $categories)));
        $this->line(sprintf(
            '  Budget today   : %d/%d used, %d remaining, max/run %d',
            $callsToday,
            $dailyBudget,
            $remaining,
            $maxCalls
        ));

        if (! Schema::hasTable('grimba_newsapi_runs')) {
            $risks[] = 'grimba_newsapi_runs table is missing';
            $this->line('  Latest run     : run ledger unavailable');
        } else {
            $latest = DB::table('grimba_newsapi_runs')
                ->orderByDesc('started_at')
                ->orderByDesc('id')
                ->first();
            $latestSuccess = DB::table('grimba_newsapi_runs')
                ->where('status', 'ok')
                ->whereNotNull('finished_at')
                ->orderByDesc('finished_at')
                ->orderByDesc('id')
                ->first();

            $this->line(sprintf(
                '  Latest run     : %s',
                $latest
                    ? sprintf('%s at %s', (string) $latest->status, (string) ($latest->started_at ?: $latest->created_at))
                    : 'never'
            ));
            $this->line(sprintf(
                '  Latest success : %s',
                $latestSuccess
                    ? sprintf(
                        '%s (%d returned, %d ingested)',
                        (string) $latestSuccess->finished_at,
                        (int) $latestSuccess->returned_articles,
                        (int) $latestSuccess->ingested_articles
                    )
                    : 'never'
            ));

            if ($recentHours > 0) {
                $cutoff = now()->subHours($recentHours);
                $latestSuccessAt = $latestSuccess?->finished_at
                    ? Carbon::parse((string) $latestSuccess->finished_at)
                    : null;

                if (! $latestSuccessAt || $latestSuccessAt->lt($cutoff)) {
                    $risks[] = sprintf('no successful NewsAPI run in the last %d hour(s)', $recentHours);
                }
            }
        }

        if (! $configured) {
            $risks[] = 'NewsAPI key missing; configure NEWSAPI_KEY or /admin/grimba/newsapi';
        }

        if (! $active) {
            $risks[] = 'NewsAPI is inactive; enable grimba_newsapi_active after the key is configured';
        }

        if ($active && $remaining <= 0) {
            $risks[] = 'NewsAPI daily request budget is exhausted';
        }

        if ($active && $planned > $maxCalls) {
            $risks[] = sprintf('planned sweep exceeds max calls per run: %d/%d', $planned, $maxCalls);
        }

        if ($risks === [] || $allowInactive) {
            foreach ($risks as $risk) {
                $this->warn('  Attention     : ' . $risk);
            }
            $this->info('NewsAPI readiness ' . ($risks === [] ? 'passed.' : 'observed with --allow-inactive.'));

            return self::SUCCESS;
        }

        foreach ($risks as $risk) {
            $this->error('  Risk          : ' . $risk);
        }
        $this->error('NewsAPI readiness failed.');

        return self::FAILURE;
    }
}
