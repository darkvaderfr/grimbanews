@php
    Theme::layout('grimba-chrome');
    $postStyle = 'grid';
    $query = request()->query('q', '');
    $total = $posts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
        ? $posts->total()
        : $posts->count();
    $availableSources = $availableSources ?? collect();
    $availableOwners  = $availableOwners  ?? collect();
    $selectedSource   = $selectedSource   ?? null;
    $selectedBias     = $selectedBias     ?? null;
    $selectedOwner    = $selectedOwner    ?? '';
    $fromDate         = $fromDate         ?? '';
    $toDate           = $toDate           ?? '';
    $savedSearchCriteria = $savedSearchCriteria ?? \App\Support\GrimbaSavedSearches::normalize(request()->query());
    $savedSearchActive = (bool) ($savedSearchActive ?? false);
    $savedSearchLimitReached = (bool) ($savedSearchLimitReached ?? false);
    $savedSearchCount = (int) ($savedSearchCount ?? 0);
    $biasChoices      = [
        ''        => __('Tous biais'),
        'left'    => __('Gauche'),
        'center'  => __('Centre'),
        'right'   => __('Droite'),
        'unknown' => __('Non classé'),
    ];
@endphp

<section class="grimba-search-page container py-5">
    <header class="grimba-search-page__panel glass-panel p-4 p-md-5 mb-4">
        <span class="grimba-methodology__kicker">{{ __('Recherche') }}</span>
        <h1 class="grimba-methodology__title mt-2 mb-2">
            @if($query === '')
                {{ __('Que cherchez-vous ?') }}
            @else
                {{ trans_choice(':count résultat|:count résultats', $total, ['count' => $total]) }}
                <span class="grimba-search-page__query">{{ __('pour « :query »', ['query' => $query]) }}</span>
            @endif
        </h1>
        <form method="GET" action="{{ url('/search') }}" class="grimba-search-page__form mt-3" role="search">
            {{-- S339 — refactored to Bootstrap form-control / form-select
                  so the S322 dark-mode rules apply. Inline white-bg styles
                  dropped — they were overriding dark-mode tints. --}}
            <div class="row g-2 align-items-stretch">
                <div class="col-12 col-lg">
                    <input type="search" name="q" value="{{ $query }}"
                           placeholder="{{ __('Rechercher une histoire, un sujet, une source…') }}"
                           class="form-control form-control-lg"
                           style="border-radius: 9999px;">
                </div>
                <div class="col-6 col-md-4 col-lg-auto">
                    <select name="source" aria-label="{{ __('Filtrer par source') }}"
                            class="form-select form-select-lg" style="border-radius: 9999px;">
                        <option value="">{{ __('Toutes sources') }}</option>
                        @foreach($availableSources as $src)
                            <option value="{{ $src->id }}" @selected((int) $selectedSource === (int) $src->id)>
                                {{ $src->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4 col-lg-auto">
                    <select name="bias" aria-label="{{ __('Filtrer par biais') }}"
                            class="form-select form-select-lg" style="border-radius: 9999px;">
                        @foreach($biasChoices as $key => $label)
                            <option value="{{ $key }}" @selected((string) $selectedBias === (string) $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-auto">
                    <select name="owner" aria-label="{{ __('Filtrer par propriétaire') }}"
                            class="form-select form-select-lg" style="border-radius: 9999px;">
                        <option value="">{{ __('Tous propriétaires') }}</option>
                        @foreach($availableOwners as $owner)
                            <option value="{{ $owner }}" @selected((string) $selectedOwner === (string) $owner)>
                                {{ $owner }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-lg-auto">
                    <input type="date" name="from_date" value="{{ $fromDate }}"
                           aria-label="{{ __('Date de début') }}"
                           class="form-control form-control-lg" style="border-radius: 9999px;">
                </div>
                <div class="col-6 col-md-3 col-lg-auto">
                    <input type="date" name="to_date" value="{{ $toDate }}"
                           aria-label="{{ __('Date de fin') }}"
                           class="form-control form-control-lg" style="border-radius: 9999px;">
                </div>
                <div class="grimba-search-page__actions col-12 col-lg-auto d-flex align-items-center gap-2">
                    <button type="submit" class="btn-grimba btn-grimba--solid">{{ __('Chercher') }}</button>
                    @if($selectedSource || $selectedBias || $selectedOwner || $fromDate || $toDate)
                        <a href="{{ url('/search?q=' . urlencode($query)) }}"
                           class="small text-decoration-underline opacity-65"
                           style="color: var(--gn-ink-muted);">{{ __('Réinitialiser les filtres') }}</a>
                    @endif
                </div>
            </div>
        </form>

        @if(session('status'))
            <div class="grimba-search-alert mt-3">
                {{ session('status') }}
            </div>
        @endif

        @if($query !== '')
            <div class="grimba-saved-search mt-3">
                @auth('member')
                    <form method="POST" action="{{ route('public.saved-searches.store') }}" class="grimba-saved-search__inner">
                        @csrf
                        <input type="hidden" name="q" value="{{ $savedSearchCriteria['search_query'] }}">
                        <input type="hidden" name="source" value="{{ $savedSearchCriteria['source_id'] }}">
                        <input type="hidden" name="bias" value="{{ $savedSearchCriteria['bias'] }}">
                        <input type="hidden" name="owner" value="{{ $savedSearchCriteria['owner'] }}">
                        <input type="hidden" name="from_date" value="{{ $savedSearchCriteria['from_date'] }}">
                        <input type="hidden" name="to_date" value="{{ $savedSearchCriteria['to_date'] }}">

                        <div class="grimba-saved-search__copy">
                            <strong>{{ $savedSearchActive ? __('Recherche suivie') : __('Suivre cette recherche') }}</strong>
                            <span>
                                @if($savedSearchActive)
                                    {{ __('Les nouveaux articles arriveront dans votre alerte hebdomadaire.') }}
                                @elseif($savedSearchLimitReached)
                                    {{ trans_choice(':count alerte active|:count alertes actives', $savedSearchCount, ['count' => $savedSearchCount]) }}
                                @else
                                    {{ __('Recevez les nouveaux articles qui correspondent à ces filtres.') }}
                                @endif
                            </span>
                        </div>

                        @if($savedSearchActive || $savedSearchLimitReached)
                            <a href="{{ url('/account') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                                {{ __('Gérer') }}
                            </a>
                        @else
                            <button type="submit" class="btn-grimba btn-grimba--solid btn-grimba--sm">
                                {{ __('Activer') }}
                            </button>
                        @endif
                    </form>
                @else
                    <div class="grimba-saved-search__inner">
                        <div class="grimba-saved-search__copy">
                            <strong>{{ __('Suivre cette recherche') }}</strong>
                            <span>{{ __('Connectez-vous pour recevoir les nouveaux articles chaque semaine.') }}</span>
                        </div>
                        <a href="{{ route('public.member.login') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                            {{ __('Connexion') }}
                        </a>
                    </div>
                @endauth
            </div>
        @endif
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
        {{-- S339 — cinematic empty state. Concrete CTAs instead of an
              inline list of underlined links. --}}
        <div class="glass-panel p-4 p-md-5 text-center" style="max-width: 720px; margin: 0 auto;">
            <span class="grimba-methodology__kicker">{{ __('Aucun résultat') }}</span>
            <h2 class="grimba-methodology__title mt-2 mb-3" style="font-size: clamp(22px, 2.4vw, 28px);">
                {{ __('Pas de match pour « :query »', ['query' => $query]) }}
            </h2>
            <p class="opacity-85 mb-4" style="max-width: 50ch; margin-left: auto; margin-right: auto;">
                {{ __("Essayez un mot-clé plus court ou retirez un filtre. Ou explorez les surfaces où l'actualité circule par dossier ou par camp.") }}
            </p>
            <div class="d-flex gap-2 flex-wrap justify-content-center">
                <a href="{{ url('/search') }}" class="btn-grimba btn-grimba--solid">{{ __('Réinitialiser la recherche') }}</a>
                <a href="{{ url('/comparatif') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">{{ __('Tous les dossiers') }}</a>
                <a href="{{ url('/angles-morts') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">{{ __('Angles morts') }}</a>
                <a href="{{ url('/sources') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">{{ __('Sources classées') }}</a>
            </div>
        </div>
    @endif
</section>
