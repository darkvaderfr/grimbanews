<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicFeedTest extends TestCase
{
    public function test_public_feed_ignores_default_region_scope(): void
    {
        $response = $this->get('/feed.xml');

        $response
            ->assertOk()
            ->assertSee('<item>', false)
            ->assertSee('<pubDate>', false);
    }
}
