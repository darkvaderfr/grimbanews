@php
    $currentRegion = (string) (request()->cookie('grimba_region') ?? 'monde');

    $regions = [
        'monde'         => ['label' => 'Monde',          'flag' => '🌍'],
        'afrique'       => ['label' => 'Afrique',        'flag' => '🌍'],
        'europe'        => ['label' => 'Europe',         'flag' => '🇪🇺'],
        'france'        => ['label' => 'France',         'flag' => '🇫🇷'],
        'international' => ['label' => 'International',  'flag' => '🌐'],
    ];

    $current = $regions[$currentRegion] ?? $regions['monde'];
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
            <li>
                <button type="button"
                        role="option"
                        aria-selected="{{ $key === $currentRegion ? 'true' : 'false' }}"
                        data-grimba-region="{{ $key }}"
                        class="grimba-region__option @if($key === $currentRegion) is-active @endif">
                    <span aria-hidden="true">{{ $r['flag'] }}</span>
                    <span>{{ $r['label'] }}</span>
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

        function setOpen(open) {
            root.classList.toggle('is-open', open);
            trigger.setAttribute('aria-expanded', String(open));
        }

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            setOpen(! root.classList.contains('is-open'));
        });
        document.addEventListener('click', (e) => {
            if (! root.contains(e.target)) setOpen(false);
        });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });

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
