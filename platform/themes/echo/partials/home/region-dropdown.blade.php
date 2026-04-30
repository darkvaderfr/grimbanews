@php
    /*
     * Africa / International edition toggle.
     *
     * The cookie name stays `grimba_region` for compatibility with the
     * existing GrimbaRegionScope, but the public product language is now
     * "edition". Legacy six-region values are folded into International.
     */
    $currentRegion = (string) (request()->cookie('grimba_region') ?? 'international');
    $migrationMap = [
        'monde' => 'international',
        'europe' => 'international',
        'afrique' => 'africa',
        'france' => 'international',
        'uk' => 'international',
        'us' => 'international',
        'canada' => 'international',
    ];
    $currentRegion = $migrationMap[$currentRegion] ?? $currentRegion;
    if (! in_array($currentRegion, ['africa', 'international'], true)) {
        $currentRegion = 'international';
    }

    $editions = [
        'africa' => [
            'label' => 'Afrique',
            'href' => route('public.edition.africa'),
            'count_key' => 'africa',
        ],
        'international' => [
            'label' => 'International',
            'href' => route('public.edition.international'),
            'count_key' => 'international',
        ],
    ];

    $africaCountries = [
        'DZ','AO','BJ','BW','BF','BI','CV','CM','CF','TD','KM','CG','CD','DJ',
        'EG','GQ','ER','SZ','ET','GA','GM','GH','GN','GW','CI','KE','LS','LR',
        'LY','MG','MW','ML','MR','MU','MA','MZ','NA','NE','NG','RW','ST','SN',
        'SC','SL','SO','ZA','SS','SD','TZ','TG','TN','UG','ZM','ZW',
    ];

    $editionCounts = \Illuminate\Support\Facades\Cache::remember('grimba_edition_counts_v1', 60, function () use ($africaCountries) {
        return [
            'international' => \Botble\Blog\Models\Post::withoutGlobalScope('grimba_region')
                ->where('status', 'published')
                ->count(),
            'africa' => \Botble\Blog\Models\Post::withoutGlobalScope('grimba_region')
                ->where('status', 'published')
                ->whereIn('source_id', function ($q) use ($africaCountries): void {
                    $q->select('id')->from('news_sources')->whereIn('country', $africaCountries);
                })
                ->count(),
        ];
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
            $count = (int) ($editionCounts[$edition['count_key']] ?? 0);
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
