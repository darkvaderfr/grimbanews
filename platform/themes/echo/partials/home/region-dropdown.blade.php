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
@endphp

<style>
    .grimba-edition-toggle {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px;
        border: 1px solid rgba(26, 23, 19, 0.14);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.58);
        box-shadow: 0 10px 28px rgba(26, 23, 19, 0.08);
    }

    .grimba-edition-toggle__option {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 34px;
        padding: 6px 12px;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: #1a1713;
        font-size: 13px;
        font-weight: 800;
        line-height: 1;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .grimba-edition-toggle__option:hover,
    .grimba-edition-toggle__option:focus-visible {
        color: #1a1713;
        background: rgba(26, 23, 19, 0.07);
        outline: none;
    }

    .grimba-edition-toggle__option.is-active {
        background: #1a1713;
        color: #f8f1e6;
        box-shadow: 0 8px 18px rgba(26, 23, 19, 0.16);
    }

    .grimba-edition-toggle__count {
        opacity: .62;
        font-size: 11px;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    html[data-bs-theme="dark"] .grimba-edition-toggle {
        background: rgba(246, 241, 232, 0.08);
        border-color: rgba(246, 241, 232, 0.18);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.28);
    }

    html[data-bs-theme="dark"] .grimba-edition-toggle__option {
        color: #f8f1e6;
    }

    html[data-bs-theme="dark"] .grimba-edition-toggle__option:hover,
    html[data-bs-theme="dark"] .grimba-edition-toggle__option:focus-visible {
        color: #ffffff;
        background: rgba(246, 241, 232, 0.12);
    }

    html[data-bs-theme="dark"] .grimba-edition-toggle__option.is-active {
        background: #f8f1e6;
        color: #15130f;
    }
</style>

<div class="grimba-edition-toggle" role="group" aria-label="{{ __('Choisir une édition') }}" data-grimba-edition-root>
    @foreach($editions as $key => $edition)
        @php
            $count = (int) ($editionCounts[$key] ?? 0);
            $isActive = $key === $currentRegion;
        @endphp
        <a href="{{ $edition['href'] }}"
           class="grimba-edition-toggle__option @if($isActive) is-active @endif"
           aria-pressed="{{ $isActive ? 'true' : 'false' }}"
           data-grimba-edition="{{ $key }}">
            <span>{{ $edition['label'] }}</span>
            <span class="grimba-edition-toggle__count">{{ number_format($count) }}</span>
        </a>
    @endforeach
</div>

<script>
    (function () {
        const root = document.querySelector('[data-grimba-edition-root]');
        if (!root) return;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

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
