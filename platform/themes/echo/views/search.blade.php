@php
    Theme::layout('grimba-chrome');
    $postStyle = 'grid';
    $query = request()->query('q', '');
    $total = $posts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
        ? $posts->total()
        : $posts->count();
@endphp

<section class="grimba-search-page container py-5">
    <header class="glass-panel p-4 p-md-5 mb-4">
        <span class="grimba-methodology__kicker">Recherche</span>
        <h1 class="grimba-methodology__title mt-2 mb-2">
            @if($query === '')
                Que cherchez-vous ?
            @else
                {{ $total }} {{ $total === 1 ? 'résultat' : 'résultats' }}
                <span class="opacity-75">pour «&nbsp;{{ $query }}&nbsp;»</span>
            @endif
        </h1>
        <form method="GET" action="{{ url('/search') }}" class="mt-3" role="search">
            <div class="d-flex gap-2 flex-wrap">
                <input type="search" name="q" value="{{ $query }}"
                       placeholder="Rechercher une histoire, un sujet, une source…"
                       class="flex-grow-1"
                       style="min-width: 280px; padding: 0.6rem 1rem; border-radius: 9999px; border: 1px solid var(--gn-rule); background: rgba(255,255,255,0.8);">
                <button type="submit" class="btn-grimba btn-grimba--solid">Chercher</button>
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
            <p class="mb-2"><strong>Aucun résultat pour «&nbsp;{{ $query }}&nbsp;».</strong></p>
            <p class="small opacity-75 mb-3">
                Essayez un autre mot-clé, ou parcourez les
                <a href="{{ url('/comparatif') }}">dossiers ouverts</a>,
                le <a href="{{ url('/angles-morts') }}">fil des angles morts</a>
                ou les <a href="{{ url('/sources') }}">sources classées</a>.
            </p>
        </div>
    @endif
</section>
