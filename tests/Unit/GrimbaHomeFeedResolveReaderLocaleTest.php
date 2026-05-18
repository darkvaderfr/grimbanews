<?php

namespace Tests\Unit;

use App\Support\GrimbaHomeFeed;
use Tests\TestCase;

/**
 * S-LSAT-04 — pin the contract of GrimbaHomeFeed::resolveReaderLocale().
 *
 * The method is the single source of truth for which locale a reader-
 * facing page renders against. Every cache key + strict filter on
 * the breaking / latest / home surfaces is partitioned by its return
 * value, so a silent drift here would either leak wrong-locale
 * content or fragment the cache to uselessness.
 */
class GrimbaHomeFeedResolveReaderLocaleTest extends TestCase
{
    protected function tearDown(): void
    {
        // Tests mutate request() globals; the framework boots a fresh
        // request between tests but we explicitly reset to be safe.
        request()->replace([]);
        request()->cookies->replace([]);
        parent::tearDown();
    }

    public function test_query_param_fr_resolves_to_fr(): void
    {
        request()->merge(['lang' => 'fr']);
        $this->assertSame('fr', GrimbaHomeFeed::resolveReaderLocale());
    }

    public function test_query_param_en_resolves_to_en(): void
    {
        request()->merge(['lang' => 'en']);
        $this->assertSame('en', GrimbaHomeFeed::resolveReaderLocale());
    }

    public function test_uppercase_query_param_normalizes_to_lowercase(): void
    {
        request()->merge(['lang' => 'FR']);
        $this->assertSame('fr', GrimbaHomeFeed::resolveReaderLocale());

        request()->merge(['lang' => 'En']);
        $this->assertSame('en', GrimbaHomeFeed::resolveReaderLocale());
    }

    public function test_array_query_param_does_not_crash_and_falls_through(): void
    {
        // Zen audit fix 2026-05-18: `?lang[]=fr&lang[]=en` parses
        // into an array. The pre-fix code crashed with
        // "Array to string conversion". The is_string() guard makes
        // it fall through to the cookie / framework default branch.
        request()->merge(['lang' => ['fr', 'en']]);
        $resolved = GrimbaHomeFeed::resolveReaderLocale();
        $this->assertContains($resolved, ['fr', 'en'], 'array input must not crash; falls through to fallback');
    }

    public function test_unsupported_locale_query_falls_through_to_default(): void
    {
        request()->merge(['lang' => 'de']);
        $resolved = GrimbaHomeFeed::resolveReaderLocale();
        $this->assertContains($resolved, ['fr', 'en'], 'unsupported locale must not be returned verbatim');
    }

    public function test_cookie_used_when_query_absent(): void
    {
        request()->cookies->set('grimba_lang', 'en');
        $this->assertSame('en', GrimbaHomeFeed::resolveReaderLocale());
    }

    public function test_query_param_precedes_cookie(): void
    {
        request()->cookies->set('grimba_lang', 'fr');
        request()->merge(['lang' => 'en']);
        $this->assertSame('en', GrimbaHomeFeed::resolveReaderLocale());
    }

    public function test_invalid_cookie_falls_through_to_framework_default(): void
    {
        request()->cookies->set('grimba_lang', 'xx-invalid');
        $resolved = GrimbaHomeFeed::resolveReaderLocale();
        $this->assertContains($resolved, ['fr', 'en']);
    }

    public function test_ultimate_fallback_clamps_unsupported_app_locale_to_fr(): void
    {
        // No query, no cookie, framework default unsupported (`es`).
        $previous = app()->getLocale();
        app()->setLocale('es');
        try {
            $this->assertSame('fr', GrimbaHomeFeed::resolveReaderLocale());
        } finally {
            app()->setLocale($previous);
        }
    }

    public function test_empty_string_query_falls_through_to_cookie(): void
    {
        request()->merge(['lang' => '']);
        request()->cookies->set('grimba_lang', 'en');
        $this->assertSame('en', GrimbaHomeFeed::resolveReaderLocale());
    }
}
