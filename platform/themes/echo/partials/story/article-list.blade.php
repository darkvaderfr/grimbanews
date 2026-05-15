@php
    /**
     * S148 — Cluster article list. Renders all posts in the same
     * story_cluster, grouped by bias, with filter tabs.
     *
     * @var \Illuminate\Database\Eloquent\Collection $clusterPosts
     * @var \Botble\Blog\Models\Post                 $currentPost  the post the reader landed on
     */

    use App\Support\GrimbaTranslationPresenter as GnTr;
    use App\Support\GrimbaStoryInsights;
    use Botble\Blog\Models\Post as BlogPost;
    use Illuminate\Support\Str;

    GnTr::warm($clusterPosts);

    $byBias = ['left' => [], 'center' => [], 'right' => [], 'unknown' => []];
    foreach ($clusterPosts as $cp) {
        $b = $cp->bias_rating ?? 'unknown';
        if (! isset($byBias[$b])) $b = 'unknown';
        $byBias[$b][] = $cp;
    }
    $totalCount   = $clusterPosts->count();
    $countLabels  = [
        'left'    => count($byBias['left']),
        'center'  => count($byBias['center']),
        'right'   => count($byBias['right']),
        'unknown' => count($byBias['unknown']),
    ];

    $biasMeta = [
        'left'    => ['label' => __('Gauche'),     'color' => '#3b82f6', 'short' => 'L'],
        'center'  => ['label' => __('Centre'),     'color' => '#a8a8a8', 'short' => 'C'],
        'right'   => ['label' => __('Droite'),     'color' => '#e84c3d', 'short' => 'D'],
        'unknown' => ['label' => __('Non classé'), 'color' => '#6b6459', 'short' => '·'],
    ];

    $sortMode = request()->cookie('grimba_cluster_sort', 'bias') === 'recent' ? 'recent' : 'bias';
    $jumpList = GrimbaStoryInsights::buildJumpList($clusterPosts, (int) $currentPost->id);

    $clusterList = $sortMode === 'recent'
        ? $clusterPosts->sortByDesc('created_at')->values()
        : collect(['left', 'center', 'right', 'unknown'])
            ->flatMap(static fn (string $bucket) => $byBias[$bucket])
            ->values();
@endphp

