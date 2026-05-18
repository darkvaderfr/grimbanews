<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

/**
 * S-MODE-10 — pin the theme-bootstrap contract across every
 * reader surface. The Wave HHH FOUC guard's job is to set three
 * theme attributes on `<html>` before the body parses, driven by
 * the `grimba_theme` cookie (+ a JS localStorage path that the
 * server can't observe in tests).
 *
 * If a future refactor changes the SSR cookie semantics or drops
 * one of the attributes, every dark-mode user sees a white flash
 * on every page load. These tests fail loud so we catch it before
 * production.
 *
 * The PwaShellTest covers `/` for these attrs in detail; this
 * sweep extends to the rest of the chrome-layout surfaces.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class GrimbaDarkModeContractTest extends TestCase
{
    /**
     * @return array<int, array{0: string}>
     */
    public static function readerSurfaces(): array
    {
        return [
            ['/'],
            ['/breaking'],
            ['/latest'],
            ['/dossiers'],
            ['/advertise'],
            ['/sources'],
        ];
    }

    /**
     * @dataProvider readerSurfaces
     */
    public function test_surface_renders_dark_mode_attrs_when_cookie_is_dark(string $path): void
    {
        $html = $this->withUnencryptedCookies(['grimba_theme' => 'dark'])
            ->get($path)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-bs-theme="dark"', $html, "$path must set data-bs-theme to dark when cookie says so.");
        $this->assertStringContainsString('data-grimba-theme-pref="dark"', $html, "$path must set data-grimba-theme-pref to dark.");
        // data-theme stays "light" by design (the chrome layout
        // comment: "stock Echo's data-theme='dark' CSS never bleeds
        // in; our own dark palette keys off data-bs-theme").
        $this->assertStringContainsString('data-theme="light"', $html, "$path must keep data-theme='light' so stock Echo dark CSS doesn't bleed.");
    }

    /**
     * @dataProvider readerSurfaces
     */
    public function test_surface_renders_light_mode_attrs_by_default(string $path): void
    {
        // No cookie → SSR defaults to light per the deterministic
        // policy PwaShellTest enforces.
        $html = $this->get($path)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-bs-theme="light"', $html);
        $this->assertStringContainsString('data-grimba-theme-pref="light"', $html);
    }

    /**
     * @dataProvider readerSurfaces
     */
    public function test_surface_carries_fouc_guard_script(string $path): void
    {
        // S-MODE-09 — the inline FOUC guard MUST be present so the
        // localStorage-set theme attaches BEFORE the body renders.
        // Look for a distinctive symbol from the guard's body —
        // the `echo-theme` localStorage key it reads.
        $html = $this->get($path)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString("localStorage.getItem('echo-theme')", $html, "$path must inline the FOUC guard.");
    }
}
