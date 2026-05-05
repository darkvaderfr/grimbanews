<?php

namespace Tests\Unit;

use App\Ground\Regions;
use Tests\TestCase;

/**
 * Fleet K — 4-region editorial split unit tests.
 */
class RegionsTest extends TestCase
{
    public function test_canonical_regions_are_valid(): void
    {
        $this->assertTrue(Regions::valid('africa'));
        $this->assertTrue(Regions::valid('europe'));
        $this->assertTrue(Regions::valid('americas'));
        $this->assertTrue(Regions::valid('international'));
    }

    public function test_invalid_region_keys_fail_validation(): void
    {
        $this->assertFalse(Regions::valid('asia'));
        $this->assertFalse(Regions::valid('uk'));
        $this->assertFalse(Regions::valid(''));
    }

    public function test_country_lists_have_expected_size(): void
    {
        // Per Fleet K plan: 54 / 49 / 35
        $this->assertCount(54, Regions::AFRICA);
        $this->assertCount(49, Regions::EUROPE);
        $this->assertCount(35, Regions::AMERICAS);
    }

    public function test_country_lists_have_no_overlap(): void
    {
        $africa   = Regions::AFRICA;
        $europe   = Regions::EUROPE;
        $americas = Regions::AMERICAS;

        $this->assertEmpty(array_intersect($africa, $europe), 'Africa and Europe must not overlap');
        $this->assertEmpty(array_intersect($africa, $americas), 'Africa and Americas must not overlap');
        $this->assertEmpty(array_intersect($europe, $americas), 'Europe and Americas must not overlap');
    }

    public function test_other_named_codes_is_union_with_no_dupes(): void
    {
        $expected = count(Regions::AFRICA) + count(Regions::EUROPE) + count(Regions::AMERICAS);
        $actual = count(Regions::otherNamedCodes());
        $this->assertSame($expected, $actual);
    }

    public function test_countries_lookup(): void
    {
        $this->assertSame(Regions::AFRICA, Regions::countries('africa'));
        $this->assertSame(Regions::EUROPE, Regions::countries('europe'));
        $this->assertSame(Regions::AMERICAS, Regions::countries('americas'));
        $this->assertNull(Regions::countries('international'));
        $this->assertNull(Regions::countries('garbage'));
    }

    public function test_legacy_cookie_migration(): void
    {
        // Legacy 6-region picker values fold into the 4 canonical keys.
        $this->assertSame('africa', Regions::migrate('afrique'));
        $this->assertSame('europe', Regions::migrate('europe'));
        $this->assertSame('europe', Regions::migrate('france'));
        $this->assertSame('europe', Regions::migrate('uk'));
        $this->assertSame('europe', Regions::migrate('gb'));
        $this->assertSame('americas', Regions::migrate('us'));
        $this->assertSame('americas', Regions::migrate('canada'));
        $this->assertSame('americas', Regions::migrate('amerique'));
        $this->assertSame('americas', Regions::migrate('amériques'));
        $this->assertSame('international', Regions::migrate('monde'));

        // Canonical values pass through.
        $this->assertSame('africa', Regions::migrate('africa'));
        $this->assertSame('international', Regions::migrate('international'));

        // Garbage falls back to international.
        $this->assertSame('international', Regions::migrate('asia'));
        $this->assertSame('international', Regions::migrate(''));
        $this->assertSame('international', Regions::migrate('zzzzz'));
    }

    public function test_labels_are_french(): void
    {
        $this->assertSame('Afrique',       Regions::label('africa'));
        $this->assertSame('Europe',        Regions::label('europe'));
        $this->assertSame('Amériques',     Regions::label('americas'));
        $this->assertSame('International', Regions::label('international'));
    }
}
