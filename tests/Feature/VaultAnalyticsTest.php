<?php

namespace Tests\Feature;

use App\Support\GrimbaVault;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VaultAnalyticsTest extends TestCase
{
    private function publishedPost(): object
    {
        $post = DB::table('posts')
            ->where('status', 'published')
            ->whereNotNull('name')
            ->orderBy('id')
            ->first(['id', 'name']);

        $this->assertNotNull($post, 'Fixture database must contain at least one published post.');

        DB::table('posts')->where('id', $post->id)->update(['source_id' => null]);

        return $post;
    }

    public function test_vault_toggle_logs_privacy_preserving_save_and_unsave_events(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        DB::table('vault_events')->delete();

        $post = $this->publishedPost();

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.42'])
            ->postJson('/coffre/toggle', ['post_id' => $post->id])
            ->assertOk()
            ->assertJsonPath('saved', true);

        $this->call(
            'POST',
            '/coffre/toggle',
            ['post_id' => $post->id],
            [GrimbaVault::COOKIE => (string) $post->id],
            [],
            ['HTTP_ACCEPT' => 'application/json', 'REMOTE_ADDR' => '203.0.113.42']
        )
            ->assertOk()
            ->assertJsonPath('saved', false);

        $rows = DB::table('vault_events')->orderBy('id')->get();

        $this->assertCount(2, $rows);
        $this->assertSame(['save', 'unsave'], $rows->pluck('event')->all());
        $this->assertSame([(int) $post->id, (int) $post->id], $rows->pluck('post_id')->map(fn ($id) => (int) $id)->all());
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $rows[0]->ip_hash);
        $this->assertSame($rows[0]->ip_hash, $rows[1]->ip_hash);
        $this->assertNotSame('203.0.113.42', $rows[0]->ip_hash);
        $this->assertNotEmpty($rows[0]->ts);
    }

    public function test_vault_events_archive_command_writes_monthly_csv(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        DB::table('vault_events')->delete();

        DB::table('vault_events')->insert([
            [
                'event' => 'save',
                'post_id' => 111,
                'ts' => '2026-05-03 10:00:00',
                'ip_hash' => str_repeat('a', 64),
            ],
            [
                'event' => 'unsave',
                'post_id' => 222,
                'ts' => '2026-05-04 11:00:00',
                'ip_hash' => str_repeat('b', 64),
            ],
            [
                'event' => 'save',
                'post_id' => 333,
                'ts' => '2026-04-30 23:59:59',
                'ip_hash' => str_repeat('c', 64),
            ],
        ]);

        $path = sys_get_temp_dir() . '/grimbanews-vault-events-2026-05.csv';
        if (is_file($path)) {
            unlink($path);
        }

        $this->artisan('grimba:archive-vault-events', [
            '--month' => '2026-05',
            '--path' => $path,
        ])
            ->assertExitCode(0);

        $csv = (string) file_get_contents($path);

        $this->assertStringContainsString('event,post_id,ts,ip_hash', $csv);
        $this->assertStringContainsString('save,111,"2026-05-03 10:00:00",' . str_repeat('a', 64), $csv);
        $this->assertStringContainsString('unsave,222,"2026-05-04 11:00:00",' . str_repeat('b', 64), $csv);
        $this->assertStringNotContainsString('333', $csv);

        unlink($path);
    }

    public function test_vault_events_archive_is_scheduled_weekly(): void
    {
        Artisan::call('schedule:list');

        $this->assertStringContainsString('grimba:archive-vault-events', Artisan::output());
    }
}
