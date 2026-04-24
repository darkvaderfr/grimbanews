<?php

/**
 * GrimbaNews — dev-server router for `php -S`.
 *
 * PHP's built-in server (`php -S host:port -t public`) will happily
 * 404 any request with a known file extension (.xml, .json, .png)
 * when no matching file is on disk — it never forwards those to
 * public/index.php. That silently broke /feed.xml locally even
 * though the Route::get('feed.xml', …) was perfectly wired.
 *
 * This router mirrors what Laravel's `artisan serve` does under
 * the hood: serve real public/ files as-is, forward everything else
 * through public/index.php.
 *
 * Usage:
 *     php -S 127.0.0.1:8000 -t public server.php
 *
 * (Or keep using `php artisan serve` — either works now.)
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$publicPath = __DIR__ . '/public' . $uri;

// If the request is for a real file in public/, let the built-in
// server serve it directly (images, JS, CSS, fonts, etc.).
if ($uri !== '/' && file_exists($publicPath) && ! is_dir($publicPath)) {
    return false;
}

// Otherwise, route through Laravel.
require_once __DIR__ . '/public/index.php';
