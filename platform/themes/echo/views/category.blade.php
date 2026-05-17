@php
    Theme::set('pageTitle', __($category->name));
    Theme::set('grimbaCategoryPage', true);
    Theme::layout('grimba-chrome');

    $rawFollow = (string) request()->cookie('grimba_follow', '');
    $followedIds = array_filter(array_map('intval', explode(',', $rawFollow)));
    $isFollowed = in_array($category->id, $followedIds, true);

    // S138 — category-level bias distribution. Computed from the
    // last 200 published posts in this category. Reveals how the
    // topic is being covered overall — when one side dominates, the
    // bar surfaces it before the reader even scrolls.
    use App\Support\GrimbaPostRecency;
    use App\Support\GrimbaRegionQuery;
    use App\Support\GrimbaTranslationPresenter as GnTr;
    use Botble\Blog\Models\Post;
    $catBias = Post::query()
        ->whereHas('categories', fn ($q) => $q->where('categories.id', $category->id))
        ->where('status', 'published')
        ->tap(fn ($q) => GnTr::orderForTargetLocale($q))
        ->limit(200)
        ->get(['bias_rating'])
        ->reduce(function (array $a, $p) {
            $r = $p->bias_rating ?? 'unknown';
            if (! isset($a[$r])) $a[$r] = 0;
            $a[$r]++;
            return $a;
        }, ['left' => 0, 'center' => 0, 'right' => 0, 'unknown' => 0]);
    $catKnown = $catBias['left'] + $catBias['center'] + $catBias['right'];
    $catTotal = $catKnown + $catBias['unknown'];
    $catPct = [
        'left'   => $catKnown ? round($catBias['left']   * 100 / $catKnown) : 0,
        'center' => $catKnown ? round($catBias['center'] * 100 / $catKnown) : 0,
        'right'  => $catKnown ? round($catBias['right']  * 100 / $catKnown) : 0,
    ];
    $catBiasLabels = [
        'left' => __('Gauche'),
        'center' => __('Centre'),
        'right' => __('Droite'),
    ];
    $catDominantKey = collect($catPct)->sortDesc()->keys()->first();
    $catDominantLabel = $catKnown ? ($catBiasLabels[$catDominantKey] ?? __('Non classé')) : __('Non classé');
    $catFresh24 = Post::query()
        ->whereHas('categories', fn ($q) => $q->where('categories.id', $category->id))
        ->where('status', 'published')
        ->whereRaw(GrimbaPostRecency::expression() . ' >= ?', [now()->subDay()->toDateTimeString()])
        ->count();
@endphp

