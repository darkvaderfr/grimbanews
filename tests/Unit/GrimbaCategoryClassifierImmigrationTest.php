<?php

namespace Tests\Unit;

use App\Services\GrimbaCategoryClassifier;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Zen audit follow-up (Vader 2026-05-17) — pin the Immigration-before-
 * Société first-match precedence in `GrimbaCategoryClassifier`. Before
 * the fix, immigration / migrant / asile tokens were funneled into
 * Société (Immigration tag = 0 posts despite 20+ keyword matches).
 *
 * These assertions don't write to the DB — they call the public
 * classifier API directly with crafted fixtures.
 */
class GrimbaCategoryClassifierImmigrationTest extends TestCase
{
    private int $immigrationId = 0;
    private int $societeId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->immigrationId = (int) DB::table('categories')->where('name', 'Immigration')->value('id');
        $this->societeId     = (int) DB::table('categories')->where('name', 'Société')->value('id');
        if ($this->immigrationId === 0) {
            $this->markTestSkipped('Immigration category row not present in test DB.');
        }
    }

    public function test_immigration_keyword_resolves_to_immigration_category(): void
    {
        $classifier = new GrimbaCategoryClassifier();
        $categories = $classifier->classify('Immigration policy debate intensifies in Senate', null);
        $this->assertContains($this->immigrationId, $categories);
        $this->assertNotContains($this->societeId, $categories, 'Société should not double-tag for an Immigration-headline post');
    }

    public function test_french_asile_resolves_to_immigration(): void
    {
        $classifier = new GrimbaCategoryClassifier();
        $categories = $classifier->classify(
            "Demande d'asile : la Cimade défend les droits des étrangers",
            'Couverture des frontières et de la naturalisation.'
        );
        $this->assertContains($this->immigrationId, $categories);
    }

    public function test_migrant_token_resolves_to_immigration(): void
    {
        $classifier = new GrimbaCategoryClassifier();
        $categories = $classifier->classify('Migrant deaths in the Mediterranean climb again', null);
        $this->assertContains($this->immigrationId, $categories);
    }

    public function test_refugee_token_resolves_to_immigration(): void
    {
        $classifier = new GrimbaCategoryClassifier();
        $categories = $classifier->classify('Refugee resettlement program expands to 12 countries', null);
        $this->assertContains($this->immigrationId, $categories);
    }

    public function test_societe_keyword_still_resolves_to_societe(): void
    {
        // Make sure the Immigration rule didn't steal Société keywords.
        $classifier = new GrimbaCategoryClassifier();
        $categories = $classifier->classify(
            "Manifestation des étudiants pour l'éducation publique",
            'Famille, logement, école — les enjeux clés.'
        );
        $this->assertContains($this->societeId, $categories);
        $this->assertNotContains($this->immigrationId, $categories, 'Pure Société post should not pick up Immigration');
    }

    public function test_mixed_signals_pick_first_match(): void
    {
        // Title carries both Immigration AND Société tokens. First-match
        // wins → Immigration (per the rule order in the classifier).
        $classifier = new GrimbaCategoryClassifier();
        $categories = $classifier->classify("Logement des migrants : nouvelle politique de l'État", null);
        $this->assertContains($this->immigrationId, $categories);
    }
}
