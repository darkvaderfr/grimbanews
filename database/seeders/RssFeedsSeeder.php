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
        $feedsByName = [
            'Le Monde'          => 'https://www.lemonde.fr/rss/une.xml',
            'Libération'        => 'https://www.liberation.fr/arc/outboundfeeds/rss/?outputType=xml',
            'Mediapart'         => 'https://www.mediapart.fr/articles/feed',
            'Le Figaro'         => 'https://www.lefigaro.fr/rss/figaro_actualites.xml',
            'France 24'         => 'https://www.france24.com/fr/france/rss',
            'L\'Opinion'        => 'https://www.lopinion.fr/feed.xml',
            'Valeurs Actuelles' => 'https://www.valeursactuelles.com/feed',
            'Jeune Afrique'     => 'https://www.jeuneafrique.com/feed/',
            'RFI Afrique'       => 'https://www.rfi.fr/fr/afrique/rss',
            'Cameroon Tribune'  => 'https://www.cameroon-tribune.cm/rss',
            'BBC'               => 'https://feeds.bbci.co.uk/news/rss.xml',
            'The Guardian'      => 'https://www.theguardian.com/world/rss',
            'Reuters'           => 'https://feeds.reuters.com/reuters/topNews',
        ];

        $now = now();

        foreach ($feedsByName as $sourceName => $url) {
            $source = DB::table('news_sources')
                ->where('name', $sourceName)
                ->first(['id']);

            if (! $source) {
                $this->command?->warn("news_sources row for '{$sourceName}' not found — skipping.");
                continue;
            }

            DB::table('rss_feeds')->updateOrInsert(
                ['source_id' => $source->id, 'url' => $url],
                [
                    'feed_format' => str_contains($url, '/atom') || str_ends_with($url, '.atom')
                        ? 'atom'
                        : 'rss',
                    'is_active'   => true,
                    'updated_at'  => $now,
                    'created_at'  => $now,
                ]
            );
        }

        $this->command?->info('Seeded ' . DB::table('rss_feeds')->count() . ' RSS feeds.');
    }
}
