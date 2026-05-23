@php
    /**
     * B9/S202 — small confidence indicator shown near bias badges when
     * the source signal needs editorial review.
     *
     * @var object|null $source Optional news_sources row
     * @var object|null $post   Optional post row/model
     */
    $source = $source ?? null;
    $post = $post ?? null;
    $cred = $source->credibility_score ?? $post->credibility_score ?? null;
    $hasLinkedSource = (bool) ($source?->id ?? $post?->source_id ?? false);

    $reason = null;
    if ($cred !== null && (int) $cred < 50) {
        $reason = __('source faible confiance');
    } elseif (! $hasLinkedSource && in_array($post->bias_rating ?? null, ['left', 'center', 'right'], true)) {
        $reason = __('source non liée');
    }
@endphp

@if($reason)
    <span class="grimba-bias-confidence" title="{{ __('Biais auto-détecté') }} · {{ $reason }}">
        {{ __('biais auto-détecté') }}
    </span>
@endif
