@php
    Theme::layout('grimba-chrome');
    /**
     * @var object $source
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $posts
     * @var array $stats
     */

    // S309 — adopt the Wave 0 chip atoms for 7-tier bias / 5-tier
    // factuality / 8-cat ownership. The 3-tier $biasColor stays as the
    // header's accent rail (it's our visual anchor).
    $__biasTier = \App\Ground\Bias::tier($source->bias_rating ?? null, $source->bias_score ?? null);
    $__factTier = \App\Ground\Factuality::tier($source->credibility_score ?? null);
    $__ownCat   = \App\Ground\Ownership::category($source->ownership_type ?? null, $source->owner_name ?? null);

    $biasColor = \App\Ground\Bias::color(\App\Ground\Bias::side($__biasTier) === 'unknown' ? 'unknown' : \App\Ground\Bias::side($__biasTier));
    $biasLabel = \App\Ground\Bias::label($__biasTier);

    $biasScore = is_numeric($source->bias_score ?? null) ? max(-2.0, min(2.0, (float) $source->bias_score)) : null;
    $biasScorePosition = $biasScore === null ? null : (($biasScore + 2.0) / 4.0) * 100;

    $known = ($stats['left'] ?? 0) + ($stats['center'] ?? 0) + ($stats['right'] ?? 0);
    $pct = [
        'left'   => $known ? round(($stats['left']   ?? 0) * 100 / $known) : 0,
        'center' => $known ? round(($stats['center'] ?? 0) * 100 / $known) : 0,
        'right'  => $known ? round(($stats['right']  ?? 0) * 100 / $known) : 0,
    ];
@endphp

