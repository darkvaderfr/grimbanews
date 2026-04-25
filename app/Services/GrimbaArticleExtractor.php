<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/*
 * S163 — best-effort full-article body extractor.
 *
 * GrimbaNews' paid-tier reading experience needs the upstream article
 * body, not just the description. NewsAPI free tier truncates content
 * at ~200 chars so we can't lift it from the API.
 *
 * Strategy (no external service, no Composer dep):
 *   1. Fetch the article URL with a polite UA + 15s timeout
 *   2. Parse the HTML into a DOMDocument
 *   3. Score each <article>, <main>, and content-class element by
 *      its visible-text length (a crude readability proxy)
 *   4. Pick the highest-scoring element, strip nav / aside / script /
 *      style / form / iframe / button / figure-caption / share-bar,
 *      keep paragraphs + headings + blockquotes + simple inline tags
 *   5. Return cleaned HTML + a status string
 *
 * Anything we can't extract returns null + an error string so the
 * caller can record it. Paywall pages, JS-only sites, and aggressive
 * bot blocks are common failures — we degrade gracefully.
 */
class GrimbaArticleExtractor
{
    private const USER_AGENT = 'GrimbaNewsBot/1.0 (+https://grimbanews.com/bot)';
    private const TIMEOUT = 15;

    /** Tags allowed in extracted HTML — everything else is stripped. */
    private const KEEP_TAGS = [
        'p', 'h2', 'h3', 'h4', 'blockquote', 'ul', 'ol', 'li',
        'strong', 'b', 'em', 'i', 'a', 'br',
    ];

    /** Selectors / classes that look like article body vs nav/cruft. */
    private const CONTENT_HINTS = [
        'article', 'main', 'article-body', 'story-body', 'entry-content',
        'post-content', 'article__content', 'article-content', 'story__body',
        'body__inner', 'content-body', 'article-text', 'article__body',
        'post-body', 'blog-post-body', 'article-wrap', 'article__wrap',
    ];

    private const CRUFT_HINTS = [
        'nav', 'aside', 'footer', 'header', 'sidebar', 'related',
        'share', 'social', 'comments', 'newsletter', 'subscribe',
        'ad-', 'advert', 'promo', 'recommended', 'paywall',
        'cookie', 'modal', 'popup', 'breadcrumb',
    ];

