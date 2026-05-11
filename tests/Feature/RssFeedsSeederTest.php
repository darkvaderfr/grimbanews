<?php

namespace Tests\Feature;

use Database\Seeders\NewsSourcesSeeder;
use Database\Seeders\RssFeedsSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RssFeedsSeederTest extends TestCase
{
    public function test_all_africa_feeds_remain_quarantined_until_replacements_are_verified(): void
    {
        $this->seed(NewsSourcesSeeder::class);

        $allAfricaId = (int) DB::table('news_sources')->where('name', 'All Africa')->value('id');
        $this->assertGreaterThan(0, $allAfricaId);

        DB::table('rss_feeds')
            ->where('source_id', $allAfricaId)
            ->where('url', 'like', 'https://%allafrica.com/%')
            ->update([
                'is_active' => true,
                'notes' => 'stale fixture state',
                'updated_at' => now(),
            ]);

        $this->seed(RssFeedsSeeder::class);

        $feeds = DB::table('rss_feeds')
            ->where('source_id', $allAfricaId)
            ->where('url', 'like', 'https://%allafrica.com/%')
            ->get(['is_active', 'notes']);

        $this->assertCount(8, $feeds);
        $this->assertSame(0, $feeds->where('is_active', true)->count());

        foreach ($feeds as $feed) {
            $this->assertStringContainsString('quarantined', (string) $feed->notes);
            $this->assertStringContainsString('working replacement', (string) $feed->notes);
        }
    }
}
