<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/*
 * Seeds rss_feeds with known French / Francophone RSS endpoints,
 * keyed to the news_sources row by source name (the seeded set
 * from NewsSourcesSeeder is authoritative).
 *
 * Not every source has a public RSS — AFP / Jeune Afrique content
 * APIs are private — so the seed list is a pragmatic starting
 * point. URLs that 404 or throw will surface in rss_feeds.last_error
 * after the first poll, and an editor can update or mark them
 * inactive via the admin UI shipped in S72.
 *
 * Run: php artisan db:seed --class=RssFeedsSeeder
 */
class RssFeedsSeeder extends Seeder
{
    public function run(): void
    {
        // feedsByName: source_name => [url, is_active, notes]
        // Broken feeds are retained with is_active=false so the admin UI
        // shows why (audit trail), not silently dropped.
        $feedsByName = [
            'Le Monde'          => ['url' => 'https://www.lemonde.fr/rss/une.xml',                               'active' => true,  'notes' => null],
            'Libération'        => ['url' => 'https://www.liberation.fr/arc/outboundfeeds/rss/?outputType=xml', 'active' => true,  'notes' => null],
            'Mediapart'         => ['url' => 'https://www.mediapart.fr/articles/feed',                           'active' => true,  'notes' => null],
            'Le Figaro'         => ['url' => 'https://www.lefigaro.fr/rss/figaro_actualites.xml',                'active' => true,  'notes' => null],
            'France 24'         => ['url' => 'https://www.france24.com/fr/france/rss',                           'active' => true,  'notes' => null],
            'L\'Opinion'        => ['url' => 'https://www.lopinion.fr/feed.xml',                                 'active' => false, 'notes' => '2026-04-24: returns 404 behind CF. Needs replacement URL from editor.'],
            'Valeurs Actuelles' => ['url' => 'https://www.valeursactuelles.com/feed',                            'active' => true,  'notes' => null],
            'Jeune Afrique'     => ['url' => 'https://www.jeuneafrique.com/feed/',                               'active' => true,  'notes' => null],
            'RFI Afrique'       => ['url' => 'https://www.rfi.fr/fr/afrique/rss',                                'active' => true,  'notes' => null],
            'Cameroon Tribune'  => ['url' => 'https://www.cameroon-tribune.cm/rss',                              'active' => false, 'notes' => '2026-04-24: returns 403. Host serves iso-8859-1 404 body — likely UA-filtered or feed removed.'],
            'BBC'               => ['url' => 'https://feeds.bbci.co.uk/news/rss.xml',                            'active' => true,  'notes' => null],
            'The Guardian'      => ['url' => 'https://www.theguardian.com/world/rss',                            'active' => true,  'notes' => null],
            'Reuters'           => ['url' => 'https://feeds.reuters.com/reuters/topNews',                        'active' => false, 'notes' => '2026-04-24: feeds.reuters.com DNS gone after their paywall migration. No replacement without API access.'],
            'All Africa'        => ['url' => 'https://allafrica.com/tools/headlines/rdf/latest/headlines.rdf',   'active' => true,  'notes' => 'Pan-African wire service — compensates for Cameroon Tribune outage.'],
        ];

        $now = now();

        foreach ($feedsByName as $sourceName => $cfg) {
            $source = DB::table('news_sources')
                ->where('name', $sourceName)
                ->first(['id']);

            if (! $source) {
                $this->command?->warn("news_sources row for '{$sourceName}' not found — skipping.");
                continue;
            }

            DB::table('rss_feeds')->updateOrInsert(
                ['source_id' => $source->id, 'url' => $cfg['url']],
                [
                    'feed_format' => str_contains($cfg['url'], '/atom') || str_ends_with($cfg['url'], '.atom')
                        ? 'atom'
                        : 'rss',
                    'is_active'   => $cfg['active'],
                    'notes'       => $cfg['notes'],
                    'updated_at'  => $now,
                    'created_at'  => $now,
                ]
            );
        }

        $active = DB::table('rss_feeds')->where('is_active', true)->count();
        $total  = DB::table('rss_feeds')->count();
        $this->command?->info("Seeded {$total} RSS feeds ({$active} active).");
    }
}
