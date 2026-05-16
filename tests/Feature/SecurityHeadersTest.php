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
}
