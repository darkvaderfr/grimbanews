<?php

namespace Tests\Unit;

use App\Support\GrimbaRssFeedHealth;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class RssFeedHealthTest extends TestCase
{
    public function test_scores_active_stale_and_sick_feeds(): void
    {
        $healthy = (object) [
            'is_active' => true,
            'consecutive_failures' => 0,
            'last_polled_at' => Carbon::now()->subHour(),
            'last_success_at' => Carbon::now()->subHour(),
            'items_ingested' => 12,
        ];

        $stale = (object) [
            'is_active' => true,
            'consecutive_failures' => 2,
            'last_polled_at' => Carbon::now()->subHour(),
            'last_success_at' => Carbon::now()->subDays(2),
            'items_ingested' => 8,
        ];

        $sick = (object) [
            'is_active' => true,
            'consecutive_failures' => 6,
            'last_polled_at' => Carbon::now()->subHour(),
            'last_success_at' => null,
            'items_ingested' => 0,
        ];

        $this->assertSame(100, GrimbaRssFeedHealth::score($healthy));
        $this->assertSame('Sain', GrimbaRssFeedHealth::label($healthy));

        $this->assertTrue(GrimbaRssFeedHealth::isStale($stale));
        $this->assertSame('Sans succes', GrimbaRssFeedHealth::label($stale));

        $this->assertTrue(GrimbaRssFeedHealth::isSick($sick));
        $this->assertSame('Malade', GrimbaRssFeedHealth::label($sick));
        $this->assertLessThan(40, GrimbaRssFeedHealth::score($sick));
    }
}
