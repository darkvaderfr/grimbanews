<?php

namespace Tests\Feature;

use App\Support\GrimbaVault;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VaultTest extends TestCase
{
    private function publishedPost(): object
    {
        $post = DB::table('posts')
            ->where('status', 'published')
            ->whereNotNull('name')
            ->orderBy('id')
            ->first(['id', 'name', 'source_name', 'bias_rating', 'created_at']);

        $this->assertNotNull($post, 'Fixture database must contain at least one published post.');

        return $post;
    }

    private function readerCookies(array $extra = []): array
    {
        return array_merge([
            'grimba_lang' => 'en',
            'grimba_onboarded' => '1',
        ], $extra);
    }

    public function test_vault_page_renders_empty_state(): void
    {
        $this->withUnencryptedCookies($this->readerCookies())
            ->get('/coffre')
            ->assertOk()
            ->assertSee('My vault')
            ->assertSee('No saved articles yet');
    }

    public function test_vault_page_renders_saved_article_from_cookie(): void
    {
        $post = $this->publishedPost();

        $this->withUnencryptedCookies($this->readerCookies([
            GrimbaVault::COOKIE => (string) $post->id,
        ]))
            ->get('/coffre')
            ->assertOk()
            ->assertSee((string) $post->name);
    }

    public function test_vault_export_empty_csv_contains_only_header(): void
    {
        $response = $this->withUnencryptedCookies($this->readerCookies())
            ->get('/coffre/export.csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('rang,post_id,titre,source,biais,publie_le', $content);
        $this->assertSame(1, substr_count(trim(str_replace("\xEF\xBB\xBF", '', $content)), "\n") + 1);
    }

    public function test_vault_export_csv_hydrates_saved_article(): void
    {
        $post = $this->publishedPost();

        $response = $this->withUnencryptedCookies($this->readerCookies([
            GrimbaVault::COOKIE => (string) $post->id,
        ]))
            ->get('/coffre/export.csv');

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString((string) $post->id, $content);
        $this->assertStringContainsString((string) $post->name, $content);
    }

    public function test_header_vault_badge_counts_unique_valid_cookie_ids(): void
    {
        $post = $this->publishedPost();
        $cookie = implode(',', [$post->id, $post->id, '0', 'bad']);

        $response = $this->withUnencryptedCookies($this->readerCookies([
            GrimbaVault::COOKIE => $cookie,
        ]))
            ->get('/')
            ->assertOk()
            ->assertSee('id="grimba-vault-count"', false);

        $this->assertMatchesRegularExpression(
            '/id="grimba-vault-count"[^>]*>1<\/span>/',
            $response->getContent()
        );
    }

    public function test_vault_cookie_parser_caps_and_deduplicates_ids(): void
    {
        $raw = implode(',', array_merge([5, 4, 4, 0, -1, 'bad'], range(1, 80)));

        $ids = GrimbaVault::parseIds($raw);

        $this->assertCount(50, $ids);
        $this->assertSame($ids, array_values(array_unique($ids)));
        $this->assertSame([5, 4, 1, 2, 3], array_slice($ids, 0, 5));
    }
}