<section class="grimba-source py-5">
    <div class="container">

        <header class="glass-panel p-4 p-md-5 mb-4" style="border-left:6px solid {{ $biasColor }};">
            <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                <span class="grimba-methodology__kicker">{{ __('Source') }}{{ $source->country ? ' · ' . $source->country : '' }}</span>

                {{-- S309 — three Ground-fidelity chips. --}}
                {!! Theme::partial('bias-chip', ['tier' => $__biasTier, 'size' => 'md', 'showLabel' => true]) !!}
                @if($__factTier !== 'unknown')
                    {!! Theme::partial('factuality-chip', ['tier' => $__factTier, 'size' => 'md', 'showLabel' => true]) !!}
                @endif
                @if($__ownCat !== 'other')
                    {!! Theme::partial('ownership-chip', ['category' => $__ownCat, 'owner' => $source->owner_name, 'size' => 'md', 'showLabel' => true]) !!}
                @endif

                @if($source->owner_name)
                    <span class="small opacity-65">
                        {{ __('propriété de') }} <strong>{{ $source->owner_name }}</strong>
                    </span>
                @endif
            </div>

            <h1 class="grimba-methodology__title mt-1 mb-3" style="font-size: clamp(32px, 4vw, 52px);">
                {{ $source->name }}
            </h1>

            @if ($source->description)
                <p class="mb-3 opacity-85" style="font-size:17px; line-height:1.5;">
                    {!! BaseHelper::clean($source->description) !!}
                </p>
            @else
                <p class="mb-3 opacity-75">
                    {{ $source->name }} —
                    @if($source->bias_rating === 'left')
                        {{ __('ligne éditoriale orientée à gauche.') }}
                    @elseif($source->bias_rating === 'right')
                        {{ __('ligne éditoriale orientée à droite.') }}
                    @elseif($source->bias_rating === 'center')
                        {{ __('ligne éditoriale centriste / factuelle.') }}
                    @else
                        {{ __('positionnement éditorial non encore classé.') }}
                    @endif
                    {{ __('Couverture archivée par GrimbaNews.') }}
                </p>
            @endif

            @if ($source->website)
                <a href="https://{{ ltrim($source->website, '/') }}" target="_blank" rel="noopener"
                   class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                    {{ __('Visiter :site', ['site' => $source->website]) }} ↗
                </a>
            @endif

            @if($biasScore !== null)
                <div class="mt-4" style="max-width:540px;">
                    <div class="d-flex justify-content-between small opacity-75 mb-2">
                        <span>{{ __('Très gauche') }}</span>
                        <span>{{ __('Centre') }}</span>
                        <span>{{ __('Très droite') }}</span>
                    </div>
                    <div style="position:relative;height:12px;border-radius:999px;background:linear-gradient(90deg,#3b82f6 0%,#a8a8a8 50%,#e84c3d 100%);">
                        <span style="
                            position:absolute;top:50%;left:{{ $biasScorePosition }}%;
                            width:22px;height:22px;border-radius:999px;
                            transform:translate(-50%,-50%);
                            background:var(--gn-paper,#f6f1e8);
                            border:3px solid {{ $biasColor }};
                            box-shadow:0 8px 20px rgba(0,0,0,.18);
                        " aria-hidden="true"></span>
                    </div>
                    <p class="small opacity-60 mt-2 mb-0">
                        <span class="text-uppercase">{{ __('score biais') }}</span>
                        <strong>{{ number_format($biasScore, 1) }}</strong>
                        · {{ __('Score fin -2.0 à +2.0. Le badge L/C/R reste compatible avec les anciennes vues.') }}
                    </p>
                </div>
            @endif
        </header>

        @if($known > 0)
            <section class="glass-panel p-3 p-md-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <h2 class="h6 mb-0">{{ __('Distribution sur GrimbaNews') }}</h2>
                    @include(Theme::getThemeNamespace('partials.info-pill'), [
                        'size' => 'sm',
                        'tone' => 'soft',
                        'body' => __("Comment les articles de cette source se répartissent dans les rubriques de GrimbaNews. Aide à voir où la source publie le plus."),
                    ])
                </div>
                <p class="small opacity-75 mb-2">
                    {{ trans_choice(':count article archivé|:count articles archivés', $stats['total'], ['count' => $stats['total']]) }} · {{ __('biais déclaré') }}
                    <strong style="color:{{ $biasColor }};">{{ $biasLabel }}</strong>
                </p>
                <div style="display:flex;height:14px;border-radius:9999px;overflow:hidden;background:rgba(0,0,0,.08);">
                    <div style="width:{{ $pct['left'] }}%;background:#3b82f6;" title="{{ __('Gauche') }} {{ $pct['left'] }}%"></div>
                    <div style="width:{{ $pct['center'] }}%;background:#a8a8a8;" title="{{ __('Centre') }} {{ $pct['center'] }}%"></div>
                    <div style="width:{{ $pct['right'] }}%;background:#e84c3d;" title="{{ __('Droite') }} {{ $pct['right'] }}%"></div>
                </div>
                <div class="d-flex justify-content-between small mt-2">
                    <span style="color:#3b82f6;font-weight:600;">{{ __('Gauche') }} {{ $pct['left'] }}%</span>
                    <span style="color:#a8a8a8;font-weight:600;">{{ __('Centre') }} {{ $pct['center'] }}%</span>
                    <span style="color:#e84c3d;font-weight:600;">{{ __('Droite') }} {{ $pct['right'] }}%</span>
                </div>
                <p class="small opacity-60 mt-2 mb-0">
                    {{ __("Calcul basé sur le biais individuel des articles archivés depuis cette source. Quand cette barre s'écarte fortement du biais éditorial déclaré, elle révèle des angles inattendus dans la couverture.") }}
                </p>
            </section>
        @endif

        <h2 class="h5 mb-3">{{ __('Articles récents de :source', ['source' => $source->name]) }}</h2>

        @if($posts->isEmpty())
            <div class="glass-panel p-4 text-center">
                <p class="mb-0">{{ __('Aucun article archivé pour cette source — encore.') }}</p>
                <p class="mb-0 small opacity-75">
                    {{ __('Les nouveaux articles apparaissent ici dès que le flux RSS est traité (toutes les 30 min).') }}
                </p>
            </div>
        @else
            <div class="row g-4">
                @foreach($posts as $post)
                    <div class="col-lg-4 col-md-6 col-12">
                        @include(Theme::getThemeNamespace('partials.blog.post.partials.items.grid'), ['post' => $post])
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {!! $posts->links() !!}
            </div>
        @endif

        {{-- S309 — Sources avec un biais similaire. Pulls 6 sources
              sharing the same 3-tier side that aren't this source. --}}
        @php
            $__sourceSide = \App\Ground\Bias::side($__biasTier);
            $__similarSources = collect();
            if ($__sourceSide !== 'unknown') {
                // Safe column list: pre-logo-migration installs don't have
                // logo_url / logo_status / logo_checked_at, so we resolve
                // them defensively at render time.
                $__simCols = ['id', 'slug', 'name', 'website', 'bias_rating', 'bias_score', 'credibility_score', 'ownership_type', 'owner_name', 'country'];
                if (\Illuminate\Support\Facades\Schema::hasColumn('news_sources', 'logo_url')) {
                    $__simCols[] = 'logo_url';
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('news_sources', 'logo_status')) {
                    $__simCols[] = 'logo_status';
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('news_sources', 'logo_checked_at')) {
                    $__simCols[] = 'logo_checked_at';
                }
                $__similarSources = \Illuminate\Support\Facades\DB::table('news_sources')
                    ->where('id', '!=', $source->id)
                    ->where('bias_rating', $__sourceSide)
                    ->orderByDesc('credibility_score')
                    ->orderBy('name')
                    ->limit(6)
                    ->get($__simCols);
            }
        @endphp

        @if($__similarSources->isNotEmpty())
            <section class="grimba-similar-sources mt-5 pt-4" style="border-top:1px dashed rgba(0,0,0,0.12);">
                <h2 class="h6 mb-3" style="font-family:'Public Sans',system-ui,sans-serif; font-weight:700; letter-spacing:0.4px; text-transform:uppercase; font-size:13px; opacity:0.75;">
                    {{ __('Sources avec un biais similaire') }}
                </h2>
                <div class="row g-3">
                    @foreach($__similarSources as $sim)
                        @php
                            $__simBiasTier = \App\Ground\Bias::tier($sim->bias_rating ?? null, $sim->bias_score ?? null);
                            $__simFactTier = \App\Ground\Factuality::tier($sim->credibility_score ?? null);
                            $__simOwnCat   = \App\Ground\Ownership::category($sim->ownership_type ?? null, $sim->owner_name ?? null);
                        @endphp
                        <div class="col-12 col-md-6 col-lg-4">
                            <a href="{{ url('/sources/' . $sim->slug) }}"
                               class="grimba-similar-source">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    {!! Theme::partial('source-logo', [
                                        'source_id' => $sim->id,
                                        'name'      => $sim->name,
                                        'website'   => $sim->website,
                                        'logo_url'  => $sim->logo_url ?? null,
                                        'logo_status' => $sim->logo_status ?? 'unknown',
                                        'logo_checked_at' => $sim->logo_checked_at ?? null,
                                        'size'      => 28,
                                        'color'     => \App\Ground\Bias::color($__simBiasTier),
                                    ]) !!}
                                    <strong style="font-size:14.5px; font-family:'Public Sans',system-ui,sans-serif;">
                                        {{ $sim->name }}
                                    </strong>
                                </div>
                                <div class="d-flex align-items-center flex-wrap gap-1">
                                    {!! Theme::partial('bias-chip', ['tier' => $__simBiasTier, 'size' => 'sm']) !!}
                                    @if($__simFactTier !== 'unknown')
                                        {!! Theme::partial('factuality-chip', ['tier' => $__simFactTier, 'size' => 'sm']) !!}
                                    @endif
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <p class="mt-5">
            <a href="{{ url('/sources') }}" class="btn-grimba btn-grimba--ghost">
                ← {{ __('Toutes les sources') }}
            </a>
        </p>
    </div>
</section>
