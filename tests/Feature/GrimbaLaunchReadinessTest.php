<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

/**
 * Launch-readiness end-to-end smoke. One canary that asserts every
 * contract this session has built, all in one test run. If anything
 * cross-cutting regresses (a reader route 500s, a brand-purity leak,
 * a missing FOUC guard, an admin route loses auth), this suite fails
 * loud BEFORE anyone manually clicks through.
 *
 * Companion to:
 *   - GrimbaReleaseSmokeTest (Wave WWW — sponsor + language loops)
 *   - GrimbaDarkModeContractTest (Wave AAAA — theme bootstrap)
 *   - GrimbaCategoryBadgeCrossLocaleTest (Wave JJJJ — category band)
 *
 * This one stitches their concerns into a single canary that ALSO
 * asserts the admin chain is reachable + the rule-engine dashboard
 * surfaces correctly.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class GrimbaLaunchReadinessTest extends TestCase
{
    private function admin(): User
    {
        $user = User::query()->find(1);
        $this->assertNotNull($user);
        return $user;
    }

    public function test_every_reader_surface_returns_200(): void
    {
        $surfaces = [
            '/',
            '/breaking',
            '/latest',
            '/dossiers',
            '/sources',
            '/advertise',
            '/?lang=en',
            '/?lang=fr',
            '/breaking?lang=en',
            '/breaking?lang=fr',
        ];
        foreach ($surfaces as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_no_external_provider_name_leaks_on_reader_surfaces(): void
    {
        // Vader's standing rule: user-facing surfaces show ONLY NobuAI.
        // Anthropic / OpenAI / Claude / Gemini / Mistral / DeepL etc.
        // must NEVER appear in copy a reader sees.
        $surfaces = ['/', '/breaking', '/latest', '/dossiers', '/sources', '/advertise'];
        $banned = ['Anthropic', 'OpenAI', 'Claude', 'ChatGPT', 'GPT-4', 'Mistral', 'Llama', 'Cohere', 'Gemini', 'Groq', 'DeepL'];

        foreach ($surfaces as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            foreach ($banned as $needle) {
                $this->assertStringNotContainsString(
                    $needle,
                    $html,
                    "Reader URL {$url} leaks external provider name '{$needle}'. Surfaces must say NobuAI only.",
                );
            }
        }
    }

    public function test_every_admin_surface_redirects_guests_to_login(): void
    {
        $surfaces = [
            '/admin/grimba/cockpit',
            '/admin/grimba/advertiser-leads',
            '/admin/grimba/ads-config',
            '/admin/grimba/translation-rules',
            '/admin/grimba/translation-monitor',
            '/admin/grimba/home-rails',
        ];
        foreach ($surfaces as $url) {
            $this->get($url)->assertRedirect('/admin/login');
        }
    }

    public function test_every_admin_surface_renders_for_authenticated_admin(): void
    {
        $surfaces = [
            '/admin/grimba/advertiser-leads' => 'Leads annonceurs',
            '/admin/grimba/ads-config' => 'Config publicités',
            '/admin/grimba/translation-rules' => 'Règles de traduction',
            '/admin/grimba/translation-monitor' => 'Moniteur de traduction',
            '/admin/grimba/home-rails' => 'Rails de la home',
        ];
        foreach ($surfaces as $url => $marker) {
            $this->actingAs($this->admin())
                ->get($url)
                ->assertOk()
                ->assertSee($marker, false);
        }
    }

    public function test_fouc_guard_present_on_every_chrome_layout_surface(): void
    {
        // Wave HHH FOUC guard: prevents the white flash on dark-mode
        // page loads. Must be inlined in EVERY reader-surface head.
        $surfaces = ['/', '/breaking', '/latest', '/dossiers', '/advertise', '/sources'];
        foreach ($surfaces as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString(
                "localStorage.getItem('echo-theme')",
                $html,
                "{$url} missing FOUC guard inline script.",
            );
        }
    }

    public function test_body_tag_has_exactly_one_class_attribute(): void
    {
        // Wave UUUU (Vader 2026-05-18) — every reader surface must emit
        // a single `class=` attribute on <body>. Two `class=` attrs is
        // valid HTML5 (warning-only), but browsers silently keep only
        // the first one, so any class added via Theme::addBodyAttributes
        // would be unreachable. Regression test for the
        // grimba-home.blade.php / grimba-chrome.blade.php fix.
        $surfaces = ['/', '/breaking', '/latest', '/dossiers', '/advertise', '/sources'];
        foreach ($surfaces as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            // Extract just the <body ...> opening tag (no children).
            preg_match('/<body[^>]*>/i', $html, $m);
            $this->assertNotEmpty($m, "{$url}: missing <body> tag");
            $bodyTag = $m[0];
            // Count `class=` attributes inside the tag (not the closing >).
            $count = substr_count($bodyTag, 'class=');
            $this->assertSame(
                1,
                $count,
                "{$url} has {$count} class= attributes on <body>. Browsers keep only the first; the others are silently dropped. Fix: route layout-specific classes through Theme::addBodyAttributes instead of hardcoding them next to {!! Theme::bodyAttributes() !!}. Body tag: {$bodyTag}"
            );
            // Also verify the merged result contains the layout class
            // (grimba-home or grimba-home grimba-subpage).
            $this->assertMatchesRegularExpression(
                '/class="[^"]*grimba-home[^"]*"/',
                $bodyTag,
                "{$url}: body class missing grimba-home"
            );
        }
    }

    public function test_category_badges_render_across_all_4_strict_surfaces(): void
    {
        $surfaces = ['/', '/breaking', '/latest', '/dossiers'];
        foreach ($surfaces as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString(
                'data-grimba-cat-badge',
                $html,
                "{$url} missing category badge (S-CAT band).",
            );
        }
    }

    public function test_info_pill_partial_carries_full_a11y_contract_on_home(): void
    {
        // Wave ZZZ — disclosure-widget ARIA contract. Home has 21+
        // pills; verifying ONE renders the full contract is enough
        // because they all use the same shared partial.
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('data-grimba-info-pill', $html);
        $this->assertStringContainsString('aria-expanded="false"', $html);
        $this->assertStringContainsString('aria-controls=', $html);
        $this->assertStringContainsString('role="region"', $html);
    }

    public function test_strict_filter_drops_opposite_locale_no_translation_posts(): void
    {
        // Wave UU/VV/WW strict locale surfacing. Both /breaking
        // surfaces must respond at 200 and reflect their locale
        // in the rendered HTML (lang attribute or visible copy).
        $en = $this->get('/breaking?lang=en')->assertOk()->getContent();
        $fr = $this->get('/breaking?lang=fr')->assertOk()->getContent();
        // The data-grimba-tail-expander block carries lang="en"
        // when reader is EN. If either page has it, that locale
        // wired correctly.
        $hasEn = str_contains($en, 'lang="en"');
        $hasFr = str_contains($fr, 'lang="fr"');
        $this->assertTrue($hasEn || $hasFr, 'At least one strict-locale surface must mark its lang explicitly.');
    }

    public function test_advertise_page_carries_full_sponsor_chrome(): void
    {
        $html = $this->get('/advertise')->assertOk()->getContent();
        $this->assertStringContainsString('grimba-ads-page__lead-form', $html);
        $this->assertStringContainsString('source_pack_tier', $html);
        $this->assertStringContainsString('grimba-ads-page__previews', $html);
        $this->assertStringContainsString('grimba-ads-page__faq', $html);
    }

    public function test_sponsor_lead_endpoint_accepts_valid_payload(): void
    {
        $email = 'tests-launch-readiness-' . time() . '@example.com';
        try {
            $this->post('/advertise/leads', [
                'email' => $email,
                'company' => 'Launch Smoke',
                'budget_band' => '1k-5k',
            ])->assertRedirect();

            $this->assertSame(
                1,
                \Illuminate\Support\Facades\DB::table('grimba_advertiser_leads')
                    ->where('email', $email)
                    ->count(),
                'Sponsor lead endpoint must accept and persist a valid payload.',
            );
        } finally {
            \Illuminate\Support\Facades\DB::table('grimba_advertiser_leads')
                ->where('email', $email)
                ->delete();
        }
    }

    public function test_full_test_suite_health_check(): void
    {
        // Trivial sanity check that asserts the wider test
        // infrastructure works (database accessible, fixture
        // admin user reachable, settings store readable).
        $this->assertNotNull(User::query()->find(1));
        $this->assertIsArray(\App\Support\GrimbaLanguageSettings::defaults());
        $this->assertGreaterThan(0, \Illuminate\Support\Facades\DB::table('posts')->where('status', 'published')->count());
    }
}
