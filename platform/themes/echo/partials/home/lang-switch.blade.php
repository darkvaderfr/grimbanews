@php
    $currentLang = (string) (request()->cookie('grimba_lang') ?? app()->getLocale() ?? 'fr');
    if (! in_array($currentLang, ['fr', 'en'], true)) $currentLang = 'fr';
@endphp

<div class="grimba-theme-switch grimba-lang-switch" role="radiogroup" aria-label="{{ __('Choix de la langue') }}">
    <button type="button" data-grimba-lang="fr"
            aria-pressed="{{ $currentLang === 'fr' ? 'true' : 'false' }}"
            aria-label="{{ __('Lire en français') }}"
            title="Français">FR</button>
    <button type="button" data-grimba-lang="en"
            aria-pressed="{{ $currentLang === 'en' ? 'true' : 'false' }}"
            aria-label="{{ __('Read in English') }}"
            title="English">EN</button>
</div>

<script>
    (function () {
        const buttons = document.querySelectorAll('[data-grimba-lang]');
        if (!buttons.length) return;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

        buttons.forEach(btn => btn.addEventListener('click', async () => {
            const lang = btn.dataset.grimbaLang;
            const res = await fetch(@json(route('public.lang.set')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ lang })
            }).then(r => r.json()).catch(() => null);
            if (res && res.ok) window.location.reload();
        }));
    })();
</script>