<section class="grimba-category-hero container">
    <header class="glass-panel grimba-category-hero__panel p-4 p-md-5 mb-4">
        <div class="grimba-category-hero__top">
            <div class="grimba-category-hero__intro">
                <span class="grimba-methodology__kicker">Sujet</span>
                <h1 class="grimba-methodology__title mt-2 mb-2">{{ __($category->name) }}</h1>
                @if($category->description)
                    <p class="grimba-category-hero__copy mb-0">
                        {!! \Illuminate\Support\Str::limit(strip_tags($category->description), 260) !!}
                    </p>
                @else
                    <p class="grimba-category-hero__copy mb-0">
                        {{ __('Toutes les histoires classées dans :topic, côté à côté avec leurs biais éditoriaux, sources et pays d’origine.', ['topic' => __($category->name)]) }}
                    </p>
                @endif
            </div>

            <button type="button"
                    class="btn-grimba grimba-category-hero__follow {{ $isFollowed ? 'btn-grimba--solid' : 'btn-grimba--ghost' }}"
                    data-grimba-follow="{{ $category->id }}"
                    data-grimba-category-hero>
                <span class="grimba-category-hero__glyph">{{ $isFollowed ? '✓' : '+' }}</span>
                <span>{{ $isFollowed ? 'Suivi' : 'Suivre ce sujet' }}</span>
            </button>
        </div>

        @if($catKnown > 0)
            <div class="grimba-category-signal mt-4">
                <div class="grimba-category-signal__summary">
                    <div>
                        <span>{{ __('Signal éditorial') }}</span>
                        <strong>{{ __('Couverture sur :topic', ['topic' => __($category->name)]) }}</strong>
                    </div>
                    <em>
                        {{ trans_choice(':count article archivé|:count articles archivés', $catTotal, ['count' => $catTotal]) }}
                        @if($catBias['unknown'] > 0)
                            · {{ trans_choice(':count non classé|:count non classés', $catBias['unknown'], ['count' => $catBias['unknown']]) }}
                        @endif
                    </em>
                </div>
                <div class="grimba-category-signal__bar" aria-label="{{ __('Distribution des biais') }}">
                    <span style="--w: {{ $catPct['left'] }}%; --dot: #3b82f6;" title="{{ __('Gauche') }} {{ $catPct['left'] }}%"></span>
                    <span style="--w: {{ $catPct['center'] }}%; --dot: #a8a8a8;" title="{{ __('Centre') }} {{ $catPct['center'] }}%"></span>
                    <span style="--w: {{ $catPct['right'] }}%; --dot: #e84c3d;" title="{{ __('Droite') }} {{ $catPct['right'] }}%"></span>
                </div>
                <div class="grimba-category-signal__legend">
                    <span style="--dot:#3b82f6;">{{ __('Gauche') }} {{ $catPct['left'] }}%</span>
                    <span style="--dot:#a8a8a8;">{{ __('Centre') }} {{ $catPct['center'] }}%</span>
                    <span style="--dot:#e84c3d;">{{ __('Droite') }} {{ $catPct['right'] }}%</span>
                </div>
                <div class="grimba-category-signal__stats">
                    <article>
                        <span>{{ __('Dominant') }}</span>
                        <strong>{{ $catDominantLabel }}</strong>
                    </article>
                    <article>
                        <span>{{ __('Fraîcheur 24h') }}</span>
                        <strong>{{ $catFresh24 }}</strong>
                    </article>
                    <article>
                        <span>{{ __('Base analysée') }}</span>
                        <strong>{{ $catKnown }}</strong>
                    </article>
                </div>
                <p class="grimba-category-signal__note mb-0">
                    {{ __("Distribution réelle des biais sur les 200 derniers articles du sujet. Si un côté domine, l’écart devient visible avant d’ouvrir un article.") }}
                </p>
            </div>
        @endif

    </header>

    {{-- S316 — Top sources for this topic. Surfaces which outlets cover
          this topic most so the reader sees who's setting the agenda
          before reading any individual story. Pulls top 8 sources by
          article count in this category, last 90 days. --}}
    @php
        $__topSourcesQuery = \Illuminate\Support\Facades\DB::table('news_sources as s')
            ->join('posts as p', 'p.source_id', '=', 's.id')
            ->join('post_categories as pc', 'pc.post_id', '=', 'p.id')
            ->where('pc.category_id', $category->id)
            ->where('p.status', 'published')
            ->whereRaw(GrimbaPostRecency::expression('p') . ' >= ?', [now()->subDays(90)->toDateTimeString()]);

        GrimbaRegionQuery::applyToSourceCountry($__topSourcesQuery, 's.country');

        $__topSources = $__topSourcesQuery
            ->groupBy('s.id', 's.name', 's.slug', 's.bias_rating', 's.bias_score', 's.credibility_score', 's.ownership_type', 's.owner_name', 's.country')
            ->select('s.id', 's.name', 's.slug', 's.bias_rating', 's.bias_score', 's.credibility_score', 's.ownership_type', 's.owner_name', 's.country',
                \Illuminate\Support\Facades\DB::raw('COUNT(p.id) as article_count'))
            ->orderByDesc('article_count')
            ->limit(8)
            ->get();
    @endphp

    @if($__topSources->isNotEmpty())
        <section class="grimba-topic-top-sources container mt-4 mb-2">
            <div class="d-flex align-items-center gap-2 mb-3">
                <h2 class="h6 grimba-section-eyebrow grimba-topic-top-sources__title mb-0">
                    {{ __('Sources qui couvrent le plus :topic ces 90 jours', ['topic' => __($category->name)]) }}
                </h2>
                @include(Theme::getThemeNamespace('partials.info-pill'), [
                    'size' => 'sm',
                    'tone' => 'soft',
                    'body' => __("Les sources qui ont le plus contribué à cette rubrique sur 90 jours. Cumul d'articles publiés — pas un score d'audience ou de popularité."),
                ])
            </div>
            <div class="row g-2">
                @foreach($__topSources as $ts)
                    @php
                        $__tsBias = \App\Ground\Bias::tier($ts->bias_rating ?? null, $ts->bias_score ?? null);
                        $__tsFact = \App\Ground\Factuality::tier($ts->credibility_score ?? null);
                        $__tsOriginKey = \App\Support\GrimbaSourceBreakdown::originKeyForCountry($ts->country ?? null);
                        $__tsCountry = \App\Support\GrimbaSourceBreakdown::countryLabel($ts->country ?? null);
                        $__tsOrigin = \App\Support\GrimbaSourceBreakdown::originLabel($__tsOriginKey);
                        $__tsOriginColor = \App\Support\GrimbaSourceBreakdown::originColor($__tsOriginKey);
                    @endphp
                    <div class="col-12 col-md-6 col-lg-3">
                        <a href="{{ url('/sources/' . $ts->slug) }}"
                           class="grimba-topic-source-card"
                           style="--topic-origin-color: {{ $__tsOriginColor }};">
                            <span class="grimba-topic-source-card__rank">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <strong>{{ $ts->name }}</strong>
                            <div class="grimba-topic-source-card__meta">
                                <span>{{ trans_choice(':count article|:count articles', (int) $ts->article_count, ['count' => (int) $ts->article_count]) }}</span>
                                <span>{{ $__tsCountry }}</span>
                            </div>
                            <div class="grimba-topic-source-card__chips">
                                <span class="grimba-topic-source-card__origin">{{ $__tsOrigin }}</span>
                                {!! Theme::partial('bias-chip', ['tier' => $__tsBias, 'size' => 'sm']) !!}
                                @if($__tsFact !== 'unknown')
                                    {!! Theme::partial('factuality-chip', ['tier' => $__tsFact, 'size' => 'sm', 'showLabel' => false]) !!}
                                @endif
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</section>

