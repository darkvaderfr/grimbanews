@php
    Theme::layout('grimba-chrome');
    /**
     * @var object $source
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $posts
     * @var array $stats
     */

    $biasColor = match ($source->bias_rating) {
        'left'   => '#3b82f6',
        'center' => '#a8a8a8',
        'right'  => '#e84c3d',
        default  => '#6b6459',
    };
    $biasLabel = match ($source->bias_rating) {
        'left'   => __('Gauche'),
        'center' => __('Centre'),
        'right'  => __('Droite'),
        default  => __('Non classé'),
    };
    $ownershipLabel = match ($source->ownership_type) {
        'independent'    => __('Indépendant'),
        'corporate'      => __('Privé'),
        'public'         => __('Public'),
        'state-owned'    => __('État'),
        'foundation'     => __('Fondation'),
        'cooperative'    => __('Coopérative'),
        default          => '—',
    };
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
            <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
                <span class="grimba-methodology__kicker">{{ __('Source') }} · {{ $source->country ?: '—' }}</span>
                <span style="
                        display:inline-block; padding:4px 10px;
                        border-radius:999px;
                        background: {{ $biasColor }}1a;
                        color: {{ $biasColor }};
                        font-size:12px; font-weight:700; letter-spacing:0.4px; text-transform:uppercase;
                    ">{{ $biasLabel }}</span>
                <span class="small opacity-75">
                    {{ __('Crédibilité') }} {{ $source->credibility_score ?? '—' }} · {{ $ownershipLabel }}
                    @if($biasScore !== null)
                        · {{ __('score biais') }} <strong>{{ number_format($biasScore, 1) }}</strong>
                    @endif
                    @if($source->owner_name)
                        · {{ __('propriété de') }} <strong>{{ $source->owner_name }}</strong>
                    @endif
                </span>
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
                        {{ __('Score fin -2.0 à +2.0. Le badge L/C/R reste compatible avec les anciennes vues.') }}
                    </p>
                </div>
            @endif
        </header>

        @if($known > 0)
            <section class="glass-panel p-3 p-md-4 mb-4">
                <h2 class="h6 mb-2">{{ __('Distribution sur GrimbaNews') }}</h2>
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

        <p class="mt-5">
            <a href="{{ url('/sources') }}" class="btn-grimba btn-grimba--ghost">
                ← {{ __('Toutes les sources') }}
            </a>
        </p>
    </div>
</section>
