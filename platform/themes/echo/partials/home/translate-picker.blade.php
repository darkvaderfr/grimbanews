@php
    $mode = (string) (request()->cookie('grimba_translate') ?? 'original');
    if (! in_array($mode, ['original', 'auto', 'both'], true)) $mode = 'original';
@endphp

<div class="grimba-translate" data-grimba-translate-root>
    <button type="button" class="grimba-translate__trigger" data-grimba-translate-toggle
            aria-haspopup="listbox" aria-expanded="false" title="{{ __('Mode de lecture') }}">
        <span aria-hidden="true">Aa</span>
        <span>{{ ['original' => __('VO'), 'auto' => __('Auto'), 'both' => __('VO + Auto')][$mode] }}</span>
        <span aria-hidden="true" class="grimba-region__caret">▾</span>
    </button>
    <ul class="grimba-region__menu" role="listbox" aria-label="{{ __('Mode de lecture') }}">
        @foreach([
            'original' => ['label' => __('Version originale'),     'desc' => __('Articles affichés dans leur langue d\'origine.')],
            'auto'     => ['label' => __('Traduction automatique'), 'desc' => __('Tout traduit dans votre langue (machine).')],
            'both'     => ['label' => __('Original + traduction'),  'desc' => __('Affiche les deux, côte à côte.')],
        ] as $key => $r)
            <li>
                <button type="button"
                        role="option"
                        aria-selected="{{ $key === $mode ? 'true' : 'false' }}"
                        data-grimba-translate="{{ $key }}"
                        class="grimba-region__option @if($key === $mode) is-active @endif"
                        style="align-items: flex-start;">
                    <span aria-hidden="true">●</span>
                    <span>
                        <strong>{{ $r['label'] }}</strong>
                        <span class="d-block small opacity-75 mt-1">{{ $r['desc'] }}</span>
                    </span>
                </button>
            </li>
        @endforeach
    </ul>
</div>

<script>
    (function () {
        const root = document.querySelector('[data-grimba-translate-root]');
        if (!root) return;
        const trigger = root.querySelector('[data-grimba-translate-toggle]');
        const menu    = root.querySelector('.grimba-region__menu');
        const csrf    = document.querySelector('meta[name="csrf-token"]')?.content || '';

        function positionMenu() {
            const r = trigger.getBoundingClientRect();
            // Right-align: menu's right edge follows trigger's right edge.
            // Flip upward if the menu would clip below the viewport.
            menu.style.visibility = 'hidden';
            menu.style.display = 'block';
            const mw = menu.offsetWidth;
            const mh = menu.offsetHeight;
            menu.style.visibility = '';
            menu.style.display = '';

            let top  = r.bottom + 6;
            if (top + mh > window.innerHeight - 8) {
                top = Math.max(8, r.top - mh - 6);
            }
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
        trigger.addEventListener('click', (e) => { e.stopPropagation(); setOpen(! root.classList.contains('is-open')); });
        document.addEventListener('click', (e) => { if (! root.contains(e.target) && ! menu.contains(e.target)) setOpen(false); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });
        window.addEventListener('resize', () => { if (root.classList.contains('is-open')) positionMenu(); });
        window.addEventListener('scroll', () => { if (root.classList.contains('is-open')) positionMenu(); }, { passive: true });

        menu.querySelectorAll('[data-grimba-translate]').forEach(btn => btn.addEventListener('click', async () => {
            const mode = btn.dataset.grimbaTranslate;
            const res = await fetch(@json(route('public.translate.set')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ mode })
            }).then(r => r.json()).catch(() => null);
            if (res && res.ok) window.location.reload();
        }));
    })();
</script>
