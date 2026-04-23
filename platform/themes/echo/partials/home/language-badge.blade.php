@php
    /**
     * Language badge for a post — shows the article's ORIGINAL language
     * so readers know what they're consuming regardless of their UI locale.
     *
     * @var \Botble\Blog\Models\Post $post
     * @var bool $compact
     */
    $lang = strtolower((string) ($post->original_language ?? ''));
    $labelFr = [
        'fr' => 'FR', 'en' => 'EN', 'es' => 'ES', 'pt' => 'PT', 'de' => 'DE', 'ar' => 'AR',
    ][$lang] ?? null;
    $compact = $compact ?? true;
@endphp

@if($labelFr)
    <span class="grimba-lang-badge @if($compact) grimba-lang-badge--sm @endif"
          title="{{ __('Article en :lang', ['lang' => strtoupper($lang)]) }}">
        {{ $labelFr }}
    </span>
@endif
