<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Vader 2026-05-18 — widen content depth for thin editorial categories
 * (Monde, Santé, Sciences, Sports, Climat, Société) before launch.
 *
 * Two modes of insertion:
 *   1. **New publishers** — full `news_sources` row + RSS feed
 *      (Mediapart, Liberation, BBC News, Nature, Science.org,
 *       InsideClimateNews, Reporterre, Carbon Brief).
 *   2. **New feeds on existing publishers** — adds a subcategory feed
 *      to a publisher already in the DB (Le Monde Santé/Sciences,
 *      France 24 /fr/rss general, NPR 1004 World).
 *
 * Idempotent on both axes: skips when host is already a `news_sources`
 * row AND skips when the exact RSS URL is already in `rss_feeds`.
 *
 * Every candidate URL in the list below was verified with a live HTTP
 * 200 probe before this command was written; the seeder is safe to
 * re-run.
 */
class GrimbaSeedThinCategorySources extends Command
{
    protected $signature = 'grimba:seed-thin-category-sources
        {--dry-run : show what would be inserted without writing}';

    protected $description = 'Seed verified RSS sources + feeds for thin editorial categories (pre-launch content depth).';

    /**
     * @return array<int, array{name:string, website:string, language:string, country:string, bias:string, ownership:string, credibility:int, feed:string, notes:string}>
     */
    private function newPublishers(): array
    {
        return [
            [
                'name' => 'BBC News',
                'website' => 'https://www.bbc.co.uk',
                'language' => 'en',
                'country' => 'GB',
                'bias' => 'center',
                'ownership' => 'government',
                'credibility' => 88,
                'feed' => 'https://feeds.bbci.co.uk/news/world/rss.xml',
                'notes' => 'BBC News world feed — broad international coverage.',
            ],
            [
                'name' => 'BBC Sport',
                'website' => 'https://www.bbc.co.uk/sport',
                'language' => 'en',
                'country' => 'GB',
                'bias' => 'center',
                'ownership' => 'government',
                'credibility' => 88,
                'feed' => 'https://feeds.bbci.co.uk/sport/rss.xml',
                'notes' => 'BBC Sport — global sports coverage.',
            ],
            [
                'name' => 'Mediapart',
                'website' => 'https://www.mediapart.fr',
                'language' => 'fr',
                'country' => 'FR',
                'bias' => 'left',
                'ownership' => 'independent',
                'credibility' => 82,
                'feed' => 'https://www.mediapart.fr/articles/feed',
                'notes' => 'Investigation journalism, French independent paper.',
            ],
            [
                'name' => 'Libération',
                'website' => 'https://www.liberation.fr',
                'language' => 'fr',
                'country' => 'FR',
                'bias' => 'left',
                'ownership' => 'private_equity',
                'credibility' => 80,
                'feed' => 'https://www.liberation.fr/arc/outboundfeeds/rss/?outputType=xml',
                'notes' => 'French daily — politics, society, culture.',
            ],
            [
                'name' => 'Nature',
                'website' => 'https://www.nature.com',
                'language' => 'en',
                'country' => 'GB',
                'bias' => 'center',
                'ownership' => 'corporation',
                'credibility' => 95,
                'feed' => 'https://feeds.nature.com/nature/rss/current',
                'notes' => 'Peer-reviewed science journal news feed.',
            ],
            [
                'name' => 'Science.org News',
                'website' => 'https://www.science.org',
                'language' => 'en',
                'country' => 'US',
                'bias' => 'center',
                'ownership' => 'nonprofit',
                'credibility' => 94,
                'feed' => 'https://www.science.org/rss/news_current.xml',
                'notes' => 'AAAS Science magazine news feed.',
            ],
            [
                'name' => 'Inside Climate News',
                'website' => 'https://insideclimatenews.org',
                'language' => 'en',
                'country' => 'US',
                'bias' => 'left',
                'ownership' => 'nonprofit',
                'credibility' => 86,
                'feed' => 'https://insideclimatenews.org/feed/',
                'notes' => 'Pulitzer-winning climate + environment newsroom.',
            ],
            [
                'name' => 'Reporterre',
                'website' => 'https://reporterre.net',
                'language' => 'fr',
                'country' => 'FR',
                'bias' => 'left',
                'ownership' => 'nonprofit',
                'credibility' => 78,
                'feed' => 'https://reporterre.net/spip.php?page=backend',
                'notes' => "Le quotidien de l'écologie. Climat, biodiversité, transitions.",
            ],
            [
                'name' => 'Carbon Brief',
                'website' => 'https://www.carbonbrief.org',
                'language' => 'en',
                'country' => 'GB',
                'bias' => 'center',
                'ownership' => 'nonprofit',
                'credibility' => 90,
                'feed' => 'https://www.carbonbrief.org/feed/',
                'notes' => 'Climate science + policy analysis.',
            ],
        ];
    }

