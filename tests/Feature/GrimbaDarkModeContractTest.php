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

    /**
     * Wave CCCCC (Vader 2026-05-18) — assert each reader surface in
     * dark mode has NO hardcoded white-ish inline backgrounds that
     * would create a "cream paper card floating on dark canvas"
     * visual bug. The audit caught 3 such cases on home before
     * Waves VVVV/WWWW/XXXX/ZZZZ/AAAAA flipped them.
     *
     * @dataProvider readerSurfaces
     */
    public function test_surface_has_no_hardcoded_white_inline_backgrounds_in_dark(string $path): void
    {
        $html = $this->withUnencryptedCookies(['grimba_theme' => 'dark'])
            ->get($path)
            ->assertOk()
            ->getContent();

        // Strip out the legitimate-by-design dark-canvas overlays
        // where `#fff` is the FOREGROUND, not background — primarily
        // the `color: #fff` cases on always-dark (.grimba-blind-card,
        // .grimba-hero overlays) and the breaking ticker eyebrow.
        // We're specifically hunting inline `style="...background...
        // #fff..."` reachable on a real reader surface.
        preg_match_all(
            '/style="[^"]*background[^;"]*(?:#fffaf[0-1]|#fff7e8|#fffff[a-f0-9]?)[^"]*"/i',
            $html,
            $hits,
        );

        // Filter out hits where the same style includes `color:#000`
        // or `data-on-dark` — those are intentional dark-canvas
        // overlays that USE light backgrounds for high-contrast CTAs
        // (e.g. the breaking page CTA in WAVE VVVV).
        $real = array_filter($hits[0] ?? [], function (string $s): bool {
            return ! str_contains($s, 'color: #000')
                && ! str_contains($s, '#14110d')
                && ! str_contains($s, '#1a1713');
        });

        $this->assertSame(
            [],
            array_values($real),
            "{$path} in dark mode renders unbacked light-cream inline backgrounds. Wave VVVV/WWWW pattern: flip these to dark via a [data-bs-theme=\"dark\"] override. Hits: " . implode(' | ', $real)
        );
    }

    /**
     * Wave CCCCC — verify the duplicate-body-class fix from UUUU
     * hasn't regressed under the dark-cookie path. Browsers silently
     * drop the second `class=` attr, so any reintroduction would
     * make the dark-mode body class unreachable.
     *
     * @dataProvider readerSurfaces
     */
    public function test_dark_cookie_path_emits_single_body_class(string $path): void
    {
        $html = $this->withUnencryptedCookies(['grimba_theme' => 'dark'])
            ->get($path)
            ->assertOk()
            ->getContent();

        preg_match('/<body[^>]*>/i', $html, $m);
        $this->assertNotEmpty($m, "{$path}: missing <body> tag");
        $count = substr_count($m[0], 'class=');
        $this->assertSame(
            1,
            $count,
            "{$path} (dark cookie) has {$count} class= attributes on <body> — Wave UUUU regression."
        );
    }
}
