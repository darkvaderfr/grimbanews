@extends(BaseHelper::getAdminMasterLayoutTemplate())

@php
    $biasColor = ['left' => '#3b82f6', 'center' => '#a8a8a8', 'right' => '#e84c3d', 'unknown' => '#9ca3af'];
    $pct = [
        'left'   => $coverageTotal ? round($coverage['left']   * 100 / $coverageTotal) : 0,
        'center' => $coverageTotal ? round($coverage['center'] * 100 / $coverageTotal) : 0,
        'right'  => $coverageTotal ? round($coverage['right']  * 100 / $coverageTotal) : 0,
    ];

    $sparkMax = max(1, max(array_column($sparkline, 'n')));
@endphp

@section('content')
<div class="grimba-cockpit">

    {{-- Meta strip --}}
    <div class="grimba-cockpit__meta">
        <span class="grimba-cockpit__kicker">Aujourd'hui</span>
        <span>·</span>
        <strong>{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</strong>
        <span>·</span>
        <span>{{ $publishedToday }} {{ $publishedToday === 1 ? 'article publié' : 'articles publiés' }}</span>
        <span>·</span>
        <span>{{ $draftCount }} en brouillon</span>
    </div>

    <div class="row g-3 mb-3">
        {{-- Coverage balance --}}
        <div class="col-lg-8 col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Équilibre de couverture — aujourd'hui</h3>
                </div>
                <div class="card-body">
                    @if($coverageTotal === 0)
                        <p class="text-muted mb-0">Aucun article publié aujourd'hui.</p>
                    @else
                        <div class="grimba-coverage__bar mb-3" style="display:flex;height:12px;border-radius:9999px;overflow:hidden;background:rgba(26,23,19,0.06);">
                            <div style="width:{{ $pct['left'] }}%;background:{{ $biasColor['left'] }};"></div>
                            <div style="width:{{ $pct['center'] }}%;background:{{ $biasColor['center'] }};"></div>
                            <div style="width:{{ $pct['right'] }}%;background:{{ $biasColor['right'] }};"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span style="color:{{ $biasColor['left'] }};font-weight:600;">● Gauche {{ $coverage['left'] }} ({{ $pct['left'] }}%)</span>
                            <span style="color:{{ $biasColor['center'] }};font-weight:600;">● Centre {{ $coverage['center'] }} ({{ $pct['center'] }}%)</span>
                            <span style="color:{{ $biasColor['right'] }};font-weight:600;">● Droite {{ $coverage['right'] }} ({{ $pct['right'] }}%)</span>
                            @if($coverage['unknown'] > 0)
                                <span class="text-muted">Non classés {{ $coverage['unknown'] }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Angles morts counter --}}
        <div class="col-lg-4 col-12">
            <div class="card" style="border:1px solid rgba(138,43,226,0.3);">
                <div class="card-body text-center">
                    <div style="font-family: var(--gn-font-display); font-size: 2.8rem; font-weight: 700; color: #8a2be2; line-height: 1;">
                        {{ $blindspotCount }}
                    </div>
                    <div class="text-uppercase small text-muted mt-1" style="letter-spacing:0.08em; font-weight:600;">Angles morts</div>
                    <a href="{{ route('grimba.story-clusters.index') }}" class="btn btn-sm btn-outline-primary mt-3">Gérer les dossiers</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        {{-- Active dossiers --}}
        <div class="col-lg-6 col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Dossiers actifs</h3>
                </div>
                <div class="card-body">
                    @if($activeClusters->isEmpty())
                        <p class="text-muted mb-0">Aucun dossier avec articles publiés.</p>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach($activeClusters as $c)
                                @php
                                    $total = max(1, array_sum($c->spread));
                                    $cp = [
                                        'left'   => round($c->spread['left']   * 100 / $total),
                                        'center' => round($c->spread['center'] * 100 / $total),
                                        'right'  => round($c->spread['right']  * 100 / $total),
                                    ];
                                @endphp
                                <li class="mb-3 pb-3" @if(!$loop->last) style="border-bottom:1px solid var(--gn-rule);" @endif>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <a href="{{ route('grimba.story-clusters.edit', $c->id) }}" class="text-decoration-none" style="color:var(--gn-ink);font-family:var(--gn-font-display);font-size:1.05rem;font-weight:600;">
                                            {{ $c->topic }}
                                        </a>
                                        <span class="text-muted small">{{ $c->post_count }} articles</span>
                                    </div>
                                    <div style="display:flex;height:6px;border-radius:9999px;overflow:hidden;background:rgba(26,23,19,0.06);">
                                        <div style="width:{{ $cp['left'] }}%;background:{{ $biasColor['left'] }};"></div>
                                        <div style="width:{{ $cp['center'] }}%;background:{{ $biasColor['center'] }};"></div>
                                        <div style="width:{{ $cp['right'] }}%;background:{{ $biasColor['right'] }};"></div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top sources --}}
        <div class="col-lg-6 col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sources les plus citées (7 j)</h3>
                </div>
                <div class="card-body">
                    @if($topSources->isEmpty())
                        <p class="text-muted mb-0">Aucune source utilisée cette semaine.</p>
                    @else
                        @php $srcMax = max(1, $topSources->max('n')); @endphp
                        <ul class="list-unstyled mb-0">
                            @foreach($topSources as $s)
                                @php
                                    $score = (int) ($s->score ?? 0);
                                    $barColor = $score >= 85 ? '#22c55e' : ($score >= 70 ? '#eab308' : '#ef4444');
                                    $w = round($s->n * 100 / $srcMax);
                                @endphp
                                <li class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center small mb-1">
                                        <strong>{{ $s->source_name }}</strong>
                                        <span class="text-muted">{{ $s->n }} articles · crédibilité {{ $score }}</span>
                                    </div>
                                    <div style="height:8px;border-radius:9999px;background:rgba(26,23,19,0.06);overflow:hidden;">
                                        <div style="width:{{ $w }}%;height:100%;background:{{ $barColor }};"></div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Newsletter signups 7-day sparkline --}}
        <div class="col-lg-8 col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Inscriptions newsletter — 7 j</h3>
                    <span class="text-muted">{{ $signupsTotal }} total</span>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-end gap-2" style="height:90px;">
                        @foreach($sparkline as $s)
                            @php $h = max(4, round($s['n'] * 100 / $sparkMax)); @endphp
                            <div class="flex-grow-1 text-center" style="position:relative;">
                                <div style="background:var(--gn-ink);height:{{ $h }}%;border-radius:6px 6px 0 0;min-height:4px;"></div>
                                <div class="small text-muted mt-1" style="font-size:0.7rem;">{{ \Carbon\Carbon::parse($s['date'])->locale('fr')->isoFormat('ddd')[0] }}</div>
                                @if($s['n'] > 0)
                                    <div style="position:absolute;top:-18px;left:0;right:0;font-size:0.75rem;font-weight:700;">{{ $s['n'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick nav --}}
        <div class="col-lg-4 col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions rapides</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('posts.create') }}" class="btn btn-primary">+ Nouvel article</a>
                        <a href="{{ route('grimba.news-sources.create') }}" class="btn btn-outline-primary">+ Nouvelle source</a>
                        <a href="{{ route('grimba.story-clusters.create') }}" class="btn btn-outline-primary">+ Nouveau dossier</a>
                        <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-secondary">Voir le site public →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .grimba-cockpit__meta {
        font-family: var(--gn-font-mono);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--gn-ink-soft);
        margin-bottom: 1.25rem;
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .grimba-cockpit__kicker {
        background: var(--gn-ink);
        color: var(--gn-paper);
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        letter-spacing: 0.08em;
    }
</style>
@endsection
