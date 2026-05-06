<?php

namespace Tests\Feature;

use App\Mail\GrimbaSavedSearchDigestMail;
use App\Support\GrimbaSavedSearches;
use Botble\ACL\Models\User;
use Botble\Blog\Models\Post;
use Botble\Member\Models\Member;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class SavedSearchAlertsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        if (Schema::hasTable('saved_searches')) {
            DB::table('saved_searches')->delete();
        }
    }

    public function test_member_can_save_and_remove_search_alert(): void
    {
        $member = $this->member();
        $query = 'd7savedneedle' . Str::lower(Str::random(6));

        $this->actingAs($member, 'member')
            ->post('/search/alerts', [
                'q' => $query,
                'bias' => 'center',
                'owner' => 'D7 Owner',
            ])
            ->assertRedirect(GrimbaSavedSearches::searchUrl([
                'search_query' => $query,
                'bias' => 'center',
                'owner' => 'D7 Owner',
            ]));

        $searchId = (int) DB::table('saved_searches')
            ->where('member_id', $member->id)
            ->where('search_query', $query)
            ->value('id');

        $this->assertGreaterThan(0, $searchId);
        $this->assertDatabaseHas('saved_searches', [
            'id' => $searchId,
            'member_id' => $member->id,
            'search_query' => $query,
            'bias' => 'center',
            'owner' => 'D7 Owner',
            'active' => 1,
        ]);

        $this->actingAs($member, 'member')
            ->get('/account')
            ->assertOk()
            ->assertSee($query);

        $this->actingAs($member, 'member')
            ->delete('/account/saved-searches/' . $searchId)
            ->assertRedirect('/account');

        $this->assertDatabaseMissing('saved_searches', [
            'id' => $searchId,
        ]);
    }

    public function test_weekly_command_sends_new_saved_search_matches(): void
    {
        Mail::fake();

        $member = $this->member();
        $author = User::query()->find(1);
        $this->assertNotNull($author, 'Fixture database must contain the system admin user.');

        $suffix = Str::lower(Str::random(8));
        $needle = 'alertneedle' . $suffix;
        $owner = 'Saved Search Owner ' . $suffix;
        $sourceId = $this->sourceId('Saved Search Source ' . $suffix, $owner, 'center');

        $criteria = [
            'search_query' => $needle,
            'source_id' => $sourceId,
            'bias' => 'center',
            'owner' => $owner,
            'from_date' => now()->subDays(7)->toDateString(),
            'to_date' => now()->addDay()->toDateString(),
        ];

        DB::table('saved_searches')->insert([
            'member_id' => $member->id,
            'search_query' => $criteria['search_query'],
            'source_id' => $criteria['source_id'],
            'bias' => $criteria['bias'],
            'owner' => $criteria['owner'],
            'from_date' => $criteria['from_date'],
            'to_date' => $criteria['to_date'],
            'search_hash' => GrimbaSavedSearches::hash($criteria),
            'active' => true,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        $postId = $this->postId(
            $needle . ' new matching article',
            $sourceId,
            $author,
            now()->subDay()
        );

        $this->artisan('grimba:saved-search-digests')
            ->expectsOutput('Sent 1 saved-search digest(s); skipped 0.')
            ->assertExitCode(0);

        Mail::assertSent(GrimbaSavedSearchDigestMail::class, function (GrimbaSavedSearchDigestMail $mail) use ($member, $postId): bool {
            $firstDigest = $mail->digests->first();

            return (int) $mail->member->id === (int) $member->id
                && $firstDigest
                && $firstDigest['posts']->pluck('id')->map(fn ($id): int => (int) $id)->contains((int) $postId);
        });

        $this->assertNotNull(DB::table('saved_searches')->where('member_id', $member->id)->value('last_sent_at'));
    }

    public function test_weekly_saved_search_command_is_scheduled(): void
    {
        Artisan::call('schedule:list');

        $this->assertStringContainsString('grimba:saved-search-digests', Artisan::output());
    }

    private function member(): Member
    {
        $member = Member::query()->first();

        $this->assertNotNull($member, 'Fixture database must contain at least one member account.');

        return $member;
    }

    private function sourceId(string $name, string $owner, string $bias): int
    {
        return (int) DB::table('news_sources')->insertGetId([
            'name' => $name,
            'slug' => Str::slug($name),
            'website' => Str::slug($name) . '.test',
            'bias_rating' => $bias,
            'ownership_type' => 'corporate',
            'owner_name' => $owner,
            'credibility_score' => 80,
            'country' => 'FR',
            'language' => 'fr',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function postId(string $name, int $sourceId, User $author, mixed $createdAt): int
    {
        $sourceName = DB::table('news_sources')->where('id', $sourceId)->value('name');

        $postId = (int) DB::table('posts')->insertGetId([
            'name' => $name,
            'description' => 'Saved search alert fixture body.',
            'content' => '<p>Saved search alert fixture body.</p>',
            'status' => 'published',
            'author_id' => $author->getKey(),
            'author_type' => User::class,
            'source_id' => $sourceId,
            'source_name' => $sourceName,
            'bias_rating' => 'center',
            'original_language' => 'fr',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        DB::table('slugs')->insert([
            'key' => Str::slug($name),
            'reference_id' => $postId,
            'reference_type' => Post::class,
            'prefix' => 'blog',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $postId;
    }
}
