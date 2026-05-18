<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class AdminChromeAssetsTest extends TestCase
{
    public function test_admin_chrome_assets_keep_dropdowns_readable_and_theme_synced(): void
    {
        $root = dirname(__DIR__, 2);
        $css = file_get_contents($root . '/public/themes/echo/css/grimba-admin.css');
        $js = file_get_contents($root . '/public/themes/echo/js/grimba-admin-theme.js');

        $this->assertStringContainsString('--gn-dropdown-bg:  rgba(255, 255, 255, 0.98);', $css);
        $this->assertStringContainsString('--gn-z-admin-header: 4000;', $css);
        $this->assertStringContainsString('--gn-z-admin-dropdown: 5000;', $css);
        $this->assertStringContainsString('backdrop-filter: none !important;', $css);
        $this->assertStringContainsString('z-index: var(--gn-z-admin-dropdown) !important;', $css);
        $this->assertStringContainsString('body .page-header .btn-list', $css);
        $this->assertStringContainsString('body .dropdown-menu.show[data-bs-popper]', $css);
        $this->assertStringContainsString('body[data-bs-theme="dark"] .navbar.navbar-expand-md.d-print-none', $css);
        $this->assertStringContainsString('body[data-bs-theme="dark"] {', $css);
        $this->assertStringContainsString('color-scheme: dark;', $css);
        $this->assertStringContainsString('body[data-bs-theme="dark"] .dropdown-menu:not(.navbar-vertical .dropdown-menu)', $css);
        $this->assertStringContainsString('body[data-bs-theme="dark"] .input-group-text', $css);
        $this->assertStringContainsString('body[data-bs-theme="dark"] .modal-content', $css);
        $this->assertStringContainsString('body[data-bs-theme="dark"] .nav-tabs .nav-link.active', $css);
        $this->assertStringContainsString('body .dropdown-menu .dropdown-item:hover', $css);
        $this->assertStringContainsString('body .navbar-vertical .dropdown-menu', $css);
        $this->assertStringContainsString('body .grimba-admin-hero::after', $css);
        $this->assertStringContainsString('body .grimba-admin-metric-value', $css);
        $this->assertStringContainsString('body .grimba-admin-actions', $css);
        $this->assertStringContainsString('body .grimba-admin-empty', $css);
        $this->assertStringContainsString('body .grimba-admin-empty__copy', $css);
        $this->assertStringContainsString('body .grimba-admin-empty__actions', $css);
        $this->assertStringContainsString('body .grimba-admin-table-responsive', $css);
        $this->assertStringContainsString('body .grimba-admin-table td[data-label]::before', $css);
        $this->assertStringContainsString('body .grimba-rss-feed-row--stale > *', $css);
        $this->assertStringContainsString('body .grimba-admin-form-section', $css);
        $this->assertStringContainsString('body .grimba-admin-form-section__hint', $css);
        $this->assertStringContainsString('body .grimba-admin-form-actions', $css);
        $this->assertStringContainsString('body .grimba-admin-screen .alert', $css);
        $this->assertStringContainsString('body .grimba-admin-screen .alert-warning', $css);
        $this->assertStringContainsString('body .grimba-admin-screen .alert-danger', $css);
        $this->assertStringContainsString('body[data-bs-theme="dark"] .grimba-admin-screen .alert-secondary', $css);
        $this->assertStringContainsString('body .grimba-admin-wayfinder', $css);
        $this->assertStringContainsString('body .grimba-admin-wayfinder a:hover', $css);
        $this->assertStringContainsString('body .grimba-admin-inline-actions', $css);
        $this->assertStringContainsString('body .grimba-admin-inline-actions .btn-sm', $css);
        $this->assertStringContainsString('@media (max-width: 767.98px)', $css);
        $this->assertStringContainsString('grid-template-columns: minmax(7rem, 42%) 1fr;', $css);
        $this->assertStringContainsString('body .grimba-admin-screen .btn-outline-danger', $css);
        $this->assertStringContainsString('body .grimba-admin-screen .btn-outline-warning', $css);
        $this->assertStringContainsString('background-size: 42px 42px, 42px 42px, auto, auto;', $css);

        $this->assertStringContainsString("window.localStorage.getItem('tablerTheme')", $js);
        $this->assertStringContainsString("window.localStorage.setItem('tablerTheme', mode)", $js);
        $this->assertStringContainsString('function disablePublicWorkerOnAdmin()', $js);
        $this->assertStringContainsString("window.location.pathname.match(/^\\/admin(?:\\/|$)/)", $js);
        $this->assertStringContainsString('navigator.serviceWorker.getRegistrations()', $js);
        $this->assertStringContainsString('function serverMode()', $js);
        $this->assertStringContainsString('a[href*="/toggle-theme-mode?theme="]', $js);
        $this->assertStringContainsString("if (href.indexOf('theme=dark') !== -1)", $js);
        $this->assertStringContainsString("if (server) {\n            return server;", $js);
        $this->assertStringContainsString('function currentMode(preferDom)', $js);
        $this->assertStringContainsString("if (stored) {\n            return stored;", $js);
        $this->assertStringContainsString("var cookie = cookieMode();", $js);
        $this->assertStringContainsString('applyMode(true)', $js);
        $this->assertStringContainsString("document.documentElement.setAttribute('data-bs-theme', effective)", $js);
        $this->assertStringContainsString("if (document.body.getAttribute('data-bs-theme') !== effective)", $js);
        $this->assertStringContainsString("document.body.setAttribute('data-bs-theme', effective)", $js);
        $this->assertStringContainsString("document.body.removeAttribute('data-bs-theme')", $js);
        $this->assertStringNotContainsString('window.setInterval(applyMode', $js);

        $chrome = file_get_contents($root . '/platform/themes/echo/functions/grimba-admin-chrome.php');
        $this->assertStringContainsString('/themes/echo/js/grimba-admin-theme.js?v=20260428.2', $chrome);
    }

    public function test_custom_grimba_admin_views_use_shared_shell(): void
    {
        $root = dirname(__DIR__, 2);
        $dir = $root . '/resources/views/grimba-admin';
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        $checked = 0;

        foreach ($it as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            // Blade partials (filenames starting with `_`) are
            // fragments included into pages — they don't carry the
            // shell themselves, so they're exempt from this check.
            if (str_starts_with($file->getFilename(), '_')) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            $this->assertMatchesRegularExpression(
                '/grimba-admin-screen|grimba-llm-admin|grimba-cockpit/',
                $contents,
                $file->getPathname() . ' must use the shared Grimba admin shell.'
            );
            $checked++;
        }

        $this->assertGreaterThan(10, $checked);
    }

    public function test_admin_layout_chrome_does_not_hardcode_dark_mode(): void
    {
        $root = dirname(__DIR__, 2);
        $partials = [
            '/platform/core/base/resources/views/layouts/vertical/partials/header.blade.php',
            '/platform/core/base/resources/views/layouts/vertical/partials/aside.blade.php',
            '/platform/core/base/resources/views/layouts/vertical/partials/navbar.blade.php',
            '/platform/core/base/resources/views/layouts/horizontal/partials/navbar.blade.php',
        ];

        foreach ($partials as $partial) {
            $contents = file_get_contents($root . $partial);

            $this->assertStringContainsString("AdminHelper::themeMode() === 'dark'", $contents, $partial);
            $this->assertStringNotContainsString("\n    data-bs-theme=\"dark\"\n", $contents, $partial);
        }
    }

    public function test_key_admin_queues_use_shared_empty_states(): void
    {
        $root = dirname(__DIR__, 2);
        $views = [
            '/resources/views/grimba-admin/rss-drafts/index.blade.php',
            '/resources/views/grimba-admin/vault-analytics/index.blade.php',
            '/resources/views/grimba-admin/newsapi/index.blade.php',
            '/resources/views/grimba-admin/subscribers/index.blade.php',
            '/resources/views/grimba-admin/news-sources/index.blade.php',
            '/resources/views/grimba-admin/news-sources/classification.blade.php',
            '/resources/views/grimba-admin/news-sources/triage.blade.php',
            '/resources/views/grimba-admin/cluster-review/index.blade.php',
            '/resources/views/grimba-admin/coverage-map/index.blade.php',
            '/resources/views/grimba-admin/story-clusters/index.blade.php',
        ];

        foreach ($views as $view) {
            $contents = file_get_contents($root . $view);

            $this->assertStringContainsString('grimba-admin-empty', $contents, $view);
            $this->assertStringContainsString('grimba-admin-empty__actions', $contents, $view);
        }
    }

    public function test_key_admin_tables_use_mobile_labels(): void
    {
        $root = dirname(__DIR__, 2);
        $views = [
            '/resources/views/grimba-admin/rss-drafts/index.blade.php',
            '/resources/views/grimba-admin/rss-feeds/index.blade.php',
            '/resources/views/grimba-admin/newsapi/index.blade.php',
            '/resources/views/grimba-admin/subscribers/index.blade.php',
            '/resources/views/grimba-admin/news-sources/index.blade.php',
            '/resources/views/grimba-admin/news-sources/classification.blade.php',
            '/resources/views/grimba-admin/news-sources/triage.blade.php',
            '/resources/views/grimba-admin/cluster-review/index.blade.php',
            '/resources/views/grimba-admin/coverage-map/index.blade.php',
            '/resources/views/grimba-admin/story-clusters/index.blade.php',
        ];

        foreach ($views as $view) {
            $contents = file_get_contents($root . $view);

            $this->assertStringContainsString('grimba-admin-table-responsive', $contents, $view);
            $this->assertStringContainsString('grimba-admin-table', $contents, $view);
            $this->assertStringContainsString('data-label="Actions"', $contents, $view);
            $this->assertStringContainsString('grimba-admin-inline-actions', $contents, $view);
        }
    }

    public function test_key_admin_forms_use_shared_form_sections(): void
    {
        $root = dirname(__DIR__, 2);
        $views = [
            '/resources/views/grimba-admin/news-sources/form.blade.php',
            '/resources/views/grimba-admin/rss-feeds/form.blade.php',
            '/resources/views/grimba-admin/story-clusters/form.blade.php',
            '/resources/views/grimba-admin/translation/index.blade.php',
            '/resources/views/grimba-admin/cookies/index.blade.php',
        ];

        foreach ($views as $view) {
            $contents = file_get_contents($root . $view);

            $this->assertStringContainsString('grimba-admin-form', $contents, $view);
            $this->assertStringContainsString('grimba-admin-form-section', $contents, $view);
            $this->assertStringContainsString('grimba-admin-wayfinder', $contents, $view);
        }
    }

    public function test_admin_cinematic_sok_checklist_is_recorded(): void
    {
        $root = dirname(__DIR__, 2);
        $doc = file_get_contents($root . '/docs/GRIMBANEWS_ADMIN_CINEMATIC_SOK.md');

        $this->assertStringContainsString('Decision:** Ship after S244', $doc);
        $this->assertStringContainsString('No translucent dropdowns', $doc);
        $this->assertStringContainsString('Dark/light parity', $doc);
        $this->assertStringContainsString('Clear wayfinding', $doc);
        $this->assertStringContainsString('The SOK outcome is **ship / continue**', $doc);
    }

    public function test_admin_production_readiness_smoke_is_recorded(): void
    {
        $root = dirname(__DIR__, 2);
        $doc = file_get_contents($root . '/docs/GRIMBANEWS_ADMIN_PROD_READINESS_SMOKE.md');

        $this->assertStringContainsString('No production deployment was run', $doc);
        $this->assertStringContainsString('php artisan grimba:health` passed', $doc);
        $this->assertStringContainsString('52` Grimba admin routes', $doc);
        $this->assertStringContainsString('php artisan test` passed with `50` tests and `754` assertions', $doc);
    }

    public function test_admin_deployment_checklist_is_recorded_without_deploying(): void
    {
        $root = dirname(__DIR__, 2);
        $doc = file_get_contents($root . '/docs/GRIMBANEWS_ADMIN_DEPLOYMENT_CHECKLIST.md');

        $this->assertStringContainsString('Production deployment is not run from this sprint', $doc);
        $this->assertStringContainsString('php artisan optimize:clear', $doc);
        $this->assertStringContainsString('/admin/grimba/cockpit', $doc);
        $this->assertStringContainsString('no destructive migration is required for rollback', $doc);
    }

    public function test_admin_visual_regression_routes_are_recorded(): void
    {
        $root = dirname(__DIR__, 2);
        $doc = file_get_contents($root . '/docs/GRIMBANEWS_ADMIN_VISUAL_REGRESSION_ROUTES.md');
        $routes = [
            '/admin/grimba/cockpit',
            '/admin/grimba/translation',
            '/admin/grimba/rss-drafts',
            '/admin/grimba/news-sources/triage',
            '/admin/grimba/coverage-map',
            '/admin/grimba/cookies',
        ];

        foreach ($routes as $route) {
            $this->assertStringContainsString($route, $doc);
        }

        $this->assertStringContainsString('Dropdowns are solid and above page content', $doc);
        $this->assertStringContainsString('Dense table rows become labeled cards on mobile', $doc);
    }

    public function test_grimba_admin_hero_copy_avoids_obvious_mixed_english(): void
    {
        $root = dirname(__DIR__, 2);
        $views = [
            '/resources/views/grimba-admin/newsapi/index.blade.php',
            '/resources/views/grimba-admin/coverage-map/index.blade.php',
            '/resources/views/grimba-admin/cookies/index.blade.php',
            '/resources/views/grimba-admin/cockpit.blade.php',
            '/resources/views/grimba-admin/rss-feeds/index.blade.php',
            '/resources/views/grimba-admin/vault-analytics/index.blade.php',
            '/resources/views/grimba-admin/rss-drafts/index.blade.php',
            '/resources/views/grimba-admin/story-clusters/index.blade.php',
            '/resources/views/grimba-admin/cluster-review/index.blade.php',
            '/resources/views/grimba-admin/subscribers/index.blade.php',
            '/resources/views/grimba-admin/news-sources/classification.blade.php',
            '/resources/views/grimba-admin/news-sources/triage.blade.php',
            '/resources/views/grimba-admin/news-sources/index.blade.php',
            '/resources/views/grimba-admin/story-clusters/form.blade.php',
            '/resources/views/grimba-admin/rss-feeds/form.blade.php',
            '/resources/views/grimba-admin/news-sources/form.blade.php',
            '/resources/views/grimba-admin/translation/index.blade.php',
        ];
        $forbidden = [
            'Command center',
            'RSS command lane',
            'RSS control tower',
            'Source health monitor',
            'Vault analytics',
            'Ingest engine',
            'Audience command',
            'Source intelligence',
            'Classification queue',
            'Cluster desk',
            'Story desk',
            'Source registry',
            'RSS intake',
            'Cookie banner',
            'Coverage map',
            'Edit source',
            'New source',
            'Edit feed',
            'New RSS feed',
            'Edit story cluster',
            'New story cluster',
            'NobuAI Provider Vault',
            'LLM keys and translation controls',
        ];

        foreach ($views as $view) {
            $contents = file_get_contents($root . $view);

            foreach ($forbidden as $phrase) {
                $this->assertStringNotContainsString($phrase, $contents, $view);
            }
        }
    }

    public function test_admin_backend_closeout_index_links_required_artifacts(): void
    {
        $root = dirname(__DIR__, 2);
        $doc = file_get_contents($root . '/docs/GRIMBANEWS_ADMIN_BACKEND_CLOSEOUT_INDEX.md');
        $required = [
            'GRIMBANEWS_ADMIN_CINEMATIC_SOK.md',
            'GRIMBANEWS_ADMIN_PROD_READINESS_SMOKE.md',
            'GRIMBANEWS_ADMIN_DEPLOYMENT_CHECKLIST.md',
            'GRIMBANEWS_ADMIN_VISUAL_REGRESSION_ROUTES.md',
            'GRIMBANEWS_SPRINT_PLAN.md',
        ];

        foreach ($required as $artifact) {
            $this->assertStringContainsString($artifact, $doc);
        }

        $this->assertStringContainsString('Deployment is intentionally deferred', $doc);
    }
}
