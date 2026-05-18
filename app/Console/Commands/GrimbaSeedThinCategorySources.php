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
            [
                // GISTI — added 2026-05-18 as an immigration-focused
                // francophone publisher to widen the Immigration
                // category content depth.
                'name' => 'GISTI',
                'website' => 'https://www.gisti.org',
                'language' => 'fr',
                'country' => 'FR',
                'bias' => 'left',
                'ownership' => 'nonprofit',
                'credibility' => 80,
                'feed' => 'https://www.gisti.org/spip.php?page=backend',
                'notes' => "Groupe d'information et de soutien des immigré·e·s. Couverture juridique + plaidoyer.",
            ],
            [
                // France TV Info — public-service French broadcaster.
                // The /societe/immigration.rss subfeed is the
                // launch-blocker-relevant one; the seeder attaches it
                // here as a new source (matching by host falls through
                // when the host isn't yet in news_sources).
                'name' => 'France TV Info',
                'website' => 'https://www.francetvinfo.fr',
                'language' => 'fr',
                'country' => 'FR',
                'bias' => 'center',
                'ownership' => 'government',
                'credibility' => 86,
                'feed' => 'https://www.francetvinfo.fr/societe/immigration.rss',
                'notes' => 'Service public audiovisuel français — vertical Immigration.',
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
            ['host' => 'lemonde.fr',   'feed' => 'https://www.lemonde.fr/afrique/rss_full.xml',  'notes' => 'Le Monde Afrique vertical (2026-05-18).'],
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

        // Zen audit fix 2026-05-18: substring LIKE was silently
        // colliding BBC News + BBC Sport (same host, different paths)
        // AND missing no-www DB rows. Switch to **name-first** dedup
        // (same publisher = same name) with normalized-host as the
        // fallback for legacy rows whose name has drifted. Two
        // products sharing a host but having distinct names (BBC News,
        // BBC Sport) now correctly seed as separate sources.
        $nameIndex = DB::table('news_sources')->pluck('id', 'name')->toArray();
        $hostIndex = $this->buildHostIndex();

        // Pass 1 — new publishers.
        foreach ($this->newPublishers() as $p) {
            // Dedup priority: exact name match first (handles renames /
            // case where multiple publishers share a host). When name
            // doesn't match, fall back to normalized-host match (handles
            // publishers added under a slightly different label —
            // e.g. "Franceinfo.fr" already in DB; seeder asks for
            // "France TV Info" with the same host).
            $host = self::normaliseHost($p['website']);
            $existingByName = $nameIndex[$p['name']] ?? null;
            $existingByHost = $hostIndex[$host] ?? null;

            if ($existingByName) {
                $publishersReport[] = [$p['name'], 'already_exists (#' . $existingByName . ')'];
                $publishersSkipped++;
                continue;
            }

            // Same host as an existing publisher but different name —
            // attach this publisher's feed as a sub-feed of the
            // existing source rather than create a duplicate row. This
            // is the right shape for "France TV Info" + "Franceinfo.fr"
            // (one publisher, multiple editorial verticals).
            if ($existingByHost) {
                $hasFeed = DB::table('rss_feeds')->where('url', $p['feed'])->exists();
                if ($hasFeed) {
                    $publishersReport[] = [$p['name'], 'feed_already_attached_to (#' . $existingByHost . ')'];
                    $publishersSkipped++;
                    continue;
                }
                if ($dry) {
                    $publishersReport[] = [$p['name'], 'WOULD_ATTACH feed to existing #' . $existingByHost];
                    continue;
                }
                DB::table('rss_feeds')->insert([
                    'source_id' => $existingByHost,
                    'url' => $p['feed'],
                    'feed_format' => 'rss',
                    'is_active' => true,
                    'notes' => $p['notes'] . ' (Pass-1 feed-attach 2026-05-18)',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $publishersReport[] = [$p['name'], 'FEED_ATTACHED to #' . $existingByHost];
                $feedsAddedToExisting++;
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
            $sourceId = $hostIndex[self::normaliseHost('https://' . $f['host'])] ?? null;

            if (! $sourceId) {
                $feedReport[] = [$f['feed'], 'no_source_match for host ' . $f['host']];
                continue;
            }
            if ($dry) {
                $feedReport[] = [$f['feed'], 'WOULD_ATTACH to #' . $sourceId];
                continue;
            }
            DB::table('rss_feeds')->insert([
                'source_id' => $sourceId,
                'url' => $f['feed'],
                'feed_format' => 'rss',
                'is_active' => true,
                'notes' => $f['notes'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $feedReport[] = [$f['feed'], 'ATTACHED to #' . $sourceId];
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

    /**
     * Build a one-shot host→source_id map from `news_sources.website`,
     * keyed by normalised host (lowercased, stripped of `www.`, port,
     * and path). Used as the idempotency lookup for both passes.
     *
     * @return array<string, int>
     */
    private function buildHostIndex(): array
    {
        $index = [];
        foreach (DB::table('news_sources')->select('id', 'website')->get() as $row) {
            $host = self::normaliseHost((string) $row->website);
            if ($host !== '' && ! isset($index[$host])) {
                $index[$host] = (int) $row->id;
            }
        }
        return $index;
    }

    /**
     * Normalise any URL-like string to a comparable host token. Strips
     * scheme, leading `www.`, port, path, query, lowercases. Returns
     * '' when the input has no usable host (caller treats as no-match).
     */
    public static function normaliseHost(string $url): string
    {
        $url = trim($url);
        if ($url === '') return '';
        // Add scheme if missing so parse_url returns host.
        if (! preg_match('#^[a-z]+://#i', $url)) {
            $url = 'https://' . $url;
        }
        $host = (string) parse_url($url, PHP_URL_HOST);
        $host = strtolower($host);
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }
        return $host;
    }
}
