@php
    Theme::layout('grimba-chrome');
    $postStyle = 'grid';
    $query = request()->query('q', '');
    $total = $posts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
        ? $posts->total()
        : $posts->count();
    $availableSources = $availableSources ?? collect();
    $selectedSource   = $selectedSource   ?? null;
    $selectedBias     = $selectedBias     ?? null;
    $biasChoices      = [
        ''        => __('Tous biais'),
        'left'    => __('Gauche'),
        'center'  => __('Centre'),
        'right'   => __('Droite'),
        'unknown' => __('Non classé'),
    ];
@endphp

<section class="grimba-search-page container py-5">
    <header class="glass-panel p-4 p-md-5 mb-4">
        <span class="grimba-methodology__kicker">{{ __('Recherche') }}</span>
        <h1 class="grimba-methodology__title mt-2 mb-2">
            @if($query === '')
                {{ __('Que cherchez-vous ?') }}
            @else
                {{ trans_choice(':count résultat|:count résultats', $total, ['count' => $total]) }}
                <span class="opacity-75">{{ __('pour « :query »', ['query' => $query]) }}</span>
            @endif
        </h1>
        <form method="GET" action="{{ url('/search') }}" class="mt-3" role="search">
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <input type="search" name="q" value="{{ $query }}"
                       placeholder="{{ __('Rechercher une histoire, un sujet, une source…') }}"
                       class="flex-grow-1"
                       style="min-width: 280px; padding: 0.6rem 1rem; border-radius: 9999px; border: 1px solid var(--gn-rule); background: rgba(255,255,255,0.8);">

                <select name="source" aria-label="{{ __('Filtrer par source') }}"
                        style="padding: 0.6rem 1rem; border-radius: 9999px; border: 1px solid var(--gn-rule); background: rgba(255,255,255,0.8);">
                    <option value="">{{ __('Toutes sources') }}</option>
                    @foreach($availableSources as $src)
                        <option value="{{ $src->id }}" @selected((int) $selectedSource === (int) $src->id)>
                            {{ $src->name }}
                        </option>
                    @endforeach
                </select>

                <select name="bias" aria-label="{{ __('Filtrer par biais') }}"
                        style="padding: 0.6rem 1rem; border-radius: 9999px; border: 1px solid var(--gn-rule); background: rgba(255,255,255,0.8);">
                    @foreach($biasChoices as $key => $label)
                        <option value="{{ $key }}" @selected((string) $selectedBias === (string) $key)>{{ $label }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn-grimba btn-grimba--solid">{{ __('Chercher') }}</button>

                @if($selectedSource || $selectedBias)
                    <a href="{{ url('/search?q=' . urlencode($query)) }}"
                       class="small" style="color: var(--gn-ink); opacity: .6;">{{ __('Réinitialiser les filtres') }}</a>
                @endif
            </div>
        </form>
    </header>

    @if ($posts->isNotEmpty())
        <div class="row g-4">
            @foreach($posts as $post)
                <div class="col-lg-4 col-md-6 col-12">
                    @include(Theme::getThemeNamespace('partials.blog.post.partials.items.grid'), ['post' => $post])
                </div>
            @endforeach
        </div>

        @if($posts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="mt-4">
                {!! $posts->withQueryString()->links() !!}
            </div>
        @endif
    @elseif($query !== '')
        <div class="glass-panel p-4 text-center">
            <p class="mb-2"><strong>{{ __('Aucun résultat pour « :query ».', ['query' => $query]) }}</strong></p>
            <p class="small opacity-75 mb-3">
                {{ __('Essayez un autre mot-clé, ou parcourez les') }}
                <a href="{{ url('/comparatif') }}">{{ __('dossiers ouverts') }}</a>,
                {{ __('le') }} <a href="{{ url('/angles-morts') }}">{{ __('fil des angles morts') }}</a>
                {{ __('ou les') }} <a href="{{ url('/sources') }}">{{ __('sources classées') }}</a>.
            </p>
        </div>
    @endif
</section>
