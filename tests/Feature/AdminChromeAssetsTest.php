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

        $this->assertStringContainsString('--gn-dropdown-bg:  rgba(255, 255, 255, 0.96);', $css);
        $this->assertStringContainsString('--gn-z-admin-dropdown: 1090;', $css);
        $this->assertStringContainsString('backdrop-filter: none !important;', $css);
        $this->assertStringContainsString('z-index: var(--gn-z-admin-dropdown) !important;', $css);
        $this->assertStringContainsString('body[data-bs-theme="dark"] .navbar.navbar-expand-md.d-print-none', $css);
        $this->assertStringContainsString('body .dropdown-menu .dropdown-item:hover', $css);
        $this->assertStringContainsString('body .navbar-vertical .dropdown-menu', $css);

        $this->assertStringContainsString("window.localStorage.getItem('tablerTheme')", $js);
        $this->assertStringContainsString("document.documentElement.setAttribute('data-bs-theme', effective)", $js);
        $this->assertStringContainsString("document.body.setAttribute('data-bs-theme', effective)", $js);
        $this->assertStringContainsString("document.body.removeAttribute('data-bs-theme')", $js);
    }
}
