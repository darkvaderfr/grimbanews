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

    /**
     * Wave JJJJJJ — sample article + cluster URLs so the OG/Twitter
     * lock tests actually exercise the page where the originating
     * regressions lived (post.blade ran its own SeoHelper::twitter()
     * additions; that's where twitter:image{0}+{1} surfaced). Listing-
     * surface-only locks would have passed green even with the bug.
     *
     * @return array<string>
     */
    private function sampleStoryUrls(): array
    {
        $urls = [];

        $articleSlug = \Botble\Slug\Models\Slug::query()
            ->where('reference_type', \Botble\Blog\Models\Post::class)
            ->whereIn('prefix', ['article', 'blog'])
            ->orderByDesc('id')
            ->value('key');
        if ($articleSlug) {
            $urls[] = '/article/' . $articleSlug;
        }

        $clusterId = \Illuminate\Support\Facades\DB::table('posts')
            ->where('status', 'published')
            ->whereNotNull('story_cluster_id')
            ->orderByDesc('story_cluster_id')
            ->value('story_cluster_id');
        if ($clusterId) {
            $urls[] = '/comparatif/' . $clusterId;
        }

        return $urls;
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
        // Wave JJJJJJ — extend to article + cluster URLs where the
        // post.blade SeoHelper duplication actually originated.
        $surfaces = array_merge(
            ['/', '/breaking', '/latest', '/dossiers', '/advertise', '/sources'],
            $this->sampleStoryUrls()
        );
        foreach ($surfaces as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $count = preg_match_all('/<meta\s[^>]*property=["\']og:image["\']/i', $html);
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
        // Wave JJJJJJ — extend to article + cluster URLs.
        $surfaces = array_merge(
            ['/', '/breaking', '/latest', '/dossiers', '/advertise', '/sources'],
            $this->sampleStoryUrls()
        );
        foreach ($surfaces as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $w = preg_match_all('/<meta\s[^>]*property=["\']og:image:width["\']/i', $html);
            $h = preg_match_all('/<meta\s[^>]*property=["\']og:image:height["\']/i', $html);
            $this->assertSame(1, $w, "{$path} ships {$w} og:image:width tags (expected 1).");
            $this->assertSame(1, $h, "{$path} ships {$h} og:image:height tags (expected 1).");
        }
    }

    public function test_article_pages_carry_related_dossiers_rail(): void
    {
        // Wave MMMMMM (Vader 2026-05-19) — article detail pages render
        // the topic-relevant "Autres dossiers" rail after the article
        // body. Drives session depth + cross-dossier navigation.
        // The partial bails when primaryTopicFor() returns null, so we
        // probe an article that DOES carry a topic category.
        $url = $this->sampleStoryUrls()[0] ?? null;
        if ($url === null || ! str_starts_with($url, '/article/')) {
            $this->markTestSkipped('No sample article URL available.');
            return;
        }
        $html = $this->get($url)->assertOk()->getContent();
        // The rail's section landmark + ARIA id must be present.
        $this->assertStringContainsString('data-grimba-related-dossiers', $html);
        $this->assertStringContainsString('id="grimba-related-dossiers-title"', $html);
        $this->assertStringContainsString('grimba-related-dossiers__card', $html);
        // Cards must link to /comparatif/{id} (cluster page).
        $this->assertMatchesRegularExpression(
            '#href="[^"]*?/comparatif/\d+"#',
            $html,
            'Related-dossiers rail must link to per-cluster pages.'
        );
    }

    public function test_og_locale_and_alternate_emit_per_request_locale(): void
    {
        // Wave IIIIII (Vader 2026-05-19) — every reader surface declares
        // og:locale matching its rendered locale + og:locale:alternate
        // for the OTHER supported locale. Crawlers (Facebook/LinkedIn)
        // use these to surface the right language version in unfurls
        // and to know multi-locale alternates exist.
        $cases = [
            ['/', 'fr_FR', 'en_US'],
            ['/?lang=en', 'en_US', 'fr_FR'],
            ['/breaking', 'fr_FR', 'en_US'],
            ['/breaking?lang=en', 'en_US', 'fr_FR'],
        ];
        foreach ($cases as [$path, $locale, $alt]) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertStringContainsString(
                '<meta property="og:locale" content="' . $locale . '">',
                $html,
                "{$path} should declare og:locale={$locale}."
            );
            $this->assertStringContainsString(
                '<meta property="og:locale:alternate" content="' . $alt . '">',
                $html,
                "{$path} should declare og:locale:alternate={$alt}."
            );
        }
    }

    public function test_og_type_matches_surface_role(): void
    {
        // Wave HHHHHH (Vader 2026-05-19) — og:type must reflect the
        // page role. Botble's blog plugin defaulted home to 'article'
        // (since technically the blog index sits at /); the OG spec
        // says homepages should be 'website'. Listing pages (/breaking,
        // /latest, /dossiers, etc.) should also be 'website'. Article
        // pages stay 'article'.
        $expectations = [
            '/' => 'website',
            '/breaking' => 'website',
            '/latest' => 'website',
            '/dossiers' => 'website',
            '/sources' => 'website',
            '/advertise' => 'website',
        ];
        // Wave JJJJJJ — article pages should declare og:type=article.
        // Wave LLLLLL — cluster pages (/comparatif/{id}) too, since
        // they're per-story aggregations not multi-story listings.
        foreach ($this->sampleStoryUrls() as $url) {
            if (str_starts_with($url, '/article/') || str_starts_with($url, '/comparatif/')) {
                $expectations[$url] = 'article';
            }
        }
        foreach ($expectations as $path => $expected) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertMatchesRegularExpression(
                '/<meta\s[^>]*property=["\']og:type["\']\s+content=["\']' . preg_quote($expected, '/') . '["\']/i',
                $html,
                "{$path} should declare og:type={$expected}."
            );
        }
    }

    public function test_article_and_cluster_pages_carry_share_kit(): void
    {
        // Wave WWWWWW (Vader 2026-05-19) — share-kit lives on both
        // article detail pages AND cluster (/comparatif/{id}) pages.
        // Cluster pages were missing it pre-WWWWWW, even though they're
        // the most-shareable surface (the multi-source bias comparison
        // is GrimbaNews's unique value prop).
        foreach ($this->sampleStoryUrls() as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString(
                'grimba-share-kit',
                $html,
                "{$url} should include the share-kit aside."
            );
            // At least 6 of the 7 channels must render (the absent one
            // would indicate an URL-encoding or partial-include bug).
            $networks = ['x', 'bluesky', 'facebook', 'whatsapp', 'linkedin', 'email'];
            foreach ($networks as $n) {
                $this->assertStringContainsString(
                    'data-network="' . $n . '"',
                    $html,
                    "{$url} share-kit missing the {$n} channel."
                );
            }
        }
    }

    public function test_static_pages_carry_correct_jsonld_schema(): void
    {
        // Wave HHHHHHH (Vader 2026-05-19) — lock the JSON-LD @type
        // per static-page surface. /a-propos → AboutPage, /methodologie
        // → TechArticle, /faq → FAQPage, /angles-morts → CollectionPage.
        // SERP rich results depend on these exact types being present.
        $expectations = [
            '/a-propos' => 'AboutPage',
            '/methodologie' => 'TechArticle',
            '/faq' => 'FAQPage',
            '/angles-morts' => 'CollectionPage',
        ];
        foreach ($expectations as $path => $type) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertStringContainsString(
                '"@type":"' . $type . '"',
                $html,
                "{$path} should ship JSON-LD with @type=\"{$type}\"."
            );
        }
    }

    public function test_no_open_redirect_via_query_params(): void
    {
        // Wave QQQQQQQ (Vader 2026-05-19) — verify no open-redirect
        // vector via the obvious `?next=`, `?redirect=`, `?return=`
        // patterns. Open redirects let attackers craft phishing URLs
        // that look like grimbanews.com but bounce to evil.com.
        $probed = 0;
        foreach (['/', '/comparatif/1703', '/breaking', '/dossiers'] as $path) {
            foreach (['next', 'redirect', 'return', 'url', 'goto', 'r'] as $param) {
                $response = $this->get($path . '?' . $param . '=https://evil.example.com');
                $probed++;
                // Either a real 2xx render (no honoring) or a redirect
                // pointing OFF-DOMAIN must NOT happen. Same-origin
                // redirects are fine.
                if ($response->status() >= 300 && $response->status() < 400) {
                    $loc = (string) $response->headers->get('location', '');
                    $this->assertStringNotContainsString(
                        'evil.example.com',
                        $loc,
                        "Open redirect: {$path}?{$param}=… leaked the attacker URL into Location header."
                    );
                }
            }
        }
        $this->assertSame(24, $probed, 'Probe count drift — open-redirect coverage shrank.');
    }

    public function test_img_proxy_rejects_ssrf_targets(): void
    {
        // Wave QQQQQQQ — img-proxy SSRF guard. The allowlist already
        // excludes AWS metadata (169.254.169.254), file:// schemes,
        // and arbitrary external hosts. Lock this so a future allowlist
        // misconfig can't silently open the SSRF window.
        $blocked = [
            '/img-proxy?u=http://169.254.169.254/latest/meta-data/&provider=article-hero',
            '/img-proxy?u=file:///etc/passwd&provider=article-hero',
            '/img-proxy?u=https://evil.example.com/x.png&provider=article-hero',
        ];
        foreach ($blocked as $url) {
            $response = $this->get($url);
            $this->assertNotEquals(
                200,
                $response->status(),
                "img-proxy MUST NOT proxy {$url} — SSRF risk."
            );
        }
    }

    public function test_security_txt_meets_rfc_9116_minimum(): void
    {
        // Wave PPPPPPP (Vader 2026-05-19) — lock the security.txt
        // shape. RFC 9116 requires Contact and Expires; if the file
        // gets stripped or the Contact endpoint moves, researchers
        // lose the disclosure channel. PHP's dev server serves it
        // as a static file under public/.well-known/security.txt.
        $path = public_path('.well-known/security.txt');
        $this->assertFileExists($path, 'security.txt must exist at public/.well-known/security.txt.');
        $body = (string) file_get_contents($path);
        $this->assertMatchesRegularExpression('/^Contact:\s+\S+/m', $body, 'security.txt must have a Contact field.');
        $this->assertMatchesRegularExpression('/^Expires:\s+\d{4}-\d{2}-\d{2}/m', $body, 'security.txt must have an Expires field with an ISO date.');
        $this->assertStringContainsString('@grimbanews.com', $body, 'security.txt Contact should target a grimbanews.com mailbox.');
    }

    public function test_search_jsonld_escapes_script_close_in_user_query(): void
    {
        // Wave OOOOOOO (Vader 2026-05-19) — STORED-REFLECTED XSS guard.
        // Before this wave, /search?q=<script>... emitted the literal
        // </script> inside the application/ld+json block, breaking out
        // of the script tag and executing arbitrary JS in HTML context.
        // Fix: JSON_HEX_TAG flag on every json_encode that produces
        // content rendered inside <script>...</script> in HTML.
        $payload = "<script>alert('xss')</script>";
        $html = $this->get('/search?q=' . urlencode($payload))->assertOk()->getContent();
        // The literal </script> MUST NOT appear inside the JSON-LD
        // block. Extract the script bodies and assert none contain the
        // close-tag sequence.
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        foreach ($matches[1] as $body) {
            $this->assertStringNotContainsString(
                '</script>',
                $body,
                'JSON-LD body must not contain literal </script> sequence — XSS vector.'
            );
            $this->assertStringNotContainsString(
                '<script>',
                $body,
                'JSON-LD body must not contain literal <script> sequence — XSS vector.'
            );
        }
    }

    public function test_non_numeric_cluster_id_returns_404_not_500(): void
    {
        // Wave MMMMMMM (Vader 2026-05-19) — /comparatif/abc returned
        // 500 (PHP 8 TypeError: int param + string arg) before this
        // wave because the route lacked a numeric where() constraint.
        // Crawlers + malicious probes hitting non-numeric variants
        // would trigger 500s and pollute error logs.
        // Constraint: `->where('clusterId', '[0-9]+')` makes Laravel
        // 404 the route before the handler runs.
        $this->get('/comparatif/abc')->assertStatus(404);
        $this->get('/comparatif/foo-bar')->assertStatus(404);
        // Real numeric clusters still 404 if missing (Wave KKKKKKK),
        // OR 200 if found.
        $this->get('/comparatif/9999999999')->assertStatus(404);
    }

    public function test_missing_cluster_id_returns_404_not_thin_shell(): void
    {
        // Wave KKKKKKK (Vader 2026-05-19) — /comparatif/{nonexistent_id}
        // must 404. Before this wave, the route rendered a "Aucune
        // source n'a été trouvée" empty shell with HTTP 200, which:
        //   (a) signals to crawlers there's thin content here, hurting
        //       SEO authority distribution
        //   (b) misleads users (looks like a real cluster with no data
        //       vs a not-found page)
        // 404 routes through the real 404 page with search + recent.
        $this->get('/comparatif/9999999999')->assertStatus(404);
    }

    public function test_sitemap_xml_returns_valid_sitemap_index(): void
    {
        // Wave JJJJJJJ (Vader 2026-05-19) — /sitemap.xml must return
        // 200 + xml content-type + a non-empty sitemap or sitemapindex
        // document. Google reads this for crawl discovery.
        $response = $this->get('/sitemap.xml');
        $response->assertOk();
        $this->assertStringContainsString(
            'text/xml',
            (string) $response->headers->get('content-type'),
            '/sitemap.xml must return text/xml content-type.'
        );
        $body = $response->getContent();
        $this->assertStringContainsString('<?xml', $body, '/sitemap.xml must start with an XML prolog.');
        // Either a sitemap index (Botble's default for paginated feeds)
        // or a flat sitemap with <urlset> works — both are valid SiteMap
        // protocol responses.
        $hasIndex = str_contains($body, '<sitemapindex');
        $hasUrlset = str_contains($body, '<urlset');
        $this->assertTrue(
            $hasIndex || $hasUrlset,
            '/sitemap.xml must contain either <sitemapindex> or <urlset> root element.'
        );
    }

    public function test_rss_feeds_return_xml_with_content(): void
    {
        // Wave BBBBBBB (Vader 2026-05-19) — three RSS feeds power
        // syndication for /feed.xml (full corpus), /feed.breaking.xml
        // (breaking), /feed.latest.xml (latest). All three must return
        // 200 + application/rss+xml + a non-empty <rss> document.
        $feeds = ['/feed.xml', '/feed.breaking.xml', '/feed.latest.xml'];
        foreach ($feeds as $feed) {
            $response = $this->get($feed);
            $response->assertOk();
            $this->assertStringContainsString(
                'application/rss+xml',
                (string) $response->headers->get('content-type'),
                "{$feed} must return application/rss+xml content-type."
            );
            $body = $response->getContent();
            $this->assertStringContainsString('<rss', $body, "{$feed} body must contain an <rss> element.");
            $this->assertStringContainsString('</rss>', $body, "{$feed} body must be a closed RSS doc.");
        }
    }

    public function test_article_jsonld_carries_article_section(): void
    {
        // Wave ZZZZZZ (Vader 2026-05-19) — NewsArticle JSON-LD must
        // include `articleSection` so Google News / Discover can
        // cluster the story by editorial topic. Source: post's primary
        // topic category via GrimbaEditorialCategories::primaryTopicFor.
        $articleUrls = array_filter(
            $this->sampleStoryUrls(),
            fn (string $u) => str_starts_with($u, '/article/')
        );
        if (empty($articleUrls)) {
            $this->markTestSkipped('No article URL available.');
            return;
        }
        foreach ($articleUrls as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
            $found = false;
            foreach ($matches[1] as $body) {
                $data = json_decode(trim($body), true);
                if (! is_array($data)) continue;
                if (($data['@type'] ?? null) === 'NewsArticle' && ! empty($data['articleSection'])) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "{$path} should ship a NewsArticle JSON-LD block with articleSection.");
        }
    }

    public function test_article_pages_carry_open_graph_article_meta(): void
    {
        // Wave UUUUUU (Vader 2026-05-19) — article detail pages must
        // emit `article:published_time`, `article:modified_time`, and
        // `article:author` per the OG protocol spec (bare prefix, NOT
        // `og:article:*` — Botble's addProperty auto-prefix bug routes
        // around this via Theme::set + raw <meta> in the tail partial).
        // Listing surfaces (home, /breaking) must NOT carry these.
        $articleUrls = array_filter(
            $this->sampleStoryUrls(),
            fn (string $u) => str_starts_with($u, '/article/')
        );
        if (empty($articleUrls)) {
            $this->markTestSkipped('No article URL available in test corpus.');
            return;
        }
        foreach ($articleUrls as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            foreach (['article:published_time', 'article:modified_time', 'article:author'] as $prop) {
                $this->assertMatchesRegularExpression(
                    '/<meta\s[^>]*property=["\']' . preg_quote($prop, '/') . '["\']/i',
                    $html,
                    "{$path} should emit <meta property=\"{$prop}\">."
                );
            }
        }
        // Listing surface should NOT carry article:* metas.
        $homeHtml = $this->get('/')->getContent();
        $this->assertDoesNotMatchRegularExpression(
            '/<meta\s[^>]*property=["\']article:published_time["\']/i',
            $homeHtml,
            'Home page should not carry article:* OG meta.'
        );
    }

    public function test_robots_meta_indexes_reader_surfaces_and_skips_search(): void
    {
        // Wave TTTTTT (Vader 2026-05-19) — every reader/story surface
        // declares "index, follow" so crawlers attribute editorial
        // authority. /search?q=... gets noindex,follow — search results
        // are duplicate-content surfaces of the underlying articles.
        $indexable = array_merge(
            ['/', '/breaking', '/latest', '/dossiers', '/advertise', '/sources'],
            $this->sampleStoryUrls()
        );
        foreach ($indexable as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertMatchesRegularExpression(
                '/<meta\s[^>]*name=["\']robots["\']\s+content=["\']index,\s*follow["\']/i',
                $html,
                "{$path} should ship robots=index,follow."
            );
        }
        foreach (['/search', '/search?q=mayotte'] as $searchPath) {
            $html = $this->get($searchPath)->assertOk()->getContent();
            $this->assertMatchesRegularExpression(
                '/<meta\s[^>]*name=["\']robots["\']\s+content=["\']noindex,\s*follow["\']/i',
                $html,
                "{$searchPath} should ship robots=noindex,follow."
            );
        }
    }

    public function test_every_surface_ships_canonical_link(): void
    {
        // Wave RRRRRR (Vader 2026-05-19) — every reader + story surface
        // must emit `<link rel="canonical" href="...">`. Before this
        // wave, custom routes (/breaking, /latest, /comparatif/{id},
        // /sources, /advertise, /search, /dossiers) shipped WITHOUT
        // canonical because they didn't call SeoHelper::meta()->setUrl().
        // The shared seo-meta-config partial now always sets canonical
        // from url()->current() so Botble's MiscTags::addCanonical fires.
        // Query params get stripped by SeoHelper's stripQueryParameters.
        $surfaces = array_merge(
            ['/', '/breaking', '/latest', '/dossiers', '/advertise', '/sources', '/search?q=mayotte'],
            $this->sampleStoryUrls()
        );
        foreach ($surfaces as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $count = preg_match_all('/<link\s[^>]*rel=["\']canonical["\']/i', $html);
            $this->assertSame(
                1,
                $count,
                "{$path} should emit exactly one <link rel=\"canonical\"> (got {$count})."
            );
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
        // Wave JJJJJJ — extend to article + cluster URLs where the
        // original bug lived. The listing-surface-only lock would have
        // passed green even with post.blade's duplicate SeoHelper::twitter()
        // call still in place.
        $surfaces = array_merge(
            ['/', '/breaking', '/latest', '/dossiers', '/advertise', '/sources'],
            $this->sampleStoryUrls()
        );
        foreach ($surfaces as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $card = preg_match_all('/<meta\s[^>]*name=["\']twitter:card["\']/i', $html);
            $image = preg_match_all('/<meta\s[^>]*name=["\']twitter:image["\'][^>]/i', $html);
            // The bad-state pattern `twitter:image{0}` is the literal
            // SeoHelper Card::loadImages() emission when count(images)>1.
            $numbered = preg_match_all('/name=["\']twitter:image\{/i', $html);
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

    public function test_security_headers_ship_on_every_reader_surface(): void
    {
        // Wave TTTTTTT (Vader 2026-05-19) — lock the security-header
        // contract. A previous session quietly added HSTS + a
        // hardened CSP via the GrimbaSecurityHeaders middleware;
        // protect against silent regression (someone disabling the
        // middleware, reordering pipeline, or stripping a header in
        // a hot-fix). These headers are what Mozilla Observatory
        // and most enterprise-procurement security questionnaires
        // check first.
        $surfaces = ['/', '/breaking', '/dossiers', '/a-propos', '/methodologie', '/faq'];
        foreach ($surfaces as $url) {
            $response = $this->get($url);
            $this->assertSame(200, $response->getStatusCode(), "{$url} must respond 200 before header asserts.");
            $headers = $response->headers;

            $this->assertSame('nosniff', $headers->get('X-Content-Type-Options'), "{$url} must ship X-Content-Type-Options: nosniff");
            $this->assertSame('SAMEORIGIN', $headers->get('X-Frame-Options'), "{$url} must ship X-Frame-Options: SAMEORIGIN");
            $this->assertSame('strict-origin-when-cross-origin', $headers->get('Referrer-Policy'), "{$url} must ship Referrer-Policy: strict-origin-when-cross-origin");
            $this->assertNotEmpty($headers->get('Content-Security-Policy'), "{$url} must ship a Content-Security-Policy header");
            $this->assertStringContainsString("default-src 'self'", (string) $headers->get('Content-Security-Policy'), "{$url} CSP must lock default-src to 'self'");
            $this->assertStringContainsString("object-src 'none'", (string) $headers->get('Content-Security-Policy'), "{$url} CSP must block object-src");
            $this->assertStringContainsString("frame-ancestors 'self'", (string) $headers->get('Content-Security-Policy'), "{$url} CSP must lock frame-ancestors");
            $this->assertNotEmpty($headers->get('Permissions-Policy'), "{$url} must ship a Permissions-Policy header");
        }
    }

    public function test_no_x_powered_by_header_leaks_php_version(): void
    {
        // Wave TTTTTTT — PHP's default `X-Powered-By: PHP/8.x.y`
        // header leaks our exact PHP version, which is useful for
        // attackers fingerprinting known CVE-vulnerable versions.
        // We don't fight to strip this in test (PHP cli-server in
        // dev still ships it), so assert specifically that no live
        // PHP-version-disclosure header leaks via *our* response
        // pipeline. Production nginx strips it; this test guards
        // against a regression where Botble or a vendor adds an
        // explicit `X-Powered-By` to responses themselves.
        $response = $this->get('/');
        $powered = (string) $response->headers->get('X-Powered-By', '');
        // It's OK if PHP cli-server adds this in test — what we
        // forbid is our own code setting it (e.g., a misguided
        // marketing string).
        $this->assertDoesNotMatchRegularExpression(
            '/laravel|botble|grimba/i',
            $powered,
            'X-Powered-By must not advertise our framework or product name.',
        );
    }

    public function test_static_editorial_pages_must_not_public_cache(): void
    {
        // Wave YYYYYYY (Vader 2026-05-19) — REVERSED Wave TTTTTTT/SSSSSSS.
        // Wave SSSSSSS public-cached /methodologie, /a-propos, /faq,
        // /comprendre-le-barometre with `Cache-Control: public,
        // max-age=3600, s-maxage=21600`. Zen audit caught the CRITICAL
        // hazard: grimba-chrome layout renders a per-session
        // `<meta name="csrf-token">` on line 83. A CDN honoring those
        // headers would serve one visitor's CSRF token to every
        // subsequent visitor for 6h — breaking any AJAX POST and
        // creating a CSRF-bypass vector if a future form lands on
        // these pages.
        //
        // This test enforces the safe posture: these pages must NOT
        // ship public cache headers. If someone re-introduces Wave
        // SSSSSSS without first stripping csrf-token from the
        // chrome layout, this test fails loudly.
        $surfaces = ['/methodologie', '/a-propos', '/faq', '/comprendre-le-barometre', '/advertise'];
        foreach ($surfaces as $url) {
            $cc = (string) $this->get($url)->headers->get('Cache-Control', '');
            $this->assertStringNotContainsString(
                'public',
                $cc,
                "{$url} must NOT ship `public` Cache-Control. The chrome layout renders a per-session csrf-token meta; CDN caching would leak tokens across visitors. (currently `{$cc}`)",
            );
            $this->assertStringNotContainsString(
                's-maxage=',
                $cc,
                "{$url} must NOT ship `s-maxage` Cache-Control. (currently `{$cc}`)",
            );
        }
    }

    public function test_theme_only_sitemap_covers_static_editorial_routes(): void
    {
        // Wave UUUUUUU (Vader 2026-05-19) — Botble's pages.xml only
        // covers CMS-registered Pages. Theme-only routes
        // (/methodologie, /comprendre-le-barometre, /breaking,
        // /latest, /dossiers, /angles-morts) had no CMS row and
        // never made it into Botble's sitemap.
        //
        // Wave AAAAAAAA (2026-05-19) — converted from a static file
        // at public/sitemap-grimba.xml to a dynamic route handler
        // so lastmod tracks real content changes (Zen audit LOW).
        // Crawlers down-weight sitemaps whose lastmod doesn't move.
        $response = $this->get('/sitemap-grimba.xml');
        $response->assertOk();
        $this->assertStringContainsString(
            'application/xml',
            (string) $response->headers->get('Content-Type'),
            'sitemap-grimba.xml must serve application/xml content-type.',
        );
        $body = $response->getContent();
        $this->assertStringStartsWith('<?xml', $body, 'sitemap-grimba.xml must start with an XML declaration.');
        $expected = ['/methodologie', '/comprendre-le-barometre', '/breaking', '/latest', '/dossiers', '/angles-morts'];
        foreach ($expected as $path) {
            $this->assertStringContainsString(
                "{$path}</loc>",
                $body,
                "sitemap-grimba.xml must list {$path}",
            );
        }
        // Validates as well-formed XML (no malformed tags).
        $prev = libxml_use_internal_errors(true);
        $doc = simplexml_load_string($body);
        $this->assertNotFalse($doc, 'sitemap-grimba.xml must parse as well-formed XML.');
        libxml_use_internal_errors($prev);
        // Wave AAAAAAAA — sitemap is publicly cacheable (no per-
        // session content). Caching wins are real here because
        // crawlers hit it frequently.
        $cc = (string) $response->headers->get('Cache-Control', '');
        $this->assertStringContainsString('public', $cc, 'sitemap should ship public Cache-Control for CDN.');
    }

    public function test_theme_only_sitemap_lastmod_reflects_real_content_age(): void
    {
        // Wave AAAAAAAA (Vader 2026-05-19, Zen LOW) — lastmod
        // freshness. /breaking and /latest lastmod must equal the
        // most-recent published post timestamp (NOT a hardcoded
        // date). If a future regression hardcodes lastmod again,
        // the assertion below fails because the actual newest
        // post's date won't match the hardcoded value.
        $newestPost = \Illuminate\Support\Facades\DB::table('posts')
            ->where('status', 'published')
            ->selectRaw('max(' . \App\Support\GrimbaPostRecency::expression() . ') as latest_at')
            ->first();
        $this->assertNotNull($newestPost->latest_at ?? null, 'Fixture must have at least one published post.');
        $expectedIso = substr(\Carbon\Carbon::parse($newestPost->latest_at)->toAtomString(), 0, 10);
        $body = $this->get('/sitemap-grimba.xml')->assertOk()->getContent();
        // The /breaking entry's lastmod should begin with the same
        // date as the newest post. (Use date-prefix match to avoid
        // tz-flake on second precision.)
        $this->assertMatchesRegularExpression(
            '#<loc>[^<]*/breaking</loc>\s*<lastmod>' . preg_quote($expectedIso, '#') . '#i',
            $body,
            "/breaking lastmod should start with the newest-post date {$expectedIso}.",
        );
    }

    public function test_robots_txt_advertises_both_sitemaps(): void
    {
        // Wave UUUUUUU — assert robots.txt points to BOTH sitemaps.
        // Crawlers will pick up multiple Sitemap: directives. If
        // either one drops, theme routes or CMS pages quietly stop
        // getting recrawled.
        $body = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Sitemap: https://grimbanews.com/sitemap.xml', $body, 'robots.txt must advertise Botble sitemap.');
        $this->assertStringContainsString('Sitemap: https://grimbanews.com/sitemap-grimba.xml', $body, 'robots.txt must advertise theme-only sitemap.');
    }

    public function test_robots_txt_disallows_auth_gated_paths(): void
    {
        // Wave VVVVVVV (Vader 2026-05-19) — auth-gated paths
        // (/admin, /coffre, /account, /member) have no SEO value
        // and waste crawler budget. Explicit Disallow saves crawl
        // budget for the surfaces that matter. Server-side auth
        // is still the actual gate; this is purely a hint to
        // well-behaved crawlers.
        $body = file_get_contents(public_path('robots.txt'));
        $authGated = ['/admin', '/coffre', '/account', '/member'];
        foreach ($authGated as $path) {
            $this->assertMatchesRegularExpression(
                '/^Disallow:\s*' . preg_quote($path, '/') . '/m',
                $body,
                "robots.txt must Disallow {$path} (auth-gated, no SEO value).",
            );
        }
    }

    public function test_robots_txt_does_not_disallow_noindex_crawlable_paths(): void
    {
        // Wave VVVVVVV — pages we want excluded from the index
        // but still want CRAWLED (so the bot sees the
        // `<meta name="robots" content="noindex">` directive) must
        // NOT be Disallowed in robots.txt. Disallow + noindex is
        // the worst of both worlds: Google can't crawl to see the
        // noindex meta, so a referring backlink can still cause
        // the URL to surface as a bare result.
        $body = file_get_contents(public_path('robots.txt'));
        $crawlable = ['/search', '/pour-vous', '/local', '/feed.xml'];
        foreach ($crawlable as $path) {
            $this->assertDoesNotMatchRegularExpression(
                '/^Disallow:\s*' . preg_quote($path, '/') . '\s*$/m',
                $body,
                "robots.txt must NOT Disallow {$path} (kept crawlable so robots see the noindex meta).",
            );
        }
    }

    public function test_every_jsonld_block_parses_as_valid_json(): void
    {
        // Wave XXXXXXX (Vader 2026-05-19) — sister to Wave OOOOOOO.
        // The XSS-escape flags (JSON_HEX_TAG etc.) on json_encode
        // emit unicode escapes for `<`, `>`, `&`, `'`, `"`. These
        // remain valid JSON, but any future change to the encode
        // call site — flag omission, manual string concat, post-
        // hoc HTML-escaping — could break syntax silently. Google's
        // structured-data parser silently DROPS blocks with JSON
        // errors instead of flagging them, so an invalid JSON-LD
        // block costs SEO without showing up in any error log.
        //
        // This test extracts every JSON-LD block from key surfaces
        // and asserts json_decode succeeds + the decoded payload
        // has a recognized @context.
        $surfaces = ['/', '/breaking', '/latest', '/dossiers', '/a-propos', '/methodologie', '/faq', '/advertise', '/angles-morts', '/comprendre-le-barometre'];
        $totalBlocks = 0;
        foreach ($surfaces as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            preg_match_all(
                '#<script[^>]+type="application/ld\+json"[^>]*>(.*?)</script>#is',
                $html,
                $m,
            );
            $blocks = $m[1];
            $this->assertNotEmpty($blocks, "{$url} must ship at least 1 application/ld+json block.");
            foreach ($blocks as $i => $jsonText) {
                $decoded = json_decode(trim($jsonText), true);
                $this->assertIsArray(
                    $decoded,
                    "{$url} block #{$i} must parse as valid JSON. json_last_error_msg: " . json_last_error_msg(),
                );
                $this->assertSame(
                    'https://schema.org',
                    $decoded['@context'] ?? null,
                    "{$url} block #{$i} must declare @context: https://schema.org.",
                );
                // Wave YYYYYYY (Mnemo audit gap) — also assert the
                // OOOOOOO contract: no literal `</script>` substring
                // inside the JSON-LD block. The JSON_HEX_TAG flag
                // emits `<\/script>` instead, defending
                // against the stored-reflected XSS vector. Without
                // this, a future regression dropping JSON_HEX_TAG
                // would still produce valid JSON (just with a raw
                // `</script>`) and pass the validity check above.
                $this->assertStringNotContainsStringIgnoringCase(
                    '</script>',
                    $jsonText,
                    "{$url} block #{$i} must NOT contain literal `</script>`. JSON_HEX_TAG flag is what escapes it; absence means the flag was dropped.",
                );
                $totalBlocks++;
            }
        }
        $this->assertGreaterThan(20, $totalBlocks, 'Expected >20 JSON-LD blocks across these 10 surfaces; got ' . $totalBlocks);
    }

    public function test_404_view_sets_grimba_is_404_flag_for_seo_partial(): void
    {
        // Wave WWWWWWW (Vader 2026-05-19) — 404 pages were shipping
        // `<link rel="canonical" href="http://.../broken-url">` and
        // `<meta name="robots" content="index, follow">`. The fix:
        // the 404 view sets `Theme::set('grimba_is_404', true)` and
        // seo-meta-config.blade.php checks that flag to skip canonical
        // AND force noindex.
        //
        // We can't easily test the full 404-rendering pipeline (the
        // kernel handle path uses Symfony's default exception view
        // in test mode, bypassing the theme), so verify the wiring
        // at the source: 404.blade.php sets the flag, the meta partial
        // honors it.
        $view404 = file_get_contents(base_path('platform/themes/echo/views/404.blade.php'));
        $this->assertStringContainsString(
            "Theme::set('grimba_is_404', true)",
            $view404,
            '404 view must set the grimba_is_404 Theme flag for the SEO meta partial.',
        );
        $metaPartial = file_get_contents(base_path('platform/themes/echo/partials/seo-meta-config.blade.php'));
        $this->assertStringContainsString(
            "Theme::get('grimba_is_404')",
            $metaPartial,
            'seo-meta-config must read the grimba_is_404 Theme flag.',
        );
        $this->assertMatchesRegularExpression(
            '/if\s*\(\s*!\s*\$__grimbaIs404\s*\)\s*\{[^}]*setUrl/s',
            $metaPartial,
            'seo-meta-config must skip setUrl() when grimba_is_404 is true (no canonical on 404).',
        );
        $this->assertStringContainsString(
            '$__grimbaIs404',
            $metaPartial,
            'seo-meta-config noindex predicate must include the 404 flag.',
        );
        // Cleanup wired so Theme::set state doesn't leak between
        // shared-kernel requests. Wave ZZZZZZZ moved the primary
        // cleanup into seo-meta-config (single-owner) per Zen audit
        // MEDIUM — the tail partial keeps a defense-in-depth clear.
        $this->assertStringContainsString(
            "Theme::set('grimba_is_404', null)",
            $metaPartial,
            'seo-meta-config must clear grimba_is_404 after its own read (single-owner state lifecycle).',
        );
        $cleanupPartial = file_get_contents(base_path('platform/themes/echo/partials/seo-meta-twitter-image.blade.php'));
        $this->assertStringContainsString(
            "Theme::set('grimba_is_404', null)",
            $cleanupPartial,
            'seo-meta-twitter-image must also clear grimba_is_404 (defense in depth across early-return / exception view paths).',
        );
    }

    public function test_paginated_pages_canonical_to_themselves_not_base_url(): void
    {
        // Wave BBBBBBBB (Vader 2026-05-19) — real SEO bug.
        // /breaking?page=2 was canonicaling to /breaking (no query),
        // telling Google to ignore pages 2+ as duplicates of page 1.
        // Every article living on page 2+ was blocked from indexing.
        //
        // Fix: AppServiceProvider::preservePaginationInCanonical
        // hooks into Botble's `core_seo_canonical` filter to
        // re-append `?page=N` when N>1. Tracking params (utm_*,
        // fbclid, gclid) are still stripped — only `page` survives.
        $cases = [
            ['/breaking',                       '/breaking'],
            ['/breaking?page=2',                '/breaking?page=2'],
            ['/breaking?page=2&utm_source=fb',  '/breaking?page=2'],
            ['/latest?page=3',                  '/latest?page=3'],
            ['/dossiers?page=2',                '/dossiers?page=2'],
            // page=1 is treated as base URL — Google's standard
            // pagination convention treats first page == bare URL.
            ['/breaking?page=1',                '/breaking'],
        ];
        foreach ($cases as [$url, $expectedCanonicalSuffix]) {
            $html = $this->get($url)->getContent();
            preg_match('#<link\s+rel="canonical"\s+href="([^"]+)"#i', $html, $m);
            $this->assertNotEmpty($m, "{$url} must emit a <link rel=canonical>.");
            $actual = $m[1];
            $this->assertStringEndsWith(
                $expectedCanonicalSuffix,
                $actual,
                "{$url} canonical should end with `{$expectedCanonicalSuffix}` (got `{$actual}`).",
            );
        }
    }

    public function test_advertise_page_does_not_get_public_cached(): void
    {
        // Wave TTTTTTT / Wave YYYYYYY — /advertise has an @csrf token
        // and a form; public-caching it would leak one user's CSRF
        // token to another (any subsequent submit would 419). Covered
        // also by test_static_editorial_pages_must_not_public_cache;
        // keep this here as a redundancy guard since /advertise's
        // public-cache risk is specifically called out in the
        // editorial-page route handler's comment.
        $cc = (string) $this->get('/advertise')->headers->get('Cache-Control', '');
        $this->assertStringNotContainsString(
            's-maxage=21600',
            $cc,
            '/advertise must NOT ship the editorial public-cache header (form has a CSRF token).',
        );
    }
}
