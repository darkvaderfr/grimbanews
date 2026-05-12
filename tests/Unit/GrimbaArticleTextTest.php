<?php

namespace Tests\Unit;

use App\Support\GrimbaArticleText;
use PHPUnit\Framework\TestCase;

class GrimbaArticleTextTest extends TestCase
{
    public function test_strips_encoded_newsapi_truncation_markers(): void
    {
        $this->assertSame(
            '<p>Encoded article body</p>',
            GrimbaArticleText::stripNewsApiTruncationMarker('<p>Encoded article body &hellip; &#91;+4285 chars&#93;</p>')
        );

        $this->assertSame(
            'Plain article body',
            GrimbaArticleText::stripNewsApiTruncationMarker('Plain article body ... &lbrack;+12 chars&rbrack;')
        );
    }
}
