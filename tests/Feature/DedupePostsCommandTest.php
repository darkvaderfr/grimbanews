<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class DedupePostsCommandTest extends TestCase
{
    public function test_apply_deletes_url_duplicates_but_skips_title_only_groups_by_default(): void
    {
        $suffix = Str::lower(Str::random(8));
        $sourceId = $this->source('Dedupe Source ' . $suffix);
        $feedId = $this->feed($sourceId, 'https://example.test/dedupe-' . $suffix . '.xml');

        $keepId = $this->createPost('Dedupe canonical keeper ' . $suffix, $sourceId, now()->subHours(4));
        $dropId = $this->createPost('Dedupe canonical duplicate ' . $suffix, $sourceId, now()->subHours(3));
        $this->ledger($feedId, $keepId, 'canonical-a-' . $suffix, 'https://example.test/same-story?utm_source=rss', 'hash-' . $suffix);
        $this->ledger($feedId, $dropId, 'canonical-b-' . $suffix, 'https://example.test/same-story#later', 'hash-' . $suffix);

        $titleOnlyOne = $this->createPost('Dedupe title only ' . $suffix, $sourceId, now()->subHours(2));
        $titleOnlyTwo = $this->createPost('Dedupe title only ' . $suffix, $sourceId, now()->subHour());
        $this->ledger($feedId, $titleOnlyOne, 'title-a-' . $suffix, 'https://example.test/live-one-' . $suffix, 'title-hash-a-' . $suffix);
        $this->ledger($feedId, $titleOnlyTwo, 'title-b-' . $suffix, 'https://example.test/live-two-' . $suffix, 'title-hash-b-' . $suffix);

        $this->artisan('grimba:dedupe-posts', [
            '--apply' => true,
            '--source-id' => $sourceId,
            '--limit' => 20,
        ])
            ->expectsOutputToContain('Title-only groups are skipped')
            ->expectsOutputToContain('Deleted 1 duplicate post')
            ->assertSuccessful();

        $this->assertDatabaseHas('posts', ['id' => $keepId]);
        $this->assertDatabaseMissing('posts', ['id' => $dropId]);
        $this->assertSame(2, DB::table('rss_feed_items')->where('canonical_url_hash', 'hash-' . $suffix)->where('post_id', $keepId)->count());

        $this->assertDatabaseHas('posts', ['id' => $titleOnlyOne]);
        $this->assertDatabaseHas('posts', ['id' => $titleOnlyTwo]);
    }

    private function source(string $name): int
    {
        return (int) DB::table('news_sources')->insertGetId([
            'name' => $name,
            'slug' => Str::slug($name),
            'website' => Str::slug($name) . '.example',
            'bias_rating' => 'center',
            'ownership_type' => 'independent',
            'credibility_score' => 80,
            'country' => 'FR',
            'language' => 'fr',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function feed(int $sourceId, string $url): int
    {
        return (int) DB::table('rss_feeds')->insertGetId([
            'source_id' => $sourceId,
            'url' => $url,
            'feed_format' => 'rss',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createPost(string $name, int $sourceId, mixed $createdAt): int
    {
        $authorId = User::query()->value('id');
        $this->assertNotNull($authorId, 'Fixture database must contain a CMS user.');

        $row = [
            'name' => $name,
            'description' => 'Dedupe fixture article.',
            'content' => '<p>Dedupe fixture article.</p>',
            'status' => 'published',
            'author_id' => $authorId,
            'author_type' => User::class,
            'source_id' => $sourceId,
            'source_name' => DB::table('news_sources')->where('id', $sourceId)->value('name'),
            'bias_rating' => 'center',
            'original_language' => 'fr',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];

        if (Schema::hasColumn('posts', 'published_at')) {
            $row['published_at'] = $createdAt;
        }

        return (int) DB::table('posts')->insertGetId($row);
    }

    private function ledger(int $feedId, int $postId, string $guid, string $link, string $hash): void
    {
        DB::table('rss_feed_items')->insert([
            'feed_id' => $feedId,
            'guid' => $guid,
            'link' => $link,
            'title_snapshot' => 'Dedupe fixture',
            'post_id' => $postId,
            'seen_at' => now(),
            'published_at' => now(),
            'canonical_url_hash' => $hash,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
