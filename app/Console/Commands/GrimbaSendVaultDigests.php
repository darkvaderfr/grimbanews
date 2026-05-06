<?php

namespace App\Console\Commands;

use App\Mail\GrimbaVaultDigestMail;
use App\Support\GrimbaVault;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class GrimbaSendVaultDigests extends Command
{
    protected $signature = 'grimba:vault-digests
        {--member= : Send only one member id.}
        {--force : Send even if a digest was already sent this week.}
        {--dry-run : List eligible recipients without sending email.}';

    protected $description = 'Send weekly saved-article vault digests to opted-in members.';

    public function handle(): int
    {
        if (! $this->ready()) {
            $this->error('Member vault digest columns are missing. Run migrations before sending digests.');

            return self::FAILURE;
        }

        $weekStart = CarbonImmutable::now()->startOfWeek()->toDateTimeString();
        $onlyMember = (int) $this->option('member');
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        $query = DB::table('members')
            ->where('weekly_vault_digest', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereNotNull('vault_digest_post_ids')
            ->where('vault_digest_post_ids', '!=', '')
            ->orderBy('id');

        if (! $force) {
            $query->where(function ($where) use ($weekStart): void {
                $where->whereNull('vault_digest_sent_at')
                    ->orWhere('vault_digest_sent_at', '<', $weekStart);
            });
        }

        if ($onlyMember > 0) {
            $query->where('id', $onlyMember);
        }

        $sent = 0;
        $skipped = 0;

        foreach ($query->get() as $member) {
            $ids = GrimbaVault::memberDigestIds($member);
            $posts = GrimbaVault::resolvePosts($ids);

            if ($posts->isEmpty()) {
                $skipped++;
                $this->line(sprintf('Skipped member #%d: no published saved articles.', (int) $member->id));
                continue;
            }

            if ($dryRun) {
                $this->line(sprintf(
                    'Would send member #%d <%s> %d article(s).',
                    (int) $member->id,
                    (string) $member->email,
                    $posts->count()
                ));
                $sent++;
                continue;
            }

            Mail::to((string) $member->email, trim((string) (($member->first_name ?? '') . ' ' . ($member->last_name ?? ''))))
                ->send(new GrimbaVaultDigestMail($member, $posts));

            DB::table('members')
                ->where('id', (int) $member->id)
                ->update([
                    'vault_digest_sent_at' => now(),
                    'updated_at' => now(),
                ]);

            $sent++;
        }

        $this->info(sprintf(
            '%s %d vault digest(s); skipped %d.',
            $dryRun ? 'Matched' : 'Sent',
            $sent,
            $skipped
        ));

        return self::SUCCESS;
    }

    private function ready(): bool
    {
        return Schema::hasTable('members')
            && Schema::hasColumn('members', 'weekly_vault_digest')
            && Schema::hasColumn('members', 'vault_digest_post_ids')
            && Schema::hasColumn('members', 'vault_digest_sent_at');
    }
}
