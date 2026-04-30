<?php

namespace Tests\Feature;

use App\Services\GrimbaArticleImageScraper;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ArticleImageScraperTest extends TestCase
{
    public function test_article_image_scraper_reads_jsonld_image_metadata(): void
    {
        Http::fake([
            'https://publisher.test/story' => Http::response(
                '<html><head><script type="application/ld+json">{"image":{"url":"/media/story.webp"}}</script></head></html>',
                200,
                ['Content-Type' => 'text/html']
            ),
        ]);

        [$url, $method] = app(GrimbaArticleImageScraper::class)
            ->extractFromUrl('https://publisher.test/story');

        $this->assertSame('https://publisher.test/media/story.webp', $url);
        $this->assertSame('jsonld', $method);
    }

    public function test_article_image_scraper_uses_largest_srcset_candidate(): void
    {
        Http::fake([
            'https://publisher.test/srcset' => Http::response(
                '<html><body><img srcset="/small.jpg 320w, /large.jpg 1280w" alt=""></body></html>',
                200,
                ['Content-Type' => 'text/html']
            ),
        ]);

        [$url, $method] = app(GrimbaArticleImageScraper::class)
            ->extractFromUrl('https://publisher.test/srcset');

        $this->assertSame('https://publisher.test/large.jpg', $url);
        $this->assertSame('srcset', $method);
    }
}
