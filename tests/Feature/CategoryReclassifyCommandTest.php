<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class CategoryReclassifyCommandTest extends TestCase
{
    public function test_category_scoped_reclassify_reports_and_replaces_changed_pivots(): void
    {
        $fixture = $this->fixture('cli');

        $this->artisan('grimba:classify-categories', ['--category' => $fixture['old_category_id']])
            ->expectsOutputToContain('Category scope: J4 Old Category cli')
            ->expectsOutputToContain('Done. 1 classified · 1 changed · 0 unchanged · 0 skipped')
            ->assertSuccessful();

        $this->assertFalse(DB::table('post_categories')
            ->where('post_id', $fixture['post_id'])
            ->where('category_id', $fixture['old_category_id'])
            ->exists());

        $this->assertTrue(DB::table('post_categories')
            ->where('post_id', $fixture['post_id'])
            ->where('category_id', $fixture['afrique_id'])
            ->exists());
    }

    public function test_cockpit_runbook_button_can_trigger_category_reclassify(): void
    {
        $fixture = $this->fixture('admin');

        $this->actingAs($this->admin())
            ->get('/admin/grimba/cockpit')
            ->assertOk()
            ->assertSee('Reclasser catégorie')
            ->assertSee('name="category_id"', false);

        $this->actingAs($this->admin())
            ->post('/admin/grimba/cockpit/runbook', [
                'action' => 'category_reclassify',
                'category_id' => $fixture['old_category_id'],
            ])
            ->assertRedirect('/admin/grimba/cockpit')
            ->assertSessionHas('success_msg');

        $this->assertTrue(DB::table('post_categories')
            ->where('post_id', $fixture['post_id'])
            ->where('category_id', $fixture['afrique_id'])
            ->exists());
    }

    /**
     * @return array{old_category_id: int, afrique_id: int, post_id: int}
     */
    private function fixture(string $suffix): array
    {
        $this->cleanup($suffix);

        $author = $this->admin();
        $afriqueId = $this->category('Afrique', $author);
        $this->category('International', $author);
        $oldCategoryId = $this->category('J4 Old Category ' . $suffix, $author);
        $postId = $this->article('J4 Mali reclassify fixture ' . $suffix, $author);

        DB::table('post_categories')->insert([
            'post_id' => $postId,
            'category_id' => $oldCategoryId,
        ]);

        return [
            'old_category_id' => $oldCategoryId,
            'afrique_id' => $afriqueId,
            'post_id' => $postId,
        ];
    }

    private function admin(): User
    {
        $user = User::query()->find(1);

        $this->assertNotNull($user, 'Fixture database must contain the system admin user.');

        return $user;
    }

    private function category(string $name, User $author): int
    {
        $existingId = DB::table('categories')->where('name', $name)->value('id');
        if ($existingId) {
            return (int) $existingId;
        }

        return (int) DB::table('categories')->insertGetId([
            'name' => $name,
            'parent_id' => 0,
            'description' => 'J4 category reclassify fixture.',
            'status' => 'published',
            'author_id' => $author->getKey(),
            'author_type' => User::class,
            'icon' => null,
            'order' => 0,
            'is_featured' => 0,
            'is_default' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function article(string $name, User $author): int
    {
        return (int) DB::table('posts')->insertGetId([
            'name' => $name,
            'description' => 'Mali and Sahel coverage should reclassify into Afrique.',
            'content' => '<p>Mali and Sahel coverage should reclassify into Afrique.</p>',
            'status' => 'published',
            'author_id' => $author->getKey(),
            'author_type' => User::class,
            'is_featured' => 0,
            'views' => 0,
            'bias_rating' => 'center',
            'is_blindspot' => 0,
            'source_name' => 'J4 Reclassify Fixture',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function cleanup(string $suffix): void
    {
        $postIds = DB::table('posts')
            ->where('name', 'J4 Mali reclassify fixture ' . $suffix)
            ->pluck('id')
            ->all();

        if ($postIds !== []) {
            DB::table('post_categories')->whereIn('post_id', $postIds)->delete();
            DB::table('posts')->whereIn('id', $postIds)->delete();
        }

        DB::table('categories')
            ->where('name', 'J4 Old Category ' . $suffix)
            ->delete();

        DB::table('slugs')
            ->where('key', Str::slug('J4 Old Category ' . $suffix))
            ->delete();
    }
}
