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
        'left'   => 'Gauche',
        'center' => 'Centre',
        'right'  => 'Droite',
        default  => 'Non classé',
    };
    $ownershipLabel = match ($source->ownership_type) {
        'independent'    => 'Indépendant',
        'corporate'      => 'Privé',
        'public'         => 'Public',
        'state-owned'    => 'État',
        'foundation'     => 'Fondation',
        'cooperative'    => 'Coopérative',
        default          => '—',
    };

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
                <span class="grimba-methodology__kicker">Source · {{ $source->country ?: '—' }}</span>
                <span style="
                        display:inline-block; padding:4px 10px;
                        border-radius:999px;
                        background: {{ $biasColor }}1a;
                        color: {{ $biasColor }};
                        font-size:12px; font-weight:700; letter-spacing:0.4px; text-transform:uppercase;
                    ">{{ $biasLabel }}</span>
                <span class="small opacity-75">
                    Crédibilité {{ $source->credibility_score ?? '—' }} · {{ $ownershipLabel }}
                    @if($source->owner_name)
                        · propriété de <strong>{{ $source->owner_name }}</strong>
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
                        ligne éditoriale orientée à gauche.
                    @elseif($source->bias_rating === 'right')
                        ligne éditoriale orientée à droite.
                    @elseif($source->bias_rating === 'center')
                        ligne éditoriale centriste / factuelle.
                    @else
                        positionnement éditorial non encore classé.
                    @endif
                    Couverture archivée par GrimbaNews.
                </p>
            @endif

            @if ($source->website)
                <a href="https://{{ ltrim($source->website, '/') }}" target="_blank" rel="noopener"
                   class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                    Visiter {{ $source->website }} ↗
                </a>
            @endif
        </header>

        @if($known > 0)
            <section class="glass-panel p-3 p-md-4 mb-4">
                <h2 class="h6 mb-2">Distribution sur GrimbaNews</h2>
                <p class="small opacity-75 mb-2">
                    {{ $stats['total'] }} {{ $stats['total'] === 1 ? 'article archivé' : 'articles archivés' }} · biais déclaré
                    <strong style="color:{{ $biasColor }};">{{ $biasLabel }}</strong>
                </p>
                <div style="display:flex;height:14px;border-radius:9999px;overflow:hidden;background:rgba(0,0,0,.08);">
                    <div style="width:{{ $pct['left'] }}%;background:#3b82f6;" title="Gauche {{ $pct['left'] }}%"></div>
                    <div style="width:{{ $pct['center'] }}%;background:#a8a8a8;" title="Centre {{ $pct['center'] }}%"></div>
                    <div style="width:{{ $pct['right'] }}%;background:#e84c3d;" title="Droite {{ $pct['right'] }}%"></div>
                </div>
                <div class="d-flex justify-content-between small mt-2">
                    <span style="color:#3b82f6;font-weight:600;">Gauche {{ $pct['left'] }}%</span>
                    <span style="color:#a8a8a8;font-weight:600;">Centre {{ $pct['center'] }}%</span>
                    <span style="color:#e84c3d;font-weight:600;">Droite {{ $pct['right'] }}%</span>
                </div>
                <p class="small opacity-60 mt-2 mb-0">
                    Calcul basé sur le biais individuel des articles archivés depuis cette source.
                    Quand cette barre s'écarte fortement du biais éditorial déclaré, elle révèle des
                    angles inattendus dans la couverture.
                </p>
            </section>
        @endif

        <h2 class="h5 mb-3">Articles récents de {{ $source->name }}</h2>

        @if($posts->isEmpty())
            <div class="glass-panel p-4 text-center">
                <p class="mb-0">Aucun article archivé pour cette source — encore.</p>
                <p class="mb-0 small opacity-75">
                    Les nouveaux articles apparaissent ici dès que le flux RSS est traité (toutes les 30 min).
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
                ← Toutes les sources
            </a>
        </p>
    </div>
</section>
