<?php

namespace Tests\Unit;

use App\Services\GrimbaLanguageDetector;
use PHPUnit\Framework\TestCase;

class GrimbaLanguageDetectorTest extends TestCase
{
    // ---------------------------------------------------------------
    // normalise()
    // ---------------------------------------------------------------

    public function test_normalise_iso2_passthrough(): void
    {
        $this->assertSame('fr', GrimbaLanguageDetector::normalise('fr'));
        $this->assertSame('en', GrimbaLanguageDetector::normalise('EN'));
        $this->assertSame('fr', GrimbaLanguageDetector::normalise('fr_CA'));
        $this->assertSame('en', GrimbaLanguageDetector::normalise('en-US'));
    }

    public function test_normalise_full_names(): void
    {
        $this->assertSame('fr', GrimbaLanguageDetector::normalise('French'));
        $this->assertSame('fr', GrimbaLanguageDetector::normalise('français'));
        $this->assertSame('fr', GrimbaLanguageDetector::normalise('francais'));
        $this->assertSame('fr', GrimbaLanguageDetector::normalise('FRA'));
        $this->assertSame('en', GrimbaLanguageDetector::normalise('English'));
        $this->assertSame('en', GrimbaLanguageDetector::normalise('eng'));
    }

    public function test_normalise_unknown_and_empty(): void
    {
        $this->assertNull(GrimbaLanguageDetector::normalise(''));
        $this->assertNull(GrimbaLanguageDetector::normalise(null));
        $this->assertNull(GrimbaLanguageDetector::normalise('unknown'));
        $this->assertNull(GrimbaLanguageDetector::normalise('klingon'));
        $this->assertNull(GrimbaLanguageDetector::normalise('es')); // out of scope
        $this->assertNull(GrimbaLanguageDetector::normalise(123)); // not a string
    }

    // ---------------------------------------------------------------
    // fromTld()
    // ---------------------------------------------------------------

    public function test_tld_french_signals(): void
    {
        $this->assertSame('fr', GrimbaLanguageDetector::fromTld('https://lemonde.fr/article/x'));
        $this->assertSame('fr', GrimbaLanguageDetector::fromTld('https://www.rfi.fr/'));
        // Note: a .com domain owned by a French/Senegalese publisher
        // (e.g. senego.com) intentionally returns 'en' from TLD alone —
        // the conservative allowlist won't override the .com signal.
        // A higher-precedence signal (caller_hint or source_language)
        // must correct it.
        $this->assertSame('en', GrimbaLanguageDetector::fromTld('http://senego.com/'));
    }

    public function test_tld_african_francophone(): void
    {
        $this->assertSame('fr', GrimbaLanguageDetector::fromTld('https://news.example.sn/'));
        $this->assertSame('fr', GrimbaLanguageDetector::fromTld('https://example.ci/path'));
        $this->assertSame('fr', GrimbaLanguageDetector::fromTld('https://lapresse.cd/'));
    }

    public function test_tld_english_signals(): void
    {
        $this->assertSame('en', GrimbaLanguageDetector::fromTld('https://www.bbc.co.uk/news'));
        $this->assertSame('en', GrimbaLanguageDetector::fromTld('https://nytimes.com/x'));
        $this->assertSame('en', GrimbaLanguageDetector::fromTld('https://abc.net.au/news'));
        $this->assertSame('en', GrimbaLanguageDetector::fromTld('https://punchng.com/x'));
    }

    public function test_tld_unknown_or_neutral(): void
    {
        $this->assertNull(GrimbaLanguageDetector::fromTld('https://example.io/x'));
        $this->assertNull(GrimbaLanguageDetector::fromTld(''));
        $this->assertNull(GrimbaLanguageDetector::fromTld('not-a-url'));
    }

    // ---------------------------------------------------------------
    // fromText()
    // ---------------------------------------------------------------

    public function test_text_clean_french_paragraph(): void
    {
        $fr = "Le président de la République a annoncé que la France et l'Italie travailleront ensemble sur le climat et la santé publique. C'est une étape importante pour les négociations.";
        $this->assertSame('fr', GrimbaLanguageDetector::fromText($fr));
    }

    public function test_text_clean_english_paragraph(): void
    {
        $en = "The president of the United States said the country and its allies will work together on climate and public health. It is an important step for the negotiations.";
        $this->assertSame('en', GrimbaLanguageDetector::fromText($en));
    }

    public function test_text_diacritics_alone_are_fr_signal(): void
    {
        $fr = "Café à Montréal — élections, débat, économie.";
        $this->assertSame('fr', GrimbaLanguageDetector::fromText($fr));
    }

    public function test_text_too_short_returns_null(): void
    {
        $this->assertNull(GrimbaLanguageDetector::fromText('hi'));
        $this->assertNull(GrimbaLanguageDetector::fromText(''));
        $this->assertNull(GrimbaLanguageDetector::fromText('Click here.'));
    }

