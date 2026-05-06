<?php

namespace Tests\Feature;

use App\Mail\GrimbaVaultDigestMail;
use App\Support\GrimbaVault;
use Botble\Member\Models\Member;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class VaultDigestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        DB::table('members')->update([
            'weekly_vault_digest' => false,
            'vault_digest_post_ids' => null,
            'vault_digest_synced_at' => null,
            'vault_digest_sent_at' => null,
        ]);
    }

    private function member(): Member
    {
        $member = Member::query()->first();

        $this->assertNotNull($member, 'Fixture database must contain at least one member account.');

        return $member;
    }

    private function publishedPost(): object
    {
        $post = DB::table('posts')
            ->where('status', 'published')
            ->whereNotNull('name')
            ->orderBy('id')
            ->first(['id', 'name']);

        $this->assertNotNull($post, 'Fixture database must contain at least one published post.');

        return $post;
    }

    public function test_member_can_toggle_weekly_vault_digest_from_account(): void
    {
        $member = $this->member();
        $post = $this->publishedPost();

        $this->actingAs($member, 'member')
            ->withUnencryptedCookies([GrimbaVault::COOKIE => (string) $post->id])
            ->post('/account/vault-digest', ['weekly_vault_digest' => '1'])
            ->assertRedirect('/account');

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'weekly_vault_digest' => 1,
            'vault_digest_post_ids' => (string) $post->id,
        ]);

        $this->actingAs($member, 'member')
            ->post('/account/vault-digest', ['weekly_vault_digest' => '0'])
            ->assertRedirect('/account');

        $row = DB::table('members')->where('id', $member->id)->first();

        $this->assertSame(0, (int) $row->weekly_vault_digest);
        $this->assertNull($row->vault_digest_post_ids);
    }

    public function test_logged_in_save_toggle_refreshes_opted_in_digest_snapshot(): void
    {
        $member = $this->member();
        $post = $this->publishedPost();

        DB::table('members')->where('id', $member->id)->update([
            'weekly_vault_digest' => true,
        ]);

        $this->actingAs($member, 'member')
            ->postJson('/coffre/toggle', ['post_id' => $post->id])
            ->assertOk()
            ->assertJsonPath('saved', true);

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'vault_digest_post_ids' => (string) $post->id,
        ]);
    }

    public function test_weekly_command_sends_digest_and_marks_member_sent(): void
    {
        Mail::fake();

        $member = $this->member();
        $post = $this->publishedPost();

        DB::table('members')->where('id', $member->id)->update([
            'weekly_vault_digest' => true,
            'vault_digest_post_ids' => (string) $post->id,
            'vault_digest_synced_at' => now(),
            'vault_digest_sent_at' => null,
        ]);

        $this->artisan('grimba:vault-digests')
            ->expectsOutput('Sent 1 vault digest(s); skipped 0.')
            ->assertExitCode(0);

        Mail::assertSent(GrimbaVaultDigestMail::class, function (GrimbaVaultDigestMail $mail) use ($member, $post): bool {
            return (int) $mail->member->id === (int) $member->id
                && $mail->posts->pluck('id')->map(fn ($id): int => (int) $id)->contains((int) $post->id);
        });

        $this->assertNotNull(DB::table('members')->where('id', $member->id)->value('vault_digest_sent_at'));
    }

    public function test_weekly_vault_digest_command_is_scheduled(): void
    {
        Artisan::call('schedule:list');

        $this->assertStringContainsString('grimba:vault-digests', Artisan::output());
    }
}