<section class="grimba-story-articles">
    <div class="grimba-story-articles__head">
        <h2 class="grimba-story-articles__title">
            <span style="opacity:0.55;">{{ $totalCount }}</span>
            {{ trans_choice('article|articles', $totalCount) }}
        </h2>
        <div class="grimba-story-articles__tabs" role="tablist" aria-label="{{ __('Filtrer les articles du dossier') }}" data-grimba-cluster-tabs>
            <button type="button" data-bias-tab="all" role="tab" aria-controls="grimba-cluster-panel" aria-selected="true"
                    class="grimba-story-articles__tab">
                {{ __('Tous') }} · {{ $totalCount }}
            </button>
            @foreach(['left', 'center', 'right'] as $b)
                @if($countLabels[$b] > 0)
                    <button type="button" data-bias-tab="{{ $b }}" role="tab" aria-controls="grimba-cluster-panel" aria-selected="false"
                            class="grimba-story-articles__tab">
                        <span class="grimba-story-articles__tab-dot" style="--tab-dot: {{ $biasMeta[$b]['color'] }};"></span>
                        {{ $biasMeta[$b]['label'] }} · {{ $countLabels[$b] }}
                    </button>
                @endif
            @endforeach
        </div>
    </div>

    <div class="grimba-story-articles__tools">
        @if(count($jumpList) >= 2)
            <div class="grimba-story-jump">
                <span class="grimba-story-jump__label">{{ __('Lu chez') }}</span>
                @foreach($jumpList as $jump)
                    @php $color = $biasMeta[$jump['bias']]['color'] ?? 'rgba(26,23,19,0.45)'; @endphp
                    <a href="#story-article-{{ $jump['id'] }}"
                       class="grimba-story-jump__chip"
                       style="--story-jump-color: {{ $color }};">
                        <span class="grimba-story-jump__dot" aria-hidden="true"></span>
                        {{ $jump['label'] }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="grimba-story-sort" data-grimba-cluster-sort>
            <span class="grimba-story-sort__label">{{ __('Trier') }}</span>
            <button type="button"
                    class="btn-grimba btn-grimba--sm {{ $sortMode === 'bias' ? 'btn-grimba--solid' : 'btn-grimba--ghost' }}"
                    data-sort-mode="bias"
                    aria-pressed="{{ $sortMode === 'bias' ? 'true' : 'false' }}">
                {{ __('Par camp') }}
            </button>
            <button type="button"
                    class="btn-grimba btn-grimba--sm {{ $sortMode === 'recent' ? 'btn-grimba--solid' : 'btn-grimba--ghost' }}"
                    data-sort-mode="recent"
                    aria-pressed="{{ $sortMode === 'recent' ? 'true' : 'false' }}">
                {{ __('Plus récent') }}
            </button>
        </div>
    </div>

    {{-- S171 — pre-load source meta for every cluster post in one
         query so each card render is a hash lookup, not a roundtrip. --}}
    @php
        $__sources = $sourceMeta ?? null;
        if (! $__sources) {
            $__sourceIds = $clusterPosts->pluck('source_id')->filter()->unique()->all();
            $__sources = \App\Support\GrimbaSourceMeta::forIds($__sourceIds);
        }

        $__postIds = $clusterPosts->pluck('id')->filter()->map(fn ($id) => (int) $id)->unique()->values();
        $__articleUrls = $__postIds->isEmpty()
            ? collect()
            : \Illuminate\Support\Facades\DB::table('slugs')
                ->whereIn('reference_id', $__postIds->all())
                ->where('reference_type', BlogPost::class)
                ->whereIn('prefix', ['article', 'blog'])
                ->orderByRaw("CASE prefix WHEN 'article' THEN 0 ELSE 1 END")
                ->get(['reference_id', 'key'])
                ->unique('reference_id')
                ->mapWithKeys(fn ($slug) => [(int) $slug->reference_id => url('/article/' . $slug->key)]);
    @endphp

    <ul class="list-unstyled m-0" data-grimba-cluster-list id="grimba-cluster-panel" role="tabpanel">
        @foreach($clusterList as $cp)
                @php
                    $bucket = isset($biasMeta[$cp->bias_rating ?? '']) ? ($cp->bias_rating ?? 'unknown') : 'unknown';
                    $isCurrent = (int) $cp->id === (int) $currentPost->id;
                    $meta = $biasMeta[$bucket];
                    $src = $cp->source_id && isset($__sources[$cp->source_id]) ? $__sources[$cp->source_id] : null;
                    $sortTs = optional($cp->created_at)->timestamp ?? 0;
                    $sortBias = match ($bucket) {
                        'left' => 1,
                        'center' => 2,
                        'right' => 3,
                        default => 4,
                    };
                    $title = GnTr::title($cp);
                    $description = GnTr::description($cp);
                    $articleUrl = $__articleUrls->get((int) $cp->id) ?: ($cp->url ?: '#');
                    $bodyCandidate = \App\Support\GrimbaArticleText::cleanIngestBody(GnTr::body($cp))
                        ?: (\App\Support\GrimbaArticleText::readableBody($cp, 80)?->html ?? null);
                    $bodyPreview = trim(strip_tags((string) $bodyCandidate));
                    if ($bodyPreview !== '' && mb_strlen($bodyPreview) > mb_strlen(strip_tags((string) $description)) + 60) {
                        $description = $bodyPreview;
                    }
                    $isTranslated = GnTr::isTranslated($cp);
                    $categories = $cp->relationLoaded('categories') ? $cp->categories : collect();
                    $categories = $categories
                        ->reject(fn ($category): bool => in_array($category->name, \App\Support\GrimbaEditorialCategories::internalReviewNames(), true))
                        ->reject(fn ($category): bool => in_array($category->name, \App\Support\GrimbaEditorialCategories::editionNames(), true))
                        ->values();
                @endphp
                <li data-bias="{{ $bucket }}"
                    id="story-article-{{ (int) $cp->id }}"
                    data-sort-ts="{{ $sortTs }}"
                    data-sort-bias="{{ $sortBias }}"
                    data-grimba-compare-row
                    data-compare-id="{{ (int) $cp->id }}"
                    data-compare-bias="{{ $bucket }}"
                    data-compare-side-color="{{ $meta['color'] }}"
                    data-compare-source="{{ $cp->source_name ?? '—' }}"
                    data-compare-title="{{ $title }}"
                    data-compare-desc="{{ $description ? Str::limit(strip_tags($description), 280) : '' }}"
                    data-compare-url="{{ $articleUrl }}"
                    class="grimba-story-article {{ $isCurrent ? 'grimba-story-article--current' : '' }}"
                    style="--story-side-color: {{ $meta['color'] }}; --story-side-line: {{ $isCurrent ? $meta['color'] . '55' : 'rgba(26,23,19,0.08)' }}; --story-card-bg: {{ $isCurrent ? $meta['color'] . '0d' : 'rgba(255,255,255,0.55)' }};">

                    {{-- Source row: compare control, source identity, one lean badge. Detailed ownership/factuality lives in the breakdown panel. --}}
                    <div class="grimba-story-article__source-row">
                        {{-- S307 — compare checkbox. Sits left of the logo, only
                              activates after the reader checks one. --}}
                        <label class="grimba-compare-toggle" title="{{ __('Sélectionner pour comparer côte à côte') }}">
                            <input type="checkbox"
                                   class="grimba-compare-toggle__input"
                                   data-grimba-compare-toggle
                                   aria-label="{{ __('Comparer cette source') }}">
                            <span class="grimba-compare-toggle__control" aria-hidden="true"></span>
                        </label>
                        {!! Theme::partial('source-logo', [
                            'source_id' => $src->id ?? 0,
                            'name'    => $cp->source_name ?? '—',
                            'website' => $src->website ?? null,
                            'logo_url' => $src->logo_url ?? null,
                            'logo_status' => $src->logo_status ?? 'unknown',
                            'logo_checked_at' => $src->logo_checked_at ?? null,
                            'size'    => 28,
                            'color'   => $meta['color'],
                        ]) !!}
                        <strong class="grimba-story-article__source-name">
                            {{ $cp->source_name ?? '—' }}
                        </strong>

                        @php
                            $__biasTier = \App\Ground\Bias::tier($cp->bias_rating ?? null, $src->bias_score ?? null);
                            $__country = trim((string) ($src->country ?? ''));
                        @endphp

                        @if($__country !== '')
                            <span class="grimba-story-article__source-country">{{ strtoupper($__country) }}</span>
                        @endif

                        <span class="grimba-story-article__bias">
                            {!! Theme::partial('bias-chip', [
                                'tier' => $__biasTier,
                                'size' => 'sm',
                                'showLabel' => true,
                            ]) !!}
                        </span>

                        @if($isCurrent)
                            <span class="grimba-story-article__current">{{ __('Vous lisez') }}</span>
                        @endif

                        {{-- S173 — save-for-later icon. Cookie-only, no auth. --}}
                        {!! Theme::partial('save-button', ['post' => $cp, 'variant' => 'icon']) !!}
                    </div>

                    <h3 class="grimba-story-article__headline">
                        @if($isCurrent)
                            {{ $title }}
                        @else
                            <a href="{{ $articleUrl }}" class="grimba-story-article__headline-link">
                                {{ $title }}
                            </a>
                        @endif
                    </h3>

                    @if($isTranslated)
                        <div class="mb-2">
                            {!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}
                        </div>
                    @endif

                    @if($categories->isNotEmpty())
                        <div class="grimba-story-article__categories">
                            @foreach($categories->take(4) as $category)
                                <a href="{{ $category->url }}"
                                   class="grimba-story-article__category">
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if($description)
                        <p class="grimba-story-article__excerpt">
                            {{ Str::limit(strip_tags($description), 200) }}
                        </p>
                    @endif

                    <div class="grimba-story-article__meta">
                        <span class="grimba-story-article__time">
                            {{ $cp->created_at ? $cp->created_at->locale('fr')->diffForHumans() : '' }}
                        </span>
                        @if(! $isCurrent)
                            <a href="{{ $articleUrl }}" class="grimba-story-article__read">
                                {{ __('Lire dans GrimbaNews') }}
                            </a>
                        @endif
                    </div>
                </li>
        @endforeach
    </ul>

    {{-- S307 — Compare-sources toolbar (sticky, hidden until ≥2 selected). --}}
    <div id="grimba-compare-toolbar"
         role="region"
         aria-label="{{ __('Outil de comparaison') }}"
         class="grimba-compare-toolbar"
         hidden>
        <span class="grimba-compare-toolbar__count">
            <span data-grimba-compare-count>0</span>
            <span>{{ __('sources sélectionnées') }}</span>
        </span>
        <span class="grimba-compare-toolbar__hint">
            {{ __('Sélectionnez 2 ou 3 sources pour les comparer côte à côte.') }}
        </span>
        <div class="grimba-compare-toolbar__actions">
            <button type="button"
                    data-grimba-compare-clear
                    class="btn-grimba btn-grimba--ghost btn-grimba--sm grimba-compare-toolbar__clear">
                {{ __('Effacer') }}
            </button>
            <button type="button"
                    data-grimba-compare-open
                    class="btn-grimba btn-grimba--solid btn-grimba--sm grimba-compare-toolbar__open"
                    disabled>
                {{ __('Comparer côte à côte') }}
            </button>
        </div>
    </div>

    {{-- S307 — Compare modal (3-up side-by-side). --}}
    <div id="grimba-compare-modal"
         class="grimba-newsletter-modal grimba-compare-modal"
         role="dialog"
         aria-modal="true"
         aria-hidden="true"
         aria-labelledby="grimba-compare-title">
        <div class="grimba-newsletter-modal__backdrop" data-grimba-compare-close></div>
        <div class="grimba-newsletter-modal__panel glass-panel grimba-compare-modal__panel"
             role="document">
            <button type="button"
                    class="grimba-newsletter-modal__close"
                    aria-label="{{ __('Fermer') }}"
                    data-grimba-compare-close>×</button>

            <header class="grimba-compare-modal__header">
                <h2 id="grimba-compare-title" class="m-0 grimba-compare-modal__title">
                    {{ __('Comparer le cadrage') }}
                </h2>
                <span class="grimba-compare-modal__lede">{{ __('Voyez comment chaque source titre la même histoire.') }}</span>
            </header>

            <div class="row g-3 grimba-compare-modal__grid" data-grimba-compare-grid></div>

            <p class="grimba-compare-modal__note">
                {{ __('Le titre et l\'extrait viennent de la source originale. Ouvrez l\'article dans GrimbaNews pour lire le texte disponible et accéder au lien éditeur depuis le lecteur.') }}
            </p>
        </div>
    </div>
</section>

<script>
    (function () {
        const tabs = document.querySelectorAll('[data-grimba-cluster-tabs] [data-bias-tab]');
        const items = document.querySelectorAll('[data-grimba-cluster-list] [data-bias]');
        const sortWrap = document.querySelector('[data-grimba-cluster-sort]');
        if (! tabs.length || ! items.length) return;

        function activate(filter) {
            tabs.forEach(t => {
                const isActive = t.dataset.biasTab === filter;
                t.setAttribute('aria-selected', String(isActive));
            });
            items.forEach(li => {
                const bias = li.dataset.bias;
                li.style.display = (filter === 'all' || bias === filter) ? '' : 'none';
            });
        }

        function sortItems(mode) {
            const list = document.querySelector('[data-grimba-cluster-list]');
            if (! list) return;
            const nodes = Array.from(items);
            nodes.sort((a, b) => {
                if (mode === 'recent') {
                    return Number(b.dataset.sortTs || 0) - Number(a.dataset.sortTs || 0);
                }
                const biasDelta = Number(a.dataset.sortBias || 99) - Number(b.dataset.sortBias || 99);
                if (biasDelta !== 0) return biasDelta;
                return Number(b.dataset.sortTs || 0) - Number(a.dataset.sortTs || 0);
            });
            nodes.forEach(node => list.appendChild(node));
            document.cookie = 'grimba_cluster_sort=' + mode + '; path=/; max-age=' + (60 * 60 * 24 * 365) + '; SameSite=Lax';

            if (sortWrap) {
                sortWrap.querySelectorAll('[data-sort-mode]').forEach(btn => {
                    const active = btn.dataset.sortMode === mode;
                    btn.setAttribute('aria-pressed', String(active));
                    btn.classList.toggle('btn-grimba--solid', active);
                    btn.classList.toggle('btn-grimba--ghost', !active);
                });
            }
        }

        tabs.forEach(t => t.addEventListener('click', () => activate(t.dataset.biasTab)));
        sortWrap?.querySelectorAll('[data-sort-mode]').forEach(btn => {
            btn.addEventListener('click', () => sortItems(btn.dataset.sortMode || 'bias'));
        });
    })();

    /* S307 — compare-sources state machine. Selection cap = 3.
       Toolbar slides in once ≥1 selected; "Comparer" enables at ≥2;
       modal opens with side-by-side cards. All DOM-built with
       createElement / textContent — no innerHTML on user values. */
    (function () {
        const toolbar = document.getElementById('grimba-compare-toolbar');
        const countEl = toolbar?.querySelector('[data-grimba-compare-count]');
        const openBtn = toolbar?.querySelector('[data-grimba-compare-open]');
        const clearBtn = toolbar?.querySelector('[data-grimba-compare-clear]');
        const modal = document.getElementById('grimba-compare-modal');
        const grid = modal?.querySelector('[data-grimba-compare-grid]');
        if (!toolbar || !modal || !grid) return;

        const MAX = 3;
        const READ_IN_GRIMBA_LABEL = @json(__('Lire dans GrimbaNews'));
        let selected = [];
        let lastFocus = null;
        const trap = window.GrimbaFocus?.trap(modal, {
            initialFocus: '[data-grimba-compare-close]',
            onEscape: closeModal
        });

        function refreshToolbar() {
            const n = selected.length;
            if (countEl) countEl.textContent = String(n);
            toolbar.hidden = n === 0;
            if (openBtn) openBtn.disabled = n < 2;
        }

        function applyRowVisual(row) {
            const checked = row.querySelector('[data-grimba-compare-toggle]')?.checked;
            const color = row.dataset.compareSideColor || '#1a1713';
            row.style.outline = checked ? '2px solid ' + color : '';
            row.style.outlineOffset = checked ? '2px' : '';
        }

        function toggleRow(row) {
            const cb = row.querySelector('[data-grimba-compare-toggle]');
            if (!cb) return;
            if (cb.checked) {
                if (selected.length >= MAX) {
                    cb.checked = false;
                    return;
                }
                if (!selected.includes(row)) selected.push(row);
            } else {
                selected = selected.filter((r) => r !== row);
            }
            applyRowVisual(row);
            refreshToolbar();
        }

        function clearAll() {
            selected.forEach((r) => {
                const cb = r.querySelector('[data-grimba-compare-toggle]');
                if (cb) cb.checked = false;
                applyRowVisual(r);
            });
            selected = [];
            refreshToolbar();
        }

        function buildCard(row) {
            const color  = row.dataset.compareSideColor || '#1a1713';
            const source = row.dataset.compareSource || '—';
            const title  = row.dataset.compareTitle || '—';
            const desc   = row.dataset.compareDesc || '';
            const url    = row.dataset.compareUrl || '';

            const col = document.createElement('article');
            col.className = (selected.length === 2 ? 'col-12 col-md-6' : 'col-12 col-md-4') + ' grimba-compare-modal__col';

            const inner = document.createElement('div');
            inner.className = 'grimba-compare-modal__card';
            inner.style.setProperty('--compare-color', color);
            inner.style.setProperty('--compare-color-line', color + '33');
            inner.style.setProperty('--compare-color-soft', color + '08');

            const header = document.createElement('header');
            header.className = 'grimba-compare-modal__card-head';
            const sourceEl = document.createElement('strong');
            sourceEl.className = 'grimba-compare-modal__source';
            sourceEl.textContent = source;
            header.appendChild(sourceEl);

            const h3 = document.createElement('h3');
            h3.className = 'grimba-compare-modal__headline';
            h3.textContent = title;

            inner.appendChild(header);
            inner.appendChild(h3);

            if (desc) {
                const p = document.createElement('p');
                p.className = 'grimba-compare-modal__excerpt';
                p.textContent = desc;
                inner.appendChild(p);
            }

            if (url && (/^https?:\/\//i.test(url) || url.startsWith('/'))) {
                const a = document.createElement('a');
                a.href = url;
                a.className = 'grimba-compare-modal__link';
                a.textContent = READ_IN_GRIMBA_LABEL;
                inner.appendChild(a);
            }

            col.appendChild(inner);
            return col;
        }

        function openModal() {
            if (selected.length < 2) return;
            grid.replaceChildren();
            selected.forEach((row) => grid.appendChild(buildCard(row)));

            lastFocus = document.activeElement;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            trap?.activate(lastFocus);
        }

        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            grid.replaceChildren();
            trap?.deactivate(false);
            if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
        }

        document.querySelectorAll('[data-grimba-compare-toggle]').forEach((cb) => {
            cb.addEventListener('change', () => {
                const row = cb.closest('[data-grimba-compare-row]');
                if (row) toggleRow(row);
            });
        });
        clearBtn?.addEventListener('click', clearAll);
        openBtn?.addEventListener('click', openModal);
        modal.querySelectorAll('[data-grimba-compare-close]').forEach((b) => b.addEventListener('click', closeModal));

    })();
</script>
