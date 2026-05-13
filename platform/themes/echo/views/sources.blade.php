@php
    Theme::layout('grimba-chrome');
    /**
     * @var array<string,\Illuminate\Support\Collection> $grouped  Sources grouped by bias_rating
     * @var int $total
     */

    $biasMeta = [
        'left'    => ['label' => __('Gauche'),      'color' => '#3b82f6'],
        'center'  => ['label' => __('Centre'),      'color' => '#a8a8a8'],
        'right'   => ['label' => __('Droite'),      'color' => '#ef4444'],
        'unknown' => ['label' => __('Non évalué'),  'color' => '#9ca3af'],
    ];

    $ownershipLabel = [
        'state'       => __('État'),
        'corporate'   => __('Privé'),
        'independent' => __('Indépendant'),
        'nonprofit'   => __('Associatif'),
    ];

    // Build the list of unique countries represented for the country filter.
    $allCountries = collect($grouped)
        ->flatten(1)
        ->pluck('country')
        ->filter()
        ->unique()
        ->sort()
        ->values();
@endphp

<section class="grimba-sources py-5">
    <div class="container">

        <header class="glass-panel p-4 mb-4">
            <span class="grimba-methodology__kicker">{{ __('Sources classées') }}</span>
            <h1 class="grimba-methodology__title mt-2 mb-2">
                {{ trans_choice(':count média sous notre grille d’analyse|:count médias sous notre grille d’analyse', $total, ['count' => $total]) }}
            </h1>
            <p class="grimba-sources__lede mb-3">
                {{ __('Biais éditorial, type de propriété, score de crédibilité, pays d’origine et langue. Classements ouverts et révisables — voir la') }}
                <a href="{{ url('/methodologie') }}" class="text-decoration-underline">{{ __('méthodologie') }}</a>.
            </p>
            <a href="{{ url('/proprietaires') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                {{ __('Carte des propriétaires') }} →
            </a>
        </header>
        @include(Theme::getThemeNamespace('partials.home.ad-slot'), [
            'location' => 'grimba_sources_top',
            'class' => 'grimba-ad-slot--leaderboard mb-4',
        ])

        <div class="grimba-sources__controls glass-panel p-3 mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-lg-4 col-md-5 col-12">
                    <label class="small text-uppercase opacity-75 fw-semibold mb-1 d-block">{{ __('Rechercher') }}</label>
                    <input
                        type="search"
                        id="grimba-sources-search"
                        class="form-control"
                        placeholder="{{ __('Nom du média, site, pays…') }}"
                        aria-label="{{ __('Rechercher une source') }}"
                    >
                </div>
                <div class="col-lg-5 col-md-7 col-12">
                    <label class="small text-uppercase opacity-75 fw-semibold mb-1 d-block">{{ __('Biais') }}</label>
                    <div class="grimba-sources__bias-filter d-flex gap-2 flex-wrap" role="tablist">
                        <button type="button" class="btn-grimba btn-grimba--sm btn-grimba--solid" data-bias="all">{{ __('Tous') }}</button>
                        <button type="button" class="btn-grimba btn-grimba--sm btn-grimba--ghost" data-bias="left"
                                style="color:{{ $biasMeta['left']['color'] }};border-color:{{ $biasMeta['left']['color'] }}44;">● {{ __('Gauche') }}</button>
                        <button type="button" class="btn-grimba btn-grimba--sm btn-grimba--ghost" data-bias="center"
                                style="color:{{ $biasMeta['center']['color'] }};border-color:{{ $biasMeta['center']['color'] }}44;">● {{ __('Centre') }}</button>
                        <button type="button" class="btn-grimba btn-grimba--sm btn-grimba--ghost" data-bias="right"
                                style="color:{{ $biasMeta['right']['color'] }};border-color:{{ $biasMeta['right']['color'] }}44;">● {{ __('Droite') }}</button>
                    </div>
                </div>
                <div class="col-lg-3 col-12">
                    <label class="small text-uppercase opacity-75 fw-semibold mb-1 d-block">{{ __('Pays') }}</label>
                    <select id="grimba-sources-country" class="form-select">
                        <option value="all">{{ __('Tous les pays') }}</option>
                        @foreach($allCountries as $cc)
                            <option value="{{ $cc }}">{{ $cc }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 small opacity-75">
                <span id="grimba-sources-count">{{ trans_choice(':count source affichée|:count sources affichées', $total, ['count' => $total]) }}</span>
                <button type="button" id="grimba-sources-reset" class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                    {{ __('Réinitialiser') }}
                </button>
            </div>

            {{-- S311 — factuality tier filter pills. --}}
            <div class="row g-3 align-items-center mt-2">
                <div class="col-12">
                    <label class="small text-uppercase opacity-75 fw-semibold mb-1 d-block">{{ __('Fiabilité') }}</label>
                    <div class="grimba-sources__fact-filter d-flex gap-2 flex-wrap" role="tablist">
                        <button type="button" class="btn-grimba btn-grimba--sm btn-grimba--solid" data-fact="all">{{ __('Toutes') }}</button>
                        @foreach(['very_high' => 'Très haute', 'high' => 'Haute', 'mixed' => 'Mixte', 'low' => 'Basse', 'very_low' => 'Très basse'] as $tier => $label)
                            @php $col = \App\Ground\Factuality::color($tier); @endphp
                            <button type="button"
                                    class="btn-grimba btn-grimba--sm btn-grimba--ghost"
                                    data-fact="{{ $tier }}"
                                    style="color:{{ $col }};border-color:{{ $col }}55;">
                                {{ __($label) }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div id="grimba-sources-list">
            @foreach(['left','center','right','unknown'] as $biasKey)
                @php $bucket = $grouped[$biasKey] ?? collect(); @endphp
                @continue($bucket->isEmpty())

                <section class="grimba-sources__group mb-5" data-bias-group="{{ $biasKey }}">
                    <h2 class="h4 d-flex align-items-center gap-2 mb-3">
                        <span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:{{ $biasMeta[$biasKey]['color'] }};"></span>
                        {{ $biasMeta[$biasKey]['label'] }}
                        <span class="small opacity-75" data-bucket-count>({{ $bucket->count() }})</span>
                    </h2>

                    <div class="row g-3">
                        @foreach($bucket as $src)
                            <div class="col-lg-4 col-md-6 col-12 grimba-sources__item"
                                 data-name="{{ strtolower($src->name) }}"
                                 data-website="{{ strtolower($src->website ?? '') }}"
                                 data-country="{{ $src->country ?? '' }}"
                                 data-bias="{{ $biasKey }}"
                                 data-fact="{{ \App\Ground\Factuality::tier($src->credibility_score ?? null) }}">
                                <article class="glass-card p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center gap-3 min-w-0">
                                            {!! Theme::partial('source-logo', [
                                                'source_id' => $src->id,
                                                'name' => $src->name,
                                                'website' => $src->website,
                                                'logo_url' => $src->logo_url ?? null,
                                                'logo_status' => $src->logo_status ?? 'unknown',
                                                'logo_checked_at' => $src->logo_checked_at ?? null,
                                                'size' => 44,
                                                'color' => $biasMeta[$biasKey]['color'],
                                            ]) !!}
                                            <h3 class="h6 mb-0">
                                                @if($src->slug ?? null)
                                                    <a href="{{ url('/sources/' . $src->slug) }}" class="text-decoration-none" style="color:var(--gn-ink,#1a1713);">
                                                        {{ $src->name }} →
                                                    </a>
                                                @elseif($src->website)
                                                    <a href="https://{{ $src->website }}" target="_blank" rel="noopener" class="text-decoration-none">
                                                        {{ $src->name }}
                                                    </a>
                                                @else
                                                    {{ $src->name }}
                                                @endif
                                            </h3>
                                        </div>
                                        <span class="d-inline-flex flex-column align-items-end gap-1">
                                            @php
                                                $__rowBias = \App\Ground\Bias::tier($src->bias_rating ?? null, $src->bias_score ?? null);
                                                $__rowFact = \App\Ground\Factuality::tier($src->credibility_score ?? null);
                                                $__rowOwn  = \App\Ground\Ownership::category($src->ownership_type ?? null, $src->owner_name ?? null);
                                            @endphp
                                            {!! Theme::partial('bias-chip', ['tier' => $__rowBias, 'size' => 'sm']) !!}
                                            @if($__rowFact !== 'unknown')
                                                {!! Theme::partial('factuality-chip', ['tier' => $__rowFact, 'size' => 'sm']) !!}
                                            @endif
                                        </span>
                                    </div>

                                    <div class="small opacity-85 d-flex flex-wrap gap-2 align-items-center">
                                        @if($__rowOwn !== 'other')
                                            {!! Theme::partial('ownership-chip', [
                                                'category' => $__rowOwn,
                                                'owner' => $src->owner_name ?? null,
                                                'size' => 'sm',
                                                'showLabel' => true,
                                            ]) !!}
                                        @endif
                                        @if($src->country)
                                            <span class="opacity-70">{{ $src->country }}</span>
                                        @endif
                                        @if($src->language)
                                            <span class="opacity-70 text-uppercase">{{ $src->language }}</span>
                                        @endif
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mt-3 small">
                                        <span style="padding:4px 9px;border-radius:9999px;background:rgba(26,23,19,0.05);">
                                            {{ trans_choice(':count dossier / 30 j|:count dossiers / 30 j', (int) ($src->recent_cluster_count ?? 0), ['count' => (int) ($src->recent_cluster_count ?? 0)]) }}
                                        </span>
                                        <span style="padding:4px 9px;border-radius:9999px;background:rgba(26,23,19,0.05);">
                                            {{ trans_choice(':count article|:count articles', (int) ($src->article_count ?? 0), ['count' => (int) ($src->article_count ?? 0)]) }}
                                        </span>
                                    </div>

                                    @if($src->credibility_score)
                                        @php
                                            $score = (int) $src->credibility_score;
                                            $barColor = $score >= 85 ? '#22c55e' : ($score >= 70 ? '#eab308' : '#ef4444');
                                        @endphp
                                        <div class="mt-3">
                                            <div class="d-flex justify-content-between small mb-1">
                                                <span class="opacity-75">{{ __('Crédibilité') }}</span>
                                                <strong>{{ $score }}/100</strong>
                                            </div>
                                            <div style="height:6px;border-radius:9999px;background:rgba(0,0,0,0.08);overflow:hidden;">
                                                <div style="width:{{ $score }}%;height:100%;background:{{ $barColor }};"></div>
                                            </div>
                                        </div>
                                    @endif
                                </article>
                            </div>
                        @endforeach
                    </div>
                </section>
                @if($loop->first)
                    @include(Theme::getThemeNamespace('partials.home.ad-slot'), [
                        'location' => 'grimba_sources_mid',
                        'class' => 'grimba-ad-slot--native my-4',
                    ])
                @endif
            @endforeach
        </div>

        <div id="grimba-sources-empty" class="glass-panel p-4 text-center d-none">
            <p class="mb-0">{{ __('Aucune source ne correspond à ces filtres.') }}</p>
        </div>
    </div>
</section>

<script>
    (function () {
        const search  = document.getElementById('grimba-sources-search');
        const country = document.getElementById('grimba-sources-country');
        const reset   = document.getElementById('grimba-sources-reset');
        const count   = document.getElementById('grimba-sources-count');
        const empty   = document.getElementById('grimba-sources-empty');
        const list    = document.getElementById('grimba-sources-list');
        const biasBtns = document.querySelectorAll('.grimba-sources__bias-filter [data-bias]');
        const factBtns = document.querySelectorAll('.grimba-sources__fact-filter [data-fact]');
        let   activeBias = 'all';
        let   activeFact = 'all';

        function apply() {
            const q = (search.value || '').trim().toLowerCase();
            const c = country.value;

            let shown = 0;
            document.querySelectorAll('.grimba-sources__item').forEach(item => {
                const matchBias    = activeBias === 'all' || item.dataset.bias === activeBias;
                const matchFact    = activeFact === 'all' || item.dataset.fact === activeFact;
                const matchCountry = c === 'all' || item.dataset.country === c;
                const matchText    = !q
                    || item.dataset.name.includes(q)
                    || item.dataset.website.includes(q)
                    || item.dataset.country.toLowerCase().includes(q);

                const visible = matchBias && matchFact && matchCountry && matchText;
                item.classList.toggle('d-none', !visible);
                if (visible) shown++;
            });

            // Group headers fade out when empty so the page stays clean.
            document.querySelectorAll('.grimba-sources__group').forEach(g => {
                const remaining = g.querySelectorAll('.grimba-sources__item:not(.d-none)').length;
                g.classList.toggle('d-none', remaining === 0);
                const countEl = g.querySelector('[data-bucket-count]');
                if (countEl) countEl.textContent = '(' + remaining + ')';
            });

            count.textContent = shown === 1
                ? @json(__(':count source affichée', ['count' => 1]))
                : @json(__(':count sources affichées', ['count' => '__COUNT__'])).replace('__COUNT__', shown);
            empty.classList.toggle('d-none', shown !== 0);
            list.classList.toggle('d-none', shown === 0);
        }

        biasBtns.forEach(btn => btn.addEventListener('click', () => {
            activeBias = btn.dataset.bias;
            biasBtns.forEach(b => {
                b.classList.toggle('btn-grimba--solid', b === btn);
                b.classList.toggle('btn-grimba--ghost', b !== btn);
            });
            apply();
        }));

        factBtns.forEach(btn => btn.addEventListener('click', () => {
            activeFact = btn.dataset.fact;
            factBtns.forEach(b => {
                b.classList.toggle('btn-grimba--solid', b === btn);
                b.classList.toggle('btn-grimba--ghost', b !== btn);
            });
            apply();
        }));

        search.addEventListener('input', apply);
        country.addEventListener('change', apply);
        // Reset also clears the factuality pill state
        const resetFact = () => {
            activeFact = 'all';
            factBtns.forEach((b) => {
                b.classList.toggle('btn-grimba--solid', b.dataset.fact === 'all');
                b.classList.toggle('btn-grimba--ghost', b.dataset.fact !== 'all');
            });
        };

        reset.addEventListener('click', () => {
            search.value = '';
            country.value = 'all';
            activeBias = 'all';
            biasBtns.forEach(b => {
                const isAll = b.dataset.bias === 'all';
                b.classList.toggle('btn-grimba--solid', isAll);
                b.classList.toggle('btn-grimba--ghost', !isAll);
            });
            resetFact();
            apply();
        });
    })();
</script>