@include(Theme::getThemeNamespace('views.loop'))

<script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const btn = document.querySelector('[data-grimba-category-hero]');
        if (!btn) return;

        btn.addEventListener('click', async () => {
            const id = btn.dataset.grimbaFollow;
            const res = await fetch(@json(route('public.topics.follow')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ category_id: id, action: 'toggle' })
            }).then(r => r.json()).catch(() => null);

            if (!res || !res.ok) return;

            const nowFollowed = res.followed.includes(parseInt(id));
            btn.classList.toggle('btn-grimba--solid', nowFollowed);
            btn.classList.toggle('btn-grimba--ghost', ! nowFollowed);
            btn.querySelector('.grimba-category-hero__glyph').textContent = nowFollowed ? '✓' : '+';
            btn.querySelector('span:last-child').textContent = nowFollowed ? 'Suivi' : 'Suivre ce sujet';

            // Sync the matching chip + counter.
            const chip = document.querySelector(`.grimba-chip[data-category-id="${id}"]`);
            if (chip) {
                chip.classList.toggle('grimba-chip--followed', nowFollowed);
                const chipBtn = chip.querySelector('[data-grimba-follow]');
                if (chipBtn) chipBtn.textContent = nowFollowed ? '✓' : '+';
            }
            const counter = document.getElementById('grimba-follow-count');
            if (counter) counter.textContent = String(res.count);
        });
    })();
</script>
