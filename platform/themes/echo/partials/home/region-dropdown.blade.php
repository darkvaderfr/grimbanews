@php
    /**
     * S146 — region map aligned with audience countries Vader actually
     * publishes for: France, UK, US, Canada, Africa, International.
     * Drops the previous Monde/Europe placeholders that didn't map
     * cleanly to news-source country codes (FR/GB/US/CA/Africa/—).
     *
     * The cookie value is the routing key the rest of the site reads
     * (filtering RSS feeds, NewsAPI top-headlines, blog facets), so
     * the keys are stable lowercase ISO-style strings.
     *
     * S155 — picker now shows (N) beside each region label, computed
     * from the same country mapping as GrimbaRegionScope so what the
     * reader sees in the dropdown matches what they get after the
     * cookie flips. International always shows the unfiltered total.
     */
    $currentRegion = (string) (request()->cookie('grimba_region') ?? 'international');

    $regions = [
        'france'        => ['label' => 'France',        'flag' => '🇫🇷'],
        'uk'            => ['label' => 'UK',            'flag' => '🇬🇧'],
        'us'            => ['label' => 'US',            'flag' => '🇺🇸'],
        'canada'        => ['label' => 'Canada',        'flag' => '🇨🇦'],
        'africa'        => ['label' => 'Afrique',       'flag' => '🌍'],
        'international' => ['label' => 'International', 'flag' => '🌐'],
    ];

    // Migrate legacy values: monde/europe → international, afrique → africa.
    $migrationMap = ['monde' => 'international', 'europe' => 'international', 'afrique' => 'africa'];
    if (isset($migrationMap[$currentRegion])) $currentRegion = $migrationMap[$currentRegion];

    $current = $regions[$currentRegion] ?? $regions['international'];

    // S155 — per-region published-post counts. Mirror the country-code
    // map from GrimbaRegionScope (kept inline rather than coupling
    // partials to Scope internals).
    $regionCountryMap = [
        'france' => ['FR'],
        'uk'     => ['GB', 'UK'],
        'us'     => ['US'],
        'canada' => ['CA'],
        'africa' => ['DZ','AO','BJ','BW','BF','BI','CV','CM','CF','TD','KM','CG','CD','DJ','EG','GQ','ER','SZ','ET','GA','GM','GH','GN','GW','CI','KE','LS','LR','LY','MG','MW','ML','MR','MU','MA','MZ','NA','NE','NG','RW','ST','SN','SC','SL','SO','ZA','SS','SD','TZ','TG','TN','UG','ZM','ZW'],
    ];

    // Cache the counts for a minute — every page load shouldn't re-aggregate.
    $regionCounts = \Illuminate\Support\Facades\Cache::remember(
        'grimba_region_counts_v1',
        60,
        function () use ($regionCountryMap) {
            $counts = [];
            // Total (international) = all published, scope-bypassed via withoutGlobalScope.
            $counts['international'] = \Botble\Blog\Models\Post::withoutGlobalScope('grimba_region')
                ->where('status', 'published')
                ->count();
            foreach ($regionCountryMap as $key => $codes) {
                $counts[$key] = \Botble\Blog\Models\Post::withoutGlobalScope('grimba_region')
                    ->where('status', 'published')
                    ->whereIn('source_id', function ($q) use ($codes): void {
                        $q->select('id')->from('news_sources')->whereIn('country', $codes);
                    })
                    ->count();
            }
            return $counts;
        }
    );
@endphp

<div class="grimba-region" data-grimba-region-root>
    <button type="button" class="grimba-region__trigger" data-grimba-region-toggle
            aria-haspopup="listbox" aria-expanded="false">
        <span aria-hidden="true">{{ $current['flag'] }}</span>
        <span>Édition {{ $current['label'] }}</span>
        <span aria-hidden="true" class="grimba-region__caret">▾</span>
    </button>
    <ul class="grimba-region__menu" role="listbox" aria-label="Édition régionale">
        @foreach($regions as $key => $r)
            @php
                $count = (int) ($regionCounts[$key] ?? 0);
                $disabled = $count === 0 && $key !== 'international';
            @endphp
            <li>
                <button type="button"
                        role="option"
                        aria-selected="{{ $key === $currentRegion ? 'true' : 'false' }}"
                        @if($disabled) aria-disabled="true" @endif
                        data-grimba-region="{{ $key }}"
                        class="grimba-region__option @if($key === $currentRegion) is-active @endif"
                        @if($disabled) style="opacity:0.4; cursor:not-allowed;" disabled @endif>
                    <span aria-hidden="true">{{ $r['flag'] }}</span>
                    <span>{{ $r['label'] }}</span>
                    <span class="ms-auto small opacity-65" style="font-variant-numeric: tabular-nums;">
                        {{ number_format($count) }}
                    </span>
                </button>
            </li>
        @endforeach
    </ul>
</div>

<script>
    (function () {
        const root = document.querySelector('[data-grimba-region-root]');
        if (!root) return;

        const trigger = root.querySelector('[data-grimba-region-toggle]');
        const menu    = root.querySelector('.grimba-region__menu');
        const csrf    = document.querySelector('meta[name="csrf-token"]')?.content || '';

        function positionMenu() {
            const r = trigger.getBoundingClientRect();
            menu.style.visibility = 'hidden';
            menu.style.display = 'block';
            const mw = menu.offsetWidth, mh = menu.offsetHeight;
            menu.style.visibility = '';
            menu.style.display = '';
            let top  = r.bottom + 6;
            if (top + mh > window.innerHeight - 8) top = Math.max(8, r.top - mh - 6);
            const left = Math.max(8, r.right - mw);
            menu.style.top  = top + 'px';
            menu.style.left = left + 'px';
            menu.style.right = 'auto';
        }
        function setOpen(open) {
            if (open) positionMenu();
            root.classList.toggle('is-open', open);
            trigger.setAttribute('aria-expanded', String(open));
        }

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            setOpen(! root.classList.contains('is-open'));
        });
        document.addEventListener('click', (e) => {
            if (! root.contains(e.target) && ! menu.contains(e.target)) setOpen(false);
        });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });
        window.addEventListener('resize', () => { if (root.classList.contains('is-open')) positionMenu(); });
        window.addEventListener('scroll', () => { if (root.classList.contains('is-open')) positionMenu(); }, { passive: true });

        menu.querySelectorAll('[data-grimba-region]').forEach(btn => btn.addEventListener('click', async () => {
            const region = btn.dataset.grimbaRegion;
            const res = await fetch(@json(route('public.region.set')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ region })
            }).then(r => r.json()).catch(() => null);
            if (res && res.ok) window.location.reload();
        }));
    })();
</script>
