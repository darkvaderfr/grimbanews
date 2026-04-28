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
        // feedsByName: source_name => [url, is_active, notes] or
        // source_name => [[url, is_active, notes], ...]. Multiple feeds per
        // publisher are expected; rss_feeds is unique by source_id + url.
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
            'All Africa'        => [
                ['url' => 'https://allafrica.com/tools/headlines/rdf/latest/headlines.rdf',        'active' => true, 'notes' => 'Pan-African English wire service.'],
                ['url' => 'https://fr.allafrica.com/tools/headlines/rdf/latest/headlines.rdf',     'active' => true, 'notes' => 'French latest headlines feed.'],
                ['url' => 'https://fr.allafrica.com/tools/headlines/rdf/westafrica/headlines.rdf', 'active' => true, 'notes' => 'French West Africa headlines.'],
                ['url' => 'https://fr.allafrica.com/tools/headlines/rdf/centralafrica/headlines.rdf','active' => true,'notes' => 'French Central Africa headlines.'],
                ['url' => 'https://fr.allafrica.com/tools/headlines/rdf/business/headlines.rdf',   'active' => true, 'notes' => 'French business headlines.'],
                ['url' => 'https://fr.allafrica.com/tools/headlines/rdf/health/headlines.rdf',     'active' => true, 'notes' => 'French health headlines.'],
                ['url' => 'https://fr.allafrica.com/tools/headlines/rdf/conflict/headlines.rdf',   'active' => true, 'notes' => 'French conflict and security headlines.'],
                ['url' => 'https://fr.allafrica.com/tools/headlines/rdf/environment/headlines.rdf','active' => true, 'notes' => 'French environment headlines.'],
            ],
            'Global News'       => [
                ['url' => 'https://globalnews.ca/canada/feed/',      'active' => true, 'notes' => 'Official Canada section feed.'],
                ['url' => 'https://globalnews.ca/world/feed/',       'active' => true, 'notes' => 'Official World section feed.'],
                ['url' => 'https://globalnews.ca/politics/feed/',    'active' => true, 'notes' => 'Official Politics section feed.'],
                ['url' => 'https://globalnews.ca/money/feed/',       'active' => true, 'notes' => 'Official Money section feed.'],
                ['url' => 'https://globalnews.ca/health/feed/',      'active' => true, 'notes' => 'Official Health section feed.'],
                ['url' => 'https://globalnews.ca/environment/feed/', 'active' => true, 'notes' => 'Official Environment section feed.'],
                ['url' => 'https://globalnews.ca/sports/feed/',      'active' => true, 'notes' => 'Official Sports section feed.'],
                ['url' => 'https://globalnews.ca/us-news/feed/',     'active' => true, 'notes' => 'Official US News section feed.'],
            ],
            'CBC News'          => [
                ['url' => 'https://www.cbc.ca/cmlink/rss-topstories', 'active' => true, 'notes' => 'Canadian public broadcaster top stories feed.'],
                ['url' => 'https://www.cbc.ca/cmlink/rss-canada',     'active' => true, 'notes' => 'Canadian public broadcaster Canada feed.'],
                ['url' => 'https://www.cbc.ca/cmlink/rss-world',      'active' => true, 'notes' => 'Canadian public broadcaster world feed.'],
            ],
            'NPR'               => ['url' => 'https://feeds.npr.org/1001/rss.xml', 'active' => true, 'notes' => 'US public radio top stories feed.'],
            'Al Jazeera English'=> ['url' => 'https://www.aljazeera.com/xml/rss/all.xml', 'active' => true, 'notes' => 'Al Jazeera English all-news RSS feed.'],
            'VOA Afrique'       => ['url' => 'https://www.voaafrique.com/api/', 'active' => true, 'notes' => 'VOA Afrique official RSS endpoint.'],
            'WHO Africa'        => [
                ['url' => 'https://www.afro.who.int/rss/featured-news.xml', 'active' => true, 'notes' => 'WHO Africa featured news feed.'],
                ['url' => 'https://www.afro.who.int/rss/emergencies.xml',   'active' => true, 'notes' => 'WHO Africa emergencies feed.'],
            ],

            // S152 — right-leaning feeds added 2026-04-26 to balance the
            // FR/center-heavy default mix. Seven of these poll cleanly;
            // Le Point + L'Opinion default URLs 404 and are kept inactive
            // until an editor supplies a working replacement.
            'Daily Mail'             => ['url' => 'https://www.dailymail.co.uk/news/index.rss',          'active' => true,  'notes' => 'S152 — right-leaning UK tabloid coverage.'],
            'The Telegraph'          => ['url' => 'https://www.telegraph.co.uk/rss.xml',                 'active' => true,  'notes' => 'S152 — right-leaning UK broadsheet.'],
            'The Wall Street Journal'=> ['url' => 'https://feeds.a.dj.com/rss/RSSWorldNews.xml',         'active' => true,  'notes' => 'S152 — right-of-center US business daily, world news section.'],
            'National Review'        => ['url' => 'https://www.nationalreview.com/feed/',                'active' => true,  'notes' => 'S152 — US conservative magazine.'],
            'Fox News'               => ['url' => 'https://moxie.foxnews.com/google-publisher/latest.xml','active' => true, 'notes' => 'S152 — right-leaning US cable + web news.'],
            'The Washington Times'   => ['url' => 'https://www.washingtontimes.com/rss/headlines/news/world/','active' => true,'notes' => 'S152 — US right-leaning daily, world section.'],
            'Breitbart News'         => ['url' => 'https://feeds.feedburner.com/breitbart',              'active' => true,  'notes' => 'S152 — US right-wing news site.'],
            // Le Point + L'Opinion attempted at S152 — both 404 from
            // their advertised RSS endpoints. Skipped here until an
            // editor supplies a working URL.
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

            $feedConfigs = isset($cfg['url']) ? [$cfg] : $cfg;

            foreach ($feedConfigs as $feed) {
                DB::table('rss_feeds')->updateOrInsert(
                    ['source_id' => $source->id, 'url' => $feed['url']],
                    [
                        'feed_format' => str_contains($feed['url'], '/atom') || str_ends_with($feed['url'], '.atom')
                            ? 'atom'
                            : 'rss',
                        'is_active'   => $feed['active'],
                        'notes'       => $feed['notes'],
                        'updated_at'  => $now,
                        'created_at'  => $now,
                    ]
                );
            }
        }

        $active = DB::table('rss_feeds')->where('is_active', true)->count();
        $total  = DB::table('rss_feeds')->count();
        $this->command?->info("Seeded {$total} RSS feeds ({$active} active).");
    }
}
