<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_public_reader_routes_ship_enforced_csp_headers(): void
    {
        $response = $this->withUnencryptedCookies([
            'grimba_lang' => 'en',
            'grimba_onboarded' => '1',
        ])->get('/');

        $response->assertOk();

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertNotSame('', $csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("script-src 'self' 'unsafe-inline'", $csp);
        $this->assertStringContainsString('googlesyndication.com', $csp);
        $this->assertStringContainsString('doubleclick.net', $csp);
        $this->assertNull($response->headers->get('Content-Security-Policy-Report-Only'));

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');
    }

    public function test_json_reader_endpoints_carry_the_same_csp(): void
    {
        $response = $this->withUnencryptedCookies([
            'grimba_lang' => 'en',
            'grimba_onboarded' => '1',
        ])->get('/command-palette.json');

        $response->assertOk();
        $this->assertStringContainsString(
            "default-src 'self'",
            (string) $response->headers->get('Content-Security-Policy')
        );
    }

    public function test_hsts_emitted_on_https_requests_only(): void
    {
        // Wave ZZZZZ (Vader 2026-05-19) — HSTS lock. The middleware
        // only emits Strict-Transport-Security when the request is
        // already secure (`$request->isSecure()`). This test covers
        // both branches:

        // 1. HTTP request → NO HSTS header (meaningless on plaintext
        //    + misleading to MITM tooling).
        $http = $this->get('http://localhost/');
        $http->assertOk();
        $this->assertNull(
            $http->headers->get('Strict-Transport-Security'),
            'HSTS must NOT be emitted over plaintext HTTP.'
        );

        // 2. HTTPS request → HSTS with documented max-age +
        //    includeSubDomains, NO preload.
        $https = $this->get('https://localhost/');
        $https->assertOk();
        $hsts = (string) $https->headers->get('Strict-Transport-Security');
        $this->assertNotSame('', $hsts, 'HSTS must be emitted over HTTPS.');
        $this->assertStringContainsString('max-age=15552000', $hsts);
        $this->assertStringContainsString('includeSubDomains', $hsts);
        $this->assertStringNotContainsString(
            'preload',
            $hsts,
            'Do not set HSTS preload until permanent-HTTPS commitment is explicit.'
        );
    }
}
