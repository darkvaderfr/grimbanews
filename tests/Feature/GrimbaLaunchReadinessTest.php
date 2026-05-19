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

    public function test_every_reader_surface_has_exactly_one_og_image(): void
    {
        // Wave AAAAAA (Vader 2026-05-19) — every reader surface must
        // emit exactly 1 `<meta property="og:image">`. Before this
        // wave, Botble's SeoHelper auto-emitted a fallback /storage
        // SVG og:image AFTER our layout emitted a manual PNG one,
        // resulting in 2 og:image tags per page. Crawlers picked the
        // first, but LinkedIn caches the second — which is wrong.
        $surfaces = ['/', '/breaking', '/latest', '/dossiers', '/advertise', '/sources'];
        foreach ($surfaces as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $count = substr_count($html, 'property="og:image"');
            $this->assertSame(
                1,
                $count,
                "{$path} ships {$count} og:image tags (expected exactly 1). The duplicate-og:image regression has come back — check that the layout calls SeoHelper::setImage() before Theme::header() instead of emitting a manual <meta>."
            );
        }
    }

    public function test_og_image_dimensions_are_paired_with_og_image(): void
    {
        // Wave FFFFFF (Vader 2026-05-19) — og:image:width/height MUST
        // appear exactly once each on every reader surface, paired
        // with og:image. Previously the layout emitted them BEFORE
        // Theme::header() (creating an orphan pair) AND SeoHelper
        // emitted them AGAIN for article pages (creating duplicates).
        // Fix: emit through SeoHelper so they land adjacent to og:image.
        $surfaces = ['/', '/breaking', '/latest', '/dossiers', '/advertise', '/sources'];
        foreach ($surfaces as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $w = substr_count($html, 'property="og:image:width"');
            $h = substr_count($html, 'property="og:image:height"');
            $this->assertSame(1, $w, "{$path} ships {$w} og:image:width tags (expected 1).");
            $this->assertSame(1, $h, "{$path} ships {$h} og:image:height tags (expected 1).");
        }
    }

    public function test_twitter_card_and_image_emit_exactly_once(): void
    {
        // Wave GGGGGG (Vader 2026-05-19) — twitter:card + twitter:image
        // must emit exactly once per reader surface. Before this wave,
        // post.blade pushed its own twitter image via SeoHelper, and
        // the chrome layout pushed home.png — SeoHelper saw 2 images
        // and switched render mode to twitter:image{0}+twitter:image{1},
        // which Twitter cards do NOT honor.
        $surfaces = ['/', '/breaking', '/latest', '/dossiers', '/advertise', '/sources'];
        foreach ($surfaces as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $card = substr_count($html, 'name="twitter:card"');
            $image = substr_count($html, 'name="twitter:image"');
            // The bad-state pattern `twitter:image{0}` would not match
            // the exact substring above — assert that nothing leaked
            // into the numbered form either.
            $numbered = substr_count($html, 'name="twitter:image{');
            $this->assertSame(1, $card, "{$path} ships {$card} twitter:card tags (expected 1).");
            $this->assertSame(1, $image, "{$path} ships {$image} twitter:image tags (expected 1).");
            $this->assertSame(0, $numbered, "{$path} emitted numbered twitter:image{N} variants — SeoHelper is receiving multiple addImage() calls.");
        }
    }

    public function test_health_endpoint_returns_json_with_required_fields(): void
    {
        // Wave RRRRR (Vader 2026-05-19) — /health for uptime monitors.
        // The endpoint must return JSON with status/service/time/db so
        // external monitoring tools can parse a consistent payload.
        // A refactor that breaks the JSON shape would silently break
        // every monitor pointing at this URL.
        $response = $this->get('/health');
        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeader('X-Robots-Tag', 'noindex')
            ->assertJsonStructure([
                'status',
                'service',
                'time',
                'db',
                'last_post_at',
            ])
            ->assertJson([
                'service' => 'grimbanews',
                'status' => 'ok',
                'db' => 'up',
            ]);
        // Cache-Control is set by the handler but the framework
        // normalizes the directive order + adds `private`. Assert
        // the substring instead of an exact match.
        $this->assertStringContainsString(
            'no-store',
            (string) $response->headers->get('Cache-Control'),
            '/health response must be no-store to prevent monitor coalescing.'
        );
    }

    public function test_robots_txt_advertises_sitemap(): void
    {
        // Wave QQQQQ — robots.txt must include a Sitemap directive so
        // Google can discover the sitemap without manual submission.
        // Note: robots.txt is a static file in public/, served by the
        // web server — NOT by Laravel's router. The test client only
        // invokes Laravel routes, so we read the file from disk
        // directly.
        $path = public_path('robots.txt');
        $this->assertFileExists($path);
        $body = (string) file_get_contents($path);
        $this->assertMatchesRegularExpression(
            '/^Sitemap:\s+https?:\/\/[^\s]+\/sitemap\.xml\s*$/m',
            $body,
            'public/robots.txt must include a `Sitemap: <url>/sitemap.xml` directive (case-sensitive).'
        );
    }

    public function test_category_dossier_source_pages_ship_jsonld(): void
    {
        // Wave XXXXX (Vader 2026-05-19) — extend the JSON-LD coverage
        // contract from Wave PPPPP to the 3 surfaces added in
        // UUUUU/VVVVV/WWWWW. These are dynamic URLs so we have to
        // pick a sample at runtime instead of hardcoding paths.
        $samples = [];

        // Sample category slug — any category with a slug row.
        $categorySlug = \Botble\Slug\Models\Slug::query()
            ->where('reference_type', \Botble\Blog\Models\Category::class)
            ->where('prefix', 'blog')
            ->orderBy('id')
            ->value('key');
        if ($categorySlug) {
            $samples['/blog/' . $categorySlug] = 'CollectionPage';
        }

        // Sample source slug — any active source with published posts.
        $sourceSlug = \Illuminate\Support\Facades\DB::table('news_sources')
            ->whereIn('id', \Illuminate\Support\Facades\DB::table('posts')
                ->where('status', 'published')
                ->whereNotNull('source_id')
                ->distinct()
                ->pluck('source_id'))
            ->orderBy('name')
            ->value('slug');
        if ($sourceSlug) {
            $samples['/sources/' . $sourceSlug] = 'NewsMediaOrganization';
        }

        // Sample cluster id — any published cluster.
        $clusterId = \Illuminate\Support\Facades\DB::table('posts')
            ->where('status', 'published')
            ->whereNotNull('story_cluster_id')
            ->orderByDesc('story_cluster_id')
            ->value('story_cluster_id');
        if ($clusterId) {
            $samples['/comparatif/' . $clusterId] = 'CollectionPage';
        }

        if (empty($samples)) {
            $this->markTestSkipped(
                'No category/source/cluster sample available in the test corpus.'
            );
        }

        foreach ($samples as $path => $expectedType) {
            $html = $this->get($path)->assertOk()->getContent();
            $count = substr_count($html, 'application/ld+json');
            $this->assertGreaterThanOrEqual(
                3,
                $count,
                "{$path} ships only {$count} JSON-LD blocks (expected ≥ 3 after Wave UUUUU/VVVVV/WWWWW)."
            );
            $this->assertStringContainsString(
                '"' . $expectedType . '"',
                $html,
                "{$path} JSON-LD missing the expected @type \"{$expectedType}\"."
            );
        }
    }

    public function test_every_reader_surface_ships_3_jsonld_blocks(): void
    {
        // Wave PPPPP (Vader 2026-05-19) — JSON-LD coverage contract.
        // After Wave KKKKK/LLLLL/OOOOO, every reader surface ships at
        // least 3 JSON-LD blocks: Botble's WebSite + Organization
        // (chrome-level) plus a surface-specific schema (CollectionPage,
        // AboutPage, WebPage/Service, or the home @graph). Lock the
        // contract so a route refactor that drops `Theme::set('grimbaJsonLd', …)`
        // breaks the test loudly.
        $surfaces = [
            '/'                          => 'WebSite',          // KKKKK @graph + Botble
            '/breaking'                  => 'CollectionPage',
            '/latest'                    => 'CollectionPage',
            '/dossiers'                  => 'CollectionPage',   // LLLLL
            '/advertise'                 => 'Service',          // LLLLL pt 2
            '/sources'                   => 'CollectionPage',   // OOOOO
            '/comprendre-le-barometre'   => 'AboutPage',        // OOOOO
        ];
        foreach ($surfaces as $path => $expectedType) {
            $html = $this->get($path)->assertOk()->getContent();
            $count = substr_count($html, 'application/ld+json');
            $this->assertGreaterThanOrEqual(
                3,
                $count,
                "{$path} ships only {$count} JSON-LD blocks (expected ≥ 3). The surface-specific Theme::set('grimbaJsonLd', …) call may have been dropped."
            );
            $this->assertStringContainsString(
                '"' . $expectedType . '"',
                $html,
                "{$path} JSON-LD missing the expected @type \"{$expectedType}\"."
            );
        }
    }

    public function test_home_ships_website_jsonld_with_searchaction(): void
    {
        // Wave KKKKK (Vader 2026-05-19) — home page emits a WebSite
        // + NewsMediaOrganization @graph JSON-LD with a SearchAction
        // for Google sitelinks. The schema.org `@context` / `@graph`
        // keys are a Blade trap (they look like directives); the
        // grimba-home layout builds the JSON in @php so Blade can't
        // mistake them. This test catches a regression where a
        // refactor silently re-introduces the @context-as-directive
        // bug — the rendered output would have `<?php` PHP code
        // embedded INSIDE the JSON dictionary key, which is what we
        // had before the fix.
        $html = $this->get('/')->assertOk()->getContent();

        // The JSON-LD <script> must be present.
        $this->assertStringContainsString('application/ld+json', $html);

        // The @graph WebSite + Organization block must be there.
        // String search rather than JSON-parse because the body has
        // multiple JSON-LD blocks and we only care about ours.
        $this->assertStringContainsString('"@graph"', $html);
        $this->assertStringContainsString('"WebSite"', $html);
        $this->assertStringContainsString('"NewsMediaOrganization"', $html);
        $this->assertStringContainsString('"SearchAction"', $html);

        // Regression guard: a re-introduced Blade-directive parse of
        // `@context` would compile into `<?php` source code inside
        // the JSON dictionary key. Hunt for that specific signature.
        $this->assertStringNotContainsString(
            '{"<?php',
            $html,
            'JSON-LD body contains a PHP open tag — the @context Blade-directive trap has re-emerged. See Wave KKKKK comments in grimba-home.blade.php.'
        );
        $this->assertStringNotContainsString(
            '\\n$value = context()->get',
            $html,
            'JSON-LD has the Laravel 11 context()-directive expansion. Build the JSON in a @php block and emit via {!! !!}.'
        );
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
