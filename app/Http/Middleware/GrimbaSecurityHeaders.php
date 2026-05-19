<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GrimbaSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->remove('Content-Security-Policy-Report-Only');

        $this->setMissing($response, 'Content-Security-Policy', $this->contentSecurityPolicy());
        $this->setMissing($response, 'X-Content-Type-Options', 'nosniff');
        $this->setMissing($response, 'X-Frame-Options', 'SAMEORIGIN');
        $this->setMissing($response, 'Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->setMissing($response, 'Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');

        // Wave ZZZZZ (Vader 2026-05-19) — HSTS for HTTPS requests.
        // Only emit when the connection is already secure (otherwise
        // HSTS is meaningless and gives MITM tooling a misleading
        // signal). Mozilla Observatory minimum: max-age=15552000
        // (180 days) + includeSubDomains. We don't set `preload`
        // until we're absolutely committed to permanent HTTPS — it's
        // hard to back out of the browser preload list.
        if ($request->isSecure()) {
            $this->setMissing(
                $response,
                'Strict-Transport-Security',
                'max-age=15552000; includeSubDomains'
            );
        }

        return $response;
    }

    private function setMissing(Response $response, string $name, string $value): void
    {
        if (! $response->headers->has($name)) {
            $response->headers->set($name, $value);
        }
    }

    private function contentSecurityPolicy(): string
    {
        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            // Echo/Botble still ship inline handlers and style blocks; keep
            // the allowances explicit until those templates are nonce-ready.
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https:",
            "style-src 'self' 'unsafe-inline' https:",
            "img-src 'self' data: blob: https: http:",
            "font-src 'self' data: https:",
            "connect-src 'self' https: http:",
            "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://maps.google.com https://www.google.com https://pagead2.googlesyndication.com https://googleads.g.doubleclick.net https://tpc.googlesyndication.com https://*.googlesyndication.com https://*.doubleclick.net https://*.google.com",
            "media-src 'self' https: http:",
            "manifest-src 'self'",
            "worker-src 'self' blob:",
        ];

        return implode('; ', $directives);
    }
}
