@php
    /*
     * Fleet K3 — 4-region edition toggle. Cookie name stays
     * `grimba_region` for back-compat with the scope. Country lists +
     * label / migration helpers live in App\Ground\Regions.
     */
    use App\Ground\Regions;

    $currentRegion = Regions::migrate((string) (request()->cookie('grimba_region') ?? 'international'));

    $editions = [
        'africa' => [
            'label' => Regions::label('africa'),
            'href'  => route('public.edition.africa'),
        ],
        'europe' => [
            'label' => Regions::label('europe'),
            'href'  => route('public.edition.europe'),
        ],
        'americas' => [
            'label' => Regions::label('americas'),
            'href'  => route('public.edition.americas'),
        ],
        'international' => [
            'label' => Regions::label('international'),
            'href'  => route('public.edition.international'),
        ],
    ];

    $editionCounts = \Illuminate\Support\Facades\Cache::remember('grimba_edition_counts_v2', 60, function () {
        $excluded = Regions::otherNamedCodes();

        $counts = [
            'africa'   => 0,
            'europe'   => 0,
            'americas' => 0,
            'international' => 0,
        ];

        foreach (['africa', 'europe', 'americas'] as $region) {
            $codes = Regions::countries($region);
            if (! $codes) continue;
            $counts[$region] = \Botble\Blog\Models\Post::withoutGlobalScope('grimba_region')
                ->where('status', 'published')
                ->whereIn('source_id', function ($q) use ($codes): void {
                    $q->select('id')->from('news_sources')->whereIn('country', $codes);
                })
                ->count();
        }

        // International: posts whose source country is NOT in any of
        // the three named regions, OR has no country tag, OR has no
        // source at all. Mirrors the GrimbaRegionScope negative filter.
        $counts['international'] = \Botble\Blog\Models\Post::withoutGlobalScope('grimba_region')
            ->where('status', 'published')
            ->where(function ($q) use ($excluded): void {
                $q->whereIn('source_id', function ($sub) use ($excluded): void {
                    $sub->select('id')->from('news_sources')
                        ->where(function ($w) use ($excluded): void {
                            $w->whereNull('country')->orWhereNotIn('country', $excluded);
                        });
                })->orWhereNull('source_id');
            })
            ->count();

        return $counts;
    });
    $activeEdition = $editions[$currentRegion] ?? $editions['international'];
    $activeCount = (int) ($editionCounts[$currentRegion] ?? 0);
@endphp

<div class="grimba-edition-toggle grimba-edition-picker" aria-label="{{ __('Choisir une édition') }}" data-grimba-edition-root>
    <button type="button"
            class="grimba-edition-picker__trigger"
            data-grimba-edition-trigger
            aria-haspopup="true"
            aria-expanded="false">
        <span class="grimba-edition-picker__label">{{ $activeEdition['label'] }}</span>
        <span class="grimba-edition-toggle__count">{{ number_format($activeCount) }}</span>
        <span class="grimba-edition-picker__chevron" aria-hidden="true">⌄</span>
    </button>

    <div class="grimba-edition-picker__menu" role="menu">
        @foreach($editions as $key => $edition)
        @php
            $count = (int) ($editionCounts[$key] ?? 0);
            $isActive = $key === $currentRegion;
        @endphp
        <a href="{{ $edition['href'] }}"
           class="grimba-edition-toggle__option grimba-edition-picker__option @if($isActive) is-active @endif"
           aria-pressed="{{ $isActive ? 'true' : 'false' }}"
           role="menuitemradio"
           aria-checked="{{ $isActive ? 'true' : 'false' }}"
           data-grimba-edition="{{ $key }}">
            <span>{{ $edition['label'] }}</span>
            <span class="grimba-edition-toggle__count">{{ number_format($count) }}</span>
        </a>
        @endforeach
    </div>
</div>

<script>
    (function () {
        const root = document.querySelector('[data-grimba-edition-root]');
        if (!root) return;
        const trigger = root.querySelector('[data-grimba-edition-trigger]');
        const menu = root.querySelector('.grimba-edition-picker__menu');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

        function positionMenu() {
            if (!trigger || !menu) return;

            const gap = 8;
            const pad = 8;
            const rect = trigger.getBoundingClientRect();
            const menuWidth = Math.min(menu.offsetWidth || 216, window.innerWidth - (pad * 2));
            const menuHeight = menu.offsetHeight || 168;
            const left = Math.min(window.innerWidth - menuWidth - pad, Math.max(pad, rect.right - menuWidth));
            const top = Math.min(window.innerHeight - menuHeight - pad, rect.bottom + gap);

            menu.style.left = left + 'px';
            menu.style.top = Math.max(pad, top) + 'px';
        }

        function close() {
            root.classList.remove('is-open');
            trigger?.setAttribute('aria-expanded', 'false');
        }

        trigger?.addEventListener('click', event => {
            event.preventDefault();
            const open = !root.classList.contains('is-open');
            root.classList.toggle('is-open', open);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) positionMenu();
        });

        document.addEventListener('click', event => {
            if (!root.contains(event.target)) close();
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') close();
        });

        window.addEventListener('resize', () => {
            if (root.classList.contains('is-open')) positionMenu();
        }, { passive: true });

        window.addEventListener('scroll', () => {
            if (root.classList.contains('is-open')) positionMenu();
        }, { passive: true });

        root.querySelectorAll('[data-grimba-edition]').forEach(link => {
            link.addEventListener('click', async event => {
                event.preventDefault();
                const region = link.dataset.grimbaEdition;
                const res = await fetch(@json(route('public.region.set')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ region })
                }).then(r => r.json()).catch(() => null);

                if (res && res.ok) {
                    window.location.href = link.href;
                }
            });
        });
    })();
</script>
