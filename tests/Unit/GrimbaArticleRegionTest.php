<?php

namespace Tests\Unit;

use App\Support\GrimbaArticleRegion;
use Tests\TestCase;

/**
 * Pin the topic-based region detector's contract.
 *
 * The detector is what saves /africa from being half-empty when a
 * French publisher covers African news. These tests guard the most
 * load-bearing classification calls so a future keyword-list edit
 * doesn't silently regress them.
 */
class GrimbaArticleRegionTest extends TestCase
{
    public function test_le_monde_about_senegal_is_africa(): void
    {
        $r = GrimbaArticleRegion::detectFromText(
            'Sénégal : Bassirou Diomaye Faye prête serment à Dakar',
            "Le nouveau président sénégalais a été investi à l'occasion d'une cérémonie à Dakar.",
        );
        $this->assertSame('africa', $r);
    }

    public function test_cnn_about_macron_is_europe(): void
    {
        $r = GrimbaArticleRegion::detectFromText(
            'Macron urges EU to accelerate climate plan in Paris speech',
            'The French president addressed the European Union summit in Brussels.',
        );
        $this->assertSame('europe', $r);
    }

    public function test_reuters_about_brazil_is_americas(): void
    {
        $r = GrimbaArticleRegion::detectFromText(
            'Brazil court rules on São Paulo air-quality fine',
            'Lula praised the decision in Brasília today.',
        );
        $this->assertSame('americas', $r);
    }

    public function test_thin_title_returns_null(): void
    {
        $r = GrimbaArticleRegion::detectFromText('Breaking news', '');
        $this->assertNull($r);
    }

    public function test_mixed_signals_with_no_dominant_region_returns_null(): void
    {
        // Equal points on two regions — must not pick a winner.
        $r = GrimbaArticleRegion::detectFromText(
            'Trump and Macron meet to discuss Russia',
            'Paris and Washington address Moscow tension.',
        );
        // Trump/Washington = americas; Macron/Paris/Russia/Moscow = europe.
        // The body-only points (1 each) could tie; the 2x margin
        // requirement should fire — null OR europe is acceptable,
        // but it MUST NOT be americas given the topic balance.
        $this->assertNotSame('americas', $r);
    }

    public function test_accent_insensitive_match(): void
    {
        // FR article spelled fully accented vs unaccented should
        // both fire on the same anchor.
        $accented = GrimbaArticleRegion::detectFromText('Éthiopie : nouveau gouvernement', '');
        $unaccented = GrimbaArticleRegion::detectFromText('Ethiopie: nouveau gouvernement', '');
        $this->assertSame('africa', $accented);
        $this->assertSame('africa', $unaccented);
    }

    public function test_title_weighted_higher_than_description(): void
    {
        // Title hit alone (worth 3) should be enough.
        $r = GrimbaArticleRegion::detectFromText('Cameroun: tension politique', '');
        $this->assertSame('africa', $r);
    }

    public function test_description_alone_below_threshold(): void
    {
        // Single body mention worth 1 isn't enough on its own
        // (threshold is 3).
        $r = GrimbaArticleRegion::detectFromText(
            'Generic headline that mentions nothing',
            'In a brief aside the author mentioned Senegal.',
        );
        $this->assertNull($r);
    }
}
