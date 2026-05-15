<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $feeds = [
            'France 24' => [
                ['url' => 'https://www.france24.com/fr/afrique/rss', 'notes' => 'Free Africa-section RSS backstop added 2026-05-15.'],
            ],
            'BBC' => [
                ['url' => 'https://feeds.bbci.co.uk/news/world/africa/rss.xml', 'notes' => 'Free BBC Africa RSS backstop added 2026-05-15.'],
            ],
            'The Guardian' => [
                ['url' => 'https://www.theguardian.com/world/africa/rss', 'notes' => 'Free Guardian Africa RSS backstop added 2026-05-15.'],
            ],
            'Financial Afrik' => [
                ['url' => 'https://www.financialafrik.com/feed/', 'notes' => 'Free Financial Afrik RSS backstop added 2026-05-15.'],
            ],
        ];

        $now = now();

        foreach ($feeds as $sourceName => $items) {
            $sourceId = DB::table('news_sources')->where('name', $sourceName)->value('id');
            if (! $sourceId) {
                continue;
            }

            foreach ($items as $feed) {
                DB::table('rss_feeds')->updateOrInsert(
                    ['source_id' => $sourceId, 'url' => $feed['url']],
                    [
                        'feed_format' => 'rss',
                        'is_active' => true,
                        'notes' => $feed['notes'],
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('rss_feeds')->whereIn('url', [
            'https://www.france24.com/fr/afrique/rss',
            'https://feeds.bbci.co.uk/news/world/africa/rss.xml',
            'https://www.theguardian.com/world/africa/rss',
            'https://www.financialafrik.com/feed/',
        ])->delete();
    }
};
