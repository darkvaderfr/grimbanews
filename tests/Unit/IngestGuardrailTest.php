<?php

namespace Tests\Unit;

use App\Support\GrimbaIngestGuardrails;
use PHPUnit\Framework\TestCase;

class IngestGuardrailTest extends TestCase
{
    public function test_ingest_guardrails_report_all_readiness_flags(): void
    {
        $post = (object) [
            'source_id' => null,
            'source_name' => null,
            'bias_rating' => 'unknown',
            'original_language' => 'en',
            'translated_name' => null,
            'description' => 'Short.',
        ];

        $this->assertSame([
            'source manquante',
            'biais inconnu',
            'traduction manquante',
            'extrait trop court',
        ], GrimbaIngestGuardrails::flags($post));
    }

    public function test_ingest_guardrails_accept_ready_french_draft(): void
    {
        $post = (object) [
            'source_id' => 1,
            'source_name' => 'Fixture Source',
            'bias_rating' => 'center',
            'original_language' => 'fr',
            'translated_name' => null,
            'description' => str_repeat('Description assez longue pour passer les garde-fous. ', 2),
        ];

        $this->assertSame([], GrimbaIngestGuardrails::flags($post));
    }

    public function test_ingest_guardrails_tally_counts_ready_and_blocked_reasons(): void
    {
        $ready = (object) [
            'source_id' => 1,
            'source_name' => 'Fixture Source',
            'bias_rating' => 'center',
            'original_language' => 'fr',
            'translated_name' => null,
            'description' => str_repeat('Description assez longue pour passer les garde-fous. ', 2),
        ];
        $blocked = (object) [
            'source_id' => null,
            'source_name' => null,
            'bias_rating' => 'unknown',
            'original_language' => 'en',
            'translated_name' => null,
            'description' => 'Short.',
        ];

        $stats = GrimbaIngestGuardrails::tally([$ready, $blocked]);

        $this->assertSame(2, $stats['total']);
        $this->assertSame(1, $stats['ready']);
        $this->assertSame(1, $stats['blocked']);
        $this->assertSame(1, $stats['reasons']['source manquante']);
        $this->assertSame(1, $stats['reasons']['biais inconnu']);
        $this->assertSame(1, $stats['reasons']['traduction manquante']);
        $this->assertSame(1, $stats['reasons']['extrait trop court']);
    }
}
