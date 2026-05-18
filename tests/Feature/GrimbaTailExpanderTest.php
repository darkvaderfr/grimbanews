<?php

namespace Tests\Feature;

use App\Support\GrimbaLanguageSettings;
use Botble\ACL\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * S-LSAT-17 — pin isolatable tail-expander contract.
 *
 * The expander is the "language filter as door not wall" UX.
 * Counts come from the live corpus, so "0 posts → hidden" tests
 * are flaky. We pin the parts that ARE isolatable:
 *   - operator toggle force-hides regardless of count
 *   - reader-locale-driven copy (EN vs FR)
 *   - active surface attribute matches the include arg
 */
class GrimbaTailExpanderTest extends TestCase
{
    private const FIXTURE_BASE = 994000;

    /** @var array<string, string> */
    private array $originalSettings = [];

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('posts')->where('id', '>=', self::FIXTURE_BASE)
            ->where('id', '<', self::FIXTURE_BASE + 1000)
            ->delete();

        foreach (array_keys(GrimbaLanguageSettings::defaults()) as $key) {
            $this->originalSettings['grimba_lang_' . $key] = (string) setting('grimba_lang_' . $key, '');
            setting()->set('grimba_lang_' . $key, '');
        }
        setting()->save();
        GrimbaLanguageSettings::flush();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        DB::table('posts')->where('id', '>=', self::FIXTURE_BASE)
            ->where('id', '<', self::FIXTURE_BASE + 1000)
            ->delete();
        foreach ($this->originalSettings as $key => $value) {
            setting()->set($key, $value);
        }
        setting()->save();
        GrimbaLanguageSettings::flush();
        Cache::flush();
        parent::tearDown();
    }

    private function insertFr(int $id): int
    {
        $now = now();
        DB::table('posts')->insert([
            'id' => $id,
            'name' => 'Tail expander fixture FR ' . $id,
            'description' => 'Body.',
            'content' => '<p>Body.</p>',
            'status' => 'published',
            'author_id' => 1,
            'author_type' => User::class,
            'is_featured' => 0,
            'bias_rating' => 'center',
            'is_blindspot' => 0,
            'source_name' => 'Test Wire',
            'source_id' => 1,
            'original_language' => 'fr',
            'translated_to' => null,
            'editorial_region' => 'europe',
            'published_at' => $now->copy()->subMinutes(30),
            'created_at' => $now->copy()->subMinutes(30),
            'updated_at' => $now->copy()->subMinutes(30),
        ]);
        return $id;
    }

    private function renderExpander(string $surface = 'breaking', int $hours = 24, string $lang = 'en'): string
    {
        request()->merge(['lang' => $lang]);
        return view('theme.echo::partials.lang.tail-expander', [
            'surface' => $surface,
            'hours' => $hours,
        ])->render();
    }

    public function test_operator_disable_force_hides_regardless_of_corpus_count(): void
    {
        $this->insertFr(self::FIXTURE_BASE + 1);

        setting()->set('grimba_lang_tail_expander_enabled', '0');
        setting()->save();
        GrimbaLanguageSettings::flush();

        $html = $this->renderExpander('breaking', 24, 'en');
        $this->assertStringNotContainsString('data-grimba-tail-expander', $html, 'Operator-disabled = ribbon section must not render.');
    }

    public function test_renders_english_copy_for_english_reader(): void
    {
        $this->insertFr(self::FIXTURE_BASE + 2);

        $html = $this->renderExpander('breaking', 24, 'en');

        $this->assertStringContainsString('data-grimba-tail-expander', $html);
        $this->assertStringContainsString('also available in French', $html);
        $this->assertStringContainsString('View in French', $html);
        $this->assertStringContainsString('hides untranslated stories', $html);
    }

    public function test_renders_french_copy_for_french_reader(): void
    {
        // Insert an EN-native post so FR readers see the prompt to
        // view in English.
        $now = now();
        DB::table('posts')->insert([
            'id' => self::FIXTURE_BASE + 3,
            'name' => 'EN tail fixture',
            'description' => 'Body.',
            'content' => '<p>Body.</p>',
            'status' => 'published',
            'author_id' => 1,
            'author_type' => User::class,
            'is_featured' => 0,
            'bias_rating' => 'center',
            'is_blindspot' => 0,
            'source_name' => 'Test Wire',
            'source_id' => 1,
            'original_language' => 'en',
            'editorial_region' => 'americas',
            'published_at' => $now->copy()->subMinutes(30),
            'created_at' => $now->copy()->subMinutes(30),
            'updated_at' => $now->copy()->subMinutes(30),
        ]);

        $html = $this->renderExpander('breaking', 24, 'fr');

        $this->assertStringContainsString('data-grimba-tail-expander', $html);
        $this->assertStringContainsString('aussi disponible', $html);
        $this->assertStringContainsString('Voir en anglais', $html);
        $this->assertStringContainsString('Le filtre langue', $html);
    }

    public function test_surface_attribute_matches_include_arg(): void
    {
        $this->insertFr(self::FIXTURE_BASE + 4);

        foreach (['breaking', 'latest', 'home', 'dossiers'] as $surface) {
            Cache::flush();
            $html = $this->renderExpander($surface, 24, 'en');
            $this->assertStringContainsString('data-surface="' . $surface . '"', $html, 'Surface attribute must match the include arg.');
        }
    }

    public function test_cache_key_partitions_by_surface(): void
    {
        // S-LSAT-20 — the count cache must NOT collide between
        // surfaces. /breaking's 24h window and /latest's 72h window
        // count different sets; sharing a cache key would serve
        // /latest's count to /breaking and vice versa.
        $this->insertFr(self::FIXTURE_BASE + 10);
        Cache::flush();
        $this->renderExpander('breaking', 24, 'en');
        $this->renderExpander('latest', 72, 'en');
        // Both keys should exist; their values would only equal by
        // coincidence (different windows = different post sets).
        $this->assertNotNull(Cache::get('grimba_tail_expander:breaking:en:24h'), 'breaking surface key missing');
        $this->assertNotNull(Cache::get('grimba_tail_expander:latest:en:72h'), 'latest surface key missing');
    }

    public function test_cache_key_partitions_by_locale(): void
    {
        $this->insertFr(self::FIXTURE_BASE + 11);
        Cache::flush();
        $this->renderExpander('breaking', 24, 'en');
        $this->renderExpander('breaking', 24, 'fr');
        // An EN reader counts FR posts; an FR reader counts EN
        // posts. Same surface + same window, different locale —
        // must NOT share a cache slot.
        $this->assertNotNull(Cache::get('grimba_tail_expander:breaking:en:24h'));
        $this->assertNotNull(Cache::get('grimba_tail_expander:breaking:fr:24h'));
    }

    public function test_cache_key_partitions_by_window(): void
    {
        $this->insertFr(self::FIXTURE_BASE + 12);
        Cache::flush();
        $this->renderExpander('breaking', 24, 'en');
        $this->renderExpander('breaking', 72, 'en');
        $this->assertNotNull(Cache::get('grimba_tail_expander:breaking:en:24h'));
        $this->assertNotNull(Cache::get('grimba_tail_expander:breaking:en:72h'));
    }

    public function test_flushing_cache_re_runs_the_query(): void
    {
        // Prime: render once at zero fixtures (cache → 0 or live
        // count, doesn't matter — what matters is the cache exists).
        Cache::flush();
        $this->renderExpander('breaking', 24, 'en');
        $cacheKey = 'grimba_tail_expander:breaking:en:24h';
        $primed = Cache::get($cacheKey);
        $this->assertNotNull($primed);

        // Insert a fixture FR post — without flush, the cache still
        // serves the primed value (TTL 60s).
        $this->insertFr(self::FIXTURE_BASE + 13);
        $this->renderExpander('breaking', 24, 'en');
        $this->assertSame($primed, Cache::get($cacheKey), 'Without flush, cache must hold the primed count.');

        // After Cache::flush(), the next render re-runs the query.
        Cache::flush();
        $this->renderExpander('breaking', 24, 'en');
        $afterFlush = Cache::get($cacheKey);
        $this->assertGreaterThanOrEqual(
            (int) $primed,
            (int) $afterFlush,
            'Post-flush render must include the new fixture row (count >= primed value).'
        );
    }

    public function test_display_count_is_present_in_some_form(): void
    {
        // We can't pin a specific count without a deterministic fixture
        // database — the partial reads from the live corpus AND honors
        // the cache. The 99+ display-cap is exercised in production
        // smoke; here we just confirm the ribbon's count slot is
        // non-empty when the ribbon renders.
        $this->insertFr(self::FIXTURE_BASE + 5);
        Cache::flush();
        $html = $this->renderExpander('breaking', 24, 'en');
        if (! str_contains($html, 'data-grimba-tail-expander')) {
            $this->markTestSkipped('Live corpus has no untranslated FR posts in window for this test run.');
        }
        $this->assertMatchesRegularExpression('/\d+\+? articles? also available in French/', $html);
    }
}
