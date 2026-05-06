<?php

namespace App\Console\Commands;

use App\Mail\GrimbaSavedSearchDigestMail;
use App\Support\GrimbaSavedSearches;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class GrimbaSendSavedSearchDigests extends Command
{
    protected $signature = 'grimba:saved-search-digests
        {--member= : Send only one member id.}
        {--force : Ignore weekly sent markers and include all matching articles.}
        {--dry-run : List eligible recipients without sending email.}';

    protected $description = 'Send weekly member saved-search alerts for newly matching articles.';

    public function handle(): int
    {
        if (! $this->ready()) {
            $this->error('Saved search tables are missing. Run migrations before sending digests.');

            return self::FAILURE;
        }

        $weekStart = CarbonImmutable::now()->startOfWeek()->toDateTimeString();
        $onlyMember = (int) $this->option('member');
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        $query = DB::table('saved_searches')
            ->join('members', 'members.id', '=', 'saved_searches.member_id')
            ->where('saved_searches.active', true)
            ->whereNotNull('members.email')
            ->where('members.email', '!=', '')
            ->orderBy('saved_searches.member_id')
            ->orderBy('saved_searches.id');

        if (! $force) {
            $query->where(function ($where) use ($weekStart): void {
                $where->whereNull('saved_searches.last_sent_at')
                    ->orWhere('saved_searches.last_sent_at', '<', $weekStart);
            });
        }

        if ($onlyMember > 0) {
            $query->where('saved_searches.member_id', $onlyMember);
        }

        $rows = $query->get([
            'saved_searches.*',
            'members.email as member_email',
            'members.first_name as member_first_name',
            'members.last_name as member_last_name',
        ]);

        $sent = 0;
        $skipped = 0;

        foreach ($rows->groupBy('member_id') as $memberRows) {
            $first = $memberRows->first();
            $member = (object) [
                'id' => (int) $first->member_id,
                'email' => (string) $first->member_email,
                'first_name' => (string) ($first->member_first_name ?? ''),
                'last_name' => (string) ($first->member_last_name ?? ''),
            ];

            $digests = collect();
            $sentSearchIds = [];

            foreach ($memberRows as $search) {
                $since = null;
                if (! $force) {
                    $since = $search->last_sent_at
                        ? CarbonImmutable::parse($search->last_sent_at)
                        : CarbonImmutable::parse($search->created_at);
                }

                $posts = GrimbaSavedSearches::matchingPosts($search, $since, GrimbaSavedSearches::DIGEST_POST_LIMIT);

                if (! $dryRun) {
                    DB::table('saved_searches')
                        ->where('id', (int) $search->id)
                        ->update([
                            'last_checked_at' => now(),
                            'updated_at' => now(),
                        ]);
                }

                if ($posts->isEmpty()) {
                    continue;
                }

                $sentSearchIds[] = (int) $search->id;
                $digests->push([
                    'search' => $search,
                    'label' => GrimbaSavedSearches::label($search),
                    'url' => GrimbaSavedSearches::searchUrl($search),
                    'posts' => $posts,
                ]);
            }

            if ($digests->isEmpty()) {
                $skipped++;
                $this->line(sprintf('Skipped member #%d: no new saved-search matches.', (int) $member->id));
                continue;
            }

            if ($dryRun) {
                $articleCount = $digests->sum(fn (array $digest): int => $digest['posts']->count());
                $this->line(sprintf(
                    'Would send member #%d <%s> %d search(es), %d article(s).',
                    (int) $member->id,
                    (string) $member->email,
                    $digests->count(),
                    $articleCount
                ));
                $sent++;
                continue;
            }

            Mail::to((string) $member->email, trim($member->first_name . ' ' . $member->last_name))
                ->send(new GrimbaSavedSearchDigestMail($member, $digests));

            DB::table('saved_searches')
                ->whereIn('id', $sentSearchIds)
                ->update([
                    'last_sent_at' => now(),
                    'last_checked_at' => now(),
                    'updated_at' => now(),
                ]);

            $sent++;
        }

        $this->info(sprintf(
            '%s %d saved-search digest(s); skipped %d.',
            $dryRun ? 'Matched' : 'Sent',
            $sent,
            $skipped
        ));

        return self::SUCCESS;
    }

    private function ready(): bool
    {
        return Schema::hasTable('members') && GrimbaSavedSearches::ready();
    }
}
