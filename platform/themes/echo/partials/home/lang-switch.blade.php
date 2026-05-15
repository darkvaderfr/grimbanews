@php
    $currentLang = (string) (request()->cookie('grimba_lang') ?? app()->getLocale() ?? 'fr');
    if (! in_array($currentLang, ['fr', 'en'], true)) $currentLang = 'fr';
@endphp

<button type="button"
        class="grimba-lang-toggle grimba-lang-toggle--compact"
        data-grimba-lang-toggle
        data-current-lang="{{ $currentLang }}"
        aria-label="{{ $currentLang === 'fr' ? __('Passer en anglais') : __('Switch to French') }}">
    <span class="grimba-lang-toggle__current">{{ strtoupper($currentLang) }}</span>
    <span class="grimba-lang-toggle__next" aria-hidden="true">{{ $currentLang === 'fr' ? 'EN' : 'FR' }}</span>
</button>

<script>
    (function () {
        const toggle = document.querySelector('[data-grimba-lang-toggle]');
        if (!toggle) return;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

        toggle.addEventListener('click', async () => {
            const lang = (toggle.dataset.currentLang || 'fr') === 'fr' ? 'en' : 'fr';
            const res = await fetch(@json(route('public.lang.set')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ lang })
            }).then(r => r.json()).catch(() => null);
            if (res && res.ok) window.location.reload();
        });
    })();
</script>
