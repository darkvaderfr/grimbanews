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
class SourceClassificationDashboardTest extends TestCase
{
    private function admin(): User
    {
        $user = User::query()->find(1);

        $this->assertNotNull($user, 'Fixture database must contain the system admin user.');

        return $user;
    }

    public function test_source_classification_dashboard_renders_ranked_inline_editor(): void
    {
        $sourceId = $this->sourceFixture('J2 Classification Fixture', [
            'credibility_score' => null,
            'bias_rating' => 'unknown',
            'owner_name' => null,
            'country' => null,
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/grimba/news-sources/classification?q=J2%20Classification')
            ->assertOk()
            ->assertSee('Table de classification')
            ->assertSee('Sources classées par crédibilité')
            ->assertSee('Crédibilité manquante')
            ->assertSee('J2 Classification Fixture')
            ->assertSee('data-source-id="' . $sourceId . '"', false)
            ->assertSee('data-field="bias_rating"', false)
            ->assertSee('data-field="ownership_type"', false)
            ->assertSee('data-field="owner_name"', false)
            ->assertSee('data-field="country"', false)
            ->assertSee('quick-classify', false);
    }

    public function test_source_classification_inline_endpoint_updates_one_source(): void
    {
        $sourceId = $this->sourceFixture('J2 Inline Update Fixture');

        $this->assertDatabaseHas('news_sources', [
            'id' => $sourceId,
            'name' => 'J2 Inline Update Fixture',
        ]);

        $this->actingAs($this->admin())
            ->postJson('/admin/grimba/news-sources/' . $sourceId . '/quick-classify', [
                'bias_rating' => 'center',
                'bias_score' => '0',
                'ownership_type' => 'independent',
                'owner_name' => 'J2 Editorial Trust',
                'credibility_score' => '87',
                'country' => 'FR',
                'language' => 'fr',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $source = DB::table('news_sources')->where('id', $sourceId)->first();

        $this->assertSame('center', $source->bias_rating);
        $this->assertSame('independent', $source->ownership_type);
        $this->assertSame('J2 Editorial Trust', $source->owner_name);
        $this->assertSame(87, (int) $source->credibility_score);
        $this->assertSame('FR', $source->country);
    }

    private function sourceFixture(string $name, array $overrides = []): int
    {
        DB::table('news_sources')->where('name', $name)->delete();

        return (int) DB::table('news_sources')->insertGetId(array_merge([
            'name' => $name,
            'slug' => Str::slug($name),
            'website' => 'j2-classification.example',
            'bias_rating' => 'unknown',
            'ownership_type' => null,
            'owner_name' => null,
            'credibility_score' => 20,
            'country' => null,
            'language' => 'fr',
            'notes' => 'J2 dashboard fixture',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }
}
