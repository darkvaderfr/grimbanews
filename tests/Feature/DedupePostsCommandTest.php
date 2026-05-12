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
            ->expectsOutputToContain('grimba:dedupe-posts --review-title-groups')
            ->expectsOutputToContain('Deleted 1 duplicate post')
            ->assertSuccessful();

        $this->assertDatabaseHas('posts', ['id' => $keepId]);
        $this->assertDatabaseMissing('posts', ['id' => $dropId]);
        $this->assertSame(2, DB::table('rss_feed_items')->where('canonical_url_hash', 'hash-' . $suffix)->where('post_id', $keepId)->count());

        $this->assertDatabaseHas('posts', ['id' => $titleOnlyOne]);
        $this->assertDatabaseHas('posts', ['id' => $titleOnlyTwo]);
    }

    public function test_title_only_review_mode_lists_urls_without_deleting_posts(): void
    {
        $suffix = Str::lower(Str::random(8));
        $sourceId = $this->source('Dedupe Review Source ' . $suffix);
        $feedId = $this->feed($sourceId, 'https://example.test/dedupe-review-' . $suffix . '.xml');

        $firstId = $this->createPost('Dedupe review title ' . $suffix, $sourceId, now()->subHours(2));
        $secondId = $this->createPost('Dedupe review title ' . $suffix, $sourceId, now()->subHour());
        $this->ledger($feedId, $firstId, 'review-a-' . $suffix, 'https://example.test/review-one-' . $suffix, 'review-hash-a-' . $suffix);
        $this->ledger($feedId, $secondId, 'review-b-' . $suffix, 'https://example.test/review-two-' . $suffix, 'review-hash-b-' . $suffix);

        $this->artisan('grimba:dedupe-posts', [
            '--review-title-groups' => true,
            '--source-id' => $sourceId,
            '--limit' => 20,
        ])
            ->expectsOutputToContain('Title-only duplicate review: 1 group(s) [DRY REVIEW]')
            ->expectsOutputToContain('Dedupe review title ' . $suffix)
            ->expectsOutputToContain((string) $firstId)
            ->expectsOutputToContain((string) $secondId)
            ->expectsOutputToContain('https://example.test/review-one-' . $suffix)
            ->expectsOutputToContain('https://example.test/review-two-' . $suffix)
            ->expectsOutputToContain('No posts were deleted')
            ->assertSuccessful();

        $this->assertDatabaseHas('posts', ['id' => $firstId]);
        $this->assertDatabaseHas('posts', ['id' => $secondId]);
    }

    public function test_same_title_same_normalized_url_is_actionable_without_title_group_flag(): void
    {
        $suffix = Str::lower(Str::random(8));
        $sourceId = $this->source('Dedupe Same Url Source ' . $suffix);
        $feedId = $this->feed($sourceId, 'https://example.test/dedupe-same-url-' . $suffix . '.xml');

        $keepId = $this->createPost('Dedupe same url title ' . $suffix, $sourceId, now()->subHours(2));
        $dropId = $this->createPost('Dedupe same url title ' . $suffix, $sourceId, now()->subHour());
        $this->ledger($feedId, $keepId, 'same-url-a-' . $suffix, 'https://example.test/story-' . $suffix . '?utm_source=rss', 'stale-hash-a-' . $suffix);
        $this->ledger($feedId, $dropId, 'same-url-b-' . $suffix, 'https://example.test/story-' . $suffix . '#rss-copy', 'stale-hash-b-' . $suffix);

        $this->artisan('grimba:dedupe-posts', [
            '--apply' => true,
            '--source-id' => $sourceId,
            '--limit' => 20,
        ])
            ->expectsOutputToContain('1 same-url title')
            ->expectsOutputToContain('Deleted 1 duplicate post')
            ->doesntExpectOutputToContain('Title-only groups are skipped')
            ->assertSuccessful();

        $this->assertDatabaseHas('posts', ['id' => $keepId]);
        $this->assertDatabaseMissing('posts', ['id' => $dropId]);
        $this->assertSame(2, DB::table('rss_feed_items')->whereIn('guid', ['same-url-a-' . $suffix, 'same-url-b-' . $suffix])->where('post_id', $keepId)->count());
    }

    public function test_publisher_url_aliases_are_actionable_without_title_group_flag(): void
    {
        $suffix = Str::lower(Str::random(8));
        $sourceId = $this->source('Dedupe Publisher Alias Source ' . $suffix);
        $feedId = $this->feed($sourceId, 'https://example.test/dedupe-publisher-alias-' . $suffix . '.xml');

        $keepId = $this->createPost('Dedupe publisher alias title ' . $suffix, $sourceId, now()->subHours(2));
        $dropId = $this->createPost('Dedupe publisher alias title ' . $suffix, $sourceId, now()->subHour());
        $this->ledger(
            $feedId,
            $keepId,
            'publisher-alias-a-' . $suffix,
            'https://www.lemonde.fr/international/article/2026/04/26/story-one_6683458_3210.html',
            'legacy-hash-a-' . $suffix
        );
        $this->ledger(
            $feedId,
            $dropId,
            'publisher-alias-b-' . $suffix,
            'https://www.lemonde.fr/international/article/2026/04/26/story-two_6683458_3211.html',
            'legacy-hash-b-' . $suffix
        );

        $this->artisan('grimba:dedupe-posts', [
            '--apply' => true,
            '--source-id' => $sourceId,
            '--limit' => 20,
        ])
            ->expectsOutputToContain('1 same-url title')
            ->expectsOutputToContain('Deleted 1 duplicate post')
            ->doesntExpectOutputToContain('Title-only groups are skipped')
            ->assertSuccessful();

        $this->assertDatabaseHas('posts', ['id' => $keepId]);
        $this->assertDatabaseMissing('posts', ['id' => $dropId]);
        $this->assertSame(2, DB::table('rss_feed_items')->whereIn('guid', ['publisher-alias-a-' . $suffix, 'publisher-alias-b-' . $suffix])->where('post_id', $keepId)->count());
    }

    public function test_title_group_uses_embedded_original_links_when_ledger_is_missing(): void
    {
        $suffix = Str::lower(Str::random(8));
        $sourceId = $this->source('Dedupe Embedded Link Source ' . $suffix);
        $feedId = $this->feed($sourceId, 'https://example.test/dedupe-embedded-link-' . $suffix . '.xml');
        $url = 'https://allafrica.com/stories/202604240206-' . $suffix . '.html';

        $keepId = $this->createPost(
            'Dedupe embedded link title ' . $suffix,
            $sourceId,
            now()->subHours(2),
            '<p><a href="' . $url . '" target="_blank" rel="noopener">Lire l’article original</a></p><p>Fixture body.</p>'
        );
        $dropId = $this->createPost('Dedupe embedded link title ' . $suffix, $sourceId, now()->subHour());
        $this->ledger($feedId, $dropId, 'embedded-link-b-' . $suffix, $url, 'embedded-link-hash-b-' . $suffix);

        $this->artisan('grimba:dedupe-posts', [
            '--apply' => true,
            '--source-id' => $sourceId,
            '--limit' => 20,
        ])
            ->expectsOutputToContain('1 same-url title')
            ->expectsOutputToContain('Deleted 1 duplicate post')
            ->doesntExpectOutputToContain('Title-only groups are skipped')
            ->assertSuccessful();

        $this->assertDatabaseHas('posts', ['id' => $keepId]);
        $this->assertDatabaseMissing('posts', ['id' => $dropId]);
        $this->assertSame(1, DB::table('rss_feed_items')->where('guid', 'embedded-link-b-' . $suffix)->where('post_id', $keepId)->count());
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

    private function createPost(string $name, int $sourceId, mixed $createdAt, ?string $content = null): int
    {
        $authorId = User::query()->value('id');
        $this->assertNotNull($authorId, 'Fixture database must contain a CMS user.');

        $row = [
            'name' => $name,
            'description' => 'Dedupe fixture article.',
            'content' => $content ?? '<p>Dedupe fixture article.</p>',
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
