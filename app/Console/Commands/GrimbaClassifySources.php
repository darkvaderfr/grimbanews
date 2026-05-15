<?php

namespace App\Console\Commands;

use App\Support\GrimbaSourceClassifier;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GrimbaClassifySources extends Command
{
    protected $signature = 'grimba:classify-sources
        {--apply : Persist high-confidence source classifications. Defaults to dry-run.}
        {--limit= : Maximum source rows to inspect.}
        {--min-confidence=80 : Minimum confidence required to update a row.}
        {--all : Inspect inactive sources too. Defaults to active sources only.}
        {--include-classified : Re-evaluate sources that already have complete metadata.}
        {--overwrite : Replace existing non-empty classifier fields. Manual-lock notes are never overwritten.}
        {--sync-posts : Copy updated source metadata to posts that still have missing/unknown source metadata.}';

    protected $description = 'Audit and safely classify news source bias, origin, ownership, factuality, and language.';

    public function handle(): int
    {
        if (! Schema::hasTable('news_sources')) {
            $this->error('news_sources table is missing.');

            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');
        $overwrite = (bool) $this->option('overwrite');
        $syncPosts = (bool) $this->option('sync-posts');
        $minConfidence = max(0, min(100, (int) $this->option('min-confidence')));
        $limit = $this->option('limit') !== null ? max(1, (int) $this->option('limit')) : null;
        $activeIds = (bool) $this->option('all') ? [] : $this->activeSourceIds();

        $query = $this->sourceQuery($activeIds)
            ->orderByRaw("CASE WHEN bias_rating IS NULL OR bias_rating = '' OR bias_rating = 'unknown' THEN 0 ELSE 1 END")
            ->orderBy('name');

        if (! (bool) $this->option('include-classified')) {
            $query->where(fn (Builder $q) => $this->needsClassificationScope($q));
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        $candidates = [];
        $applied = 0;
        $syncedPosts = 0;
        $skipped = 0;

        foreach ($query->get($this->sourceColumns()) as $source) {
            if (GrimbaSourceClassifier::hasManualLock($source->notes ?? null)) {
                $skipped++;
                continue;
            }

            $evidenceUrl = $source->website ?: $this->sourceEvidenceUrl((int) $source->id) ?: null;
            $classification = GrimbaSourceClassifier::classify(
                (string) $source->name,
                $evidenceUrl,
                $source->api_id ?? null
            );

            if ($classification === null || (int) $classification['confidence'] < $minConfidence) {
                $skipped++;
                continue;
            }

            $updates = $this->updatesFor($source, $classification, $overwrite);
            if ($updates === []) {
                $skipped++;
                continue;
            }

            $candidates[] = [
                'id' => (int) $source->id,
                'name' => (string) $source->name,
                'bias' => $updates['bias_rating'] ?? $source->bias_rating ?? null,
                'country' => $updates['country'] ?? $source->country ?? null,
                'owner' => $updates['owner_name'] ?? $source->owner_name ?? null,
                'credibility' => $updates['credibility_score'] ?? $source->credibility_score ?? null,
                'confidence' => (int) $classification['confidence'],
                'method' => (string) $classification['method'],
                'basis' => (string) $classification['basis'],
            ];

            if (! $apply) {
                continue;
            }

            DB::table('news_sources')
                ->where('id', (int) $source->id)
                ->update([
                    ...$updates,
                    'updated_at' => now(),
                ]);
            $applied++;

            if ($syncPosts) {
                $syncedPosts += $this->syncPosts((int) $source->id, $updates, $overwrite);
            }
        }

        $this->line(sprintf(
            '%s %d source classification update(s); %d skipped.',
            $apply ? 'Applied' : 'Dry-run found',
            $apply ? $applied : count($candidates),
            $skipped
        ));

        if ($candidates !== []) {
            $this->table(
                ['id', 'source', 'bias', 'country', 'owner', 'cred', 'conf', 'method', 'basis'],
                array_map(
                    fn (array $row) => [
                        $row['id'],
                        $row['name'],
                        $row['bias'] ?: '-',
                        $row['country'] ?: '-',
                        $row['owner'] ? mb_strimwidth((string) $row['owner'], 0, 38, '...') : '-',
                        $row['credibility'] ?: '-',
                        $row['confidence'],
                        $row['method'],
                        mb_strimwidth((string) $row['basis'], 0, 32, '...'),
                    ],
                    array_slice($candidates, 0, 25)
                )
            );
        }

        if (! $apply) {
            $this->comment('Dry-run only. Re-run with --apply to persist these source classifications.');
        } elseif ($syncPosts) {
            $this->line(sprintf('Synced %d post metadata field group(s).', $syncedPosts));
        }

        $this->line($this->coverageLine());

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
    private function sourceQuery(array $activeIds): Builder
    {
        $query = DB::table('news_sources');

        return $activeIds !== [] ? $query->whereIn('id', $activeIds) : $query;
    }

    private function needsClassificationScope(Builder $query): void
    {
        $query
            ->whereNull('credibility_score')
            ->orWhereNull('ownership_type')
            ->orWhere('ownership_type', '')
            ->orWhereNull('country')
            ->orWhere('country', '')
            ->orWhereNull('language')
            ->orWhere('language', '')
            ->orWhereNull('bias_rating')
            ->orWhere('bias_rating', '')
            ->orWhere('bias_rating', 'unknown');

        if (Schema::hasColumn('news_sources', 'owner_name')) {
            $query->orWhereNull('owner_name')->orWhere('owner_name', '');
        }
    }

    /**
     * @return array<int, string>
     */
    private function sourceColumns(): array
    {
        return collect([
            'id',
            'name',
            'website',
            'bias_rating',
            'ownership_type',
            'owner_name',
            'credibility_score',
            'country',
            'language',
            'notes',
            'api_id',
        ])->filter(fn (string $column): bool => Schema::hasColumn('news_sources', $column))->values()->all();
    }

    /**
     * @param array<string, mixed> $classification
     * @return array<string, mixed>
     */
    private function updatesFor(object $source, array $classification, bool $overwrite): array
    {
        $updates = [];

        foreach (['bias_rating', 'ownership_type', 'owner_name', 'credibility_score', 'country', 'language', 'bias_score'] as $field) {
            if (! array_key_exists($field, $classification) || ! Schema::hasColumn('news_sources', $field)) {
                continue;
            }

            if ($classification[$field] === null) {
                continue;
            }

            if ($overwrite || $this->fieldIsMissing($field, $source->{$field} ?? null)) {
                $updates[$field] = $classification[$field];
            }
        }

        if ($updates !== []) {
            if (Schema::hasColumn('news_sources', 'classification_confidence')) {
                $updates['classification_confidence'] = (int) $classification['confidence'];
            }
            if (Schema::hasColumn('news_sources', 'classification_method')) {
                $updates['classification_method'] = (string) $classification['method'];
            }
            if (Schema::hasColumn('news_sources', 'classified_at')) {
                $updates['classified_at'] = now();
            }
        }

        return $updates;
    }

    private function fieldIsMissing(string $field, mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if ($field === 'bias_rating') {
            return ! in_array((string) $value, ['left', 'center', 'right'], true);
        }

        if ($field === 'ownership_type') {
            return in_array((string) $value, ['unknown', 'other'], true);
        }

        return false;
    }

    /**
     * @param array<string, mixed> $updates
     */
    private function syncPosts(int $sourceId, array $updates, bool $overwrite): int
    {
        $postUpdates = array_intersect_key($updates, array_flip(['bias_rating', 'credibility_score', 'ownership_type']));
        if ($postUpdates === []) {
            return 0;
        }

        $query = DB::table('posts')->where('source_id', $sourceId);
        if (! $overwrite) {
            $query->where(function (Builder $q) use ($postUpdates): void {
                foreach ($postUpdates as $field => $_value) {
                    $q->orWhere(fn (Builder $fieldQuery) => $this->missingPostFieldScope($fieldQuery, (string) $field));
                }
            });
        }

        return $query->update([
            ...$postUpdates,
            'updated_at' => now(),
        ]);
    }

    private function missingPostFieldScope(Builder $query, string $field): void
    {
        $query->whereNull($field);

        if ($field === 'credibility_score') {
            return;
        }

        $query->orWhere($field, '')->orWhere($field, 'unknown');
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

        if (Schema::hasTable('newsapi_items')) {
            $articleUrl = DB::table('newsapi_items')
                ->where('source_id', $sourceId)
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->value('article_url');

            if (is_string($articleUrl) && trim($articleUrl) !== '') {
                return $articleUrl;
            }
        }

        return null;
    }

    private function coverageLine(): string
    {
        $total = DB::table('news_sources')
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
            ->count();

        $classified = DB::table('news_sources')
            ->where(function (Builder $active): void {
                $active->whereExists(function (Builder $query): void {
                    $query->selectRaw('1')
                        ->from('posts')
                        ->whereColumn('posts.source_id', 'news_sources.id');
                })->orWhereExists(function (Builder $query): void {
                    $query->selectRaw('1')
                        ->from('rss_feeds')
                        ->whereColumn('rss_feeds.source_id', 'news_sources.id')
                        ->where('rss_feeds.is_active', true);
                });
            })
            ->whereIn('bias_rating', ['left', 'center', 'right'])
            ->whereNotNull('credibility_score')
            ->whereNotNull('country')
            ->count();

        $pct = $total > 0 ? (int) round($classified * 100 / $total) : 100;

        return sprintf('Active source coverage: %d/%d classified (%d%%).', $classified, $total, $pct);
    }
}
