<?php

/*
 * GrimbaNews — admin chrome injection.
 *
 * Adds our paper tokens + Fraunces / Public Sans / JetBrains Mono
 * fonts + grimba-admin.css to every admin page, without forking
 * Botble's master template. Uses Botble's Assets façade.
 *
 * Also mirrors the grimba_theme cookie onto <html data-bs-theme>
 * so the admin dark-mode flips together with the reader.
 */

use Botble\Base\Facades\Assets;

app()->booted(function (): void {
    if (! class_exists(Assets::class)) {
        return;
    }

    $request = request();
    if (! $request) {
        return;
    }

    $adminPrefix = config('core.base.general.admin_dir', 'admin');

    // Only inject on admin surfaces.
    if (! $request->is($adminPrefix . '*')) {
        return;
    }

    Assets::addStylesDirectly([
        'https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Public+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap',
        '/themes/echo/css/grimba-admin.css',
    ]);

    // Inline script at the end of head: mirror grimba_theme cookie onto
    // <html data-bs-theme>, so admin dark mode matches the reader.
    Assets::addScriptsDirectly([
        '/themes/echo/js/grimba-admin-theme.js?v=20260428.2',
    ]);
});