    /**
     * @return array{ok:bool, html:?string, error:?string}
     */
    public function extractFromUrl(string $url): array
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return ['ok' => false, 'html' => null, 'error' => 'invalid url'];
        }

        try {
            $res = Http::withUserAgent(self::USER_AGENT)
                ->withHeaders([
                    'Accept'          => 'text/html,application/xhtml+xml',
                    'Accept-Language' => 'fr,en;q=0.6',
                ])
                ->timeout(self::TIMEOUT)
                ->connectTimeout(8)
                ->withOptions(['allow_redirects' => ['max' => 5]])
                ->get($url);

            if (! $res->successful()) {
                return ['ok' => false, 'html' => null, 'error' => 'http ' . $res->status()];
            }

            $html = (string) $res->body();
            if (mb_strlen($html) < 500) {
                return ['ok' => false, 'html' => null, 'error' => 'empty body'];
            }

            return $this->extractFromHtml($html);
        } catch (Throwable $e) {
            return ['ok' => false, 'html' => null, 'error' => 'fetch failed: ' . $e->getMessage()];
        }
    }

    /**
     * @return array{ok:bool, html:?string, error:?string}
     */
    public function extractFromHtml(string $html): array
    {
        // Best-effort UTF-8 normalization. Many sites ship Latin-1 or
        // partial encodings — DOMDocument needs UTF-8.
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);

        // Drop obvious noise globally before scoring (script/style/etc.)
        foreach (['script', 'style', 'noscript', 'svg', 'form', 'iframe', 'button'] as $tag) {
            $nodes = $xpath->query("//{$tag}");
            if ($nodes) foreach (iterator_to_array($nodes) as $n) $n->parentNode?->removeChild($n);
        }

        // Score candidate containers.
        $best = null;
        $bestScore = 0;

        $candidates = $xpath->query("//article | //main | //*[contains(@class, 'article') or contains(@class, 'story') or contains(@class, 'post-content') or contains(@class, 'content-body')]");

        if ($candidates) {
            foreach ($candidates as $node) {
                if (! ($node instanceof \DOMElement)) continue;
                $score = $this->scoreNode($node);
                if ($score > $bestScore) {
                    $best = $node;
                    $bestScore = $score;
                }
            }
        }

        // Fallback: take <body> if no candidate scored well enough.
        if (! $best || $bestScore < 200) {
            $bodies = $xpath->query('//body');
            if ($bodies && $bodies->length > 0) {
                $best = $bodies->item(0);
            }
        }

        if (! $best) {
            return ['ok' => false, 'html' => null, 'error' => 'no candidate found'];
        }

        // Strip cruft children inside the chosen container.
        $this->stripCruft($best, $xpath);

        // Render allowed tags only.
        $cleaned = $this->renderAllowedTags($best);

        $textLen = mb_strlen(strip_tags($cleaned));
        if ($textLen < 200) {
            return ['ok' => false, 'html' => null, 'error' => 'too short (' . $textLen . ' chars)'];
        }

        return ['ok' => true, 'html' => $cleaned, 'error' => null];
    }

    private function scoreNode(\DOMElement $node): int
    {
        $text = trim((string) $node->textContent);
        $score = mb_strlen($text);

        // Bonus for matching content-y class names.
        $cls = mb_strtolower((string) $node->getAttribute('class'));
        foreach (self::CONTENT_HINTS as $hint) {
            if (str_contains($cls, $hint)) $score += 500;
        }
        // Penalty for matching cruft class names.
        foreach (self::CRUFT_HINTS as $hint) {
            if (str_contains($cls, $hint)) $score -= 1000;
        }
        return $score;
    }

    private function stripCruft(\DOMNode $root, DOMXPath $xpath): void
    {
        if (! ($root instanceof \DOMElement)) return;

        // Remove children whose class names look like cruft.
        $bad = [];
        $walker = $xpath->query('.//*', $root);
        if ($walker) {
            foreach ($walker as $n) {
                if (! ($n instanceof \DOMElement)) continue;
                $cls = mb_strtolower((string) $n->getAttribute('class'));
                $id  = mb_strtolower((string) $n->getAttribute('id'));
                $sig = $cls . ' ' . $id;
                foreach (self::CRUFT_HINTS as $hint) {
                    if (str_contains($sig, $hint)) {
                        $bad[] = $n;
                        break;
                    }
                }
            }
        }
        foreach ($bad as $n) $n->parentNode?->removeChild($n);
    }

    private function renderAllowedTags(\DOMNode $node): string
    {
        $doc = $node->ownerDocument;
        $html = $doc?->saveHTML($node) ?? '';

        // Strip everything except KEEP_TAGS.
        $allowedList = '<' . implode('><', self::KEEP_TAGS) . '>';
        $html = strip_tags($html, $allowedList);

        // Drop attributes except href on <a>.
        $html = preg_replace_callback(
            '#<(\w+)([^>]*)>#i',
            function ($m) {
                $tag = strtolower($m[1]);
                if ($tag === 'a' && preg_match('/href\s*=\s*"([^"]+)"/i', $m[2], $h)) {
                    return '<a href="' . htmlspecialchars($h[1], ENT_QUOTES) . '" target="_blank" rel="noopener">';
                }
                return '<' . $tag . '>';
            },
            $html
        ) ?? $html;

        // Collapse whitespace.
        $html = preg_replace('/\s+/u', ' ', $html) ?? $html;
        $html = preg_replace('#\s*</p>\s*#', '</p>', $html) ?? $html;
        $html = preg_replace('#\s*<p>\s*#', '<p>', $html) ?? $html;

        return trim($html);
    }
}
