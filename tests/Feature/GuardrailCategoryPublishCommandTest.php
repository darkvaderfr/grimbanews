<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class GuardrailCategoryPublishCommandTest extends TestCase
{
    public function test_guardrail_drafts_publish_into_review_categories(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        $suffix = Str::lower(Str::random(8));
        $now = now();
        $author = User::query()->find(1);

        $this->assertNotNull($author, 'Fixture database must contain the system admin user.');

        $lowCredSourceId = DB::table('news_sources')->insertGetId([
            'name' => 'Guardrail Low Cred ' . $suffix,
            'slug' => 'guardrail-low-cred-' . $suffix,
            'website' => 'low-cred-' . $suffix . '.test',
            'bias_rating' => 'left',
            'credibility_score' => 42,
            'country' => 'FR',
            'language' => 'fr',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $unknownBiasSourceId = DB::table('news_sources')->insertGetId([
            'name' => 'Guardrail Unknown Bias ' . $suffix,
            'slug' => 'guardrail-unknown-bias-' . $suffix,
            'website' => 'unknown-bias-' . $suffix . '.test',
            'bias_rating' => 'unknown',
            'credibility_score' => 91,
            'country' => 'FR',
            'language' => 'fr',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $lowCredPostId = $this->draftPostId('Guardrail low credibility article ' . $suffix, $lowCredSourceId, $author);
        $unknownBiasPostId = $this->draftPostId('Guardrail unknown bias article ' . $suffix, $unknownBiasSourceId, $author);

        $this->artisan('grimba:publish-guardrail-categories', ['--limit' => 1])
            ->expectsTable(['Category', 'Published'], [
                ['Trusted Source Credibility', 1],
                ['Unclassified Source Bias', 1],
            ])
            ->expectsOutput('Published 2 post(s).')
            ->assertSuccessful();

        $trustedCategoryId = (int) DB::table('categories')->where('name', 'Trusted Source Credibility')->value('id');
        $unclassifiedCategoryId = (int) DB::table('categories')->where('name', 'Unclassified Source Bias')->value('id');

        $this->assertSame('published', DB::table('posts')->where('id', $lowCredPostId)->value('status'));
        $this->assertSame('published', DB::table('posts')->where('id', $unknownBiasPostId)->value('status'));
        $this->assertNotNull(DB::table('posts')->where('id', $lowCredPostId)->value('published_at'));
        $this->assertNotNull(DB::table('posts')->where('id', $unknownBiasPostId)->value('published_at'));
        $this->assertTrue(DB::table('post_categories')->where('post_id', $lowCredPostId)->where('category_id', $trustedCategoryId)->exists());
        $this->assertTrue(DB::table('post_categories')->where('post_id', $unknownBiasPostId)->where('category_id', $unclassifiedCategoryId)->exists());
    }

    private function draftPostId(string $name, int $sourceId, User $author): int
    {
        return (int) DB::table('posts')->insertGetId([
            'name' => $name,
            'description' => 'Guardrail category publish command fixture.',
            'content' => '<p>Guardrail category publish command fixture.</p>',
            'status' => 'draft',
            'author_id' => $author->getKey(),
            'author_type' => User::class,
            'source_id' => $sourceId,
            'source_name' => DB::table('news_sources')->where('id', $sourceId)->value('name'),
            'original_language' => 'fr',
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);
    }
}
