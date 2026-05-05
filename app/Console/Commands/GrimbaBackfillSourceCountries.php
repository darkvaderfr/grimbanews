<?php

namespace App\Console\Commands;

use App\Support\GrimbaSourceCountryBackfill;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class GrimbaBackfillSourceCountries extends Command
{
    protected $signature = 'grimba:backfill-source-countries
        {--apply : Persist inferred countries. Defaults to dry-run audit.}
        {--limit= : Maximum missing source rows to inspect.}
        {--min-confidence=80 : Minimum confidence required to update a row.}';

    protected $description = 'Audit and safely backfill missing news_sources.country values for the 4-region edition scope (K8).';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $limit = $this->option('limit') !== null ? max(1, (int) $this->option('limit')) : null;
        $minConfidence = max(0, min(100, (int) $this->option('min-confidence')));
        $activeIds = $this->activeSourceIds();

        $total = $this->scopedSources($activeIds)->count();
        $taggedBefore = $this->taggedSources($activeIds)->count();
        $missingQuery = $this->missingSources($activeIds)->orderBy('name');

        if ($limit !== null) {
            $missingQuery->limit($limit);
        }

        $candidates = [];
        $skipped = [];

        foreach ($missingQuery->get(['id', 'name', 'website', 'api_id']) as $source) {
            $websiteEvidence = $source->website
                ?: $this->sourceEvidenceUrl((int) $source->id)
                ?: $source->name;
            $inferred = GrimbaSourceCountryBackfill::infer($source->name, $websiteEvidence, $source->api_id ?? null);

            if ($inferred && $inferred['confidence'] >= $minConfidence) {
                $candidates[] = [
                    'id' => (int) $source->id,
                    'name' => (string) $source->name,
                    ...$inferred,
                ];
            } else {
                $skipped[] = [
                    'id' => (int) $source->id,
                    'name' => (string) $source->name,
                    'reason' => $inferred ? 'confidence below threshold' : 'no safe inference',
                ];
            }
        }

        if ($apply) {
            foreach ($candidates as $candidate) {
                DB::table('news_sources')
                    ->where('id', $candidate['id'])
                    ->where(function (Builder $query): void {
                        $query->whereNull('country')->orWhere('country', '');
                    })
                    ->update([
                        'country' => $candidate['country'],
                        'updated_at' => now(),
                    ]);
            }
        }

        $taggedAfter = $apply
            ? $this->taggedSources($activeIds)->count()
            : min($total, $taggedBefore + count($candidates));

        $this->line(sprintf(
            'K8 source country coverage: %d/%d active sources tagged (%d%%).',
            $taggedBefore,
            $total,
            $this->pct($taggedBefore, $total)
        ));

        $this->line(sprintf(
            '%s %d inferred update(s); projected coverage %d/%d (%d%%).',
            $apply ? 'Applied' : 'Dry-run found',
            count($candidates),
            $taggedAfter,
            $total,
            $this->pct($taggedAfter, $total)
        ));

        if ($candidates !== []) {
            $this->table(
                ['id', 'source', 'country', 'confidence', 'method', 'basis'],
                array_map(
                    fn (array $row) => [
                        $row['id'],
                        $row['name'],
                        $row['country'],
                        $row['confidence'],
                        $row['method'],
                        $row['basis'],
                    ],
                    array_slice($candidates, 0, 20)
                )
            );
        }

        if (! $apply) {
            $this->comment('Dry-run only. Re-run with --apply to persist these inferred country tags.');
        }

        if ($skipped !== []) {
            $this->line(sprintf('%d missing source(s) still need editor review.', count($skipped)));
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, int>
     */
    private function activeSourceIds(): array
    {
        return DB::table('news_sources')
            ->whereExists(function (Builder $query): void {
                $query->selectRaw('1')
                    ->from('posts')
                    ->whereColumn('posts.source_id', 'news_sources.id');
            })
            ->orWhereExists(function (Builder $query): void {
                $query->selectRaw('1')
                    ->from('rss_feeds')
                    ->whereColumn('rss_feeds.source_id', 'news_sources.id')
                    ->where('rss_feeds.is_active', true);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @param array<int, int> $activeIds
     */
    private function scopedSources(array $activeIds): Builder
    {
        $query = DB::table('news_sources');

        return $activeIds !== [] ? $query->whereIn('id', $activeIds) : $query;
    }

    /**
     * @param array<int, int> $activeIds
     */
    private function taggedSources(array $activeIds): Builder
    {
        return $this->scopedSources($activeIds)
            ->whereNotNull('country')
            ->where('country', '!=', '');
    }

    /**
     * @param array<int, int> $activeIds
     */
    private function missingSources(array $activeIds): Builder
    {
        return $this->scopedSources($activeIds)
            ->where(function (Builder $query): void {
                $query->whereNull('country')->orWhere('country', '');
            });
    }

    private function sourceEvidenceUrl(int $sourceId): ?string
    {
        $feedUrl = DB::table('rss_feeds')
            ->where('source_id', $sourceId)
            ->where('is_active', true)
            ->orderByDesc('last_success_at')
            ->orderByDesc('id')
            ->value('url');

        if (is_string($feedUrl) && trim($feedUrl) !== '') {
            return $feedUrl;
        }

        $articleUrl = DB::table('newsapi_items')
            ->where('source_id', $sourceId)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->value('article_url');

        return is_string($articleUrl) && trim($articleUrl) !== '' ? $articleUrl : null;
    }

    private function pct(int $count, int $total): int
    {
        return $total > 0 ? (int) round($count * 100 / $total) : 100;
    }
}
