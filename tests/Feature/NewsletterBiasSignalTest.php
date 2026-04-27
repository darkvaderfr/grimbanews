<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NewsletterBiasSignalTest extends TestCase
{
    public function test_newsletter_signup_snapshots_reader_bias_mix_from_cookie(): void
    {
        $ids = DB::table('posts')
            ->where('status', 'published')
            ->orderBy('id')
            ->limit(4)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertCount(4, $ids, 'Fixture database must contain at least four published posts.');

        DB::table('posts')->where('id', $ids[0])->update(['bias_rating' => 'left']);
        DB::table('posts')->where('id', $ids[1])->update(['bias_rating' => 'left']);
        DB::table('posts')->where('id', $ids[2])->update(['bias_rating' => 'right']);
        DB::table('posts')->where('id', $ids[3])->update(['bias_rating' => 'unknown']);

        DB::table('newsletter_subscriptions')->where('email', 'bias-signal@example.test')->delete();

        $this->withUnencryptedCookie('grimba_read', implode(',', $ids))
            ->from('/')
            ->post('/newsletter/subscribe', [
                'email' => 'bias-signal@example.test',
                'source_key' => 'feature_test',
            ])
            ->assertRedirect();

        $row = DB::table('newsletter_subscriptions')->where('email', 'bias-signal@example.test')->first();

        $this->assertNotNull($row);
        $this->assertSame(2, (int) $row->reader_bias_left);
        $this->assertSame(0, (int) $row->reader_bias_center);
        $this->assertSame(1, (int) $row->reader_bias_right);
        $this->assertSame(1, (int) $row->reader_bias_unknown);
        $this->assertSame('rebalance_center', $row->digest_variant);
    }
}
