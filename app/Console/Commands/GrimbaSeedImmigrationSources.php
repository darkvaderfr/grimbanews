<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Vader 2026-05-17 — seed immigration-focused publishers + RSS feeds.
 *
 * The base 676-source pool has zero immigration-themed publishers
 * (Wave EE's classifier fix moved 46 keyword-matching posts into the
 * Immigration category, but that's still under the 500/category launch
 * target). This command adds 6 well-known immigration-focused
 * publishers + their RSS feeds so the RSS poller starts capturing
 * dedicated coverage on the next tick.
 *
 * Idempotent: skips sources whose website host already exists.
 */
class GrimbaSeedImmigrationSources extends Command
{
    protected $signature = 'grimba:seed-immigration-sources
        {--dry-run : show what would be inserted without writing}';

    protected $description = 'Seed 6 immigration-focused publishers + RSS feeds for pre-launch content depth.';

    /**
     * @return array<int, array{name:string, website:string, language:string, country:string, bias:string, ownership:string, credibility:int, feeds:array<int,string>, notes:string}>
     */
    private function publishers(): array
    {
        return [
            [
                'name' => 'The New Humanitarian',
                'website' => 'https://www.thenewhumanitarian.org',
                'language' => 'en',
                'country' => 'CH',
                'bias' => 'center',
                'ownership' => 'nonprofit',
                'credibility' => 92,
                'feeds' => ['https://www.thenewhumanitarian.org/rss/all'],
                'notes' => 'Geneva-based humanitarian-affairs newsroom (formerly IRIN). Daily coverage of displacement, migration, asylum, refugee crises.',
            ],
            [
                'name' => 'Migration Policy Institute',
                'website' => 'https://www.migrationpolicy.org',
                'language' => 'en',
                'country' => 'US',
                'bias' => 'center',
                'ownership' => 'nonprofit',
                'credibility' => 90,
                'feeds' => ['https://www.migrationpolicy.org/rss/migration-information-source'],
                'notes' => 'Washington-based think tank; deep analysis of immigration policy + migration data.',
            ],
            [
                'name' => 'La Cimade',
                'website' => 'https://www.lacimade.org',
                'language' => 'fr',
                'country' => 'FR',
                'bias' => 'left',
                'ownership' => 'nonprofit',
                'credibility' => 82,
                'feeds' => ['https://www.lacimade.org/feed/'],
                'notes' => 'Association française de solidarité avec les personnes migrantes. Couverture des droits des étrangers, asile, frontières.',
            ],
            [
                'name' => "France terre d'asile",
                'website' => 'https://www.france-terre-asile.org',
                'language' => 'fr',
                'country' => 'FR',
                'bias' => 'left',
                'ownership' => 'nonprofit',
                'credibility' => 82,
                'feeds' => ['https://www.france-terre-asile.org/toutes-les-actualites?format=feed&type=rss'],
                'notes' => "Association d'aide aux demandeurs d'asile et réfugiés en France.",
            ],
            [
                'name' => 'UNHCR News',
                'website' => 'https://www.unhcr.org',
                'language' => 'en',
                'country' => 'CH',
                'bias' => 'center',
                'ownership' => 'government',
                'credibility' => 95,
                'feeds' => ['https://www.unhcr.org/rss/news.xml'],
                'notes' => 'UN Refugee Agency. Authoritative source on refugee policy + emergencies.',
            ],
            [
                'name' => 'Refugees International',
                'website' => 'https://www.refugeesinternational.org',
                'language' => 'en',
                'country' => 'US',
                'bias' => 'center',
                'ownership' => 'nonprofit',
                'credibility' => 88,
                'feeds' => ['https://www.refugeesinternational.org/rss/'],
                'notes' => 'Independent advocacy and policy organization for displaced people worldwide.',
            ],
        ];
    }

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $publishers = $this->publishers();

        $added = $skipped = $feedsAdded = 0;
        $report = [];

        foreach ($publishers as $p) {
            $host = parse_url($p['website'], PHP_URL_HOST);
            if (! $host) {
                $report[] = [$p['name'], 'invalid_website', 0];
                continue;
            }

            $existing = DB::table('news_sources')
                ->whereRaw('LOWER(website) LIKE ?', ['%' . strtolower($host) . '%'])
                ->orWhere('name', $p['name'])
                ->first();

            if ($existing) {
                $report[] = [$p['name'], 'already_exists (#' . $existing->id . ')', 0];
                $skipped++;
                continue;
            }

            if ($dry) {
                $report[] = [$p['name'], 'WOULD_INSERT', count($p['feeds'])];
                continue;
            }

            $now = now();
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

            $feedsInserted = 0;
            foreach ($p['feeds'] as $url) {
                $hasFeedRow = DB::table('rss_feeds')->where('url', $url)->exists();
                if ($hasFeedRow) {
                    continue;
                }
                DB::table('rss_feeds')->insert([
                    'source_id' => $sourceId,
                    'url' => $url,
                    'feed_format' => 'rss',
                    'is_active' => true,
                    'notes' => 'Seeded by grimba:seed-immigration-sources 2026-05-17',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $feedsInserted++;
            }

            $added++;
            $feedsAdded += $feedsInserted;
            $report[] = [$p['name'], 'INSERTED (#' . $sourceId . ')', $feedsInserted];
        }

        $this->table(['Publisher', 'Status', 'Feeds'], $report);
        $this->info(sprintf(
            '%s — %d sources added, %d feeds added, %d skipped.',
            $dry ? 'DRY RUN' : 'Done',
            $added,
            $feedsAdded,
            $skipped
        ));
        if (! $dry && ($added > 0 || $feedsAdded > 0)) {
            $this->line('Next: run `php artisan grimba:poll-rss` to fetch the first batch, then `grimba:classify-categories` to assign category pivots.');
        }

        return self::SUCCESS;
    }
}