    /**
     * Feeds to add to publishers already in `news_sources`. Each entry
     * matches via `website` host substring.
     *
     * @return array<int, array{host:string, feed:string, notes:string}>
     */
    private function newFeedsForExistingSources(): array
    {
        return [
            ['host' => 'lemonde.fr',   'feed' => 'https://www.lemonde.fr/sante/rss_full.xml',    'notes' => 'Le Monde Santé vertical (2026-05-18).'],
            ['host' => 'lemonde.fr',   'feed' => 'https://www.lemonde.fr/sciences/rss_full.xml', 'notes' => 'Le Monde Sciences vertical (2026-05-18).'],
            ['host' => 'rfi.fr',       'feed' => 'https://www.rfi.fr/fr/sports/rss',             'notes' => 'RFI Sports (2026-05-18).'],
            ['host' => 'france24.com', 'feed' => 'https://www.france24.com/fr/rss',              'notes' => 'France 24 fil général (2026-05-18).'],
            ['host' => 'npr.org',      'feed' => 'https://feeds.npr.org/1004/rss.xml',           'notes' => 'NPR World (2026-05-18).'],
        ];
    }

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $now = now();

        $publishersReport = [];
        $publishersAdded = 0;
        $publishersSkipped = 0;
        $feedsAddedToExisting = 0;

        // Pass 1 — new publishers.
        foreach ($this->newPublishers() as $p) {
            $host = parse_url($p['website'], PHP_URL_HOST);
            $existing = DB::table('news_sources')
                ->whereRaw('LOWER(website) LIKE ?', ['%' . strtolower($host) . '%'])
                ->orWhere('name', $p['name'])
                ->first();

            if ($existing) {
                $publishersReport[] = [$p['name'], 'already_exists (#' . $existing->id . ')'];
                $publishersSkipped++;
                continue;
            }

            if ($dry) {
                $publishersReport[] = [$p['name'], 'WOULD_INSERT'];
                continue;
            }

            $sourceId = DB::table('news_sources')->insertGetId([
                'name' => $p['name'],
                'website' => $p['website'],
                'language' => $p['language'],
                'country' => $p['country'],
                'bias_rating' => $p['bias'],
                'ownership_type' => $p['ownership'],
                'credibility_score' => $p['credibility'],
                'notes' => $p['notes'],
                'slug' => Str::slug($p['name']),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('rss_feeds')->insert([
                'source_id' => $sourceId,
                'url' => $p['feed'],
                'feed_format' => 'rss',
                'is_active' => true,
                'notes' => 'Seeded by grimba:seed-thin-category-sources 2026-05-18',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $publishersReport[] = [$p['name'], 'INSERTED (#' . $sourceId . ')'];
            $publishersAdded++;
        }

        // Pass 2 — new feeds on existing publishers.
        $feedReport = [];
        foreach ($this->newFeedsForExistingSources() as $f) {
            $existingFeed = DB::table('rss_feeds')->where('url', $f['feed'])->first();
            if ($existingFeed) {
                $feedReport[] = [$f['feed'], 'already_exists (rss#' . $existingFeed->id . ')'];
                continue;
            }
            $source = DB::table('news_sources')
                ->whereRaw('LOWER(website) LIKE ?', ['%' . strtolower($f['host']) . '%'])
                ->orderBy('id')
                ->first();

            if (! $source) {
                $feedReport[] = [$f['feed'], 'no_source_match for host ' . $f['host']];
                continue;
            }
            if ($dry) {
                $feedReport[] = [$f['feed'], 'WOULD_ATTACH to #' . $source->id];
                continue;
            }
            DB::table('rss_feeds')->insert([
                'source_id' => $source->id,
                'url' => $f['feed'],
                'feed_format' => 'rss',
                'is_active' => true,
                'notes' => $f['notes'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $feedReport[] = [$f['feed'], 'ATTACHED to #' . $source->id];
            $feedsAddedToExisting++;
        }

        $this->info('Publishers:');
        $this->table(['Publisher', 'Status'], $publishersReport);
        $this->info('Subcategory feeds for existing publishers:');
        $this->table(['Feed URL', 'Status'], $feedReport);

        $this->info(sprintf(
            '%s — %d publishers added, %d skipped, %d new feeds attached to existing publishers.',
            $dry ? 'DRY RUN' : 'Done',
            $publishersAdded,
            $publishersSkipped,
            $feedsAddedToExisting
        ));
        if (! $dry && ($publishersAdded > 0 || $feedsAddedToExisting > 0)) {
            $this->line('Next: `php artisan grimba:poll-feeds` to fetch first batch + `grimba:classify-categories` to assign category pivots.');
        }

        return self::SUCCESS;
    }
}
