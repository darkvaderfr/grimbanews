<?php

namespace Tests\Feature;

use App\Support\GrimbaVault;
use App\Support\GrimbaVaultEvents;
use Botble\ACL\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class VaultAnalyticsDashboardTest extends TestCase
{
    public function test_vault_analytics_dashboard_renders_weekly_saves_and_return_conversion(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        DB::table(GrimbaVaultEvents::TABLE)->delete();

        $post = $this->publishedPost();
        $weekStart = now()->startOfWeek();
        $savedAndReturned = GrimbaVaultEvents::ipHash('203.0.113.10');
        $savedOnly = GrimbaVaultEvents::ipHash('203.0.113.11');
        $returnOnly = GrimbaVaultEvents::ipHash('203.0.113.12');

        DB::table(GrimbaVaultEvents::TABLE)->insert([
            [
                'event' => GrimbaVaultEvents::EVENT_SAVE,
                'post_id' => $post->id,
                'ts' => $weekStart->copy()->addDay()->setTime(9, 0),
                'ip_hash' => $savedAndReturned,
            ],
            [
                'event' => GrimbaVaultEvents::EVENT_RETURN_VISIT,
                'post_id' => 0,
                'ts' => $weekStart->copy()->addDay()->setTime(9, 30),
                'ip_hash' => $savedAndReturned,
            ],
            [
                'event' => GrimbaVaultEvents::EVENT_SAVE,
                'post_id' => $post->id,
                'ts' => $weekStart->copy()->addDays(2)->setTime(10, 0),
                'ip_hash' => $savedOnly,
            ],
            [
                'event' => GrimbaVaultEvents::EVENT_RETURN_VISIT,
                'post_id' => 0,
                'ts' => $weekStart->copy()->addDays(2)->setTime(11, 0),
                'ip_hash' => $returnOnly,
            ],
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/grimba/vault-analytics?week=' . $weekStart->toDateString())
            ->assertOk()
            ->assertSee('Analytics coffre')
            ->assertSee('Articles les plus sauvegardés')
            ->assertSee('Sauvegardes')
            ->assertSee('Retours convertis')
            ->assertSee('50%')
            ->assertSee($post->name)
            ->assertSee('grimba-vault-week-bars', false)
            ->assertDontSee('203.0.113.10');
    }

    public function test_vault_page_records_privacy_safe_return_visit_once_per_day(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        DB::table(GrimbaVaultEvents::TABLE)->delete();

        $post = $this->publishedPost();

        $this
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.30'])
            ->withUnencryptedCookies($this->readerCookies([
                GrimbaVault::COOKIE => (string) $post->id,
            ]))
            ->get('/coffre')
            ->assertOk();

        $this
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.30'])
            ->withUnencryptedCookies($this->readerCookies([
                GrimbaVault::COOKIE => (string) $post->id,
            ]))
            ->get('/coffre')
            ->assertOk();

        $rows = DB::table(GrimbaVaultEvents::TABLE)
            ->where('event', GrimbaVaultEvents::EVENT_RETURN_VISIT)
            ->get();

        $this->assertCount(1, $rows);
        $this->assertSame(0, (int) $rows->first()->post_id);
        $this->assertSame(GrimbaVaultEvents::ipHash('203.0.113.30'), $rows->first()->ip_hash);
        $this->assertNotSame('203.0.113.30', $rows->first()->ip_hash);
    }

    private function admin(): User
    {
        $user = User::query()->find(1);

        $this->assertNotNull($user, 'Fixture database must contain the system admin user.');

        return $user;
    }

    /**
     * @param array<string, string> $extra
     * @return array<string, string>
     */
    private function readerCookies(array $extra = []): array
    {
        return array_merge([
            'grimba_lang' => 'en',
            'grimba_onboarded' => '1',
        ], $extra);
    }

    private function publishedPost(): object
    {
        $post = DB::table('posts')
            ->where('status', 'published')
            ->orderByDesc('id')
            ->first(['id', 'name']);

        $this->assertNotNull($post, 'Fixture database must contain a published post.');

        return $post;
    }
}
