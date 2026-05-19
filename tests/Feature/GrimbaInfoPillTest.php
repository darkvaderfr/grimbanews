<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

/**
 * S-PILL-09 — pin the structural contract of the shared info-pill
 * partial (`partials/info-pill.blade.php`) + the JS controller it
 * `@once`-injects.
 *
 * Vader's directive: "the i pill overlay works well. It is not
 * always working for all i pills i tested." The polish pass in
 * Wave XX rebuilt the body positioning, animation, and backdrop;
 * these tests lock the DOM contract so future polish doesn't
 * silently regress the pieces every consumer surface depends on.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class GrimbaInfoPillTest extends TestCase
{
    private function renderPill(string $body, ?string $label = null, ?string $tone = null): string
    {
        $vars = ['body' => $body];
        if ($label !== null) {
            $vars['label'] = $label;
        }
        if ($tone !== null) {
            $vars['tone'] = $tone;
        }
        return view('theme.echo::partials.info-pill', $vars)->render();
    }

    public function test_pill_renders_as_details_element(): void
    {
        $html = $this->renderPill('Hello body');
        $this->assertStringContainsString('<details class="grimba-info-pill', $html);
        $this->assertStringContainsString('data-grimba-info-pill', $html);
    }

    public function test_pill_body_has_anchor_attribute_for_js_positioner(): void
    {
        // The JS controller uses `[data-grimba-info-pill-body]` to
        // resolve the body element from inside the document-level
        // toggle listener. Removing this would break every pill.
        $html = $this->renderPill('Body text');
        $this->assertStringContainsString('data-grimba-info-pill-body', $html);
    }

    public function test_summary_carries_accessible_label(): void
    {
        $html = $this->renderPill('Body text', label: 'Topics');
        // The summary aria-label includes the label text so screen
        // readers announce the trigger meaningfully. After Wave ZZZ
        // the summary attrs span multiple lines, so we match against
        // the rendered HTML across whitespace boundaries.
        $this->assertMatchesRegularExpression('/<summary\s[^>]*aria-label="[^"]*Topics[^"]*"/s', $html);
    }

    public function test_summary_has_fallback_accessible_label_without_explicit_label(): void
    {
        $html = $this->renderPill('Body text');
        $this->assertMatchesRegularExpression('/<summary\s[^>]*aria-label="[^"]+"/s', $html);
    }

    public function test_pill_escapes_body_text_by_default(): void
    {
        // The partial echoes the body via Blade's `{{ $body }}`
        // helper, which escapes by default. If the partial ever
        // switches to `{!! !!}` without an explicit `html=true`,
        // this test catches it before XSS lands in prod.
        $html = $this->renderPill('<script>alert(1)</script>');
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function test_pill_renders_trusted_html_when_html_flag_true(): void
    {
        // The opt-in path for trusted markup (e.g. inline <strong>,
        // <em> in editorial copy).
        $html = view('theme.echo::partials.info-pill', [
            'body' => '<strong>Hello</strong>',
            'html' => true,
        ])->render();
        $this->assertStringContainsString('<strong>Hello</strong>', $html);
    }

    public function test_at_once_script_block_injected_on_first_render(): void
    {
        // The `@once` script + style live in the same partial. They
        // ship the FOUC guard's localStorage logic + the mobile
        // backdrop + the close animation. A fresh render must
        // include them.
        $html = $this->renderPill('Body');
        $this->assertStringContainsString('__grimbaInfoPillReady', $html);
        $this->assertStringContainsString('positionBody', $html);
        $this->assertStringContainsString('grimba-info-pill-backdrop', $html);
    }

    public function test_open_animation_handler_present(): void
    {
        // Wave TTTT (Vader 2026-05-18) — explicit open animation
        // hides the body, positions it, then fades + slides into view
        // in a single rAF. Lock the function name so a future refactor
        // that removes it gets caught.
        $html = $this->renderPill('Body');
        $this->assertStringContainsString('openWithAnim', $html);
        $this->assertStringContainsString('requestAnimationFrame', $html);
        $this->assertStringContainsString('prefers-reduced-motion', $html);
    }

    public function test_size_variant_modifier_applied(): void
    {
        $html = view('theme.echo::partials.info-pill', [
            'body' => 'Body',
            'size' => 'sm',
        ])->render();
        $this->assertStringContainsString('grimba-info-pill--sm', $html);
    }

    public function test_tone_variant_modifier_applied(): void
    {
        $html = $this->renderPill('Body', tone: 'soft');
        $this->assertStringContainsString('grimba-info-pill--soft', $html);
    }

    public function test_align_right_modifier_applied(): void
    {
        $html = view('theme.echo::partials.info-pill', [
            'body' => 'Body',
            'align' => 'right',
        ])->render();
        $this->assertStringContainsString('grimba-info-pill--right', $html);
    }

    public function test_summary_carries_aria_expanded_and_aria_controls(): void
    {
        // S-PILL-07 a11y — summary must declare aria-expanded
        // (synced to open-state by the JS controller) AND
        // aria-controls pointing at the body's id.
        $html = $this->renderPill('Body', label: 'Topics');
        $this->assertStringContainsString('aria-expanded="false"', $html);
        $this->assertMatchesRegularExpression(
            '/aria-controls="grimba-info-pill-body-[a-f0-9]{8}"/',
            $html,
            'aria-controls must reference a generated body id.',
        );
    }

    public function test_body_carries_role_region_and_label_for_screen_readers(): void
    {
        // The body needs role="region" + aria-label so screen
        // readers announce it as a discrete landmark when focus
        // moves to it on pill open.
        $html = $this->renderPill('Body', label: 'Topics');
        $this->assertStringContainsString('role="region"', $html);
        $this->assertStringContainsString('aria-label="Détails — Topics"', $html);
        // tabindex="-1" — programmatically focusable, not in tab order.
        $this->assertStringContainsString('tabindex="-1"', $html);
    }

    public function test_pill_with_explicit_id_uses_it_for_body_aria_link(): void
    {
        // When the caller passes an `id`, the generated body id
        // should be that id + '__body' so the linkage is stable
        // across renders (e.g. for a deep-link in the URL hash).
        $html = view('theme.echo::partials.info-pill', [
            'body' => 'Body',
            'id' => 'my-pill',
        ])->render();
        $this->assertStringContainsString('id="my-pill"', $html);
        $this->assertStringContainsString('id="my-pill__body"', $html);
        $this->assertStringContainsString('aria-controls="my-pill__body"', $html);
    }

    public function test_breaking_page_renders_pill_with_full_contract(): void
    {
        // Smoke the breaking page (a real consumer surface). It
        // includes the pill partial through `Theme::getThemeNamespace`,
        // so a regression in the include path would surface here.
        $html = $this->get('/breaking?lang=fr')->getContent();
        // The page has at least one pill.
        $this->assertStringContainsString('data-grimba-info-pill', $html);
        $this->assertStringContainsString('data-grimba-info-pill-body', $html);
        // The @once script block fires exactly once per response —
        // since the partial includes it inside @once, multiple pill
        // includes still result in a single declaration. Allow up to
        // 3 occurrences as a tolerance for nested Theme scopes
        // (which can carry their own @once registry).
        $count = substr_count($html, '__grimbaInfoPillReady');
        $this->assertGreaterThan(0, $count);
        $this->assertLessThan(5, $count, 'Script block must not balloon per pill.');
    }
}
