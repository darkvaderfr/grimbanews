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
        $this->assertStringContainsString('@media (max-width: 767.98px)', $css);
        $this->assertStringContainsString('grid-template-columns: minmax(7rem, 42%) 1fr;', $css);
        $this->assertStringContainsString('body .grimba-admin-screen .btn-outline-danger', $css);
        $this->assertStringContainsString('body .grimba-admin-screen .btn-outline-warning', $css);
        $this->assertStringContainsString('background-size: 42px 42px, 42px 42px, auto, auto;', $css);

        $this->assertStringContainsString("window.localStorage.getItem('tablerTheme')", $js);
        $this->assertStringContainsString("window.localStorage.setItem('tablerTheme', mode)", $js);
        $this->assertStringContainsString('function currentMode(preferDom)', $js);
        $this->assertStringContainsString('applyMode(true)', $js);
        $this->assertStringContainsString("document.documentElement.setAttribute('data-bs-theme', effective)", $js);
        $this->assertStringContainsString("document.body.setAttribute('data-bs-theme', effective)", $js);
        $this->assertStringContainsString("document.body.removeAttribute('data-bs-theme')", $js);
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

    public function test_key_admin_queues_use_shared_empty_states(): void
    {
        $root = dirname(__DIR__, 2);
        $views = [
            '/resources/views/grimba-admin/rss-drafts/index.blade.php',
            '/resources/views/grimba-admin/newsapi/index.blade.php',
            '/resources/views/grimba-admin/subscribers/index.blade.php',
            '/resources/views/grimba-admin/news-sources/index.blade.php',
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
            '/resources/views/grimba-admin/story-clusters/index.blade.php',
        ];

        foreach ($views as $view) {
            $contents = file_get_contents($root . $view);

            $this->assertStringContainsString('grimba-admin-table-responsive', $contents, $view);
            $this->assertStringContainsString('grimba-admin-table', $contents, $view);
            $this->assertStringContainsString('data-label="Actions"', $contents, $view);
        }
    }
}
