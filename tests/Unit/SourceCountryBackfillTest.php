<?php

namespace Tests\Unit;

use App\Support\GrimbaSourceCountryBackfill;
use PHPUnit\Framework\TestCase;

class SourceCountryBackfillTest extends TestCase
{
    public function test_infers_country_from_domain_maps_and_country_tlds(): void
    {
        $this->assertSame('GB', GrimbaSourceCountryBackfill::infer('BBC News', 'https://www.bbc.com/news')['country']);
        $this->assertSame('GB', GrimbaSourceCountryBackfill::infer('Daily Mail', 'dailymail.co.uk/news')['country']);
        $this->assertSame('CA', GrimbaSourceCountryBackfill::infer('CBC News', 'cbc.ca/news')['country']);
        $this->assertSame('FR', GrimbaSourceCountryBackfill::infer('Example France', 'example.fr')['country']);
        $this->assertSame('BF', GrimbaSourceCountryBackfill::infer('Le Pays', 'lepays.bf')['country']);
        $this->assertSame('US', GrimbaSourceCountryBackfill::infer('FDA.gov', null)['country']);
        $this->assertSame('FR', GrimbaSourceCountryBackfill::infer('Telerama.fr', null)['country']);
    }

    public function test_does_not_guess_generic_or_global_sources(): void
    {
        $this->assertNull(GrimbaSourceCountryBackfill::infer('Reuters', 'reuters.com'));
        $this->assertNull(GrimbaSourceCountryBackfill::infer('Unknown Blog', 'example.com'));
    }

    public function test_normalizes_only_iso2_country_codes(): void
    {
        $this->assertSame('FR', GrimbaSourceCountryBackfill::normalizeCountry(' fr '));
        $this->assertNull(GrimbaSourceCountryBackfill::normalizeCountry('France'));
    }
}