    public function test_text_mixed_below_confidence_returns_null(): void
    {
        // 1 FR + 1 EN marker — too little signal, returns null
        $mixed = "Le market is open.";
        $this->assertNull(GrimbaLanguageDetector::fromText($mixed, 0.9));
    }

    // ---------------------------------------------------------------
    // detect() — signal precedence
    // ---------------------------------------------------------------

    public function test_detect_caller_hint_wins(): void
    {
        $this->assertSame('fr', GrimbaLanguageDetector::detect([
            'caller_hint' => 'fr',
            'source_language' => 'english', // contradicts but ignored
            'source_url' => 'https://nytimes.com',
            'text_sample' => 'This is clearly English text and many more words.',
        ]));
    }

    public function test_detect_falls_through_to_source_language(): void
    {
        $this->assertSame('en', GrimbaLanguageDetector::detect([
            'caller_hint' => null,
            'source_language' => 'English',
            'source_url' => 'https://lemonde.fr',
            'text_sample' => 'Le président parle à Paris pour la presse française.',
        ]));
    }

    public function test_detect_falls_through_to_tld(): void
    {
        $this->assertSame('fr', GrimbaLanguageDetector::detect([
            'source_url' => 'https://rfi.fr/africa/article',
        ]));
    }

    public function test_detect_falls_through_to_text(): void
    {
        $fr = 'Le gouvernement français annonce une nouvelle réforme du système de retraite. ';
        $fr .= "C'est une décision attendue par les syndicats et les organisations patronales.";
        $this->assertSame('fr', GrimbaLanguageDetector::detect([
            'source_url' => 'https://example.io',
            'text_sample' => $fr,
        ]));
    }

    public function test_detect_returns_null_when_nothing_signals(): void
    {
        $this->assertNull(GrimbaLanguageDetector::detect([
            'source_url' => 'https://example.io',
            'text_sample' => 'OK',
        ]));
        $this->assertNull(GrimbaLanguageDetector::detect([]));
    }

    public function test_detect_ignores_unknown_caller_hint(): void
    {
        // unknown caller_hint should NOT short-circuit; falls through.
        $this->assertSame('en', GrimbaLanguageDetector::detect([
            'caller_hint' => 'klingon',
            'source_url' => 'https://nytimes.com',
        ]));
    }

    // ---------------------------------------------------------------
    // Real-world fixtures — feed these through the full chain
    // ---------------------------------------------------------------

    public function test_fixture_lemonde_no_caller_no_source_lang(): void
    {
        $this->assertSame('fr', GrimbaLanguageDetector::detect([
            'source_url' => 'https://www.lemonde.fr/politique/article/...',
            'text_sample' => 'Le ministre annonce une nouvelle loi sur la justice.',
        ]));
    }

    public function test_fixture_bbc_no_caller_no_source_lang(): void
    {
        $this->assertSame('en', GrimbaLanguageDetector::detect([
            'source_url' => 'https://www.bbc.co.uk/news/world',
            'text_sample' => 'The prime minister said the country will face challenges ahead.',
        ]));
    }

    public function test_fixture_reuters_com_with_fr_caller_hint(): void
    {
        // .com defaults to en, but caller_hint wins.
        $this->assertSame('fr', GrimbaLanguageDetector::detect([
            'caller_hint' => 'fr',
            'source_url' => 'https://reuters.com/world/article',
            'text_sample' => 'Reuters said the meeting ended successfully.',
        ]));
    }

    public function test_fixture_african_fr_source_via_tld(): void
    {
        $this->assertSame('fr', GrimbaLanguageDetector::detect([
            'source_url' => 'https://wakatsera.com.bf/article/x', // .bf TLD
        ]));
    }

    public function test_fixture_thecitizen_tz_en_via_text(): void
    {
        // .tz TLD not in our allowlist; should fall to text scoring.
        $this->assertSame('en', GrimbaLanguageDetector::detect([
            'source_url' => 'https://thecitizen.co.tz/news',
            'text_sample' => 'The president of Tanzania said the country and its partners will work together.',
        ]));
    }

    public function test_fixture_aldjazair_dz_treats_as_fr_via_tld(): void
    {
        // Algeria — FR + AR media; we treat .dz as FR-dominant per the
        // allowlist. Caller hint can override if a specific feed is AR.
        $this->assertSame('fr', GrimbaLanguageDetector::detect([
            'source_url' => 'https://www.aps.dz/economie/article',
        ]));
    }

    public function test_fixture_caller_hint_es_returns_null(): void
    {
        // We only speak FR/EN. ES caller_hint is rejected.
        $this->assertNull(GrimbaLanguageDetector::detect([
            'caller_hint' => 'es',
            'source_url' => 'https://example.io',
        ]));
    }

    public function test_fixture_text_only_long_english(): void
    {
        $sample = str_repeat('The government said it is going to launch a new program for the people. ', 5);
        $this->assertSame('en', GrimbaLanguageDetector::detect([
            'text_sample' => $sample,
        ]));
    }
}
