@php
    /**
     * Source Attribution header for single posts.
     * Shows source, bias, credibility bar, ownership, blindspot notice.
     *
     * @var \Botble\Blog\Models\Post $post
     */
    use Illuminate\Support\Facades\DB;

    // Ensure locale is set from cookie before __() lookups + disclaimer logic.
    $__grimbaLang = (string) request()->cookie('grimba_lang', '');
    if ($__grimbaLang === 'en' || $__grimbaLang === 'fr') {
        app()->setLocale($__grimbaLang);
    }

    $source = null;
    if ($post->source_id) {
        $source = DB::table('news_sources')->where('id', $post->source_id)->first();
    }

    $biasLabel = match ($post->bias_rating ?? null) {
        'left'   => 'Gauche',
        'center' => 'Centre',
        'right'  => 'Droite',
        default  => null,
    };

    $ownershipLabel = [
        'state'       => 'État',
        'corporate'   => 'Privé',
        'independent' => 'Indépendant',
        'nonprofit'   => 'Associatif',
    ];

    $credScore = $source->credibility_score ?? $post->credibility_score ?? null;
    $credColor = $credScore === null ? null : (
        $credScore >= 85 ? '#22c55e' : ($credScore >= 70 ? '#eab308' : '#ef4444')
    );
@endphp

<section class="grimba-post-attribution">
    <div class="grimba-post-attribution__row">
        <div class="grimba-post-attribution__source">
            <span class="grimba-post-attribution__kicker">Source</span>
            <strong class="grimba-post-attribution__name">
                @if($source && $source->website)
                    <a href="https://{{ $source->website }}" target="_blank" rel="noopener">{{ $source->name ?? $post->source_name }}</a>
                @else
                    {{ $source->name ?? $post->source_name ?? '—' }}
                @endif
            </strong>
            @if($source?->country || $source?->language)
                <span class="grimba-post-attribution__meta small opacity-75">
                    @if($source?->country){{ $source->country }}@endif
                    @if($source?->country && $source?->language) · @endif
                    @if($source?->language){{ strtoupper($source->language) }}@endif
                </span>
            @endif
        </div>

        @if($biasLabel)
            <span class="d-inline-flex flex-column align-items-start gap-1">
                {!! Theme::partial('bias-badge', [
                    'bias'      => $post->bias_rating,
                    'showLabel' => true,
                    'size'      => 'md',
                ]) !!}
                @include(Theme::getThemeNamespace('partials.bias-confidence'), [
                    'source' => $source,
                    'post' => $post,
                ])
            </span>
        @endif

        @if($post->is_blindspot)
            <span class="blindspot-badge">Angle mort</span>
        @endif

        @if($source?->ownership_type)
            <span class="grimba-post-attribution__ownership">
                {{ $ownershipLabel[$source->ownership_type] ?? ucfirst($source->ownership_type) }}
            </span>
        @endif
    </div>

    @if($credScore !== null)
        <div class="grimba-post-attribution__cred">
            <div class="d-flex justify-content-between small mb-1">
                <span class="opacity-75">{{ __('Crédibilité de la source') }}</span>
                <strong>{{ $credScore }}/100</strong>
            </div>
            <div class="grimba-post-attribution__cred-bar">
                <div style="width:{{ $credScore }}%;background:{{ $credColor }};"></div>
            </div>
        </div>
    @endif

    @if($post->is_blindspot)
        <div class="grimba-post-attribution__blindspot-notice">
            <strong>{{ __('Angle mort') }}.</strong>
            {{ __('Cette histoire n\'est couverte que par un seul côté du spectre politique.') }}
            <a href="{{ url('/angles-morts') }}">{{ __('Voir d\'autres angles morts') }} →</a>
        </div>
    @endif

    {{-- S170 — translation feature dropped; the language disclaimers
         (was here from S97) are gone. Articles render in their
         original language. --}}
</section>
