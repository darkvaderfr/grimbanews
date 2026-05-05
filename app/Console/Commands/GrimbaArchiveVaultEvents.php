<?php

namespace App\Console\Commands;

use App\Support\GrimbaVaultEvents;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class GrimbaArchiveVaultEvents extends Command
{
    protected $signature = 'grimba:archive-vault-events
        {--month= : Archive month in YYYY-MM format. Defaults to the current month.}
        {--path= : Override the CSV path. Defaults to storage/exports/vault_events_YYYY-MM.csv.}';

    protected $description = 'Archive privacy-preserving vault save/unsave events to a monthly CSV.';

    public function handle(): int
    {
        if (! Schema::hasTable(GrimbaVaultEvents::TABLE)) {
            $this->error('vault_events table is missing. Run migrations before archiving vault analytics.');

            return self::FAILURE;
        }

        $month = (string) ($this->option('month') ?: now()->format('Y-m'));
        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->error('The --month option must use YYYY-MM format.');

            return self::FAILURE;
        }

        $start = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $month . '-01 00:00:00');
        if (! $start) {
            $this->error('The --month option is not a valid calendar month.');

            return self::FAILURE;
        }

        $end = $start->addMonth();
        $path = (string) ($this->option('path') ?: storage_path('exports/vault_events_' . $month . '.csv'));

        File::ensureDirectoryExists(dirname($path));

        $rows = DB::table(GrimbaVaultEvents::TABLE)
            ->where('ts', '>=', $start->toDateTimeString())
            ->where('ts', '<', $end->toDateTimeString())
            ->orderBy('ts')
            ->orderBy('id')
            ->get(['event', 'post_id', 'ts', 'ip_hash']);

        $handle = fopen($path, 'w');
        if (! $handle) {
            $this->error('Unable to open archive path for writing: ' . $path);

            return self::FAILURE;
        }

        fputcsv($handle, ['event', 'post_id', 'ts', 'ip_hash']);
        foreach ($rows as $row) {
            fputcsv($handle, [
                (string) $row->event,
                (int) $row->post_id,
                (string) $row->ts,
                (string) $row->ip_hash,
            ]);
        }
        fclose($handle);

        $this->info(sprintf(
            'Archived %d vault event(s) for %s to %s.',
            $rows->count(),
            $month,
            $path
        ));

        return self::SUCCESS;
    }
}
