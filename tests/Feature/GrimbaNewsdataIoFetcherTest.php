<?php

namespace Tests\Feature;

use App\Services\GrimbaNewsdataIoFetcher;
use App\Support\GrimbaProviderCredits;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Note: deliberately does NOT use RefreshDatabase — this test fakes
 * Http and exercises in-memory settings. RefreshDatabase would wipe
 * seeded fixtures that sibling Feature tests (MostReadByBiasTest,
 * PwaShellTest, SavedSearchAlertsTest) rely on.
 */
class GrimbaNewsdataIoFetcherTest extends TestCase
{
    /** @var array<string, string> */
    private array $originalSettings = [];

    protected function setUp(): void
    {
        parent::setUp();
        GrimbaProviderCredits::reset('newsdata-io');

        // Snapshot then override the newsdata.io settings used in tests.
        foreach ([
            'grimba_newsdata_io_key' => 'test-key-001',
            'grimba_newsdata_io_active' => '1',
            'grimba_newsdata_io_queries' => 'test query',
            'grimba_newsdata_io_languages' => 'fr,en',
            'grimba_newsdata_io_countries' => 'fr,us',
            'grimba_newsdata_io_categories' => 'top',
            'grimba_newsdata_io_max_calls_per_run' => '1',
            'grimba_newsdata_io_daily_credit_budget' => '190',
        ] as $key => $value) {
            $this->originalSettings[$key] = (string) setting($key, '');
            setting()->set($key, $value);
        }
        setting()->save();
    }

    protected function tearDown(): void
    {
        // Restore pre-test settings so we don't leak state into other Feature tests.
        foreach ($this->originalSettings as $key => $value) {
            setting()->set($key, $value);
        }
        setting()->save();
        GrimbaProviderCredits::reset('newsdata-io');

        parent::tearDown();
    }

    public function test_skipped_when_not_active(): void
    {
        setting()->set('grimba_newsdata_io_active', '');

        $result = app(GrimbaNewsdataIoFetcher::class)->fetch();

        $this->assertSame('skipped', $result[0]['status']);
        $this->assertStringContainsString('disabled', $result[0]['error']);
    }

    public function test_skipped_when_no_api_key(): void
    {
        setting()->set('grimba_newsdata_io_key', '');

        $result = app(GrimbaNewsdataIoFetcher::class)->fetch();

        $this->assertSame('skipped', $result[0]['status']);
        $this->assertStringContainsString('not configured', $result[0]['error']);
    }

    public function test_skipped_when_daily_budget_reached(): void
    {
        setting()->set('grimba_newsdata_io_daily_credit_budget', 2);

        // Pretend we already burned 2 credits today.
        GrimbaProviderCredits::bump('newsdata-io');
        GrimbaProviderCredits::bump('newsdata-io');

        $result = app(GrimbaNewsdataIoFetcher::class)->fetch();

        $this->assertSame('skipped', $result[0]['status']);
        $this->assertStringContainsString('budget reached', $result[0]['error']);
    }

    public function test_status_error_payload_counts_as_failed(): void
    {
        Http::fake([
            'newsdata.io/api/1/latest*' => Http::response([
                'status' => 'error',
                'results' => ['code' => 'RateLimitExceeded', 'message' => 'Daily limit hit.'],
            ], 200),
        ]);

        $result = app(GrimbaNewsdataIoFetcher::class)->fetch();

        $this->assertSame('failed', $result[0]['status']);
        $this->assertStringContainsString('Daily limit hit', $result[0]['error']);
    }

    public function test_http_500_counts_as_failed(): void
    {
        Http::fake([
            'newsdata.io/api/1/latest*' => Http::response(['error' => 'boom'], 503),
        ]);

        $result = app(GrimbaNewsdataIoFetcher::class)->fetch();

        $this->assertSame('failed', $result[0]['status']);
        $this->assertStringContainsString('HTTP 503', $result[0]['error']);
    }

    public function test_empty_results_is_a_successful_zero_returned_run(): void
    {
        Http::fake([
            'newsdata.io/api/1/latest*' => Http::response([
                'status' => 'success',
                'totalResults' => 0,
                'results' => [],
            ], 200),
        ]);

        $result = app(GrimbaNewsdataIoFetcher::class)->fetch();

        $this->assertNotSame('failed', $result[0]['status']);
        $this->assertNotSame('skipped', $result[0]['status']);
        $this->assertSame(0, $result[0]['returned']);
        $this->assertSame(0, $result[0]['ingested']);
    }

    public function test_normalise_extracts_canonical_fields(): void
    {
        $fetcher = app(GrimbaNewsdataIoFetcher::class);
        $row = $fetcher->normaliseArticle([
            'article_id' => 'abc123',
            'title' => 'Breaking: Test Article',
            'link' => 'https://reuters.com/world/test-article',
            'description' => 'Short description of the test article.',
            'content' => 'Longer body content.',
            'image_url' => 'https://reuters.com/img.jpg',
            'source_id' => 'reuters',
            'source_name' => 'Reuters',
            'source_url' => 'https://reuters.com',
            'country' => ['us'],
            'language' => 'english',
            'pubDate' => '2026-05-16 12:00:00',
        ]);

        $this->assertSame('newsdata-io:abc123', $row['provider_item_id']);
        $this->assertSame('Breaking: Test Article', $row['title']);
        $this->assertSame('https://reuters.com/world/test-article', $row['url']);
        $this->assertSame('Reuters', $row['source_name']);
        $this->assertSame('US', $row['source_country']);
        // Zen audit 2026-05-16: normalise full-name "english" → ISO-2 "en"
        // so downstream posts.language filters get a consistent shape.
        $this->assertSame('en', $row['language']);
        $this->assertNotNull($row['published_at']);
    }

    public function test_language_normalisation_handles_iso2_and_full_names(): void
    {
        $fetcher = app(GrimbaNewsdataIoFetcher::class);
        $base = [
            'article_id' => 'lang-test',
            'title' => 'Title',
            'link' => 'https://example.com/test',
        ];

        // ISO-2 codes pass through.
        $this->assertSame('fr', $fetcher->normaliseArticle($base + ['language' => 'fr'])['language']);
        // Full names map.
        $this->assertSame('en', $fetcher->normaliseArticle($base + ['language' => 'English'])['language']);
        $this->assertSame('fr', $fetcher->normaliseArticle($base + ['language' => 'français'])['language']);
        $this->assertSame('es', $fetcher->normaliseArticle($base + ['language' => 'spanish'])['language']);
        // Unknown maps to null when not matching the 2-5-char ISO pattern.
        $this->assertNull($fetcher->normaliseArticle($base + ['language' => 'Klingon space dialect'])['language']);
        // Missing language = null.
        $this->assertNull($fetcher->normaliseArticle($base)['language']);
    }

    public function test_credit_counter_bumps_on_successful_call(): void
    {
        Http::fake([
            'newsdata.io/api/1/latest*' => Http::response([
                'status' => 'success',
                'results' => [],
            ], 200),
        ]);

        $this->assertSame(0, GrimbaProviderCredits::cached('newsdata-io'));

        app(GrimbaNewsdataIoFetcher::class)->fetch();

        $this->assertSame(1, GrimbaProviderCredits::cached('newsdata-io'));
    }
}
