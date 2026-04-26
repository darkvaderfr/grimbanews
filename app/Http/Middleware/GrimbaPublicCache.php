<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GrimbaPublicCache
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! in_array($request->method(), ['GET', 'HEAD'], true) || $response->getStatusCode() !== 200) {
            return $response;
        }

        $path = trim($request->path(), '/');
        $cacheable = $path === ''
            || $path === 'sources'
            || str_starts_with($path, 'sources/')
            || $path === 'comparatif'
            || str_starts_with($path, 'comparatif/');

        if (! $cacheable) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'public, max-age=300, s-maxage=900');
        $response->headers->set('Vary', 'Cookie, Accept-Encoding');

        return $response;
    }
}
